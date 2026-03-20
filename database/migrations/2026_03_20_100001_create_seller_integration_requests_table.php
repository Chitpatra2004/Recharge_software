<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_integration_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('website_url');
            $table->string('callback_url')
                  ->comment('GET URL on seller\'s server — we call it to update recharge status');
            $table->string('site_username', 100)->nullable()
                  ->comment('Username on seller\'s site (for reference only)');
            $table->string('site_password_hint', 100)->nullable()
                  ->comment('Password hint only — never store actual password');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_integration_requests');
    }
};
