<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;

/**
 * ReportController — admin-only reporting endpoints.
 *
 * All methods share the same filter validation logic (extracted to
 * validateFilters()). Each report method delegates heavy lifting to
 * ReportService so this controller stays thin.
 *
 * Common filter parameters accepted by all endpoints:
 *   date_from      — ISO date string (default: 30 days ago)
 *   date_to        — ISO date string (default: today)
 *   operator_code  — filter by operator (AIRTEL, JIO, etc.)
 *   status         — filter by transaction/complaint/wallet status
 *   user_id        — filter by specific user
 *   per_page       — results per page (1–100, default 20)
 */
class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reports) {}

    // ── 0a. Account Ledger ───────────────────────────────────────────────────

    public function accountLedger(Request $request): JsonResponse
    {
        $q = DB::table('wallet_transactions as wt')
            ->join('users as u', 'u.id', '=', 'wt.user_id')
            ->select(
                'wt.id', 'wt.type', 'wt.amount', 'wt.balance_before', 'wt.balance_after',
                'wt.description', 'wt.bank_name', 'wt.rrn', 'wt.remark', 'wt.admin_remark',
                'wt.reference_type', 'wt.reference_id', 'wt.txn_id', 'wt.created_at',
                'u.name as user_name', 'u.id as user_id'
            )
            ->orderByDesc('wt.created_at');

        if ($request->filled('type'))      { $q->where('wt.type', $request->type); }
        if ($request->filled('date_from')) { $q->whereDate('wt.created_at', '>=', $request->date_from); }
        if ($request->filled('date_to'))   { $q->whereDate('wt.created_at', '<=', $request->date_to); }
        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $q->where(fn ($sq) => $sq->where('wt.description', 'like', $s)
                ->orWhere('wt.txn_id', 'like', $s)
                ->orWhere('wt.rrn', 'like', $s)
                ->orWhere('u.name', 'like', $s));
        }

        $rows = $q->paginate($request->integer('per_page', 25));

        $summary = DB::table('wallet_transactions')
            ->selectRaw("
                SUM(CASE WHEN type='credit' THEN amount ELSE 0 END) as total_credits,
                SUM(CASE WHEN type='debit'  THEN amount ELSE 0 END) as total_debits,
                COUNT(*) as total_entries
            ")
            ->first();

        return response()->json(['data' => $rows, 'summary' => $summary]);
    }

    // ── 0b. Update Wallet Transaction ────────────────────────────────────────

    public function updateWalletTransaction(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'bank_name'    => ['sometimes', 'nullable', 'string', 'max:100'],
            'rrn'          => ['sometimes', 'nullable', 'string', 'max:100'],
            'remark'       => ['sometimes', 'nullable', 'string', 'max:255'],
            'admin_remark' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'amount'       => ['sometimes', 'numeric', 'min:0.01'],
            'type'         => ['sometimes', 'in:credit,debit'],
        ]);

        $txn = DB::table('wallet_transactions')->where('id', $id)->first();
        if (! $txn) {
            return response()->json(['message' => 'Transaction not found.'], 404);
        }

        DB::table('wallet_transactions')->where('id', $id)->update(
            array_merge(array_filter($data, fn ($v) => $v !== null), ['updated_at' => now()])
        );

        return response()->json([
            'message' => 'Transaction updated successfully.',
            'data'    => DB::table('wallet_transactions')->where('id', $id)->first(),
        ]);
    }

    // ── 0. User Detail ────────────────────────────────────────────────────────

    public function showUser(int $id): JsonResponse
    {
        $user = DB::table('users')->where('id', $id)->first();
        if (! $user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $wallet = DB::table('wallets')->where('user_id', $id)->first();
        $rechargeStats = DB::table('recharge_transactions')
            ->where('user_id', $id)
            ->selectRaw("COUNT(*) as total, SUM(amount) as total_amount,
                SUM(CASE WHEN status='success' THEN 1 ELSE 0 END) as success_count,
                SUM(CASE WHEN status='failed'  THEN 1 ELSE 0 END) as failed_count")
            ->first();

        $recentRecharges = DB::table('recharge_transactions')
            ->where('user_id', $id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['id','mobile','operator_code','amount','status','created_at']);

        $walletTxns = DB::table('wallet_transactions')
            ->where('user_id', $id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['id','type','amount','description','balance_after','created_at']);

        $paymentRequests = DB::table('payment_requests')
            ->where('user_id', $id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return response()->json([
            'user'            => $user,
            'wallet_balance'  => $wallet ? (float) $wallet->balance : 0.0,
            'recharge_stats'  => $rechargeStats,
            'recent_recharges'=> $recentRecharges,
            'wallet_txns'     => $walletTxns,
            'payment_requests'=> $paymentRequests,
        ]);
    }

    // ── 1. User Report ────────────────────────────────────────────────────────

    public function users(Request $request): JsonResponse
    {
        $filters = $this->parseFilters($request, [
            'role'            => ['sometimes', 'in:admin,retailer,distributor,api_user,buyer'],
            'status'          => ['sometimes', 'in:active,inactive,suspended'],
            'search'          => ['sometimes', 'string', 'max:100'],
            'exclude_sellers' => ['sometimes', 'in:0,1'],
        ]);

        if ($filters instanceof JsonResponse) return $filters;

        // When exclude_sellers=1 (user list page default), hide only api_user
        if (! empty($filters['exclude_sellers']) && $filters['exclude_sellers'] === '1') {
            $filters['exclude_roles'] = ['api_user'];
        }

        $data = $this->reports->userReport($filters, (int) ($filters['per_page'] ?? 20));

        return response()->json([
            'report'  => 'user_report',
            'filters' => $this->activeFilters($filters),
            'summary' => $data['summary'],
            'users'   => $data['users'],
        ]);
    }

    // ── 2. Recharge Report ────────────────────────────────────────────────────

    public function recharges(Request $request): JsonResponse
    {
        $filters = $this->parseFilters($request, [
            'operator_code'  => ['sometimes', 'string', 'max:30'],
            'status'         => ['sometimes', 'in:pending,queued,processing,success,failed,refunded,partial'],
            'mobile'         => ['sometimes', 'digits_between:10,15'],
            'recharge_type'  => ['sometimes', 'in:prepaid,postpaid,dth,broadband'],
            'api_provider'   => ['sometimes', 'string', 'max:50'],
        ]);

        if ($filters instanceof JsonResponse) return $filters;

        $data = $this->reports->rechargeReport($filters, (int) ($filters['per_page'] ?? 50));

        return response()->json([
            'report'       => 'recharge_report',
            'filters'      => $this->activeFilters($filters),
            'summary'      => $data['summary'],
            'daily'        => $data['daily'],
            'transactions' => $data['transactions'],
        ]);
    }

    // ── 3. Operator-Wise Report ───────────────────────────────────────────────

    public function operators(Request $request): JsonResponse
    {
        $filters = $this->parseFilters($request, [
            'operator_code' => ['sometimes', 'string', 'max:30'],
            'category'      => ['sometimes', 'in:mobile,dth,broadband'],
        ]);

        if ($filters instanceof JsonResponse) return $filters;

        $data = $this->reports->operatorReport($filters, (int) ($filters['per_page'] ?? 20));

        return response()->json([
            'report'            => 'operator_report',
            'filters'           => $this->activeFilters($filters),
            'operators'         => $data['operators'],
            'route_performance' => $data['routePerformance'],
        ]);
    }

    // ── 4. Failure Analysis ───────────────────────────────────────────────────

    public function failures(Request $request): JsonResponse
    {
        $filters = $this->parseFilters($request, [
            'operator_code' => ['sometimes', 'string', 'max:30'],
        ]);

        if ($filters instanceof JsonResponse) return $filters;

        $data = $this->reports->failureAnalysis($filters, (int) ($filters['per_page'] ?? 50));

        return response()->json([
            'report'          => 'failure_analysis',
            'filters'         => $this->activeFilters($filters),
            'by_reason'       => $data['byReason'],
            'by_operator'     => $data['byOperator'],
            'hourly_heatmap'  => $data['hourlyHeatmap'],
            'details'         => $data['details'],
        ]);
    }

    // ── 5. Payment Gateway / Wallet Topup Report ──────────────────────────────

    public function payments(Request $request): JsonResponse
    {
        $filters = $this->parseFilters($request, [
            'type'   => ['sometimes', 'in:credit,debit,reserve,release,reversal'],
            'status' => ['sometimes', 'in:pending,completed,failed,reversed'],
        ]);

        if ($filters instanceof JsonResponse) return $filters;

        $data = $this->reports->paymentReport($filters, (int) ($filters['per_page'] ?? 50));

        return response()->json([
            'report'          => 'payment_report',
            'filters'         => $this->activeFilters($filters),
            'summary'         => $data['summary'],
            'daily_cashflow'  => $data['dailyCashflow'],
            'transactions'    => $data['transactions'],
        ]);
    }

    // ── 6. Complaint Report ───────────────────────────────────────────────────

    public function complaints(Request $request): JsonResponse
    {
        $filters = $this->parseFilters($request, [
            'type'         => ['sometimes', 'in:recharge_failed,balance_deducted,wrong_recharge,refund,operator_delay,other'],
            'priority'     => ['sometimes', 'in:low,medium,high,critical'],
            'status'       => ['sometimes', 'in:open,in_progress,waiting_on_operator,waiting_on_user,resolved,closed,escalated'],
            'sla_breached' => ['sometimes', 'boolean'],
        ]);

        if ($filters instanceof JsonResponse) return $filters;

        $data = $this->reports->complaintReport($filters, (int) ($filters['per_page'] ?? 30));

        return response()->json([
            'report'        => 'complaint_report',
            'filters'       => $this->activeFilters($filters),
            'summary'       => $data['summary'],
            'by_type'       => $data['byType'],
            'agent_workload'=> $data['agentWorkload'],
            'complaints'    => $data['complaints'],
        ]);
    }

    // ── 7. Wallet Account Report ──────────────────────────────────────────────

    public function wallets(Request $request): JsonResponse
    {
        $filters = $this->parseFilters($request, [
            'status'      => ['sometimes', 'in:active,frozen'],
            'role'        => ['sometimes', 'in:retailer,distributor,api_user,buyer'],
            'type'        => ['sometimes', 'in:credit,debit,reserve,release,reversal'],
            'min_balance' => ['sometimes', 'numeric', 'min:0'],
            'max_balance' => ['sometimes', 'numeric', 'min:0'],
        ]);

        if ($filters instanceof JsonResponse) return $filters;

        $data = $this->reports->walletReport($filters, (int) ($filters['per_page'] ?? 30));

        return response()->json([
            'report'       => 'wallet_report',
            'filters'      => $this->activeFilters($filters),
            'summary'      => $data['summary'],
            'distribution' => $data['distribution'],
            'ledger'       => $data['ledger'],
            'wallets'      => $data['wallets'],
        ]);
    }

    // ── 8. Pending / Queued Transactions ─────────────────────────────────────

    public function pending(Request $request): JsonResponse
    {
        $perPage   = min((int) $request->integer('per_page', 25), 100);
        $minAge    = $request->integer('min_age', 0);
        $operator  = $request->input('operator_code');
        $dateFrom  = $request->input('date_from') ?? $request->input('date');
        $dateTo    = $request->input('date_to')   ?? $request->input('date');
        $search    = $request->input('search');
        $userName  = $request->input('user_name');
        $status    = $request->input('status');

        $allowedStatuses = ['pending', 'queued', 'processing'];

        $query = DB::table('recharge_transactions as rt')
            ->leftJoin('users as u', 'u.id', '=', 'rt.user_id')
            ->leftJoin('operator_routes as orr', 'orr.id', '=', 'rt.operator_route_id')
            ->whereIn('rt.status', $allowedStatuses)
            ->whereNull('rt.deleted_at')
            // Prevent accidental duplicate rows if joins expand later
            ->distinct()
            ->select([
                'rt.id',
                'rt.mobile',
                'rt.operator_code',
                'rt.recharge_type',
                'rt.amount',
                'rt.commission',
                'rt.status',
                'rt.retry_count',
                'rt.operator_ref',
                'rt.api_ref',
                'rt.created_at',
                'rt.updated_at',
                'rt.failure_reason',
                'u.name as seller_name',
                'u.email as seller_email',
                'orr.api_provider',
                'orr.name as route_name',
                'orr.id as route_id',
                DB::raw('TIMESTAMPDIFF(MINUTE, rt.created_at, NOW()) as age_minutes'),
            ]);

        if ($operator) {
            $query->where('rt.operator_code', strtoupper($operator));
        }

        // Date filters are provided as "local dates" from the UI. Our DB timestamps
        // are stored in UTC. Using whereDate() causes midnight boundary bugs (IST vs UTC),
        // e.g. a recharge at 00:10 IST is still "yesterday" in UTC.
        // Fix: convert selected day range to UTC and filter by created_at between.
        if ($dateFrom || $dateTo) {
            $tz = config('app.timezone', 'UTC');
            $fromUtc = $dateFrom
                ? Carbon::parse($dateFrom, $tz)->startOfDay()->utc()
                : Carbon::parse($dateTo, $tz)->startOfDay()->utc();
            $toUtc = $dateTo
                ? Carbon::parse($dateTo, $tz)->endOfDay()->utc()
                : Carbon::parse($dateFrom, $tz)->endOfDay()->utc();

            $query->whereBetween('rt.created_at', [$fromUtc, $toUtc]);
        }

        if ($minAge > 0) {
            $query->whereRaw('TIMESTAMPDIFF(MINUTE, rt.created_at, NOW()) >= ?', [$minAge]);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('rt.mobile', 'like', "%{$search}%")
                  ->orWhere('rt.id', $search);
            });
        }

        if ($userName) {
            $query->where('u.name', 'like', "%{$userName}%");
        }

        if ($status && \in_array($status, $allowedStatuses, true)) {
            $query->where('rt.status', $status);
        }

        $query->orderByDesc('rt.created_at');

        $rows = $query->paginate($perPage);

        // Stats
        $stats = DB::table('recharge_transactions')
            ->whereIn('status', ['pending', 'queued', 'processing'])
            ->whereNull('deleted_at')
            ->selectRaw("
                COUNT(*)                                                        as total,
                SUM(amount)                                                     as total_amount,
                SUM(CASE WHEN status = 'queued'     THEN 1 ELSE 0 END)        as queued,
                SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END)        as processing,
                SUM(CASE WHEN TIMESTAMPDIFF(MINUTE, created_at, NOW()) > 30
                         THEN 1 ELSE 0 END)                                    as stuck
            ")
            ->first();

        return response()->json([
            'data'  => $rows,
            'stats' => $stats,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Shared helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Validate and parse common + report-specific filters.
     * Returns validated array on success, JsonResponse on failure.
     */
    private function parseFilters(Request $request, array $extraRules = []): array|JsonResponse
    {
        $rules = array_merge([
            'date_from' => ['sometimes', 'date', 'before_or_equal:date_to'],
            'date_to'   => ['sometimes', 'date', 'after_or_equal:date_from'],
            'user_id'   => ['sometimes', 'integer', 'exists:users,id'],
            'per_page'  => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page'      => ['sometimes', 'integer', 'min:1'],
        ], $extraRules);

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid report filters.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $f = $validator->validated();

        // Cast date strings to Carbon objects once
        if (isset($f['date_from'])) {
            $f['date_from'] = \Illuminate\Support\Carbon::parse($f['date_from'])->startOfDay();
        }
        if (isset($f['date_to'])) {
            $f['date_to'] = \Illuminate\Support\Carbon::parse($f['date_to'])->endOfDay();
        }

        return $f;
    }

    /**
     * Strip null/empty values for a cleaner "applied filters" response node.
     */
    private function activeFilters(array $filters): array
    {
        return array_filter($filters, fn ($v) => $v !== null && $v !== '');
    }
}
