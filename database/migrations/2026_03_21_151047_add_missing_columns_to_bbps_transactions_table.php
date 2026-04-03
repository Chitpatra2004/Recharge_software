<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bbps_transactions', function (Blueprint $table) {
            // Idempotency key for duplicate-payment prevention
            $table->string('idempotency_key', 128)->nullable()->unique()->after('user_id');

            // Biller identifier (e.g. 'TPDDL', 'IGL') — separate from biller_name
            $table->string('biller_id', 30)->nullable()->after('idempotency_key');

            // Timestamps for finalisation and retry scheduling
            $table->timestamp('processed_at')->nullable()->after('bill_details');
            $table->timestamp('next_retry_at')->nullable()->after('processed_at');
        });

        // Extend status enum to include 'processing' (intermediate state during API call)
        \DB::statement("ALTER TABLE bbps_transactions MODIFY COLUMN status ENUM('pending','processing','success','failed') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        \DB::statement("ALTER TABLE bbps_transactions MODIFY COLUMN status ENUM('pending','success','failed') NOT NULL DEFAULT 'pending'");

        Schema::table('bbps_transactions', function (Blueprint $table) {
            $table->dropColumn(['idempotency_key', 'biller_id', 'processed_at', 'next_retry_at']);
        });
    }
};
