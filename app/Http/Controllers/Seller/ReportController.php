<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Operator;
use App\Models\SellerPaymentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /** GET /api/v1/seller/reports/account */
    public function account(Request $request): JsonResponse
    {
        $user = $request->user();

        $base = DB::table('recharge_transactions')
            ->where('user_id', $user->id)
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->filled('date_to'),   fn($q) => $q->whereDate('created_at', '<=', $request->date_to));

        $stats = (clone $base)
            ->selectRaw('
                COUNT(*) as total_recharges,
                SUM(amount) as total_amount,
                SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) as success_count,
                SUM(CASE WHEN status = "failed"  THEN 1 ELSE 0 END) as failed_count
            ')
            ->first();

        $daily = (clone $base)
            ->selectRaw('
                DATE(created_at) as date,
                COUNT(*) as total,
                SUM(amount) as amount,
                SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) as success,
                SUM(CASE WHEN status = "failed"  THEN 1 ELSE 0 END) as failed
            ')
            ->groupBy('date')
            ->orderByDesc('date')
            ->get()
            ->map(fn($r) => [
                'date'    => $r->date,
                'total'   => (int) $r->total,
                'amount'  => (float) $r->amount,
                'success' => (int) $r->success,
                'failed'  => (int) $r->failed,
            ]);

        $wallet = DB::table('wallets')->where('user_id', $user->id)->first();

        return response()->json([
            'data' => [
                'total_recharges' => (int) ($stats->total_recharges ?? 0),
                'total_amount'    => (float) ($stats->total_amount ?? 0),
                'success_count'   => (int) ($stats->success_count ?? 0),
                'failed_count'    => (int) ($stats->failed_count ?? 0),
                'success_rate'    => ($stats->total_recharges ?? 0) > 0
                    ? round(($stats->success_count ?? 0) / $stats->total_recharges * 100, 1) : 0,
                'wallet_balance'  => $wallet ? (float) $wallet->balance : 0.0,
                'total_topup'     => $wallet ? (float) $wallet->total_topup : 0.0,
                'total_recharged' => $wallet ? (float) $wallet->total_recharged : 0.0,
            ],
            'daily' => $daily,
        ]);
    }

    /** GET /api/v1/seller/reports/topup */
    public function topup(Request $request): JsonResponse
    {
        $user    = $request->user();
        $perPage = min($request->integer('per_page', 20), 100);

        $requests = SellerPaymentRequest::where('user_id', $user->id)
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->orderByDesc('created_at')
            ->paginate($perPage);

        $summary = [
            'total_count'     => SellerPaymentRequest::where('user_id', $user->id)->count(),
            'approved_amount' => (float) SellerPaymentRequest::where('user_id', $user->id)->where('status','approved')->sum('amount'),
            'pending_amount'  => (float) SellerPaymentRequest::where('user_id', $user->id)->where('status','pending')->sum('amount'),
        ];

        $arr = $requests->toArray();
        $arr['summary'] = $summary;
        return response()->json($arr);
    }

    /** GET /api/v1/seller/reports/operator */
    public function operator(Request $request): JsonResponse
    {
        $user = $request->user();

        $rows = DB::table('recharge_transactions')
            ->where('user_id', $user->id)
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->groupBy('operator_code')
            ->selectRaw('
                operator_code as operator,
                COUNT(*) as count,
                SUM(amount) as total_amount,
                SUM(CASE WHEN status = "success" THEN commission ELSE 0 END) as total_discount,
                SUM(CASE WHEN status = "success" THEN net_amount  ELSE 0 END) as net_amount,
                SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) as success,
                SUM(CASE WHEN status = "failed"  THEN 1 ELSE 0 END) as failed
            ')
            ->orderByDesc('total_amount')
            ->get()
            ->map(fn($r) => (object) [
                'operator'       => $r->operator,
                'count'          => (int)   $r->count,
                'success'        => (int)   $r->success,
                'failed'         => (int)   $r->failed,
                'total_amount'   => (float) $r->total_amount,
                'total_discount' => (float) $r->total_discount,
                'net_amount'     => (float) $r->net_amount,
            ]);

        return response()->json(['operators' => $rows]);
    }

    /** GET /api/v1/seller/reports/my-commission */
    public function myCommission(Request $request): JsonResponse
    {
        $user = $request->user();

        $settings = DB::table('seller_operator_commissions')
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('operator_code');

        $earned = DB::table('recharge_transactions')
            ->where('user_id', $user->id)
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->groupBy('operator_code')
            ->selectRaw('
                operator_code,
                COUNT(*) as total_count,
                SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) as success_count,
                SUM(CASE WHEN status = "success" THEN amount ELSE 0 END) as success_amount,
                SUM(CASE WHEN status = "success" THEN commission ELSE 0 END) as earned_commission
            ')
            ->get()
            ->keyBy('operator_code');

        $rows = Operator::query()
            ->orderBy('category')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'category', 'commission_rate', 'is_active'])
            ->map(function (Operator $operator) use ($settings, $earned, $user) {
                $setting = $settings->get($operator->code);
                $stat = $earned->get($operator->code);

                return [
                    'operator_code'      => $operator->code,
                    'operator_name'      => $operator->name,
                    'category'           => $operator->category,
                    'commission'         => (float) ($setting->commission ?? $operator->commission_rate ?? $user->commission_rate ?? 0),
                    'commission_type'    => $setting->commission_type ?? 'percentage',
                    'is_active'          => (bool) ($setting->is_active ?? $operator->is_active),
                    'total_count'        => (int) ($stat->total_count ?? 0),
                    'success_count'      => (int) ($stat->success_count ?? 0),
                    'success_amount'     => (float) ($stat->success_amount ?? 0),
                    'earned_commission'  => (float) ($stat->earned_commission ?? 0),
                ];
            });

        return response()->json([
            'data' => $rows,
            'summary' => [
                'operators' => $rows->count(),
                'active' => $rows->where('is_active', true)->count(),
                'total_success_amount' => round($rows->sum('success_amount'), 2),
                'total_earned_commission' => round($rows->sum('earned_commission'), 2),
            ],
        ]);
    }

    /** GET /api/v1/seller/reports/ledger */
    public function ledger(Request $request): JsonResponse
    {
        $user    = $request->user();
        $perPage = min($request->integer('per_page', 25), 100);

        $rechargeClass = \App\Models\RechargeTransaction::class;

        $rows = DB::table('wallet_transactions as wt')
            ->leftJoin('recharge_transactions as rt', function ($join) use ($rechargeClass) {
                $join->on('wt.reference_id', '=', 'rt.id')
                     ->where('wt.reference_type', $rechargeClass);
            })
            ->where('wt.user_id', $user->id)
            ->when($request->filled('type'), fn($q) => $q->where('wt.type', $request->type))
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('wt.created_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('wt.created_at', '<=', $request->date_to))
            ->orderByDesc('wt.created_at')
            ->paginate($perPage, [
                'wt.id', 'wt.txn_id', 'wt.type',
                'wt.amount as txn_amount',
                'wt.balance_before as opening_balance',
                'wt.balance_after  as closing_balance',
                'wt.description', 'wt.created_at',
                'rt.amount      as recharge_amount',
                'rt.commission  as discount',
                'rt.net_amount  as net_debit',
                'rt.mobile', 'rt.operator_code',
            ]);

        $wallet = DB::table('wallets')->where('user_id', $user->id)->first();
        $arr    = $rows->toArray();
        $arr['balance'] = $wallet ? (float) $wallet->balance : 0.0;
        return response()->json($arr);
    }
}
