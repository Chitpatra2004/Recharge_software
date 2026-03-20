<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/v1/seller/auth/register
     * Submit registration — admin must approve before login is allowed.
     */
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:100'],
            'email'        => ['required', 'email', 'max:191', 'unique:users,email'],
            'mobile'       => ['required', 'string', 'max:15', 'unique:users,mobile'],
            'password'     => ['required', 'string', 'min:8', 'confirmed'],
            'company_name' => ['sometimes', 'nullable', 'string', 'max:150'],
            'gstin'        => ['sometimes', 'nullable', 'string', 'max:15'],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'mobile'   => $data['mobile'],
            'password' => $data['password'],
            'role'     => 'api_user',
            'status'   => 'inactive',   // pending admin approval
        ]);

        // Create wallet
        app(\App\Contracts\Services\WalletServiceInterface::class)->getOrCreateWallet($user);

        ActivityLogger::log(
            'seller.register',
            "New seller registration: {$user->email}",
            $user,
            ['company_name' => $data['company_name'] ?? null, 'gstin' => $data['gstin'] ?? null],
            $user->id,
            $request
        );

        return response()->json([
            'message' => 'Registration submitted successfully. Admin will review and approve your account within 24 hours.',
        ], 201);
    }

    /**
     * POST /api/v1/seller/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            ActivityLogger::log('seller.login_failed', "Failed seller login: {$request->email}");
            throw ValidationException::withMessages([
                'email' => ['These credentials do not match our records.'],
            ]);
        }

        $user = Auth::user();

        if ($user->role !== 'api_user') {
            Auth::logout();
            return response()->json(['message' => 'This portal is for API sellers only.'], 403);
        }

        if ($user->status === 'inactive') {
            Auth::logout();
            return response()->json([
                'message' => 'Account pending admin approval. You will be notified once approved.',
                'status'  => 'pending',
            ], 403);
        }

        if ($user->status === 'suspended') {
            Auth::logout();
            return response()->json([
                'message' => 'Account suspended. Please contact support.',
                'status'  => 'suspended',
            ], 403);
        }

        // Revoke old seller-portal tokens to avoid accumulation
        $user->tokens()->where('name', 'seller-portal')->delete();
        $token = $user->createToken('seller-portal')->plainTextToken;

        ActivityLogger::log('seller.login', 'Seller logged in.', $user, [], $user->id, $request);

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'mobile' => $user->mobile,
                'role'   => $user->role,
                'status' => $user->status,
            ],
        ]);
    }

    /**
     * POST /api/v1/seller/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully.']);
    }

    /**
     * GET /api/v1/seller/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        return response()->json([
            'data' => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'mobile' => $user->mobile,
                'role'   => $user->role,
                'status' => $user->status,
            ],
        ]);
    }
}
