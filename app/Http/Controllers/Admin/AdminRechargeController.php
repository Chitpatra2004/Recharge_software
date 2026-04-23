<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RechargeTransaction;
use App\Contracts\Services\RechargeServiceInterface;
use App\Services\ApiRequestLogSchema;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminRechargeController extends Controller
{
    public function __construct(
        private readonly RechargeServiceInterface $rechargeService
    ) {}

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

        $attempts = DB::table('recharge_attempts as ra')
            ->leftJoin('operator_routes as orr', 'orr.id', '=', 'ra.operator_route_id')
            ->where('ra.recharge_transaction_id', $id)
            ->orderBy('ra.attempt_number')
            ->get([
                'ra.id',
                'ra.attempt_number',
                'ra.status',
                'ra.request_url',
                'ra.request_payload',
                'ra.response_payload',
                'ra.response_code',
                'ra.duration_ms',
                'ra.error_message',
                'ra.created_at',
                'orr.operator_code',
                'orr.api_endpoint',
            ]);

        $referenceValues = array_values(array_filter(array_unique([
            (string) $txn->id,
            $txn->idempotency_key ?? null,
            $txn->api_ref ?? null,
            $txn->operator_ref ?? null,
        ])));

        $apiLogsQuery = DB::table('api_request_logs')
            ->where(function ($q) use ($referenceValues, $txn) {
                if (ApiRequestLogSchema::has('reference_id') && ! empty($referenceValues)) {
                    foreach ($referenceValues as $ref) {
                        $q->orWhere('reference_id', $ref);
                    }
                }

                $q->orWhere(function ($sub) use ($txn) {
                    $sub->where('path', 'like', '%recharge%')
                        ->where('user_id', $txn->user_id)
                        ->where('created_at', '>=', now()->subDays(7));
                });
            })
            ->orderByDesc('created_at')
            ->limit(20);

        $apiLogColumns = [
            'id',
            'method',
            'path',
            ApiRequestLogSchema::has('reference_id')
                ? 'reference_id'
                : DB::raw('NULL as reference_id'),
            'status_code',
            'response_time_ms',
            'ip_address',
            ApiRequestLogSchema::has('request_payload')
                ? 'request_payload'
                : DB::raw('NULL as request_payload'),
            'error_message',
            'created_at',
        ];

        $apiLogs = $apiLogsQuery->get($apiLogColumns);

        return response()->json([
            'data' => [
                'transaction' => $txn,
                'attempts'    => $attempts,
                'api_logs'    => $apiLogs,
            ],
        ]);
    }

    /**
     * POST /api/v1/employee/recharges/{id}/resend
     *
     * Admin manual retry / resend for non-successful transactions.
     */
    public function resend(Request $request, int $id): JsonResponse
    {
        $transaction = RechargeTransaction::findOrFail($id);

        if (in_array($transaction->status, ['success', 'refunded'], true)) {
            return response()->json([
                'message' => "Transaction #{$id} is already '{$transaction->status}' and cannot be resent.",
            ], 422);
        }

        if (in_array($transaction->status, ['failed', 'partial'], true)) {
            $transaction->update([
                'status'         => 'pending',
                'failure_reason' => null,
                'processed_at'   => null,
            ]);
            $transaction->refresh();
        }

        $this->rechargeService->process($transaction);
        $transaction->refresh();

        ActivityLogger::log(
            'admin.recharge_resent',
            "Admin resent recharge #{$id} for mobile {$transaction->mobile}",
            null,
            ['txn_id' => $id, 'status' => $transaction->status],
            null,
            $request
        );

        return response()->json([
            'message' => "Transaction #{$id} resent successfully.",
            'status'  => $transaction->status,
        ]);
    }
}
