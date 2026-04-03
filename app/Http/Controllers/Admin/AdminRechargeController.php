<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RechargeTransaction;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminRechargeController extends Controller
{
    /**
     * POST /api/v1/employee/recharges/{id}/refund
     *
     * Admin-side refund accessible via employee token.
     * Handles all non-terminal statuses (pending, queued, processing, failed, partial).
     */
    public function refund(Request $request, int $id): JsonResponse
    {
        $transaction = RechargeTransaction::findOrFail($id);

        // Already refunded or successful — nothing to do
        if (in_array($transaction->status, ['refunded', 'success'])) {
            return response()->json([
                'message' => "Transaction #{$id} is already '{$transaction->status}' — cannot refund.",
            ], 422);
        }

        DB::transaction(function () use ($transaction) {
            // Credit wallet back to the seller/user
            $wallet = DB::table('wallets')
                ->where('user_id', $transaction->user_id)
                ->lockForUpdate()
                ->first();

            if ($wallet) {
                $balanceBefore = (float) $wallet->balance;
                $refundAmount  = (float) $transaction->amount;
                $balanceAfter  = $balanceBefore + $refundAmount;

                DB::table('wallets')
                    ->where('id', $wallet->id)
                    ->update(['balance' => $balanceAfter, 'updated_at' => now()]);

                DB::table('wallet_transactions')->insert([
                    'wallet_id'      => $wallet->id,
                    'user_id'        => $transaction->user_id,
                    'txn_id'         => 'REFUND-' . strtoupper(Str::random(10)),
                    'type'           => 'credit',
                    'amount'         => $refundAmount,
                    'balance_before' => $balanceBefore,
                    'balance_after'  => $balanceAfter,
                    'description'    => "Admin refund for recharge txn #{$transaction->id} ({$transaction->mobile})",
                    'reference_type' => RechargeTransaction::class,
                    'reference_id'   => $transaction->id,
                    'status'         => 'completed',
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }

            // Mark transaction as refunded
            DB::table('recharge_transactions')
                ->where('id', $transaction->id)
                ->update([
                    'status'       => 'refunded',
                    'processed_at' => now(),
                    'updated_at'   => now(),
                ]);
        });

        ActivityLogger::log(
            'admin.recharge_refunded',
            "Admin refunded recharge #{$id} (₹{$transaction->amount}) for mobile {$transaction->mobile}",
            null,
            ['txn_id' => $id, 'amount' => $transaction->amount, 'prev_status' => $transaction->status],
            null,
            $request
        );

        return response()->json([
            'message' => "Refund of ₹{$transaction->amount} processed successfully for {$transaction->mobile}.",
        ]);
    }

    /**
     * GET /api/v1/employee/recharges/{id}
     *
     * Get a single transaction detail for admin review.
     */
    public function show(int $id): JsonResponse
    {
        $txn = DB::table('recharge_transactions as rt')
            ->leftJoin('users as u', 'u.id', '=', 'rt.user_id')
            ->where('rt.id', $id)
            ->whereNull('rt.deleted_at')
            ->select([
                'rt.*',
                'u.name as seller_name',
                'u.email as seller_email',
                'u.mobile as seller_mobile',
            ])
            ->first();

        if (! $txn) {
            return response()->json(['message' => 'Transaction not found.'], 404);
        }

        return response()->json(['data' => $txn]);
    }
}
