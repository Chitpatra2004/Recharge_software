<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seller_integration_requests', function (Blueprint $table) {
            $table->json('recharge_api')->nullable()->after('allowed_ips');
            $table->json('status_api')->nullable()->after('recharge_api');
            $table->json('balance_api')->nullable()->after('status_api');
            $table->json('dispute_api')->nullable()->after('balance_api');
            $table->json('callback_config')->nullable()->after('dispute_api');
            $table->json('op_code_map')->nullable()->after('callback_config');
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
