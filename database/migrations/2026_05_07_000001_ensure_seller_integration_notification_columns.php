<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seller_integration_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('seller_integration_requests', 'api_name')) {
                $table->string('api_name', 150)->nullable()->after('user_id');
            }

            if (! Schema::hasColumn('seller_integration_requests', 'low_balance_notification')) {
                $table->boolean('low_balance_notification')->default(false)->after('api_name');
            }

            if (! Schema::hasColumn('seller_integration_requests', 'low_balance_limit')) {
                $table->decimal('low_balance_limit', 12, 2)->nullable()->after('low_balance_notification');
            }

            if (! Schema::hasColumn('seller_integration_requests', 'notification_types')) {
                $table->json('notification_types')->nullable()->after('low_balance_limit');
            }
        });
    }

    public function down(): void
    {
        Schema::table('seller_integration_requests', function (Blueprint $table) {
            foreach (['notification_types', 'low_balance_limit', 'low_balance_notification', 'api_name'] as $column) {
                if (Schema::hasColumn('seller_integration_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
