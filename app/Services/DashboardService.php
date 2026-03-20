<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * DashboardService — all real-time analytics queries with layered caching.
 *
 * ┌─────────────────────────────────────────────────────────────────────┐
 * │  CACHE STRATEGY                                                      │
 * │                                                                      │
 * │  Key                    TTL   Bust trigger                          │
 * │  ─────────────────────  ────  ──────────────────────────────────    │
 * │  dashboard:summary       60s  RechargeInitiated/Completed/Failed    │
 * │  dashboard:live          10s  RechargeInitiated/Completed/Failed    │
 * │  dashboard:operators    120s  RechargeCompleted/Failed              │
 * │  dashboard:gateway       60s  RechargeCompleted/Failed              │
 * │  dashboard:complaints    60s  complaint.created/updated             │
 * │  dashboard:chart:hourly 300s  time-based only (5-min is fine)       │
 * │  dashboard:chart:weekly 1800s time-based only (30-min is fine)      │
 * │                                                                      │
 * │  Cache driver: file (local) / redis (production)                    │
 * │  Both drivers use identical API — no code change needed.            │
 * └─────────────────────────────────────────────────────────────────────┘
 */
class DashboardService
{
    // ── Cache TTL constants (seconds) ──────────────────────────────────────
    const TTL_SUMMARY   =   60;
    const TTL_LIVE      =   10;
    const TTL_OPERATORS =  120;
    const TTL_GATEWAY   =   60;
    const TTL_COMPLAINTS=   60;
    const TTL_CHART_H   =  300;  // hourly chart
    const TTL_CHART_W   = 1800;  // weekly chart

    // ── Cache key registry (used by BustDashboardCache listener) ──────────
    const KEYS = [
        'summary'        => 'dashboard:summary',
        'live'           => 'dashboard:live',
        'operators'      => 'dashboard:operators',
        'gateway'        => 'dashboard:gateway',
        'complaints'     => 'dashboard:complaints',
        'chart_hourly'   => 'dashboard:chart:hourly',
        'chart_weekly'   => 'dashboard:chart:weekly',
    ];

    // ─────────────────────────────────────────────────────────────────────
    // 1 · SUMMARY — KPI cards (busted on every transaction event)
    // ─────────────────────────────────────────────────────────────────────

