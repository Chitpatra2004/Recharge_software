<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seller_integration_requests', function (Blueprint $table) {
            $table->string('api_name', 150)->nullable()->after('user_id');
            $table->boolean('low_balance_notification')->default(false)->after('api_name');
            $table->decimal('low_balance_limit', 12, 2)->nullable()->after('low_balance_notification');
            $table->json('notification_types')->nullable()->after('low_balance_limit');
        });
    }

    public function down(): void
    {
        Schema::table('seller_integration_requests', function (Blueprint $table) {
            $table->dropColumn(['api_name', 'low_balance_notification', 'low_balance_limit', 'notification_types']);
        });
    }
};
