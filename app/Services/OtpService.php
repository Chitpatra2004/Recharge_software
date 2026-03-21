<?php

namespace App\Services;

use App\Models\Otp;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OtpService
{
    /** OTP valid for 5 minutes */
    private const TTL_MINUTES = 5;

    /**
     * Generate, store and "send" an OTP.
     * Returns the raw 6-digit OTP (for simulation / logging).
     */
    public function generate(
        string $identifier,
        string $type,
        ?int   $userId = null,
        string $ip = ''
    ): array {
        // Invalidate any prior unused OTPs for same identifier+type
        Otp::where('identifier', $identifier)
            ->where('type', $type)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        $rawOtp       = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $pendingToken = ($type === 'login_2fa') ? Str::random(48) : null;

        $otp = Otp::create([
            'user_id'       => $userId,
            'identifier'    => $identifier,
            'otp_hash'      => Hash::make($rawOtp),
            'type'          => $type,
            'pending_token' => $pendingToken,
            'expires_at'    => now()->addMinutes(self::TTL_MINUTES),
            'ip_address'    => $ip ?: null,
        ]);

        // Simulate SMS — in production replace with real SMS gateway
        $this->sendSms($identifier, $rawOtp, $type);

        return [
            'otp'           => $rawOtp,          // ONLY for dev/simulation
            'pending_token' => $pendingToken,
            'expires_at'    => $otp->expires_at,
        ];
    }

    /**
     * Verify an OTP for a given identifier & type.
     */
    public function verify(string $identifier, string $type, string $rawOtp): Otp|false
    {
        $record = Otp::where('identifier', $identifier)
            ->where('type', $type)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (! $record) {
            return false;
        }

        if (! Hash::check($rawOtp, $record->otp_hash)) {
            return false;
        }

        $record->markUsed();
        return $record;
    }

    /**
     * Find a valid pending login token (used in 2FA step 2).
     */
    public function findPendingToken(string $token): ?Otp
    {
        return Otp::where('pending_token', $token)
            ->where('type', 'login_2fa')
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();
    }

    /** Simulate SMS sending — logs to Laravel log in development */
    private function sendSms(string $to, string $otp, string $type): void
    {
        $purpose = match ($type) {
            'login_2fa'        => 'login verification',
            'reset_password'   => 'password reset',
            'register_verify'  => 'email/mobile verification',
            default            => 'verification',
        };

        // In production: replace below with Twilio / MSG91 / Fast2SMS etc.
        Log::channel('daily')->info("[SMS SIMULATION] To: {$to} | OTP: {$otp} | Purpose: {$purpose} | Expires in 5 minutes");

        // Also log to default channel during development
        if (config('app.debug')) {
            Log::info("🔐 OTP [{$purpose}] → {$to} : {$otp}");
        }
    }
}
