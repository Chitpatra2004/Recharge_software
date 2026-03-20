<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Services\RechargeServiceInterface;
use App\Exceptions\DuplicateTransactionException;
use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\OperatorUnavailableException;
use App\Exceptions\WalletFrozenException;
use App\Http\Controllers\Controller;
use App\Models\RechargeTransaction;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * BuyerRechargeController — buyer/partner-facing API endpoints.
 *
 * All routes here require ApiKeyAuth middleware with specific scopes.
 * Response format is standardised so external partners can parse it
 * consistently without handling multiple shapes.
 *
 * Standard success envelope:
 *   { "success": true, "data": {...}, "meta": {...} }
 *
 * Standard error envelope:
 *   { "success": false, "error": { "code": "ERR_CODE", "message": "..." } }
 */
class BuyerRechargeController extends Controller
{
    public function __construct(
        private readonly RechargeServiceInterface $rechargeService
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/v1/buyer/recharge
    // Scope required: recharge:write
    //
    // Initiates a mobile/DTH recharge.
    // Returns immediately with status=queued — poll /buyer/recharge/{txn_id}
    // or configure a callback URL to receive final status asynchronously.
    // ─────────────────────────────────────────────────────────────────────────
    public function recharge(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'mobile'          => ['required', 'digits_between:10,15'],
            'operator_code'   => ['required', 'string', 'max:30', 'exists:operators,code'],
            'amount'          => ['required', 'numeric', 'min:' . config('recharge.min_amount', 10), 'max:' . config('recharge.max_amount', 10000)],
            'recharge_type'   => ['sometimes', 'in:prepaid,postpaid,dth,broadband'],
            'circle'          => ['sometimes', 'nullable', 'string', 'max:50'],
            'idempotency_key' => ['sometimes', 'string', 'max:128'],
            // callback_url intentionally excluded — use POST /buyer/callback/register
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                'VALIDATION_ERROR',
                'Request validation failed.',
                422,
                $validator->errors()->toArray()
            );
        }

        $data = $validator->validated();

        // Auto-generate idempotency key if client didn't provide one
        $data['idempotency_key'] ??= 'auto_' . Str::uuid()->toString();
        $data['ip_address']       = $request->ip();
        $data['recharge_type']  ??= 'prepaid';

        // FIX M3: per-request callback_url override removed.
        // Previously any single recharge POST could silently change the API key's
        // registered callback URL — a compromised or shared key could redirect
        // all future callbacks to an attacker-controlled URL.
        // Callback URL registration is only allowed via POST /buyer/callback/register.
        unset($data['callback_url']);

