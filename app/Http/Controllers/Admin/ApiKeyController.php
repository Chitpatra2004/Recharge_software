<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * ApiKeyController — employee-accessible API key management.
 *
 * Endpoints (under auth:employee guard):
 *   GET    /api/v1/employee/api-keys          — list all keys (paginated)
 *   POST   /api/v1/employee/api-keys          — generate new key for a user
 *   DELETE /api/v1/employee/api-keys/{id}     — revoke (soft-delete) a key
 */
class ApiKeyController extends Controller
{
    /**
     * GET /api/v1/employee/users/search?q=...
     * Quick user search for the API key generation modal.
     */
    public function searchUsers(Request $request): JsonResponse
    {
        $q = trim($request->input('q', $request->input('search', '')));

        $users = User::where('status', 'active')
            ->when($q, fn ($query) =>
                $query->where(fn ($sub) =>
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('mobile', 'like', "%{$q}%")
                )
            )
            ->select('id', 'name', 'email', 'mobile', 'role')
            ->orderBy('name')
            ->limit(15)
            ->get();

        return response()->json(['data' => $users]);
    }

    /**
     * List all API keys with associated user info.
     */
    public function index(Request $request): JsonResponse
    {
        $keys = ApiKey::with('user:id,name,email,role,status')
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return response()->json(['data' => $keys]);
    }

    /**
     * Generate a new API key for the specified user.
     * Returns the raw key once — never stored in plain text.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id'  => ['required', 'integer', 'exists:users,id'],
            'name'     => ['required', 'string', 'max:100'],
            'scopes'   => ['sometimes', 'array'],
            'scopes.*' => ['string', 'in:recharge:read,recharge:write,wallet:read,*'],
        ]);

        $user   = User::findOrFail($validated['user_id']);
        $scopes = $validated['scopes'] ?? ['recharge:write', 'recharge:read', 'wallet:read'];

        $rawKey = 'rk_' . Str::random(60);

        // Deactivate any existing key with the same name for this user
        ApiKey::where('user_id', $user->id)
            ->where('name', $validated['name'])
            ->update(['is_active' => false]);

        $apiKey = ApiKey::create([
            'user_id'    => $user->id,
            'name'       => $validated['name'],
            'key_prefix' => substr($rawKey, 0, 12),
            'key_hash'   => hash('sha256', $rawKey),
            'scopes'     => $scopes,
            'is_active'  => true,
        ]);

        ActivityLogger::log(
            'admin.api_key_generated',
            "API key '{$validated['name']}' generated for user #{$user->id}",
            $user,
            ['name' => $validated['name'], 'scopes' => $scopes],
            null,
            $request
        );

        return response()->json([
            'message'    => 'Store this key securely — it will not be shown again.',
            'key'        => $rawKey,
            'api_key'    => $rawKey,
            'id'         => $apiKey->id,
            'name'       => $apiKey->name,
            'key_prefix' => $apiKey->key_prefix,
            'scopes'     => $apiKey->scopes,
            'user'       => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
        ], 201);
    }

    /**
     * Revoke (soft-delete) an API key by ID.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $apiKey = ApiKey::findOrFail($id);
        $apiKey->delete();

        ActivityLogger::log(
            'admin.api_key_revoked',
            "API key #{$id} ({$apiKey->name}) revoked",
            null,
            ['key_id' => $id, 'name' => $apiKey->name],
            null,
            $request
        );

        return response()->json(['message' => 'API key revoked successfully.']);
    }
}
