<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\SellerGstInvoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GstController extends Controller
{
    /** GET /api/v1/seller/gst */
    public function index(Request $request): JsonResponse
    {
        $user    = $request->user();
        $perPage = min($request->integer('per_page', 20), 100);

        $invoices = SellerGstInvoice::where('user_id', $user->id)
            ->orderByDesc('invoice_date')
            ->paginate($perPage);

        return response()->json(['data' => $invoices]);
    }

    /** POST /api/v1/seller/gst */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'invoice_number' => ['required', 'string', 'max:100'],
            'invoice_date'   => ['required', 'date'],
            'amount'         => ['required', 'numeric', 'min:0'],
            'gst_amount'     => ['required', 'numeric', 'min:0'],
            'period_from'    => ['required', 'date'],
            'period_to'      => ['required', 'date', 'after_or_equal:period_from'],
            'file'           => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png'],
        ]);

        $user     = $request->user();
        $filePath = $request->file('file')
            ->store("seller_gst/{$user->id}", 'local');

        $invoice = SellerGstInvoice::create([
            'user_id'        => $user->id,
            'invoice_number' => $data['invoice_number'],
            'invoice_date'   => $data['invoice_date'],
            'amount'         => $data['amount'],
            'gst_amount'     => $data['gst_amount'],
            'file_path'      => $filePath,
            'period_from'    => $data['period_from'],
            'period_to'      => $data['period_to'],
        ]);

        return response()->json([
            'message' => 'GST invoice uploaded successfully.',
            'data'    => $invoice,
        ], 201);
    }
}
