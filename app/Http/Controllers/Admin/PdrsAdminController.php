<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OperatorRoute;
use App\Models\RechargeTransaction;
use App\Services\PdrsApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PdrsAdminController extends Controller
{
    public function __construct(private readonly PdrsApiService $pdrs) {}

    // ── GET /api/v1/employee/pdrs/{route}/balance ─────────────────────────────
    public function balance(OperatorRoute $route): JsonResponse
    {
        $result = $this->pdrs->balance($route);

        if (! $result['success']) {
            return response()->json([
                'message' => 'Balance check failed.',
                'error'   => $result['error'] ?? 'Unknown error',
            ], 502);
        }

        // Cache the balance in api_config so the list page can display it
        $cfg = $route->api_config ?? [];
        $cfg['balance'] = $result['balance'];
        $route->update(['api_config' => $cfg]);

        return response()->json([
            'balance'    => $result['balance'],
            'route_id'   => $route->id,
            'route_name' => $route->name,
        ]);
    }

    // ── GET /api/v1/employee/pdrs/{route}/check-status?order_id={} ───────────
    public function checkStatus(Request $request, OperatorRoute $route): JsonResponse
    {
        $request->validate(['order_id' => ['required', 'string', 'max:50']]);

        $result = $this->pdrs->checkStatus($route, $request->order_id);

        if (! $result['success']) {
            return response()->json([
                'message' => 'Status check failed.',
                'error'   => $result['error'] ?? 'Unknown error',
            ], 502);
        }

        return response()->json($result);
    }

    // ── POST /api/v1/employee/pdrs/{route}/complain ───────────────────────────
    public function raiseComplaint(Request $request, OperatorRoute $route): JsonResponse
    {
        $request->validate([
            'order_id' => ['required', 'string', 'max:50'],
            'message'  => ['nullable', 'string', 'max:500'],
        ]);

        $result = $this->pdrs->raiseComplaint($route, $request->order_id, $request->input('message', 'complain'));

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'] ?? ($result['success'] ? 'Complaint submitted.' : 'Failed.'),
        ], $result['success'] ? 200 : 502);
    }
}
