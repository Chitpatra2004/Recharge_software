<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Repositories\RechargeRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(private readonly RechargeRepositoryInterface $rechargeRepo) {}

    /**
     * GET /api/v1/transactions
     * Paginated, filterable transaction history for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'mobile', 'from', 'to', 'operator_code']);
        $perPage = $request->integer('per_page', 20);

        $transactions = $this->rechargeRepo->getForUser($request->user()->id, $filters, $perPage);

        return response()->json(['data' => $transactions]);
    }

    /**
     * GET /api/v1/transactions/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $transaction = $request->user()
            ->rechargeTransactions()
            ->with(['attempts:id,recharge_transaction_id,attempt_number,status,duration_ms,created_at'])
            ->findOrFail($id);

        return response()->json(['data' => $transaction]);
    }
}
