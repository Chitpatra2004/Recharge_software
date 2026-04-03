<?php

namespace App\Repositories;

use App\Contracts\Repositories\RechargeRepositoryInterface;
use App\Models\RechargeTransaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class RechargeRepository implements RechargeRepositoryInterface
{
    /**
     * Columns returned for list queries — avoids transferring large payload
     * columns (e.g. operator_response JSON) that are not needed in listings.
     */
    private const LIST_COLUMNS = [
        'id', 'user_id', 'mobile', 'operator_code', 'recharge_type',
        'amount', 'commission', 'status', 'idempotency_key',
        'operator_ref', 'failure_reason', 'retry_count',
        'processed_at', 'created_at', 'updated_at',
    ];

    public function create(array $data): RechargeTransaction
    {
        return RechargeTransaction::create($data);
    }

    public function findById(int $id): ?RechargeTransaction
    {
        return RechargeTransaction::find($id);
    }

    public function findByIdempotencyKey(string $key): ?RechargeTransaction
    {
        return RechargeTransaction::where('idempotency_key', $key)->first();
    }

    public function findByOperatorRef(string $ref): ?RechargeTransaction
    {
        return RechargeTransaction::where('operator_ref', $ref)->first();
    }

    public function updateStatus(int $id, string $status, array $extra = []): bool
    {
        return (bool) RechargeTransaction::where('id', $id)
            ->update(array_merge(['status' => $status], $extra));
    }

    public function getForUser(int $userId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        // Default date window: last 90 days — allows MySQL RANGE partition pruning.
        // Callers can override via $filters['from'] / $filters['to'].
        $from = ! empty($filters['from'])
            ? Carbon::parse($filters['from'])->startOfDay()
            : now()->subDays(90)->startOfDay();

        $to = ! empty($filters['to'])
            ? Carbon::parse($filters['to'])->endOfDay()
            : now()->endOfDay();

        $query = RechargeTransaction::select(self::LIST_COLUMNS)
            ->where('user_id', $userId)
            // Range predicate on created_at uses the partition index directly;
            // whereDate() wraps in DATE() which prevents pruning.
            ->whereBetween('created_at', [$from, $to])
            ->latest();

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['mobile'])) {
            $query->where('mobile', $filters['mobile']);
        }
        if (! empty($filters['operator_code'])) {
            $query->where('operator_code', $filters['operator_code']);
        }

        return $query->paginate($perPage);
    }

    public function incrementRetryCount(int $id): void
    {
        RechargeTransaction::where('id', $id)->increment('retry_count');
    }
}
