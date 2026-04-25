<?php

namespace App\Services;

use App\Models\OperatorRoute;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * PDRS (pdrs.online) API — Balance check, Status check, Complaint APIs.
 * Recharge calls go through RechargeService; this handles admin/utility calls.
 *
 * Docs:
 *   Balance : GET /API2/Balance?username={}&token={}
 *   Status  : GET /API2/status?userid={}&token={}&order_id={}
 *   Complain: GET /API2/complain_api?username={}&token={}&order_id={}&Message=complain
 */
class PdrsApiService
{
    private const BASE_URL = 'https://pdrs.online/API2';
    private const TIMEOUT  = 15;

    private function creds(OperatorRoute $route): array
    {
        $cfg = $route->api_config ?? [];
        return [
            'username' => $cfg['username']  ?? '',
            'token'    => $cfg['api_token'] ?? '',
        ];
    }

    // ── Balance check ─────────────────────────────────────────────────────────
    // Response: {"balance":3858}
    public function balance(OperatorRoute $route): array
    {
        $c = $this->creds($route);

        try {
            $response = Http::timeout(self::TIMEOUT)
                ->get(self::BASE_URL . '/Balance', [
                    'username' => $c['username'],
                    'token'    => $c['token'],
                ]);

            $data = $response->json() ?? [];

            return [
                'success' => $response->successful() && isset($data['balance']),
                'balance' => $data['balance'] ?? null,
                'raw'     => $data,
            ];
        } catch (\Throwable $e) {
            Log::error('PDRS balance check failed', ['route_id' => $route->id, 'error' => $e->getMessage()]);
            return ['success' => false, 'balance' => null, 'error' => $e->getMessage()];
        }
    }

    // ── Transaction status check ──────────────────────────────────────────────
    // Response: {"status":"Success","tid":34903,"order_id":"01123",...}
    public function checkStatus(OperatorRoute $route, string $orderId): array
    {
        $c = $this->creds($route);

        try {
            $response = Http::timeout(self::TIMEOUT)
                ->get(self::BASE_URL . '/status', [
                    'userid'   => $c['username'],
                    'token'    => $c['token'],
                    'order_id' => $orderId,
                ]);

            $data = $response->json() ?? [];

            return [
                'success' => $response->successful(),
                'status'  => $data['status']       ?? null,
                'tid'     => $data['tid']           ?? null,
                'order_id'=> $data['order_id']      ?? null,
                'mobile'  => $data['mobile']        ?? null,
                'amount'  => $data['amount']        ?? null,
                'operator_id' => $data['operator_id'] ?? null,
                'raw'     => $data,
            ];
        } catch (\Throwable $e) {
            Log::error('PDRS status check failed', ['route_id' => $route->id, 'order_id' => $orderId, 'error' => $e->getMessage()]);
            return ['success' => false, 'status' => null, 'error' => $e->getMessage()];
        }
    }

    // ── Complaint submission ──────────────────────────────────────────────────
    // Response: {"status":0,"message":"Your Complain Submit Successfully"}
    public function raiseComplaint(OperatorRoute $route, string $orderId, string $message = 'complain'): array
    {
        $c = $this->creds($route);

        try {
            $response = Http::timeout(self::TIMEOUT)
                ->get(self::BASE_URL . '/complain_api', [
                    'username' => $c['username'],
                    'token'    => $c['token'],
                    'order_id' => $orderId,
                    'Message'  => $message,
                ]);

            $data = $response->json() ?? [];

            return [
                'success' => $response->successful() && ($data['status'] ?? 1) === 0,
                'message' => $data['message'] ?? null,
                'raw'     => $data,
            ];
        } catch (\Throwable $e) {
            Log::error('PDRS complaint failed', ['route_id' => $route->id, 'order_id' => $orderId, 'error' => $e->getMessage()]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
