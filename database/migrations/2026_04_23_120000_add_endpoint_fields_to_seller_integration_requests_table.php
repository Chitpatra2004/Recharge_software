<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seller_integration_requests', function (Blueprint $table) {
            $table->string('status_check_url')->nullable()->after('callback_url');
            $table->string('dispute_url')->nullable()->after('status_check_url');
            $table->text('allowed_ips')->nullable()->after('site_password_hint');
        });
    }

    public function down(): void
    {
        Schema::table('seller_integration_requests', function (Blueprint $table) {
            $table->dropColumn(['status_check_url', 'dispute_url', 'allowed_ips']);
        });
    }
};
