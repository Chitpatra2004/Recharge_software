<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * ApiRequestLogger — records every API request to api_request_logs.
 *
 * Runs AFTER the response is built so latency is not added to the
 * user-facing response time. Uses DB::table() directly to avoid
 * Eloquent overhead on the hot path.
 *
 * Attach to any route group:
 *   ->middleware(ApiRequestLogger::class)
 */
class ApiRequestLogger
{
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        // Fire-and-forget: do not let logging errors bubble up
        try {
            $this->log($request, $response, $startTime);
        } catch (\Throwable) {
            // Intentionally swallowed — logging must never break the API
        }

        return $response;
    }

    private function log(Request $request, Response $response, float $startTime): void
    {
        $apiKey = $request->attributes->get('resolved_api_key');
        $user   = $request->user();

        DB::table('api_request_logs')->insert([
            'api_key_id'       => $apiKey?->id,
            'user_id'          => $user?->id,
            'method'           => $request->method(),
            'path'             => '/' . ltrim($request->path(), '/'),
            'query_string'     => $request->getQueryString() ?: null,
            'status_code'      => $response->getStatusCode(),
            'response_time_ms' => (int) ((microtime(true) - $startTime) * 1000),
            'ip_address'       => $request->ip(),
            'user_agent'       => substr((string) $request->userAgent(), 0, 500),
            'request_size'     => (int) $request->header('Content-Length', 0),
            'response_size'    => strlen($response->getContent()),
            'error_message'    => $response->getStatusCode() >= 400
                                    ? $this->extractError($response)
                                    : null,
            'created_at'       => now(),
        ]);

        // Keep api_keys.last_used_at and request_count up-to-date
        if ($apiKey) {
            DB::table('api_keys')
                ->where('id', $apiKey->id)
                ->update([
                    'last_used_at' => now(),
                    'last_used_ip' => $request->ip(),
                    'request_count' => DB::raw('request_count + 1'),
                ]);
        }
    }

    private function extractError(Response $response): ?string
    {
        try {
            $data = json_decode($response->getContent(), true);
            return isset($data['message'])
                ? substr($data['message'], 0, 500)
                : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
