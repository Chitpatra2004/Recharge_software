<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\SellerIntegrationRequest;
use App\Models\SellerPaymentRequest;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user  = $request->user();
        $today = now()->toDateString();

        // Sales stats
        $totalSales = DB::table('recharge_transactions')
            ->where('user_id', $user->id)
            ->count();

        $totalAmount = DB::table('recharge_transactions')
            ->where('user_id', $user->id)
            ->sum('amount');

        $todaySales = DB::table('recharge_transactions')
            ->where('user_id', $user->id)
            ->whereDate('created_at', $today)
            ->count();

        $todayAmount = DB::table('recharge_transactions')
            ->where('user_id', $user->id)
            ->whereDate('created_at', $today)
            ->sum('amount');

        $successCount = DB::table('recharge_transactions')
            ->where('user_id', $user->id)
            ->where('status', 'success')
            ->count();

        $pendingRecharges = DB::table('recharge_transactions')
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending', 'queued', 'processing'])
            ->count();

        // Wallet balance
        $wallet = DB::table('wallets')->where('user_id', $user->id)->first();
        $balance = $wallet ? (float) $wallet->balance : 0.0;

        // Pending payment requests
        $pendingPayments = SellerPaymentRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        // Integration status
        $integration = SellerIntegrationRequest::where('user_id', $user->id)
            ->latest()
            ->first();

        // Recent 5 transactions
        $recent = DB::table('recharge_transactions')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get([
                'id',
                'mobile',
                'operator_code',
                'recharge_type as type',
                'amount',
                'status',
                'created_at',
            ]);

        return response()->json([
            'data' => [
                'stats' => [
                    'total_sales'       => $totalSales,
                    'total_amount'      => (float) $totalAmount,
                    'today_sales'       => $todaySales,
                    'today_amount'      => (float) $todayAmount,
                    'success_count'     => $successCount,
                    'success_rate'      => $totalSales > 0 ? round($successCount / $totalSales * 100, 1) : 0,
                    'wallet_balance'    => $balance,
                    'pending_payments'  => $pendingPayments,
                    'pending_recharges' => $pendingRecharges,
                ],
                'integration' => $integration ? [
                    'status'     => $integration->status,
                    'website'    => $integration->website_url,
                    'created_at' => $integration->created_at,
                ] : null,
                'recent_sales' => $recent,
                'notice' => [
                    'enabled' => (string) SystemSetting::get('seller_notice_enabled', '0') === '1',
                    'title' => SystemSetting::get('seller_notice_title', 'Notice'),
                    'message' => SystemSetting::get('seller_notice_message', ''),
                ],
            ],
        ]);
    }
}
