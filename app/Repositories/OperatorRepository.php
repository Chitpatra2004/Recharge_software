<?php

namespace App\Repositories;

use App\Contracts\Repositories\OperatorRepositoryInterface;
use App\Models\OperatorRoute;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class OperatorRepository implements OperatorRepositoryInterface
{
    private const CACHE_TTL = 300; // 5 minutes

    public function getActiveRoutes(string $operatorCode, string $rechargeType, float $amount): Collection
    {
        $cacheKey = "operator_routes:{$operatorCode}:{$rechargeType}";

        $routes = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($operatorCode, $rechargeType) {
            return OperatorRoute::active()
                ->forOperator($operatorCode, $rechargeType)
                ->get();
        });

        // Filter by amount range (not cached to allow precise filtering)
        return $routes->filter(fn (OperatorRoute $r) =>
            $amount >= (float) $r->min_amount && $amount <= (float) $r->max_amount
        )->values();
    }

    public function findById(int $id): ?OperatorRoute
    {
        return OperatorRoute::find($id);
    }

    public function decrementSuccessRate(int $id): void
    {
        OperatorRoute::where('id', $id)
            ->where('success_rate', '>', 0)
            ->decrement('success_rate', 2);

        $this->bustCache($id);
    }

    public function incrementSuccessRate(int $id): void
    {
        OperatorRoute::where('id', $id)
            ->where('success_rate', '<', 100)
            ->increment('success_rate', 1);

        $this->bustCache($id);
    }

    public function allActive(): Collection
    {
        return OperatorRoute::active()->orderBy('operator_code')->orderBy('priority')->get();
    }

    private function bustCache(int $id): void
    {
        // Select only the two columns needed — avoids loading the full model.
        $route = OperatorRoute::where('id', $id)
            ->select(['operator_code', 'recharge_type'])
            ->first();

        if ($route) {
            Cache::forget("operator_routes:{$route->operator_code}:{$route->recharge_type}");
        }
    }
}
