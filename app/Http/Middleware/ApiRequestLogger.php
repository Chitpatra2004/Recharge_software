<?php

namespace App\Http\Middleware;

use App\Services\ApiRequestLogSchema;
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
    private const SENSITIVE_KEYS = [
        'password', 'password_confirmation', 'token', 'api_key', 'key',
        'secret', 'totp_secret', 'otp', 'pending_token', 'authorization',
    ];

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

        $data = [
            'api_key_id'       => $apiKey?->id,
            'user_id'          => $user?->id,
            'method'           => $request->method(),
            'path'             => '/' . ltrim($request->path(), '/'),
            'query_string'     => $this->buildQueryString($request),
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
        ];

        if (ApiRequestLogSchema::has('reference_id')) {
            $data['reference_id'] = $this->extractReferenceId($request);
        }

        if (ApiRequestLogSchema::has('request_payload')) {
            $data['request_payload'] = $this->buildRequestPayload($request);
        }

        DB::table('api_request_logs')->insert($data);

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

    private function extractReferenceId(Request $request): ?string
    {
        $candidates = [
            $request->route('id'),
            $request->route('txnId'),
            $request->input('transaction_id'),
            $request->input('txn_id'),
            $request->input('reference_id'),
            $request->input('reference'),
            $request->input('ref_id'),
            $request->input('idempotency_key'),
            $request->query('transaction_id'),
            $request->query('txn_id'),
            $request->query('reference_id'),
            $request->query('ref_id'),
            $request->query('idempotency_key'),
        ];

        foreach ($candidates as $value) {
            if ($value === null || $value === '') {
                continue;
            }

            return substr((string) $value, 0, 191);
        }

        return null;
    }

    private function buildRequestPayload(Request $request): ?string
    {
        $payload = $request->except([
            'document', 'pan_image', 'gst_certificate',
        ]);

        if (empty($payload)) {
            return null;
        }

        $masked = $this->maskSensitiveValues($payload);
        $json = json_encode($masked, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if (! $json) {
            return null;
        }

        return substr($json, 0, 5000);
    }

    private function buildQueryString(Request $request): ?string
    {
        $query = $request->query->all();

        if ($query === []) {
            return null;
        }

        return substr(http_build_query($this->maskSensitiveValues($query)), 0, 1000) ?: null;
    }

    private function maskSensitiveValues(array $payload): array
    {
        $masked = [];

        foreach ($payload as $key => $value) {
            $normalizedKey = strtolower((string) $key);

            if (in_array($normalizedKey, self::SENSITIVE_KEYS, true)) {
                $masked[$key] = '***';
                continue;
            }

            if (is_array($value)) {
                $masked[$key] = $this->maskSensitiveValues($value);
                continue;
            }

            if (is_string($value) && strlen($value) > 500) {
                $masked[$key] = substr($value, 0, 500) . '...';
                continue;
            }

            $masked[$key] = $value;
        }

        return $masked;
    }
}
