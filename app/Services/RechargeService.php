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
use App\Jobs\RetryRecharge;
use App\Models\RechargeAttempt;
use App\Models\RechargeTransaction;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
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
    // PRIMARY ENTRY POINT — Real-time synchronous processing
    //
    // Flow (no queue workers needed):
    //   1. Idempotency key dedup
    //   2. Validate operator routes exist
    //   3. DB::transaction: 60-sec window dedup → lock wallet → reserve → persist
    //      (transaction committed before HTTP so DB connection is not held during API call)
    //   4. Call operator API synchronously with Guzzle (timeout: 10 s)
    //   5a. Success  → DB::transaction: finalize debit + mark 'success'
    //   5b. Failure  → DB::transaction: release reserve + mark 'failed'
    //   5c. Timeout  → leave reserve, mark 'pending' (cron RetryRecharge will retry)
    //   6. Return transaction with final status
    // ─────────────────────────────────────────────────────────────────────────

    public function processSync(User $user, array $data): RechargeTransaction
    {
        // ── Step 1: Fast idempotency check (no lock needed yet) ───────────────
        $existing = $this->rechargeRepo->findByIdempotencyKey($data['idempotency_key']);
        if ($existing) {
            throw new DuplicateTransactionException($existing);
        }

        // ── Step 2: Validate routes BEFORE touching wallet ────────────────────
        $routes = $this->operatorRepo->getActiveRoutes(
            $data['operator_code'],
            $data['recharge_type'] ?? 'prepaid',
            (float) $data['amount']
        );
        if ($routes->isEmpty()) {
            throw new OperatorUnavailableException($data['operator_code']);
        }

        // ── Step 3: Reserve wallet balance + persist transaction ──────────────
        // This transaction commits immediately so the DB connection is free
        // during the (potentially slow) HTTP call to the operator.
        $transaction = DB::transaction(function () use ($user, $data) {

            // Serializable dedup within 60-second window
            $this->assertNoDuplicateInWindow($user->id, $data);

            $wallet = $this->walletRepo->findByUserIdLocked($user->id);

            if (! $wallet || ! $wallet->isActive()) {
                throw new WalletFrozenException();
            }

            if (! $wallet->hasSufficientBalance((float) $data['amount'])) {
                throw new InsufficientBalanceException(
                    (float) $data['amount'],
                    $wallet->availableBalance()
                );
            }

            $commission = round((float) $data['amount'] * ($user->commission_rate / 100), 2);
            $netAmount  = round((float) $data['amount'] - $commission, 2);

            // Reserve (not debit) — actual debit only on confirmed success
            $this->walletRepo->reserve($wallet, (float) $data['amount']);

            return $this->rechargeRepo->create([
                'user_id'         => $user->id,
                'buyer_id'        => $data['buyer_id']      ?? null,
                'idempotency_key' => $data['idempotency_key'],
                'mobile'          => $data['mobile'],
                'operator_code'   => $data['operator_code'],
                'circle'          => $data['circle']        ?? null,
                'recharge_type'   => $data['recharge_type'] ?? 'prepaid',
                'amount'          => $data['amount'],
                'commission'      => $commission,
                'net_amount'      => $netAmount,
                'status'          => 'pending',
                'ip_address'      => $data['ip_address']    ?? null,
            ]);
        });

        event(new RechargeInitiated($transaction));

        // ── Step 4: Call operator API synchronously ───────────────────────────
        $this->rechargeRepo->updateStatus($transaction->id, 'processing');

        $syncTimeout    = (int) config('recharge.sync_timeout', 10);
        $connectTimeout = (int) config('recharge.connect_timeout', 5);
        $attemptNumber  = 1;
        $succeeded      = false;
        $timedOut       = false;

        foreach ($routes as $route) {
            $startTime = microtime(true);

            try {
                $apiRef = (string) Str::uuid();
                [$payload, $safePayload] = $this->buildPayload($transaction, $route->api_config ?? [], $apiRef);

                $this->rechargeRepo->updateStatus($transaction->id, 'processing', [
                    'api_ref' => $apiRef,
                ]);

                // Guzzle HTTP call — enforces hard timeout
                $routeTimeout = min($route->timeout_seconds ?? $syncTimeout, $syncTimeout);

                $response = Http::timeout($routeTimeout)
                    ->connectTimeout($connectTimeout)
                    ->withHeaders(['Accept' => 'application/json'])
                    ->post($route->api_endpoint, $payload);

                $duration = (int) ((microtime(true) - $startTime) * 1000);
                $isOk     = $response->successful() && $this->isOperatorSuccess($response->json());

                RechargeAttempt::create([
                    'recharge_transaction_id' => $transaction->id,
                    'operator_route_id'       => $route->id,
                    'attempt_number'          => $attemptNumber,
                    'status'                  => $isOk ? 'success' : 'failed',
                    'request_url'             => $route->api_endpoint,
                    'request_payload'         => $safePayload,
                    'response_payload'        => $response->json(),
                    'response_code'           => $response->status(),
                    'duration_ms'             => $duration,
                ]);

                if ($isOk) {
                    // ── Step 5a: Confirmed success ────────────────────────────
                    $operatorRef = $response->json('txn_id')
                                ?? $response->json('ref_id')
                                ?? $response->json('operator_ref');

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

                // Operator returned failure — try next route
                $this->operatorRepo->decrementSuccessRate($route->id);
                $attemptNumber++;

            } catch (ConnectionException $e) {
                // ── Guzzle connection / read timeout ──────────────────────────
                $duration = (int) ((microtime(true) - $startTime) * 1000);

                RechargeAttempt::create([
                    'recharge_transaction_id' => $transaction->id,
                    'operator_route_id'       => $route->id,
                    'attempt_number'          => $attemptNumber,
                    'status'                  => 'error',
                    'request_url'             => $route->api_endpoint,
                    'request_payload'         => $safePayload ?? [],
                    'duration_ms'             => $duration,
                    'error_message'           => 'Timeout: ' . $e->getMessage(),
                ]);

                Log::warning('Recharge sync: operator API timeout', [
                    'transaction_id' => $transaction->id,
                    'route_id'       => $route->id,
                    'duration_ms'    => $duration,
                ]);

                // Don't fail permanently on timeout — try next route
                $timedOut = true;
                $attemptNumber++;

            } catch (\Throwable $e) {
                $duration = (int) ((microtime(true) - $startTime) * 1000);

                RechargeAttempt::create([
                    'recharge_transaction_id' => $transaction->id,
                    'operator_route_id'       => $route->id,
                    'attempt_number'          => $attemptNumber,
                    'status'                  => 'error',
                    'request_url'             => $route->api_endpoint,
                    'request_payload'         => $safePayload ?? [],
                    'duration_ms'             => $duration,
                    'error_message'           => $e->getMessage(),
                ]);

                Log::error('Recharge sync: unexpected exception', [
                    'transaction_id' => $transaction->id,
                    'route_id'       => $route->id,
                    'error'          => $e->getMessage(),
                ]);

                $this->operatorRepo->decrementSuccessRate($route->id);
                $attemptNumber++;
            }
        }

        // ── Step 5b / 5c: All routes tried — decide outcome ───────────────────
        if (! $succeeded) {
            if ($timedOut) {
                // 5c: At least one route timed out, none succeeded
                // Keep reserved balance, mark pending — cron (RetryRecharge) will retry
                DB::transaction(function () use ($transaction) {
                    $this->rechargeRepo->updateStatus($transaction->id, 'pending', [
                        'failure_reason' => 'Operator API timed out. Pending automatic retry.',
                        'next_retry_at'  => now()->addMinutes(5),
                    ]);
                });

                Log::info('Recharge sync: marked pending (timeout)', ['transaction_id' => $transaction->id]);
            } else {
                // 5b: All routes explicitly failed — immediate refund
                $this->handleFailure($transaction, 'All operator routes exhausted.');
            }
        }

        return $transaction->fresh();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // LEGACY — kept for RetryRecharge cron job
    // ─────────────────────────────────────────────────────────────────────────

    public function initiate(User $user, array $data): RechargeTransaction
    {
        $existing = $this->rechargeRepo->findByIdempotencyKey($data['idempotency_key']);
        if ($existing) {
            throw new DuplicateTransactionException($existing);
        }

        $routes = $this->operatorRepo->getActiveRoutes(
            $data['operator_code'],
            $data['recharge_type'] ?? 'prepaid',
            (float) $data['amount']
        );
        if ($routes->isEmpty()) {
            throw new OperatorUnavailableException($data['operator_code']);
        }

        return DB::transaction(function () use ($user, $data) {
            $this->assertNoDuplicateInWindow($user->id, $data);

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

            $this->walletRepo->reserve($wallet, (float) $data['amount']);

            $transaction = $this->rechargeRepo->create([
                'user_id'         => $user->id,
                'buyer_id'        => $data['buyer_id']      ?? null,
                'idempotency_key' => $data['idempotency_key'],
                'mobile'          => $data['mobile'],
                'operator_code'   => $data['operator_code'],
                'circle'          => $data['circle']        ?? null,
                'recharge_type'   => $data['recharge_type'] ?? 'prepaid',
                'amount'          => $data['amount'],
                'commission'      => $commission,
                'net_amount'      => $netAmount,
                'status'          => 'queued',
                'ip_address'      => $data['ip_address']    ?? null,
            ]);

            // NOTE: No dispatch here. RetryRecharge job is dispatched by
            // scheduleRetryOrFail() for pending transactions that timed out.

            event(new RechargeInitiated($transaction));
            return $transaction;
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // process() — used by RetryRecharge cron job to retry pending transactions
    // ─────────────────────────────────────────────────────────────────────────

    public function process(RechargeTransaction $transaction): void
    {
        if ($transaction->isTerminal()) {
            return;
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

                $this->rechargeRepo->updateStatus($transaction->id, 'processing', [
                    'api_ref' => $apiRef,
                ]);

                $response = Http::timeout($route->timeout_seconds ?? 10)
                    ->connectTimeout(5)
                    ->withHeaders(['Accept' => 'application/json'])
                    ->post($route->api_endpoint, $payload);

                $duration = (int) ((microtime(true) - $startTime) * 1000);
                $isOk     = $response->successful() && $this->isOperatorSuccess($response->json());

                RechargeAttempt::create([
                    'recharge_transaction_id' => $transaction->id,
                    'operator_route_id'       => $route->id,
                    'attempt_number'          => $attemptNumber,
                    'status'                  => $isOk ? 'success' : 'failed',
                    'request_url'             => $route->api_endpoint,
                    'request_payload'         => $safePayload,
                    'response_payload'        => $response->json(),
                    'response_code'           => $response->status(),
                    'duration_ms'             => $duration,
                ]);

                if ($isOk) {
                    $operatorRef = $response->json('txn_id')
                                ?? $response->json('ref_id')
                                ?? $response->json('operator_ref');

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
                    'request_payload'         => $safePayload ?? [],
                    'duration_ms'             => $duration,
                    'error_message'           => $e->getMessage(),
                ]);

                Log::error('Recharge process (retry) exception', [
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
    // handleCallback() — operator async webhook
    // ─────────────────────────────────────────────────────────────────────────

    public function handleCallback(string $operatorRef, string $status, array $payload): void
    {
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
                Log::info('Callback: operator reports pending', [
                    'transaction_id' => $transaction->id,
                    'ref'            => $operatorRef,
                ]);

            } else {
                $this->rechargeRepo->updateStatus($transaction->id, 'failed', [
                    'failure_reason' => $payload['message'] ?? "Operator callback failure: {$status}",
                    'processed_at'   => now(),
                ]);
                $wallet = $this->walletRepo->findByUserIdLocked($transaction->user_id);
                $this->walletRepo->releaseReserve($wallet, (float) $transaction->amount);
                $eventToFire = new RechargeFailed($transaction->fresh());
            }
        });

        if ($eventToFire) {
            event($eventToFire);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // refund() — admin manual refund
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
    // handleFailure() — public so jobs can call it as a safety net
    // ─────────────────────────────────────────────────────────────────────────

    public function handleFailure(RechargeTransaction $transaction, string $reason): void
    {
        DB::transaction(function () use ($transaction, $reason) {
            $this->rechargeRepo->updateStatus($transaction->id, 'failed', [
                'failure_reason' => $reason,
                'processed_at'   => now(),
            ]);

            $wallet = $this->walletRepo->findByUserIdLocked($transaction->user_id);
            $this->walletRepo->releaseReserve($wallet, (float) $transaction->amount);
        });

        event(new RechargeFailed($transaction->fresh()));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

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

    private function scheduleRetryOrFail(RechargeTransaction $transaction, string $reason): void
    {
        $this->rechargeRepo->incrementRetryCount($transaction->id);
        $transaction->refresh();

        $maxRetries = config('recharge.max_retries', 3);

        if ($transaction->retry_count <= $maxRetries) {
            $delayMinutes = 2 ** $transaction->retry_count;
            $nextRetryAt  = now()->addMinutes($delayMinutes);

            $this->rechargeRepo->updateStatus($transaction->id, 'pending', [
                'next_retry_at' => $nextRetryAt,
            ]);

            // Dispatch retry job (runs via cron / manual worker — optional)
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

    private function finalizeDebit(RechargeTransaction $transaction): void
    {
        $wallet = $this->walletRepo->findByUserIdLocked($transaction->user_id);

        $this->walletRepo->debit($wallet, (float) $transaction->amount, [
            'description'    => "Recharge #{$transaction->id} — {$transaction->mobile}",
            'reference_type' => RechargeTransaction::class,
            'reference_id'   => $transaction->id,
        ]);

        $this->walletRepo->releaseReserve($wallet, (float) $transaction->amount);
    }

    private function buildPayload(RechargeTransaction $t, array $config, string $apiRef): array
    {
        $payload = [
            'api_key'  => $config['api_key'] ?? '',
            'mobile'   => $t->mobile,
            'operator' => $t->operator_code,
            'amount'   => $t->amount,
            'ref_id'   => $apiRef,
            'txn_id'   => $t->id,
            'circle'   => $t->circle,
            'type'     => $t->recharge_type,
        ];

        $safePayload              = $payload;
        $safePayload['api_key']   = $this->maskSecret($payload['api_key']);

        return [$payload, $safePayload];
    }

    private function maskSecret(string $value): string
    {
        $len = strlen($value);
        if ($len <= 4) {
            return str_repeat('*', $len);
        }
        return substr($value, 0, 4) . str_repeat('*', min($len - 4, 12));
    }

    private function isOperatorSuccess(array $response): bool
    {
        $status = strtolower(
            $response['status'] ?? $response['STATUS'] ?? $response['code'] ?? ''
        );
        return in_array($status, ['success', 'successful', '1', 'ok', 'true']);
    }
}
