<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\Operator;
use App\Models\SellerIntegrationRequest;
use App\Support\DefaultOperatorCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ApiConfigController extends Controller
{
    /**
     * GET /api/v1/seller/api-config
     */
    public function config(Request $request): JsonResponse
    {
        DefaultOperatorCatalog::ensure();

        $user = $request->user();

        // Active API key info
        $apiKey = ApiKey::where('user_id', $user->id)
            ->where('is_active', true)
            ->latest()
            ->first();

        // Latest integration request
        $integration = SellerIntegrationRequest::where('user_id', $user->id)
            ->latest()
            ->first();

        return response()->json([
            'data' => [
                'server_ip'    => env('APP_SERVER_IP', request()->server('SERVER_ADDR', '0.0.0.0')),
                'callback_url' => url('/api/v1/recharge/callback/' . $user->id),
                'api_key'      => $apiKey ? [
                    'id'         => $apiKey->id,
                    'prefix'     => $apiKey->key_prefix,
                    'scopes'     => $apiKey->scopes,
                    'last_used'  => $apiKey->last_used_at,
                    'created_at' => $apiKey->created_at,
                ] : null,
                'integration'  => $integration,
                'notification_settings' => $integration ? [
                    'api_name'                 => $integration->api_name,
                    'low_balance_notification' => (bool) $integration->low_balance_notification,
                    'low_balance_limit'        => $integration->low_balance_limit,
                    'notification_types'       => $integration->notification_types ?? [],
                ] : null,
                'operators'    => DefaultOperatorCatalog::ordered(
                    Operator::query()->where('is_active', true)
                )
                    ->get(['id', 'name', 'code']),
                'can_generate_token' => $integration
                    && $integration->status === 'approved'
                    && $this->hasRequiredApiTokenSetup($integration),
                'sale_access'  => [
                    'allowed'      => $integration
                        && $integration->status === 'approved'
                        && $integration->api_status === 'enabled'
                        && $integration->admin_status === 'enabled',
                    'api_status'   => $integration?->api_status ?? 'disabled',
                    'admin_status' => $integration?->admin_status ?? 'disabled',
                ],
                'api_endpoint' => url('/api/v1/buyer/recharge'),
                'api_docs_url' => url('/seller/api-docs'),
            ],
        ]);
    }

    /**
     * PATCH /api/v1/seller/api-config/integration/update
     * Allow seller to update integration details (even after approval).
     */
    public function updateIntegrationDetails(Request $request): JsonResponse
    {
        $data = $request->validate($this->integrationRules());

        $user        = $request->user();
        $integration = SellerIntegrationRequest::where('user_id', $user->id)->latest()->first();

        if (! $integration) {
            return response()->json(['message' => 'No integration request found. Please submit first.'], 404);
        }

        $integration->update([
            'api_name'           => $data['api_name'] ?? $integration->api_name,
            'website_url'        => $data['website_url'],
            'callback_url'       => $data['callback_url'],
            'status_check_url'   => $data['status_check_url'] ?? Arr::get($data, 'status_api.url'),
            'dispute_url'        => $data['dispute_url'] ?? Arr::get($data, 'dispute_api.url'),
            'site_username'      => $data['site_username'] ?? $integration->site_username,
            'site_password_hint' => $data['site_password_hint'] ?? $integration->site_password_hint,
            'allowed_ips'        => $this->normalizeAllowedIps($data['allowed_ips']),
            'recharge_api'       => $this->normalizeEndpointConfig($data['recharge_api'] ?? null, true),
            'status_api'         => $this->normalizeEndpointConfig($data['status_api'] ?? null, true),
            'balance_api'        => $this->normalizeEndpointConfig($data['balance_api'] ?? null, false),
            'dispute_api'        => $this->normalizeEndpointConfig($data['dispute_api'] ?? null, false),
            'callback_config'    => $this->normalizeCallbackConfig($data['callback_config'] ?? null),
            'op_code_map'        => $this->normalizeOpCodeMap($data['op_code_map'] ?? null),
        ]);

        return response()->json(['message' => 'Integration details updated successfully.']);
    }

    /**
     * PATCH /api/v1/seller/api-config/notification-settings
     */
    public function updateNotificationSettings(Request $request): JsonResponse
    {
        $data = $request->validate([
            'api_name'                 => ['required', 'string', 'max:150'],
            'low_balance_notification' => ['required', 'boolean'],
            'low_balance_limit'        => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'notification_types'       => ['nullable', 'array'],
            'notification_types.*'     => ['string', 'in:email,hangout,gmail,whatsapp,outlook'],
        ]);

        $user        = $request->user();
        $integration = SellerIntegrationRequest::where('user_id', $user->id)->latest()->first();

        if ($integration) {
            $integration->update([
                'api_name'                 => $data['api_name'],
                'low_balance_notification' => (bool) $data['low_balance_notification'],
                'low_balance_limit'        => $data['low_balance_notification'] ? ($data['low_balance_limit'] ?? null) : null,
                'notification_types'       => $data['low_balance_notification'] ? ($data['notification_types'] ?? []) : [],
            ]);
        } else {
            SellerIntegrationRequest::create([
                'user_id'                  => $user->id,
                'api_name'                 => $data['api_name'],
                'low_balance_notification' => (bool) $data['low_balance_notification'],
                'low_balance_limit'        => $data['low_balance_notification'] ? ($data['low_balance_limit'] ?? null) : null,
                'notification_types'       => $data['low_balance_notification'] ? ($data['notification_types'] ?? []) : [],
                'status'                   => 'pending',
                'api_status'               => 'disabled',
                'admin_status'             => 'disabled',
                'website_url'              => '',
                'callback_url'             => '',
                'allowed_ips'              => '',
            ]);
        }

        return response()->json(['message' => 'Notification settings saved successfully.']);
    }

    /**
     * POST /api/v1/seller/api-config/generate-token
     * Seller self-generates their API token. No admin approval required.
     */
    public function generateToken(Request $request): JsonResponse
    {
        $user = $request->user();

        $rawKey = 'rk_' . Str::random(60);

        // Deactivate any existing key for this seller
        ApiKey::where('user_id', $user->id)
            ->where('name', 'Seller API Key')
            ->update(['is_active' => false]);

        // Resolve current IP whitelist from integration if available
        $integration = SellerIntegrationRequest::where('user_id', $user->id)->latest()->first();
        $ipWhitelist = null;
        if ($integration && filled($integration->allowed_ips)) {
            $ips = preg_split('/[\r\n,]+/', $integration->allowed_ips) ?: [];
            $ips = array_values(array_filter(array_map('trim', $ips)));
            $ipWhitelist = $ips === [] ? null : $ips;
        }

        $apiKey = ApiKey::create([
            'user_id'      => $user->id,
            'name'         => 'Seller API Key',
            'key_prefix'   => substr($rawKey, 0, 12),
            'key_hash'     => hash('sha256', $rawKey),
            'scopes'       => ['recharge:read', 'recharge:write', 'wallet:read'],
            'ip_whitelist' => $ipWhitelist,
            'is_active'    => true,
        ]);

        return response()->json([
            'message'    => 'API token generated. Copy it now — it will not be shown again.',
            'api_key'    => $rawKey,
            'key_prefix' => $apiKey->key_prefix,
        ], 201);
    }

    /**
     * PATCH /api/v1/seller/api-config/toggle-api
     */
    public function toggleApiStatus(Request $request): JsonResponse
    {
        $user        = $request->user();
        $integration = SellerIntegrationRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->latest()
            ->first();

        if (! $integration) {
            return response()->json(['message' => 'Integration must be approved before toggling API status.'], 422);
        }

        $newStatus = $integration->api_status === 'enabled' ? 'disabled' : 'enabled';
        $integration->update(['api_status' => $newStatus]);

        return response()->json([
            'message'    => 'API status set to ' . $newStatus . '.',
            'api_status' => $newStatus,
        ]);
    }

    /**
     * POST /api/v1/seller/api-config/integration
     */
    public function submitIntegration(Request $request): JsonResponse
    {
        $data = $request->validate($this->integrationRules());

        $user = $request->user();

        // Allow resubmission only if previous was rejected or none exists
        $existing = SellerIntegrationRequest::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'You already have an active integration request (status: ' . $existing->status . ').',
            ], 422);
        }

        SellerIntegrationRequest::create([
            'user_id'           => $user->id,
            'website_url'       => $data['website_url'],
            'callback_url'      => $data['callback_url'],
            'status_check_url'  => $data['status_check_url'] ?? Arr::get($data, 'status_api.url'),
            'dispute_url'       => $data['dispute_url'] ?? Arr::get($data, 'dispute_api.url'),
            'site_username'     => $data['site_username'] ?? null,
            'site_password_hint'=> $data['site_password_hint'] ?? null,
            'allowed_ips'       => $this->normalizeAllowedIps($data['allowed_ips']),
            'recharge_api'      => $this->normalizeEndpointConfig($data['recharge_api'] ?? null, true),
            'status_api'        => $this->normalizeEndpointConfig($data['status_api'] ?? null, true),
            'balance_api'       => $this->normalizeEndpointConfig($data['balance_api'] ?? null, false),
            'dispute_api'       => $this->normalizeEndpointConfig($data['dispute_api'] ?? null, false),
            'callback_config'   => $this->normalizeCallbackConfig($data['callback_config'] ?? null),
            'op_code_map'       => $this->normalizeOpCodeMap($data['op_code_map'] ?? null),
            'status'            => 'pending',
            'api_status'        => 'disabled',
            'admin_status'      => 'disabled',
        ]);

        return response()->json([
            'message' => 'Integration request submitted. Admin will review within 24 hours.',
        ], 201);
    }

    private function normalizeAllowedIps(string $value): string
    {
        $ips = preg_split('/[\r\n,]+/', $value) ?: [];
        $ips = array_values(array_filter(array_map('trim', $ips)));

        return implode("\n", $ips);
    }

    private function integrationRules(): array
    {
        $responseTypes = ['JSON', 'XML', 'OTHER'];
        $methods       = ['GET', 'POST', 'PUT', 'PATCH'];

        return [
            'website_url'                     => ['required', 'url', 'max:255'],
            'callback_url'                    => ['required', 'url', 'max:255'],
            'status_check_url'                => ['nullable', 'url', 'max:255'],
            'dispute_url'                     => ['nullable', 'url', 'max:255'],
            'allowed_ips'                     => ['required', 'string', 'max:1000'],
            'site_username'                   => ['sometimes', 'nullable', 'string', 'max:100'],
            'site_password_hint'              => ['sometimes', 'nullable', 'string', 'max:100'],

            'recharge_api'                    => ['nullable', 'array'],
            'recharge_api.method'             => ['nullable', Rule::in($methods)],
            'recharge_api.url'                => ['nullable', 'url', 'max:500'],
            'recharge_api.params'             => ['nullable', 'string', 'max:3000'],
            'recharge_api.response_type'      => ['nullable', Rule::in($responseTypes)],
            'recharge_api.separator'          => ['nullable', 'string', 'max:20'],
            'recharge_api.status_field'       => ['nullable', 'string', 'max:100'],
            'recharge_api.api_txnid_field'    => ['nullable', 'string', 'max:100'],
            'recharge_api.live_id_field'      => ['nullable', 'string', 'max:100'],
            'recharge_api.balance_field'      => ['nullable', 'string', 'max:100'],
            'recharge_api.success_param'      => ['nullable', 'string', 'max:100'],
            'recharge_api.pending_param'      => ['nullable', 'string', 'max:100'],
            'recharge_api.failure_param'      => ['nullable', 'string', 'max:100'],

            'status_api'                      => ['nullable', 'array'],
            'status_api.method'               => ['nullable', Rule::in($methods)],
            'status_api.url'                  => ['nullable', 'url', 'max:500'],
            'status_api.params'               => ['nullable', 'string', 'max:3000'],
            'status_api.response_type'        => ['nullable', Rule::in($responseTypes)],
            'status_api.separator'            => ['nullable', 'string', 'max:20'],
            'status_api.status_field'         => ['nullable', 'string', 'max:100'],
            'status_api.api_txnid_field'      => ['nullable', 'string', 'max:100'],
            'status_api.live_id_field'        => ['nullable', 'string', 'max:100'],
            'status_api.balance_field'        => ['nullable', 'string', 'max:100'],
            'status_api.success_param'        => ['nullable', 'string', 'max:100'],
            'status_api.pending_param'        => ['nullable', 'string', 'max:100'],
            'status_api.failure_param'        => ['nullable', 'string', 'max:100'],

            'balance_api'                     => ['nullable', 'array'],
            'balance_api.method'              => ['nullable', Rule::in($methods)],
            'balance_api.url'                 => ['nullable', 'url', 'max:500'],
            'balance_api.params'              => ['nullable', 'string', 'max:3000'],
            'balance_api.response_type'       => ['nullable', Rule::in($responseTypes)],
            'balance_api.separator'           => ['nullable', 'string', 'max:20'],
            'balance_api.balance_field'       => ['nullable', 'string', 'max:100'],

            'dispute_api'                     => ['nullable', 'array'],
            'dispute_api.method'              => ['nullable', Rule::in($methods)],
            'dispute_api.url'                 => ['nullable', 'url', 'max:500'],
            'dispute_api.params'              => ['nullable', 'string', 'max:3000'],
            'dispute_api.response_type'       => ['nullable', Rule::in($responseTypes)],
            'dispute_api.separator'           => ['nullable', 'string', 'max:20'],
            'dispute_api.status_field'        => ['nullable', 'string', 'max:100'],
            'dispute_api.success_param'       => ['nullable', 'string', 'max:100'],
            'dispute_api.pending_param'       => ['nullable', 'string', 'max:100'],
            'dispute_api.failure_param'       => ['nullable', 'string', 'max:100'],

            'callback_config'                 => ['nullable', 'array'],
            'callback_config.response_type'   => ['nullable', Rule::in($responseTypes)],
            'callback_config.ip_validation'   => ['nullable', 'string', 'max:1000'],
            'callback_config.status_field'    => ['nullable', 'string', 'max:100'],
            'callback_config.ourtransid'      => ['nullable', 'string', 'max:100'],
            'callback_config.api_txnid_field' => ['nullable', 'string', 'max:100'],
            'callback_config.live_id_field'   => ['nullable', 'string', 'max:100'],
            'callback_config.balance_field'   => ['nullable', 'string', 'max:100'],
            'callback_config.success_param'   => ['nullable', 'string', 'max:100'],
            'callback_config.pending_param'   => ['nullable', 'string', 'max:100'],
            'callback_config.failure_param'   => ['nullable', 'string', 'max:100'],

            'op_code_map'                     => ['nullable', 'array'],
            'op_code_map.*.company_name'      => ['nullable', 'string', 'max:150'],
            'op_code_map.*.our_code'          => ['nullable', 'string', 'max:100'],
            'op_code_map.*.seller_code'       => ['nullable', 'string', 'max:100'],
            'op_code_map.*.opparam1'          => ['nullable', 'string', 'max:100'],
            'op_code_map.*.opparam2'          => ['nullable', 'string', 'max:100'],
            'op_code_map.*.opparam3'          => ['nullable', 'string', 'max:100'],
            'op_code_map.*.opparam4'          => ['nullable', 'string', 'max:100'],
            'op_code_map.*.opparam5'          => ['nullable', 'string', 'max:100'],
        ];
    }

    private function normalizeEndpointConfig(?array $config, bool $includeStatusFields): ?array
    {
        if (! is_array($config)) {
            return null;
        }

        $normalized = [
            'method'         => strtoupper((string) ($config['method'] ?? 'GET')),
            'url'            => trim((string) ($config['url'] ?? '')),
            'params'         => trim((string) ($config['params'] ?? '')),
            'response_type'  => strtoupper((string) ($config['response_type'] ?? 'JSON')),
            'separator'      => trim((string) ($config['separator'] ?? '')),
            'balance_field'  => trim((string) ($config['balance_field'] ?? '')),
            'success_param'  => trim((string) ($config['success_param'] ?? '')),
            'pending_param'  => trim((string) ($config['pending_param'] ?? '')),
            'failure_param'  => trim((string) ($config['failure_param'] ?? '')),
        ];

        if ($includeStatusFields) {
            $normalized['status_field']    = trim((string) ($config['status_field'] ?? ''));
            $normalized['api_txnid_field'] = trim((string) ($config['api_txnid_field'] ?? ''));
            $normalized['live_id_field']   = trim((string) ($config['live_id_field'] ?? ''));
        }

        if (in_array($normalized['response_type'], ['JSON', 'XML'], true)) {
            $normalized['separator'] = '';
        }

        return array_filter($normalized, static fn ($value) => $value !== '');
    }

    private function normalizeCallbackConfig(?array $config): ?array
    {
        if (! is_array($config)) {
            return null;
        }

        $normalized = [
            'response_type'   => strtoupper((string) ($config['response_type'] ?? 'JSON')),
            'ip_validation'   => $this->normalizeAllowedIps((string) ($config['ip_validation'] ?? '')),
            'status_field'    => trim((string) ($config['status_field'] ?? '')),
            'ourtransid'      => trim((string) ($config['ourtransid'] ?? '')),
            'api_txnid_field' => trim((string) ($config['api_txnid_field'] ?? '')),
            'live_id_field'   => trim((string) ($config['live_id_field'] ?? '')),
            'balance_field'   => trim((string) ($config['balance_field'] ?? '')),
            'success_param'   => trim((string) ($config['success_param'] ?? '')),
            'pending_param'   => trim((string) ($config['pending_param'] ?? '')),
            'failure_param'   => trim((string) ($config['failure_param'] ?? '')),
        ];

        return array_filter($normalized, static fn ($value) => $value !== '');
    }

    private function normalizeOpCodeMap(?array $codes): ?array
    {
        if (! is_array($codes)) {
            return null;
        }

        $normalized = [];
        foreach ($codes as $row) {
            $companyName = trim((string) ($row['company_name'] ?? ''));
            $ourCode    = trim((string) ($row['our_code'] ?? ''));
            $sellerCode = trim((string) ($row['seller_code'] ?? ''));
            $opparam1   = trim((string) ($row['opparam1'] ?? ''));
            $opparam2   = trim((string) ($row['opparam2'] ?? ''));
            $opparam3   = trim((string) ($row['opparam3'] ?? ''));
            $opparam4   = trim((string) ($row['opparam4'] ?? ''));
            $opparam5   = trim((string) ($row['opparam5'] ?? ''));

            if (
                $companyName !== '' || $ourCode !== '' || $sellerCode !== ''
                || $opparam1 !== '' || $opparam2 !== '' || $opparam3 !== ''
                || $opparam4 !== '' || $opparam5 !== ''
            ) {
                $normalized[] = array_filter([
                    'company_name' => $companyName,
                    'our_code'     => $ourCode,
                    'seller_code'  => $sellerCode,
                    'opparam1'     => $opparam1,
                    'opparam2'     => $opparam2,
                    'opparam3'     => $opparam3,
                    'opparam4'     => $opparam4,
                    'opparam5'     => $opparam5,
                ], static fn ($value) => $value !== '');
            }
        }

        return $normalized === [] ? null : $normalized;
    }

    private function hasRequiredApiTokenSetup(SellerIntegrationRequest $integration): bool
    {
        return filled($integration->callback_url)
            && $this->normalizeAllowedIps($integration->allowed_ips ?? '') !== '';
    }
}
