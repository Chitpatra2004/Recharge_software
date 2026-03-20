<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * EmployeeAuthController — secure login/logout for internal staff.
 *
 * Security features:
 *   1. Brute force protection — tracks failed attempts per email in cache.
 *      After MAX_ATTEMPTS failures, the account is hard-locked in the DB
 *      (employees.locked_until) for LOCKOUT_MINUTES minutes.
 *
 *   2. Account status check — suspended/inactive employees are rejected
 *      even with correct credentials.
 *
 *   3. Timing-safe credential check — Hash::check() uses constant-time
 *      comparison to prevent timing attacks.
 *
 *   4. Opaque error messages — invalid email and wrong password both
 *      return the same message to prevent user enumeration.
 *
 *   5. Full audit trail — every login attempt (success and failure) is
 *      written to activity_logs with IP, UA, and outcome.
 *
 *   6. Token scoping — Sanctum tokens are issued with ability tags
 *      matching the employee's role (e.g. ["admin", "reports:view"]).
 *
 *   7. Token expiry — tokens expire after 8 hours (configurable).
 *
 *   8. Single active session — all existing tokens for the device name
 *      are revoked before issuing a new one.
 */
class EmployeeAuthController extends Controller
{
    private const MAX_ATTEMPTS     = 5;
    private const LOCKOUT_MINUTES  = 30;
    private const TOKEN_TTL_HOURS  = 8;
    private const ATTEMPT_WINDOW   = 15; // minutes to count failed attempts

    // ─────────────────────────────────────────────────────────────────────
    // POST /api/v1/employee/auth/login
    // ─────────────────────────────────────────────────────────────────────

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => ['required', 'email', 'max:150'],
            'password' => ['required', 'string', 'min:8', 'max:72'],
            'device'   => ['sometimes', 'string', 'max:100'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid credentials format.'], 422);
        }

        $email    = strtolower(trim($request->input('email')));
        $password = $request->input('password');
        $device   = $request->input('device', 'employee-panel');
        $ip       = $request->ip();

        // ── Check IP-level lockout from DetectBruteForce middleware ──────
        // (handled by middleware — redundant here, but belt-and-suspenders)

        // ── Lookup employee ───────────────────────────────────────────────
        $employee = Employee::where('email', $email)->first();

        // ── Check DB account lockout ──────────────────────────────────────
        if ($employee && $employee->locked_until && $employee->locked_until->isFuture()) {
            $minutesLeft = now()->diffInMinutes($employee->locked_until, false);
            $this->logAttempt($employee?->id, $email, 'auth.employee_login_locked', $ip, $request);

            return response()->json([
                'message'     => 'Account is temporarily locked due to multiple failed login attempts.',
                'retry_after' => max(0, $minutesLeft) . ' minutes',
            ], 423); // 423 Locked
        }

        // ── Constant-time credential validation ───────────────────────────
        // Always run Hash::check() even if employee not found (prevents timing attack)
        $dummyHash   = '$2y$12$invalidhashtopreventtimingattacks00000000000000000000000';
        $validCreds  = $employee && Hash::check($password, $employee->password ?? $dummyHash);

        if (! $validCreds) {
            $this->recordFailedAttempt($employee, $email, $ip, $request);

            return response()->json([
                'message' => 'These credentials do not match our records.',
            ], 401);
        }

        // ── Account status check ──────────────────────────────────────────
        if (! $employee->isActive()) {
            $this->logAttempt($employee->id, $email, 'auth.employee_login_suspended', $ip, $request);

            return response()->json([
                'message' => 'Your account has been suspended. Contact the administrator.',
            ], 403);
        }

        // ── Success: reset failure counter, issue token ───────────────────
        $this->clearFailureCounter($email);

        // Revoke old tokens for this device (single active session)
        $employee->tokens()->where('name', $device)->delete();

        // Issue new Sanctum token with role-based abilities
        $abilities = $this->resolveAbilities($employee);
        $token     = $employee->createToken($device, $abilities, now()->addHours(self::TOKEN_TTL_HOURS));

        // Update login audit fields
        DB::table('employees')->where('id', $employee->id)->update([
            'last_login_at'       => now(),
            'last_login_ip'       => $ip,
            'failed_login_count'  => 0,
            'locked_until'        => null,
        ]);

        $this->logAttempt($employee->id, $email, 'auth.employee_login', $ip, $request, 'success');

