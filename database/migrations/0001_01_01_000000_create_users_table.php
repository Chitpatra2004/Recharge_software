<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ╔══════════════════════════════════════════════════════════════════╗
 * ║  TABLE: users                                                     ║
 * ║                                                                   ║
 * ║  Stores API consumers: retailers, distributors, and API users.   ║
 * ║  Employees are in a separate table (employees) with their own    ║
 * ║  auth to enforce separation of concerns.                         ║
 * ║                                                                   ║
 * ║  INDEXING STRATEGY:                                              ║
 * ║  - email / mobile: UNIQUE — used for login lookup                ║
 * ║  - (role, status): composite — admin dashboards filter by both   ║
 * ║  - deleted_at: included in soft-delete scopes                    ║
 * ╚══════════════════════════════════════════════════════════════════╝
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // Identity
            $table->string('name', 100);
            $table->string('email', 191)->unique();
            $table->string('mobile', 15)->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamp('email_verified_at')->nullable();

            // Access control
            $table->enum('role', ['admin', 'retailer', 'distributor', 'api_user'])
                  ->default('retailer');
            $table->enum('status', ['active', 'inactive', 'suspended'])
                  ->default('active');

            // Financial
            $table->decimal('commission_rate', 5, 2)->default(0.00)
                  ->comment('Commission % applied on each recharge');

            // API access
            $table->string('api_key', 64)->nullable()->unique()
                  ->comment('SHA-256 hash of raw API key; raw key stored in api_keys table');
            $table->string('ip_whitelist', 500)->nullable()
                  ->comment('Comma-separated allowed IPs for API access');

            // Metadata
            $table->string('referral_code', 20)->nullable()->unique();
            $table->foreignId('referred_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // ── Indexes ──────────────────────────────────────────────────
            // Composite: role + status used by admin list/filter queries
            $table->index(['role', 'status'], 'idx_users_role_status');
            // Soft-delete scope performance
            $table->index('deleted_at', 'idx_users_deleted_at');
        });

        // ── password_reset_tokens ────────────────────────────────────────
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email', 191)->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // ── sessions ─────────────────────────────────────────────────────
        // SESSION_DRIVER=redis in production; this table is the fallback.
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
