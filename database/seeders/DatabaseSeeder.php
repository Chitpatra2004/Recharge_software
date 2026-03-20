<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin user
        $admin = \App\Models\User::updateOrCreate(
            ['email' => 'admin@recharge.com'],
            [
                'name'     => 'Super Admin',
                'mobile'   => '9000000001',
                'password' => \Illuminate\Support\Facades\Hash::make('Admin@123456'),
                'role'     => 'admin',
                'status'   => 'active',
            ]
        );
        \App\Models\Wallet::updateOrCreate(['user_id' => $admin->id], ['balance' => 0, 'status' => 'active']);

        // Demo retailer
        $retailer = \App\Models\User::updateOrCreate(
            ['email' => 'retailer@recharge.com'],
            [
                'name'            => 'Demo Retailer',
                'mobile'          => '9000000002',
                'password'        => \Illuminate\Support\Facades\Hash::make('Retailer@123456'),
                'role'            => 'retailer',
                'status'          => 'active',
                'commission_rate' => 1.50,
            ]
        );
        \App\Models\Wallet::updateOrCreate(['user_id' => $retailer->id], ['balance' => 5000.00, 'status' => 'active']);

        // ── Operators (master catalog) ─────────────────────────────────
        $operatorDefs = [
            ['AIRTEL', 'Airtel',  'mobile'],
            ['JIO',    'Jio',     'mobile'],
            ['BSNL',   'BSNL',    'mobile'],
            ['VI',     'Vi',      'mobile'],
            ['TATA_SKY','Tata Play','dth'],
        ];

        $operatorMap = [];
        foreach ($operatorDefs as [$code, $name, $category]) {
            $op = \App\Models\Operator::updateOrCreate(
                ['code' => $code],
                [
                    'name'             => $name,
                    'category'         => $category,
                    'is_active'        => true,
                    'prepaid_enabled'  => true,
                    'postpaid_enabled' => $category === 'mobile',
                    'min_amount'       => 10.00,
                    'max_amount'       => 10000.00,
                ]
            );
            $operatorMap[$code] = $op->id;
        }

        // ── Operator routes (API routing per operator) ──────────────────
        $routes = [
            ['AIRTEL', 'prepaid',  'ProviderA', 'https://api.provider-a.com/recharge', 1],
            ['JIO',    'prepaid',  'ProviderA', 'https://api.provider-a.com/recharge', 1],
            ['BSNL',   'prepaid',  'ProviderB', 'https://api.provider-b.com/recharge', 2],
            ['AIRTEL', 'postpaid', 'ProviderA', 'https://api.provider-a.com/postpaid', 1],
        ];

        foreach ($routes as [$code, $type, $provider, $endpoint, $priority]) {
            \App\Models\OperatorRoute::updateOrCreate(
                ['operator_code' => $code, 'recharge_type' => $type, 'api_provider' => $provider],
                [
                    'operator_id'     => $operatorMap[$code],
                    'name'            => "{$code} {$type} via {$provider}",
                    'api_endpoint'    => $endpoint,
                    'api_config'      => ['api_key' => 'DEMO_KEY_' . $code],
                    'priority'        => $priority,
                    'success_rate'    => 98,
                    'timeout_seconds' => 30,
                    'max_retries'     => 3,
                    'is_active'       => true,
                    'min_amount'      => 10.00,
                    'max_amount'      => 10000.00,
                ]
            );
        }
    }
}
