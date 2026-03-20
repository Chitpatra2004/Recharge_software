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
         * ║  TABLE: complaint_logs                                            ║
         * ║                                                                   ║
         * ║  Append-only audit trail for every action on a complaint.        ║
         * ║  actor_type distinguishes between user (customer) and employee   ║
         * ║  (internal staff) actions.                                       ║
         * ║                                                                   ║
         * ║  INDEXING STRATEGY:                                              ║
         * ║  - (complaint_id, created_at): timeline view of a ticket         ║
         * ║  - (actor_type, actor_id): "what did this agent do today?"       ║
         * ╚══════════════════════════════════════════════════════════════════╝
         */
        Schema::create('complaint_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();

            // Polymorphic actor — either a user (customer) or employee (staff)
            $table->enum('actor_type', ['user', 'employee'])->default('employee');
            $table->unsignedBigInteger('actor_id');

            $table->string('action', 80)
                  ->comment('e.g. created, assigned, status_changed, refund_issued, note_added');

            // What changed (for status_changed actions)
            $table->string('from_status', 50)->nullable();
            $table->string('to_status', 50)->nullable();

            $table->text('note')->nullable();
            $table->json('meta')->nullable()
                  ->comment('Extra context: previous values, system info');

            // Timestamps only — no updated_at (append-only)
            $table->timestamp('created_at')->useCurrent();

            // ── Indexes ──────────────────────────────────────────────────
            $table->index(['complaint_id', 'created_at'], 'idx_clogs_complaint_date');
            $table->index(['actor_type', 'actor_id'],     'idx_clogs_actor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_logs');
    }
};
