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

        $stats = DB::table('recharge_transactions')
            ->where('user_id', $user->id)
            ->selectRaw('
                COUNT(*) as total_recharges,
                SUM(amount) as total_amount,
                SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) as success_count,
                SUM(CASE WHEN status = "failed"  THEN 1 ELSE 0 END) as failed_count
            ')
            ->first();

        $wallet = DB::table('wallets')->where('user_id', $user->id)->first();

        return response()->json([
            'data' => [
                'total_recharges' => (int) ($stats->total_recharges ?? 0),
                'total_amount'    => (float) ($stats->total_amount ?? 0),
                'success_count'   => (int) ($stats->success_count ?? 0),
                'failed_count'    => (int) ($stats->failed_count ?? 0),
                'success_rate'    => $stats->total_recharges > 0
                    ? round($stats->success_count / $stats->total_recharges * 100, 1) : 0,
                'wallet_balance'  => $wallet ? (float) $wallet->balance : 0.0,
                'total_topup'     => $wallet ? (float) $wallet->total_topup : 0.0,
                'total_recharged' => $wallet ? (float) $wallet->total_recharged : 0.0,
            ],
        ]);
    }

    /** GET /api/v1/seller/reports/topup */
    public function topup(Request $request): JsonResponse
    {
        $user    = $request->user();
        $perPage = min($request->integer('per_page', 20), 100);

        $requests = SellerPaymentRequest::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json(['data' => $requests]);
    }

    /** GET /api/v1/seller/reports/operator */
    public function operator(Request $request): JsonResponse
    {
        $user = $request->user();

        $rows = DB::table('recharge_transactions')
            ->where('user_id', $user->id)
            ->groupBy('operator_code')
            ->selectRaw('
                operator_code,
                COUNT(*) as total_count,
                SUM(amount) as total_amount,
                SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) as success_count
            ')
            ->orderByDesc('total_amount')
            ->get()
            ->map(function ($r) {
                $r->success_rate = $r->total_count > 0
                    ? round($r->success_count / $r->total_count * 100, 1) : 0;
                return $r;
            });

        return response()->json(['data' => $rows]);
    }

    /** GET /api/v1/seller/reports/ledger */
    public function ledger(Request $request): JsonResponse
    {
        $user    = $request->user();
        $perPage = min($request->integer('per_page', 20), 100);

        $rows = DB::table('wallet_transactions')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate($perPage, [
                'id', 'type', 'amount', 'balance_after', 'description', 'reference', 'created_at',
            ]);

        return response()->json(['data' => $rows]);
    }
}
