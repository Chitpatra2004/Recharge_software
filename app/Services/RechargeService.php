<?php

namespace App\Services;

use App\Contracts\Repositories\OperatorRepositoryInterface;
use App\Contracts\Repositories\RechargeRepositoryInterface;
use App\Contracts\Repositories\WalletRepositoryInterface;
use App\Contracts\Services\RechargeServiceInterface;
use App\Events\RechargeCompleted;
use App\Events\RechargeFailed;
use App\Events\RechargeInitiated;
use App\Exceptions\DuplicateTransactionException;
use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\OperatorUnavailableException;
use App\Exceptions\WalletFrozenException;
use App\Jobs\ProcessRecharge;
use App\Jobs\RetryRecharge;
use App\Models\RechargeAttempt;
use App\Models\RechargeTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RechargeService implements RechargeServiceInterface
{
    public function __construct(
        private readonly RechargeRepositoryInterface  $rechargeRepo,
        private readonly WalletRepositoryInterface    $walletRepo,
        private readonly OperatorRepositoryInterface  $operatorRepo,
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // Step 1 · initiate()  — called by HTTP controller
    //
    // Responsibilities:
    //   1. Idempotency key dedup (exact match → return existing record)
    //   2. 60-second window dedup (same mobile+operator+type+amount)
    //   3. Validate operator routes exist
    //   4. Pessimistic-lock wallet, reserve balance
    //   5. Persist transaction with status = 'queued'
    //   6. Dispatch ProcessRecharge job
    // ─────────────────────────────────────────────────────────────────────────

    public function initiate(User $user, array $data): RechargeTransaction
    {
        // ── Fast idempotency check (before acquiring DB lock) ────────────────
        $existing = $this->rechargeRepo->findByIdempotencyKey($data['idempotency_key']);
        if ($existing) {
            throw new DuplicateTransactionException($existing);
        }

        // ── Ensure at least one operator route exists before touching wallet ──
        $routes = $this->operatorRepo->getActiveRoutes(
            $data['operator_code'],
            $data['recharge_type'] ?? 'prepaid',
            (float) $data['amount']
        );
        if ($routes->isEmpty()) {
            throw new OperatorUnavailableException($data['operator_code']);
        }

        return DB::transaction(function () use ($user, $data) {

            // ── 60-second window duplicate prevention ─────────────────────
            // Runs inside the transaction so the check is serializable with
            // the subsequent INSERT — prevents race conditions where two
            // requests slip past a pre-transaction check simultaneously.
            $this->assertNoDuplicateInWindow($user->id, $data);

            // ── Pessimistic-lock wallet row ───────────────────────────────
            $wallet = $this->walletRepo->findByUserIdLocked($user->id);

            if (! $wallet || ! $wallet->isActive()) {
                throw new WalletFrozenException();
            }

            $commission = round((float) $data['amount'] * ($user->commission_rate / 100), 2);
            $netAmount  = round((float) $data['amount'] - $commission, 2);

            if (! $wallet->hasSufficientBalance((float) $data['amount'])) {
                throw new InsufficientBalanceException(
                    (float) $data['amount'],
                    $wallet->availableBalance()
                );
            }

            // ── Reserve balance (locked; released on terminal status) ─────
            // We do NOT debit yet — the balance is only moved to
            // reserved_balance. Actual debit happens in finalizeDebit()
            // after a confirmed success from the operator.
            $this->walletRepo->reserve($wallet, (float) $data['amount']);

            // ── Persist transaction ───────────────────────────────────────
            $transaction = $this->rechargeRepo->create([
                'user_id'          => $user->id,
                'buyer_id'         => $data['buyer_id']      ?? null,
                'idempotency_key'  => $data['idempotency_key'],
                'mobile'           => $data['mobile'],
                'operator_code'    => $data['operator_code'],
                'circle'           => $data['circle']        ?? null,
                'recharge_type'    => $data['recharge_type'] ?? 'prepaid',
                'amount'           => $data['amount'],
                'commission'       => $commission,
                'net_amount'       => $netAmount,
                'status'           => 'queued',
                'ip_address'       => $data['ip_address']    ?? null,
            ]);

            // ── Async processing — tiny delay lets DB replica catch up ────
            ProcessRecharge::dispatch($transaction->id)
                ->onQueue(config('recharge.queue', 'recharges'))
                ->delay(now()->addSeconds(2));

            event(new RechargeInitiated($transaction));

            return $transaction;
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Step 2 · process()  — called by ProcessRecharge / RetryRecharge job
    //
    // Flow:
    //   • Guard: skip if already terminal (idempotent worker)
    //   • Mark 'processing'
    //   • Loop operator routes in priority order, attempt HTTP call each
    //   • On success → finalizeDebit() + fire RechargeCompleted
    //   • On exhausted routes → schedule RetryRecharge with exponential backoff
    //     or call handleFailure() when max retries reached
    // ─────────────────────────────────────────────────────────────────────────

    public function process(RechargeTransaction $transaction): void
    {
        if ($transaction->isTerminal()) {
            return; // idempotent
        }

        $this->rechargeRepo->updateStatus($transaction->id, 'processing');

        $routes = $this->operatorRepo->getActiveRoutes(
            $transaction->operator_code,
            $transaction->recharge_type,
            (float) $transaction->amount
        );

        if ($routes->isEmpty()) {
            $this->scheduleRetryOrFail($transaction, 'No active operator route available.');
            return;
        }

        $attemptNumber = $transaction->attempts()->count() + 1;
        $succeeded     = false;

        foreach ($routes as $route) {
            $startTime = microtime(true);

            try {
                $apiRef = (string) Str::uuid();
                [$payload, $safePayload] = $this->buildPayload($transaction, $route->api_config ?? [], $apiRef);

                // Store our outgoing ref on the transaction so callbacks can match
                $this->rechargeRepo->updateStatus($transaction->id, 'processing', [
                    'api_ref' => $apiRef,
                ]);

                $response = Http::timeout($route->timeout_seconds)
                    ->withHeaders(['Accept' => 'application/json'])
                    ->post($route->api_endpoint, $payload);

                $duration = (int) ((microtime(true) - $startTime) * 1000);
                $isOk     = $response->successful() && $this->isOperatorSuccess($response->json());

                // Log every attempt — use $safePayload (api_key masked) not $payload
                RechargeAttempt::create([
                    'recharge_transaction_id' => $transaction->id,
                    'operator_route_id'       => $route->id,
                    'attempt_number'          => $attemptNumber,
                    'status'                  => $isOk ? 'success' : 'failed',
                    'request_url'             => $route->api_endpoint,
                    'request_payload'         => $safePayload,   // FIX C2: masked
                    'response_payload'        => $response->json(),
                    'response_code'           => $response->status(),
                    'duration_ms'             => $duration,
                ]);

                if ($isOk) {
                    $operatorRef = $response->json('txn_id')
                                ?? $response->json('ref_id')
                                ?? $response->json('operator_ref');

                    // Atomic: debit wallet + mark success in one transaction
                    DB::transaction(function () use ($transaction, $operatorRef, $response, $route) {
                        $this->rechargeRepo->updateStatus($transaction->id, 'success', [
                            'operator_ref'      => $operatorRef,
                            'operator_route_id' => $route->id,
                            'operator_response' => $response->json(),
                            'processed_at'      => now(),
                        ]);
                        $this->finalizeDebit($transaction);
                    });

                    $this->operatorRepo->incrementSuccessRate($route->id);
                    event(new RechargeCompleted($transaction->fresh()));
                    $succeeded = true;
                    break;
                }

                // Operator returned a failure response — try next route
                $this->operatorRepo->decrementSuccessRate($route->id);
                $attemptNumber++;

            } catch (\Throwable $e) {
                $duration = (int) ((microtime(true) - $startTime) * 1000);

                RechargeAttempt::create([
                    'recharge_transaction_id' => $transaction->id,
                    'operator_route_id'       => $route->id,
                    'attempt_number'          => $attemptNumber,
                    'status'                  => 'error',
                    'request_url'             => $route->api_endpoint,
                    'request_payload'         => $safePayload ?? [],  // FIX C2: masked
                    'duration_ms'             => $duration,
                    'error_message'           => $e->getMessage(),
                ]);

                Log::error('Recharge attempt exception', [
                    'transaction_id' => $transaction->id,
                    'route_id'       => $route->id,
                    'error'          => $e->getMessage(),
                ]);

                $this->operatorRepo->decrementSuccessRate($route->id);
                $attemptNumber++;
            }
        }

        if (! $succeeded) {
            $this->scheduleRetryOrFail($transaction, 'All operator routes exhausted.');
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Step 3 · handleCallback()  — called by webhook controller
    //
    // Operators may respond asynchronously. This method:
    //   • Locates the transaction by operator_ref
    //   • Skips if already in a terminal state (idempotent)
    //   • Finalises debit on success, or releases reserve on failure
    // ─────────────────────────────────────────────────────────────────────────

    public function handleCallback(string $operatorRef, string $status, array $payload): void
    {
        // ── FIX C1: Lock the transaction row BEFORE reading terminal status ────
        // Without this lock, two simultaneous callbacks both see isTerminal()=false
        // and both proceed to finalizeDebit() → double-debit.
        // lockForUpdate() serialises concurrent callbacks for the same operator_ref.
        //
        // Events are fired OUTSIDE the transaction to avoid holding a lock
        // while executing listener code (cache writes, log inserts, etc.).
        $eventToFire = null;

        DB::transaction(function () use ($operatorRef, $status, $payload, &$eventToFire) {
            $transaction = RechargeTransaction::where('operator_ref', $operatorRef)
                ->lockForUpdate()
                ->first();

            if (! $transaction) {
                Log::warning('Callback: unknown operator_ref', ['ref' => $operatorRef]);
                return;
            }

            if ($transaction->isTerminal()) {
                // Already finalised — idempotent, nothing to do
                return;
            }

            $normalised = strtolower($status);

            if (in_array($normalised, ['success', 'successful', '1', 'ok', 'true'])) {
                $this->rechargeRepo->updateStatus($transaction->id, 'success', [
                    'operator_response' => $payload,
                    'processed_at'      => now(),
                ]);
                $this->finalizeDebit($transaction);
                $eventToFire = new RechargeCompleted($transaction->fresh());

            } elseif (in_array($normalised, ['pending', 'processing', 'queued'])) {
                // Operator says still in progress — leave status as-is
                Log::info('Callback: operator reports pending', [
                    'transaction_id' => $transaction->id,
                    'ref'            => $operatorRef,
                ]);

            } else {
                // Inline failure logic to avoid nested DB::transaction() calls
                $this->rechargeRepo->updateStatus($transaction->id, 'failed', [
                    'failure_reason' => $payload['message'] ?? "Operator callback failure: {$status}",
                    'processed_at'   => now(),
                ]);
                $wallet = $this->walletRepo->findByUserIdLocked($transaction->user_id);
                $this->walletRepo->releaseReserve($wallet, (float) $transaction->amount);
                $eventToFire = new RechargeFailed($transaction->fresh());
            }
        });

        // Fire event after the transaction commits so listeners see the final state
        if ($eventToFire) {
            event($eventToFire);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Step 4 · refund()  — admin action
    // ─────────────────────────────────────────────────────────────────────────

    public function refund(RechargeTransaction $transaction, User $requestedBy): void
    {
        if (! in_array($transaction->status, ['failed', 'partial'])) {
            throw new \RuntimeException(
                "Cannot refund transaction #{$transaction->id} with status '{$transaction->status}'."
            );
        }

        DB::transaction(function () use ($transaction) {
            $wallet = $this->walletRepo->findByUserIdLocked($transaction->user_id);

            $this->walletRepo->credit($wallet, (float) $transaction->amount, [
                'description'    => "Refund for txn #{$transaction->id}",
                'reference_type' => RechargeTransaction::class,
                'reference_id'   => $transaction->id,
            ]);

            $this->rechargeRepo->updateStatus($transaction->id, 'refunded', [
                'processed_at' => now(),
            ]);
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Block the same mobile + operator + type + amount within the configured
     * dedup window (default 60 seconds).
     *
     * Must be called INSIDE a DB::transaction() so that the read and the
     * subsequent INSERT are serializable, preventing two concurrent requests
     * from both slipping through simultaneously.
     */
    private function assertNoDuplicateInWindow(int $userId, array $data): void
    {
        $windowSeconds = config('recharge.dedup_window', 60);
        $rechargeType  = $data['recharge_type'] ?? 'prepaid';

        $duplicate = RechargeTransaction::where('user_id', $userId)
            ->where('mobile', $data['mobile'])
            ->where('operator_code', $data['operator_code'])
            ->where('recharge_type', $rechargeType)
            ->where('amount', $data['amount'])
            ->whereNotIn('status', ['failed', 'refunded'])
            ->where('created_at', '>=', now()->subSeconds($windowSeconds))
            ->first();

        if ($duplicate) {
            throw new DuplicateTransactionException($duplicate);
        }
    }

    /**
     * Increment retry count, then either schedule a RetryRecharge job
     * (with exponential back-off) or mark the transaction as permanently failed.
     *
     * Back-off schedule (retries = 1 → 2 → 3):
     *   retry 1 after  2 min,  retry 2 after  4 min,  retry 3 after  8 min.
     */
    private function scheduleRetryOrFail(RechargeTransaction $transaction, string $reason): void
    {
        $this->rechargeRepo->incrementRetryCount($transaction->id);
        $transaction->refresh();

        $maxRetries = config('recharge.max_retries', 3);

        if ($transaction->retry_count <= $maxRetries) {
            // Exponential backoff: 2^retry_count minutes
            $delayMinutes = 2 ** $transaction->retry_count;
            $nextRetryAt  = now()->addMinutes($delayMinutes);

            $this->rechargeRepo->updateStatus($transaction->id, 'queued', [
                'next_retry_at' => $nextRetryAt,
            ]);

            RetryRecharge::dispatch($transaction->id)
                ->onQueue(config('recharge.queue', 'recharges'))
                ->delay($nextRetryAt);

            Log::info('Recharge retry scheduled', [
                'transaction_id' => $transaction->id,
                'retry_count'    => $transaction->retry_count,
                'next_retry_at'  => $nextRetryAt->toIso8601String(),
            ]);
        } else {
            $this->handleFailure(
                $transaction,
                $reason . " Max retries ({$maxRetries}) reached."
            );
        }
    }

    /**
     * Terminal failure: release reserved balance, fire RechargeFailed event.
     * Public so ProcessRecharge::failed() can call it as a safety net when the
     * job infrastructure crashes before normal service logic runs.
     */
    public function handleFailure(RechargeTransaction $transaction, string $reason): void
    {
        DB::transaction(function () use ($transaction, $reason) {
            $this->rechargeRepo->updateStatus($transaction->id, 'failed', [
                'failure_reason' => $reason,
                'processed_at'   => now(),
            ]);

            // Release reserved balance back to available
            $wallet = $this->walletRepo->findByUserIdLocked($transaction->user_id);
            $this->walletRepo->releaseReserve($wallet, (float) $transaction->amount);
        });

        event(new RechargeFailed($transaction->fresh()));
    }

    /**
     * Move amount from reserved_balance to deducted_balance (actual debit).
     * Must be called within a DB::transaction().
     */
    private function finalizeDebit(RechargeTransaction $transaction): void
    {
        $wallet = $this->walletRepo->findByUserIdLocked($transaction->user_id);

        $this->walletRepo->debit($wallet, (float) $transaction->amount, [
            'description'    => "Recharge #{$transaction->id} — {$transaction->mobile}",
            'reference_type' => RechargeTransaction::class,
            'reference_id'   => $transaction->id,
        ]);

        // Release the reservation that was created during initiation
        $this->walletRepo->releaseReserve($wallet, (float) $transaction->amount);
    }

    /**
     * Build the outgoing payload for the operator API.
     * Returns [payload_to_send, safe_payload_for_log] — the log copy masks the api_key.
     *
     * @return array{0: array, 1: array}
     */
    private function buildPayload(RechargeTransaction $t, array $config, string $apiRef): array
    {
        $payload = [
            'api_key'    => $config['api_key'] ?? '',
            'mobile'     => $t->mobile,
            'operator'   => $t->operator_code,
            'amount'     => $t->amount,
            'ref_id'     => $apiRef,       // our unique outgoing reference
            'txn_id'     => $t->id,        // internal ID for reconciliation
            'circle'     => $t->circle,
            'type'       => $t->recharge_type,
        ];

        // ── FIX C2: Never log operator credentials in plain text ──────────────
        // Store a sanitised copy in recharge_attempts; keep full payload only in
        // memory for the actual HTTP call.
        $safePayload          = $payload;
        $safePayload['api_key'] = $this->maskSecret($payload['api_key']);

        return [$payload, $safePayload];
    }

    /**
     * Return first 4 chars + asterisks, e.g.  "abcd************".
     * Shows enough for debugging (key prefix) without exposing the secret.
     */
    private function maskSecret(string $value): string
    {
        $len = strlen($value);
        if ($len <= 4) {
            return str_repeat('*', $len);
        }
        return substr($value, 0, 4) . str_repeat('*', min($len - 4, 12));
    }

    /**
     * Normalise varied operator API success indicators into a boolean.
     */
    private function isOperatorSuccess(array $response): bool
    {
        $status = strtolower(
            $response['status'] ?? $response['STATUS'] ?? $response['code'] ?? ''
        );
        return in_array($status, ['success', 'successful', '1', 'ok', 'true']);
    }
}
