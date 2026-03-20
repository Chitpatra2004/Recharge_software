<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ╔══════════════════════════════════════════════════════════════════╗
 * ║  TABLE: api_request_logs                                          ║
 * ║                                                                   ║
 * ║  Append-only log of every inbound API request.                   ║
 * ║  Kept separate from activity_logs to avoid polluting the audit   ║
 * ║  trail with high-volume HTTP noise.                              ║
 * ║                                                                   ║
 * ║  VOLUME: equals total API requests (could be millions/day)       ║
 * ║  RETENTION: typically 30–90 days, then archive/delete            ║
 * ║                                                                   ║
 * ║  INDEXING STRATEGY:                                              ║
 * ║  - (api_key_id, created_at): per-key usage reports              ║
 * ║  - (user_id,    created_at): per-user usage reports             ║
 * ║  - (status_code, created_at): error-rate monitoring             ║
 * ║  - (ip_address,  created_at): abuse/security queries            ║
 * ╚══════════════════════════════════════════════════════════════════╝
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_request_logs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('api_key_id')->nullable()
                  ->comment('NULL when authenticated via Sanctum token');
            $table->unsignedBigInteger('user_id')->nullable();

            // Request details
            $table->string('method', 10);
            $table->string('path', 500);
            $table->string('query_string', 1000)->nullable();

            // Response details
            $table->unsignedSmallInteger('status_code');
            $table->unsignedInteger('response_time_ms')->default(0);
            $table->unsignedInteger('request_size')->default(0)
                  ->comment('Bytes from Content-Length header');
            $table->unsignedInteger('response_size')->default(0)
                  ->comment('Bytes in response body');

            // Client context
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();

            // Error capture (only for 4xx / 5xx responses)
            $table->string('error_message', 500)->nullable();

            // Append-only — no updated_at
            $table->timestamp('created_at')->useCurrent();

            // ── FK constraints ────────────────────────────────────────────
            $table->foreign('api_key_id')->references('id')->on('api_keys')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();

            // ── Indexes ───────────────────────────────────────────────────
            $table->index(['api_key_id', 'created_at'], 'idx_arl_key_date');
            $table->index(['user_id',    'created_at'], 'idx_arl_user_date');
            $table->index(['status_code','created_at'], 'idx_arl_status_date');
            $table->index(['ip_address', 'created_at'], 'idx_arl_ip_date');
            $table->index('created_at',                 'idx_arl_created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_request_logs');
    }
};
