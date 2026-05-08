<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('employee_permission_groups')) {
            Schema::create('employee_permission_groups', function (Blueprint $table) {
                $table->id();
                $table->string('key', 80)->unique();
                $table->string('name', 120);
                $table->string('description', 255)->nullable();
                $table->string('color', 20)->default('#2563eb');
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('employee_permission_definitions')) {
            Schema::create('employee_permission_definitions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('group_id')->constrained('employee_permission_groups')->cascadeOnDelete();
                $table->string('key', 120)->unique('uq_emp_perm_defs_key');
                $table->string('name', 150);
                $table->string('description', 255)->nullable();
                $table->boolean('is_pii')->default(false);
                $table->boolean('is_dangerous')->default(false);
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['group_id', 'is_active', 'sort_order'], 'idx_emp_perm_defs_group_active_sort');
            });

            return;
        }

        Schema::table('employee_permission_definitions', function (Blueprint $table) {
            if (! $this->indexExists('employee_permission_definitions', 'uq_emp_perm_defs_key')) {
                $table->unique('key', 'uq_emp_perm_defs_key');
            }

            if (! $this->indexExists('employee_permission_definitions', 'idx_emp_perm_defs_group_active_sort')) {
                $table->index(['group_id', 'is_active', 'sort_order'], 'idx_emp_perm_defs_group_active_sort');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_permission_definitions');
        Schema::dropIfExists('employee_permission_groups');
    }

    private function indexExists(string $table, string $index): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();
    }
};
