<?php

namespace App\Contracts\Repositories;

use App\Models\RechargeTransaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RechargeRepositoryInterface
{
    public function create(array $data): RechargeTransaction;

    public function findById(int $id): ?RechargeTransaction;

    public function findByIdempotencyKey(string $key): ?RechargeTransaction;

    public function findByOperatorRef(string $ref): ?RechargeTransaction;

    public function updateStatus(int $id, string $status, array $extra = []): bool;

    public function getForUser(int $userId, array $filters = [], int $perPage = 20): LengthAwarePaginator;

    public function incrementRetryCount(int $id): void;
}
