<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Activity logs — immutable audit trail for all user/system actions.
 * Partitioned by created_at (use MySQL partitioning in production).
 */
return new class extends Migration
{
    public function up(): void
    {
        /**
         * ╔══════════════════════════════════════════════════════════════════╗
         * ║  TABLE: activity_logs                                             ║
         * ║                                                                   ║
         * ║  VOLUME: 5–10× recharge volume = 5M–10M rows/day                ║
         * ║  Append-only immutable audit trail.                              ║
         * ║                                                                   ║
         * ║  PARTITION STRATEGY: RANGE by UNIX_TIMESTAMP(created_at)        ║
         * ║  Older partitions archived to cold storage after 90 days.       ║
         * ║                                                                   ║
         * ║  actor_type: 'user' | 'employee' | 'system'                     ║
         * ║  Allows tracking both customer and staff actions in one table.   ║
         * ║                                                                   ║
         * ║  INDEXING STRATEGY:                                              ║
         * ║  - (actor_type, actor_id, created_at): user/employee activity    ║
         * ║  - (action, created_at): action-type frequency reports           ║
         * ║  - (subject_type, subject_id): "who touched this record?"        ║
         * ║  - ip_address: security audit (suspicious IP queries)            ║
         * ╚══════════════════════════════════════════════════════════════════╝
         */
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            // Who performed the action (polymorphic actor)
            $table->enum('actor_type', ['user', 'employee', 'system'])->default('user');
            $table->unsignedBigInteger('actor_id')->nullable()
                  ->comment('NULL for system-initiated actions');

            // Action taken
            $table->string('action', 100)
                  ->comment('e.g. auth.login, recharge.initiated, wallet.topup');
            $table->string('description', 500)->nullable();

            // What record was acted upon (polymorphic subject)
            $table->string('subject_type', 100)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();

            // Change data capture — before/after state for audits
            $table->json('properties')->nullable()
                  ->comment('{"before":{}, "after":{}, "meta":{}}');

            // Request context
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('url', 1000)->nullable();
            $table->string('method', 10)->nullable();
            $table->string('session_id', 100)->nullable();

            // created_at NOT NULL — required for partitioning
            $table->timestamp('created_at')->useCurrent();
            // No updated_at — append-only

            // ── Indexes ──────────────────────────────────────────────────
            $table->index(['actor_type', 'actor_id', 'created_at'], 'idx_al_actor_date');
            $table->index(['action', 'created_at'],                  'idx_al_action_date');
            $table->index(['subject_type', 'subject_id'],            'idx_al_subject');
            $table->index('ip_address',                              'idx_al_ip');
            $table->index('created_at',                              'idx_al_created_at');
        });

        // ── Monthly partitioning — same strategy as recharge_transactions ──
        if (app()->environment('production', 'staging')) {
            \Illuminate\Support\Facades\DB::statement("
                ALTER TABLE activity_logs
                PARTITION BY RANGE (UNIX_TIMESTAMP(created_at)) (
                    PARTITION al_2026_01 VALUES LESS THAN (UNIX_TIMESTAMP('2026-02-01')),
                    PARTITION al_2026_02 VALUES LESS THAN (UNIX_TIMESTAMP('2026-03-01')),
                    PARTITION al_2026_03 VALUES LESS THAN (UNIX_TIMESTAMP('2026-04-01')),
                    PARTITION al_2026_04 VALUES LESS THAN (UNIX_TIMESTAMP('2026-05-01')),
                    PARTITION al_2026_05 VALUES LESS THAN (UNIX_TIMESTAMP('2026-06-01')),
                    PARTITION al_2026_06 VALUES LESS THAN (UNIX_TIMESTAMP('2026-07-01')),
                    PARTITION al_2026_07 VALUES LESS THAN (UNIX_TIMESTAMP('2026-08-01')),
                    PARTITION al_2026_08 VALUES LESS THAN (UNIX_TIMESTAMP('2026-09-01')),
                    PARTITION al_2026_09 VALUES LESS THAN (UNIX_TIMESTAMP('2026-10-01')),
                    PARTITION al_2026_10 VALUES LESS THAN (UNIX_TIMESTAMP('2026-11-01')),
                    PARTITION al_2026_11 VALUES LESS THAN (UNIX_TIMESTAMP('2026-12-01')),
                    PARTITION al_2026_12 VALUES LESS THAN (UNIX_TIMESTAMP('2027-01-01')),
                    PARTITION al_future   VALUES LESS THAN MAXVALUE
                )
            ");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
