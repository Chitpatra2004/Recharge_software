<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Extend the role ENUM to include 'buyer'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','retailer','distributor','api_user','buyer') NOT NULL DEFAULT 'retailer'");
    }

    public function down(): void
    {
        // Remove 'buyer' — any existing buyer rows would need to be handled first
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','retailer','distributor','api_user') NOT NULL DEFAULT 'retailer'");
    }
};
