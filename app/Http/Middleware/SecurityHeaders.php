<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SecurityHeaders — applies hardened HTTP security headers to every response.
 *
 * Headers applied:
 *
 *   X-Content-Type-Options: nosniff
 *     Prevents MIME-type sniffing attacks where the browser tries to
 *     "guess" the content type of a response and executes it as script.
 *
 *   X-Frame-Options: DENY
 *     Prevents the page from being embedded in <iframe> on any origin.
 *     Blocks clickjacking attacks.
 *
 *   X-XSS-Protection: 1; mode=block
 *     Legacy header — tells old IE/Chrome versions to block reflected XSS.
 *     Superseded by CSP but kept for backward compatibility.
 *
 *   Strict-Transport-Security (HSTS)
 *     Forces HTTPS for 1 year. Once a browser receives this, it will
 *     refuse to connect over plain HTTP until the max-age expires.
 *     Only set on HTTPS connections to avoid breaking local dev.
 *
 *   Content-Security-Policy
 *     Restricts the sources from which scripts, styles, images, and
 *     fonts can be loaded. default-src 'self' means nothing external.
 *
 *   Permissions-Policy
 *     Disables browser APIs this app does not use (camera, mic, etc.)
 *     Prevents malicious scripts from silently accessing hardware.
 *
 *   Referrer-Policy: strict-origin-when-cross-origin
 *     Only sends the origin (not the full URL) in the Referer header
 *     when navigating to a different origin. Protects query parameters
 *     (which might contain tokens or search terms) from leaking.
 *
 *   Cache-Control: no-store
 *     Prevents API responses from being stored in browser or proxy caches.
 *     Sensitive financial data must never be cached.
 *
 * Removed headers:
 *   X-Powered-By  — leaks PHP version
 *   Server        — leaks web server type and version
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // ── Clickjacking ──────────────────────────────────────────────────
        $response->headers->set('X-Frame-Options', 'DENY');

        // ── MIME sniffing ─────────────────────────────────────────────────
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // ── Legacy XSS filter ─────────────────────────────────────────────
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // ── Referrer leakage ──────────────────────────────────────────────
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // ── Hardware API restrictions ─────────────────────────────────────
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), payment=(), usb=(), fullscreen=(self)'
        );

        // ── Content Security Policy ───────────────────────────────────────
        // Web (Blade) pages need 'unsafe-inline' for inline <script> blocks.
        // API responses get a stricter policy since they never serve scripts.
        $isApi = str_starts_with($request->path(), 'api/');

        $scriptSrc = $isApi ? "'self'" : "'self' 'unsafe-inline'";
        $fontSrc   = $isApi ? "'self'" : "'self' https://fonts.gstatic.com";
        $styleSrc  = $isApi ? "'self' 'unsafe-inline'" : "'self' 'unsafe-inline' https://fonts.googleapis.com";

        $csp = implode('; ', [
            "default-src 'self'",
            "script-src {$scriptSrc}",
            "style-src {$styleSrc}",
            "img-src 'self' data:",
            "font-src {$fontSrc}",
            "connect-src 'self'",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "object-src 'none'",
        ]);
        $response->headers->set('Content-Security-Policy', $csp);

        // ── HSTS — HTTPS only, 1 year ─────────────────────────────────────
        // Only set on HTTPS to avoid blocking HTTP in local development
        if ($request->isSecure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        // ── Prevent caching of sensitive API responses ────────────────────
        if (str_starts_with($request->path(), 'api/')) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
        }

        // ── Remove server fingerprint headers ─────────────────────────────
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}
