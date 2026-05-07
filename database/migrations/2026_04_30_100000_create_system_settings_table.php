<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('system_settings')) {
            Schema::create('system_settings', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        // Seed defaults
        $defaults = [
            'platform_name'          => 'ColdPay',
            'support_email'          => 'support@coldpay.in',
            'support_phone'          => '',
            'timezone'               => 'Asia/Kolkata',
            'currency'               => 'INR',
            'maintenance_mode'       => '0',

            'notif_topup_request'    => '1',
            'notif_api_failure'      => '1',
            'notif_low_balance'      => '1',
            'notif_new_admin'        => '1',
            'notif_daily_summary'    => '0',
            'notif_complaint_esc'    => '1',

            'min_wallet_balance'     => '100',
            'auto_topup_threshold'   => '5000',
            'max_single_recharge'    => '10000',
            'gst_on_commission'      => '1',

            'api_timeout'            => '30',
            'auto_fallback'          => '1',
            'rate_limit_per_seller'  => '100',
            'webhook_retry_attempts' => '3',

            'smtp_host'              => '',
            'smtp_port'              => '587',
            'smtp_username'          => '',
            'smtp_password'          => '',
            'sms_provider'           => 'Textlocal',
            'sms_api_key'            => '',
            'sms_sender_id'          => '',
        ];

        $now = now();
        foreach ($defaults as $key => $value) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $key],
                [
                    'value'      => $value,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
