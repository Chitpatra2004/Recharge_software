<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('identifier', 160)->index();        // email or mobile used
            $table->string('otp_hash', 255);                   // bcrypt hash of OTP
            $table->enum('type', ['login_2fa', 'reset_password', 'register_verify']);
            $table->string('pending_token', 64)->nullable()->unique(); // UUID for 2FA flow
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['identifier', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otps');
    }
};
