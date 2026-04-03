<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserPaymentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserPaymentRequestController extends Controller
{
    /** GET /api/v1/payment-requests */
    public function index(Request $request): JsonResponse
    {
        $user    = $request->user();
        $perPage = min($request->integer('per_page', 15), 100);

        $rows = UserPaymentRequest::where('user_id', $user->id)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json($rows);
    }

    /** POST /api/v1/payment-requests */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'amount'           => ['required', 'numeric', 'min:10', 'max:500000'],
            'payment_mode'     => ['required', 'in:upi,bank_transfer,neft,rtgs,cheque'],
            'reference_number' => ['required', 'string', 'max:100'],
            'upi_id'           => ['sometimes', 'nullable', 'string', 'max:100'],
            'payment_date'     => ['sometimes', 'nullable', 'date', 'before_or_equal:today'],
            'notes'            => ['sometimes', 'nullable', 'string', 'max:500'],
            'proof_image'      => ['sometimes', 'nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf'],
        ]);

        $user      = $request->user();
        $proofPath = null;

        if ($request->hasFile('proof_image')) {
            $proofPath = $request->file('proof_image')
                ->store("user_proofs/{$user->id}", 'local');
        }

        $pr = UserPaymentRequest::create([
            'user_id'          => $user->id,
            'amount'           => $data['amount'],
            'payment_mode'     => $data['payment_mode'],
            'reference_number' => $data['reference_number'],
            'upi_id'           => $data['upi_id'] ?? null,
            'payment_date'     => $data['payment_date'] ?? null,
            'notes'            => $data['notes'] ?? null,
            'proof_image'      => $proofPath,
            'status'           => 'pending',
        ]);

        return response()->json([
            'message' => 'Payment request submitted. Admin will review and credit your wallet within 24 hours.',
            'data'    => $pr,
        ], 201);
    }
}
