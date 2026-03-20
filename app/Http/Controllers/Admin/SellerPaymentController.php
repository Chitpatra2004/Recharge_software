<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SellerPaymentRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SellerPaymentController extends Controller
{
    /** GET /api/v1/employee/sellers/payment-requests/list */
    public function index(Request $request): JsonResponse
    {
        $query = SellerPaymentRequest::with('user:id,name,email,mobile')
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $perPage  = min($request->integer('per_page', 25), 100);
        $requests = $query->paginate($perPage);

        $stats = DB::table('seller_payment_requests')
            ->selectRaw("
                SUM(CASE WHEN status='pending'  THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN status='pending'  THEN amount ELSE 0 END) as pending_amount,
                SUM(CASE WHEN status='approved' THEN 1 ELSE 0 END) as approved_count,
                SUM(CASE WHEN status='approved' THEN amount ELSE 0 END) as approved_amount
            ")
            ->first();

        return response()->json(['data' => $requests, 'stats' => $stats]);
    }

    /** POST /api/v1/employee/sellers/payment-requests/{id}/approve */
    public function approve(Request $request, int $id): JsonResponse
    {
        $request->validate(['notes' => ['sometimes', 'nullable', 'string', 'max:500']]);

        $pr = SellerPaymentRequest::with('user')->findOrFail($id);

        if ($pr->status !== 'pending') {
            return response()->json(['message' => 'Request is already ' . $pr->status . '.'], 422);
        }

        DB::transaction(function () use ($pr, $request) {
            $pr->update([
                'status'       => 'approved',
                'admin_notes'  => $request->notes,
                'processed_at' => now(),
            ]);

            // Credit wallet
            $wallet = DB::table('wallets')->where('user_id', $pr->user_id)->lockForUpdate()->first();
            if ($wallet) {
                $newBalance = (float) $wallet->balance + (float) $pr->amount;
                DB::table('wallets')->where('user_id', $pr->user_id)->update([
                    'balance'     => $newBalance,
                    'total_topup' => DB::raw("total_topup + {$pr->amount}"),
                    'updated_at'  => now(),
                ]);
            }

            // Record wallet transaction
            DB::table('wallet_transactions')->insert([
                'user_id'       => $pr->user_id,
                'type'          => 'credit',
                'amount'        => $pr->amount,
                'balance_after' => $wallet ? (float) $wallet->balance + (float) $pr->amount : $pr->amount,
                'description'   => "Wallet topup approved — {$pr->payment_mode} ref: {$pr->reference_number}",
                'reference'     => 'SPR-' . $pr->id,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        });

        ActivityLogger::log('admin.seller_payment_approved',
            "Seller payment request #{$id} approved — ₹{$pr->amount}",
            null, ['request_id' => $id, 'amount' => $pr->amount, 'seller_id' => $pr->user_id],
            null, $request);

        return response()->json(['message' => "Payment of ₹{$pr->amount} approved and credited to wallet."]);
    }

    /** POST /api/v1/employee/sellers/payment-requests/{id}/reject */
    public function reject(Request $request, int $id): JsonResponse
    {
        $request->validate(['notes' => ['required', 'string', 'max:500']]);

        $pr = SellerPaymentRequest::findOrFail($id);

        if ($pr->status !== 'pending') {
            return response()->json(['message' => 'Request is already ' . $pr->status . '.'], 422);
        }

        $pr->update([
            'status'       => 'rejected',
            'admin_notes'  => $request->notes,
            'processed_at' => now(),
        ]);

        ActivityLogger::log('admin.seller_payment_rejected',
            "Seller payment request #{$id} rejected",
            null, ['request_id' => $id, 'reason' => $request->notes], null, $request);

        return response()->json(['message' => 'Payment request rejected.']);
    }
}
