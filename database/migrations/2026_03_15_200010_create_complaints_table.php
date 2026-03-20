<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /**
         * ╔══════════════════════════════════════════════════════════════════╗
         * ║  TABLE: complaints                                                ║
         * ║                                                                   ║
         * ║  Support ticketing system tied to recharge transactions.         ║
         * ║  Assigned to employees (not users) via assigned_employee_id.     ║
         * ║                                                                   ║
         * ║  SLA tracking: sla_deadline computed at creation time from       ║
         * ║  priority (critical=1h, high=4h, medium=24h, low=72h).          ║
         * ║                                                                   ║
         * ║  INDEXING STRATEGY:                                              ║
         * ║  - (user_id, status): user's own complaint list                  ║
         * ║  - (status, priority, created_at): agent dashboard queue         ║
         * ║  - (assigned_employee_id, status): employee workload view        ║
         * ║  - (sla_deadline, status): SLA breach monitoring cron job        ║
         * ╚══════════════════════════════════════════════════════════════════╝
         */
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recharge_transaction_id')
                  ->nullable()->constrained()->nullOnDelete();

            // Assigned to an employee (internal staff), not a user
            $table->foreignId('assigned_employee_id')
                  ->nullable()
                  ->constrained('employees')
                  ->nullOnDelete();

            // Ticket identity
            $table->string('ticket_number', 20)->unique()
                  ->comment('Auto-generated: TKT-XXXXXXXX');
            $table->string('subject', 255);
            $table->text('description');

            // Classification
            $table->enum('type', [
                'recharge_failed', 'balance_deducted', 'wrong_recharge',
                'refund', 'operator_delay', 'other',
            ])->default('other');

            // Workflow status
            $table->enum('status', [
                'open', 'in_progress', 'waiting_on_operator',
                'waiting_on_user', 'resolved', 'closed', 'escalated',
            ])->default('open');

            $table->enum('priority', ['low', 'medium', 'high', 'critical'])
                  ->default('medium');

            // SLA
            $table->timestamp('sla_deadline')->nullable()
                  ->comment('Computed from priority at creation: critical=1h, high=4h, medium=24h, low=72h');
            $table->boolean('sla_breached')->default(false);

            // Resolution
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_note')->nullable();

            // Financial action taken
            $table->enum('resolution_action', [
                'none', 'refunded', 'reprocessed', 'manual_credit', 'closed_no_action',
            ])->default('none');

            $table->timestamps();
            $table->softDeletes();

            // ── Indexes ──────────────────────────────────────────────────
            $table->index(['user_id', 'status'],                      'idx_comp_user_status');
            $table->index(['status', 'priority', 'created_at'],       'idx_comp_status_priority_date');
            $table->index(['assigned_employee_id', 'status'],         'idx_comp_assignee_status');
            $table->index(['sla_deadline', 'status', 'sla_breached'], 'idx_comp_sla');
            $table->index('deleted_at',                               'idx_comp_deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
