<?php

namespace App\Services;

use App\Contracts\Repositories\WalletRepositoryInterface;
use App\Contracts\Services\WalletServiceInterface;
use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\WalletFrozenException;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WalletService implements WalletServiceInterface
{
    public function __construct(
        private readonly WalletRepositoryInterface $walletRepo
    ) {}

    public function getOrCreateWallet(User $user): Wallet
    {
        $wallet = $this->walletRepo->findByUserId($user->id);

        if (! $wallet) {
            $wallet = $this->walletRepo->createWalletForUser($user->id);
        }

        return $wallet;
    }

    public function topup(User $user, float $amount, string $description, array $meta = []): WalletTransaction
    {
        return DB::transaction(function () use ($user, $amount, $description, $meta) {
            $wallet = $this->walletRepo->findByUserIdLocked($user->id);

            if (! $wallet) {
                $wallet = $this->walletRepo->createWalletForUser($user->id);
            }

            if (! $wallet->isActive()) {
                throw new WalletFrozenException();
            }

            $txn = $this->walletRepo->credit($wallet, $amount, [
                'txn_id'      => Str::uuid()->toString(),
                'description' => $description,
                'ip_address'  => $meta['ip_address'] ?? null,
                'reference_type' => $meta['reference_type'] ?? null,
                'reference_id'   => $meta['reference_id'] ?? null,
            ]);

            $this->invalidateBalanceCache($user->id);

            return $txn;
        });
    }

    public function debit(User $user, float $amount, string $description, array $meta = []): WalletTransaction
    {
        return DB::transaction(function () use ($user, $amount, $description, $meta) {
            $wallet = $this->walletRepo->findByUserIdLocked($user->id);

            if (! $wallet || ! $wallet->isActive()) {
                throw new WalletFrozenException();
            }

            if (! $wallet->hasSufficientBalance($amount)) {
                throw new InsufficientBalanceException($amount, $wallet->availableBalance());
            }

            $txn = $this->walletRepo->debit($wallet, $amount, [
                'txn_id'      => Str::uuid()->toString(),
                'description' => $description,
                'ip_address'  => $meta['ip_address'] ?? null,
                'reference_type' => $meta['reference_type'] ?? null,
                'reference_id'   => $meta['reference_id'] ?? null,
            ]);

            $this->invalidateBalanceCache($user->id);

            return $txn;
        });
    }

    public function getBalance(User $user): float
    {
        return Cache::remember(
            "wallet_balance:{$user->id}",
            60,
            fn () => (float) ($this->walletRepo->findByUserId($user->id)?->balance ?? 0.00)
        );
    }

    private function invalidateBalanceCache(int $userId): void
    {
        Cache::forget("wallet_balance:{$userId}");
    }
}
