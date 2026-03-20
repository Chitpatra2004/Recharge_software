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
         * ║  TABLE: wallets                                                   ║
         * ║                                                                   ║
         * ║  One wallet per user (enforced by UNIQUE on user_id).            ║
         * ║  balance is always accessed via SELECT FOR UPDATE (pessimistic   ║
         * ║  lock) inside a DB transaction to prevent race conditions.       ║
         * ║                                                                   ║
         * ║  reserved_balance: funds locked for queued/processing recharges  ║
         * ║  available_balance = balance - reserved_balance                  ║
         * ║                                                                   ║
         * ║  total_recharged / total_topup: running aggregates to avoid      ║
         * ║  expensive SUM() on wallet_transactions at report time.          ║
         * ║                                                                   ║
         * ║  INDEXING STRATEGY:                                              ║
         * ║  - user_id: UNIQUE — primary lookup, no extra index needed       ║
         * ║  - status: used in frozen-wallet checks                          ║
         * ╚══════════════════════════════════════════════════════════════════╝
         */
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->unique()   // 1 wallet per user, enforced at DB level
                  ->constrained()
                  ->cascadeOnDelete();

            // Live balance fields
            $table->decimal('balance', 14, 2)->default(0.00)
                  ->comment('Actual current balance');
            $table->decimal('reserved_balance', 14, 2)->default(0.00)
                  ->comment('Locked for in-flight recharges (queued/processing)');
            $table->decimal('credit_limit', 14, 2)->default(0.00)
                  ->comment('Overdraft allowed; 0 = prepaid only');

            // Running totals for fast reporting (avoids SUM scans)
            $table->decimal('total_recharged', 16, 2)->default(0.00)
                  ->comment('Lifetime total of recharge debits');
            $table->decimal('total_topup', 16, 2)->default(0.00)
                  ->comment('Lifetime total of credits/top-ups');

            // Wallet-level limits
            $table->decimal('daily_debit_limit', 14, 2)->nullable()
                  ->comment('Max debit per calendar day; null = unlimited');
            $table->decimal('daily_debit_used', 14, 2)->default(0.00)
                  ->comment('Resets to 0 at midnight via scheduled job');
            $table->date('daily_limit_reset_date')->nullable();

            $table->enum('status', ['active', 'frozen'])->default('active');

            $table->timestamps();

            // ── Indexes ──────────────────────────────────────────────────
            // user_id UNIQUE covers single-record lookups
            // status index used for frozen-wallet admin queries
            $table->index('status', 'idx_wallets_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
