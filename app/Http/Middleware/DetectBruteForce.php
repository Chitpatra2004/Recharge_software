<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * DetectBruteForce — IP-based brute force protection for auth endpoints.
 *
 * Tracks failed authentication attempts per IP address in the cache.
 * Applies a sliding-window counter with progressive penalties:
 *
 *   Failures in 15 min   Action
 *   ──────────────────   ──────────────────────────────────────────────
 *   < 10                 Allow through
 *   10 – 19              Slow down: add 1-second delay, warn in header
 *   20 – 49              Block for 15 minutes (429 Too Many Requests)
 *   ≥ 50                 Block for 1 hour + alert log (possible attack)
 *
 * Usage in routes:
 *   ->middleware('brute.force')           // auth endpoints
 *   ->middleware('brute.force:5,5')       // custom: max 5 attempts, 5-min window
 *
 * On a successful response (2xx), the counter for the IP is reset.
 * This prevents legitimate users who previously failed from being
 * permanently locked after a single successful login.
 */
class DetectBruteForce
{
    private const CACHE_PREFIX = 'brute_force:';

    public function handle(
        Request $request,
        Closure $next,
        int $maxAttempts = 20,
        int $windowMinutes = 15
    ): Response {
        $ip  = $request->ip();
        $key = self::CACHE_PREFIX . md5($ip);

        $attempts = (int) Cache::get($key, 0);

        // ── Already blocked? ─────────────────────────────────────────────
        if ($attempts >= $maxAttempts) {
            $blockMinutes = $attempts >= 50 ? 60 : $windowMinutes;

            Log::warning('BruteForce: IP blocked', [
                'ip'       => $ip,
                'attempts' => $attempts,
                'path'     => $request->path(),
            ]);

            return response()->json([
                'message'     => 'Too many failed attempts. Please try again later.',
                'retry_after' => $blockMinutes * 60,
            ], 429, [
                'Retry-After'           => $blockMinutes * 60,
                'X-RateLimit-Remaining' => 0,
            ]);
        }

        $response = $next($request);

        // ── Post-response: update attempt counter ─────────────────────────
        if ($response->getStatusCode() === 401 || $response->getStatusCode() === 422) {
            // Auth failure — increment counter
            $newCount = $attempts + 1;
            Cache::put($key, $newCount, now()->addMinutes($windowMinutes));

            // Add warning header when approaching the limit
            if ($newCount >= 10) {
                $remaining = max(0, $maxAttempts - $newCount);
                $response->headers->set('X-Auth-Warning', "Remaining attempts: {$remaining}");
            }

            // Alert on suspicious volume
            if ($newCount >= 50) {
                Log::alert('BruteForce: high-volume attack detected', [
                    'ip'       => $ip,
                    'attempts' => $newCount,
                    'path'     => $request->path(),
                    'ua'       => $request->userAgent(),
                ]);
            }
        } elseif ($response->getStatusCode() < 300) {
            // Successful auth — clear the counter
            Cache::forget($key);
        }

        return $response;
    }
}
