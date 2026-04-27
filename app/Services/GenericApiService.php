<?php

namespace App\Services;

use App\Models\OperatorRoute;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Generic API caller — drives all API provider operations
 * using configuration stored in OperatorRoute.api_config.
 *
 * Sections read from api_config:
 *   credentials  : username, api_token
 *   balance_api  : method, url, params, balance_key
 *   status_api   : method, url, params, status_key, txnid_key
 *   complaint_api: method, url, params
 */
class GenericApiService
{
    private const TIMEOUT = 20;

    // ── Balance check ─────────────────────────────────────────────────────────
    public function balance(OperatorRoute $route): array
    {
        $cfg = $route->api_config ?? [];
        $ba  = $cfg['balance_api'] ?? [];

        if (empty($ba['url'])) {
            return ['success' => false, 'balance' => null, 'error' => 'Balance API not configured.'];
        }

        try {
            $params = $this->buildParams($ba['params'] ?? '', $cfg, []);
            $resp   = $this->call($ba['method'] ?? 'GET', $ba['url'], $params);
            $data   = $resp->json() ?? [];
            $key    = $ba['balance_key'] ?? 'balance';

            return [
                'success' => $resp->successful() && array_key_exists($key, $data),
                'balance' => $data[$key] ?? null,
                'raw'     => $data,
            ];
        } catch (\Throwable $e) {
            Log::error('GenericApiService::balance', ['route' => $route->id, 'error' => $e->getMessage()]);
            return ['success' => false, 'balance' => null, 'error' => $e->getMessage()];
        }
    }

    // ── Status check ──────────────────────────────────────────────────────────
    public function checkStatus(OperatorRoute $route, string $orderId): array
    {
        $cfg = $route->api_config ?? [];
        $sa  = $cfg['status_api'] ?? [];

        if (empty($sa['url'])) {
            return ['success' => false, 'status' => null, 'error' => 'Status API not configured.'];
        }

        try {
            $params = $this->buildParams($sa['params'] ?? '', $cfg, ['[order_id]' => $orderId, '[transid]' => $orderId]);
            $resp   = $this->call($sa['method'] ?? 'GET', $sa['url'], $params);
            $data   = $resp->json() ?? [];

            return [
                'success'    => $resp->successful(),
                'status'     => $data[$sa['status_key'] ?? 'status'] ?? null,
                'txnid'      => $data[$sa['txnid_key']  ?? 'tid']    ?? null,
                'order_id'   => $data['order_id']    ?? null,
                'mobile'     => $data['mobile']      ?? null,
                'amount'     => $data['amount']      ?? null,
                'operator_id'=> $data['operator_id'] ?? null,
                'raw'        => $data,
            ];
        } catch (\Throwable $e) {
            Log::error('GenericApiService::checkStatus', ['route' => $route->id, 'error' => $e->getMessage()]);
            return ['success' => false, 'status' => null, 'error' => $e->getMessage()];
        }
    }

    // ── Complaint ─────────────────────────────────────────────────────────────
    public function raiseComplaint(OperatorRoute $route, string $orderId, string $message = 'complain'): array
    {
        $cfg = $route->api_config ?? [];
        $ca  = $cfg['complaint_api'] ?? [];

        if (empty($ca['url'])) {
            return ['success' => false, 'message' => 'Complaint API not configured.'];
        }

        try {
            $params = $this->buildParams($ca['params'] ?? '', $cfg, [
                '[order_id]' => $orderId,
                '[transid]'  => $orderId,
                '[message]'  => $message,
                '[Message]'  => $message,
            ]);
            $resp = $this->call($ca['method'] ?? 'GET', $ca['url'], $params);
            $data = $resp->json() ?? [];

            return [
                'success' => $resp->successful() && ($data['status'] ?? 1) === 0,
                'message' => $data['message'] ?? null,
                'raw'     => $data,
            ];
        } catch (\Throwable $e) {
            Log::error('GenericApiService::raiseComplaint', ['route' => $route->id, 'error' => $e->getMessage()]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ── Internal helpers ──────────────────────────────────────────────────────

    private function buildParams(string $template, array $cfg, array $extra): array
    {
        $replacements = array_merge([
            '[username]'   => $cfg['username']  ?? '',
            '[apitoken]'   => $cfg['api_token'] ?? '',
            '[password]'   => $cfg['api_token'] ?? '',
            '[token]'      => $cfg['api_token'] ?? '',
        ], $extra);

        $str = str_replace(array_keys($replacements), array_values($replacements), $template);
        parse_str($str, $params);
        return $params;
    }

    private function call(string $method, string $url, array $params): \Illuminate\Http\Client\Response
    {
        $http = Http::timeout(self::TIMEOUT);
        return strtoupper($method) === 'GET'
            ? $http->get($url, $params)
            : $http->asForm()->post($url, $params);
    }
}
