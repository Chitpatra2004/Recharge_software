<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_request_logs', function (Blueprint $table) {
            $table->string('reference_id', 191)->nullable()->after('query_string');
            $table->text('request_payload')->nullable()->after('response_size');

            $table->index(['reference_id', 'created_at'], 'idx_arl_reference_date');
        });
    }

    public function down(): void
    {
        Schema::table('api_request_logs', function (Blueprint $table) {
            $table->dropIndex('idx_arl_reference_date');
            $table->dropColumn(['reference_id', 'request_payload']);
        });
    }
};
