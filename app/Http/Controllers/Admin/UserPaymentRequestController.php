<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserPaymentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserPaymentRequestController extends Controller
{
    /** GET /api/v1/employee/user-payment-requests */
    public function index(Request $request): JsonResponse
    {
        $q = UserPaymentRequest::with([
                'user:id,name,email,mobile,role,status',
                'processor:id,name,email',
            ])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $q->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $q->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $q->where(function ($sub) use ($search) {
                $sub->where('reference_number', 'like', $search)
                    ->orWhere('upi_id', 'like', $search)
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $search)
                        ->orWhere('email', 'like', $search)
                        ->orWhere('mobile', 'like', $search));
            });
        }

        $perPage = min($request->integer('per_page', 20), 100);
        $rows = $q->paginate($perPage);

        $userIds = collect($rows->items())->pluck('user_id')->unique()->values();
        $wallets = DB::table('wallets')->whereIn('user_id', $userIds)->get()->keyBy('user_id');
        $references = collect($rows->items())->pluck('reference_number')->filter()->unique()->values();
        $walletTxns = DB::table('wallet_transactions')
            ->whereIn('rrn', $references)
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('rrn');

        $rows->getCollection()->transform(function (UserPaymentRequest $row) use ($wallets, $walletTxns) {
            $wallet = $wallets->get($row->user_id);
            $walletTxn = $walletTxns->get($row->reference_number)?->first();

            $row->current_balance = $wallet ? (float) $wallet->balance : 0.0;
            $row->wallet_status = $wallet->status ?? null;
            $row->wallet_transaction = $walletTxn;

            return $row;
        });

        $stats = DB::selectOne("
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'pending'  THEN 1 ELSE 0 END) AS pending_count,
                SUM(CASE WHEN status = 'pending'  THEN amount ELSE 0 END) AS pending_amount,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved_count,
                SUM(CASE WHEN status = 'approved' THEN amount ELSE 0 END) AS approved_amount,
                SUM(CASE WHEN status = 'approved' AND DATE(processed_at) = CURDATE() THEN 1   ELSE 0 END) AS approved_today,
                SUM(CASE WHEN status = 'approved' AND DATE(processed_at) = CURDATE() THEN amount ELSE 0 END) AS approved_today_amount
            FROM user_payment_requests
        ");

        return response()->json([
            'data'  => $rows,
            'stats' => $stats,
        ]);
    }

    /** GET /api/v1/employee/user-payment-requests/pg-report */
    public function pgReport(Request $request): JsonResponse
    {
        $base = UserPaymentRequest::with(['user:id,name,email,mobile,role,status', 'processor:id,name,email'])
            ->orderByDesc('created_at');

        $this->applyPgFilters($base, $request);

        $perPage = min($request->integer('per_page', 50), 100);
        $rows = $base->paginate($perPage);

        $rows->getCollection()->transform(function (UserPaymentRequest $row) {
            $row->site_order_id = 'PG-' . str_pad((string) $row->id, 8, '0', STR_PAD_LEFT);
            $row->pg_name = strtoupper(str_replace('_', ' ', (string) $row->payment_mode));
            $row->bank_rrn = $row->reference_number;
            return $row;
        });

        $summaryQuery = UserPaymentRequest::query();
        $this->applyPgFilters($summaryQuery, $request);
        $summary = $summaryQuery
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw('SUM(amount) as total_amount')
            ->selectRaw("SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as success_count")
            ->selectRaw("SUM(CASE WHEN status = 'approved' THEN amount ELSE 0 END) as success_amount")
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count")
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_amount")
            ->selectRaw("SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as failed_count")
            ->selectRaw("SUM(CASE WHEN status = 'rejected' THEN amount ELSE 0 END) as failed_amount")
            ->first();

        $gatewayQuery = UserPaymentRequest::query();
        $this->applyPgFilters($gatewayQuery, $request);
        $gateways = $gatewayQuery
            ->selectRaw('payment_mode as pg_name')
            ->selectRaw('status')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(amount) as amount')
            ->groupBy('payment_mode', 'status')
            ->orderBy('payment_mode')
            ->get();

        return response()->json([
            'data' => $rows,
            'summary' => $summary,
            'gateways' => $gateways,
        ]);
    }

    /** GET /api/v1/employee/user-payment-requests/pg-switching-report */
    public function pgSwitchingReport(Request $request): JsonResponse
    {
        $q = UserPaymentRequest::query();

        if ($request->filled('date_from')) $q->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to')) $q->whereDate('created_at', '<=', $request->date_to);

        $rows = $q->selectRaw('payment_mode as pg_name')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(amount) as total_amount')
            ->selectRaw("SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as success_count")
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count")
            ->selectRaw("SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as failed_count")
            ->selectRaw('MAX(created_at) as last_used_at')
            ->groupBy('payment_mode')
            ->orderByDesc('success_count')
            ->get()
            ->values()
            ->map(function ($row, $index) {
                $total = max((int) $row->total, 1);
                $row->priority = $index + 1;
                $row->success_rate = round(((int) $row->success_count / $total) * 100, 2);
                $row->switch_status = $index === 0 ? 'primary' : 'backup';
                return $row;
            });

        return response()->json(['data' => $rows]);
    }

    private function applyPgFilters($q, Request $request): void
    {
        if ($request->filled('date_from')) $q->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to')) $q->whereDate('created_at', '<=', $request->date_to);
        if ($request->filled('pg_name')) $q->where('payment_mode', $request->pg_name);

        if ($request->filled('status')) {
            $status = $request->status;
            $mapped = match ($status) {
                'success' => 'approved',
                'failed' => 'rejected',
                default => $status,
            };
            $q->where('status', $mapped);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $like = '%' . $search . '%';
            $q->where(function ($sub) use ($search, $like) {
                if (preg_match('/^PG-?0*(\d+)$/i', $search, $m)) {
                    $sub->orWhere('id', (int) $m[1]);
                }
                if (ctype_digit($search)) {
                    $sub->orWhere('id', (int) $search);
                }
                $sub->orWhere('reference_number', 'like', $like)
                    ->orWhere('upi_id', 'like', $like)
                    ->orWhereHas('user', fn ($u) => $u->where('mobile', 'like', $like)
                        ->orWhere('name', 'like', $like)
                        ->orWhere('email', 'like', $like));
            });
        }
    }

    /** POST /api/v1/employee/user-payment-requests/{id}/approve */
    public function approve(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'admin_notes' => ['sometimes', 'nullable', 'string', 'max:500'],
        ]);

        $pr = UserPaymentRequest::with('user')->findOrFail($id);

        if ($pr->status !== 'pending') {
            return response()->json(['message' => 'Request is already ' . $pr->status . '.'], 422);
        }

        DB::transaction(function () use ($request, $pr, $data) {
            $wallet = DB::table('wallets')->where('user_id', $pr->user_id)->lockForUpdate()->first();

            if ($wallet) {
                $balAfter = (float) $wallet->balance + (float) $pr->amount;
                DB::table('wallets')->where('user_id', $pr->user_id)->update([
                    'balance'    => $balAfter,
                    'updated_at' => now(),
                ]);

                DB::table('wallet_transactions')->insert([
                    'wallet_id'      => $wallet->id,
                    'user_id'        => $pr->user_id,
                    'type'           => 'credit',
                    'amount'         => $pr->amount,
                    'balance_before' => (float) $wallet->balance,
                    'balance_after'  => $balAfter,
                    'description'    => 'Wallet top-up approved - Ref: ' . $pr->reference_number,
                    'bank_name'      => strtoupper((string) $pr->payment_mode),
                    'rrn'            => $pr->reference_number,
                    'remark'         => $pr->notes,
                    'admin_remark'   => $data['admin_notes'] ?? null,
                    'reference_type' => UserPaymentRequest::class,
                    'reference_id'   => $pr->id,
                    'txn_id'         => 'TOPUP-' . strtoupper(substr(md5($pr->id . now()), 0, 10)),
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }

            $updateData = [
                'status'       => 'approved',
                'admin_notes'  => $data['admin_notes'] ?? null,
                'processed_at' => now(),
            ];

            if (\Illuminate\Support\Facades\Schema::hasColumn('user_payment_requests', 'processed_by_employee_id')) {
                $updateData['processed_by_employee_id'] = $request->user('employee')?->id;
            }

            $pr->update($updateData);
        });

        return response()->json([
            'message' => 'Payment request approved and Rs ' . number_format($pr->amount, 2) . ' credited to user wallet.',
            'data'    => $pr->fresh(['user', 'processor']),
        ]);
    }

    /** POST /api/v1/employee/user-payment-requests/{id}/reject */
    public function reject(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'admin_notes' => ['required', 'string', 'max:500'],
        ]);

        $pr = UserPaymentRequest::findOrFail($id);

        if ($pr->status !== 'pending') {
            return response()->json(['message' => 'Request is already ' . $pr->status . '.'], 422);
        }

        $updateData = [
            'status'       => 'rejected',
            'admin_notes'  => $data['admin_notes'],
            'processed_at' => now(),
        ];

        if (\Illuminate\Support\Facades\Schema::hasColumn('user_payment_requests', 'processed_by_employee_id')) {
            $updateData['processed_by_employee_id'] = $request->user('employee')?->id;
        }

        $pr->update($updateData);

        return response()->json([
            'message' => 'Payment request rejected.',
            'data'    => $pr->fresh(['user', 'processor']),
        ]);
    }
}
