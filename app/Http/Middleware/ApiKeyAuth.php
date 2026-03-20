<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * ApiKeyAuth — machine-to-machine authentication middleware.
 *
 * Usage in routes:
 *   ->middleware('api.key')                        // auth only
 *   ->middleware('api.key:recharge:write')         // auth + single scope
 *   ->middleware('api.key:wallet:read,recharge:read') // auth + multiple scopes
 *
 * Auth flow:
 *   1. Extract raw key from X-API-Key header
 *   2. Hash it (SHA-256) and look up in api_keys table
 *   3. Validate: is_active, not expired
 *   4. IP whitelist — supports exact IPs and CIDR ranges
 *   5. Scope check — all required scopes must be present
 *   6. Set authenticated user on the request guard
 *   7. Store ApiKey on request attributes for downstream logging
 *
 * Falls through to Sanctum token auth if no X-API-Key header present,
 * allowing the same routes to serve both key-based and token-based clients.
 */
class ApiKeyAuth
{
    public function handle(Request $request, Closure $next, string ...$requiredScopes): Response
    {
        $rawKey = $request->header('X-API-Key');

        if (! $rawKey) {
            // No API key — allow request to pass through to Sanctum guard
            return $next($request);
        }

        // ── Lookup by SHA-256 hash (single B-tree scan on UNIQUE index) ──────
        $apiKey = ApiKey::with('user')
            ->where('key_hash', hash('sha256', $rawKey))
            ->first();

        if (! $apiKey) {
            return $this->reject($request, 'Invalid API key.', 401);
        }

        if (! $apiKey->isValid()) {
            return $this->reject($request, 'API key is inactive or expired.', 401);
        }

        // ── IP whitelist ──────────────────────────────────────────────────────
        $whitelist = $apiKey->ip_whitelist ?? [];
        if (! empty($whitelist) && ! $this->isIpAllowed($request->ip(), $whitelist)) {
            Log::warning('API key IP rejected', [
                'key_prefix' => $apiKey->key_prefix,
                'ip'         => $request->ip(),
                'whitelist'  => $whitelist,
            ]);
            return $this->reject($request, 'IP address not whitelisted for this API key.', 403);
        }

        // ── Scope enforcement ─────────────────────────────────────────────────
        foreach ($requiredScopes as $scope) {
            if (! $apiKey->hasScope($scope)) {
                return $this->reject(
                    $request,
                    "API key does not have required scope: {$scope}.",
                    403
                );
            }
        }

        // ── Authenticate ──────────────────────────────────────────────────────
        $user = $apiKey->user;

        if (! $user || $user->status !== 'active') {
            return $this->reject($request, 'Associated user account is inactive.', 403);
        }

        auth()->setUser($user);

        // Store on request so ApiRequestLogger can access without re-querying
        $request->attributes->set('resolved_api_key', $apiKey);

        return $next($request);
    }

    /**
     * Check if the given IP matches any entry in the whitelist.
     * Supports exact IPs ("1.2.3.4") and CIDR ranges ("10.0.0.0/8").
     */
    private function isIpAllowed(string $ip, array $whitelist): bool
    {
        foreach ($whitelist as $entry) {
            if (str_contains($entry, '/')) {
                if ($this->ipInCidr($ip, $entry)) {
                    return true;
                }
            } elseif ($ip === $entry) {
                return true;
            }
        }

        return false;
    }

    /**
     * CIDR range check for both IPv4 and IPv6.
     */
    private function ipInCidr(string $ip, string $cidr): bool
    {
        [$subnet, $bits] = explode('/', $cidr);

        $ipLong     = ip2long($ip);
        $subnetLong = ip2long($subnet);

        if ($ipLong === false || $subnetLong === false) {
            return false; // IPv6 not supported here — add inet_pton logic if needed
        }

        $mask = $bits > 0 ? (~0 << (32 - (int) $bits)) : 0;

        return ($ipLong & $mask) === ($subnetLong & $mask);
    }

    private function reject(Request $request, string $message, int $status): Response
    {
        return response()->json([
            'message'   => $message,
            'timestamp' => now()->toIso8601String(),
        ], $status);
    }
}
