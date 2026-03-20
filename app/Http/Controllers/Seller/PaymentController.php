<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\SellerPaymentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /** GET /api/v1/seller/payments */
    public function index(Request $request): JsonResponse
    {
        $user    = $request->user();
        $perPage = min($request->integer('per_page', 20), 100);

        $requests = SellerPaymentRequest::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json(['data' => $requests]);
    }

    /** POST /api/v1/seller/payments */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'amount'           => ['required', 'numeric', 'min:100'],
            'payment_mode'     => ['required', 'in:bank_transfer,upi,neft,rtgs,cheque'],
            'reference_number' => ['required', 'string', 'max:100'],
            'notes'            => ['sometimes', 'nullable', 'string', 'max:500'],
            'proof_image'      => ['sometimes', 'nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf'],
        ]);

        $user      = $request->user();
        $proofPath = null;

        if ($request->hasFile('proof_image')) {
            $proofPath = $request->file('proof_image')
                ->store("seller_proofs/{$user->id}", 'local');
        }

        $pr = SellerPaymentRequest::create([
            'user_id'          => $user->id,
            'amount'           => $data['amount'],
            'payment_mode'     => $data['payment_mode'],
            'reference_number' => $data['reference_number'],
            'notes'            => $data['notes'] ?? null,
            'proof_image'      => $proofPath,
            'status'           => 'pending',
        ]);

        return response()->json([
            'message' => 'Payment request submitted. Admin will review and credit your wallet.',
            'data'    => $pr,
        ], 201);
    }
}
