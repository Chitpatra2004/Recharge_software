<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\SellerIntegrationRequest;
use App\Models\User;
use App\Notifications\RegistrationApprovedNotification;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SellerController extends Controller
{
    /** GET /api/v1/employee/sellers */
    public function index(Request $request): JsonResponse
    {
        $query = User::whereIn('role', ['api_user', 'retailer'])
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

            // Document flags for admin review
            $u->has_pan         = ! empty($u->pan_image_path);
            $u->has_gst         = ! empty($u->gst_certificate_path);
            $u->has_document    = ! empty($u->document_path);

            return $u;
        });

        // Summary stats
        $stats = DB::table('users')
            ->whereIn('role', ['api_user', 'retailer'])
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN approval_status = 'pending'  THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'active'            THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'suspended'         THEN 1 ELSE 0 END) as suspended
            ")
            ->first();

        return response()->json(['data' => $sellers, 'stats' => $stats]);
    }

    /** GET /api/v1/employee/sellers/{id} */
    public function show(int $id): JsonResponse
    {
        $user = User::whereIn('role', ['api_user', 'retailer'])->findOrFail($id);

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
        $user = User::whereIn('role', ['api_user', 'retailer'])->findOrFail($id);
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

        $user = User::whereIn('role', ['api_user', 'retailer'])->findOrFail($id);
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
                'admin_notes' => $request->notes,
                'approved_at' => now(),
            ]);

            // Auto-generate API key if seller doesn't have one
            if (! ApiKey::where('user_id', $ir->user_id)->where('is_active', true)->exists()) {
                $rawKey = 'rk_' . Str::random(60);
                ApiKey::create([
                    'user_id'    => $ir->user_id,
                    'name'       => 'Seller API Key',
                    'key_prefix' => substr($rawKey, 0, 12),
                    'key_hash'   => hash('sha256', $rawKey),
                    'scopes'     => ['recharge:read', 'recharge:write', 'wallet:read'],
                    'is_active'  => true,
                ]);
                // Note: raw key is NOT returned here — admin generates keys via API Keys page
            }

            ActivityLogger::log('admin.integration_approved', "Integration #{$id} approved", null,
                ['integration_id' => $id], null, $request);

            return response()->json(['message' => 'Integration request approved. API key auto-generated.']);
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

    /**
     * POST /api/v1/employee/sellers/{id}/login-as
     * Create an impersonation token for the seller — admin opens seller portal as them.
     */
    public function loginAs(Request $request, int $id): JsonResponse
    {
        $seller = User::where('role', 'api_user')->findOrFail($id);

        // Revoke existing impersonation tokens
        $seller->tokens()->where('name', 'admin-impersonate')->delete();
        $token = $seller->createToken('admin-impersonate')->plainTextToken;

        ActivityLogger::log('admin.login_as_seller', "Admin impersonating seller #{$id} ({$seller->email})", null,
            ['seller_id' => $id], null, $request);

        return response()->json([
            'message'       => 'Impersonation token created.',
            'token'         => $token,
            'seller_portal' => url('/seller/dashboard'),
            'user'          => ['id' => $seller->id, 'name' => $seller->name, 'email' => $seller->email],
        ]);
    }
}
