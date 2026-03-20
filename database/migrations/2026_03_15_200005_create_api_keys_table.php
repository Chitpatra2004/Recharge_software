<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ╔══════════════════════════════════════════════════════════════════╗
 * ║  TABLE: api_keys                                                  ║
 * ║                                                                   ║
 * ║  Dedicated machine-to-machine key management.                    ║
 * ║  Separate from Sanctum tokens — supports:                        ║
 * ║   • Multiple named keys per user (dev, prod, staging)            ║
 * ║   • Per-key IP whitelist                                         ║
 * ║   • Per-key permission scopes                                    ║
 * ║   • Expiry dates                                                 ║
 * ║   • Usage tracking (last_used_at, request_count)                ║
 * ║                                                                   ║
 * ║  SECURITY: Raw key is NEVER stored. Only SHA-256 hash is kept.  ║
 * ║  Raw key is returned once at creation time.                      ║
 * ║                                                                   ║
 * ║  INDEXING STRATEGY:                                              ║
 * ║  - key_hash: UNIQUE — every inbound request hashes the raw key  ║
 * ║    and lookups by hash (single B-tree scan).                     ║
 * ║  - (user_id, is_active): listing keys for a user                ║
 * ║  - (expires_at, is_active): cleanup / expiry cron job           ║
 * ╚══════════════════════════════════════════════════════════════════╝
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Human-readable label (e.g. "Production Server", "Mobile App")
            $table->string('name', 100);

            // First 10 chars of the raw key shown in UI for identification
            // e.g. "rk_live_Ab" — safe to store, not the full key
            $table->string('key_prefix', 12)
                  ->comment('First 12 chars of raw key — shown in UI for identification');

            // SHA-256 hash of the full raw key — used for constant-time lookup
            $table->string('key_hash', 64)->unique();

            // Granular permission scopes (JSON array)
            // e.g. ["recharge:write", "wallet:read", "transactions:read"]
            $table->json('scopes')->nullable()
                  ->comment('Permission scopes: ["recharge:write","wallet:read"]');

            // IP restriction (JSON array of IPs/CIDRs)
            $table->json('ip_whitelist')->nullable()
                  ->comment('Allowed IPs: ["1.2.3.4","10.0.0.0/8"]');

            // Usage tracking
            $table->timestamp('last_used_at')->nullable();
            $table->string('last_used_ip', 45)->nullable();
            $table->unsignedBigInteger('request_count')->default(0)
                  ->comment('Lifetime request counter');

            // Expiry
            $table->timestamp('expires_at')->nullable()
                  ->comment('NULL = never expires');

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // ── Indexes ──────────────────────────────────────────────────
            // key_hash UNIQUE covers the hot path (every inbound API request)
            $table->index(['user_id', 'is_active'],   'idx_apikeys_user_active');
            $table->index(['expires_at', 'is_active'],'idx_apikeys_expiry');
            $table->index('deleted_at',               'idx_apikeys_deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};
