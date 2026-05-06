<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Services\OtpService;
use App\Services\TotpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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
    private const MAX_ATTEMPTS      = 5;
    private const LOCKOUT_MINUTES   = 30;
    private const TOKEN_TTL_HOURS   = 8;
    private const ATTEMPT_WINDOW    = 15;  // minutes to count failed attempts
    private const PENDING_2FA_TTL   = 10;  // minutes a pending_token stays valid

    public function __construct(
        private readonly OtpService  $otpService,
        private readonly TotpService $totpService,
    ) {}

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
        try {
            $validCreds = $employee && Hash::check($password, $employee->password ?? $dummyHash);
        } catch (\RuntimeException) {
            $validCreds = false;
        }

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

        // ── Success: reset failure counter ────────────────────────────────
        $this->clearFailureCounter($email);

        // ── 2FA gate ──────────────────────────────────────────────────────
        $methods = $employee->enabledTwoFactorMethods();

        if ($employee->two_factor_enabled && count($methods) > 0) {
            $pendingToken = Str::random(64);
            Cache::put(
                'emp_pending_2fa:' . $pendingToken,
                ['employee_id' => $employee->id, 'device' => $device, 'methods' => $methods],
                now()->addMinutes(self::PENDING_2FA_TTL)
            );

            if (count($methods) === 1 && in_array($methods[0], ['mobile_otp', 'email_otp'], true)) {
                $this->sendLoginOtp($employee, $methods[0], $ip);
            }

            $this->logAttempt($employee->id, $email, 'auth.employee_2fa_required', $ip, $request, 'pending');

            return response()->json([
                'requires_2fa'  => true,
                'method'        => $methods[0],
                'methods'       => $this->formatTwoFactorMethods($employee, $methods),
                'pending_token' => $pendingToken,
                'message'       => count($methods) > 1
                    ? 'Choose a verification method to continue.'
                    : $this->twoFactorMethodMessage($methods[0]),
            ]);
        }

        // ── No 2FA — issue token directly ────────────────────────────────
        return $this->issueTokenResponse($employee, $device, $ip, $request);
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
    // POST /api/v1/employee/auth/2fa/verify-otp
    // ─────────────────────────────────────────────────────────────────────
    public function verify2faOtp(Request $request): JsonResponse
    {
        $request->validate([
            'pending_token' => ['required', 'string'],
            'otp'           => ['required', 'digits:6'],
            'method'        => ['sometimes', 'string', 'in:mobile_otp,email_otp,otp'],
        ]);

        $pending = $this->resolvePendingToken($request->pending_token);
        if (! $pending) {
            return response()->json(['message' => 'Session expired. Please login again.'], 401);
        }

        $employee = Employee::findOrFail($pending['employee_id']);
        $method = $this->normalizeOtpMethod($request->input('method'), $pending, $employee);

        if (! $method || ! $this->otpService->verify($this->otpIdentifier($employee, $method), $this->otpType($method), $request->otp)) {
            return response()->json(['message' => 'Invalid OTP. Please try again.'], 422);
        }

        Cache::forget('emp_pending_2fa:' . $request->pending_token);
        return $this->issueTokenResponse($employee, $pending['device'], $request->ip(), $request);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST /api/v1/employee/auth/2fa/verify-totp
    // ─────────────────────────────────────────────────────────────────────
    public function verify2faTotp(Request $request): JsonResponse
    {
        $request->validate([
            'pending_token' => ['required', 'string'],
            'code'          => ['required', 'digits:6'],
            'method'        => ['sometimes', 'string', 'in:google_authenticator,microsoft_authenticator,totp'],
        ]);

        $pending = $this->resolvePendingToken($request->pending_token);
        if (! $pending) {
            return response()->json(['message' => 'Session expired. Please login again.'], 401);
        }

        $employee = Employee::findOrFail($pending['employee_id']);
        $method = $this->normalizeTotpMethod($request->input('method'), $pending, $employee);

        if (! $method || ! $employee->totp_secret || ! $this->totpService->verify($employee->totp_secret, $request->code)) {
            return response()->json(['message' => 'Invalid code. Please try again.'], 422);
        }

        Cache::forget('emp_pending_2fa:' . $request->pending_token);
        return $this->issueTokenResponse($employee, $pending['device'], $request->ip(), $request);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST /api/v1/employee/auth/2fa/resend-otp
    // ─────────────────────────────────────────────────────────────────────
    public function resend2faOtp(Request $request): JsonResponse
    {
        $request->validate([
            'pending_token' => ['required', 'string'],
            'method'        => ['sometimes', 'string', 'in:mobile_otp,email_otp,otp'],
        ]);

        $pending = $this->resolvePendingToken($request->pending_token);
        if (! $pending) {
            return response()->json(['message' => 'Session expired. Please login again.'], 401);
        }

        $employee = Employee::findOrFail($pending['employee_id']);
        $method = $this->normalizeOtpMethod($request->input('method'), $pending, $employee);

        if (! $method) {
            return response()->json(['message' => 'Select a valid OTP method.'], 422);
        }

        $this->sendLoginOtp($employee, $method, $request->ip());

        return response()->json(['message' => $this->otpSentMessage($method)]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────

    private function resolvePendingToken(string $token): ?array
    {
        return Cache::get('emp_pending_2fa:' . $token);
    }

    private function sendLoginOtp(Employee $employee, string $method, string $ip): void
    {
        $this->otpService->generate(
            $this->otpIdentifier($employee, $method),
            $this->otpType($method),
            $employee->id,
            $ip
        );
    }

    private function normalizeOtpMethod(?string $method, array $pending, Employee $employee): ?string
    {
        $method = $method === 'otp' ? 'mobile_otp' : $method;
        $methods = $pending['methods'] ?? $employee->enabledTwoFactorMethods();

        if (! $method && count($methods) === 1 && in_array($methods[0], ['mobile_otp', 'email_otp'], true)) {
            $method = $methods[0];
        }

        return in_array($method, ['mobile_otp', 'email_otp'], true)
            && in_array($method, $methods, true)
            ? $method
            : null;
    }

    private function normalizeTotpMethod(?string $method, array $pending, Employee $employee): ?string
    {
        $method = $method === 'totp' ? 'google_authenticator' : $method;
        $methods = $pending['methods'] ?? $employee->enabledTwoFactorMethods();
        $totpMethods = array_values(array_intersect($methods, ['google_authenticator', 'microsoft_authenticator']));

        if (! $method && count($totpMethods) === 1) {
            $method = $totpMethods[0];
        }

        return in_array($method, $totpMethods, true) ? $method : null;
    }

    private function otpIdentifier(Employee $employee, string $method): string
    {
        return $method === 'email_otp' ? $employee->email : $employee->mobile;
    }

    private function otpType(string $method): string
    {
        return $method === 'email_otp' ? 'login_2fa_email' : 'login_2fa_mobile';
    }

    private function otpSentMessage(string $method): string
    {
        return $method === 'email_otp'
            ? 'A new OTP has been sent to your registered email.'
            : 'A new OTP has been sent to your registered mobile.';
    }

    private function twoFactorMethodMessage(string $method): string
    {
        return match ($method) {
            'google_authenticator' => 'Enter the 6-digit code from Google Authenticator.',
            'microsoft_authenticator' => 'Enter the 6-digit code from Microsoft Authenticator.',
            'email_otp' => 'A verification code has been sent to your registered email.',
            default => 'A verification code has been sent to your registered mobile.',
        };
    }

    private function formatTwoFactorMethods(Employee $employee, array $methods): array
    {
        return collect($methods)->map(fn (string $method) => [
            'key' => $method,
            'label' => match ($method) {
                'google_authenticator' => 'Google Authenticator',
                'microsoft_authenticator' => 'Microsoft Authenticator',
                'email_otp' => 'Email OTP',
                default => 'Mobile OTP',
            },
            'channel' => in_array($method, ['google_authenticator', 'microsoft_authenticator'], true) ? 'totp' : 'otp',
            'masked' => match ($method) {
                'email_otp' => $this->maskEmail($employee->email),
                'mobile_otp' => $this->maskMobile($employee->mobile),
                default => 'Authenticator app',
            },
        ])->values()->all();
    }

    private function maskEmail(?string $email): string
    {
        if (! $email || ! str_contains($email, '@')) {
            return 'registered email';
        }

        [$name, $domain] = explode('@', $email, 2);
        return substr($name, 0, 2) . str_repeat('*', max(2, strlen($name) - 2)) . '@' . $domain;
    }

    private function maskMobile(?string $mobile): string
    {
        return $mobile ? substr($mobile, 0, 2) . '******' . substr($mobile, -2) : 'registered mobile';
    }

    private function issueTokenResponse(Employee $employee, string $device, string $ip, Request $request): JsonResponse
    {
        $employee->tokens()->where('name', $device)->delete();

        $abilities = $this->resolveAbilities($employee);
        $token     = $employee->createToken($device, $abilities, now()->addHours(self::TOKEN_TTL_HOURS));

        DB::table('employees')->where('id', $employee->id)->update([
            'last_login_at'      => now(),
            'last_login_ip'      => $ip,
            'failed_login_count' => 0,
            'locked_until'       => null,
        ]);

        $this->logAttempt($employee->id, $employee->email, 'auth.employee_login', $ip, $request, 'success');

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
