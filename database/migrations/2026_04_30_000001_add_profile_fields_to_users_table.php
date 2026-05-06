<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('address')->nullable()->after('commission_rate');
            $table->string('pincode', 10)->nullable()->after('address');
            $table->string('state', 100)->nullable()->after('pincode');
            $table->string('city', 100)->nullable()->after('state');
            $table->string('pan_no', 20)->nullable()->after('city');
            $table->string('aadhar_no', 20)->nullable()->after('pan_no');
            $table->string('gst_number', 20)->nullable()->after('aadhar_no');
            $table->string('contact_person', 100)->nullable()->after('gst_number');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['address','pincode','state','city','pan_no','aadhar_no','gst_number','contact_person']);
        });
    }
};
