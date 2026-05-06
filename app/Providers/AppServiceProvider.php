<?php

namespace App\Providers;

use App\Events\RechargeCompleted;
use App\Events\RechargeFailed;
use App\Events\RechargeInitiated;
use App\Listeners\BustDashboardCache;
use App\Listeners\LogRechargeActivity;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;

class AppServiceProvider extends EventServiceProvider
{
    /**
     * Event → Listener mappings.
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        RechargeInitiated::class => [LogRechargeActivity::class, BustDashboardCache::class],
        RechargeCompleted::class => [LogRechargeActivity::class, BustDashboardCache::class],
        RechargeFailed::class    => [LogRechargeActivity::class, BustDashboardCache::class],
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        parent::boot();

        // Fix "Specified key was too long" on MySQL < 8.0 or utf8mb4 setups
        Schema::defaultStringLength(191);

        // Define authorization gate for admin-only API routes
        Gate::define('admin', fn ($user) => $user->isAdmin());

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Global API rate limiter — 60 req/min per user or IP
        RateLimiter::for('api', function (Request $request) {
            $apiKey = (string) $request->header('X-API-Key', '');
            $identity = $request->user()?->id
                ? 'user:' . $request->user()->id
                : ($apiKey !== '' ? 'key:' . hash('sha256', $apiKey) : 'ip:' . $request->ip());

            return Limit::perMinute(60)->by($identity)->response(fn () => response()->json([
                'message' => 'Too many requests. Please slow down.',
            ], 429));
        });

        // Strict recharge limiter — 10 recharge requests/min per user
        RateLimiter::for('recharge', function (Request $request) {
            $apiKey = (string) $request->header('X-API-Key', '');
            $identity = $request->user()?->id
                ? 'user:' . $request->user()->id
                : ($apiKey !== '' ? 'key:' . hash('sha256', $apiKey) : 'ip:' . $request->ip());

            return Limit::perMinute(10)->by($identity)->response(fn () => response()->json([
                'message' => 'Too many recharge requests. Please slow down.',
            ], 429));
        });

        RateLimiter::for('auth', function (Request $request) {
            $login = strtolower((string) ($request->input('email') ?: $request->input('mobile') ?: 'guest'));

            return [
                Limit::perMinute(5)->by('login:' . sha1($login . '|' . $request->ip())),
                Limit::perMinute(20)->by('auth-ip:' . $request->ip()),
            ];
        });

        RateLimiter::for('otp', function (Request $request) {
            return Limit::perMinute(5)->by('otp:' . $request->ip())->response(fn () => response()->json([
                'message' => 'Too many OTP attempts. Please wait before trying again.',
            ], 429));
        });

        RateLimiter::for('callback', function (Request $request) {
            return Limit::perMinute(120)->by('callback:' . $request->ip());
        });

        RateLimiter::for('upload', function (Request $request) {
            return Limit::perMinute(10)->by(
                $request->user()?->id ? 'user:' . $request->user()->id : 'ip:' . $request->ip()
            );
        });

        RateLimiter::for('money', function (Request $request) {
            return Limit::perMinute(5)->by(
                $request->user()?->id ? 'user:' . $request->user()->id : 'ip:' . $request->ip()
            )->response(fn () => response()->json([
                'message' => 'Too many financial requests. Please wait before trying again.',
            ], 429));
        });
    }
}
