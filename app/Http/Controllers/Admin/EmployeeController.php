<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    // GET /api/v1/admin/employees
    public function index(Request $request): JsonResponse
    {
        $this->requireAdmin($request);

        $q = Employee::query()->withTrashed(false);

        if ($search = $request->get('search')) {
            $q->where(function ($w) use ($search) {
                $w->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }
        if ($role = $request->get('role')) {
            $q->where('role', $role);
        }
        if ($status = $request->get('status')) {
            $q->where('status', $status);
        }

        $employees = $q->orderBy('created_at', 'desc')
                       ->paginate((int) ($request->get('per_page', 20)));

        return response()->json(['data' => $employees]);
    }

    // POST /api/v1/admin/employees
    public function store(Request $request): JsonResponse
    {
        $this->requireAdmin($request);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'email'       => ['required', 'email', 'max:150', 'unique:employees,email'],
            'mobile'      => ['nullable', 'string', 'size:10', 'unique:employees,mobile'],
            'password'    => ['required', 'string', 'min:8', 'max:72'],
            'role'        => ['required', Rule::in(['super_admin','admin','manager','support','finance'])],
            'department'  => ['nullable', 'string', 'max:80'],
            'designation' => ['nullable', 'string', 'max:80'],
            'status'      => ['nullable', Rule::in(['active','inactive','suspended'])],
            'permissions' => ['nullable', 'array'],
            'max_open_complaints' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $data['employee_code'] = $this->generateCode();
        $data['status']        = $data['status'] ?? 'active';

        $employee = Employee::create($data);

        return response()->json([
            'message'  => 'Employee created successfully.',
            'employee' => $employee,
        ], 201);
    }

    // GET /api/v1/admin/employees/{id}
    public function show(Request $request, int $id): JsonResponse
    {
        $this->requireAdmin($request);
        $employee = Employee::findOrFail($id);
        return response()->json(['employee' => $employee]);
    }

    // PUT /api/v1/admin/employees/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $this->requireAdmin($request);

        $employee = Employee::findOrFail($id);

        $data = $request->validate([
            'name'        => ['sometimes', 'string', 'max:100'],
            'email'       => ['sometimes', 'email', 'max:150', Rule::unique('employees','email')->ignore($id)],
            'mobile'      => ['nullable', 'string', 'size:10', Rule::unique('employees','mobile')->ignore($id)],
            'password'    => ['nullable', 'string', 'min:8', 'max:72'],
            'role'        => ['sometimes', Rule::in(['super_admin','admin','manager','support','finance'])],
            'department'  => ['nullable', 'string', 'max:80'],
            'designation' => ['nullable', 'string', 'max:80'],
            'status'      => ['sometimes', Rule::in(['active','inactive','suspended'])],
            'permissions' => ['nullable', 'array'],
            'max_open_complaints' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        // Remove null password so it doesn't overwrite existing
        if (array_key_exists('password', $data) && empty($data['password'])) {
            unset($data['password']);
        }

        $employee->update($data);

        return response()->json([
            'message'  => 'Employee updated successfully.',
            'employee' => $employee->fresh(),
        ]);
    }

    // DELETE /api/v1/admin/employees/{id}
    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->requireAdmin($request);

        $employee = Employee::findOrFail($id);

        // Prevent self-deletion
        if ($request->user()->id === $employee->id) {
            return response()->json(['message' => 'You cannot delete your own account.'], 403);
        }

        $employee->delete();

        return response()->json(['message' => 'Employee deleted.']);
    }

    // ── helpers ────────────────────────────────────────────────────────────────

    private function requireAdmin(Request $request): void
    {
        /** @var Employee $actor */
        $actor = $request->user();
        if (! $actor || ! $actor->isAdmin()) {
            abort(403, 'Admin access required.');
        }
    }

    private function generateCode(): string
    {
        $last = Employee::withTrashed()->max('id') ?? 0;
        return 'EMP' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
    }
}
