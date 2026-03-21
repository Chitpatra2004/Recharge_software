<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('document_path', 500)->nullable()->after('ip_whitelist')
                  ->comment('Uploaded ID proof (Aadhaar/PAN)');
            $table->string('totp_secret', 255)->nullable()->after('document_path')
                  ->comment('Base32 TOTP secret for Google Authenticator');
            $table->boolean('totp_enabled')->default(false)->after('totp_secret');
            $table->enum('two_factor_method', ['none', 'otp', 'totp'])
                  ->default('none')->after('totp_enabled')
                  ->comment('none=disabled, otp=SMS OTP, totp=Authenticator App');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['document_path', 'totp_secret', 'totp_enabled', 'two_factor_method']);
        });
    }
};
