<?php

namespace App\Jobs;

use App\Contracts\Repositories\RechargeRepositoryInterface;
use App\Contracts\Services\RechargeServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Log;

/**
 * RetryRecharge — scheduled retry job for failed recharge attempts.
 *
 * Dispatched by RechargeService::scheduleRetryOrFail() with an exponential
 * back-off delay (2^retry_count minutes: 2 min → 4 min → 8 min).
 *
 * Kept separate from ProcessRecharge so the queue monitor can distinguish
 * first-attempt jobs from retry jobs and apply different alerting thresholds.
 *
 * Uses the same WithoutOverlapping middleware so a retry job can't race with
 * a concurrent callback that arrives while the job is executing.
 */
class RetryRecharge implements ShouldQueue
{
    use Queueable, InteractsWithQueue;

    public int $tries   = 1;
    public int $timeout = 120;

    public function __construct(public readonly int $transactionId) {}

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping($this->transactionId))
                ->releaseAfter(60)
                ->expireAfter(130),
        ];
    }

    public function handle(
        RechargeServiceInterface    $rechargeService,
        RechargeRepositoryInterface $rechargeRepo
    ): void {
        $transaction = $rechargeRepo->findById($this->transactionId);

        if (! $transaction) {
            Log::warning("RetryRecharge: transaction #{$this->transactionId} not found — skipping.");
            return;
        }

        if ($transaction->isTerminal()) {
            Log::info("RetryRecharge: transaction #{$this->transactionId} already terminal — skipping.");
            return;
        }

        Log::info("RetryRecharge: processing retry #{$transaction->retry_count} for transaction #{$this->transactionId}");

        $rechargeService->process($transaction);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("RetryRecharge: job failed for transaction #{$this->transactionId}", [
            'exception' => $exception->getMessage(),
        ]);
    }
}
