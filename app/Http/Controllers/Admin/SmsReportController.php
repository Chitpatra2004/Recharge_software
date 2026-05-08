<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmsLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class SmsReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (! Schema::hasTable('sms_logs')) {
            return response()->json([
                'data' => [
                    'data' => [],
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => $request->integer('per_page', 20),
                    'total' => 0,
                ],
                'stats' => ['total' => 0, 'sent' => 0, 'failed' => 0, 'today' => 0],
                'message' => 'SMS log table is not migrated yet.',
            ]);
        }

        $q = SmsLog::with('user:id,name,email,mobile,role,status')
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }

        if ($request->filled('purpose')) {
            $q->where('purpose', $request->purpose);
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
                $sub->where('mobile', 'like', $search)
                    ->orWhere('message', 'like', $search)
                    ->orWhere('purpose', 'like', $search)
                    ->orWhere('provider_message_id', 'like', $search)
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $search)
                        ->orWhere('email', 'like', $search)
                        ->orWhere('mobile', 'like', $search));
            });
        }

        $perPage = min($request->integer('per_page', 20), 100);
        $rows = $q->paginate($perPage);

        $stats = [
            'total' => SmsLog::count(),
            'sent' => SmsLog::where('status', 'sent')->count(),
            'failed' => SmsLog::where('status', 'failed')->count(),
            'today' => SmsLog::whereDate('created_at', today())->count(),
        ];

        return response()->json([
            'data' => $rows,
            'stats' => $stats,
        ]);
    }
}