    public function summary(): array
    {
        return Cache::remember(self::KEYS['summary'], self::TTL_SUMMARY, function () {

            $today     = today();
            $todayEnd  = now();

            // ── Users ────────────────────────────────────────────────────
            $userStats = DB::table('users')
                ->whereNull('deleted_at')
                ->selectRaw("
                    COUNT(*)                                               AS total,
                    SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) AS new_today,
                    SUM(CASE WHEN status = 'active'            THEN 1 ELSE 0 END) AS active,
                    SUM(CASE WHEN status = 'suspended'         THEN 1 ELSE 0 END) AS suspended,
                    SUM(CASE WHEN role = 'retailer'            THEN 1 ELSE 0 END) AS retailers,
                    SUM(CASE WHEN role = 'distributor'         THEN 1 ELSE 0 END) AS distributors,
                    SUM(CASE WHEN role = 'api_user'            THEN 1 ELSE 0 END) AS api_users
                ")
                ->first();

            // ── Transactions today ───────────────────────────────────────
            // Single scan — all status counts in one query
            $txStats = DB::table('recharge_transactions')
                ->whereDate('created_at', $today)
                ->whereNull('deleted_at')
                ->selectRaw("
                    COUNT(*)                                                         AS total,
                    SUM(CASE WHEN status = 'success'    THEN 1 ELSE 0 END)          AS success,
                    SUM(CASE WHEN status = 'failed'     THEN 1 ELSE 0 END)          AS failed,
                    SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END)          AS processing,
                    SUM(CASE WHEN status = 'queued'     THEN 1 ELSE 0 END)          AS queued,
                    SUM(CASE WHEN status = 'refunded'   THEN 1 ELSE 0 END)          AS refunded,
                    SUM(CASE WHEN status = 'success'    THEN amount ELSE 0 END)     AS success_amount,
                    SUM(CASE WHEN status = 'failed'     THEN amount ELSE 0 END)     AS failed_amount,
                    SUM(amount)                                                      AS total_amount,
                    SUM(commission)                                                  AS total_commission,
                    ROUND(AVG(amount), 2)                                            AS avg_amount,
                    ROUND(
                        100.0 * SUM(CASE WHEN status='success' THEN 1 ELSE 0 END) / NULLIF(COUNT(*),0),
                        2
                    )                                                                AS success_rate_pct
                ")
                ->first();

            // ── Yesterday comparison (for delta arrows) ──────────────────
            $yestStats = DB::table('recharge_transactions')
                ->whereDate('created_at', today()->subDay())
                ->whereNull('deleted_at')
                ->selectRaw("
                    COUNT(*)                                               AS total,
                    SUM(CASE WHEN status='success' THEN 1 ELSE 0 END)     AS success,
                    SUM(CASE WHEN status='success' THEN amount ELSE 0 END) AS success_amount,
                    ROUND(
                        100.0 * SUM(CASE WHEN status='success' THEN 1 ELSE 0 END) / NULLIF(COUNT(*),0),
                        2
                    )                                                      AS success_rate_pct
                ")
                ->first();

            // ── Wallet pool ──────────────────────────────────────────────
            $walletStats = DB::table('wallets')
                ->selectRaw("
                    SUM(balance)            AS total_balance,
                    SUM(reserved_balance)   AS total_reserved,
                    COUNT(*)                AS total_wallets,
                    SUM(CASE WHEN status='frozen' THEN 1 ELSE 0 END) AS frozen_count,
                    SUM(CASE WHEN balance <= 0    THEN 1 ELSE 0 END) AS zero_balance_count
                ")
                ->first();

            // ── System health ────────────────────────────────────────────
            $queueDepth  = DB::table('jobs')->count();
            $failedJobs  = DB::table('failed_jobs')->count();
            $activeApiKeys = DB::table('api_keys')
                ->where('is_active', true)
                ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->count();

            // ── Build delta indicators (today vs yesterday) ───────────────
            $deltaTotal   = $this->delta($txStats->total,           $yestStats->total);
            $deltaSuccess = $this->delta($txStats->success_rate_pct,$yestStats->success_rate_pct);
            $deltaRevenue = $this->delta($txStats->success_amount,  $yestStats->success_amount);

            return [
                'users' => [
                    'total'        => (int) $userStats->total,
                    'new_today'    => (int) $userStats->new_today,
                    'active'       => (int) $userStats->active,
                    'suspended'    => (int) $userStats->suspended,
                    'retailers'    => (int) $userStats->retailers,
                    'distributors' => (int) $userStats->distributors,
                    'api_users'    => (int) $userStats->api_users,
                ],
                'transactions' => [
                    'total'           => (int)   $txStats->total,
                    'success'         => (int)   $txStats->success,
                    'failed'          => (int)   $txStats->failed,
                    'processing'      => (int)   $txStats->processing,
                    'queued'          => (int)   $txStats->queued,
                    'refunded'        => (int)   $txStats->refunded,
                    'pending'         => (int)   ($txStats->queued + $txStats->processing),
                    'success_rate_pct'=> (float) ($txStats->success_rate_pct ?? 0),
                    'total_amount'    => (float) ($txStats->total_amount     ?? 0),
                    'success_amount'  => (float) ($txStats->success_amount   ?? 0),
                    'failed_amount'   => (float) ($txStats->failed_amount    ?? 0),
                    'total_commission'=> (float) ($txStats->total_commission ?? 0),
                    'avg_amount'      => (float) ($txStats->avg_amount       ?? 0),
                    'delta' => [
                        'total_pct'       => $deltaTotal,
                        'success_rate_pct'=> $deltaSuccess,
                        'revenue_pct'     => $deltaRevenue,
                    ],
                ],
                'wallet' => [
                    'total_balance'       => (float) ($walletStats->total_balance    ?? 0),
                    'total_reserved'      => (float) ($walletStats->total_reserved   ?? 0),
                    'available_pool'      => (float) (($walletStats->total_balance ?? 0) - ($walletStats->total_reserved ?? 0)),
                    'total_wallets'       => (int)   ($walletStats->total_wallets    ?? 0),
                    'frozen_count'        => (int)   ($walletStats->frozen_count     ?? 0),
                    'zero_balance_count'  => (int)   ($walletStats->zero_balance_count ?? 0),
                ],
                'system' => [
                    'queue_depth'    => (int) $queueDepth,
                    'failed_jobs'    => (int) $failedJobs,
                    'active_api_keys'=> (int) $activeApiKeys,
                    'health'         => $failedJobs > 10 ? 'warning' : ($queueDepth > 100 ? 'busy' : 'healthy'),
                ],
                '_meta' => [
                    'cached_at'   => now()->toIso8601String(),
                    'ttl_seconds' => self::TTL_SUMMARY,
                    'cache_key'   => self::KEYS['summary'],
                ],
            ];
        });
    }

    // ─────────────────────────────────────────────────────────────────────
    // 2 · LIVE TRANSACTION FEED (10-second TTL — near real-time)
    //     Shows the last 30 transactions with masked mobile numbers
    // ─────────────────────────────────────────────────────────────────────

    public function liveTransactionFeed(): array
    {
        return Cache::remember(self::KEYS['live'], self::TTL_LIVE, function () {

            $transactions = DB::table('recharge_transactions as rt')
                ->select([
                    'rt.id',
                    'rt.operator_code',
                    'rt.recharge_type',
                    'rt.amount',
                    'rt.status',
                    'rt.retry_count',
                    'rt.created_at',
                    'u.name as user_name',
                    // Mask mobile: show first 4 and last 2 digits only (XXXXXX6789 → 9876XXXX89)
                    DB::raw("CONCAT(LEFT(rt.mobile,4), REPEAT('X', CHAR_LENGTH(rt.mobile)-6), RIGHT(rt.mobile,2)) AS mobile_masked"),
                ])
                ->join('users as u', 'u.id', '=', 'rt.user_id')
                ->where('rt.created_at', '>=', now()->subMinutes(30)) // last 30 min
                ->whereNull('rt.deleted_at')
                ->orderByDesc('rt.created_at')
                ->limit(30)
                ->get();

            // ── Per-minute volume for sparkline (last 15 minutes) ────────
            $sparkline = DB::table('recharge_transactions')
                ->selectRaw("
                    DATE_FORMAT(created_at, '%H:%i') AS minute,
                    COUNT(*)                          AS total,
                    SUM(CASE WHEN status='success' THEN 1 ELSE 0 END) AS success
                ")
                ->where('created_at', '>=', now()->subMinutes(15))
                ->whereNull('deleted_at')
                ->groupByRaw("DATE_FORMAT(created_at, '%H:%i')")
                ->orderByRaw("DATE_FORMAT(created_at, '%H:%i')")
                ->get();

            return [
                'feed'      => $transactions,
                'sparkline' => $sparkline,
                '_meta'     => [
                    'cached_at'   => now()->toIso8601String(),
                    'ttl_seconds' => self::TTL_LIVE,
                ],
            ];
        });
    }

    // ─────────────────────────────────────────────────────────────────────
    // 3 · OPERATOR PERFORMANCE (busted on every completed/failed txn)
    // ─────────────────────────────────────────────────────────────────────

    public function operatorPerformance(): array
    {
        return Cache::remember(self::KEYS['operators'], self::TTL_OPERATORS, function () {

            // ── Per-operator stats today ─────────────────────────────────
            $operators = DB::table('recharge_transactions as rt')
                ->select([
                    'rt.operator_code',
                    'o.name  as operator_name',
                    'o.category',
                    'o.is_active',
                ])
                ->selectRaw("
                    COUNT(*)                                                         AS total,
                    SUM(CASE WHEN rt.status='success'    THEN 1 ELSE 0 END)         AS success,
                    SUM(CASE WHEN rt.status='failed'     THEN 1 ELSE 0 END)         AS failed,
                    SUM(CASE WHEN rt.status IN ('queued','processing') THEN 1 ELSE 0 END) AS pending,
                    SUM(CASE WHEN rt.status='success'    THEN rt.amount ELSE 0 END) AS success_amount,
                    SUM(rt.amount)                                                   AS total_amount,
                    ROUND(AVG(rt.amount), 2)                                         AS avg_amount,
                    ROUND(
                        100.0 * SUM(CASE WHEN rt.status='success' THEN 1 ELSE 0 END) / NULLIF(COUNT(*),0),
                        2
                    )                                                                AS success_rate_pct,
                    MAX(rt.created_at)                                               AS last_transaction_at
                ")
                ->leftJoin('operators as o', 'o.code', '=', 'rt.operator_code')
                ->whereDate('rt.created_at', today())
                ->whereNull('rt.deleted_at')
                ->groupBy('rt.operator_code', 'o.name', 'o.category', 'o.is_active')
                ->orderByDesc('total')
                ->get();

            // ── Route-level API performance (avg call duration) ──────────
            $routePerf = DB::table('recharge_attempts as ra')
                ->select(['or_.operator_code', 'or_.name as route_name', 'or_.api_provider'])
                ->selectRaw("
                    COUNT(*)                                                    AS attempts,
                    SUM(CASE WHEN ra.status='success' THEN 1 ELSE 0 END)       AS successes,
                    ROUND(AVG(ra.duration_ms), 0)                               AS avg_ms,
                    MAX(ra.duration_ms)                                         AS max_ms,
                    MIN(ra.duration_ms)                                         AS min_ms,
                    SUM(CASE WHEN ra.duration_ms > 10000 THEN 1 ELSE 0 END)    AS slow_count,
                    ROUND(
                        100.0 * SUM(CASE WHEN ra.status='success' THEN 1 ELSE 0 END) / NULLIF(COUNT(*),0),
                        2
                    )                                                           AS success_rate_pct
                ")
                ->join('operator_routes as or_', 'or_.id', '=', 'ra.operator_route_id')
                ->whereDate('ra.created_at', today())
                ->groupBy('or_.operator_code', 'or_.name', 'or_.api_provider')
                ->orderByDesc('attempts')
                ->get();

            // ── Operator health flags ────────────────────────────────────
            $operatorsWithHealth = $operators->map(function ($op) {
                $rate = (float) $op->success_rate_pct;
                $op->health = match(true) {
                    $rate >= 95 => 'excellent',
                    $rate >= 85 => 'good',
                    $rate >= 70 => 'degraded',
                    default     => 'critical',
                };
                return $op;
            });

            return [
                'operators'   => $operatorsWithHealth,
                'routes'      => $routePerf,
                '_meta' => [
                    'cached_at'   => now()->toIso8601String(),
                    'ttl_seconds' => self::TTL_OPERATORS,
                ],
            ];
        });
    }

    // ─────────────────────────────────────────────────────────────────────
    // 4 · PAYMENT GATEWAY / WALLET PERFORMANCE
    // ─────────────────────────────────────────────────────────────────────

    public function gatewayPerformance(): array
    {
        return Cache::remember(self::KEYS['gateway'], self::TTL_GATEWAY, function () {

            // ── Wallet transactions today (topups & debits) ──────────────
            $walletToday = DB::table('wallet_transactions')
                ->whereDate('created_at', today())
                ->selectRaw("
                    SUM(CASE WHEN type='credit'   THEN 1 ELSE 0 END)          AS topup_count,
                    SUM(CASE WHEN type='credit'   THEN amount ELSE 0 END)      AS topup_amount,
                    SUM(CASE WHEN type='debit'    THEN 1 ELSE 0 END)           AS debit_count,
                    SUM(CASE WHEN type='debit'    THEN amount ELSE 0 END)      AS debit_amount,
                    SUM(CASE WHEN type='reversal' THEN 1 ELSE 0 END)           AS reversal_count,
                    SUM(CASE WHEN type='reversal' THEN amount ELSE 0 END)      AS reversal_amount,
                    SUM(CASE WHEN type='reserve'  THEN 1 ELSE 0 END)           AS reserve_count,
                    SUM(CASE WHEN status='failed' THEN 1 ELSE 0 END)           AS failed_count,
                    COUNT(DISTINCT user_id)                                     AS unique_users,
                    ROUND(AVG(CASE WHEN type='credit' THEN amount END), 2)     AS avg_topup
                ")
                ->first();

            // ── API request stats today (from api_request_logs) ──────────
            $apiStats = DB::table('api_request_logs')
                ->whereDate('created_at', today())
                ->selectRaw("
                    COUNT(*)                                                    AS total_requests,
                    SUM(CASE WHEN status_code < 400         THEN 1 ELSE 0 END) AS success_requests,
                    SUM(CASE WHEN status_code BETWEEN 400 AND 499 THEN 1 ELSE 0 END) AS client_errors,
                    SUM(CASE WHEN status_code >= 500        THEN 1 ELSE 0 END) AS server_errors,
                    ROUND(AVG(response_time_ms), 0)                            AS avg_response_ms,
                    MAX(response_time_ms)                                       AS max_response_ms,
                    SUM(CASE WHEN response_time_ms > 3000   THEN 1 ELSE 0 END) AS slow_requests,
                    COUNT(DISTINCT user_id)                                     AS unique_callers
                ")
                ->first();

            // ── Hourly topup trend (last 12 hours) ───────────────────────
            $topupTrend = DB::table('wallet_transactions')
                ->where('type', 'credit')
                ->where('created_at', '>=', now()->subHours(12))
                ->selectRaw("
                    HOUR(created_at) AS hour,
                    COUNT(*)         AS count,
                    SUM(amount)      AS amount
                ")
                ->groupByRaw('HOUR(created_at)')
                ->orderByRaw('HOUR(created_at)')
                ->get();

            $errorRate = ($apiStats->total_requests ?? 0) > 0
                ? round(100 * (($apiStats->client_errors + $apiStats->server_errors) / $apiStats->total_requests), 2)
                : 0;

            return [
                'wallet' => [
                    'topup_count'      => (int)   ($walletToday->topup_count    ?? 0),
                    'topup_amount'     => (float) ($walletToday->topup_amount   ?? 0),
                    'debit_count'      => (int)   ($walletToday->debit_count    ?? 0),
                    'debit_amount'     => (float) ($walletToday->debit_amount   ?? 0),
                    'reversal_count'   => (int)   ($walletToday->reversal_count ?? 0),
                    'reversal_amount'  => (float) ($walletToday->reversal_amount?? 0),
                    'reserve_count'    => (int)   ($walletToday->reserve_count  ?? 0),
                    'failed_count'     => (int)   ($walletToday->failed_count   ?? 0),
                    'unique_users'     => (int)   ($walletToday->unique_users   ?? 0),
                    'avg_topup'        => (float) ($walletToday->avg_topup      ?? 0),
                    'topup_trend'      => $topupTrend,
                ],
                'api' => [
                    'total_requests'   => (int)   ($apiStats->total_requests   ?? 0),
                    'success_requests' => (int)   ($apiStats->success_requests ?? 0),
                    'client_errors'    => (int)   ($apiStats->client_errors    ?? 0),
                    'server_errors'    => (int)   ($apiStats->server_errors    ?? 0),
                    'error_rate_pct'   => $errorRate,
                    'avg_response_ms'  => (int)   ($apiStats->avg_response_ms  ?? 0),
                    'max_response_ms'  => (int)   ($apiStats->max_response_ms  ?? 0),
                    'slow_requests'    => (int)   ($apiStats->slow_requests    ?? 0),
                    'unique_callers'   => (int)   ($apiStats->unique_callers   ?? 0),
                    'health'           => $errorRate < 1 ? 'healthy' : ($errorRate < 5 ? 'degraded' : 'critical'),
                ],
                '_meta' => [
                    'cached_at'   => now()->toIso8601String(),
                    'ttl_seconds' => self::TTL_GATEWAY,
                ],
            ];
        });
    }

    // ─────────────────────────────────────────────────────────────────────
    // 5 · PENDING COMPLAINTS
    // ─────────────────────────────────────────────────────────────────────

    public function pendingComplaints(): array
    {
        return Cache::remember(self::KEYS['complaints'], self::TTL_COMPLAINTS, function () {

            // ── Summary by priority and status ───────────────────────────
            $summary = DB::table('complaints')
                ->whereIn('status', ['open', 'in_progress', 'escalated', 'waiting_on_operator', 'waiting_on_user'])
                ->whereNull('deleted_at')
                ->selectRaw("
                    COUNT(*)                                                       AS total_open,
                    SUM(CASE WHEN priority='critical'  THEN 1 ELSE 0 END)         AS critical,
                    SUM(CASE WHEN priority='high'      THEN 1 ELSE 0 END)         AS high,
                    SUM(CASE WHEN priority='medium'    THEN 1 ELSE 0 END)         AS medium,
                    SUM(CASE WHEN priority='low'       THEN 1 ELSE 0 END)         AS low,
                    SUM(CASE WHEN status='escalated'   THEN 1 ELSE 0 END)         AS escalated,
                    SUM(CASE WHEN assigned_employee_id IS NULL THEN 1 ELSE 0 END) AS unassigned,
                    SUM(CASE WHEN sla_breached = 1     THEN 1 ELSE 0 END)         AS sla_breached,
                    SUM(CASE WHEN sla_deadline < NOW() AND status NOT IN ('resolved','closed') THEN 1 ELSE 0 END) AS sla_at_risk,
                    ROUND(AVG(TIMESTAMPDIFF(MINUTE, created_at, NOW())), 0)       AS avg_age_minutes
                ")
                ->first();

            // ── SLA breaches today ───────────────────────────────────────
            $slaBreachesToday = DB::table('complaints')
                ->where('sla_breached', true)
                ->whereDate('updated_at', today())
                ->whereNull('deleted_at')
                ->count();

            // ── Oldest unresolved critical complaints ────────────────────
            $criticalOpen = DB::table('complaints as c')
                ->select([
                    'c.id', 'c.ticket_number', 'c.subject', 'c.priority',
                    'c.status', 'c.sla_deadline', 'c.sla_breached', 'c.created_at',
                    'u.name as user_name',
                    'e.name as assigned_to',
                ])
                ->join('users as u', 'u.id', '=', 'c.user_id')
                ->leftJoin('employees as e', 'e.id', '=', 'c.assigned_employee_id')
                ->whereIn('c.priority', ['critical', 'high'])
                ->whereIn('c.status', ['open', 'escalated', 'in_progress'])
                ->whereNull('c.deleted_at')
                ->orderBy('c.sla_deadline')
                ->limit(10)
                ->get();

            // ── Resolved today count ─────────────────────────────────────
            $resolvedToday = DB::table('complaints')
                ->whereDate('resolved_at', today())
                ->whereNull('deleted_at')
                ->count();

            return [
                'summary' => [
                    'total_open'        => (int) ($summary->total_open     ?? 0),
                    'critical'          => (int) ($summary->critical       ?? 0),
                    'high'              => (int) ($summary->high           ?? 0),
                    'medium'            => (int) ($summary->medium         ?? 0),
                    'low'               => (int) ($summary->low            ?? 0),
                    'escalated'         => (int) ($summary->escalated      ?? 0),
                    'unassigned'        => (int) ($summary->unassigned     ?? 0),
                    'sla_breached'      => (int) ($summary->sla_breached   ?? 0),
                    'sla_at_risk'       => (int) ($summary->sla_at_risk    ?? 0),
                    'sla_breaches_today'=> (int) $slaBreachesToday,
                    'resolved_today'    => (int) $resolvedToday,
                    'avg_age_minutes'   => (int) ($summary->avg_age_minutes?? 0),
                    'alert_level'       => $this->complaintAlertLevel($summary),
                ],
                'critical_open' => $criticalOpen,
                '_meta' => [
                    'cached_at'   => now()->toIso8601String(),
                    'ttl_seconds' => self::TTL_COMPLAINTS,
                ],
            ];
        });
    }

    // ─────────────────────────────────────────────────────────────────────
    // 6a · HOURLY CHART — today's transaction volume per hour
    //      Hits: idx_rt_status_date (status, created_at) — fast range scan
    // ─────────────────────────────────────────────────────────────────────

    public function hourlyChart(): array
    {
        return Cache::remember(self::KEYS['chart_hourly'], self::TTL_CHART_H, function () {

            $raw = DB::table('recharge_transactions')
                ->whereDate('created_at', today())
                ->whereNull('deleted_at')
                ->selectRaw("
                    HOUR(created_at)                                              AS hour,
                    COUNT(*)                                                      AS total,
                    SUM(CASE WHEN status='success' THEN 1 ELSE 0 END)            AS success,
                    SUM(CASE WHEN status='failed'  THEN 1 ELSE 0 END)            AS failed,
                    SUM(CASE WHEN status IN ('queued','processing') THEN 1 ELSE 0 END) AS pending,
                    ROUND(SUM(amount), 2)                                         AS amount,
                    ROUND(
                        100.0 * SUM(CASE WHEN status='success' THEN 1 ELSE 0 END) / NULLIF(COUNT(*),0),
                        2
                    )                                                             AS success_rate_pct
                ")
                ->groupByRaw('HOUR(created_at)')
                ->orderByRaw('HOUR(created_at)')
                ->get()
                ->keyBy('hour');

            // Fill all 24 hours so chart has no gaps
            $hours = collect(range(0, 23))->map(fn ($h) => $raw->get($h, (object) [
                'hour'            => $h,
                'total'           => 0,
                'success'         => 0,
                'failed'          => 0,
                'pending'         => 0,
                'amount'          => 0,
                'success_rate_pct'=> 0,
            ]));

            // Peak hour
            $peak = $hours->sortByDesc('total')->first();

            return [
                'labels' => $hours->pluck('hour')->map(fn ($h) => str_pad($h, 2, '0', STR_PAD_LEFT) . ':00')->values(),
                'data'   => [
                    'total'            => $hours->pluck('total')->values(),
                    'success'          => $hours->pluck('success')->values(),
                    'failed'           => $hours->pluck('failed')->values(),
                    'pending'          => $hours->pluck('pending')->values(),
                    'amount'           => $hours->pluck('amount')->values(),
                    'success_rate_pct' => $hours->pluck('success_rate_pct')->values(),
                ],
                'peak_hour'  => $peak->hour ?? null,
                'peak_total' => (int) ($peak->total ?? 0),
                '_meta'  => [
                    'cached_at'   => now()->toIso8601String(),
                    'ttl_seconds' => self::TTL_CHART_H,
                ],
            ];
        });
    }

    // ─────────────────────────────────────────────────────────────────────
    // 6b · WEEKLY TREND — last 7 days
    // ─────────────────────────────────────────────────────────────────────

    public function weeklyChart(): array
    {
        return Cache::remember(self::KEYS['chart_weekly'], self::TTL_CHART_W, function () {

            $raw = DB::table('recharge_transactions')
                ->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()])
                ->whereNull('deleted_at')
                ->selectRaw("
                    DATE(created_at)                                              AS date,
                    COUNT(*)                                                      AS total,
                    SUM(CASE WHEN status='success' THEN 1 ELSE 0 END)            AS success,
                    SUM(CASE WHEN status='failed'  THEN 1 ELSE 0 END)            AS failed,
                    ROUND(SUM(amount), 2)                                         AS amount,
                    ROUND(
                        100.0 * SUM(CASE WHEN status='success' THEN 1 ELSE 0 END) / NULLIF(COUNT(*),0),
                        2
                    )                                                             AS success_rate_pct
                ")
                ->groupByRaw('DATE(created_at)')
                ->orderByRaw('DATE(created_at)')
                ->get()
                ->keyBy('date');

            // Fill all 7 days so chart has no gaps
            $days = collect(range(6, 0))->map(fn ($d) => now()->subDays($d)->format('Y-m-d'))
                ->map(fn ($date) => $raw->get($date, (object) [
                    'date'            => $date,
                    'total'           => 0,
                    'success'         => 0,
                    'failed'          => 0,
                    'amount'          => 0,
                    'success_rate_pct'=> 0,
                ]));

            return [
                'labels' => $days->pluck('date')->values(),
                'data'   => [
                    'total'            => $days->pluck('total')->values(),
                    'success'          => $days->pluck('success')->values(),
                    'failed'           => $days->pluck('failed')->values(),
                    'amount'           => $days->pluck('amount')->values(),
                    'success_rate_pct' => $days->pluck('success_rate_pct')->values(),
                ],
                '_meta' => [
                    'cached_at'   => now()->toIso8601String(),
                    'ttl_seconds' => self::TTL_CHART_W,
                ],
            ];
        });
    }

    // ─────────────────────────────────────────────────────────────────────
    // 7 · FULL DASHBOARD — single call returning all sections
    //     Frontend can call this once on load, then poll individual
    //     endpoints at their respective TTLs.
    // ─────────────────────────────────────────────────────────────────────

    public function fullDashboard(): array
    {
        return [
            'summary'    => $this->summary(),
            'live'       => $this->liveTransactionFeed(),
            'operators'  => $this->operatorPerformance(),
            'gateway'    => $this->gatewayPerformance(),
            'complaints' => $this->pendingComplaints(),
            'charts'     => [
                'hourly' => $this->hourlyChart(),
                'weekly' => $this->weeklyChart(),
            ],
            '_meta' => [
                'generated_at' => now()->toIso8601String(),
                'poll_intervals' => [
                    'summary'    => self::TTL_SUMMARY,
                    'live'       => self::TTL_LIVE,
                    'operators'  => self::TTL_OPERATORS,
                    'gateway'    => self::TTL_GATEWAY,
                    'complaints' => self::TTL_COMPLAINTS,
                    'chart'      => self::TTL_CHART_H,
                ],
            ],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────
    // Cache management helpers
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Bust cache keys that are affected by a new/updated transaction.
     * Called by BustDashboardCache listener.
     */
    public static function bustTransactionCaches(): void
    {
        Cache::forget(self::KEYS['summary']);
        Cache::forget(self::KEYS['live']);
    }

    public static function bustOperatorCaches(): void
    {
        Cache::forget(self::KEYS['operators']);
        Cache::forget(self::KEYS['gateway']);
    }

    public static function bustComplaintCaches(): void
    {
        Cache::forget(self::KEYS['complaints']);
    }

    public static function bustAll(): void
    {
        foreach (self::KEYS as $key) {
            Cache::forget($key);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────

    /** Calculate percentage delta between two values (+12.5% / -3.2%) */
    private function delta(float|int|null $current, float|int|null $previous): array
    {
        $c = (float) ($current  ?? 0);
        $p = (float) ($previous ?? 0);

        if ($p == 0) {
            return ['value' => 0, 'direction' => 'neutral', 'label' => '—'];
        }

        $pct = round((($c - $p) / $p) * 100, 1);

        return [
            'value'     => abs($pct),
            'direction' => $pct > 0 ? 'up' : ($pct < 0 ? 'down' : 'neutral'),
            'label'     => ($pct >= 0 ? '+' : '') . $pct . '%',
        ];
    }

    private function complaintAlertLevel(object $summary): string
    {
        if (($summary->critical ?? 0) > 0 || ($summary->sla_breached ?? 0) > 5) {
            return 'critical';
        }
        if (($summary->high ?? 0) > 5 || ($summary->unassigned ?? 0) > 10) {
            return 'warning';
        }
        return 'normal';
    }
}
