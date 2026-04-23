<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seller_integration_requests', function (Blueprint $table) {
            $table->enum('api_status', ['enabled', 'disabled'])->default('disabled')->after('status');
            $table->enum('admin_status', ['enabled', 'disabled'])->default('disabled')->after('api_status');
            $table->index(['user_id', 'api_status'], 'sir_user_api_status_idx');
            $table->index(['user_id', 'admin_status'], 'sir_user_admin_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('seller_integration_requests', function (Blueprint $table) {
            $table->dropIndex('sir_user_api_status_idx');
            $table->dropIndex('sir_user_admin_status_idx');
            $table->dropColumn(['api_status', 'admin_status']);
        });
    }
};
