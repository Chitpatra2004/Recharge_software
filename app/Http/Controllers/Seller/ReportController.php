<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
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
                SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) as success,
                SUM(CASE WHEN status = "failed"  THEN 1 ELSE 0 END) as failed
            ')
            ->orderByDesc('total_amount')
            ->get()
            ->map(fn($r) => (object) array_merge((array) $r, [
                'total_amount' => (float) $r->total_amount,
            ]));

        return response()->json(['operators' => $rows]);
    }

    /** GET /api/v1/seller/reports/ledger */
    public function ledger(Request $request): JsonResponse
    {
        $user    = $request->user();
        $perPage = min($request->integer('per_page', 20), 100);

        $rows = DB::table('wallet_transactions')
            ->where('user_id', $user->id)
            ->when($request->filled('type'), fn($q) => $q->where('type', $request->type))
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->orderByDesc('created_at')
            ->paginate($perPage, ['id','type','amount','balance_after','description','reference','created_at']);

        $wallet  = DB::table('wallets')->where('user_id', $user->id)->first();
        $arr     = $rows->toArray();
        $arr['balance'] = $wallet ? (float) $wallet->balance : 0.0;
        return response()->json($arr);
    }
}