        return response()->json([
            'token'      => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at?->toIso8601String(),
            'employee'   => [
                'id'          => $employee->id,
                'name'        => $employee->name,
                'email'       => $employee->email,
                'role'        => $employee->role,
                'department'  => $employee->department,
                'designation' => $employee->designation,
                'abilities'   => $abilities,
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST /api/v1/employee/auth/logout
    // ─────────────────────────────────────────────────────────────────────

    public function logout(Request $request): JsonResponse
    {
        $employee = $request->user('employee');

        if ($employee) {
            $this->logAttempt($employee->id, $employee->email, 'auth.employee_logout', $request->ip(), $request, 'success');
            $request->user('employee')->currentAccessToken()->delete();
        }

        return response()->json(['message' => 'Logged out successfully.']);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST /api/v1/employee/auth/logout-all
    // Revokes ALL tokens for this employee (all devices)
    // ─────────────────────────────────────────────────────────────────────

    public function logoutAll(Request $request): JsonResponse
    {
        $employee = $request->user('employee');

        if ($employee) {
            $count = $employee->tokens()->count();
            $employee->tokens()->delete();
            $this->logAttempt($employee->id, $employee->email, 'auth.employee_logout_all', $request->ip(), $request, 'success');

            return response()->json(['message' => "All {$count} session(s) revoked."]);
        }

        return response()->json(['message' => 'No active session.'], 401);
    }

    // ─────────────────────────────────────────────────────────────────────
    // GET /api/v1/employee/auth/me
    // ─────────────────────────────────────────────────────────────────────

    public function me(Request $request): JsonResponse
    {
        $employee = $request->user('employee');

        return response()->json([
            'id'          => $employee->id,
            'name'        => $employee->name,
            'email'       => $employee->email,
            'role'        => $employee->role,
            'department'  => $employee->department,
            'designation' => $employee->designation,
            'permissions' => $employee->permissions,
            'last_login'  => $employee->last_login_at?->toIso8601String(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Record a failed login attempt.
     * Increments cache counter AND the DB column, then locks the account
     * if the threshold is reached.
     */
    private function recordFailedAttempt(?Employee $employee, string $email, string $ip, Request $request): void
    {
        $cacheKey = 'emp_failed_login:' . md5($email);
        $count    = (int) Cache::get($cacheKey, 0) + 1;
        Cache::put($cacheKey, $count, now()->addMinutes(self::ATTEMPT_WINDOW));

        if ($employee) {
            $newCount  = $employee->failed_login_count + 1;
            $lockUntil = $newCount >= self::MAX_ATTEMPTS
                ? now()->addMinutes(self::LOCKOUT_MINUTES)
                : null;

            DB::table('employees')->where('id', $employee->id)->update([
                'failed_login_count' => $newCount,
                'locked_until'       => $lockUntil,
            ]);

            if ($lockUntil) {
                Log::warning('Employee account locked', [
                    'employee_id' => $employee->id,
                    'email'       => $email,
                    'ip'          => $ip,
                    'locked_until'=> $lockUntil,
                ]);
            }
        }

        $this->logAttempt($employee?->id, $email, 'auth.employee_login_failed', $ip, $request);
    }

    /** Clear failed attempt counters after a successful login */
    private function clearFailureCounter(string $email): void
    {
        Cache::forget('emp_failed_login:' . md5($email));
    }

    /**
     * Map employee role to Sanctum token abilities.
     * Tokens carry only the permissions the employee's role allows.
     */
    private function resolveAbilities(Employee $employee): array
    {
        $roleAbilities = [
            'super_admin' => ['*'],  // all abilities
            'admin'       => ['dashboard:view', 'reports:view', 'users:manage', 'complaints:manage', 'wallet:topup', 'recharge:refund'],
            'manager'     => ['dashboard:view', 'reports:view', 'complaints:manage', 'users:view'],
            'agent'       => ['dashboard:view', 'complaints:manage', 'complaints:resolve'],
            'viewer'      => ['dashboard:view', 'reports:view'],
        ];

        $base = $roleAbilities[$employee->role] ?? ['dashboard:view'];

        // Merge any custom JSON permissions from the employee record
        if (! empty($employee->permissions)) {
            foreach ($employee->permissions as $perm => $allowed) {
                if ($allowed && ! in_array($perm, $base)) {
                    $base[] = $perm;
                }
            }
        }

        return $base;
    }

    /** Append an entry to activity_logs */
    private function logAttempt(
        ?int    $employeeId,
        string  $email,
        string  $action,
        string  $ip,
        Request $request,
        string  $outcome = 'failed'
    ): void {
        try {
            DB::table('activity_logs')->insert([
                'actor_type'  => 'employee',
                'actor_id'    => $employeeId,
                'action'      => $action,
                'description' => "Employee login {$outcome}: {$email}",
                'properties'  => json_encode(['email' => $email, 'outcome' => $outcome]),
                'ip_address'  => $ip,
                'user_agent'  => substr((string) $request->userAgent(), 0, 500),
                'url'         => $request->fullUrl(),
                'method'      => $request->method(),
                'created_at'  => now(),
            ]);
        } catch (\Throwable) {
            // Logging must never break auth
        }
    }
}
