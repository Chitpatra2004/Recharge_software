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
        Schema::create('user_payment_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->decimal('amount', 10, 2);
            $table->string('payment_mode', 30)->default('upi'); // upi|bank_transfer|neft|rtgs|cheque
            $table->string('reference_number', 100);
            $table->string('upi_id', 100)->nullable();
            $table->date('payment_date')->nullable();
            $table->text('notes')->nullable();
            $table->string('proof_image')->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status'], 'idx_upr_user_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_payment_requests');
    }
};
