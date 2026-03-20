<?php

use App\Exceptions\DuplicateTransactionException;
use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\OperatorUnavailableException;
use App\Exceptions\WalletFrozenException;
use App\Http\Middleware\ApiKeyAuth;
use App\Http\Middleware\ApiRequestLogger;
use App\Http\Middleware\DetectBruteForce;
use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\LogEmployeeActivity;
use App\Http\Middleware\SanitizeInput;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // ── Global middleware (every request) ─────────────────────────
        $middleware->append(SecurityHeaders::class);

        // ── API group middleware ───────────────────────────────────────
        // Order matters: ForceJson first (changes Accept header),
        // then SanitizeInput (cleans body), then ApiKeyAuth (auth check)
        $middleware->prependToGroup('api', ForceJsonResponse::class);
        $middleware->appendToGroup('api', SanitizeInput::class);
        $middleware->prependToGroup('api', ApiKeyAuth::class);

        // ── Named middleware aliases ───────────────────────────────────
        $middleware->alias([
            'api.key'       => ApiKeyAuth::class,
            'log.api'       => ApiRequestLogger::class,
            'sanitize'      => SanitizeInput::class,
            'brute.force'   => DetectBruteForce::class,
            'log.employee'  => LogEmployeeActivity::class,
            'seller.role'   => \App\Http\Middleware\SellerRole::class,
        ]);

        // Trust proxies for correct IP detection behind load balancers
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Return JSON for known domain exceptions on API requests
        $exceptions->render(function (InsufficientBalanceException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
        });

        $exceptions->render(function (WalletFrozenException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 403);
            }
        });

        $exceptions->render(function (OperatorUnavailableException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 503);
            }
        });

        $exceptions->render(function (DuplicateTransactionException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message'        => 'Duplicate transaction.',
                    'transaction_id' => $e->existing->id,
                    'status'         => $e->existing->status,
                ], 409);
            }
        });
    })->create();
