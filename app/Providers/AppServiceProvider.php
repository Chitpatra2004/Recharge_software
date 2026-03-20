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
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
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

        // Global API rate limiter — 60 req/min per user or IP
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(
                $request->user()?->id ?: $request->ip()
            );
        });

        // Strict recharge limiter — 10 recharge requests/min per user
        RateLimiter::for('recharge', function (Request $request) {
            return Limit::perMinute(10)->by(
                $request->user()?->id ?: $request->ip()
            )->response(fn () => response()->json([
                'message' => 'Too many recharge requests. Please slow down.',
            ], 429));
        });
    }
}