        try {
            $transaction = $this->rechargeService->initiate($request->user(), $data);

            return response()->json([
                'success' => true,
                'data'    => [
                    'txn_id'       => $transaction->id,
                    'status'       => $transaction->status,
                    'mobile'       => $transaction->mobile,
                    'operator'     => $transaction->operator_code,
                    'amount'       => number_format((float) $transaction->amount, 2, '.', ''),
                    'commission'   => number_format((float) $transaction->commission, 2, '.', ''),
                    'net_amount'   => number_format((float) $transaction->net_amount, 2, '.', ''),
                    'submitted_at' => $transaction->created_at->toIso8601String(),
                ],
                'meta' => [
                    'message' => 'Recharge submitted and queued for processing.',
                ],
            ], 202);

        } catch (DuplicateTransactionException $e) {
            return $this->errorResponse(
                'DUPLICATE_TRANSACTION',
                'A transaction for this number was submitted within the last 60 seconds.',
                409,
                ['existing_txn_id' => $e->existing->id, 'status' => $e->existing->status]
            );
        } catch (InsufficientBalanceException $e) {
            return $this->errorResponse('INSUFFICIENT_BALANCE', $e->getMessage(), 422);
        } catch (WalletFrozenException $e) {
            return $this->errorResponse('WALLET_FROZEN', $e->getMessage(), 403);
        } catch (OperatorUnavailableException $e) {
            return $this->errorResponse('OPERATOR_UNAVAILABLE', $e->getMessage(), 503);
        } catch (\Throwable $e) {
            Log::error('BuyerRechargeController::recharge failed', [
                'user_id' => $request->user()?->id,
                'data'    => $data,
                'error'   => $e->getMessage(),
            ]);
            return $this->errorResponse('SERVER_ERROR', 'An unexpected error occurred.', 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET /api/v1/buyer/recharge/{txnId}
    // Scope required: recharge:read
    //
    // Polls the status of a previously submitted recharge transaction.
    // ─────────────────────────────────────────────────────────────────────────
    public function status(Request $request, int $txnId): JsonResponse
    {
        $transaction = RechargeTransaction::select([
                'id', 'status', 'mobile', 'operator_code', 'recharge_type',
                'amount', 'commission', 'net_amount', 'operator_ref',
                'failure_reason', 'retry_count', 'processed_at', 'created_at',
            ])
            ->where('id', $txnId)
            ->where('user_id', $request->user()->id) // ownership check
            ->first();

        if (! $transaction) {
            return $this->errorResponse(
                'NOT_FOUND',
                'Transaction not found or does not belong to your account.',
                404
            );
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'txn_id'       => $transaction->id,
                'status'       => $transaction->status,
                'mobile'       => $transaction->mobile,
                'operator'     => $transaction->operator_code,
                'type'         => $transaction->recharge_type,
                'amount'       => number_format((float) $transaction->amount, 2, '.', ''),
                'commission'   => number_format((float) $transaction->commission, 2, '.', ''),
                'net_amount'   => number_format((float) $transaction->net_amount, 2, '.', ''),
                'operator_ref' => $transaction->operator_ref,
                'failure_reason' => $transaction->status === 'failed'
                                        ? $transaction->failure_reason
                                        : null,
                'retry_count'  => $transaction->retry_count,
                'submitted_at' => $transaction->created_at->toIso8601String(),
                'processed_at' => $transaction->processed_at?->toIso8601String(),
                'is_terminal'  => $transaction->isTerminal(),
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET /api/v1/buyer/balance
    // Scope required: wallet:read
    //
    // Returns current wallet balance for the authenticated API user.
    // ─────────────────────────────────────────────────────────────────────────
    public function balance(Request $request): JsonResponse
    {
        $wallet = Wallet::select([
                'balance', 'reserved_balance', 'credit_limit',
                'daily_debit_limit', 'daily_debit_used', 'status',
            ])
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $wallet) {
            return $this->errorResponse('WALLET_NOT_FOUND', 'No wallet found for this account.', 404);
        }

        $available = max(
            0,
            (float) $wallet->balance - (float) $wallet->reserved_balance + (float) $wallet->credit_limit
        );

        return response()->json([
            'success' => true,
            'data'    => [
                'balance'           => number_format((float) $wallet->balance, 2, '.', ''),
                'available_balance' => number_format($available, 2, '.', ''),
                'reserved_balance'  => number_format((float) $wallet->reserved_balance, 2, '.', ''),
                'credit_limit'      => number_format((float) $wallet->credit_limit, 2, '.', ''),
                'daily_limit'       => $wallet->daily_debit_limit
                                         ? number_format((float) $wallet->daily_debit_limit, 2, '.', '')
                                         : null,
                'daily_used'        => number_format((float) $wallet->daily_debit_used, 2, '.', ''),
                'status'            => $wallet->status,
            ],
            'meta' => [
                'fetched_at' => now()->toIso8601String(),
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET /api/v1/buyer/transactions
    // Scope required: recharge:read
    //
    // Paginated transaction history for the authenticated user.
    // Supports filters: status, mobile, operator_code, date_from, date_to
    // ─────────────────────────────────────────────────────────────────────────
    public function transactions(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status'        => ['sometimes', 'in:queued,processing,success,failed,refunded,partial'],
            'mobile'        => ['sometimes', 'digits_between:10,15'],
            'operator_code' => ['sometimes', 'string', 'max:30'],
            'date_from'     => ['sometimes', 'date', 'before_or_equal:date_to'],
            'date_to'       => ['sometimes', 'date', 'after_or_equal:date_from'],
            'per_page'      => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('VALIDATION_ERROR', 'Invalid filter parameters.', 422, $validator->errors()->toArray());
        }

        $f = $validator->validated();

        $query = RechargeTransaction::select([
                'id', 'status', 'mobile', 'operator_code', 'recharge_type',
                'amount', 'commission', 'net_amount', 'operator_ref',
                'failure_reason', 'processed_at', 'created_at',
            ])
            ->where('user_id', $request->user()->id)
            // Always filter created_at — triggers partition pruning
            ->whereBetween('created_at', [
                $f['date_from'] ?? now()->subDays(30)->startOfDay(),
                ($f['date_to']  ?? now())->endOfDay(),
            ])
            ->when(isset($f['status']),        fn ($q) => $q->where('status', $f['status']))
            ->when(isset($f['mobile']),        fn ($q) => $q->where('mobile', $f['mobile']))
            ->when(isset($f['operator_code']), fn ($q) => $q->where('operator_code', $f['operator_code']))
            ->orderByDesc('created_at');

        $perPage      = (int) ($f['per_page'] ?? 20);
        $transactions = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $transactions->items(),
            'meta'    => [
                'current_page' => $transactions->currentPage(),
                'per_page'     => $transactions->perPage(),
                'total'        => $transactions->total(),
                'last_page'    => $transactions->lastPage(),
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/v1/buyer/callback/register
    // Scope required: recharge:write
    //
    // Register or update the callback URL for this API key.
    // The platform will POST to this URL on each transaction status change.
    // ─────────────────────────────────────────────────────────────────────────
    public function registerCallback(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'callback_url' => ['required', 'url', 'max:500'],
            'secret'       => ['sometimes', 'string', 'min:16', 'max:128'],
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('VALIDATION_ERROR', 'Validation failed.', 422, $validator->errors()->toArray());
        }

        $apiKey = $request->attributes->get('resolved_api_key');

        if (! $apiKey) {
            return $this->errorResponse('AUTH_ERROR', 'API key required for callback registration.', 401);
        }

        $updates = ['callback_url' => $request->input('callback_url')];
        if ($request->filled('secret')) {
            $updates['callback_secret'] = hash('sha256', $request->input('secret'));
        }

        DB::table('api_keys')->where('id', $apiKey->id)->update($updates);

        return response()->json([
            'success' => true,
            'data'    => [
                'callback_url' => $request->input('callback_url'),
                'registered_at' => now()->toIso8601String(),
            ],
            'meta' => [
                'message' => 'Callback URL registered. Recharge status updates will be POSTed to this URL.',
                'note'    => $request->filled('secret')
                    ? 'Callback requests will include X-Signature: HMAC-SHA256(body, your_secret).'
                    : 'No secret provided — callbacks will not be signed.',
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function errorResponse(
        string $code,
        string $message,
        int    $status,
        array  $details = []
    ): JsonResponse {
        $body = [
            'success' => false,
            'error'   => [
                'code'    => $code,
                'message' => $message,
            ],
        ];

        if (! empty($details)) {
            $body['error']['details'] = $details;
        }

        return response()->json($body, $status);
    }
}
