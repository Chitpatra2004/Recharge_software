<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OperatorRoute;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OperatorApiSettingController extends Controller
{
    // ── GET /api/v1/employee/api-providers ─────────────────────────────────
    public function listRoutes(): JsonResponse
    {
        $routes = OperatorRoute::withTrashed(false)
            ->orderBy('id')
            ->get();

        $data = $routes->map(function (OperatorRoute $r) {
            $cfg = $r->api_config ?? [];
            return [
                'id'            => $r->id,
                'api_id'        => 'API ' . $r->id,
                'name'          => $r->name,
                'api_provider'  => $r->api_provider ?? '—',
                'is_active'     => $r->is_active,
                'api_status'    => (bool) ($cfg['api_status'] ?? false),
                'auto_renewal'  => (bool) ($cfg['auto_renewal'] ?? false),
                'validity_till' => $cfg['validity_till'] ?? '0000-00-00',
                'purchase'      => $cfg['purchase']      ?? 'active',
                'balance'       => $cfg['balance']       ?? '',
                'margin'        => $cfg['margin']        ?? 0,
            ];
        });

        return response()->json(['routes' => $data]);
    }

    // ── POST /api/v1/employee/api-providers ────────────────────────────────
    public function storeRoute(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'name'         => ['required', 'string', 'max:100'],
            'api_provider' => ['nullable', 'string', 'max:150'],
            'operator_code'=> ['required', 'string', 'max:20'],
            'recharge_type'=> ['required', 'in:prepaid,postpaid,dth,bbps,fastag,other'],
            'api_endpoint' => ['nullable', 'url', 'max:500'],
        ]);

        if ($v->fails()) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $v->errors()], 422);
        }

        $route = OperatorRoute::create([
            'name'          => $request->name,
            'api_provider'  => $request->api_provider ?? '',
            'operator_code' => $request->operator_code,
            'recharge_type' => $request->recharge_type,
            'api_endpoint'  => $request->api_endpoint ?? '',
            'is_active'     => false,
            'api_config'    => ['api_status' => false, 'auto_renewal' => false,
                                'validity_till' => '0000-00-00', 'purchase' => 'active', 'margin' => 0],
        ]);

        return response()->json(['message' => 'API provider added.', 'id' => $route->id], 201);
    }

    // ── PATCH /api/v1/employee/api-providers/{route}/toggle ────────────────
    // field = 'admin' (is_active) | 'api' (api_status) | 'renewal' (auto_renewal)
    public function toggle(Request $request, OperatorRoute $route): JsonResponse
    {
        $field  = $request->input('field', 'admin');
        $remark = $request->input('remark', '');

        if ($field === 'admin') {
            $route->update(['is_active' => ! $route->is_active]);
            $val = $route->is_active;
        } else {
            $cfg          = $route->api_config ?? [];
            $key          = $field === 'renewal' ? 'auto_renewal' : 'api_status';
            $cfg[$key]    = ! ($cfg[$key] ?? false);
            if ($field === 'api' && ! $cfg[$key] && $remark) {
                $cfg['disable_remark'] = $remark;
            }
            $route->update(['api_config' => $cfg]);
            $val = $cfg[$key];
        }

        return response()->json(['value' => $val]);
    }

    // ── PUT /api/v1/employee/api-providers/{route}/basic ───────────────────
    public function updateBasic(Request $request, OperatorRoute $route): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'name'          => ['required', 'string', 'max:100'],
            'api_provider'  => ['nullable', 'string', 'max:150'],
            'operator_code' => ['required', 'string', 'max:20'],
            'recharge_type' => ['required', 'in:prepaid,postpaid,dth,bbps,fastag,other'],
            'validity_till' => ['nullable', 'date'],
            'purchase'      => ['nullable', 'in:active,deactive'],
        ]);

        if ($v->fails()) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $v->errors()], 422);
        }

        $cfg = $route->api_config ?? [];
        $cfg['validity_till'] = $request->input('validity_till', $cfg['validity_till'] ?? '0000-00-00');
        $cfg['purchase']      = $request->input('purchase', $cfg['purchase'] ?? 'active');

        $route->update([
            'name'          => $request->name,
            'api_provider'  => $request->api_provider ?? $route->api_provider,
            'operator_code' => $request->operator_code,
            'recharge_type' => $request->recharge_type,
            'api_config'    => $cfg,
        ]);

        return response()->json(['message' => 'Updated successfully.']);
    }

    // ── DELETE /api/v1/employee/api-providers/{route} ──────────────────────
    public function destroy(OperatorRoute $route): JsonResponse
    {
        $route->delete();
        return response()->json(['message' => 'API provider deleted.']);
    }

    // ── GET /api/v1/employee/operator-routes/{route}/api-setting ───────────
    public function show(OperatorRoute $route): JsonResponse
    {
        $cfg = $route->api_config ?? [];

        return response()->json([
            'route' => [
                'id'           => $route->id,
                'name'         => $route->name,
                'operator_code'=> $route->operator_code,
                'recharge_type'=> $route->recharge_type,
                'api_provider' => $route->api_provider,
                'api_endpoint' => $route->api_endpoint,
                'is_active'    => $route->is_active,
            ],
            'config' => [
                'method'         => $cfg['method']         ?? 'GET',
                'request_params' => $cfg['request_params'] ?? '',
                'response_type'  => $cfg['response_type']  ?? 'JSON',
                'separator'      => $cfg['separator']      ?? '|',
                'status_key'     => $cfg['status_key']     ?? 'status',
                'txnid_key'      => $cfg['txnid_key']      ?? 'txnid',
                'live_id_key'    => $cfg['live_id_key']    ?? 'liveid',
                'balance_key'    => $cfg['balance_key']    ?? 'balance',
                'success_val'    => $cfg['success_val']    ?? 'SUCCESS',
                'pending_val'    => $cfg['pending_val']    ?? 'PENDING',
                'failure_val'    => $cfg['failure_val']    ?? 'FAILED',
            ],
        ]);
    }

    // ── PUT /api/v1/employee/operator-routes/{route}/api-setting ───────────
    public function update(Request $request, OperatorRoute $route): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'api_endpoint'   => ['required', 'url', 'max:500'],
            'method'         => ['required', 'in:GET,POST'],
            'request_params' => ['nullable', 'string', 'max:2000'],
            'response_type'  => ['required', 'in:JSON,XML,PIPE,STRING'],
            'separator'      => ['nullable', 'string', 'max:10'],
            'status_key'     => ['required', 'string', 'max:100'],
            'txnid_key'      => ['required', 'string', 'max:100'],
            'live_id_key'    => ['nullable', 'string', 'max:100'],
            'balance_key'    => ['nullable', 'string', 'max:100'],
            'success_val'    => ['required', 'string', 'max:100'],
            'pending_val'    => ['nullable', 'string', 'max:100'],
            'failure_val'    => ['required', 'string', 'max:100'],
        ]);

        if ($v->fails()) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $v->errors()], 422);
        }

        $d   = $v->validated();
        $cfg = $route->api_config ?? [];   // preserve list-level fields

        $route->update([
            'api_endpoint' => $d['api_endpoint'],
            'api_config'   => array_merge($cfg, [
                'method'         => $d['method'],
                'request_params' => $d['request_params'] ?? '',
                'response_type'  => $d['response_type'],
                'separator'      => $d['separator'] ?? '|',
                'status_key'     => $d['status_key'],
                'txnid_key'      => $d['txnid_key'],
                'live_id_key'    => $d['live_id_key'] ?? '',
                'balance_key'    => $d['balance_key'] ?? '',
                'success_val'    => $d['success_val'],
                'pending_val'    => $d['pending_val'] ?? '',
                'failure_val'    => $d['failure_val'],
            ]),
        ]);

        return response()->json(['message' => 'API settings updated successfully.']);
    }

    // ── PUT /api/v1/employee/operator-routes/{route}/margin ────────────────
    public function updateMargin(Request $request, OperatorRoute $route): JsonResponse
    {
        $request->validate(['margin' => ['required', 'numeric', 'min:0', 'max:100']]);

        $cfg           = $route->api_config ?? [];
        $cfg['margin'] = (float) $request->margin;
        $route->update(['api_config' => $cfg]);

        return response()->json(['message' => 'Margin updated.']);
    }
}
