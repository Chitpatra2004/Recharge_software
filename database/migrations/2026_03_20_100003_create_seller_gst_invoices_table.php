<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_gst_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_number', 100);
            $table->date('invoice_date');
            $table->decimal('amount', 12, 2);
            $table->decimal('gst_amount', 10, 2);
            $table->string('file_path');
            $table->date('period_from');
            $table->date('period_to');
            $table->timestamps();

            $table->index(['user_id', 'invoice_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_gst_invoices');
    }
};
