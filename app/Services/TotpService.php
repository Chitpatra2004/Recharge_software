<?php

namespace App\Services;

/**
 * RFC 6238 TOTP implementation — no external packages required.
 * Compatible with Google Authenticator, Microsoft Authenticator, Authy.
 */
class TotpService
{
    private const DIGITS    = 6;
    private const STEP      = 30;   // seconds per time step
    private const WINDOW    = 1;    // allow ±1 step for clock skew
    private const ALGORITHM = 'sha1';

    /** Generate a Base32-encoded secret key (20 bytes = 160 bits) */
    public function generateSecret(): string
    {
        $bytes = random_bytes(20);
        return $this->base32Encode($bytes);
    }

    /** Verify a 6-digit TOTP code against the secret */
    public function verify(string $secret, string $code): bool
    {
        $code = preg_replace('/\s+/', '', $code);
        if (strlen($code) !== self::DIGITS || !ctype_digit($code)) {
            return false;
        }

        $key       = $this->base32Decode($secret);
        $timestamp = floor(time() / self::STEP);

        for ($i = -self::WINDOW; $i <= self::WINDOW; $i++) {
            if ($this->generateCode($key, $timestamp + $i) === $code) {
                return true;
            }
        }

        return false;
    }

    /** Get the current TOTP code (useful for testing) */
    public function currentCode(string $secret): string
    {
        $key = $this->base32Decode($secret);
        return $this->generateCode($key, (int) floor(time() / self::STEP));
    }

    /**
     * Build the otpauth:// URI for QR code generation.
     * Use with a QR code library or https://quickchart.io/qr?text=...
     */
    public function getQrCodeUri(string $secret, string $label, string $issuer = ''): string
    {
        if (!$issuer) {
            $issuer = config('app.name', 'RechargeHub');
        }
        $label = rawurlencode($issuer . ':' . $label);
        return "otpauth://totp/{$label}?secret={$secret}&issuer=" . rawurlencode($issuer)
             . '&algorithm=SHA1&digits=6&period=30';
    }

    /** Generate a QR code image URL using QuickChart (no server-side lib needed) */
    public function getQrImageUrl(string $secret, string $label, string $issuer = ''): string
    {
        $uri = $this->getQrCodeUri($secret, $label, $issuer);
        return 'https://quickchart.io/qr?size=200&text=' . rawurlencode($uri);
    }

    // ── Private helpers ────────────────────────────────────────────────────

    private function generateCode(string $key, int $counter): string
    {
        // Pack counter as 8-byte big-endian unsigned integer
        $msg  = pack('N*', 0) . pack('N*', $counter);
        $hash = hash_hmac(self::ALGORITHM, $msg, $key, true);

        // Dynamic truncation
        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
        $code   = (
            ((ord($hash[$offset])     & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) <<  8) |
            ((ord($hash[$offset + 3]) & 0xFF))
        );

        return str_pad((string) ($code % (10 ** self::DIGITS)), self::DIGITS, '0', STR_PAD_LEFT);
    }

    private function base32Encode(string $bytes): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $binary   = '';
        foreach (str_split($bytes) as $byte) {
            $binary .= str_pad(decbin(ord($byte)), 8, '0', STR_PAD_LEFT);
        }
        $output = '';
        foreach (str_split($binary, 5) as $chunk) {
            $output .= $alphabet[bindec(str_pad($chunk, 5, '0', STR_PAD_RIGHT))];
        }
        return $output;
    }

    private function base32Decode(string $base32): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $base32   = strtoupper(preg_replace('/\s+/', '', $base32));
        $binary   = '';
        foreach (str_split($base32) as $char) {
            $pos     = strpos($alphabet, $char);
            $binary .= str_pad(decbin((int)$pos), 5, '0', STR_PAD_LEFT);
        }
        $output = '';
        foreach (str_split($binary, 8) as $chunk) {
            if (strlen($chunk) === 8) {
                $output .= chr(bindec($chunk));
            }
        }
        return $output;
    }
}
