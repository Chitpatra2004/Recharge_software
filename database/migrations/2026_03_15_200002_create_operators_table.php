<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ╔══════════════════════════════════════════════════════════════════╗
 * ║  TABLE: operators                                                 ║
 * ║                                                                   ║
 * ║  Master catalog of telecom/utility operators (AIRTEL, JIO …).   ║
 * ║  operator_routes references this table for actual API routing.   ║
 * ║                                                                   ║
 * ║  Kept intentionally lean — UI-facing data only.                  ║
 * ║  API credentials live in operator_routes (encrypted JSON).       ║
 * ║                                                                   ║
 * ║  INDEXING STRATEGY:                                              ║
 * ║  - code: UNIQUE — used everywhere as the business key            ║
 * ║  - (category, is_active): composite — operator list API          ║
 * ╚══════════════════════════════════════════════════════════════════╝
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operators', function (Blueprint $table) {
            $table->id();

            $table->string('name', 100);
            $table->string('code', 30)->unique()
                  ->comment('Business key: AIRTEL, JIO, BSNL, VI, TATA_SKY …');

            $table->enum('category', [
                'mobile', 'dth', 'broadband',
                'electricity', 'gas', 'water', 'insurance',
            ])->default('mobile');

            $table->string('logo_url', 500)->nullable();

            // Feature flags
            $table->boolean('prepaid_enabled')->default(true);
            $table->boolean('postpaid_enabled')->default(false);
            $table->boolean('is_active')->default(true);

            // Default amount guardrails (overridden at route level)
            $table->decimal('min_amount', 10, 2)->default(1.00);
            $table->decimal('max_amount', 10, 2)->default(10000.00);

            // Operator-level commission override (null = use user rate)
            $table->decimal('commission_rate', 5, 2)->nullable();

            // TRAI/regulatory info
            $table->string('country_code', 5)->default('IN');
            $table->json('circles')->nullable()
                  ->comment('Supported telecom circles/states');

            $table->timestamps();
            $table->softDeletes();

            // ── Indexes ──────────────────────────────────────────────────
            $table->index(['category', 'is_active'], 'idx_operators_cat_active');
            $table->index('deleted_at',              'idx_operators_deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operators');
    }
};
