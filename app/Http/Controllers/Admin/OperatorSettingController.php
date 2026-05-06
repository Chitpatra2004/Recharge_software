<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Operator;
use App\Models\OperatorRoute;
use App\Support\DefaultOperatorCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OperatorSettingController extends Controller
{
    public function index(): JsonResponse
    {
        DefaultOperatorCatalog::ensure();

        $operators = DefaultOperatorCatalog::ordered(
            Operator::query()
                ->withCount('routes')
        )
            ->get()
            ->map(function (Operator $operator) {
                $route = $operator->routes()
                    ->where('is_active', true)
                    ->orderBy('priority')
                    ->first();

                return [
                    'id'            => $operator->id,
                    'name'          => $operator->name,
                    'code'          => $operator->code,
                    'category'      => $operator->category,
                    'is_active'     => (bool) $operator->is_active,
                    'reroot_count'  => (int) $operator->routes_count,
                    'api_provider'  => $route?->api_provider ?? '',
                    'min_comm'      => (float) ($operator->commission_rate ?? 0),
                    'max_comm'      => (float) ($operator->commission_rate ?? 0),
                    'min_amount'    => (float) $operator->min_amount,
                    'max_amount'    => (float) $operator->max_amount,
                ];
            });

        $apiProviders = OperatorRoute::query()
            ->select('api_provider')
            ->whereNotNull('api_provider')
            ->where('api_provider', '!=', '')
            ->distinct()
            ->orderBy('api_provider')
            ->pluck('api_provider')
            ->values();

        return response()->json([
            'operators'     => $operators,
            'api_providers' => $apiProviders,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validator($request)->validate();
        $data['code'] = strtoupper(trim($data['code']));

        $operator = Operator::create([
            'name'             => $data['name'],
            'code'             => $data['code'],
            'category'         => $data['category'],
            'is_active'        => (bool) ($data['is_active'] ?? true),
            'prepaid_enabled'  => true,
            'postpaid_enabled' => in_array($data['category'], ['mobile'], true),
            'commission_rate'  => $data['min_comm'] ?? null,
            'min_amount'       => $data['min_amount'] ?? 1,
            'max_amount'       => $data['max_amount'] ?? 10000,
        ]);

        if (! empty($data['api_provider'])) {
            $this->assignApiProvider($operator, $data['api_provider']);
        }

        return response()->json(['message' => 'Operator added.', 'id' => $operator->id], 201);
    }

    public function update(Request $request, Operator $operator): JsonResponse
    {
        $data = $this->validator($request, $operator)->validate();
        $data['code'] = strtoupper(trim($data['code']));

        $operator->update([
            'name'            => $data['name'],
            'code'            => $data['code'],
            'category'        => $data['category'],
            'is_active'       => (bool) ($data['is_active'] ?? false),
            'commission_rate' => $data['min_comm'] ?? null,
            'min_amount'      => $data['min_amount'] ?? 1,
            'max_amount'      => $data['max_amount'] ?? 10000,
        ]);

        $operator->routes()->update(['operator_code' => $operator->code]);

        if (array_key_exists('api_provider', $data)) {
            $this->assignApiProvider($operator, (string) $data['api_provider']);
        }

        return response()->json(['message' => 'Operator settings updated.']);
    }

    public function toggle(Operator $operator): JsonResponse
    {
        $operator->update(['is_active' => ! $operator->is_active]);

        return response()->json([
            'message'   => 'Operator status updated.',
            'is_active' => (bool) $operator->is_active,
        ]);
    }

    private function validator(Request $request, ?Operator $operator = null)
    {
        $id = $operator?->id;

        return Validator::make($request->all(), [
            'name'         => ['required', 'string', 'max:100'],
            'code'         => ['required', 'string', 'max:30', Rule::unique('operators', 'code')->ignore($id)],
            'category'     => ['required', Rule::in(DefaultOperatorCatalog::categories())],
            'is_active'    => ['nullable', 'boolean'],
            'api_provider' => ['nullable', 'string', 'max:50'],
            'min_comm'     => ['nullable', 'numeric', 'min:0', 'max:100'],
            'max_comm'     => ['nullable', 'numeric', 'min:0', 'max:100'],
            'min_amount'   => ['nullable', 'numeric', 'min:0'],
            'max_amount'   => ['nullable', 'numeric', 'min:0'],
        ]);
    }

    private function assignApiProvider(Operator $operator, string $provider): void
    {
        if ($provider === '') {
            return;
        }

        $template = OperatorRoute::query()
            ->where('api_provider', $provider)
            ->orderBy('id')
            ->first();

        $route = OperatorRoute::query()->firstOrNew([
            'operator_id'    => $operator->id,
            'recharge_type'  => 'prepaid',
            'api_provider'   => $provider,
        ]);

        $route->fill([
            'name'            => $operator->name . ' prepaid via ' . $provider,
            'operator_code'   => $operator->code,
            'api_endpoint'    => $template?->api_endpoint ?? '',
            'api_config'      => $template?->api_config ?? ['api_status' => false],
            'priority'        => 1,
            'success_rate'    => $template?->success_rate ?? 100,
            'timeout_seconds' => $template?->timeout_seconds ?? 30,
            'max_retries'     => $template?->max_retries ?? 3,
            'is_active'       => true,
            'min_amount'      => $operator->min_amount,
            'max_amount'      => $operator->max_amount,
        ]);
        $route->save();

        $operator->routes()
            ->where('id', '!=', $route->id)
            ->where('priority', 1)
            ->update(['priority' => 2]);
    }
}
