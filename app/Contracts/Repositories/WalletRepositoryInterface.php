<?php

namespace App\Contracts\Repositories;

use App\Models\Wallet;
use App\Models\WalletTransaction;

interface WalletRepositoryInterface
{
    public function findByUserId(int $userId): ?Wallet;

    public function findByUserIdLocked(int $userId): ?Wallet;   // SELECT FOR UPDATE

    public function debit(Wallet $wallet, float $amount, array $txnData): WalletTransaction;

    public function credit(Wallet $wallet, float $amount, array $txnData): WalletTransaction;

    public function reserve(Wallet $wallet, float $amount): void;

    public function releaseReserve(Wallet $wallet, float $amount): void;

    public function createWalletForUser(int $userId): Wallet;
}
