<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = DB::table('recharge_transactions')
            ->where('recharge_transactions.user_id', $user->id)
            ->orderByDesc('recharge_transactions.created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('recharge_transactions.created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('recharge_transactions.created_at', '<=', $request->date_to);
        }
        if ($request->filled('operator')) {
            $query->where('operator_code', $request->operator);
        }
        if ($request->filled('mobile') || $request->filled('search')) {
            $q = $request->input('mobile', $request->search);
            $query->where('mobile', 'like', "%{$q}%");
        }

        $perPage = min($request->integer('per_page', 20), 100);
        $results = $query->paginate($perPage, [
            'id',
            'mobile',
            'operator_code',
            'circle',
            'recharge_type as type',
            'amount',
            'status',
            'operator_ref as operator_txn_id',
            'api_ref as external_ref',
            'created_at',
        ]);

        return response()->json($results);
    }
}
