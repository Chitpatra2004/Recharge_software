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
         * ║  TABLE: operator_routes                                           ║
         * ║                                                                   ║
         * ║  One operator (AIRTEL) can have multiple routes (ProviderA,      ║
         * ║  ProviderB) per recharge type. Routes are tried in priority      ║
         * ║  order. success_rate (0–100) is maintained by the system and     ║
         * ║  affects automatic route selection.                              ║
         * ║                                                                   ║
         * ║  api_config stores credentials as Laravel encrypted JSON cast.   ║
         * ║                                                                   ║
         * ║  INDEXING STRATEGY:                                              ║
         * ║  - (operator_id, recharge_type, is_active, priority):            ║
         * ║    The exact query pattern used by RechargeService to get        ║
         * ║    active routes sorted by priority. This is the hot path.       ║
         * ║  - (operator_code, recharge_type): denormalized for fast         ║
         * ║    lookups without a JOIN to operators table.                    ║
         * ╚══════════════════════════════════════════════════════════════════╝
         */
        Schema::create('operator_routes', function (Blueprint $table) {
            $table->id();

            // FK to operators master table
            $table->foreignId('operator_id')
                  ->constrained('operators')
                  ->cascadeOnDelete();

            $table->string('name', 150);

            // Denormalized for join-free query in hot path
            $table->string('operator_code', 30)
                  ->comment('Denormalized from operators.code');
            $table->enum('recharge_type', ['prepaid', 'postpaid', 'dth', 'broadband'])
                  ->default('prepaid');

            // API integration
            $table->string('api_provider', 50)
                  ->comment('Third-party provider name: ProviderA, ProviderB …');
            $table->string('api_endpoint', 500);
            $table->text('api_config')->nullable()
                  ->comment('Laravel encrypted:array cast — stored as encrypted text, not raw JSON');

            // Routing control
            $table->unsignedTinyInteger('priority')->default(1)
                  ->comment('1 = highest priority; tried first');
            $table->unsignedTinyInteger('success_rate')->default(100)
                  ->comment('Rolling 0–100; decremented on failure, incremented on success');

            // Per-request configuration
            $table->unsignedSmallInteger('timeout_seconds')->default(30);
            $table->unsignedTinyInteger('max_retries')->default(3);

            // Amount guardrails for this specific route
            $table->decimal('min_amount', 10, 2)->default(1.00);
            $table->decimal('max_amount', 10, 2)->default(10000.00);

            // Optional throughput limits
            $table->decimal('daily_limit', 14, 2)->nullable()
                  ->comment('Max total amount per day via this route; null = unlimited');
            $table->decimal('monthly_limit', 16, 2)->nullable();
            $table->decimal('daily_used', 14, 2)->default(0.00)
                  ->comment('Reset nightly by scheduler');

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // ── Indexes ──────────────────────────────────────────────────

            // PRIMARY hot-path: find active routes for operator+type, ordered by priority
            // Covers: WHERE operator_id=? AND recharge_type=? AND is_active=1 ORDER BY priority
            $table->index(
                ['operator_id', 'recharge_type', 'is_active', 'priority'],
                'idx_routes_operator_type_active_priority'
            );

            // Secondary path using denormalized code (no JOIN needed)
            $table->index(
                ['operator_code', 'recharge_type', 'is_active'],
                'idx_routes_code_type_active'
            );

            $table->index('deleted_at', 'idx_routes_deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operator_routes');
    }
};
