<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('system_settings')) {
            return;
        }

        $defaults = [
            'recharge_request_timeout' => '10',
            'recharge_connect_timeout' => '5',
            'seller_callback_enabled' => '1',
            'seller_callback_timeout' => '15',
            'seller_callback_instant' => '1',
            'seller_callback_late' => '1',
            'seller_callback_late_after_minutes' => '30',
            'seller_notice_enabled' => '0',
            'seller_notice_title' => 'Notice',
            'seller_notice_message' => '',
        ];

        $now = now();
        foreach ($defaults as $key => $value) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('system_settings')) {
            return;
        }

        DB::table('system_settings')->whereIn('key', [
            'recharge_request_timeout',
            'recharge_connect_timeout',
            'seller_callback_enabled',
            'seller_callback_timeout',
            'seller_callback_instant',
            'seller_callback_late',
            'seller_callback_late_after_minutes',
            'seller_notice_enabled',
            'seller_notice_title',
            'seller_notice_message',
        ])->delete();
    }
};
