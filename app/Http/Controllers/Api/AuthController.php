<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Services\WalletServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\ApiAuthService;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function __construct(
        private readonly ApiAuthService         $authService,
        private readonly WalletServiceInterface $walletService,
        private readonly OtpService             $otpService,
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/v1/auth/register   (multipart/form-data for document upload)
    // ─────────────────────────────────────────────────────────────────────────
    public function register(RegisterRequest $request): JsonResponse
    {
        // Handle document upload
        $documentPath = null;
        if ($request->hasFile('document') && $request->file('document')->isValid()) {
            $documentPath = $request->file('document')
                ->store('documents/' . date('Y/m'), 'private');
        }

        $user = User::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'mobile'        => $request->mobile,
            'password'      => $request->password,
            'role'          => $request->input('role', 'retailer'),
            'status'        => 'active',
            'document_path' => $documentPath,
        ]);

        $this->walletService->getOrCreateWallet($user);

        ActivityLogger::log('auth.register', 'New user registered.', $user, [
            'role'          => $user->role,
            'has_document'  => (bool) $documentPath,
        ], $user->id, $request);

        return response()->json([
            'message'  => 'Registration successful. Please login to continue.',
            'redirect' => '/user/login',
            'user'     => [
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
    // Supports: email or mobile + password
    // Returns: token directly (2FA disabled) OR requires_2fa + pending_token
    // ─────────────────────────────────────────────────────────────────────────
    public function login(LoginRequest $request): JsonResponse
    {
        $field = $request->loginField();
        $user  = User::where($field, $request->login)->first();

        // User not found
        if (! $user) {
            ActivityLogger::log('auth.login_failed', "Failed login: {$request->login}");
            return response()->json(['message' => 'User not found.'], 401);
        }

        // Wrong password
        if (! Hash::check($request->password, $user->password)) {
            ActivityLogger::log('auth.login_failed', "Wrong password: {$request->login}");
            return response()->json(['message' => 'Incorrect password.'], 401);
        }

        // Account suspended
        if (! $user->isActive()) {
            return response()->json(['message' => 'Your account has been suspended.'], 403);
        }

        // ── 2FA Required ───────────────────────────────────────────────────
        if ($user->two_factor_method !== 'none') {

            if ($user->two_factor_method === 'otp') {
                // Send OTP to registered mobile
                $result = $this->otpService->generate(
                    $user->mobile,
                    'login_2fa',
                    $user->id,
                    $request->ip()
                );

                return response()->json([
                    'requires_2fa'  => true,
                    'method'        => 'otp',
                    'pending_token' => $result['pending_token'],
                    'message'       => 'OTP sent to your registered mobile number.',
                    // DEV ONLY — remove in production
                    'debug_otp'     => config('app.debug') ? $result['otp'] : null,
                ]);
            }

            if ($user->two_factor_method === 'totp') {
                // Generate a pending token so the TOTP verify step can identify the user
                $result = $this->otpService->generate(
                    $user->mobile,
                    'login_2fa',
                    $user->id,
                    $request->ip()
                );

                return response()->json([
                    'requires_2fa'  => true,
                    'method'        => 'totp',
                    'pending_token' => $result['pending_token'],
                    'message'       => 'Please enter the code from your Authenticator app.',
                ]);
            }
        }

        // ── No 2FA — issue token directly ─────────────────────────────────
        $token = $this->authService->issueToken($user, $request->device_name ?? 'web');
        ActivityLogger::log('auth.login', 'User logged in.', $user, [], $user->id, $request);

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'mobile' => $user->mobile,
                'role'   => $user->role,
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
            'name' => $name, 'scopes' => $scopes,
        ], $request->user()->id, $request);

        return response()->json([
            'message' => 'Store this key securely — it will not be shown again.',
            'api_key' => $rawKey,
            'key'     => $rawKey,
        ]);
    }
}
