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
         * ║  TABLE: recharge_transactions                                     ║
         * ║                                                                   ║
         * ║  VOLUME: 1 000 000+ rows/day ≈ 11–12 writes/second              ║
         * ║                                                                   ║
         * ║  PARTITION STRATEGY: RANGE by UNIX_TIMESTAMP(created_at)        ║
         * ║  • One partition per month                                        ║
         * ║  • Queries with created_at range scan 1 partition only           ║
         * ║  • Old partitions can be dropped (instant) for archival          ║
         * ║  • Partition pruning works with all indexed column queries        ║
         * ║                                                                   ║
         * ║  INDEXING STRATEGY (composite — leftmost-prefix rule):           ║
         * ║                                                                   ║
         * ║  idx_rt_user_status_date                                         ║
         * ║    → User dashboard: WHERE user_id=? AND status=? ORDER BY date  ║
         * ║                                                                   ║
         * ║  idx_rt_mobile_date                                              ║
         * ║    → Mobile search: WHERE mobile=? AND date BETWEEN …           ║
         * ║                                                                   ║
         * ║  idx_rt_operator_status_date                                     ║
         * ║    → Operator report: WHERE operator_code=? AND status=?        ║
         * ║                                                                   ║
         * ║  idx_rt_status_date                                              ║
         * ║    → Admin status filter / queue monitoring                      ║
         * ║                                                                   ║
         * ║  idx_rt_processed_date                                           ║
         * ║    → Reconciliation: WHERE processed_at BETWEEN … AND status=?  ║
         * ║                                                                   ║
         * ║  UNIQUE uq_idempotency_key — duplicate prevention at DB level   ║
         * ║                                                                   ║
         * ║  DENORMALIZED FIELDS: operator_code, circle stored directly to  ║
         * ║  avoid JOINs in 99% of queries. Acceptable data redundancy for   ║
         * ║  this volume level.                                              ║
         * ╚══════════════════════════════════════════════════════════════════╝
         */
        Schema::create('recharge_transactions', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('buyer_id')->nullable();
            $table->unsignedBigInteger('operator_id')->nullable()
                  ->comment('FK to operators — nullable for legacy data');
            $table->unsignedBigInteger('operator_route_id')->nullable()
                  ->comment('Which route was used for successful delivery');

            // ── Idempotency ────────────────────────────────────────────
            // Client-supplied UUID per request; UNIQUE enforced at DB level.
            // If same key arrives twice → return existing record (409 Conflict).
            $table->string('idempotency_key', 128)->unique('uq_rt_idempotency_key');

            // ── Recharge details ───────────────────────────────────────
            $table->string('mobile', 15)->comment('Target mobile / DTH subscriber ID');
            $table->string('operator_code', 30)
                  ->comment('Denormalized: AIRTEL, JIO … — no JOIN needed for filters');
            $table->string('circle', 50)->nullable()
                  ->comment('Telecom circle: Mumbai, Delhi, UP-East …');
            $table->enum('recharge_type', ['prepaid', 'postpaid', 'dth', 'broadband'])
                  ->default('prepaid');

            // ── Financial ─────────────────────────────────────────────
            $table->decimal('amount', 10, 2)
                  ->comment('Gross amount requested by user');
            $table->decimal('commission', 8, 2)->default(0.00)
                  ->comment('Commission earned by user on this transaction');
            $table->decimal('net_amount', 10, 2)
                  ->comment('amount - commission = platform revenue');
            $table->decimal('operator_margin', 8, 2)->default(0.00)
                  ->comment('Operator-level discount applied');

            // ── Status ────────────────────────────────────────────────
            $table->enum('status', [
                'pending', 'queued', 'processing',
                'success', 'failed', 'refunded', 'partial',
            ])->default('pending');

            // ── Operator API fields ───────────────────────────────────
            $table->string('operator_ref', 100)->nullable()
                  ->comment('Transaction ID from operator — used for callbacks');
            $table->string('api_ref', 100)->nullable()
                  ->comment('Our outgoing request UUID sent to operator');
            $table->json('operator_response')->nullable()
                  ->comment('Full raw response — retained for disputes');
            $table->text('failure_reason')->nullable();

            // ── Retry tracking ────────────────────────────────────────
            $table->unsignedTinyInteger('retry_count')->default(0);
            $table->timestamp('next_retry_at')->nullable();

            // ── Audit ─────────────────────────────────────────────────
            $table->timestamp('processed_at')->nullable()
                  ->comment('Set when status reaches terminal state');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();

            // created_at NOT NULL — required for partition pruning
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            $table->softDeletes();

            // ── Foreign Keys ──────────────────────────────────────────
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('buyer_id')->references('id')->on('buyers')->nullOnDelete();
            $table->foreign('operator_id')->references('id')->on('operators')->nullOnDelete();
            $table->foreign('operator_route_id')->references('id')->on('operator_routes')->nullOnDelete();

            // ── Indexes (see docblock above for query pattern mapping) ──

            // User dashboard: listing + status filter
            $table->index(['user_id', 'status', 'created_at'],      'idx_rt_user_status_date');

            // Mobile-number search (admin + user)
            $table->index(['mobile', 'created_at'],                  'idx_rt_mobile_date');

            // Operator-level reporting: volume, success rate by operator
            $table->index(['operator_code', 'status', 'created_at'], 'idx_rt_operator_status_date');

            // Admin status monitoring dashboard
            $table->index(['status', 'created_at'],                  'idx_rt_status_date');

            // Financial reconciliation (settled transactions)
            $table->index(['processed_at', 'status'],                'idx_rt_processed_status');

            // Operator callback lookup (most time-sensitive query)
            $table->index('operator_ref',                            'idx_rt_operator_ref');

            // Retry queue: find retryable transactions
            $table->index(['status', 'retry_count', 'next_retry_at'],'idx_rt_retry');

            $table->index('deleted_at',                              'idx_rt_deleted_at');
        });

        // ── Monthly RANGE partitioning (production / staging only) ──────────
        // Partition pruning on created_at reduces I/O by 11/12 for any
        // single-month query — critical at 1M+ rows/day.
        if (app()->environment('production', 'staging')) {
            \Illuminate\Support\Facades\DB::statement("
                ALTER TABLE recharge_transactions
                PARTITION BY RANGE (UNIX_TIMESTAMP(created_at)) (
                    PARTITION rt_2026_01 VALUES LESS THAN (UNIX_TIMESTAMP('2026-02-01')),
                    PARTITION rt_2026_02 VALUES LESS THAN (UNIX_TIMESTAMP('2026-03-01')),
                    PARTITION rt_2026_03 VALUES LESS THAN (UNIX_TIMESTAMP('2026-04-01')),
                    PARTITION rt_2026_04 VALUES LESS THAN (UNIX_TIMESTAMP('2026-05-01')),
                    PARTITION rt_2026_05 VALUES LESS THAN (UNIX_TIMESTAMP('2026-06-01')),
                    PARTITION rt_2026_06 VALUES LESS THAN (UNIX_TIMESTAMP('2026-07-01')),
                    PARTITION rt_2026_07 VALUES LESS THAN (UNIX_TIMESTAMP('2026-08-01')),
                    PARTITION rt_2026_08 VALUES LESS THAN (UNIX_TIMESTAMP('2026-09-01')),
                    PARTITION rt_2026_09 VALUES LESS THAN (UNIX_TIMESTAMP('2026-10-01')),
                    PARTITION rt_2026_10 VALUES LESS THAN (UNIX_TIMESTAMP('2026-11-01')),
                    PARTITION rt_2026_11 VALUES LESS THAN (UNIX_TIMESTAMP('2026-12-01')),
                    PARTITION rt_2026_12 VALUES LESS THAN (UNIX_TIMESTAMP('2027-01-01')),
                    PARTITION rt_future   VALUES LESS THAN MAXVALUE
                )
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recharge_transactions');
    }
};
