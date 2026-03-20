<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ╔══════════════════════════════════════════════════════════════════╗
 * ║  TABLE: employees                                                 ║
 * ║                                                                   ║
 * ║  Internal staff — separate from `users` (external consumers).    ║
 * ║  Has its own auth, role hierarchy, department tracking,          ║
 * ║  login audit, and shift/SLA management fields.                   ║
 * ║                                                                   ║
 * ║  INDEXING STRATEGY:                                              ║
 * ║  - employee_code: UNIQUE — used in reports and complaint assign  ║
 * ║  - email: UNIQUE — login                                         ║
 * ║  - (role, status): composite — dashboard filters                 ║
 * ║  - (department, status): composite — dept-level queries          ║
 * ║  - last_login_at: used for inactive employee audits              ║
 * ╚══════════════════════════════════════════════════════════════════╝
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            // Identity
            $table->string('employee_code', 20)->unique()
                  ->comment('e.g. EMP-0001, auto-generated');
            $table->string('name', 100);
            $table->string('email', 150)->unique();
            $table->string('mobile', 15);
            $table->string('password');
            $table->rememberToken();

            // Org structure
            $table->enum('department', [
                'operations', 'support', 'finance',
                'tech', 'sales', 'management',
            ])->default('support');
            $table->string('designation', 100)->nullable();

            // Access control — separate role hierarchy from users
            $table->enum('role', [
                'super_admin', 'admin', 'manager', 'agent', 'viewer',
            ])->default('agent');
            $table->enum('status', ['active', 'inactive', 'suspended'])
                  ->default('active');

            // Permissions JSON — granular feature flags
            $table->json('permissions')->nullable()
                  ->comment('e.g. {"refund":true,"topup":false}');

            // Login audit
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->unsignedSmallInteger('failed_login_count')->default(0);
            $table->timestamp('locked_until')->nullable()
                  ->comment('Account lock after N failed attempts');

            // Support SLA
            $table->unsignedSmallInteger('max_open_complaints')->default(50)
                  ->comment('SLA cap: max complaints assignable at once');

            $table->timestamps();
            $table->softDeletes();

            // ── Indexes ──────────────────────────────────────────────────
            $table->index(['role', 'status'],         'idx_emp_role_status');
            $table->index(['department', 'status'],   'idx_emp_dept_status');
            $table->index('last_login_at',            'idx_emp_last_login');
            $table->index('deleted_at',               'idx_emp_deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
