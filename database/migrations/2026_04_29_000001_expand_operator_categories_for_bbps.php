<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE operators MODIFY category ENUM('mobile','dth','broadband','electricity','gas','water','insurance','landline','loan','fastag','credit_card','municipal_tax','education','subscription') NOT NULL DEFAULT 'mobile'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE operators MODIFY category ENUM('mobile','dth','broadband','electricity','gas','water','insurance') NOT NULL DEFAULT 'mobile'");
    }
};
