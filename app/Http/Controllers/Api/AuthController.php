<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Services\WalletServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\ApiAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private readonly ApiAuthService      $authService,
        private readonly WalletServiceInterface $walletService,
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/v1/auth/register
    // ─────────────────────────────────────────────────────────────────────────

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'mobile'   => $request->mobile,
            'password' => $request->password,   // auto-hashed by User model cast
            'role'     => $request->input('role', 'retailer'),
            'status'   => 'active',
        ]);

        // Create a wallet automatically for every new user
        $this->walletService->getOrCreateWallet($user);

        $token = $this->authService->issueToken($user, $request->input('device_name', 'api'));

        ActivityLogger::log('auth.register', 'New user registered.', $user, [
            'role' => $user->role,
        ], $user->id, $request);

        return response()->json([
            'message' => 'Registration successful.',
            'token'   => $token,
            'user'    => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'mobile' => $user->mobile,
                'role'   => $user->role,
            ],
        ], 201);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/v1/auth/login
    // ─────────────────────────────────────────────────────────────────────────

    public function login(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            ActivityLogger::log('auth.login_failed', "Failed login: {$request->email}");

            throw ValidationException::withMessages([
                'email' => ['These credentials do not match our records.'],
            ]);
        }

        $user = Auth::user();

        if (! $user->isActive()) {
            Auth::logout();
            return response()->json(['message' => 'Account suspended.'], 403);
        }

        $token = $this->authService->issueToken($user, $request->device_name ?? 'api');

        ActivityLogger::log('auth.login', 'User logged in.', $user, [], $user->id, $request);

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        ActivityLogger::log('auth.logout', 'User logged out.', $request->user(), [], $request->user()->id, $request);
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    public function generateApiKey(Request $request): JsonResponse
    {
        $request->validate([
            'name'     => ['sometimes', 'string', 'max:100'],
            'scopes'   => ['sometimes', 'array'],
            'scopes.*' => ['string', 'in:recharge:read,recharge:write,wallet:read,*'],
        ]);

        $name   = $request->input('name', 'API Key');
        $scopes = $request->input('scopes', ['recharge:write', 'recharge:read', 'wallet:read']);

        $rawKey = $this->authService->generateApiKey($request->user(), $name, $scopes);

        ActivityLogger::log('auth.api_key_generated', "API key generated: {$name}", $request->user(), [
            'name'   => $name,
            'scopes' => $scopes,
        ], $request->user()->id, $request);

        return response()->json([
            'message' => 'Store this key securely — it will not be shown again.',
            'api_key' => $rawKey,   // primary field
            'key'     => $rawKey,   // alias for JS compatibility
        ]);
    }
}
