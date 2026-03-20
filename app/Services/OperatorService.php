<?php

namespace App\Services;

use App\Contracts\Repositories\OperatorRepositoryInterface;
use App\Models\OperatorRoute;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class OperatorService
{
    public function __construct(
        private readonly OperatorRepositoryInterface $operatorRepo
    ) {}

    public function listActive(): Collection
    {
        return Cache::remember('all_active_operators', 300, fn () => $this->operatorRepo->allActive());
    }

    /**
     * Detect operator and telecom circle from a mobile number.
     * Uses the prefix table in config/recharge.php.
     *
     * Returns ['operator' => 'AIRTEL', 'circle' => 'Delhi'] or null on no match.
     */
    public function detectOperator(string $mobile): ?array
    {
        $prefix    = substr(preg_replace('/\D/', '', $mobile), 0, 4);
        $prefixMap = config('recharge.operator_prefixes', []);
        $entry     = $prefixMap[$prefix] ?? null;

        if (! $entry) {
            return null;
        }

        // Support legacy string format ('prefix' => 'OPERATOR')
        if (is_string($entry)) {
            return ['operator' => $entry, 'circle' => null];
        }

        return $entry; // ['operator' => ..., 'circle' => ...]
    }

    public function getRouteForAmount(string $operatorCode, float $amount, string $type = 'prepaid'): ?OperatorRoute
    {
        return $this->operatorRepo->getActiveRoutes($operatorCode, $type, $amount)->first();
    }
}
