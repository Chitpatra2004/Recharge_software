<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->date('dob')->nullable()->after('mobile');
            $table->string('city', 100)->nullable()->after('dob');
            $table->string('state', 100)->nullable()->after('city');
            $table->string('pan', 10)->nullable()->after('state');
            $table->boolean('two_factor_enabled')->default(false)->after('pan');
            $table->json('backup_codes')->nullable()->after('two_factor_enabled');
            $table->json('preferences')->nullable()->after('backup_codes');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['dob', 'city', 'state', 'pan', 'two_factor_enabled', 'backup_codes', 'preferences']);
        });
    }
};
