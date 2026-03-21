<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Services\WalletServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminWalletController extends Controller
{
    public function __construct(private readonly WalletServiceInterface $walletService) {}

    /**
     * POST /api/v1/employee/sellers/{id}/wallet/adjust
     * type = 'credit' (add balance) | 'debit' (reverse/deduct)
     */
    public function adjust(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'type'        => ['required', 'in:credit,debit'],
            'amount'      => ['required', 'numeric', 'min:1', 'max:10000000'],
            'description' => ['required', 'string', 'max:255'],
        ]);

        $user   = User::findOrFail($id);
        $type   = $request->type;
        $amount = (float) $request->amount;

        if ($type === 'credit') {
            $txn = $this->walletService->topup(
                $user,
                $amount,
                $request->description,
                ['ip_address' => $request->ip(), 'reference_type' => 'admin_credit']
            );
            $action = 'admin.wallet_credit';
            $msg    = "Admin credited ₹{$amount} to user #{$id} ({$user->name})";
        } else {
            $wallet = DB::table('wallets')->where('user_id', $id)->first();
            $currentBalance = $wallet ? (float) $wallet->balance : 0.0;
            if ($amount > $currentBalance) {
                return response()->json([
                    'message' => "Cannot reverse ₹{$amount}. Current balance is ₹{$currentBalance}.",
                ], 422);
            }
            $txn = $this->walletService->debit(
                $user,
                $amount,
                $request->description,
                ['ip_address' => $request->ip(), 'reference_type' => 'admin_debit']
            );
            $action = 'admin.wallet_debit';
            $msg    = "Admin debited ₹{$amount} from user #{$id} ({$user->name})";
        }

        ActivityLogger::log($action, $msg, $user,
            ['amount' => $amount, 'type' => $type, 'description' => $request->description],
            null, $request);

        return response()->json([
            'message'     => $type === 'credit'
                ? "₹{$amount} added to {$user->name}'s wallet."
                : "₹{$amount} reversed from {$user->name}'s wallet.",
            'new_balance' => $txn->balance_after,
        ]);
    }

    /**
     * GET /api/v1/employee/sellers/{id}/wallet/transactions
     */
    public function transactions(Request $request, int $id): JsonResponse
    {
        User::findOrFail($id);

        $txns = DB::table('wallet_transactions')
            ->where('user_id', $id)
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return response()->json(['data' => $txns]);
    }
}
