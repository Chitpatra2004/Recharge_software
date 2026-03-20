<?php

namespace App\Contracts\Repositories;

use App\Models\OperatorRoute;
use Illuminate\Support\Collection;

interface OperatorRepositoryInterface
{
    public function getActiveRoutes(string $operatorCode, string $rechargeType, float $amount): Collection;

    public function findById(int $id): ?OperatorRoute;

    public function decrementSuccessRate(int $id): void;

    public function incrementSuccessRate(int $id): void;

    public function allActive(): Collection;
}
