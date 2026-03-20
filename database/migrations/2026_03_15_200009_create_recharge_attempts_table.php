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
         * ║  TABLE: recharge_attempts                                         ║
         * ║                                                                   ║
         * ║  Immutable log of every outbound API call to an operator.        ║
         * ║  One transaction can have up to max_retries × routes attempts.   ║
         * ║                                                                   ║
         * ║  Used for: debugging, operator SLA reports, success rate calc.   ║
         * ║                                                                   ║
         * ║  INDEXING STRATEGY:                                              ║
         * ║  - (txn_id, attempt_number): primary lookup when debugging       ║
         * ║  - (operator_route_id, status, created_at): route SLA reports   ║
         * ║  - duration_ms: slow-API detection queries                       ║
         * ╚══════════════════════════════════════════════════════════════════╝
         */
        Schema::create('recharge_attempts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('recharge_transaction_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('operator_route_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            $table->unsignedTinyInteger('attempt_number')
                  ->comment('1-based attempt counter per transaction');
            $table->enum('status', ['success', 'failed', 'timeout', 'error']);

            // Full request/response logging for operator disputes
            $table->string('request_url', 500)->nullable();
            $table->json('request_payload')->nullable()
                  ->comment('Outgoing payload (sanitized — no raw credentials)');
            $table->json('response_payload')->nullable()
                  ->comment('Raw operator response body');
            $table->unsignedSmallInteger('response_code')->nullable()
                  ->comment('HTTP status code');
            $table->unsignedInteger('duration_ms')->nullable()
                  ->comment('Wall-clock API round-trip in milliseconds');
            $table->text('error_message')->nullable();

            $table->timestamps();

            // ── Indexes ──────────────────────────────────────────────────
            $table->index(
                ['recharge_transaction_id', 'attempt_number'],
                'idx_attempts_txn_attempt'
            );
            $table->index(
                ['operator_route_id', 'status', 'created_at'],
                'idx_attempts_route_status_date'
            );
            // Slow-call detection: WHERE duration_ms > 5000
            $table->index('duration_ms', 'idx_attempts_duration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recharge_attempts');
    }
};
