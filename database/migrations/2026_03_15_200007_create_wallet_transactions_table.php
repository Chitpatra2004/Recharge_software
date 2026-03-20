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
         * ║  TABLE: wallet_transactions                                       ║
         * ║                                                                   ║
         * ║  VOLUME ESTIMATE: ~1–3 records per recharge transaction          ║
         * ║  = 1M–3M rows/day. Partitioned by month.                        ║
         * ║                                                                   ║
         * ║  PARTITION STRATEGY:                                             ║
         * ║  RANGE on UNIX_TIMESTAMP(created_at) — one partition per month.  ║
         * ║  Old partitions can be archived/dropped without table lock.      ║
         * ║  NOTE: Apply partition DDL after migration via raw SQL below.    ║
         * ║                                                                   ║
         * ║  INDEXING STRATEGY (all composite — leftmost prefix rule):       ║
         * ║  idx_wallet_type_date  → wallet statement, type filter           ║
         * ║  idx_user_date         → user-level ledger, date range           ║
         * ║  idx_reference         → polymorphic JOIN to source record       ║
         * ║  txn_id UNIQUE         → idempotency check                       ║
         * ║  idx_status_date       → reconciliation jobs                     ║
         * ╚══════════════════════════════════════════════════════════════════╝
         */
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();

            // Core relationships
            $table->unsignedBigInteger('wallet_id');
            $table->unsignedBigInteger('user_id');

            // Idempotency — unique per transaction event
            $table->string('txn_id', 64)->unique()
                  ->comment('UUID — prevents double-entry on retries');

            // Transaction details
            $table->enum('type', ['credit', 'debit', 'reserve', 'release', 'reversal']);
            $table->decimal('amount', 14, 2);
            $table->decimal('balance_before', 14, 2);
            $table->decimal('balance_after', 14, 2);
            $table->string('description', 500)->nullable();

            // Polymorphic reference (RechargeTransaction, TopupOrder, etc.)
            $table->string('reference_type', 100)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->enum('status', ['pending', 'completed', 'failed', 'reversed'])
                  ->default('completed');
            $table->string('ip_address', 45)->nullable();

            // created_at must be NOT NULL for partitioning
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();

            // FK constraints (not enforced on partitioned tables in MySQL 5.7,
            // but kept for documentation and future migration to MySQL 8.x)
            $table->foreign('wallet_id')->references('id')->on('wallets')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            // ── Indexes ──────────────────────────────────────────────────

            // Wallet statement page: WHERE wallet_id = ? AND type IN (…) ORDER BY created_at DESC
            $table->index(['wallet_id', 'type', 'created_at'], 'idx_wt_wallet_type_date');

            // User ledger / reporting: WHERE user_id = ? AND created_at BETWEEN …
            $table->index(['user_id', 'created_at'],           'idx_wt_user_date');

            // Reverse lookup from recharge → wallet transaction
            $table->index(['reference_type', 'reference_id'],  'idx_wt_reference');

            // Reconciliation / failed transaction cleanup jobs
            $table->index(['status', 'created_at'],            'idx_wt_status_date');
        });

        // ── Apply RANGE partitioning by month (production use) ───────────
        // Laravel Blueprint does not support PARTITION BY natively.
        // These ALTER TABLE statements add 12 monthly partitions for the
        // current year plus one catch-all MAXVALUE partition.
        // In CI/test environments this is safe to skip.
        if (app()->environment('production', 'staging')) {
            \Illuminate\Support\Facades\DB::statement("
                ALTER TABLE wallet_transactions
                PARTITION BY RANGE (UNIX_TIMESTAMP(created_at)) (
                    PARTITION wt_2026_01 VALUES LESS THAN (UNIX_TIMESTAMP('2026-02-01')),
                    PARTITION wt_2026_02 VALUES LESS THAN (UNIX_TIMESTAMP('2026-03-01')),
                    PARTITION wt_2026_03 VALUES LESS THAN (UNIX_TIMESTAMP('2026-04-01')),
                    PARTITION wt_2026_04 VALUES LESS THAN (UNIX_TIMESTAMP('2026-05-01')),
                    PARTITION wt_2026_05 VALUES LESS THAN (UNIX_TIMESTAMP('2026-06-01')),
                    PARTITION wt_2026_06 VALUES LESS THAN (UNIX_TIMESTAMP('2026-07-01')),
                    PARTITION wt_2026_07 VALUES LESS THAN (UNIX_TIMESTAMP('2026-08-01')),
                    PARTITION wt_2026_08 VALUES LESS THAN (UNIX_TIMESTAMP('2026-09-01')),
                    PARTITION wt_2026_09 VALUES LESS THAN (UNIX_TIMESTAMP('2026-10-01')),
                    PARTITION wt_2026_10 VALUES LESS THAN (UNIX_TIMESTAMP('2026-11-01')),
                    PARTITION wt_2026_11 VALUES LESS THAN (UNIX_TIMESTAMP('2026-12-01')),
                    PARTITION wt_2026_12 VALUES LESS THAN (UNIX_TIMESTAMP('2027-01-01')),
                    PARTITION wt_future   VALUES LESS THAN MAXVALUE
                )
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
