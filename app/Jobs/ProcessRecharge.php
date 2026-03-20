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
 * ProcessRecharge — primary queue job for recharge execution.
 *
 * tries = 1: retry logic is handled inside RechargeService::scheduleRetryOrFail()
 *            which dispatches a separate RetryRecharge job with exponential back-off.
 *            This gives us explicit control over delays instead of relying on the
 *            framework's built-in tries/backoff (which would re-queue immediately).
 *
 * timeout = 120: must exceed (max_routes × timeout_seconds_per_route).
 *
 * WithoutOverlapping: prevents two workers from processing the same transaction ID
 * simultaneously (e.g. when a delayed retry job overlaps with a callback).
 * Works with any Laravel cache driver — file, database, or Redis.
 */
class ProcessRecharge implements ShouldQueue
{
    use Queueable, InteractsWithQueue;

    public int $tries   = 1;
    public int $timeout = 120;

    public function __construct(public readonly int $transactionId) {}

    /**
     * Prevent concurrent execution for the same transaction.
     * releaseAfter(60)  — if lock can't be acquired, release the job back to the
     *                      queue and retry after 60 seconds.
     * expireAfter(130)  — safety valve: auto-release the lock if the job hangs
     *                      past its timeout (120 s) + a small buffer.
     */
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
            Log::warning("ProcessRecharge: transaction #{$this->transactionId} not found — skipping.");
            return;
        }

        if ($transaction->isTerminal()) {
            Log::info("ProcessRecharge: transaction #{$this->transactionId} already terminal — skipping.");
            return;
        }

        $rechargeService->process($transaction);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("ProcessRecharge: job failed for transaction #{$this->transactionId}", [
            'exception' => $exception->getMessage(),
            'trace'     => $exception->getTraceAsString(),
        ]);

        // FIX M4: The previous implementation called process() one more time here.
        // This was dangerous: scheduleRetryOrFail() may have already set status='queued'
        // and dispatched a RetryRecharge job. Calling process() again would see
        // status='queued' (non-terminal), skip the isTerminal() guard, and execute
        // a second simultaneous processing attempt.
        //
        // Safe approach: only release the wallet reserve if the transaction is still
        // in a non-terminal, non-retry-scheduled state. Let RetryRecharge handle
        // actual reprocessing.
        try {
            /** @var RechargeRepositoryInterface $repo */
            $repo = app(RechargeRepositoryInterface::class);
            $txn  = $repo->findById($this->transactionId);

            // Only intervene if the transaction is stuck in 'processing' with no
            // retry scheduled — meaning the job crashed before service logic ran.
            if ($txn && $txn->status === 'processing') {
                // Release reserved balance and mark failed — the queue will not retry
                app(RechargeServiceInterface::class)->handleFailure(
                    $txn,
                    "Job infrastructure failure: {$exception->getMessage()}"
                );
            }
        } catch (\Throwable $e) {
            Log::error("ProcessRecharge: failed() safety net also failed for #{$this->transactionId}", [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
