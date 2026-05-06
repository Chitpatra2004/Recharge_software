<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Operator;
use App\Models\OperatorRoute;
use App\Services\GenericApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OperatorApiSettingController extends Controller
{
    public function __construct(private readonly GenericApiService $apiService) {}

    // ── GET /api/v1/employee/api-providers ─────────────────────────────────
    public function listRoutes(): JsonResponse
    {
        $routes = OperatorRoute::orderBy('id')->get();

        $data = $routes->map(function (OperatorRoute $r) {
            $cfg = $r->api_config ?? [];
            return [
                'id'            => $r->id,
                'api_id'        => 'API ' . $r->id,
                'name'          => $r->name,
                'api_provider'  => $r->api_provider ?? '—',
                'operator_code' => $r->operator_code,
                'recharge_type' => $r->recharge_type,
                'is_active'     => $r->is_active,
                'api_status'    => (bool) ($cfg['api_status']   ?? false),
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
            'api_provider' => ['nullable', 'string', 'max:50'],
            'validity_till'=> ['nullable', 'date'],
            'purchase'     => ['nullable', 'in:active,deactive'],
        ]);

        if ($v->fails()) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $v->errors()], 422);
        }

        $operator = Operator::query()->orderBy('id')->first();
        if (! $operator) {
            return response()->json(['message' => 'Please add at least one operator before adding an API provider.'], 422);
        }

        $route = OperatorRoute::create([
            'name'          => $request->name,
            'api_provider'  => $request->api_provider ?? '',
            'operator_id'   => $operator->id,
            'operator_code' => $operator->code,
            'recharge_type' => 'prepaid',
            'api_endpoint'  => '',
            'is_active'     => false,
            'api_config'    => [
                'api_status'   => false, 'auto_renewal' => false,
                'validity_till'=> $request->input('validity_till') ?: '0000-00-00',
                'purchase'     => $request->input('purchase', 'active'),
                'margin'       => 0,
            ],
        ]);

        return response()->json(['message' => 'API provider added.', 'id' => $route->id], 201);
    }

    // ── PATCH /api/v1/employee/api-providers/{route}/toggle ────────────────
    public function toggle(Request $request, OperatorRoute $route): JsonResponse
    {
        $field  = $request->input('field', 'admin');
        $remark = $request->input('remark', '');

        if ($field === 'admin') {
            $route->update(['is_active' => ! $route->is_active]);
            $val = $route->is_active;
        } else {
            $cfg       = $route->api_config ?? [];
            $key       = $field === 'renewal' ? 'auto_renewal' : 'api_status';
            $cfg[$key] = ! ($cfg[$key] ?? false);
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
            'api_provider'  => ['nullable', 'string', 'max:50'],
            'validity_till' => ['nullable', 'date'],
            'purchase'      => ['nullable', 'in:active,deactive'],
        ]);

        if ($v->fails()) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $v->errors()], 422);
        }

        $cfg = $route->api_config ?? [];
        if ($request->filled('validity_till')) $cfg['validity_till'] = $request->validity_till;
        if ($request->filled('purchase'))      $cfg['purchase']      = $request->purchase;

        $route->update([
            'name'          => $request->name,
            'api_provider'  => $request->api_provider ?? $route->api_provider,
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

    // ─────────────────────────────────────────────────────────────────────────
    // FULL CONFIG — returns all 6 sections for the config portal
    // ─────────────────────────────────────────────────────────────────────────
    public function fullConfig(OperatorRoute $route): JsonResponse
    {
        $cfg = $route->api_config ?? [];

        return response()->json([
            'route' => [
                'id'            => $route->id,
                'name'          => $route->name,
                'api_provider'  => $route->api_provider,
                'operator_code' => $route->operator_code,
                'recharge_type' => $route->recharge_type,
                'is_active'     => $route->is_active,
            ],
            'credentials' => [
                'username'  => $cfg['username']  ?? '',
                // api_token never returned to browser
            ],
            'recharge_api' => [
                'method'        => $cfg['recharge_api']['method']        ?? $cfg['method']          ?? 'GET',
                'url'           => $cfg['recharge_api']['url']           ?? $route->api_endpoint    ?? '',
                'params'        => $cfg['recharge_api']['params']        ?? $cfg['request_params']  ?? '',
                'response_type' => $cfg['recharge_api']['response_type'] ?? $cfg['response_type']   ?? 'JSON',
                'separator'     => $cfg['recharge_api']['separator']     ?? $cfg['separator']       ?? '',
                'status_key'    => $cfg['recharge_api']['status_key']    ?? $cfg['status_key']      ?? 'status',
                'txnid_key'     => $cfg['recharge_api']['txnid_key']     ?? $cfg['txnid_key']       ?? 'tid',
                'live_id_key'   => $cfg['recharge_api']['live_id_key']   ?? $cfg['live_id_key']     ?? 'operator_id',
                'success_val'   => $cfg['recharge_api']['success_val']   ?? $cfg['success_val']     ?? 'Success',
                'pending_val'   => $cfg['recharge_api']['pending_val']   ?? $cfg['pending_val']     ?? 'Pending',
                'failure_val'   => $cfg['recharge_api']['failure_val']   ?? $cfg['failure_val']     ?? 'Failure',
            ],
            'balance_api' => [
                'method'      => $cfg['balance_api']['method']      ?? 'GET',
                'url'         => $cfg['balance_api']['url']         ?? '',
                'params'      => $cfg['balance_api']['params']      ?? '',
                'balance_key' => $cfg['balance_api']['balance_key'] ?? 'balance',
            ],
            'status_api' => [
                'method'     => $cfg['status_api']['method']     ?? 'GET',
                'url'        => $cfg['status_api']['url']        ?? '',
                'params'     => $cfg['status_api']['params']     ?? '',
                'status_key' => $cfg['status_api']['status_key'] ?? 'status',
                'txnid_key'  => $cfg['status_api']['txnid_key']  ?? 'tid',
            ],
            'complaint_api' => [
                'method' => $cfg['complaint_api']['method'] ?? 'GET',
                'url'    => $cfg['complaint_api']['url']    ?? '',
                'params' => $cfg['complaint_api']['params'] ?? '',
            ],
            'callback' => [
                'order_id_param'  => $cfg['callback']['order_id_param']  ?? 'uniqueid',
                'status_param'    => $cfg['callback']['status_param']    ?? 'status',
                'txnid_param'     => $cfg['callback']['txnid_param']     ?? 'transaction_id',
                'op_id_param'     => $cfg['callback']['op_id_param']     ?? 'operator_id',
                'success_val'     => $cfg['callback']['success_val']     ?? 'Success',
                'failure_val'     => $cfg['callback']['failure_val']     ?? 'Failure',
                'pending_val'     => $cfg['callback']['pending_val']     ?? 'Pending',
            ],
            'op_codes' => $cfg['op_codes'] ?? [],
        ]);
    }

    // ── PUT /api/v1/employee/api-providers/{route}/credentials ─────────────
    public function updateCredentials(Request $request, OperatorRoute $route): JsonResponse
    {
        $request->validate([
            'username'  => ['nullable', 'string', 'max:100'],
            'api_token' => ['nullable', 'string', 'max:500'],
        ]);

        $cfg = $route->api_config ?? [];
        if ($request->filled('username'))  $cfg['username']  = $request->username;
        if ($request->filled('api_token')) $cfg['api_token'] = $request->api_token;
        $route->update(['api_config' => $cfg]);

        return response()->json(['message' => 'Credentials saved.']);
    }

    // ── PUT /api/v1/employee/api-providers/{route}/recharge-api ───────────
    public function updateRechargeApi(Request $request, OperatorRoute $route): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'method'        => ['required', 'in:GET,POST'],
            'url'           => ['required', 'url', 'max:500'],
            'params'        => ['nullable', 'string', 'max:2000'],
            'response_type' => ['nullable', 'in:JSON,XML,PIPE,STRING'],
            'separator'     => ['nullable', 'string', 'max:10'],
            'status_key'    => ['required', 'string', 'max:100'],
            'txnid_key'     => ['required', 'string', 'max:100'],
            'live_id_key'   => ['nullable', 'string', 'max:100'],
            'success_val'   => ['required', 'string', 'max:100'],
            'pending_val'   => ['nullable', 'string', 'max:100'],
            'failure_val'   => ['required', 'string', 'max:100'],
        ]);
        if ($v->fails()) return response()->json(['message' => 'Validation failed.', 'errors' => $v->errors()], 422);

        $d   = $v->validated();
        $cfg = $route->api_config ?? [];
        $cfg['recharge_api'] = $d;
        // keep flat fields in sync for backward compat with RechargeService
        $cfg['method']         = $d['method'];
        $cfg['request_params'] = $d['params'] ?? '';
        $cfg['response_type']  = $d['response_type'] ?? 'JSON';
        $cfg['separator']      = $d['separator'] ?? '';
        $cfg['status_key']     = $d['status_key'];
        $cfg['txnid_key']      = $d['txnid_key'];
        $cfg['live_id_key']    = $d['live_id_key'] ?? '';
        $cfg['success_val']    = $d['success_val'];
        $cfg['pending_val']    = $d['pending_val'] ?? '';
        $cfg['failure_val']    = $d['failure_val'];

        $route->update(['api_endpoint' => $d['url'], 'api_config' => $cfg]);
        return response()->json(['message' => 'Recharge API settings saved.']);
    }

    // ── PUT /api/v1/employee/api-providers/{route}/balance-api ────────────
    public function updateBalanceApi(Request $request, OperatorRoute $route): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'method'      => ['required', 'in:GET,POST'],
            'url'         => ['nullable', 'url', 'max:500'],
            'params'      => ['nullable', 'string', 'max:1000'],
            'balance_key' => ['required', 'string', 'max:100'],
        ]);
        if ($v->fails()) return response()->json(['message' => 'Validation failed.', 'errors' => $v->errors()], 422);

        $cfg = $route->api_config ?? [];
        $cfg['balance_api'] = $v->validated();
        $route->update(['api_config' => $cfg]);
        return response()->json(['message' => 'Balance API settings saved.']);
    }

    // ── PUT /api/v1/employee/api-providers/{route}/status-api ─────────────
    public function updateStatusApi(Request $request, OperatorRoute $route): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'method'     => ['required', 'in:GET,POST'],
            'url'        => ['required', 'url', 'max:500'],
            'params'     => ['nullable', 'string', 'max:1000'],
            'status_key' => ['required', 'string', 'max:100'],
            'txnid_key'  => ['nullable', 'string', 'max:100'],
        ]);
        if ($v->fails()) return response()->json(['message' => 'Validation failed.', 'errors' => $v->errors()], 422);

        $cfg = $route->api_config ?? [];
        $cfg['status_api'] = $v->validated();
        $route->update(['api_config' => $cfg]);
        return response()->json(['message' => 'Status Check API settings saved.']);
    }

    // ── PUT /api/v1/employee/api-providers/{route}/complaint-api ──────────
    public function updateComplaintApi(Request $request, OperatorRoute $route): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'method'        => ['required', 'in:GET,POST'],
            'url'           => ['nullable', 'string', 'max:500'],
            'url_part2'     => ['nullable', 'string', 'max:500'],
            'params'        => ['nullable', 'string', 'max:1000'],
            'response_type' => ['nullable', 'in:JSON,XML,OTHER'],
            'separator'     => ['nullable', 'string', 'max:10'],
            'status_key'    => ['nullable', 'string', 'max:100'],
            'success_key'   => ['nullable', 'string', 'max:100'],
            'failure_key'   => ['nullable', 'string', 'max:100'],
            'pending_key'   => ['nullable', 'string', 'max:100'],
        ]);
        if ($v->fails()) return response()->json(['message' => 'Validation failed.', 'errors' => $v->errors()], 422);

        $cfg = $route->api_config ?? [];
        $cfg['complaint_api'] = $v->validated();
        $route->update(['api_config' => $cfg]);
        return response()->json(['message' => 'Complaint API settings saved.']);
    }

    // ── PUT /api/v1/employee/api-providers/{route}/callback ───────────────
    public function updateCallback(Request $request, OperatorRoute $route): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'order_id_param' => ['required', 'string', 'max:50'],
            'status_param'   => ['required', 'string', 'max:50'],
            'txnid_param'    => ['nullable', 'string', 'max:50'],
            'op_id_param'    => ['nullable', 'string', 'max:50'],
            'success_val'    => ['required', 'string', 'max:50'],
            'failure_val'    => ['required', 'string', 'max:50'],
            'pending_val'    => ['nullable', 'string', 'max:50'],
        ]);
        if ($v->fails()) return response()->json(['message' => 'Validation failed.', 'errors' => $v->errors()], 422);

        $cfg = $route->api_config ?? [];
        $cfg['callback'] = $v->validated();
        $route->update(['api_config' => $cfg]);
        return response()->json(['message' => 'Callback settings saved.']);
    }

    // ── PUT /api/v1/employee/api-providers/{route}/op-codes ───────────────
    public function updateOpCodes(Request $request, OperatorRoute $route): JsonResponse
    {
        $request->validate(['codes' => ['required', 'array']]);

        $codes = [];
        foreach ($request->codes as $item) {
            $our = trim($item['our_code'] ?? '');
            $api = trim($item['api_code'] ?? '');
            if ($our !== '' && $api !== '') {
                $codes[$our] = $api;
            }
        }

        $cfg = $route->api_config ?? [];
        $cfg['op_codes'] = $codes;
        $route->update(['api_config' => $cfg]);
        return response()->json(['message' => 'Operator codes saved.']);
    }

    // ── PUT /api/v1/employee/operator-routes/{route}/margin ────────────────
    public function updateMargin(Request $request, OperatorRoute $route): JsonResponse
    {
        $request->validate(['margin' => ['required', 'numeric', 'min:0', 'max:100']]);
        $cfg = $route->api_config ?? [];
        $cfg['margin'] = (float) $request->margin;
        $route->update(['api_config' => $cfg]);
        return response()->json(['message' => 'Margin updated.']);
    }

    // ── GET /api/v1/employee/api-providers/{route}/test-balance ───────────
    public function testBalance(OperatorRoute $route): JsonResponse
    {
        $result = $this->apiService->balance($route);
        if (! $result['success']) {
            return response()->json(['message' => $result['error'] ?? 'Balance check failed.'], 502);
        }
        // cache in config
        $cfg = $route->api_config ?? [];
        $cfg['balance'] = $result['balance'];
        $route->update(['api_config' => $cfg]);
        return response()->json(['balance' => $result['balance'], 'raw' => $result['raw']]);
    }

    // ── GET /api/v1/employee/api-providers/{route}/test-status?order_id={} ─
    public function testStatus(Request $request, OperatorRoute $route): JsonResponse
    {
        $request->validate(['order_id' => ['required', 'string', 'max:50']]);
        $result = $this->apiService->checkStatus($route, $request->order_id);
        if (! $result['success']) {
            return response()->json(['message' => $result['error'] ?? 'Status check failed.'], 502);
        }
        return response()->json($result);
    }

    // ── POST /api/v1/employee/api-providers/{route}/test-complaint ─────────
    public function testComplaint(Request $request, OperatorRoute $route): JsonResponse
    {
        $request->validate(['order_id' => ['required', 'string', 'max:50'], 'message' => ['nullable', 'string', 'max:300']]);
        $result = $this->apiService->raiseComplaint($route, $request->order_id, $request->input('message', 'complain'));
        return response()->json(['success' => $result['success'], 'message' => $result['message'] ?? '']);
    }
}
