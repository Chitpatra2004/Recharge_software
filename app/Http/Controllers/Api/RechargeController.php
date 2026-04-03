<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Services\RechargeServiceInterface;
use App\Exceptions\DuplicateTransactionException;
use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\OperatorUnavailableException;
use App\Exceptions\WalletFrozenException;
use App\Http\Controllers\Controller;
use App\Http\Requests\RechargeRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RechargeController extends Controller
{
    public function __construct(private readonly RechargeServiceInterface $rechargeService) {}

    /**
     * POST /api/v1/recharge
     *
     * Real-time synchronous processing — no queue workers required.
     * Reserves wallet balance → calls operator API → returns final result.
     *
     * HTTP status codes:
     *   200 — success or clean failure (amount refunded)
     *   202 — pending (operator API timed out; cron will retry)
     *   409 — duplicate transaction
     *   422 — insufficient balance / validation error
     *   403 — wallet frozen
     *   503 — operator unavailable (no configured routes)
     *   500 — unexpected server error
     */
    public function store(RechargeRequest $request): JsonResponse
    {
        try {
            $data                = $request->validated();
            $data['ip_address']  = $request->ip();

            // ── Synchronous: validate → reserve → API call → finalise ──────
            $transaction = $this->rechargeService->processSync($request->user(), $data);

            $status = $transaction->status;

            ActivityLogger::log(
                'recharge.' . $status,
                "Recharge {$status} for {$transaction->mobile}",
                $transaction,
                ['amount' => $transaction->amount, 'operator_ref' => $transaction->operator_ref],
                $request->user()->id,
                $request
            );

            // ── Build response based on final status ───────────────────────
            if ($status === 'success') {
                return response()->json([
                    'message'        => 'Recharge successful.',
                    'transaction_id' => $transaction->id,
                    'status'         => 'success',
                    'amount'         => $transaction->amount,
                    'mobile'         => $transaction->mobile,
                    'operator'       => $transaction->operator_code,
                    'operator_ref'   => $transaction->operator_ref,
                    'processed_at'   => $transaction->processed_at,
                ], 200);
            }

            if ($status === 'pending') {
                return response()->json([
                    'message'        => 'Recharge is being processed. You will be notified once confirmed.',
                    'transaction_id' => $transaction->id,
                    'status'         => 'pending',
                    'amount'         => $transaction->amount,
                    'mobile'         => $transaction->mobile,
                ], 202);
            }

            // Failed — wallet already refunded by processSync()
            return response()->json([
                'message'        => 'Recharge failed. Amount has been refunded to your wallet.',
                'transaction_id' => $transaction->id,
                'status'         => 'failed',
                'amount'         => $transaction->amount,
                'mobile'         => $transaction->mobile,
                'reason'         => $transaction->failure_reason,
            ], 200);

        } catch (DuplicateTransactionException $e) {
            return response()->json([
                'message'        => 'Duplicate transaction. This recharge was already submitted.',
                'transaction_id' => $e->existing->id,
                'status'         => $e->existing->status,
            ], 409);

        } catch (InsufficientBalanceException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);

        } catch (WalletFrozenException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 403);

        } catch (OperatorUnavailableException $e) {
            return response()->json([
                'message' => 'Service temporarily unavailable. Please try again in a few minutes.',
            ], 503);

        } catch (\Throwable $e) {
            Log::error('Recharge processSync error', [
                'user_id' => $request->user()?->id,
                'mobile'  => $request->input('mobile'),
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Server error. Please try again.',
            ], 500);
        }
    }

    /**
     * GET /api/v1/recharge/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $transaction = $request->user()
            ->rechargeTransactions()
            ->with('attempts')
            ->findOrFail($id);

        return response()->json(['data' => $transaction]);
    }

    /**
     * POST /api/v1/recharge/{id}/refund  (Admin only)
     */
    public function refund(Request $request, int $id): JsonResponse
    {
        $transaction = \App\Models\RechargeTransaction::findOrFail($id);

        $this->rechargeService->refund($transaction, $request->user());

        ActivityLogger::log(
            'recharge.refunded',
            "Refund issued for txn #{$id}",
            $transaction,
            [],
            $request->user()->id,
            $request
        );

        return response()->json(['message' => 'Refund processed.']);
    }

    /**
     * POST /api/v1/recharge/callback  (Operator webhook)
     *
     * HMAC-SHA256 verified via X-Signature header (FIX H3).
     */
    public function callback(Request $request): JsonResponse
    {
        $secret = config('recharge.callback_secret');
        if ($secret) {
            $signature = $request->header('X-Signature')
                      ?? $request->header('X-Callback-Signature');

            if (! $signature) {
                Log::warning('Callback: missing X-Signature header', ['ip' => $request->ip()]);
                return response()->json(['message' => 'Unauthorized — X-Signature header required.'], 401);
            }

            $expected = hash_hmac('sha256', $request->getContent(), $secret);

            if (! hash_equals($expected, (string) $signature)) {
                Log::warning('Callback: invalid HMAC signature', ['ip' => $request->ip()]);
                return response()->json(['message' => 'Unauthorized — invalid signature.'], 401);
            }
        }

        $operatorRef = $request->input('ref_id')
                    ?? $request->input('txn_id')
                    ?? $request->input('operator_ref');
        $status      = $request->input('status');

        if (! $operatorRef || ! $status) {
            return response()->json(['message' => 'Invalid callback — ref_id and status are required.'], 400);
        }

        $this->rechargeService->handleCallback($operatorRef, $status, $request->all());

        return response()->json(['message' => 'Callback received.']);
    }
}
