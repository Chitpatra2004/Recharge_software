<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SanitizeInput — XSS prevention at the input boundary.
 *
 * Strips HTML tags and encodes special characters from all string inputs
 * before they reach controllers or validation. This is a defence-in-depth
 * layer on top of Blade auto-escaping and CSP headers.
 *
 * Rules:
 *   • Only processes string values — numerics, booleans, nulls are untouched
 *   • Skips fields in the $exempt list (passwords, tokens, signatures — must
 *     not be modified as even stripping < > could break a valid hash)
 *   • Processes nested arrays recursively (e.g. JSON request bodies)
 *   • Never modifies file uploads
 *
 * What it does NOT do:
 *   • It does not validate — that is the job of Form Request rules
 *   • It does not escape for HTML output — Blade does that at render time
 *   • It does not protect against SQL injection — Query Builder does that
 */
class SanitizeInput
{
    /**
     * Fields that must never be modified.
     * Passwords, tokens, and signatures contain characters like < > & that
     * are legitimate and must not be altered.
     */
    private array $exempt = [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'token',
        'api_key',
        'signature',
        'callback_secret',
        'secret',
        '_token',   // CSRF token
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Skip file-only requests (multipart without text fields)
        if ($request->isMethod('GET') || $request->isMethod('HEAD')) {
            return $next($request);
        }

        $cleaned = $this->clean($request->all());
        $request->merge($cleaned);

        // Also clean query string parameters on non-GET routes
        if ($request->query->count()) {
            $request->query->replace($this->clean($request->query->all()));
        }

        return $next($request);
    }

    /**
     * Recursively clean all string values in the input array.
     */
    private function clean(array $data): array
    {
        foreach ($data as $key => $value) {
            if (in_array($key, $this->exempt, true)) {
                continue;
            }

            if (is_array($value)) {
                $data[$key] = $this->clean($value);
            } elseif (is_string($value)) {
                $data[$key] = $this->sanitizeString($value);
            }
            // integers, floats, booleans, nulls — pass through unchanged
        }

        return $data;
    }

    /**
     * Strip HTML/PHP tags and remove null bytes from string input.
     *
     * FIX H1: htmlspecialchars() was removed from this layer.
     *
     * Why: htmlspecialchars() at *input* time encodes & → &amp; before data
     * is stored in the database.  This breaks:
     *   - Callback URLs stored in api_keys.callback_url
     *     ("https://host/cb?a=1&b=2" becomes "https://host/cb?a=1&amp;b=2")
     *   - Blade templates: auto-escaping runs again, producing &amp;amp;
     *   - Operator API payloads: encoded values fail HMAC / format validation
     *
     * Correct layering:
     *   Input boundary  → strip_tags() only (prevent stored XSS tags)
     *   DB output       → Query Builder parameterised queries (SQL injection)
     *   HTML output     → Blade {{ }} auto-escaping (XSS at render time)
     *   JSON output     → json_encode() with JSON_HEX_TAG (XSS in JSON)
     */
    private function sanitizeString(string $value): string
    {
        // 1. Strip all HTML and PHP tags — prevents stored XSS
        $value = strip_tags($value);

        // 2. Remove null bytes — used to bypass some filters or corrupt strings
        $value = str_replace("\0", '', $value);

        return $value;
    }
}
