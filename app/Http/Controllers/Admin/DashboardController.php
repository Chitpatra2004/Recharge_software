<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OperatorRoute;
use App\Services\DashboardService;
use App\Services\GenericApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * DashboardController — real-time analytics endpoints for the admin panel.
 *
 * Polling strategy (returned in every response as poll_interval_seconds):
 *
 *   Endpoint              Poll interval   Use case
 *   ──────────────────    ─────────────   ──────────────────────────────────
 *   GET /dashboard        on page load    First render — all sections at once
 *   GET /dashboard/summary      30s       KPI cards (total, success rate, etc.)
 *   GET /dashboard/live         10s       Live transaction feed + sparkline
 *   GET /dashboard/operators    60s       Operator health cards
 *   GET /dashboard/gateway      60s       Wallet topup + API error rate
 *   GET /dashboard/complaints   60s       Pending complaints panel
 *   GET /dashboard/chart        5min      Hourly + weekly chart data
 *   DELETE /dashboard/cache     —         Manual cache flush (admin action)
 */
class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboard,
        private readonly GenericApiService $apiService,
    ) {}

    // ─────────────────────────────────────────────────────────────────────
    // GET /api/v1/admin/dashboard
    // Full dashboard — all sections in one call.
    // Use on initial page load; then switch to individual polling endpoints.
    // ─────────────────────────────────────────────────────────────────────

    public function index(): JsonResponse
    {
        return response()->json($this->dashboard->fullDashboard());
    }

    // ─────────────────────────────────────────────────────────────────────
    // GET /api/v1/admin/dashboard/summary
    // KPI cards — poll every 30 seconds
    // ─────────────────────────────────────────────────────────────────────

    public function summary(): JsonResponse
    {
        return response()->json([
            'section'              => 'summary',
            'data'                 => $this->dashboard->summary(),
            'poll_interval_seconds'=> DashboardService::TTL_SUMMARY,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // GET /api/v1/admin/dashboard/live
    // Live transaction feed + per-minute sparkline — poll every 10 seconds
    // ─────────────────────────────────────────────────────────────────────

    public function live(): JsonResponse
    {
        return response()->json([
            'section'              => 'live',
            'data'                 => $this->dashboard->liveTransactionFeed(),
            'poll_interval_seconds'=> DashboardService::TTL_LIVE,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // GET /api/v1/admin/dashboard/operators
    // Operator performance cards — poll every 60 seconds
    // ─────────────────────────────────────────────────────────────────────

    public function operators(): JsonResponse
    {
        return response()->json([
            'section'              => 'operators',
            'data'                 => $this->dashboard->operatorPerformance(),
            'poll_interval_seconds'=> DashboardService::TTL_OPERATORS,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // GET /api/v1/admin/dashboard/gateway
    // Wallet topup stats + API error rate — poll every 60 seconds
    // ─────────────────────────────────────────────────────────────────────

    public function gateway(): JsonResponse
    {
        return response()->json([
            'section'              => 'gateway',
            'data'                 => $this->dashboard->gatewayPerformance(),
            'poll_interval_seconds'=> DashboardService::TTL_GATEWAY,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // GET /api/v1/admin/dashboard/complaints
    // Pending complaints panel — poll every 60 seconds
    // ─────────────────────────────────────────────────────────────────────

    public function complaints(): JsonResponse
    {
        return response()->json([
            'section'              => 'complaints',
            'data'                 => $this->dashboard->pendingComplaints(),
            'poll_interval_seconds'=> DashboardService::TTL_COMPLAINTS,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // GET /api/v1/admin/dashboard/chart
    // Hourly + weekly chart data — poll every 5 minutes
    // Accepts: ?type=hourly|weekly|both (default: both)
    // ─────────────────────────────────────────────────────────────────────

    public function chart(Request $request): JsonResponse
    {
        $type = $request->input('type', 'both');

        $data = match($type) {
            'hourly' => ['hourly' => $this->dashboard->hourlyChart()],
            'weekly' => ['weekly' => $this->dashboard->weeklyChart()],
            default  => [
                'hourly' => $this->dashboard->hourlyChart(),
                'weekly' => $this->dashboard->weeklyChart(),
            ],
        };

        return response()->json([
            'section'              => 'chart',
            'data'                 => $data,
            'poll_interval_seconds'=> DashboardService::TTL_CHART_H,
        ]);
    }

    public function coldpayMobikwikBalance(Request $request): JsonResponse
    {
        $route = OperatorRoute::query()
            ->where(function ($q) {
                $q->where('api_provider', 'ColdPay Mobikwik')
                  ->orWhere('name', 'like', '%ColdPay Mobikwik%')
                  ->orWhere('api_provider', 'like', '%Mobikwik%');
            })
            ->orderByDesc('is_active')
            ->orderBy('id')
            ->first();

        if (! $route) {
            return response()->json(['message' => 'ColdPay Mobikwik API provider is not configured.'], 404);
        }

        $cfg = $route->api_config ?? [];
        $checkedAt = $cfg['balance_checked_at'] ?? null;
        if (! $request->boolean('refresh') && isset($cfg['balance']) && $checkedAt) {
            $checkedAtTime = Carbon::parse($checkedAt);
            if ($checkedAtTime->greaterThanOrEqualTo(now()->subMinutes(5))) {
                return response()->json([
                    'provider' => $route->api_provider,
                    'route_id' => $route->id,
                    'balance' => $cfg['balance'],
                    'checked_at' => $checkedAt,
                    'cached' => true,
                ]);
            }
        }

        $result = $this->apiService->balance($route);
        if (! ($result['success'] ?? false)) {
            return response()->json(['message' => $result['error'] ?? 'Balance check failed.'], 502);
        }

        $cfg['balance'] = $result['balance'];
        $cfg['balance_checked_at'] = now()->toDateTimeString();
        $route->update(['api_config' => $cfg]);

        return response()->json([
            'provider' => $route->api_provider,
            'route_id' => $route->id,
            'balance' => $result['balance'],
            'checked_at' => $cfg['balance_checked_at'],
            'cached' => false,
            'raw' => $result['raw'] ?? [],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // DELETE /api/v1/admin/dashboard/cache
    // Manually flush all dashboard caches (admin action)
    // Use after bulk data imports or manual DB corrections.
    // ─────────────────────────────────────────────────────────────────────

    public function flushCache(): JsonResponse
    {
        DashboardService::bustAll();

        return response()->json([
            'message'    => 'Dashboard cache flushed successfully.',
            'flushed_at' => now()->toIso8601String(),
            'keys'       => array_values(DashboardService::KEYS),
        ]);
    }
}
