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
        Schema::create('bbps_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('biller_category', 50); // electricity|water|gas|dth|broadband|landline|insurance|loan|fastag
            $table->string('biller_name', 100);
            $table->string('consumer_number', 100);

            $table->decimal('amount', 10, 2);
            $table->decimal('balance_before', 12, 2)->nullable();
            $table->decimal('balance_after', 12, 2)->nullable();

            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->string('txn_id', 100)->nullable()->unique();
            $table->string('biller_ref_id', 100)->nullable();
            $table->string('failure_reason', 500)->nullable();
            $table->json('bill_details')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status'], 'idx_bbps_user_status');
            $table->index('biller_category',      'idx_bbps_category');
            $table->index('created_at',            'idx_bbps_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bbps_transactions');
    }
};
