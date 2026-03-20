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
     * Initiates a recharge — debits wallet and queues processing.
     */
    public function store(RechargeRequest $request): JsonResponse
    {
        try {
            $data                  = $request->validated();
            $data['ip_address']    = $request->ip();

            $transaction = $this->rechargeService->initiate($request->user(), $data);

            ActivityLogger::log(
                'recharge.initiated',
                "Recharge queued for {$transaction->mobile}",
                $transaction,
                ['amount' => $transaction->amount],
                $request->user()->id,
                $request
            );

            return response()->json([
                'message'        => 'Recharge queued successfully.',
                'transaction_id' => $transaction->id,
                'status'         => $transaction->status,
                'amount'         => $transaction->amount,
                'mobile'         => $transaction->mobile,
            ], 202); // 202 Accepted — async processing

        } catch (DuplicateTransactionException $e) {
            return response()->json([
                'message'        => 'Duplicate transaction.',
                'transaction_id' => $e->existing->id,
                'status'         => $e->existing->status,
            ], 409);

        } catch (InsufficientBalanceException $e) {
            return response()->json(['message' => $e->getMessage()], 422);

        } catch (WalletFrozenException $e) {
            return response()->json(['message' => $e->getMessage()], 403);

        } catch (OperatorUnavailableException $e) {
            return response()->json(['message' => $e->getMessage()], 503);

        } catch (\Throwable $e) {
            Log::error('Recharge initiation error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Server error. Please try again.'], 500);
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

        ActivityLogger::log('recharge.refunded', "Refund issued for txn #{$id}", $transaction, [], $request->user()->id, $request);

        return response()->json(['message' => 'Refund processed.']);
    }

    /**
     * POST /api/v1/recharge/callback  (Operator webhook)
     *
     * Security: the callback_secret in config/recharge.php must match the
     * RECHARGE_CALLBACK_SECRET in .env.  When set, every callback must carry
     * an X-Signature header containing  HMAC-SHA256(raw_body, secret).
     * Use hash_equals() to prevent timing-attack enumeration of the secret.
     */
    public function callback(Request $request): JsonResponse
    {
        // ── HMAC-SHA256 signature verification ───────────────────────────────
        $secret = config('recharge.callback_secret');
        if ($secret) {
            // FIX H3: Accept signature from HEADERS ONLY.
            // Accepting it from $request->input('signature') was wrong because
            // the signature field would be PART of the body being verified —
            // an attacker could craft any body and include a matching signature
            // in that same body (circular trust).
            $signature = $request->header('X-Signature')
                      ?? $request->header('X-Callback-Signature');

            if (! $signature) {
                Log::warning('Callback: missing X-Signature header', ['ip' => $request->ip()]);
                return response()->json(['message' => 'Unauthorized — X-Signature header required.'], 401);
            }

            $expected = hash_hmac('sha256', $request->getContent(), $secret);

            if (! hash_equals($expected, (string) $signature)) {
                Log::warning('Callback: invalid HMAC signature', [
                    'ip'        => $request->ip(),
                    'signature' => substr((string) $signature, 0, 8) . '…',
                ]);
                return response()->json(['message' => 'Unauthorized — invalid signature.'], 401);
            }
        }

        // ── Extract fields ────────────────────────────────────────────────────
        $operatorRef = $request->input('ref_id')
                    ?? $request->input('txn_id')
                    ?? $request->input('operator_ref');
        $status      = $request->input('status');
        $payload     = $request->all();

        if (! $operatorRef || ! $status) {
            return response()->json(['message' => 'Invalid callback — ref_id and status are required.'], 400);
        }

        $this->rechargeService->handleCallback($operatorRef, $status, $payload);

        return response()->json(['message' => 'Callback received.']);
    }
}
