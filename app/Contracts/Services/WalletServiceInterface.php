<?php

namespace App\Contracts\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;

interface WalletServiceInterface
{
    public function getOrCreateWallet(User $user): Wallet;

    public function topup(User $user, float $amount, string $description, array $meta = []): WalletTransaction;

    public function debit(User $user, float $amount, string $description, array $meta = []): WalletTransaction;

    public function getBalance(User $user): float;
}
