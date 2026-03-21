<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ForgotPasswordController extends Controller
{
    public function __construct(private readonly OtpService $otpService) {}

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/v1/auth/forgot-password
    // Step 1: User enters email or mobile → send OTP
    // ─────────────────────────────────────────────────────────────────────────
    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'identifier' => ['required', 'string', 'max:255'],
        ]);

        $identifier = $request->identifier;
        $field      = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';
        $user       = User::where($field, $identifier)->first();

        // Always return success to prevent user enumeration
        if (! $user) {
            return response()->json([
                'message' => 'If an account with that email/mobile exists, an OTP has been sent.',
            ]);
        }

        // Use mobile for OTP delivery
        $result = $this->otpService->generate(
            $user->mobile,
            'reset_password',
            $user->id,
            $request->ip()
        );

        ActivityLogger::log('auth.forgot_password', "Password reset OTP sent for {$identifier}", $user, [], $user->id, $request);

        return response()->json([
            'message'    => 'OTP sent to your registered mobile number.',
            'identifier' => $user->mobile,   // confirm which mobile got the OTP
            // DEV ONLY
            'debug_otp'  => config('app.debug') ? $result['otp'] : null,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/v1/auth/forgot-password/verify-otp
    // Step 2: Verify OTP → return a short-lived reset token
    // ─────────────────────────────────────────────────────────────────────────
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'mobile' => ['required', 'digits:10'],
            'otp'    => ['required', 'digits:6'],
        ]);

        $otpRecord = $this->otpService->verify($request->mobile, 'reset_password', $request->otp);

        if (! $otpRecord) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 422);
        }

        // Issue a short-lived reset token (store new OTP record as reset_token marker)
        $resetToken = \Illuminate\Support\Str::random(64);
        \Illuminate\Support\Facades\Cache::put(
            'pwd_reset:' . $resetToken,
            ['user_id' => $otpRecord->user_id, 'mobile' => $request->mobile],
            now()->addMinutes(10)
        );

        return response()->json([
            'message'     => 'OTP verified. You may now reset your password.',
            'reset_token' => $resetToken,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/v1/auth/forgot-password/reset
    // Step 3: Set new password using reset_token
    // ─────────────────────────────────────────────────────────────────────────
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'reset_token' => ['required', 'string'],
            'password'    => [
                'required', 'string', 'confirmed', 'max:72',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
        ]);

        $payload = \Illuminate\Support\Facades\Cache::get('pwd_reset:' . $request->reset_token);

        if (! $payload) {
            return response()->json(['message' => 'Reset link has expired. Please start over.'], 422);
        }

        $user = User::findOrFail($payload['user_id']);
        $user->update(['password' => Hash::make($request->password)]);

        // Revoke all existing tokens for security
        $user->tokens()->delete();

        \Illuminate\Support\Facades\Cache::forget('pwd_reset:' . $request->reset_token);

        ActivityLogger::log('auth.password_reset', 'Password reset successful.', $user, [], $user->id, $request);

        return response()->json(['message' => 'Password reset successful. Please login with your new password.']);
    }
}
