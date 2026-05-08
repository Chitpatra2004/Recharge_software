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
use App\Models\SellerOperatorCommission;
use App\Models\SellerIntegrationRequest;
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

            $commission = $this->calculateSellerCommission($user, $data['operator_code'], (float) $data['amount']);
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
        $firstRoute     = true;

        foreach ($routes as $route) {
            $startTime = microtime(true);

            try {
                $apiRef = (string) Str::uuid();
                $cfg = $route->api_config ?? [];
                $cfg['_route_id'] = $route->id;
                [$payload, $safePayload] = $this->buildPayload($transaction, $cfg, $apiRef);

                $updates = ['api_ref' => $apiRef];
                if ($firstRoute) {
                    $updates['initial_route_id'] = $route->id;
                    $firstRoute = false;
                }
                $this->rechargeRepo->updateStatus($transaction->id, 'processing', $updates);

                // Guzzle HTTP call — enforces hard timeout
                $routeTimeout = min($route->timeout_seconds ?? $syncTimeout, $syncTimeout);
                $response = $this->callOperatorApi(
                    $route->api_endpoint, $payload, $cfg,
                    $routeTimeout, $connectTimeout
                );

                $duration = (int) ((microtime(true) - $startTime) * 1000);
                $isOk     = $response->successful() && $this->isOperatorSuccess($response->json(), $cfg);
                $reqUrl   = $this->formatRequestUrl($route->api_endpoint, $payload, $cfg);

                RechargeAttempt::create([
                    'log_type'                => 'recharge',
                    'log_label'               => 'Recharge via ' . ($route->name ?? $route->api_provider ?? 'API'),
                    'recharge_transaction_id' => $transaction->id,
                    'operator_route_id'       => $route->id,
                    'attempt_number'          => $attemptNumber,
                    'status'                  => $isOk ? 'success' : 'failed',
                    'request_url'             => $reqUrl,
                    'request_payload'         => $safePayload,
                    'response_payload'        => $response->json(),
                    'response_code'           => $response->status(),
                    'duration_ms'             => $duration,
                ]);

                if ($isOk) {
                    // ── Step 5a: Confirmed success ────────────────────────────
                    $txnidKey    = $cfg['txnid_key'] ?? null;
                    $operatorRef = $txnidKey
                        ? (data_get($response->json() ?? [], $txnidKey) ?? $apiRef)
                        : (data_get($response->json() ?? [], 'txn_id') ?? data_get($response->json() ?? [], 'ref_id') ?? data_get($response->json() ?? [], 'operator_ref') ?? data_get($response->json() ?? [], 'data.operatorRefNo') ?? data_get($response->json() ?? [], 'data.mobikwikStamp') ?? $apiRef);

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

                // If status API exists: check status on the SAME route first.
                // Reroute is allowed only when status-check confirms failure AND transaction age <= 30 minutes.
                $statusDecision = $this->statusCheckDecision($transaction, $route, $attemptNumber + 1);
                if ($statusDecision === 'success') {
                    $succeeded = true;
                    break;
                }
                if ($statusDecision === 'hold') {
                    $this->rechargeRepo->updateStatus($transaction->id, 'pending', [
                        'failure_reason' => 'Awaiting status from current API.',
                        'next_retry_at'  => now()->addMinutes(5),
                    ]);
                    $succeeded = true; // stop trying other routes
                    break;
                }
                if ($statusDecision === 'no_reroute_fail') {
                    $this->handleFailure($transaction, 'Status check failed after 30 minutes. No reroute allowed.');
                    $succeeded = true; // terminal now
                    break;
                }

                // Operator returned failure — try next route (reroute)
                $this->operatorRepo->decrementSuccessRate($route->id);
                $attemptNumber++;

            } catch (ConnectionException $e) {
                // ── Guzzle connection / read timeout ──────────────────────────
                $duration = (int) ((microtime(true) - $startTime) * 1000);

                RechargeAttempt::create([
                    'log_type'                => 'recharge',
                    'log_label'               => 'Recharge via ' . ($route->name ?? $route->api_provider ?? 'API'),
                    'recharge_transaction_id' => $transaction->id,
                    'operator_route_id'       => $route->id,
                    'attempt_number'          => $attemptNumber,
                    'status'                  => 'error',
                    'request_url'             => $this->formatRequestUrl($route->api_endpoint, $payload ?? [], $cfg ?? []),
                    'request_payload'         => $safePayload ?? [],
                    'duration_ms'             => $duration,
                    'error_message'           => 'Timeout: ' . $e->getMessage(),
                ]);

                Log::warning('Recharge sync: operator API timeout', [
                    'transaction_id' => $transaction->id,
                    'route_id'       => $route->id,
                    'duration_ms'    => $duration,
                ]);

                // Timeout is not a confirmed failure. First check status on the SAME API.
                // If status is pending/unknown → hold (no reroute). Only reroute if status confirms fail.
                $statusDecision = $this->statusCheckDecision($transaction, $route, $attemptNumber + 1);
                if ($statusDecision === 'success') {
                    $succeeded = true;
                    break;
                }
                if ($statusDecision === 'hold') {
                    $this->rechargeRepo->updateStatus($transaction->id, 'pending', [
                        'failure_reason' => 'Awaiting status from current API.',
                        'next_retry_at'  => now()->addMinutes(5),
                    ]);
                    $succeeded = true;
                    break;
                }
                if ($statusDecision === 'no_reroute_fail') {
                    $this->handleFailure($transaction, 'Status check failed after 30 minutes. No reroute allowed.');
                    $succeeded = true;
                    break;
                }

                // Status confirms failure (within reroute window) — allow fallback
                $timedOut = true;
                $attemptNumber++;

            } catch (\Throwable $e) {
                $duration = (int) ((microtime(true) - $startTime) * 1000);

                RechargeAttempt::create([
                    'log_type'                => 'recharge',
                    'log_label'               => 'Recharge via ' . ($route->name ?? $route->api_provider ?? 'API'),
                    'recharge_transaction_id' => $transaction->id,
                    'operator_route_id'       => $route->id,
                    'attempt_number'          => $attemptNumber,
                    'status'                  => 'error',
                    'request_url'             => $this->formatRequestUrl($route->api_endpoint, $payload ?? [], $cfg ?? []),
                    'request_payload'         => $safePayload ?? [],
                    'duration_ms'             => $duration,
                    'error_message'           => $e->getMessage(),
                ]);

                Log::error('Recharge sync: unexpected exception', [
                    'transaction_id' => $transaction->id,
                    'route_id'       => $route->id,
                    'error'          => $e->getMessage(),
                ]);

                // Unexpected error may still be "unknown" at operator side → status check first.
                $statusDecision = $this->statusCheckDecision($transaction, $route, $attemptNumber + 1);
                if ($statusDecision === 'success') {
                    $succeeded = true;
                    break;
                }
                if ($statusDecision === 'hold') {
                    $this->rechargeRepo->updateStatus($transaction->id, 'pending', [
                        'failure_reason' => 'Awaiting status from current API.',
                        'next_retry_at'  => now()->addMinutes(5),
                    ]);
                    $succeeded = true;
                    break;
                }
                if ($statusDecision === 'no_reroute_fail') {
                    $this->handleFailure($transaction, 'Status check failed after 30 minutes. No reroute allowed.');
                    $succeeded = true;
                    break;
                }

                $statusDecision = $this->statusCheckDecision($transaction, $route, $attemptNumber + 1);
                if ($statusDecision === 'success') {
                    $succeeded = true;
                    break;
                }
                if ($statusDecision === 'hold') {
                    $this->rechargeRepo->updateStatus($transaction->id, 'pending', [
                        'failure_reason' => 'Awaiting status from current API.',
                        'next_retry_at'  => now()->addMinutes(5),
                    ]);
                    $succeeded = true;
                    break;
                }
                if ($statusDecision === 'no_reroute_fail') {
                    $this->handleFailure($transaction, 'Status check failed after 30 minutes. No reroute allowed.');
                    $succeeded = true;
                    break;
                }

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

            $commission = $this->calculateSellerCommission($user, $data['operator_code'], (float) $data['amount']);
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
                $cfg2 = $route->api_config ?? [];
                $cfg2['_route_id'] = $route->id;
                [$payload, $safePayload] = $this->buildPayload($transaction, $cfg2, $apiRef);

                $this->rechargeRepo->updateStatus($transaction->id, 'processing', [
                    'api_ref' => $apiRef,
                ]);

                $response = $this->callOperatorApi(
                    $route->api_endpoint, $payload, $cfg2,
                    $route->timeout_seconds ?? 10, 5
                );

                $duration = (int) ((microtime(true) - $startTime) * 1000);
                $isOk     = $response->successful() && $this->isOperatorSuccess($response->json(), $cfg2);
                $reqUrl   = $this->formatRequestUrl($route->api_endpoint, $payload, $cfg2);

                RechargeAttempt::create([
                    'log_type'                => 'recharge',
                    'log_label'               => 'Recharge via ' . ($route->name ?? $route->api_provider ?? 'API'),
                    'recharge_transaction_id' => $transaction->id,
                    'operator_route_id'       => $route->id,
                    'attempt_number'          => $attemptNumber,
                    'status'                  => $isOk ? 'success' : 'failed',
                    'request_url'             => $reqUrl,
                    'request_payload'         => $safePayload,
                    'response_payload'        => $response->json(),
                    'response_code'           => $response->status(),
                    'duration_ms'             => $duration,
                ]);

                if ($isOk) {
                    $txnidKey2   = $cfg2['txnid_key'] ?? null;
                    $operatorRef = $txnidKey2
                        ? (data_get($response->json() ?? [], $txnidKey2) ?? $apiRef)
                        : (data_get($response->json() ?? [], 'txn_id') ?? data_get($response->json() ?? [], 'ref_id') ?? data_get($response->json() ?? [], 'operator_ref') ?? data_get($response->json() ?? [], 'data.operatorRefNo') ?? data_get($response->json() ?? [], 'data.mobikwikStamp') ?? $apiRef);

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

                $statusDecision = $this->statusCheckDecision($transaction, $route, $attemptNumber + 1);
                if ($statusDecision === 'success') {
                    $succeeded = true;
                    break;
                }
                if ($statusDecision === 'hold') {
                    $this->rechargeRepo->updateStatus($transaction->id, 'pending', [
                        'failure_reason' => 'Awaiting status from current API.',
                        'next_retry_at'  => now()->addMinutes(5),
                    ]);
                    $succeeded = true;
                    break;
                }
                if ($statusDecision === 'no_reroute_fail') {
                    $this->handleFailure($transaction, 'Status check failed after 30 minutes. No reroute allowed.');
                    $succeeded = true;
                    break;
                }

                $this->operatorRepo->decrementSuccessRate($route->id);
                $attemptNumber++;

            } catch (\Throwable $e) {
                $duration = (int) ((microtime(true) - $startTime) * 1000);

                RechargeAttempt::create([
                    'log_type'                => 'recharge',
                    'log_label'               => 'Recharge via ' . ($route->name ?? $route->api_provider ?? 'API'),
                    'recharge_transaction_id' => $transaction->id,
                    'operator_route_id'       => $route->id,
                    'attempt_number'          => $attemptNumber,
                    'status'                  => 'error',
                    'request_url'             => $this->formatRequestUrl($route->api_endpoint, $payload ?? [], $cfg2 ?? []),
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
        $callbackTransaction = null;

        DB::transaction(function () use ($operatorRef, $status, $payload, &$eventToFire, &$callbackTransaction) {
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

            // Log callback request payload for admin reports
            $cbAttemptNo = (int) ($transaction->attempts()->count() + 1);
            RechargeAttempt::create([
                'log_type'                => 'callback',
                'log_label'               => 'Operator callback',
                'recharge_transaction_id' => $transaction->id,
                'operator_route_id'       => $transaction->operator_route_id ?? $transaction->initial_route_id,
                'attempt_number'          => min($cbAttemptNo, 255),
                'status'                  => 'success',
                'request_url'             => 'callback',
                'request_payload'         => $payload,
                'response_payload'        => ['callback_status' => $status],
                'response_code'           => null,
                'duration_ms'             => null,
                'error_message'           => null,
            ]);

            $normalised = strtolower($status);
            $callbackTransaction = $transaction;

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

        if ($callbackTransaction) {
            $this->dispatchSellerCallback($callbackTransaction->fresh('user'), $payload, $status);
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

    private function calculateSellerCommission(User $user, string $operatorCode, float $amount): float
    {
        $setting = SellerOperatorCommission::where('user_id', $user->id)
            ->where('operator_code', $operatorCode)
            ->where('is_active', true)
            ->first();

        if ($setting) {
            $commission = $setting->commission_type === 'flat'
                ? (float) $setting->commission
                : $amount * ((float) $setting->commission / 100);

            return round(min($commission, $amount), 2);
        }

        return round($amount * ((float) $user->commission_rate / 100), 2);
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
        $template = trim($config['request_params'] ?? '');

        if ($template !== '') {
            // Template-based (PDRS and similar providers): replace [placeholder] tokens
            $search  = ['[username]', '[apitoken]', '[password]', '[token]',
                        '[number]', '[mobile]', '[amount]', '[opcode]',
                        '[operator]', '[transid]', '[txnid]', '[order_id]',
                        '[circlecode]', '[circle]', '[type]', '[apiref]'];
            $replace = [
                $config['username']  ?? '',
                $config['api_token'] ?? '',
                $config['api_token'] ?? '',
                $config['api_token'] ?? '',
                $t->mobile, $t->mobile,
                $t->amount, $this->apiOperatorCode($t, $config), $t->operator_code,
                $t->id, $t->id, $t->id,
                $t->circle ?? '*', $t->circle ?? '',
                $t->recharge_type, $apiRef,
            ];

            $paramString = str_replace($search, $replace, $template);
            $paramString = str_replace([
                '[operator_code]', '[member_id]', '[remitter_name]', '[payment_ref_id]',
                '[payment_mode]', '[payment_account_info]',
            ], [
                $this->apiOperatorCode($t, $config),
                $config['member_id'] ?? $config['username'] ?? '',
                $config['remitter_name'] ?? 'Customer',
                $apiRef,
                $config['payment_mode'] ?? 'Cash',
                $config['payment_account_info'] ?? '',
            ], $paramString);
            parse_str($paramString, $params);

            $safeParams = $params;
            foreach (['token', 'apitoken', 'api_token', 'password', 'key', 'secret'] as $k) {
                if (isset($safeParams[$k])) {
                    $safeParams[$k] = $this->maskSecret((string) $safeParams[$k]);
                }
            }

            return [$params, $safeParams];
        }

        // Legacy fixed-key format
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

        $safePayload            = $payload;
        $safePayload['api_key'] = $this->maskSecret($payload['api_key']);

        return [$payload, $safePayload];
    }

    private function callOperatorApi(
        string $endpoint, array $params, array $config,
        int $timeout, int $connectTimeout
    ): \Illuminate\Http\Client\Response {
        $method = strtolower($config['method'] ?? 'post');
        $http   = Http::timeout($timeout)->connectTimeout($connectTimeout);

        if (($config['driver'] ?? null) === 'mobikwik_v3') {
            return app(\App\Services\MobikwikRechargeApiService::class)
                ->payment($this->routeFromConfig($config), $params, $timeout, $connectTimeout);
        }

        if ($method === 'get') {
            return $http->get($endpoint, $params);
        }

        return $http->withHeaders(['Accept' => 'application/json'])->post($endpoint, $params);
    }

    private function maskSecret(string $value): string
    {
        $len = strlen($value);
        if ($len <= 4) {
            return str_repeat('*', $len);
        }
        return substr($value, 0, 4) . str_repeat('*', min($len - 4, 12));
    }

    private function isOperatorSuccess(array $response, array $config = []): bool
    {
        $statusKey  = $config['status_key']  ?? 'status';
        $successVal = $config['success_val'] ?? null;

        $status = (string) (data_get($response, $statusKey) ?? $response['STATUS'] ?? $response['code'] ?? '');

        if ($successVal) {
            $successValues = preg_split('/[,|]/', (string) $successVal) ?: [];
            return in_array(strtolower($status), array_map(fn ($v) => strtolower(trim($v)), $successValues), true);
        }

        return in_array(strtolower($status), ['success', 'successful', '1', 'ok', 'true']);
    }

    private function formatRequestUrl(string $endpoint, array $params, array $config): string
    {
        $method = strtoupper((string) ($config['method'] ?? 'POST'));
        if ($method !== 'GET' || $params === []) {
            return $endpoint;
        }

        $qs = http_build_query($params);
        if ($qs === '') {
            return $endpoint;
        }

        return str_contains($endpoint, '?') ? ($endpoint . '&' . $qs) : ($endpoint . '?' . $qs);
    }

    private function apiOperatorCode(RechargeTransaction $transaction, array $config): string
    {
        $codes = $config['op_codes'] ?? [];
        return (string) ($codes[$transaction->operator_code] ?? $transaction->operator_code);
    }

    private function routeFromConfig(array $config): \App\Models\OperatorRoute
    {
        return \App\Models\OperatorRoute::query()->findOrFail((int) ($config['_route_id'] ?? 0));
    }

    /**
     * Decide what to do after a recharge call returned non-success.
     *
     * Returns one of:
     *  - 'reroute'         : confirmed failure and still within reroute window
     *  - 'hold'            : status is pending/unknown → do not reroute yet
     *  - 'success'         : status check says success → finalize & stop
     *  - 'no_reroute_fail' : failure confirmed but transaction is older than 30 minutes
     *
     * If status API is not configured for the route, returns 'reroute' to preserve legacy behavior.
     */
    private function statusCheckDecision(RechargeTransaction $transaction, $route, int $attemptNumber): string
    {
        $cfg = $route->api_config ?? [];
        $sa  = $cfg['status_api'] ?? [];
        if (empty($sa['url'])) {
            return 'reroute';
        }

        $svc = app(\App\Services\GenericApiService::class);
        $res = $svc->checkStatus($route, (string) $transaction->id);

        RechargeAttempt::create([
            'log_type'                => 'status_check',
            'log_label'               => 'Status check via ' . ($route->name ?? $route->api_provider ?? 'API'),
            'recharge_transaction_id' => $transaction->id,
            'operator_route_id'       => $route->id,
            'attempt_number'          => min((int) $attemptNumber, 255),
            'status'                  => $res['success'] ? 'success' : 'error',
            'request_url'             => $res['request_url'] ?? ($sa['url'] ?? null),
            'request_payload'         => $res['request_params'] ?? ['order_id' => (string) $transaction->id],
            'response_payload'        => $res['raw'] ?? $res,
            'response_code'           => null,
            'duration_ms'             => null,
            'error_message'           => $res['success'] ? null : ($res['error'] ?? 'Status check failed'),
        ]);

        $status = strtolower((string) ($res['status'] ?? ''));
        $successValues = array_map('strtolower', array_map('trim', preg_split('/[,|]/', (string) ($cfg['success_val'] ?? 'success,successful,1,ok,true,RECHARGESUCCESS')) ?: []));
        $pendingValues = array_map('strtolower', array_map('trim', preg_split('/[,|]/', (string) ($cfg['pending_val'] ?? 'pending,processing,queued,inprocess,in_process,RECHARGESUCCESSPENDING,SUCCESSPENDING')) ?: []));
        $isSuccess = in_array($status, $successValues, true);
        $isPending = in_array($status, $pendingValues, true) || $status === '';

        if ($isSuccess) {
            $operatorRef = (string) ($res['txnid'] ?? ($res['operator_id'] ?? $transaction->operator_ref ?? $transaction->api_ref ?? $transaction->id));
            DB::transaction(function () use ($transaction, $operatorRef, $res, $route) {
                $this->rechargeRepo->updateStatus($transaction->id, 'success', [
                    'operator_ref'      => $operatorRef,
                    'operator_route_id' => $route->id,
                    'operator_response' => $res['raw'] ?? $res,
                    'processed_at'      => now(),
                ]);
                $this->finalizeDebit($transaction);
            });
            event(new RechargeCompleted($transaction->fresh()));
            return 'success';
        }

        if ($isPending) {
            return 'hold';
        }

        $ageMin = now()->diffInMinutes($transaction->created_at);
        if ($ageMin > 30) {
            return 'no_reroute_fail';
        }

        return 'reroute';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // handlePdrsCallback() — PDRS GET callback
    // PDRS posts: ?uniqueid={our_order_id}&status={}&operator_id={}&transaction_id={pdrs_tid}
    // ─────────────────────────────────────────────────────────────────────────
    public function handlePdrsCallback(int $transactionId, string $status, string $pdrsRef, array $payload): void
    {
        $eventToFire = null;
        $callbackTransaction = null;

        DB::transaction(function () use ($transactionId, $status, $pdrsRef, $payload, &$eventToFire, &$callbackTransaction) {
            $transaction = RechargeTransaction::where('id', $transactionId)
                ->lockForUpdate()
                ->first();

            if (! $transaction || $transaction->isTerminal()) {
                return;
            }

            $cbAttemptNo = (int) ($transaction->attempts()->count() + 1);
            RechargeAttempt::create([
                'log_type'                => 'callback',
                'log_label'               => 'PDRS callback',
                'recharge_transaction_id' => $transaction->id,
                'operator_route_id'       => $transaction->operator_route_id ?? $transaction->initial_route_id,
                'attempt_number'          => min($cbAttemptNo, 255),
                'status'                  => 'success',
                'request_url'             => 'callback',
                'request_payload'         => $payload,
                'response_payload'        => ['callback_status' => $status, 'pdrs_ref' => $pdrsRef],
                'response_code'           => null,
                'duration_ms'             => null,
                'error_message'           => null,
            ]);

            $normalised = strtolower($status);
            $callbackTransaction = $transaction;

            if (in_array($normalised, ['success', 'successful'])) {
                $this->rechargeRepo->updateStatus($transaction->id, 'success', [
                    'operator_ref'      => $pdrsRef ?: ($payload['operator_id'] ?? null),
                    'operator_response' => $payload,
                    'processed_at'      => now(),
                ]);
                $this->finalizeDebit($transaction);
                $eventToFire = new RechargeCompleted($transaction->fresh());

            } elseif (in_array($normalised, ['pending', 'processing'])) {
                Log::info('PDRS callback: operator reports pending', ['transaction_id' => $transactionId]);

            } else {
                $this->rechargeRepo->updateStatus($transaction->id, 'failed', [
                    'failure_reason' => "PDRS callback status: {$status}",
                    'processed_at'   => now(),
                ]);
                $wallet = $this->walletRepo->findByUserIdLocked($transaction->user_id);
                if ($wallet) {
                    $this->walletRepo->releaseReserve($wallet, (float) $transaction->amount);
                }
                $eventToFire = new RechargeFailed($transaction->fresh());
            }
        });

        if ($eventToFire) {
            event($eventToFire);
        }

        if ($callbackTransaction) {
            $this->dispatchSellerCallback($callbackTransaction->fresh('user'), $payload, $status);
        }
    }

    private function dispatchSellerCallback(RechargeTransaction $transaction, array $payload, string $operatorStatus): void
    {
        $integration = SellerIntegrationRequest::where('user_id', $transaction->user_id)
            ->where('status', 'approved')
            ->latest()
            ->first();

        if (! $integration || empty($integration->callback_url)) {
            return;
        }

        $callbackConfig = $integration->callback_config ?? [];
        $requestIp      = (string) ($payload['_callback_request_ip'] ?? '');

        if (! $this->isCallbackIpAllowed($requestIp, $callbackConfig['ip_validation'] ?? null)) {
            Log::warning('Seller callback blocked by IP validation.', [
                'transaction_id' => $transaction->id,
                'seller_id'      => $transaction->user_id,
                'request_ip'     => $requestIp,
                'allowed_ips'    => $callbackConfig['ip_validation'] ?? null,
            ]);
            return;
        }

        $query = [
            $callbackConfig['status_field']    ?? 'status'         => $this->normalizeSellerCallbackStatus($transaction->status, $callbackConfig),
            $callbackConfig['api_txnid_field'] ?? 'api_txnid'      => $transaction->id,
            $callbackConfig['live_id_field']   ?? 'live_id'        => $transaction->operator_ref ?: ($payload['transaction_id'] ?? $payload['operator_id'] ?? ''),
            'mobile'                                             => $transaction->mobile,
            'amount'                                             => (string) $transaction->amount,
            'operator'                                           => $transaction->operator_code,
            'operator_status'                                    => $operatorStatus,
        ];

        try {
            Http::timeout(15)->get($integration->callback_url, $query);
        } catch (\Throwable $e) {
            Log::error('Seller callback dispatch failed.', [
                'transaction_id' => $transaction->id,
                'seller_id'      => $transaction->user_id,
                'callback_url'   => $integration->callback_url,
                'error'          => $e->getMessage(),
            ]);
        }
    }

    private function normalizeSellerCallbackStatus(string $transactionStatus, array $callbackConfig): string
    {
        return match (strtolower($transactionStatus)) {
            'success' => $callbackConfig['success_param'] ?? 'success',
            'pending', 'processing' => $callbackConfig['pending_param'] ?? 'pending',
            default => $callbackConfig['failure_param'] ?? 'failed',
        };
    }

    private function isCallbackIpAllowed(string $requestIp, ?string $allowedIps): bool
    {
        if ($allowedIps === null || trim($allowedIps) === '') {
            return true;
        }

        $entries = preg_split('/[\r\n,]+/', $allowedIps) ?: [];
        $entries = array_values(array_filter(array_map('trim', $entries)));

        if ($entries === [] || $requestIp === '') {
            return false;
        }

        foreach ($entries as $entry) {
            if (str_contains($entry, '/')) {
                if ($this->ipInCidr($requestIp, $entry)) {
                    return true;
                }
                continue;
            }

            if ($entry === $requestIp) {
                return true;
            }
        }

        return false;
    }

    private function ipInCidr(string $ip, string $cidr): bool
    {
        [$subnet, $bits] = explode('/', $cidr);

        $ipLong     = ip2long($ip);
        $subnetLong = ip2long($subnet);

        if ($ipLong === false || $subnetLong === false) {
            return false;
        }

        $mask = (int) $bits > 0 ? (~0 << (32 - (int) $bits)) : 0;

        return ($ipLong & $mask) === ($subnetLong & $mask);
    }
}
