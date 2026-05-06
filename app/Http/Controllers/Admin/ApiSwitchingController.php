<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Operator;
use App\Models\OperatorRoute;
use App\Support\DefaultOperatorCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApiSwitchingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        DefaultOperatorCatalog::ensure();

        $services = Operator::query()
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->values();

        $operators = DefaultOperatorCatalog::ordered(Operator::query())
            ->get(['id', 'name', 'code', 'category']);

        $operator = $request->integer('operator_id')
            ? Operator::find($request->integer('operator_id'))
            : $operators->first();

        $rechargeType = $request->input('recharge_type', 'prepaid');
        $providers = OperatorRoute::query()
            ->select('api_provider')
            ->whereNotNull('api_provider')
            ->where('api_provider', '!=', '')
            ->distinct()
            ->orderBy('api_provider')
            ->pluck('api_provider')
            ->values();

        $routes = collect();
        if ($operator) {
            $existing = OperatorRoute::query()
                ->where('operator_id', $operator->id)
                ->where('recharge_type', $rechargeType)
                ->get()
                ->keyBy('api_provider');

            $routes = $providers->map(function (string $provider) use ($existing, $operator, $rechargeType) {
                /** @var OperatorRoute|null $route */
                $route = $existing->get($provider);
                $cfg = $route?->api_config ?? [];

                return [
                    'id'            => $route?->id,
                    'operator_id'   => $operator->id,
                    'operator_name' => $operator->name,
                    'recharge_type' => $rechargeType,
                    'api_name'      => $provider,
                    'pending_limit' => (int) ($cfg['pending_limit'] ?? 0),
                    'total_pending' => (int) ($cfg['total_pending'] ?? 0),
                    'priority'      => (int) ($route?->priority ?? 0),
                    'is_active'     => (bool) ($route?->is_active ?? false),
                    'failure_limit' => (int) ($cfg['failure_limit'] ?? 0),
                ];
            })
                ->sortBy([
                    ['is_active', 'desc'],
                    ['priority', 'asc'],
                    ['api_name', 'asc'],
                ])
                ->values();
        }

        return response()->json([
            'services'  => $services,
            'operators' => $operators,
            'selected'  => [
                'operator_id'   => $operator?->id,
                'recharge_type' => $rechargeType,
            ],
            'stats' => [
                'active_apis' => $routes->where('is_active', true)->count(),
                'routes'      => $routes->count(),
            ],
            'routes' => $routes,
        ]);
    }

    public function saveRoute(Request $request): JsonResponse
    {
        $data = Validator::make($request->all(), [
            'operator_id'   => ['required', 'exists:operators,id'],
            'api_name'      => ['required', 'string', 'max:50'],
            'recharge_type' => ['required', 'in:prepaid,postpaid,dth,broadband'],
            'pending_limit' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'priority'      => ['nullable', 'integer', 'min:0', 'max:255'],
            'is_active'     => ['required', 'boolean'],
            'failure_limit' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ])->validate();

        $operator = Operator::findOrFail($data['operator_id']);
        $template = OperatorRoute::query()
            ->where('api_provider', $data['api_name'])
            ->orderBy('id')
            ->first();

        $route = OperatorRoute::query()->firstOrNew([
            'operator_id'   => $operator->id,
            'api_provider'  => $data['api_name'],
            'recharge_type' => $data['recharge_type'],
        ]);

        $cfg = $route->exists ? ($route->api_config ?? []) : ($template?->api_config ?? []);
        $cfg['pending_limit'] = (int) ($data['pending_limit'] ?? 0);
        $cfg['failure_limit'] = (int) ($data['failure_limit'] ?? 0);

        $route->fill([
            'name'            => $operator->name . ' ' . $data['recharge_type'] . ' via ' . $data['api_name'],
            'operator_code'   => $operator->code,
            'api_endpoint'    => $route->api_endpoint ?: ($template?->api_endpoint ?? ''),
            'api_config'      => $cfg,
            'priority'        => (int) ($data['priority'] ?? 0),
            'success_rate'    => $route->success_rate ?: ($template?->success_rate ?? 100),
            'timeout_seconds' => $route->timeout_seconds ?: ($template?->timeout_seconds ?? 30),
            'max_retries'     => $route->max_retries ?: ($template?->max_retries ?? 3),
            'is_active'       => (bool) $data['is_active'],
            'min_amount'      => $route->min_amount ?: $operator->min_amount,
            'max_amount'      => $route->max_amount ?: $operator->max_amount,
        ]);
        $route->save();

        return response()->json([
            'message' => 'API switching route saved.',
            'id'      => $route->id,
        ]);
    }
}
