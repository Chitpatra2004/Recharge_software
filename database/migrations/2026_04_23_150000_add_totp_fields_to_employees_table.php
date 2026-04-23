<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('totp_secret', 255)->nullable()->after('two_factor_enabled');
            $table->boolean('totp_enabled')->default(false)->after('totp_secret');
            $table->enum('two_factor_method', ['none', 'otp', 'totp'])->default('none')->after('totp_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['totp_secret', 'totp_enabled', 'two_factor_method']);
        });
    }
};
