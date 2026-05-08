<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_payment_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('user_payment_requests', 'processed_by_employee_id')) {
                $table->unsignedBigInteger('processed_by_employee_id')->nullable()->after('processed_at');
                $table->index('processed_by_employee_id', 'idx_upr_processed_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_payment_requests', function (Blueprint $table) {
            if (Schema::hasColumn('user_payment_requests', 'processed_by_employee_id')) {
                $table->dropIndex('idx_upr_processed_by');
                $table->dropColumn('processed_by_employee_id');
            }
        });
    }
};
