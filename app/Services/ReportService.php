<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * ReportService — all reporting queries live here.
 *
 * Performance principles applied throughout:
 *  1. Always include created_at range → triggers MySQL RANGE partition pruning.
 *  2. Use DB::table() (query builder) — avoids Eloquent model hydration overhead.
 *  3. Select only required columns — reduces I/O and network transfer.
 *  4. Use ->when($condition, fn) — single query path, no conditional branches.
 *  5. Aggregates (SUM, COUNT, AVG) done in SQL — never pull rows to PHP.
 *  6. covering index columns ordered to match composite index definitions.
 *  7. LIMIT on detail queries; summary queries return aggregated rows.
 */
class ReportService
{
    // ─────────────────────────────────────────────────────────────────────────
    // 1. USER REPORT
    //    Hits index: idx_users_role_status (role, status)
    //    Summary + paginated list of users with wallet/transaction totals
    // ─────────────────────────────────────────────────────────────────────────

    public function userReport(array $filters, int $perPage = 20): array
    {
        $dateFrom = $filters['date_from'] ?? now()->subDays(30)->startOfDay();
        $dateTo   = $filters['date_to']   ?? now()->endOfDay();

        // ── Summary row ───────────────────────────────────────────────────────
        $summary = DB::table('users as u')
            ->selectRaw("
                COUNT(DISTINCT u.id)                                    AS total_users,
                SUM(CASE WHEN u.status = 'active'    THEN 1 ELSE 0 END) AS active_users,
                SUM(CASE WHEN u.status = 'inactive'  THEN 1 ELSE 0 END) AS inactive_users,
                SUM(CASE WHEN u.status = 'suspended' THEN 1 ELSE 0 END) AS suspended_users,
                SUM(CASE WHEN u.role   = 'retailer'    THEN 1 ELSE 0 END) AS retailers,
                SUM(CASE WHEN u.role   = 'distributor' THEN 1 ELSE 0 END) AS distributors,
                SUM(CASE WHEN u.role   = 'api_user'    THEN 1 ELSE 0 END) AS api_users,
                SUM(CASE WHEN u.role   = 'buyer'       THEN 1 ELSE 0 END) AS buyers,
                SUM(CASE WHEN u.created_at >= DATE_FORMAT(NOW(),'%Y-%m-01') THEN 1 ELSE 0 END) AS new_this_month,
                SUM(CASE WHEN DATE(u.created_at) = CURDATE() THEN 1 ELSE 0 END) AS new_today
            ")
            ->whereNull('u.deleted_at')
            ->when(isset($filters['role']),   fn ($q) => $q->where('u.role',   $filters['role']))
            ->when(isset($filters['status']), fn ($q) => $q->where('u.status', $filters['status']))
            ->first();

        // ── User list with denormalized wallet & transaction totals ───────────
        // LEFT JOIN wallets — one row per user (user_id UNIQUE in wallets)
        // LEFT JOIN sub-query — pre-aggregated recharge counts to avoid GROUP BY on huge table
        $rechargeSubQuery = DB::table('recharge_transactions')
            ->selectRaw('user_id, COUNT(*) AS total_txns, SUM(amount) AS total_recharged,
                         SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) AS success_count,
                         SUM(CASE WHEN status = "failed"  THEN 1 ELSE 0 END) AS failed_count')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereNull('deleted_at')
            ->groupBy('user_id');

        $users = DB::table('users as u')
            ->select([
                'u.id', 'u.name', 'u.email', 'u.mobile', 'u.role',
                'u.status', 'u.commission_rate', 'u.created_at',
                'w.balance', 'w.reserved_balance', 'w.total_recharged as lifetime_recharged',
                'w.total_topup as lifetime_topup',
                DB::raw('COALESCE(rt.total_txns,       0) AS period_txns'),
                DB::raw('COALESCE(rt.total_recharged,  0) AS period_recharged'),
                DB::raw('COALESCE(rt.success_count,    0) AS period_success'),
                DB::raw('COALESCE(rt.failed_count,     0) AS period_failed'),
            ])
            ->leftJoin('wallets as w', 'w.user_id', '=', 'u.id')
            ->leftJoinSub($rechargeSubQuery, 'rt', fn ($j) => $j->on('rt.user_id', '=', 'u.id'))
            ->whereNull('u.deleted_at')
            ->when(isset($filters['role']),    fn ($q) => $q->where('u.role',   $filters['role']))
            ->when(isset($filters['status']),  fn ($q) => $q->where('u.status', $filters['status']))
            ->when(isset($filters['search']),  fn ($q) => $q->where(function ($q2) use ($filters) {
                // FIX L1: escape LIKE wildcards before wrapping in % %.
                // Un-escaped % or _ in user input causes full-table scans and
                // may return unintended rows (e.g. searching "1_0" matches "100").
                $safe = str_replace(
                    ['\\',  '%',   '_'],
                    ['\\\\', '\\%', '\\_'],
                    $filters['search']
                );
                $term = '%' . $safe . '%';
                $q2->where('u.name', 'like', $term)
                   ->orWhere('u.email', 'like', $term)
                   ->orWhere('u.mobile', 'like', $term);
            }))
            ->orderBy('u.id', 'desc')
            ->paginate($perPage);

        return compact('summary', 'users');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2. RECHARGE REPORT
    //    Hits index: idx_rt_status_date, idx_rt_operator_status_date
    //    Daily/period breakdown + status distribution
    // ─────────────────────────────────────────────────────────────────────────

    public function rechargeReport(array $filters, int $perPage = 50): array
    {
        $dateFrom = $filters['date_from'] ?? now()->subDays(30)->startOfDay();
        $dateTo   = $filters['date_to']   ?? now()->endOfDay();

        // ── Overall summary ───────────────────────────────────────────────────
        $summary = DB::table('recharge_transactions')
            ->selectRaw("
                COUNT(*)                                                  AS total_txns,
                SUM(amount)                                               AS total_amount,
                SUM(commission)                                           AS total_commission,
                SUM(CASE WHEN status = 'success'    THEN 1 ELSE 0 END)   AS success_count,
                SUM(CASE WHEN status = 'success'    THEN amount ELSE 0 END) AS success_amount,
                SUM(CASE WHEN status = 'failed'     THEN 1 ELSE 0 END)   AS failed_count,
                SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END)   AS processing_count,
                SUM(CASE WHEN status = 'queued'     THEN 1 ELSE 0 END)   AS queued_count,
                SUM(CASE WHEN status = 'refunded'   THEN 1 ELSE 0 END)   AS refunded_count,
                ROUND(AVG(amount), 2)                                     AS avg_amount,
                ROUND(
                    100.0 * SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) / COUNT(*),
                    2
                )                                                         AS success_rate_pct
            ")
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereNull('deleted_at')
            ->when(isset($filters['operator_code']), fn ($q) => $q->where('operator_code', $filters['operator_code']))
            ->when(isset($filters['status']),        fn ($q) => $q->where('status',        $filters['status']))
            ->when(isset($filters['user_id']),       fn ($q) => $q->where('user_id',       $filters['user_id']))
            ->when(isset($filters['recharge_type']), fn ($q) => $q->where('recharge_type', $filters['recharge_type']))
            ->first();

        // ── Daily breakdown — uses idx_rt_status_date (status, created_at) ───
        $daily = DB::table('recharge_transactions')
            ->selectRaw("
                DATE(created_at)                                        AS date,
                COUNT(*)                                                AS total,
                SUM(amount)                                             AS total_amount,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END)    AS success,
                SUM(CASE WHEN status = 'failed'  THEN 1 ELSE 0 END)    AS failed,
                ROUND(100.0 * SUM(CASE WHEN status='success' THEN 1 ELSE 0 END) / COUNT(*), 2) AS success_rate
            ")
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereNull('deleted_at')
            ->when(isset($filters['operator_code']), fn ($q) => $q->where('operator_code', $filters['operator_code']))
            ->when(isset($filters['status']),        fn ($q) => $q->where('status',        $filters['status']))
            ->when(isset($filters['user_id']),       fn ($q) => $q->where('user_id',       $filters['user_id']))
            ->groupByRaw('DATE(created_at)')
            ->orderByRaw('DATE(created_at) DESC')
            ->get();

        // ── Transaction detail list ───────────────────────────────────────────
        $transactions = DB::table('recharge_transactions as rt')
            ->select([
                'rt.id', 'rt.mobile', 'rt.operator_code', 'rt.recharge_type',
                'rt.amount', 'rt.commission', 'rt.status', 'rt.operator_ref',
                'rt.retry_count', 'rt.failure_reason', 'rt.processed_at', 'rt.created_at',
                'u.name as user_name', 'u.email as user_email',
            ])
            ->join('users as u', 'u.id', '=', 'rt.user_id')
            ->whereBetween('rt.created_at', [$dateFrom, $dateTo])
            ->whereNull('rt.deleted_at')
            ->when(isset($filters['operator_code']), fn ($q) => $q->where('rt.operator_code', $filters['operator_code']))
            ->when(isset($filters['status']),        fn ($q) => $q->where('rt.status',        $filters['status']))
            ->when(isset($filters['user_id']),       fn ($q) => $q->where('rt.user_id',       $filters['user_id']))
            ->when(isset($filters['mobile']),        fn ($q) => $q->where('rt.mobile',        $filters['mobile']))
            ->when(isset($filters['recharge_type']), fn ($q) => $q->where('rt.recharge_type', $filters['recharge_type']))
            ->orderByDesc('rt.created_at')
            ->paginate($perPage);

        return compact('summary', 'daily', 'transactions');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3. OPERATOR-WISE REPORT
    //    Hits index: idx_rt_operator_status_date (operator_code, status, created_at)
    //    Per-operator volume, success rate, avg response time
    // ─────────────────────────────────────────────────────────────────────────

    public function operatorReport(array $filters, int $perPage = 20): array
    {
        $dateFrom = $filters['date_from'] ?? now()->subDays(30)->startOfDay();
        $dateTo   = $filters['date_to']   ?? now()->endOfDay();

        // ── Per-operator summary ──────────────────────────────────────────────
        $operators = DB::table('recharge_transactions as rt')
            ->select([
                'rt.operator_code',
                'o.name as operator_name',
                'o.category',
            ])
            ->selectRaw("
                COUNT(*)                                                         AS total_txns,
                SUM(rt.amount)                                                   AS total_amount,
                SUM(CASE WHEN rt.status = 'success' THEN 1 ELSE 0 END)          AS success_count,
                SUM(CASE WHEN rt.status = 'failed'  THEN 1 ELSE 0 END)          AS failed_count,
                SUM(CASE WHEN rt.status = 'refunded' THEN 1 ELSE 0 END)         AS refunded_count,
                SUM(CASE WHEN rt.status = 'success'  THEN rt.amount ELSE 0 END) AS success_amount,
                ROUND(AVG(rt.amount), 2)                                         AS avg_amount,
                ROUND(
                    100.0 * SUM(CASE WHEN rt.status = 'success' THEN 1 ELSE 0 END) / COUNT(*),
                    2
                )                                                                AS success_rate_pct,
                MAX(rt.created_at)                                               AS last_transaction_at
            ")
            ->leftJoin('operators as o', 'o.code', '=', 'rt.operator_code')
            ->whereBetween('rt.created_at', [$dateFrom, $dateTo])
            ->whereNull('rt.deleted_at')
            ->when(isset($filters['category']),     fn ($q) => $q->where('o.category',    $filters['category']))
            ->when(isset($filters['operator_code']),fn ($q) => $q->where('rt.operator_code', $filters['operator_code']))
            ->groupBy('rt.operator_code', 'o.name', 'o.category')
            ->orderByDesc('total_txns')
            ->paginate($perPage);

        // ── Route performance (avg duration per route) ────────────────────────
        $routePerformance = DB::table('recharge_attempts as ra')
            ->select([
                'ra.operator_route_id',
                'or_.name as route_name',
                'or_.api_provider',
            ])
            ->selectRaw("
                COUNT(*)                                                    AS attempts,
                SUM(CASE WHEN ra.status = 'success' THEN 1 ELSE 0 END)     AS successes,
                SUM(CASE WHEN ra.status = 'failed'  THEN 1 ELSE 0 END)     AS failures,
                ROUND(AVG(ra.duration_ms), 0)                               AS avg_duration_ms,
                MAX(ra.duration_ms)                                         AS max_duration_ms,
                ROUND(
                    100.0 * SUM(CASE WHEN ra.status = 'success' THEN 1 ELSE 0 END) / COUNT(*),
                    2
                )                                                           AS success_rate_pct
            ")
            ->join('operator_routes as or_', 'or_.id', '=', 'ra.operator_route_id')
            ->whereBetween('ra.created_at', [$dateFrom, $dateTo])
            ->when(isset($filters['operator_code']), fn ($q) => $q->where('or_.operator_code', $filters['operator_code']))
            ->groupBy('ra.operator_route_id', 'or_.name', 'or_.api_provider')
            ->orderByDesc('attempts')
            ->get();

        return compact('operators', 'routePerformance');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 4. FAILURE ANALYSIS
    //    Hits index: idx_rt_status_date, idx_rt_retry
    //    Breakdown by failure reason, retry patterns, time-of-day heatmap
    // ─────────────────────────────────────────────────────────────────────────

    public function failureAnalysis(array $filters, int $perPage = 50): array
    {
        $dateFrom = $filters['date_from'] ?? now()->subDays(7)->startOfDay();
        $dateTo   = $filters['date_to']   ?? now()->endOfDay();

        // ── Failure reasons grouped ───────────────────────────────────────────
        $byReason = DB::table('recharge_transactions')
            ->selectRaw("
                failure_reason,
                COUNT(*)            AS occurrences,
                SUM(amount)         AS amount_at_risk,
                AVG(retry_count)    AS avg_retries
            ")
            ->where('status', 'failed')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereNull('deleted_at')
            ->when(isset($filters['operator_code']), fn ($q) => $q->where('operator_code', $filters['operator_code']))
            ->groupBy('failure_reason')
            ->orderByDesc('occurrences')
            ->get();

        // ── Failure by operator ───────────────────────────────────────────────
        $byOperator = DB::table('recharge_transactions')
            ->selectRaw("
                operator_code,
                COUNT(*)                                                   AS total_failures,
                SUM(amount)                                                AS total_amount,
                SUM(CASE WHEN retry_count = 0 THEN 1 ELSE 0 END)          AS first_attempt_failures,
                SUM(CASE WHEN retry_count >= 3 THEN 1 ELSE 0 END)         AS max_retry_failures,
                ROUND(AVG(retry_count), 2)                                 AS avg_retry_count
            ")
            ->where('status', 'failed')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereNull('deleted_at')
            ->groupBy('operator_code')
            ->orderByDesc('total_failures')
            ->get();

        // ── Hourly failure heatmap (hour 0–23) ───────────────────────────────
        $hourlyHeatmap = DB::table('recharge_transactions')
            ->selectRaw("
                HOUR(created_at)    AS hour_of_day,
                COUNT(*)            AS failures,
                SUM(amount)         AS amount
            ")
            ->where('status', 'failed')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereNull('deleted_at')
            ->groupByRaw('HOUR(created_at)')
            ->orderByRaw('HOUR(created_at)')
            ->get();

        // ── Failed transactions detail — uses idx_rt_status_date ─────────────
        $details = DB::table('recharge_transactions as rt')
            ->select([
                'rt.id', 'rt.mobile', 'rt.operator_code', 'rt.amount',
                'rt.retry_count', 'rt.failure_reason', 'rt.created_at', 'rt.processed_at',
                'u.name as user_name', 'u.email as user_email',
            ])
            ->join('users as u', 'u.id', '=', 'rt.user_id')
            ->where('rt.status', 'failed')
            ->whereBetween('rt.created_at', [$dateFrom, $dateTo])
            ->whereNull('rt.deleted_at')
            ->when(isset($filters['operator_code']), fn ($q) => $q->where('rt.operator_code', $filters['operator_code']))
            ->when(isset($filters['user_id']),       fn ($q) => $q->where('rt.user_id',       $filters['user_id']))
            ->orderByDesc('rt.created_at')
            ->paginate($perPage);

        return compact('byReason', 'byOperator', 'hourlyHeatmap', 'details');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 5. PAYMENT GATEWAY / WALLET TOPUP REPORT
    //    Hits index: idx_wt_wallet_type_date, idx_wt_user_date
    //    Credits (topups) vs debits, daily cashflow, running balance
    // ─────────────────────────────────────────────────────────────────────────

    public function paymentReport(array $filters, int $perPage = 50): array
    {
        $dateFrom = $filters['date_from'] ?? now()->subDays(30)->startOfDay();
        $dateTo   = $filters['date_to']   ?? now()->endOfDay();

        // ── Summary ───────────────────────────────────────────────────────────
        $summary = DB::table('wallet_transactions')
            ->selectRaw("
                SUM(CASE WHEN type = 'credit'  THEN amount ELSE 0 END)  AS total_topup,
                SUM(CASE WHEN type = 'debit'   THEN amount ELSE 0 END)  AS total_debit,
                SUM(CASE WHEN type = 'reversal' THEN amount ELSE 0 END) AS total_reversed,
                COUNT(CASE WHEN type = 'credit'  THEN 1 END)            AS topup_count,
                COUNT(CASE WHEN type = 'debit'   THEN 1 END)            AS debit_count,
                ROUND(AVG(CASE WHEN type='credit' THEN amount END), 2)  AS avg_topup_amount,
                COUNT(DISTINCT user_id)                                  AS unique_users
            ")
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->when(isset($filters['type']),    fn ($q) => $q->where('type',    $filters['type']))
            ->when(isset($filters['user_id']), fn ($q) => $q->where('user_id', $filters['user_id']))
            ->first();

        // ── Daily cashflow ────────────────────────────────────────────────────
        $dailyCashflow = DB::table('wallet_transactions')
            ->selectRaw("
                DATE(created_at)                                         AS date,
                SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END)   AS topup,
                SUM(CASE WHEN type = 'debit'  THEN amount ELSE 0 END)   AS debit,
                COUNT(DISTINCT user_id)                                  AS active_users
            ")
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->when(isset($filters['user_id']), fn ($q) => $q->where('user_id', $filters['user_id']))
            ->groupByRaw('DATE(created_at)')
            ->orderByRaw('DATE(created_at) DESC')
            ->get();

        // ── Transaction detail ────────────────────────────────────────────────
        $transactions = DB::table('wallet_transactions as wt')
            ->select([
                'wt.id', 'wt.type', 'wt.amount', 'wt.balance_before',
                'wt.balance_after', 'wt.description', 'wt.status', 'wt.created_at',
                'u.name as user_name', 'u.email as user_email',
            ])
            ->join('users as u', 'u.id', '=', 'wt.user_id')
            ->whereBetween('wt.created_at', [$dateFrom, $dateTo])
            ->when(isset($filters['type']),    fn ($q) => $q->where('wt.type',    $filters['type']))
            ->when(isset($filters['user_id']), fn ($q) => $q->where('wt.user_id', $filters['user_id']))
            ->when(isset($filters['status']),  fn ($q) => $q->where('wt.status',  $filters['status']))
            ->orderByDesc('wt.created_at')
            ->paginate($perPage);

        return compact('summary', 'dailyCashflow', 'transactions');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 6. COMPLAINT REPORT
    //    Hits index: idx_comp_status_priority_date, idx_comp_assignee_status
    //    Ticket breakdown by status/type, SLA breach rate, agent workload
    // ─────────────────────────────────────────────────────────────────────────

    public function complaintReport(array $filters, int $perPage = 30): array
    {
        $dateFrom = $filters['date_from'] ?? now()->subDays(30)->startOfDay();
        $dateTo   = $filters['date_to']   ?? now()->endOfDay();

        // ── Summary ───────────────────────────────────────────────────────────
        $summary = DB::table('complaints')
            ->selectRaw("
                COUNT(*)                                                        AS total,
                SUM(CASE WHEN status = 'open'       THEN 1 ELSE 0 END)         AS open,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END)        AS in_progress,
                SUM(CASE WHEN status = 'resolved'   THEN 1 ELSE 0 END)         AS resolved,
                SUM(CASE WHEN status = 'closed'     THEN 1 ELSE 0 END)         AS closed,
                SUM(CASE WHEN status = 'escalated'  THEN 1 ELSE 0 END)         AS escalated,
                SUM(CASE WHEN sla_breached = 1      THEN 1 ELSE 0 END)         AS sla_breaches,
                ROUND(
                    100.0 * SUM(CASE WHEN sla_breached = 1 THEN 1 ELSE 0 END) / COUNT(*),
                    2
                )                                                               AS sla_breach_rate_pct,
                ROUND(
                    AVG(CASE WHEN resolved_at IS NOT NULL
                        THEN TIMESTAMPDIFF(MINUTE, created_at, resolved_at) END
                    ),
                    0
                )                                                               AS avg_resolution_minutes
            ")
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereNull('deleted_at')
            ->when(isset($filters['type']),     fn ($q) => $q->where('type',     $filters['type']))
            ->when(isset($filters['priority']), fn ($q) => $q->where('priority', $filters['priority']))
            ->when(isset($filters['status']),   fn ($q) => $q->where('status',   $filters['status']))
            ->first();

        // ── By type ───────────────────────────────────────────────────────────
        $byType = DB::table('complaints')
            ->selectRaw("type, COUNT(*) AS count, SUM(sla_breached) AS sla_breaches")
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereNull('deleted_at')
            ->groupBy('type')
            ->orderByDesc('count')
            ->get();

        // ── Agent workload ────────────────────────────────────────────────────
        $agentWorkload = DB::table('complaints as c')
            ->select([
                'c.assigned_employee_id',
                'e.name as agent_name',
            ])
            ->selectRaw("
                COUNT(*)                                                            AS assigned,
                SUM(CASE WHEN c.status IN ('open','in_progress') THEN 1 ELSE 0 END) AS open,
                SUM(CASE WHEN c.status = 'resolved' THEN 1 ELSE 0 END)             AS resolved,
                SUM(c.sla_breached)                                                 AS sla_breaches,
                ROUND(
                    AVG(CASE WHEN c.resolved_at IS NOT NULL
                        THEN TIMESTAMPDIFF(MINUTE, c.created_at, c.resolved_at) END
                    ), 0
                )                                                                   AS avg_resolution_minutes
            ")
            ->join('employees as e', 'e.id', '=', 'c.assigned_employee_id')
            ->whereBetween('c.created_at', [$dateFrom, $dateTo])
            ->whereNull('c.deleted_at')
            ->groupBy('c.assigned_employee_id', 'e.name')
            ->orderByDesc('assigned')
            ->get();

        // ── Complaint list ────────────────────────────────────────────────────
        $complaints = DB::table('complaints as c')
            ->select([
                'c.id', 'c.ticket_number', 'c.subject', 'c.type',
                'c.status', 'c.priority', 'c.sla_deadline', 'c.sla_breached',
                'c.resolution_action', 'c.resolved_at', 'c.created_at',
                'u.name as user_name', 'u.email as user_email',
                'e.name as assigned_to',
            ])
            ->join('users as u', 'u.id', '=', 'c.user_id')
            ->leftJoin('employees as e', 'e.id', '=', 'c.assigned_employee_id')
            ->whereBetween('c.created_at', [$dateFrom, $dateTo])
            ->whereNull('c.deleted_at')
            ->when(isset($filters['type']),        fn ($q) => $q->where('c.type',        $filters['type']))
            ->when(isset($filters['priority']),    fn ($q) => $q->where('c.priority',    $filters['priority']))
            ->when(isset($filters['status']),      fn ($q) => $q->where('c.status',      $filters['status']))
            ->when(isset($filters['user_id']),     fn ($q) => $q->where('c.user_id',     $filters['user_id']))
            ->when(isset($filters['sla_breached']),fn ($q) => $q->where('c.sla_breached',$filters['sla_breached']))
            ->orderByDesc('c.created_at')
            ->paginate($perPage);

        return compact('summary', 'byType', 'agentWorkload', 'complaints');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 7. WALLET ACCOUNT REPORT
    //    Hits index: idx_wallets_status (status) + user UNIQUE on user_id
    //    Balance distribution, low-balance alerts, frozen wallets
    // ─────────────────────────────────────────────────────────────────────────

    public function walletReport(array $filters, int $perPage = 30): array
    {
        $dateFrom = $filters['date_from'] ?? now()->subDays(30)->startOfDay();
        $dateTo   = $filters['date_to']   ?? now()->endOfDay();

        // ── Portfolio summary ─────────────────────────────────────────────────
        $summary = DB::table('wallets as w')
            ->join('users as u', 'u.id', '=', 'w.user_id')
            ->whereNull('u.deleted_at')
            ->selectRaw("
                COUNT(*)                                                  AS total_wallets,
                SUM(w.balance)                                            AS total_balance,
                SUM(w.reserved_balance)                                   AS total_reserved,
                SUM(w.total_recharged)                                    AS total_lifetime_recharged,
                SUM(w.total_topup)                                        AS total_lifetime_topup,
                SUM(CASE WHEN w.status = 'frozen'   THEN 1 ELSE 0 END)   AS frozen_wallets,
                SUM(CASE WHEN w.balance <= 0         THEN 1 ELSE 0 END)   AS zero_balance,
                SUM(CASE WHEN w.balance BETWEEN 1 AND 100 THEN 1 ELSE 0 END) AS low_balance,
                ROUND(AVG(w.balance), 2)                                  AS avg_balance,
                MAX(w.balance)                                            AS max_balance
            ")
            ->when(isset($filters['status']), fn ($q) => $q->where('w.status', $filters['status']))
            ->first();

        // ── Balance distribution buckets ──────────────────────────────────────
        $distribution = DB::table('wallets as w')
            ->join('users as u', 'u.id', '=', 'w.user_id')
            ->whereNull('u.deleted_at')
            ->selectRaw("
                CASE
                    WHEN w.balance <= 0              THEN '0'
                    WHEN w.balance BETWEEN 1   AND 100  THEN '1-100'
                    WHEN w.balance BETWEEN 101  AND 500  THEN '101-500'
                    WHEN w.balance BETWEEN 501  AND 2000 THEN '501-2000'
                    WHEN w.balance BETWEEN 2001 AND 10000 THEN '2001-10000'
                    ELSE '10000+'
                END         AS bucket,
                COUNT(*)    AS wallet_count,
                SUM(w.balance) AS bucket_total
            ")
            ->groupByRaw("
                CASE
                    WHEN w.balance <= 0              THEN '0'
                    WHEN w.balance BETWEEN 1   AND 100  THEN '1-100'
                    WHEN w.balance BETWEEN 101  AND 500  THEN '101-500'
                    WHEN w.balance BETWEEN 501  AND 2000 THEN '501-2000'
                    WHEN w.balance BETWEEN 2001 AND 10000 THEN '2001-10000'
                    ELSE '10000+'
                END
            ")
            ->orderByRaw('MIN(w.balance)')
            ->get();

        // ── Recent wallet transactions for ledger ─────────────────────────────
        $ledger = DB::table('wallet_transactions as wt')
            ->select([
                'wt.id', 'wt.type', 'wt.amount', 'wt.balance_before',
                'wt.balance_after', 'wt.description', 'wt.created_at',
                'u.name as user_name', 'u.email as user_email',
            ])
            ->join('users as u', 'u.id', '=', 'wt.user_id')
            ->whereBetween('wt.created_at', [$dateFrom, $dateTo])
            ->when(isset($filters['user_id']), fn ($q) => $q->where('wt.user_id', $filters['user_id']))
            ->when(isset($filters['type']),    fn ($q) => $q->where('wt.type',    $filters['type']))
            ->orderByDesc('wt.created_at')
            ->paginate($perPage);

        // ── Wallet list ───────────────────────────────────────────────────────
        $wallets = DB::table('wallets as w')
            ->select([
                'w.id', 'w.balance', 'w.reserved_balance', 'w.credit_limit',
                'w.total_recharged', 'w.total_topup', 'w.daily_debit_limit',
                'w.daily_debit_used', 'w.status', 'w.updated_at',
                'u.id as user_id', 'u.name', 'u.email', 'u.mobile', 'u.role',
            ])
            ->join('users as u', 'u.id', '=', 'w.user_id')
            ->whereNull('u.deleted_at')
            ->when(isset($filters['status']),  fn ($q) => $q->where('w.status',  $filters['status']))
            ->when(isset($filters['user_id']), fn ($q) => $q->where('w.user_id', $filters['user_id']))
            ->when(isset($filters['role']),    fn ($q) => $q->where('u.role',    $filters['role']))
            ->when(isset($filters['min_balance']), fn ($q) => $q->where('w.balance', '>=', $filters['min_balance']))
            ->when(isset($filters['max_balance']), fn ($q) => $q->where('w.balance', '<=', $filters['max_balance']))
            ->orderByDesc('w.balance')
            ->paginate($perPage);

        return compact('summary', 'distribution', 'ledger', 'wallets');
    }
}
