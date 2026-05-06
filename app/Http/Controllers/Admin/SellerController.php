<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\Operator;
use App\Models\SellerOperatorCommission;
use App\Models\SellerIntegrationRequest;
use App\Models\User;
use App\Notifications\RegistrationApprovedNotification;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SellerController extends Controller
{
    /** GET /api/v1/employee/sellers */
    public function index(Request $request): JsonResponse
    {
        $allowedRoles = ['api_user'];
        $role = $request->filled('role') && in_array($request->role, $allowedRoles)
            ? [$request->role]
            : $allowedRoles;

        $query = User::whereIn('role', $role)
            ->with(['latestIntegration'])
            ->withCount([
                'rechargeTransactions',
                'sellerPaymentRequests as pending_payments_count' => fn ($q) => $q->where('status', 'pending'),
            ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) =>
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('mobile', 'like', "%{$s}%")
            );
        }

        $sellers = $query->latest()->paginate($request->integer('per_page', 25));

        // Append wallet balance, api_key_hint, integration_status, and doc flags
        $sellers->getCollection()->transform(function ($u) {
            $wallet = DB::table('wallets')->where('user_id', $u->id)->first();
            $u->wallet_balance = $wallet ? (float) $wallet->balance : 0.0;

            $apiKey = DB::table('api_keys')->where('user_id', $u->id)->where('is_active', true)->latest()->first();
            $u->api_key_hint = $apiKey ? $apiKey->key_prefix : null;

            $u->integration_status = $u->latestIntegration ? $u->latestIntegration->status : 'none';
            $u->integration_id = $u->latestIntegration?->id;
            $u->api_status = $u->latestIntegration?->api_status ?? 'disabled';
            $u->admin_status = $u->latestIntegration?->admin_status ?? 'disabled';

            // Document flags for admin review
            $u->has_pan         = ! empty($u->pan_image_path);
            $u->has_gst         = ! empty($u->gst_certificate_path);
            $u->has_document    = ! empty($u->document_path);

            return $u;
        });

        // Summary stats — per role
        $statsQuery = DB::table('users')->selectRaw("
            role,
            COUNT(*) as total,
            SUM(CASE WHEN approval_status = 'pending'  THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'active'            THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN status = 'suspended'         THEN 1 ELSE 0 END) as suspended
        ")->whereIn('role', $allowedRoles)->groupBy('role')->get()->keyBy('role');

        $stats = [
            'api_user' => $statsQuery->get('api_user', (object)['total'=>0,'pending'=>0,'active'=>0,'suspended'=>0]),
        ];

        return response()->json(['data' => $sellers, 'stats' => $stats]);
    }

    /** GET /api/v1/employee/sellers/{id}/document/{type}
     *  Returns a 10-minute signed URL the browser can open directly. */
    public function viewDocument(int $id, string $type): JsonResponse
    {
        $allowed = ['pan', 'gst', 'doc'];
        if (! in_array($type, $allowed)) {
            return response()->json(['message' => 'Invalid document type.'], 422);
        }

        $pathField = ['pan' => 'pan_image_path', 'gst' => 'gst_certificate_path', 'doc' => 'document_path'][$type];
        $user = User::where('role', 'api_user')->findOrFail($id);

        if (empty($user->{$pathField}) || ! Storage::disk('private')->exists($user->{$pathField})) {
            return response()->json(['message' => 'Document not found.'], 404);
        }

        $signedUrl = URL::temporarySignedRoute(
            'admin.seller.document',  // group prefix 'admin.' + route name 'seller.document'
            now()->addMinutes(10),
            ['id' => $id, 'type' => $type]
        );

        return response()->json(['url' => $signedUrl]);
    }

    /** GET /admin/sellers/{id}/document/{type}  (signed web route — no Bearer needed) */
    public function serveDocument(int $id, string $type): Response
    {
        $pathField = ['pan' => 'pan_image_path', 'gst' => 'gst_certificate_path', 'doc' => 'document_path'][$type] ?? null;
        if (! $pathField) abort(404);

        $user = User::where('role', 'api_user')->findOrFail($id);
        $path = $user->{$pathField};

        if (! $path || ! Storage::disk('private')->exists($path)) {
            abort(404, 'Document not found.');
        }

        $file     = Storage::disk('private')->get($path);
        $mimeType = Storage::disk('private')->getAdapter()
            ? mime_content_type(Storage::disk('private')->path($path))
            : 'application/octet-stream';

        return response($file, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . basename($path) . '"');
    }

    /** GET /api/v1/employee/sellers/{id} */
    public function show(int $id): JsonResponse
    {
        $user = User::where('role', 'api_user')->findOrFail($id);

        $wallet       = DB::table('wallets')->where('user_id', $id)->first();
        $integration  = SellerIntegrationRequest::where('user_id', $id)->latest()->first();
        $apiKey       = ApiKey::where('user_id', $id)->where('is_active', true)->latest()->first();
        $recentTxns   = DB::table('recharge_transactions')
            ->where('user_id', $id)->latest()->limit(10)
            ->get(['id', 'mobile', 'operator_code', 'amount', 'status', 'created_at']);
        $payments     = DB::table('seller_payment_requests')
            ->where('user_id', $id)->latest()->limit(5)->get();
        $txnStats     = DB::table('recharge_transactions')
            ->where('user_id', $id)
            ->selectRaw('COUNT(*) as total, SUM(amount) as total_amount, SUM(CASE WHEN status="success" THEN 1 ELSE 0 END) as success_count')
            ->first();

        return response()->json([
            'data' => [
                'user'           => $user,
                'wallet_balance' => $wallet ? (float) $wallet->balance : 0.0,
                'integration'    => $integration,
                'api_key'        => $apiKey ? ['prefix' => $apiKey->key_prefix, 'scopes' => $apiKey->scopes, 'last_used' => $apiKey->last_used_at] : null,
                'txn_stats'      => $txnStats,
                'recent_txns'    => $recentTxns,
                'recent_payments'=> $payments,
            ],
        ]);
    }

    /** POST /api/v1/employee/sellers/{id}/approve */
    public function approve(Request $request, int $id): JsonResponse
    {
        $user = User::where('role', 'api_user')->findOrFail($id);
        $user->update([
            'status'          => 'active',
            'approval_status' => 'approved',
        ]);

        // Send email notification to the user
        try {
            $user->notify(new RegistrationApprovedNotification());
        } catch (\Throwable $e) {
            // Log but don't fail the approval if email fails
            \Illuminate\Support\Facades\Log::warning("Approval notification failed for user #{$id}: " . $e->getMessage());
        }

        ActivityLogger::log('admin.seller_approved', "Seller #{$id} ({$user->email}) approved", null,
            ['seller_id' => $id], null, $request);

        return response()->json(['message' => 'Seller account approved. Notification sent to user.']);
    }

    /** POST /api/v1/employee/sellers/{id}/reject */
    public function reject(Request $request, int $id): JsonResponse
    {
        $request->validate(['notes' => ['sometimes', 'nullable', 'string', 'max:500']]);

        $user = User::where('role', 'api_user')->findOrFail($id);
        $user->update([
            'status'          => 'suspended',
            'approval_status' => 'rejected',
        ]);

        ActivityLogger::log('admin.seller_rejected', "Seller #{$id} ({$user->email}) rejected", null,
            ['seller_id' => $id, 'notes' => $request->notes], null, $request);

        return response()->json(['message' => 'Seller account rejected.']);
    }

    /** POST /api/v1/employee/sellers/integrations/{id}/decision */
    public function integrationDecision(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'action' => ['required', 'in:approve,reject'],
            'notes'  => ['sometimes', 'nullable', 'string', 'max:500'],
        ]);

        $ir = SellerIntegrationRequest::findOrFail($id);

        if ($request->action === 'approve') {
            $ir->update([
                'status'      => 'approved',
                'api_status'  => 'enabled',
                'admin_status'=> 'enabled',
                'admin_notes' => $request->notes,
                'approved_at' => now(),
            ]);

            // Auto-generate API key if seller doesn't have one
            if ($this->hasRequiredApiTokenSetup($ir) && ! ApiKey::where('user_id', $ir->user_id)->where('is_active', true)->exists()) {
                $rawKey = 'rk_' . Str::random(60);
                ApiKey::create([
                    'user_id'    => $ir->user_id,
                    'name'       => 'Seller API Key',
                    'key_prefix' => substr($rawKey, 0, 12),
                    'key_hash'   => hash('sha256', $rawKey),
                    'scopes'     => ['recharge:read', 'recharge:write', 'wallet:read'],
                    'ip_whitelist' => $this->parseAllowedIps($ir->allowed_ips),
                    'is_active'  => true,
                ]);
                // Note: raw key is NOT returned here — admin generates keys via API Keys page
            }

            ActivityLogger::log('admin.integration_approved', "Integration #{$id} approved", null,
                ['integration_id' => $id], null, $request);

            $message = $this->hasRequiredApiTokenSetup($ir)
                ? 'Integration request approved. API key auto-generated.'
                : 'Integration request approved. API key was not generated because callback URL or IP whitelist is missing.';

            return response()->json(['message' => $message]);
        }

        $ir->update([
            'status'      => 'rejected',
            'admin_notes' => $request->notes,
            'rejected_at' => now(),
        ]);

        ActivityLogger::log('admin.integration_rejected', "Integration #{$id} rejected", null,
            ['integration_id' => $id, 'notes' => $request->notes], null, $request);

        return response()->json(['message' => 'Integration request rejected.']);
    }

    /** POST /api/v1/employee/sellers/{id}/api-setting */
    public function updateApiSetting(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'field' => ['required', 'in:api_status,admin_status'],
            'value' => ['required', 'in:enabled,disabled'],
        ]);

        $seller = User::where('role', 'api_user')->findOrFail($id);
        $integration = SellerIntegrationRequest::where('user_id', $seller->id)->latest()->first();

        if (! $integration) {
            return response()->json(['message' => 'No API setting found for this seller yet.'], 422);
        }

        if ($integration->status !== 'approved') {
            return response()->json(['message' => 'API setting can be changed only after integration approval.'], 422);
        }

        $field = $request->string('field')->toString();
        $value = $request->string('value')->toString();

        $integration->update([$field => $value]);

        ActivityLogger::log(
            'admin.seller_api_setting_updated',
            "Seller #{$id} {$field} changed to {$value}",
            null,
            ['seller_id' => $id, 'field' => $field, 'value' => $value],
            null,
            $request
        );

        return response()->json([
            'message' => ucfirst(str_replace('_', ' ', $field)) . " updated to {$value}.",
            'data' => [
                'integration_id' => $integration->id,
                'api_status' => $integration->api_status,
                'admin_status' => $integration->admin_status,
            ],
        ]);
    }

    /** PATCH /api/v1/employee/sellers/{id} — edit seller profile */
    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'name'            => ['required', 'string', 'max:100'],
            'email'           => ['required', 'email', 'max:150', "unique:users,email,{$id}"],
            'mobile'          => ['required', 'digits:10', "unique:users,mobile,{$id}"],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'address'         => ['nullable', 'string', 'max:500'],
            'pincode'         => ['nullable', 'string', 'max:10'],
            'state'           => ['nullable', 'string', 'max:100'],
            'city'            => ['nullable', 'string', 'max:100'],
            'pan_no'          => ['nullable', 'string', 'max:20'],
            'aadhar_no'       => ['nullable', 'string', 'max:20'],
            'gst_number'      => ['nullable', 'string', 'max:20'],
            'contact_person'  => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::where('role', 'api_user')->findOrFail($id);
        $user->update($data);

        ActivityLogger::log('admin.seller_updated', "Seller #{$id} profile updated", null,
            ['seller_id' => $id], null, $request);

        return response()->json(['message' => 'Seller profile updated.']);
    }

    /** GET /api/v1/employee/sellers/{id}/commissions */
    public function commissions(int $id): JsonResponse
    {
        $seller = User::where('role', 'api_user')->findOrFail($id);

        $saved = SellerOperatorCommission::where('user_id', $seller->id)
            ->get()
            ->keyBy('operator_code');

        $operators = Operator::query()
            ->orderBy('category')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'category', 'commission_rate', 'is_active']);

        $rows = $operators->map(function (Operator $operator) use ($seller, $saved) {
            $row = $saved->get($operator->code);
            $commission = $row?->commission ?? $operator->commission_rate ?? $seller->commission_rate ?? 0;

            return [
                'operator_id'      => $operator->id,
                'operator_code'    => $operator->code,
                'operator_name'    => $operator->name,
                'category'         => $operator->category,
                'commission'       => (float) $commission,
                'commission_type'  => $row?->commission_type ?? 'percentage',
                'api1'             => $row?->api1 ?? '',
                'limit_txn'        => (int) ($row?->limit_txn ?? 0),
                'limit_amount'     => (float) ($row?->limit_amount ?? 0),
                'blocked_amounts'  => $row?->blocked_amounts ?? '',
                'is_active'        => $row?->is_active ?? true,
                'operator_active'  => (bool) $operator->is_active,
            ];
        });

        return response()->json(['data' => $rows]);
    }

    /** PUT /api/v1/employee/sellers/{id}/commissions */
    public function updateCommissions(Request $request, int $id): JsonResponse
    {
        $seller = User::where('role', 'api_user')->findOrFail($id);

        $data = $request->validate([
            'commissions'                    => ['required', 'array'],
            'commissions.*.operator_code'    => ['required', 'string', 'max:30', 'exists:operators,code'],
            'commissions.*.commission'       => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'commissions.*.commission_type'  => ['nullable', Rule::in(['percentage', 'flat'])],
            'commissions.*.api1'             => ['nullable', 'string', 'max:100'],
            'commissions.*.limit_txn'        => ['nullable', 'integer', 'min:0'],
            'commissions.*.limit_amount'     => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'commissions.*.blocked_amounts'  => ['nullable', 'string', 'max:500'],
            'commissions.*.is_active'        => ['nullable', 'boolean'],
        ]);

        $operators = Operator::whereIn('code', collect($data['commissions'])->pluck('operator_code')->all())
            ->get()
            ->keyBy('code');

        $now = now();
        $rows = collect($data['commissions'])->map(function (array $row) use ($seller, $operators, $now) {
            $operator = $operators->get($row['operator_code']);

            return [
                'user_id'         => $seller->id,
                'operator_id'     => $operator?->id,
                'operator_code'   => $row['operator_code'],
                'commission'      => (float) ($row['commission'] ?? 0),
                'commission_type' => $row['commission_type'] ?? 'percentage',
                'api1'            => $row['api1'] ?? null,
                'limit_txn'       => (int) ($row['limit_txn'] ?? 0),
                'limit_amount'    => (float) ($row['limit_amount'] ?? 0),
                'blocked_amounts' => $row['blocked_amounts'] ?? null,
                'is_active'       => array_key_exists('is_active', $row) ? (bool) $row['is_active'] : true,
                'created_at'      => $now,
                'updated_at'      => $now,
            ];
        })->all();

        if ($rows === []) {
            return response()->json(['message' => 'No commission settings to update.']);
        }

        SellerOperatorCommission::upsert(
            $rows,
            ['user_id', 'operator_code'],
            ['operator_id', 'commission', 'commission_type', 'api1', 'limit_txn', 'limit_amount', 'blocked_amounts', 'is_active', 'updated_at']
        );

        ActivityLogger::log('admin.seller_commissions_updated', "Seller #{$id} commission settings updated", null,
            ['seller_id' => $id, 'count' => count($rows)], null, $request);

        return response()->json(['message' => 'Commission settings updated.']);
    }

    /**
     * PUT /api/v1/employee/sellers/{id}/api-config/integration
     * Admin updates a seller's integration URLs and allowed IPs.
     */
    public function updateIntegration(Request $request, int $id): JsonResponse
    {
        $data = $request->validate($this->integrationRules());

        $integration = SellerIntegrationRequest::where('user_id', $id)->latest()->first();

        if (! $integration) {
            return response()->json(['message' => 'No integration request found for this seller.'], 404);
        }

        $ips = preg_split('/[\r\n,]+/', $data['allowed_ips']) ?: [];
        $ips = implode("\n", array_values(array_filter(array_map('trim', $ips))));

        $integration->update([
            'website_url'      => $data['website_url'],
            'callback_url'     => $data['callback_url'],
            'status_check_url' => $data['status_check_url'] ?? Arr::get($data, 'status_api.url'),
            'dispute_url'      => $data['dispute_url'] ?? Arr::get($data, 'dispute_api.url'),
            'allowed_ips'      => $ips,
            'recharge_api'     => $this->normalizeEndpointConfig($data['recharge_api'] ?? null, true),
            'status_api'       => $this->normalizeEndpointConfig($data['status_api'] ?? null, true),
            'balance_api'      => $this->normalizeEndpointConfig($data['balance_api'] ?? null, false),
            'dispute_api'      => $this->normalizeEndpointConfig($data['dispute_api'] ?? null, false),
            'callback_config'  => $this->normalizeCallbackConfig($data['callback_config'] ?? null),
            'op_code_map'      => $this->normalizeOpCodeMap($data['op_code_map'] ?? null),
        ]);

        ActivityLogger::log('admin.integration_updated', "Integration config updated for seller #{$id}", null,
            ['seller_id' => $id], null, $request);

        return response()->json(['message' => 'Integration config updated successfully.', 'data' => $integration]);
    }

    /**
     * POST /api/v1/employee/sellers/{id}/api-config/generate-key
     * Admin generates a new API key for a seller.
     */
    public function generateSellerApiKey(Request $request, int $id): JsonResponse
    {
        $seller = User::where('role', 'api_user')->findOrFail($id);
        $integration = SellerIntegrationRequest::where('user_id', $seller->id)
            ->where('status', 'approved')
            ->latest()
            ->first();

        if (! $integration || ! $this->hasRequiredApiTokenSetup($integration)) {
            throw ValidationException::withMessages([
                'api_key' => ['Callback URL and IP whitelist are required before generating the seller API token.'],
            ]);
        }

        $rawKey = 'rk_' . Str::random(60);
        $name   = 'Seller API Key';

        ApiKey::where('user_id', $seller->id)
            ->where('name', $name)
            ->update(['is_active' => false]);

        $apiKey = ApiKey::create([
            'user_id'      => $seller->id,
            'name'         => $name,
            'key_prefix'   => substr($rawKey, 0, 12),
            'key_hash'     => hash('sha256', $rawKey),
            'scopes'       => ['recharge:read', 'recharge:write', 'wallet:read'],
            'ip_whitelist' => $this->parseAllowedIps($integration?->allowed_ips),
            'is_active'    => true,
        ]);

        ActivityLogger::log('admin.api_key_generated', "API key generated for seller #{$id} ({$seller->email})", null,
            ['seller_id' => $id], null, $request);

        return response()->json([
            'message'    => 'API key generated. Store this key securely — it will not be shown again.',
            'api_key'    => $rawKey,
            'key_prefix' => $apiKey->key_prefix,
            'seller'     => ['id' => $seller->id, 'name' => $seller->name],
        ], 201);
    }

    private function parseAllowedIps(?string $raw): ?array
    {
        if (! $raw) {
            return null;
        }

        $ips = preg_split('/[\r\n,]+/', $raw) ?: [];
        $ips = array_values(array_filter(array_map('trim', $ips)));

        return $ips === [] ? null : $ips;
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
        return filled($integration->callback_url) && ! empty($this->parseAllowedIps($integration->allowed_ips));
    }

    /**
     * POST /api/v1/employee/sellers/{id}/login-as
     * Create an impersonation token for the seller — admin opens seller portal as them.
     */
    public function loginAs(Request $request, int $id): JsonResponse
    {
        $seller = User::where('role', 'api_user')
            ->where('status', 'active')
            ->findOrFail($id);

        // Revoke existing impersonation tokens
        $seller->tokens()->where('name', 'admin-impersonate')->delete();
        $token = $seller->createToken(
            'admin-impersonate',
            ['*'],
            now()->addHours(2)
        )->plainTextToken;

        ActivityLogger::log('admin.login_as_seller', "Admin impersonating seller #{$id} ({$seller->email})", null,
            ['seller_id' => $id], null, $request);

        return response()->json([
            'message'       => 'Impersonation token created.',
            'token'         => $token,
            'seller_portal' => url('/seller/dashboard'),
            'user'          => [
                'id'    => $seller->id,
                'name'  => $seller->name,
                'email' => $seller->email,
                'role'  => $seller->role,
                'status'=> $seller->status,
            ],
        ]);
    }
}
