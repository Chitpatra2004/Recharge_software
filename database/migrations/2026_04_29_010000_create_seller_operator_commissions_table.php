<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_operator_commissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('operator_id')->nullable();
            $table->string('operator_code', 30);
            $table->decimal('commission', 8, 3)->default(0);
            $table->enum('commission_type', ['percentage', 'flat'])->default('percentage');
            $table->string('api1', 100)->nullable();
            $table->unsignedInteger('limit_txn')->default(0);
            $table->decimal('limit_amount', 12, 2)->default(0);
            $table->string('blocked_amounts', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('operator_id')->references('id')->on('operators')->nullOnDelete();
            $table->unique(['user_id', 'operator_code'], 'uq_seller_operator_commission');
            $table->index(['user_id', 'is_active'], 'idx_soc_user_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_operator_commissions');
    }
};
