<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
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
}
