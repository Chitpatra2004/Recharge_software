<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->requireAdmin($request);
        $this->ensureDefaults();

        $groups = DB::table('employee_permission_groups')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function ($group) {
                $group->permissions = DB::table('employee_permission_definitions')
                    ->where('group_id', $group->id)
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get();

                return $group;
            });

        return response()->json(['data' => ['groups' => $groups]]);
    }

    public function storeGroup(Request $request): JsonResponse
    {
        $this->requireAdmin($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'key' => ['nullable', 'string', 'max:80', 'unique:employee_permission_groups,key'],
            'description' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:20'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $key = $this->key($data['key'] ?? $data['name']);
        if (DB::table('employee_permission_groups')->where('key', $key)->exists()) {
            return response()->json(['message' => 'Permission group key already exists.'], 422);
        }

        $id = DB::table('employee_permission_groups')->insertGetId([
            'key' => $key,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'color' => $data['color'] ?? '#2563eb',
            'sort_order' => $data['sort_order'] ?? 100,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Permission group created.', 'id' => $id], 201);
    }

    public function updateGroup(Request $request, int $group): JsonResponse
    {
        $this->requireAdmin($request);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:20'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['updated_at'] = now();
        DB::table('employee_permission_groups')->where('id', $group)->update($data);

        return response()->json(['message' => 'Permission group updated.']);
    }

    public function storePermission(Request $request): JsonResponse
    {
        $this->requireAdmin($request);

        $data = $request->validate([
            'group_id' => ['required', 'integer', 'exists:employee_permission_groups,id'],
            'name' => ['required', 'string', 'max:150'],
            'key' => ['nullable', 'string', 'max:120', 'unique:employee_permission_definitions,key'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_pii' => ['nullable', 'boolean'],
            'is_dangerous' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $key = $this->key($data['key'] ?? $data['name'], ':');
        if (DB::table('employee_permission_definitions')->where('key', $key)->exists()) {
            return response()->json(['message' => 'Permission key already exists.'], 422);
        }

        $id = DB::table('employee_permission_definitions')->insertGetId([
            'group_id' => $data['group_id'],
            'key' => $key,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_pii' => (bool) ($data['is_pii'] ?? false),
            'is_dangerous' => (bool) ($data['is_dangerous'] ?? false),
            'sort_order' => $data['sort_order'] ?? 100,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Permission created.', 'id' => $id], 201);
    }

    public function updatePermission(Request $request, int $permission): JsonResponse
    {
        $this->requireAdmin($request);

        $data = $request->validate([
            'group_id' => ['sometimes', 'integer', 'exists:employee_permission_groups,id'],
            'name' => ['sometimes', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_pii' => ['nullable', 'boolean'],
            'is_dangerous' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['updated_at'] = now();
        DB::table('employee_permission_definitions')->where('id', $permission)->update($data);

        return response()->json(['message' => 'Permission updated.']);
    }

    public function destroyPermission(Request $request, int $permission): JsonResponse
    {
        $this->requireAdmin($request);

        DB::table('employee_permission_definitions')->where('id', $permission)->update([
            'is_active' => false,
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Permission disabled.']);
    }

    private function requireAdmin(Request $request): void
    {
        /** @var Employee|null $actor */
        $actor = $request->user();
        if (! $actor || ! $actor->isAdmin()) {
            abort(403, 'Admin access required.');
        }
    }

    private function key(string $value, string $separator = '_'): string
    {
        return Str::of($value)
            ->lower()
            ->replaceMatches('/[^a-z0-9:._-]+/', $separator)
            ->trim($separator . '._-')
            ->toString();
    }

    private function ensureDefaults(): void
    {
        if (DB::table('employee_permission_groups')->exists()) {
            return;
        }

        $defaults = [
            ['users', 'User Management', 'Access to user accounts', '#2563eb', [
                ['users:view', 'View Users', 'Browse and search user accounts', true, false],
                ['users:edit', 'Edit Users', 'Modify user details and status', true, true],
                ['users:login_as', 'Login As User', 'Open user portal as selected user', true, true],
            ]],
            ['reports', 'Reports & Analytics', 'Transaction and system reports', '#0891b2', [
                ['reports:view', 'View Reports', 'Open reports and analytics pages', false, false],
                ['reports:export', 'Export Reports', 'Download reports as CSV or Excel', true, false],
                ['activity:view', 'View Activity Logs', 'See system and employee activity', true, false],
            ]],
            ['finance', 'Financial Operations', 'Wallet and refund management', '#d97706', [
                ['wallet:manage', 'Manage Wallets', 'View and top-up user wallets', true, true],
                ['recharge:refund', 'Process Refunds', 'Approve and initiate recharge refunds', true, true],
                ['payments:approve', 'Approve Payments', 'Approve seller or user payment requests', true, true],
            ]],
            ['support', 'Customer Support', 'Complaint and ticket management', '#059669', [
                ['complaints:view', 'View Complaints', 'View complaint tickets', true, false],
                ['complaints:resolve', 'Resolve Complaints', 'Reply and resolve complaint tickets', true, false],
            ]],
            ['settings', 'System Settings', 'High-level system configuration', '#7c3aed', [
                ['settings:view', 'View Settings', 'Open system configuration pages', false, false],
                ['settings:manage', 'Manage Settings', 'Change platform settings and integrations', false, true],
                ['permissions:manage', 'Manage Permissions', 'Create permission groups and assign rights', true, true],
            ]],
        ];

        foreach ($defaults as [$key, $name, $description, $color, $permissions]) {
            $groupId = DB::table('employee_permission_groups')->insertGetId([
                'key' => $key,
                'name' => $name,
                'description' => $description,
                'color' => $color,
                'sort_order' => DB::table('employee_permission_groups')->count() + 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($permissions as $index => [$permKey, $permName, $permDescription, $isPii, $isDangerous]) {
                DB::table('employee_permission_definitions')->insert([
                    'group_id' => $groupId,
                    'key' => $permKey,
                    'name' => $permName,
                    'description' => $permDescription,
                    'is_pii' => $isPii,
                    'is_dangerous' => $isDangerous,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
