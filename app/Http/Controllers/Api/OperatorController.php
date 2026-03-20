<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OperatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OperatorController extends Controller
{
    public function __construct(private readonly OperatorService $operatorService) {}

    /**
     * GET /api/v1/operators
     */
    public function index(): JsonResponse
    {
        $operators = $this->operatorService->listActive()
            ->groupBy('operator_code')
            ->map(fn ($routes) => [
                'operator_code'   => $routes->first()->operator_code,
                'recharge_types'  => $routes->pluck('recharge_type')->unique()->values(),
                'min_amount'      => $routes->min('min_amount'),
                'max_amount'      => $routes->max('max_amount'),
            ])
            ->values();

        return response()->json(['data' => $operators]);
    }

    /**
     * GET /api/v1/operators/detect?mobile=9876543210
     *
     * Returns:
     *   operator_code  — e.g. "AIRTEL"
     *   operator_name  — e.g. "Airtel"
     *   circle         — e.g. "Delhi"
     */
    public function detect(Request $request): JsonResponse
    {
        $request->validate(['mobile' => 'required|string|max:15']);

        $result = $this->operatorService->detectOperator($request->query('mobile'));

        if (! $result) {
            return response()->json([
                'operator_code' => null,
                'operator_name' => null,
                'circle'        => null,
            ]);
        }

        $nameMap = [
            'AIRTEL' => 'Airtel',
            'JIO'    => 'Jio',
            'VI'     => 'Vi (Vodafone Idea)',
            'BSNL'   => 'BSNL',
            'MTNL'   => 'MTNL',
        ];

        return response()->json([
            'operator_code' => $result['operator'],
            'operator_name' => $nameMap[$result['operator']] ?? $result['operator'],
            'circle'        => $result['circle'],
        ]);
    }
}
