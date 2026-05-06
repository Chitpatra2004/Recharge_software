<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add log_type + log_label to recharge_attempts
        Schema::table('recharge_attempts', function (Blueprint $table) {
            $table->enum('log_type', ['recharge', 'status_check', 'callback'])
                  ->default('recharge')
                  ->after('id');
            $table->string('log_label', 120)->nullable()->after('log_type');
        });

        // Track which route was first used on a transaction (for reroute logic)
        Schema::table('recharge_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('initial_route_id')
                  ->nullable()
                  ->after('operator_route_id');
        });
    }

    public function down(): void
    {
        Schema::table('recharge_attempts', function (Blueprint $table) {
            $table->dropColumn(['log_type', 'log_label']);
        });
        Schema::table('recharge_transactions', function (Blueprint $table) {
            $table->dropColumn('initial_route_id');
        });
    }
};
