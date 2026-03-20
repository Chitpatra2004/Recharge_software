<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Services\WalletServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\WalletTopupRequest;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __construct(private readonly WalletServiceInterface $walletService) {}

    /**
     * GET /api/v1/wallet/balance
     */
    public function balance(Request $request): JsonResponse
    {
        $balance = $this->walletService->getBalance($request->user());

        return response()->json(['balance' => $balance]);
    }

    /**
     * GET /api/v1/wallet/transactions
     */
    public function transactions(Request $request): JsonResponse
    {
        $wallet = $this->walletService->getOrCreateWallet($request->user());

        $transactions = $wallet->transactions()
            ->select([
                'id', 'wallet_id', 'user_id', 'txn_id', 'type',
                'amount', 'balance_before', 'balance_after',
                'status', 'description', 'reference_type',
                'reference_id', 'created_at',
            ])
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return response()->json(['data' => $transactions]);
    }

    /**
     * POST /api/v1/wallet/self-topup  (User — add money to own wallet via UPI)
     */
    public function selfTopup(Request $request): JsonResponse
    {
        $request->validate([
            'amount'  => ['required', 'numeric', 'min:10', 'max:50000'],
            'upi_ref' => ['required', 'string', 'max:100'],
        ]);

        $amount = (float) $request->amount;
        $user   = $request->user();

        $txn = $this->walletService->topup(
            $user,
            $amount,
            'Wallet topup via UPI',
            ['ip_address' => $request->ip(), 'reference_type' => 'upi_topup']
        );

        ActivityLogger::log('wallet.self_topup', "Wallet topup ₹{$amount}", $user, ['amount' => $amount], $user->id, $request);

        return response()->json([
            'message'     => 'Wallet topped up successfully.',
            'new_balance' => $txn->balance_after,
            'txn_id'      => $txn->txn_id,
            'amount'      => $amount,
        ]);
    }

    /**
     * POST /api/v1/wallet/topup  (Admin only — top up any user's wallet)
     */
    public function topup(WalletTopupRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $targetUser = User::findOrFail($validated['user_id']);

        $txn = $this->walletService->topup(
            $targetUser,
            (float) $validated['amount'],
            $validated['description'],
            ['ip_address' => $request->ip()]
        );

        ActivityLogger::log(
            'wallet.topup',
            "Wallet topped up: {$validated['amount']} for user #{$targetUser->id}",
            $targetUser,
            ['amount' => $validated['amount']],
            $request->user()->id,
            $request
        );

        return response()->json([
            'message'        => 'Wallet topped up.',
            'new_balance'    => $txn->balance_after,
            'transaction_id' => $txn->id,
        ]);
    }
}
