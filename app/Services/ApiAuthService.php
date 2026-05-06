<?php

namespace App\Services;

use App\Models\ApiKey;
use App\Models\SellerIntegrationRequest;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class ApiAuthService
{
    /**
     * Issue a Sanctum token for the authenticated user.
     */
    public function issueToken(User $user, string $deviceName = 'api'): string
    {
        // Revoke all old tokens for this device name before issuing new one
        $user->tokens()->where('name', $deviceName)->delete();

        return $user->createToken($deviceName)->plainTextToken;
    }

    /**
     * Generate and persist a dedicated API key for machine-to-machine use.
     *
     * Keys are stored in the api_keys table (hashed with SHA-256).
     * The raw key is returned once and never stored in plain text.
     * Any existing key with the same name for this user is deactivated first.
     */
    public function generateApiKey(
        User   $user,
        string $name   = 'API Key',
        array  $scopes = ['recharge:write', 'recharge:read', 'wallet:read']
    ): string {
        $integration = null;
        if (in_array($user->role, ['api_user', 'retailer'], true)) {
            $integration = SellerIntegrationRequest::where('user_id', $user->id)
                ->where('status', 'approved')
                ->latest()
                ->first();

            if (! $integration || ! $this->hasRequiredSellerApiSetup($integration)) {
                throw ValidationException::withMessages([
                    'api_key' => ['Callback URL and IP whitelist are required before generating the seller API token.'],
                ]);
            }
        }

        $rawKey = 'rk_' . Str::random(60);

        // Deactivate any existing key with the same name so re-generation replaces it
        ApiKey::where('user_id', $user->id)
            ->where('name', $name)
            ->update(['is_active' => false]);

        ApiKey::create([
            'user_id'    => $user->id,
            'name'       => $name,
            'key_prefix' => substr($rawKey, 0, 12),
            'key_hash'   => hash('sha256', $rawKey),
            'scopes'     => $scopes,
            'ip_whitelist' => $this->parseAllowedIps($integration?->allowed_ips),
            'is_active'  => true,
        ]);

        return $rawKey;
    }

    /**
     * Validate a raw API key — looks up the api_keys table (not users.api_key).
     * Returns the owning User if the key is valid and active, or null.
     */
    public function validateApiKey(string $rawKey): ?User
    {
        $apiKey = ApiKey::with('user')
            ->where('key_hash', hash('sha256', $rawKey))
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->first();

        return $apiKey?->user?->status === 'active' ? $apiKey->user : null;
    }

    /**
     * Enforce IP whitelist for API users (if configured).
     */
    public function isIpAllowed(User $user, string $ip): bool
    {
        $allowed = $user->allowedIps();

        return empty($allowed) || in_array($ip, $allowed);
    }

    private function parseAllowedIps(?string $raw): ?array
    {
        if (! $raw) {
            return null;
        }

        $ips = preg_split('/[\r\n,]+/', $raw) ?: [];
        $ips = array_values(array_filter(array_map('trim', $ips)));

        return $ips === [] ? null : $ips;
    }

    private function hasRequiredSellerApiSetup(SellerIntegrationRequest $integration): bool
    {
        return filled($integration->callback_url) && ! empty($this->parseAllowedIps($integration->allowed_ips));
    }
}
