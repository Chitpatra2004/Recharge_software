<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('pan_image_path', 500)->nullable()->after('document_path')
                  ->comment('PAN card image upload path');
            $table->string('gst_certificate_path', 500)->nullable()->after('pan_image_path')
                  ->comment('GST certificate upload path');
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])
                  ->default('pending')->after('status')
                  ->comment('Admin approval status for new registrations');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['pan_image_path', 'gst_certificate_path', 'approval_status']);
        });
    }
};
