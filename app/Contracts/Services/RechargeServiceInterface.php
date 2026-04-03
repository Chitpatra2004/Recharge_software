<?php

namespace App\Contracts\Services;

use App\Models\RechargeTransaction;
use App\Models\User;

interface RechargeServiceInterface
{
    /**
     * [SYNC] Validate → reserve wallet → persist → call operator API → finalise.
     * Returns the transaction with its terminal/pending status set.
     *
     * Replaces the old initiate() + queue-dispatch pattern.
     * Everything runs in the HTTP request; no workers needed.
     */
    public function processSync(User $user, array $data): RechargeTransaction;

    /**
     * [LEGACY] Validate, debit wallet, persist transaction, and dispatch queue job.
     * Kept so RetryRecharge jobs (cron-triggered for pending txns) still work.
     */
    public function initiate(User $user, array $data): RechargeTransaction;

    /**
     * [LEGACY] Called by queue worker / cron — sends to operator API.
     * Used by RetryRecharge job to retry timed-out / pending transactions.
     */
    public function process(RechargeTransaction $transaction): void;

    /**
     * Handle an asynchronous callback from the operator.
     */
    public function handleCallback(string $operatorRef, string $status, array $payload): void;

    /**
     * Manually refund a failed/disputed transaction.
     */
    public function refund(RechargeTransaction $transaction, User $requestedBy): void;

    /**
     * Mark transaction permanently failed and release the wallet reserve.
     */
    public function handleFailure(RechargeTransaction $transaction, string $reason): void;
}
