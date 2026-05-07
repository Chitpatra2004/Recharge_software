<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seller_integration_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('seller_integration_requests', 'recharge_api')) {
                $table->json('recharge_api')->nullable()->after('allowed_ips');
            }

            if (! Schema::hasColumn('seller_integration_requests', 'status_api')) {
                $table->json('status_api')->nullable()->after('recharge_api');
            }

            if (! Schema::hasColumn('seller_integration_requests', 'balance_api')) {
                $table->json('balance_api')->nullable()->after('status_api');
            }

            if (! Schema::hasColumn('seller_integration_requests', 'dispute_api')) {
                $table->json('dispute_api')->nullable()->after('balance_api');
            }

            if (! Schema::hasColumn('seller_integration_requests', 'callback_config')) {
                $table->json('callback_config')->nullable()->after('dispute_api');
            }

            if (! Schema::hasColumn('seller_integration_requests', 'op_code_map')) {
                $table->json('op_code_map')->nullable()->after('callback_config');
            }
        });
    }

    public function down(): void
    {
        Schema::table('seller_integration_requests', function (Blueprint $table) {
            $table->dropColumn([
                'recharge_api',
                'status_api',
                'balance_api',
                'dispute_api',
                'callback_config',
                'op_code_map',
            ]);
        });
    }
};
