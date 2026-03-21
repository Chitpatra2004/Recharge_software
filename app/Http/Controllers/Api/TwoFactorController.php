<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\ApiAuthService;
use App\Services\OtpService;
use App\Services\TotpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TwoFactorController extends Controller
{
    public function __construct(
        private readonly OtpService     $otpService,
        private readonly TotpService    $totpService,
        private readonly ApiAuthService $authService,
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/v1/auth/2fa/verify-otp
    // Verify SMS OTP during login 2FA step
    // ─────────────────────────────────────────────────────────────────────────
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'pending_token' => ['required', 'string'],
            'otp'           => ['required', 'digits:6'],
        ]);

        // Find the pending login session
        $otpRecord = $this->otpService->findPendingToken($request->pending_token);
        if (! $otpRecord) {
            return response()->json(['message' => 'Invalid or expired session. Please login again.'], 401);
        }

        $user = User::findOrFail($otpRecord->user_id);

        // Verify OTP
        $verified = $this->otpService->verify($user->mobile, 'login_2fa', $request->otp);
        if (! $verified) {
            return response()->json(['message' => 'Invalid OTP. Please try again.'], 422);
        }

        $token = $this->authService->issueToken($user, $request->input('device_name', 'web'));
        ActivityLogger::log('auth.2fa_success', '2FA verified (OTP).', $user, [], $user->id, $request);

        return response()->json([
            'token' => $token,
            'user'  => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'role' => $user->role],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/v1/auth/2fa/verify-totp
    // Verify Google/Microsoft Authenticator code during login
    // ─────────────────────────────────────────────────────────────────────────
    public function verifyTotp(Request $request): JsonResponse
    {
        $request->validate([
            'pending_token' => ['required', 'string'],
            'code'          => ['required', 'digits:6'],
        ]);

        $otpRecord = $this->otpService->findPendingToken($request->pending_token);
        if (! $otpRecord) {
            return response()->json(['message' => 'Invalid or expired session. Please login again.'], 401);
        }

        $user = User::findOrFail($otpRecord->user_id);

        if (! $user->totp_enabled || ! $user->totp_secret) {
            return response()->json(['message' => 'TOTP is not enabled on this account.'], 422);
        }

        if (! $this->totpService->verify($user->totp_secret, $request->code)) {
            return response()->json(['message' => 'Invalid code. Please try again.'], 422);
        }

        $otpRecord->markUsed();
        $token = $this->authService->issueToken($user, $request->input('device_name', 'web'));
        ActivityLogger::log('auth.2fa_success', '2FA verified (TOTP).', $user, [], $user->id, $request);

        return response()->json([
            'token' => $token,
            'user'  => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'role' => $user->role],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/v1/auth/2fa/setup-totp  (authenticated)
    // Generate TOTP secret and QR code for user to scan
    // ─────────────────────────────────────────────────────────────────────────
    public function setupTotp(Request $request): JsonResponse
    {
        $user   = $request->user();
        $secret = $this->totpService->generateSecret();
        $label  = $user->email ?: $user->mobile;

        // Temporarily store secret (user must confirm with a code before it's activated)
        $user->update(['totp_secret' => $secret]);

        return response()->json([
            'secret'   => $secret,
            'qr_url'   => $this->totpService->getQrImageUrl($secret, $label),
            'otp_uri'  => $this->totpService->getQrCodeUri($secret, $label),
            'message'  => 'Scan the QR code with your Authenticator app, then confirm with a code.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/v1/auth/2fa/enable-totp  (authenticated)
    // Confirm TOTP setup by verifying first code
    // ─────────────────────────────────────────────────────────────────────────
    public function enableTotp(Request $request): JsonResponse
    {
        $request->validate(['code' => ['required', 'digits:6']]);

        $user = $request->user();

        if (! $user->totp_secret) {
            return response()->json(['message' => 'Run TOTP setup first.'], 422);
        }

        if (! $this->totpService->verify($user->totp_secret, $request->code)) {
            return response()->json(['message' => 'Invalid code. Make sure your device clock is correct.'], 422);
        }

        $user->update([
            'totp_enabled'      => true,
            'two_factor_method' => 'totp',
        ]);

        ActivityLogger::log('auth.totp_enabled', 'TOTP 2FA enabled.', $user, [], $user->id, $request);

        return response()->json(['message' => 'Authenticator app enabled. 2FA is now active.']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/v1/auth/2fa/enable-otp  (authenticated)
    // Enable SMS OTP as 2FA method
    // ─────────────────────────────────────────────────────────────────────────
    public function enableOtp(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->update(['two_factor_method' => 'otp']);
        ActivityLogger::log('auth.otp_enabled', 'SMS OTP 2FA enabled.', $user, [], $user->id, $request);

        return response()->json(['message' => 'SMS OTP 2FA enabled.']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/v1/auth/2fa/disable  (authenticated)
    // ─────────────────────────────────────────────────────────────────────────
    public function disable(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->update([
            'two_factor_method' => 'none',
            'totp_enabled'      => false,
            'totp_secret'       => null,
        ]);
        ActivityLogger::log('auth.2fa_disabled', '2FA disabled.', $user, [], $user->id, $request);

        return response()->json(['message' => '2FA has been disabled.']);
    }
}
