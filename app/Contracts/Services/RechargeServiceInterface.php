<?php

namespace App\Contracts\Services;

use App\Models\RechargeTransaction;
use App\Models\User;

interface RechargeServiceInterface
{
    /**
     * Validate, debit wallet, persist transaction, and dispatch queue job.
     *
     * @param  User   $user       Authenticated retailer/API user
     * @param  array  $data       Validated request data
     * @return RechargeTransaction
     */
    public function initiate(User $user, array $data): RechargeTransaction;

    /**
     * Called by the queue worker — actually sends to operator API.
     */
    public function process(RechargeTransaction $transaction): void;

    /**
     * Handle a callback from the operator confirming success/failure.
     */
    public function handleCallback(string $operatorRef, string $status, array $payload): void;

    /**
     * Manually refund a failed/disputed transaction.
     */
    public function refund(RechargeTransaction $transaction, User $requestedBy): void;

    /**
     * Mark transaction as permanently failed and release the wallet reserve.
     * Public so jobs can call it as a safety net on infrastructure failure.
     */
    public function handleFailure(RechargeTransaction $transaction, string $reason): void;
}
