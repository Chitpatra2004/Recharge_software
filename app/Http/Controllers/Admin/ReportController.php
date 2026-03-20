<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

    // ── 1. User Report ────────────────────────────────────────────────────────

    public function users(Request $request): JsonResponse
    {
        $filters = $this->parseFilters($request, [
            'role'    => ['sometimes', 'in:admin,retailer,distributor,api_user'],
            'status'  => ['sometimes', 'in:active,inactive,suspended'],
            'search'  => ['sometimes', 'string', 'max:100'],
        ]);

        if ($filters instanceof JsonResponse) return $filters;

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
            'status'         => ['sometimes', 'in:queued,processing,success,failed,refunded,partial'],
            'mobile'         => ['sometimes', 'digits_between:10,15'],
            'recharge_type'  => ['sometimes', 'in:prepaid,postpaid,dth,broadband'],
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
            'role'        => ['sometimes', 'in:retailer,distributor,api_user'],
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
