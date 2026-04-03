<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserDashboardController extends Controller
{
    /** GET /api/v1/dashboard */
    public function index(Request $request): JsonResponse
    {
        $user    = $request->user();
        $today   = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();

        // Wallet
        $wallet = DB::table('wallets')->where('user_id', $user->id)->first();

        // Today's recharges
        $todayStats = DB::table('recharge_transactions')
            ->where('user_id', $user->id)
            ->whereDate('created_at', $today)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(amount),0) as amount,
                         SUM(CASE WHEN status="success" THEN 1 ELSE 0 END) as success_count')
            ->first();

        // All-time recharge stats
        $allTime = DB::table('recharge_transactions')
            ->where('user_id', $user->id)
            ->selectRaw('COUNT(*) as total_count, COALESCE(SUM(amount),0) as total_amount')
            ->first();

        // This month
        $monthStats = DB::table('recharge_transactions')
            ->where('user_id', $user->id)
            ->whereDate('created_at', '>=', $monthStart)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(amount),0) as amount')
            ->first();

        // Today's BBPS
        $todayBbps = DB::table('bbps_transactions')
            ->where('user_id', $user->id)
            ->whereDate('created_at', $today)
            ->where('status', 'success')
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(amount),0) as amount')
            ->first();

        // Open complaints
        $openComplaints = DB::table('complaints')
            ->where('user_id', $user->id)
            ->whereNotIn('status', ['resolved', 'closed'])
            ->count();

        // Recent 5 transactions
        $recent = DB::table('recharge_transactions')
            ->where('user_id', $user->id)
            ->select(['id', 'mobile', 'operator_code', 'recharge_type', 'amount', 'status', 'created_at'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Last 7 days chart data
        $chart = DB::table('recharge_transactions')
            ->where('user_id', $user->id)
            ->whereDate('created_at', '>=', now()->subDays(6)->toDateString())
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, COALESCE(SUM(amount),0) as amount')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Fill all 7 days (even empty ones)
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = now()->subDays($i)->toDateString();
            $chartData[] = [
                'date'   => $d,
                'label'  => now()->subDays($i)->format('D'),
                'count'  => (int) ($chart[$d]->count  ?? 0),
                'amount' => (float) ($chart[$d]->amount ?? 0),
            ];
        }

        return response()->json([
            'user' => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'mobile' => $user->mobile,
                'role'   => $user->role,
                'status' => $user->status,
            ],
            'stats' => [
                'wallet_balance'    => $wallet ? (float) $wallet->balance : 0.0,
                'wallet_reserved'   => $wallet ? (float) ($wallet->reserved_balance ?? 0) : 0.0,
                'today_count'       => (int) ($todayStats->count ?? 0),
                'today_amount'      => (float) ($todayStats->amount ?? 0),
                'today_success'     => (int) ($todayStats->success_count ?? 0),
                'today_bbps_count'  => (int) ($todayBbps->count ?? 0),
                'today_bbps_amount' => (float) ($todayBbps->amount ?? 0),
                'month_count'       => (int) ($monthStats->count ?? 0),
                'month_amount'      => (float) ($monthStats->amount ?? 0),
                'total_count'       => (int) ($allTime->total_count ?? 0),
                'total_amount'      => (float) ($allTime->total_amount ?? 0),
                'open_complaints'   => (int) $openComplaints,
            ],
            'chart'  => $chartData,
            'recent' => $recent,
        ]);
    }
}
