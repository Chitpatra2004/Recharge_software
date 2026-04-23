<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Services\ApiRequestLogSchema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityLogController extends Controller
{
    // GET /api/v1/employee/activity
    public function index(Request $request): JsonResponse
    {
        /** @var Employee $actor */
        $actor = $request->user();
        if (! $actor || ! $actor->isAdmin()) {
            abort(403, 'Admin access required.');
        }

        $q = DB::table('activity_logs');

        if ($actorType = $request->get('actor_type')) {
            $q->where('actor_type', $actorType);
        }
        if ($actorId = $request->get('actor_id')) {
            $q->where('actor_id', $actorId);
        }
        if ($action = $request->get('action')) {
            $q->where('action', 'like', "%{$action}%");
        }
        if ($from = $request->get('from')) {
            $q->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $q->whereDate('created_at', '<=', $to);
        }
        if ($ip = $request->get('ip')) {
            $q->where('ip_address', $ip);
        }
        if ($search = $request->get('search')) {
            $q->where(function ($w) use ($search) {
                $w->where('description', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        $perPage = min((int) ($request->get('per_page', 50)), 100);
        $logs    = $q->orderBy('created_at', 'desc')->paginate($perPage);

        // Enrich employee names
        $employeeIds = collect($logs->items())
            ->where('actor_type', 'employee')
            ->pluck('actor_id')
            ->unique()
            ->filter();

        $employees = Employee::whereIn('id', $employeeIds)
            ->get(['id', 'name', 'employee_code', 'role'])
            ->keyBy('id');

        $items = collect($logs->items())->map(function ($row) use ($employees) {
            $arr = (array) $row;
            if ($arr['actor_type'] === 'employee' && isset($employees[$arr['actor_id']])) {
                $emp = $employees[$arr['actor_id']];
                $arr['actor_name']  = $emp->name;
                $arr['actor_code']  = $emp->employee_code;
                $arr['actor_role']  = $emp->role;
            }
            return $arr;
        });

        return response()->json([
            'data' => [
                'data'         => $items,
                'total'        => $logs->total(),
                'current_page' => $logs->currentPage(),
                'last_page'    => $logs->lastPage(),
            ],
        ]);
    }

    // GET /api/v1/employee/activity/employees-list  — for filter dropdown
    public function employeesList(Request $request): JsonResponse
    {
        /** @var Employee $actor */
        $actor = $request->user();
        if (! $actor || ! $actor->isAdmin()) {
            abort(403);
        }

        $employees = Employee::select('id', 'name', 'employee_code', 'role')
            ->orderBy('name')
            ->get();

        return response()->json(['employees' => $employees]);
    }

    // GET /api/v1/employee/api-logs
    public function apiLogs(Request $request): JsonResponse
    {
        /** @var Employee $actor */
        $actor = $request->user();
        if (! $actor || ! $actor->isAdmin()) {
            abort(403, 'Admin access required.');
        }

        $perPage = min((int) $request->integer('per_page', 50), 100);

        $baseQuery = DB::table('api_request_logs as l')
            ->leftJoin('users as u', 'u.id', '=', 'l.user_id')
            ->leftJoin('api_keys as k', 'k.id', '=', 'l.api_key_id')
            ->select([
                'l.id',
                'l.method',
                'l.path',
                'l.query_string',
                ApiRequestLogSchema::has('reference_id')
                    ? 'l.reference_id'
                    : DB::raw('NULL as reference_id'),
                'l.status_code',
                'l.response_time_ms',
                'l.ip_address',
                'l.user_agent',
                'l.request_size',
                'l.response_size',
                ApiRequestLogSchema::has('request_payload')
                    ? 'l.request_payload'
                    : DB::raw('NULL as request_payload'),
                'l.error_message',
                'l.created_at',
                'u.id as user_id',
                'u.name as user_name',
                'u.email as user_email',
                'u.mobile as user_mobile',
                'u.role as user_role',
                'k.id as api_key_id',
                'k.name as api_key_name',
                'k.key_prefix as api_key_prefix',
            ]);

        $applyFilters = function ($q) use ($request) {
            if ($method = $request->get('method')) {
                $q->where('l.method', strtoupper($method));
            }
            if ($status = $request->get('status_code')) {
                $q->where('l.status_code', (int) $status);
            }
            if ($path = $request->get('path')) {
                $q->where('l.path', 'like', '%' . $path . '%');
            }
            if (ApiRequestLogSchema::has('reference_id') && ($reference = $request->get('reference_id'))) {
                $q->where('l.reference_id', 'like', '%' . $reference . '%');
            }
            if ($userId = $request->get('user_id')) {
                $q->where('l.user_id', (int) $userId);
            }
            if ($from = $request->get('from')) {
                $q->whereDate('l.created_at', '>=', $from);
            }
            if ($to = $request->get('to')) {
                $q->whereDate('l.created_at', '<=', $to);
            }
            if ($search = $request->get('search')) {
                $q->where(function ($w) use ($search) {
                    $w->where('l.path', 'like', '%' . $search . '%')
                        ->orWhere('l.query_string', 'like', '%' . $search . '%')
                        ->orWhere('l.error_message', 'like', '%' . $search . '%')
                        ->orWhere('l.ip_address', 'like', '%' . $search . '%')
                        ->orWhere('u.name', 'like', '%' . $search . '%')
                        ->orWhere('u.email', 'like', '%' . $search . '%')
                        ->orWhere('u.mobile', 'like', '%' . $search . '%')
                        ->orWhere('k.key_prefix', 'like', '%' . $search . '%');

                    if (ApiRequestLogSchema::has('reference_id')) {
                        $w->orWhere('l.reference_id', 'like', '%' . $search . '%');
                    }

                    if (ApiRequestLogSchema::has('request_payload')) {
                        $w->orWhere('l.request_payload', 'like', '%' . $search . '%');
                    }
                });
            }
        };

        $applyFilters($baseQuery);

        $logs = $baseQuery
            ->orderByDesc('l.created_at')
            ->paginate($perPage);

        $summaryQuery = DB::table('api_request_logs as l')
            ->leftJoin('users as u', 'u.id', '=', 'l.user_id')
            ->leftJoin('api_keys as k', 'k.id', '=', 'l.api_key_id');

        $applyFilters($summaryQuery);

        $summary = $summaryQuery
            ->selectRaw('COUNT(*) as total_requests')
            ->selectRaw('SUM(CASE WHEN l.status_code BETWEEN 200 AND 299 THEN 1 ELSE 0 END) as success_requests')
            ->selectRaw('SUM(CASE WHEN l.status_code >= 400 THEN 1 ELSE 0 END) as error_requests')
            ->selectRaw('AVG(l.response_time_ms) as avg_response_time_ms')
            ->first();

        return response()->json([
            'data' => [
                'data'         => $logs->items(),
                'total'        => $logs->total(),
                'current_page' => $logs->currentPage(),
                'last_page'    => $logs->lastPage(),
                'summary'      => $summary,
            ],
        ]);
    }
}
