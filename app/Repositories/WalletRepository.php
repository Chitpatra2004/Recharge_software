<?php

namespace App\Repositories;

use App\Contracts\Repositories\WalletRepositoryInterface;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Str;

class WalletRepository implements WalletRepositoryInterface
{
    public function findByUserId(int $userId): ?Wallet
    {
        return Wallet::where('user_id', $userId)->first();
    }

    /**
     * Acquire a pessimistic lock to prevent race conditions on balance changes.
     */
    public function findByUserIdLocked(int $userId): ?Wallet
    {
        return Wallet::where('user_id', $userId)->lockForUpdate()->first();
    }

    public function debit(Wallet $wallet, float $amount, array $txnData): WalletTransaction
    {
        $before = (float) $wallet->balance;
        $after  = $before - $amount;

        // Guard against balance going below the credit limit (normally 0).
        // This is a secondary safeguard — the primary check is hasSufficientBalance()
        // called before the DB transaction. This catches any race conditions that
        // slip past the pessimistic lock due to misconfiguration.
        $floor = -1 * abs((float) $wallet->credit_limit);
        if ($after < $floor) {
            throw new \App\Exceptions\InsufficientBalanceException($amount, $before + (float) $wallet->credit_limit);
        }

        $wallet->balance = $after;
        $wallet->save();

        return WalletTransaction::create(array_merge($txnData, [
            'wallet_id'      => $wallet->id,
            'user_id'        => $wallet->user_id,
            'txn_id'         => $txnData['txn_id'] ?? Str::uuid()->toString(),
            'type'           => 'debit',
            'amount'         => $amount,
            'balance_before' => $before,
            'balance_after'  => $after,
            'status'         => 'completed',
        ]));
    }

    public function credit(Wallet $wallet, float $amount, array $txnData): WalletTransaction
    {
        $before = (float) $wallet->balance;
        $after  = $before + $amount;

        $wallet->balance = $after;
        $wallet->save();

        return WalletTransaction::create(array_merge($txnData, [
            'wallet_id'      => $wallet->id,
            'user_id'        => $wallet->user_id,
            'txn_id'         => $txnData['txn_id'] ?? Str::uuid()->toString(),
            'type'           => 'credit',
            'amount'         => $amount,
            'balance_before' => $before,
            'balance_after'  => $after,
            'status'         => 'completed',
        ]));
    }

    public function reserve(Wallet $wallet, float $amount): void
    {
        $wallet->increment('reserved_balance', $amount);
    }

    public function releaseReserve(Wallet $wallet, float $amount): void
    {
        $wallet->decrement('reserved_balance', $amount);
    }

    public function createWalletForUser(int $userId): Wallet
    {
        return Wallet::create([
            'user_id' => $userId,
            'balance' => 0.00,
            'status'  => 'active',
        ]);
    }
}
