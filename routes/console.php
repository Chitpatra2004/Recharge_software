<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('live:check', function () {
    $this->info('Checking live readiness...');

    $checks = [
        ['APP_ENV', app()->environment('production'), 'APP_ENV should be production on live.'],
        ['APP_KEY', filled(config('app.key')), 'APP_KEY is required.'],
        ['APP_URL scheme', str_starts_with((string) config('app.url'), 'https://'), 'APP_URL should include https:// on live.'],
        ['Database', true, 'Database connection should work.'],
        ['otps.type', true, 'otps.type should be compatible with OTP storage values.'],
        ['operators.category', true, 'operators.category should allow BBPS categories.'],
        ['seller API config columns', true, 'seller_integration_requests should include API config columns.'],
    ];

    try {
        DB::connection()->getPdo();
    } catch (Throwable $e) {
        $checks[3][1] = false;
        $checks[3][2] = 'Database connection failed: '.$e->getMessage();
    }

    if (Schema::hasTable('otps')) {
        $column = DB::selectOne("
            SELECT DATA_TYPE, COLUMN_TYPE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'otps'
              AND COLUMN_NAME = 'type'
        ");

        $columnType = (string) ($column->COLUMN_TYPE ?? '');
        $checks[4][1] = $column && (
            in_array(strtolower((string) $column->DATA_TYPE), ['varchar', 'char', 'text'], true)
            || (
                str_contains($columnType, 'login_2fa')
                && str_contains($columnType, 'reset_password')
                && str_contains($columnType, 'register_verify')
            )
        );
    } else {
        $checks[4][1] = false;
        $checks[4][2] = 'otps table is missing.';
    }

    if (Schema::hasTable('operators')) {
        $column = DB::selectOne("
            SELECT COLUMN_TYPE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'operators'
              AND COLUMN_NAME = 'category'
        ");

        $checks[5][1] = $column && str_contains((string) $column->COLUMN_TYPE, 'credit_card');
    } else {
        $checks[5][1] = false;
        $checks[5][2] = 'operators table is missing.';
    }

    $requiredSellerColumns = [
        'api_name',
        'low_balance_notification',
        'low_balance_limit',
        'notification_types',
        'recharge_api',
        'status_api',
        'balance_api',
        'dispute_api',
        'callback_config',
        'op_code_map',
    ];

    $checks[6][1] = Schema::hasTable('seller_integration_requests')
        && collect($requiredSellerColumns)->every(fn (string $column) => Schema::hasColumn('seller_integration_requests', $column));

    foreach ($checks as [$name, $passed, $message]) {
        if ($passed) {
            $this->line("[OK] {$name}");
        } else {
            $this->warn("[FIX] {$name}: {$message}");
        }
    }

    $this->newLine();
    $this->info('Recommended deploy order: php artisan migrate --force && php artisan optimize:clear && php artisan live:check');
})->purpose('Check common live deployment configuration and schema issues');
