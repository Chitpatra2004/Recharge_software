<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->string('bank_name', 100)->nullable()->after('description');
            $table->string('rrn', 100)->nullable()->after('bank_name');
            $table->string('remark', 255)->nullable()->after('rrn');
            $table->text('admin_remark')->nullable()->after('remark');
        });
    }

    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'rrn', 'remark', 'admin_remark']);
        });
    }
};
