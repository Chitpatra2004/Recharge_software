<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Services\OtpService;
use App\Services\TotpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class EmployeeProfileController extends Controller
{
    public function __construct(
        private readonly OtpService  $otpService,
        private readonly TotpService $totpService,
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // GET /api/v1/employee/profile
    // ─────────────────────────────────────────────────────────────────────────
    public function show(Request $request): JsonResponse
    {
        $emp = $request->user('employee');

        return response()->json([
            'employee' => [
                'id'                 => $emp->id,
                'name'               => $emp->name,
                'email'              => $emp->email,
                'mobile'             => $emp->mobile,
                'role'               => $emp->role,
                'department'         => $emp->department,
                'designation'        => $emp->designation,
                'status'             => $emp->status,
                'dob'                => $emp->dob,
                'city'               => $emp->city,
                'state'              => $emp->state,
                'pan'                => $emp->pan,
                'two_factor_enabled' => (bool) $emp->two_factor_enabled,
                'permissions'        => array_keys(array_filter($emp->permissions ?? [])),
                'last_login'         => $emp->last_login_at?->toIso8601String(),
                'created_at'         => $emp->created_at?->toIso8601String(),
                'preferences'        => $emp->preferences ?? [],
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PUT /api/v1/employee/profile
    // ─────────────────────────────────────────────────────────────────────────
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'name'  => ['sometimes', 'string', 'max:100'],
            'dob'   => ['sometimes', 'nullable', 'date', 'before:today'],
            'city'  => ['sometimes', 'nullable', 'string', 'max:100'],
            'state' => ['sometimes', 'nullable', 'string', 'max:100'],
            'pan'   => ['sometimes', 'nullable', 'string', 'max:10', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/'],
        ]);

        $emp = $request->user('employee');
        $emp->update($request->only(['name', 'dob', 'city', 'state', 'pan']));

        $this->auditLog($emp->id, 'profile.updated', 'Employee updated their profile.', $request);

        return response()->json(['message' => 'Profile updated successfully.']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PUT /api/v1/employee/auth/password
    // ─────────────────────────────────────────────────────────────────────────
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password'      => ['required', 'string'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string'],
        ]);

        $emp = $request->user('employee');

        if (! Hash::check($request->input('current_password'), $emp->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 422);
        }

        $emp->update(['password' => $request->input('password')]);

        // Revoke all other active tokens to force re-login on other devices
        $currentId = $emp->currentAccessToken()->id;
        $emp->tokens()->where('id', '!=', $currentId)->delete();

        $this->auditLog($emp->id, 'profile.password_changed', 'Employee changed their password.', $request);

        return response()->json(['message' => 'Password changed successfully. Other sessions have been logged out.']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/v1/employee/2fa/send-otp
    // ─────────────────────────────────────────────────────────────────────────
    public function sendTfaOtp(Request $request): JsonResponse
    {
        $request->validate([
            'mobile' => ['required', 'digits:10'],
        ]);

        $mobile = $request->input('mobile');

        $this->otpService->generate($mobile, 'register_verify', null, $request->ip());

        return response()->json(['message' => 'OTP sent to ' . $mobile . '.']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/v1/employee/2fa/verify
    // ─────────────────────────────────────────────────────────────────────────
    public function verifyTfaOtp(Request $request): JsonResponse
    {
        $request->validate([
            'mobile' => ['required', 'digits:10'],
            'otp'    => ['required', 'digits:6'],
        ]);

        $mobile   = $request->input('mobile');
        $otpInput = $request->input('otp');

        $verified = $this->otpService->verify($mobile, 'register_verify', $otpInput);
        if (! $verified) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 422);
        }

        $emp = $request->user('employee');

        $backupCodes = collect(range(1, 8))
            ->map(fn () => strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4)))
            ->toArray();

        $emp->update([
            'two_factor_enabled' => true,
            'mobile'             => $mobile,
            'backup_codes'       => $backupCodes,
        ]);

        $this->auditLog($emp->id, 'profile.2fa_enabled', 'Employee enabled 2FA.', $request);

        return response()->json([
            'message'      => '2FA enabled successfully.',
            'backup_codes' => $backupCodes,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/v1/employee/2fa/setup-totp
    // Generate TOTP secret + QR code for employee to scan
    // ─────────────────────────────────────────────────────────────────────────
    public function setupTotp(Request $request): JsonResponse
    {
        $emp    = $request->user('employee');
        $secret = $this->totpService->generateSecret();
        $label  = $emp->email ?: $emp->mobile;
        $qrDataUri = $this->totpService->getQrImageDataUri($secret, $label);

        $emp->update(['totp_secret' => $secret]);

        return response()->json([
            'secret'  => $secret,
            'qr_url'  => $qrDataUri ?: $this->totpService->getQrImageUrl($secret, $label),
            'otp_uri' => $this->totpService->getQrCodeUri($secret, $label),
            'message' => 'Scan the QR code with your Authenticator app, then confirm with a code.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/v1/employee/2fa/enable-totp
    // Confirm TOTP setup by verifying first code
    // ─────────────────────────────────────────────────────────────────────────
    public function enableTotp(Request $request): JsonResponse
    {
        $request->validate(['code' => ['required', 'digits:6']]);

        $emp = $request->user('employee');

        if (! $emp->totp_secret) {
            return response()->json(['message' => 'Run TOTP setup first.'], 422);
        }

        if (! $this->totpService->verify($emp->totp_secret, $request->code)) {
            return response()->json(['message' => 'Invalid code. Make sure your device clock is correct.'], 422);
        }

        $emp->update([
            'totp_enabled'       => true,
            'two_factor_enabled' => true,
            'two_factor_method'  => 'totp',
        ]);

        $this->auditLog($emp->id, 'profile.totp_enabled', 'Employee enabled TOTP 2FA.', $request);

        return response()->json(['message' => 'Authenticator app 2FA enabled successfully.']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DELETE /api/v1/employee/2fa/disable
    // ─────────────────────────────────────────────────────────────────────────
    public function disableTfa(Request $request): JsonResponse
    {
        $emp = $request->user('employee');
        $emp->update([
            'two_factor_enabled' => false,
            'totp_enabled'       => false,
            'totp_secret'        => null,
            'two_factor_method'  => 'none',
            'backup_codes'       => null,
        ]);

        $this->auditLog($emp->id, 'profile.2fa_disabled', 'Employee disabled 2FA.', $request);

        return response()->json(['message' => '2FA disabled successfully.']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET /api/v1/employee/sessions
    // ─────────────────────────────────────────────────────────────────────────
    public function sessions(Request $request): JsonResponse
    {
        $emp       = $request->user('employee');
        $currentId = $emp->currentAccessToken()->id;

        $sessions = PersonalAccessToken::where('tokenable_type', Employee::class)
            ->where('tokenable_id', $emp->id)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->get()
            ->map(fn ($t) => [
                'id'          => $t->id,
                'device'      => $t->name,
                'browser'     => '—',
                'ip'          => '—',
                'last_active' => $t->last_used_at?->toIso8601String() ?? $t->created_at->toIso8601String(),
                'is_current'  => $t->id === $currentId,
            ]);

        return response()->json(['data' => $sessions]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DELETE /api/v1/employee/sessions/{id}
    // ─────────────────────────────────────────────────────────────────────────
    public function revokeSession(Request $request, int $id): JsonResponse
    {
        $emp = $request->user('employee');

        PersonalAccessToken::where('id', $id)
            ->where('tokenable_type', Employee::class)
            ->where('tokenable_id', $emp->id)
            ->delete();

        return response()->json(['message' => 'Session revoked.']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET /api/v1/employee/login-history
    // ─────────────────────────────────────────────────────────────────────────
    public function loginHistory(Request $request): JsonResponse
    {
        $emp   = $request->user('employee');
        $limit = min((int) $request->input('limit', 10), 50);

        $logs = DB::table('activity_logs')
            ->where('actor_type', 'employee')
            ->where('actor_id', $emp->id)
            ->whereIn('action', ['auth.employee_login', 'auth.employee_login_failed'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get(['ip_address', 'action', 'created_at'])
            ->map(fn ($l) => [
                'ip'         => $l->ip_address,
                'success'    => $l->action === 'auth.employee_login',
                'created_at' => $l->created_at,
            ]);

        return response()->json(['data' => $logs]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET /api/v1/employee/activity
    // ─────────────────────────────────────────────────────────────────────────
    public function activity(Request $request): JsonResponse
    {
        $emp   = $request->user('employee');
        $limit = min((int) $request->input('limit', 10), 50);

        $logs = DB::table('activity_logs')
            ->where('actor_type', 'employee')
            ->where('actor_id', $emp->id)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get(['action', 'description', 'created_at'])
            ->map(fn ($l) => [
                'type'        => explode('.', $l->action)[0] ?? 'info',
                'action'      => $l->action,
                'description' => $l->description,
                'created_at'  => $l->created_at,
            ]);

        return response()->json(['data' => $logs]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PUT /api/v1/employee/preferences
    // ─────────────────────────────────────────────────────────────────────────
    public function savePreferences(Request $request): JsonResponse
    {
        $request->validate([
            'notifications'          => ['sometimes', 'array'],
            'notifications.email'    => ['sometimes', 'boolean'],
            'notifications.sms'      => ['sometimes', 'boolean'],
            'notifications.browser'  => ['sometimes', 'boolean'],
            'notifications.recharge' => ['sometimes', 'boolean'],
            'language'               => ['sometimes', 'string', 'in:en,hi,gu,mr,ta'],
            'timezone'               => ['sometimes', 'string', 'max:60'],
        ]);

        $emp = $request->user('employee');
        $emp->update(['preferences' => $request->only(['notifications', 'language', 'timezone'])]);

        return response()->json(['message' => 'Preferences saved.']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private: write an audit entry using the same schema as EmployeeAuthController
    // ─────────────────────────────────────────────────────────────────────────
    private function auditLog(int $employeeId, string $action, string $description, Request $request): void
    {
        try {
            DB::table('activity_logs')->insert([
                'actor_type'  => 'employee',
                'actor_id'    => $employeeId,
                'action'      => $action,
                'description' => $description,
                'properties'  => null,
                'ip_address'  => $request->ip(),
                'user_agent'  => substr((string) $request->userAgent(), 0, 500),
                'url'         => $request->fullUrl(),
                'method'      => $request->method(),
                'created_at'  => now(),
            ]);
        } catch (\Throwable) {
            // Audit logging must never break the main flow
        }
    }
}
