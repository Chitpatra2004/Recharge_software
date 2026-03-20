<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\SellerIntegrationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiConfigController extends Controller
{
    /**
     * GET /api/v1/seller/api-config
     */
    public function config(Request $request): JsonResponse
    {
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
                'callback_url' => url('/api/v1/recharge/callback'),
                'api_key'      => $apiKey ? [
                    'id'         => $apiKey->id,
                    'prefix'     => $apiKey->key_prefix,
                    'scopes'     => $apiKey->scopes,
                    'last_used'  => $apiKey->last_used_at,
                    'created_at' => $apiKey->created_at,
                ] : null,
                'integration'  => $integration,
                'api_endpoint' => url('/api/v1/buyer/recharge'),
                'api_docs_url' => url('/seller/api-docs'),
            ],
        ]);
    }

    /**
     * POST /api/v1/seller/api-config/integration
     */
    public function submitIntegration(Request $request): JsonResponse
    {
        $data = $request->validate([
            'website_url'       => ['required', 'url', 'max:255'],
            'callback_url'      => ['required', 'url', 'max:255'],
            'site_username'     => ['sometimes', 'nullable', 'string', 'max:100'],
            'site_password_hint'=> ['sometimes', 'nullable', 'string', 'max:100'],
        ]);

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
            'site_username'     => $data['site_username'] ?? null,
            'site_password_hint'=> $data['site_password_hint'] ?? null,
            'status'            => 'pending',
        ]);

        return response()->json([
            'message' => 'Integration request submitted. Admin will review within 24 hours.',
        ], 201);
    }
}
