<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
    public function __construct(private readonly DashboardService $dashboard) {}

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
