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
         * ║  TABLE: buyers                                                    ║
         * ║                                                                   ║
         * ║  End-customers / mobile subscribers belonging to a retailer.     ║
         * ║  Acts as a contact book — retailers save repeat customers.       ║
         * ║                                                                   ║
         * ║  INDEXING STRATEGY:                                              ║
         * ║  - (user_id, mobile): composite UNIQUE — same mobile can exist   ║
         * ║    under different retailers but not twice under one retailer     ║
         * ║  - mobile: standalone — admin search across all buyers           ║
         * ╚══════════════════════════════════════════════════════════════════╝
         */
        Schema::create('buyers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('operator_id')->nullable()->constrained('operators')->nullOnDelete();

            $table->string('name', 100);
            $table->string('mobile', 15);
            $table->string('email', 150)->nullable();
            $table->string('circle', 50)->nullable()
                  ->comment('Telecom circle/state: UP-East, Mumbai, Karnataka …');
            $table->string('operator_code', 30)->nullable()
                  ->comment('Denormalized from operators.code for fast filtering');
            $table->enum('status', ['active', 'blocked'])->default('active');

            $table->timestamps();
            $table->softDeletes();

            // ── Indexes ──────────────────────────────────────────────────
            // One mobile per retailer (no duplicates in same retailer's book)
            $table->unique(['user_id', 'mobile'], 'uq_buyer_user_mobile');
            // Admin search by mobile across all retailers
            $table->index('mobile',    'idx_buyers_mobile');
            $table->index('status',    'idx_buyers_status');
            $table->index('deleted_at','idx_buyers_deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buyers');
    }
};
