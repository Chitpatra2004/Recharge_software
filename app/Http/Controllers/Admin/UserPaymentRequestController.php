<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserPaymentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserPaymentRequestController extends Controller
{
    /** GET /api/v1/employee/user-payment-requests */
    public function index(Request $request): JsonResponse
    {
        $q = UserPaymentRequest::with('user:id,name,email,mobile')
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $q->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $q->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $q->where(function ($sub) use ($search) {
                $sub->where('reference_number', 'like', $search)
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $search)
                        ->orWhere('email', 'like', $search)
                        ->orWhere('mobile', 'like', $search));
            });
        }

        $perPage = min($request->integer('per_page', 20), 100);
        $rows    = $q->paginate($perPage);

        // Summary stats
        $stats = DB::selectOne("
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'pending'  THEN 1 ELSE 0 END) AS pending_count,
                SUM(CASE WHEN status = 'pending'  THEN amount ELSE 0 END) AS pending_amount,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved_count,
                SUM(CASE WHEN status = 'approved' THEN amount ELSE 0 END) AS approved_amount,
                SUM(CASE WHEN status = 'approved' AND DATE(processed_at) = CURDATE() THEN 1   ELSE 0 END) AS approved_today,
                SUM(CASE WHEN status = 'approved' AND DATE(processed_at) = CURDATE() THEN amount ELSE 0 END) AS approved_today_amount
            FROM user_payment_requests
        ");

        return response()->json([
            'data'  => $rows,
            'stats' => $stats,
        ]);
    }

    /** POST /api/v1/employee/user-payment-requests/{id}/approve */
    public function approve(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'admin_notes' => ['sometimes', 'nullable', 'string', 'max:500'],
        ]);

        $pr = UserPaymentRequest::with('user')->findOrFail($id);

        if ($pr->status !== 'pending') {
            return response()->json(['message' => 'Request is already ' . $pr->status . '.'], 422);
        }

        DB::transaction(function () use ($pr, $data) {
            // Credit user wallet
            $wallet = DB::table('wallets')->where('user_id', $pr->user_id)->lockForUpdate()->first();

            if ($wallet) {
                $balAfter = (float) $wallet->balance + (float) $pr->amount;
                DB::table('wallets')->where('user_id', $pr->user_id)->update([
                    'balance'    => $balAfter,
                    'updated_at' => now(),
                ]);
                DB::table('wallet_transactions')->insert([
                    'user_id'       => $pr->user_id,
                    'type'          => 'credit',
                    'amount'        => $pr->amount,
                    'balance_after' => $balAfter,
                    'description'   => 'Wallet top-up approved — Ref: ' . $pr->reference_number,
                    'txn_id'        => 'TOPUP-' . strtoupper(substr(md5($pr->id . now()), 0, 10)),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            $pr->update([
                'status'       => 'approved',
                'admin_notes'  => $data['admin_notes'] ?? null,
                'processed_at' => now(),
            ]);
        });

        return response()->json([
            'message' => 'Payment request approved and ₹' . number_format($pr->amount, 2) . ' credited to user wallet.',
            'data'    => $pr->fresh('user'),
        ]);
    }

    /** POST /api/v1/employee/user-payment-requests/{id}/reject */
    public function reject(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'admin_notes' => ['required', 'string', 'max:500'],
        ]);

        $pr = UserPaymentRequest::findOrFail($id);

        if ($pr->status !== 'pending') {
            return response()->json(['message' => 'Request is already ' . $pr->status . '.'], 422);
        }

        $pr->update([
            'status'       => 'rejected',
            'admin_notes'  => $data['admin_notes'],
            'processed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Payment request rejected.',
            'data'    => $pr,
        ]);
    }
}
