<?php

use App\Models\Operator;
use App\Models\OperatorRoute;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $provider = 'ColdPay Mobikwik';
        $endpoint = rtrim(env('MOBIKWIK_BASE_URL', 'https://alpha3.mobikwik.com'), '/') . '/recharge/v3/retailerPayment';

        $commonConfig = [
            'driver' => 'mobikwik_v3',
            'base_url' => env('MOBIKWIK_BASE_URL', 'https://alpha3.mobikwik.com'),
            'client_id' => env('MOBIKWIK_CLIENT_ID', ''),
            'client_secret' => env('MOBIKWIK_CLIENT_SECRET', ''),
            'member_id' => env('MOBIKWIK_MEMBER_ID', ''),
            'public_key' => env('MOBIKWIK_PUBLIC_KEY', ''),
            'key_version' => env('MOBIKWIK_KEY_VERSION', '1.0'),
            'payment_mode' => 'Cash',
            'payment_account_info' => '',
            'api_status' => false,
            'auto_renewal' => false,
            'purchase' => 'active',
            'validity_till' => '0000-00-00',
            'margin' => 0,
            'method' => 'POST',
            'request_params' => 'cn=[mobile]&op=[opcode]&cir=[circlecode]&amt=[amount]&reqid=[order_id]&customerMobile=[mobile]&remitterName=[remitter_name]&paymentRefID=[payment_ref_id]&paymentMode=[payment_mode]&paymentAccountInfo=[payment_account_info]',
            'status_key' => 'data.status',
            'txnid_key' => 'data.operatorRefNo',
            'live_id_key' => 'data.mobikwikStamp',
            'success_val' => 'SUCCESS,RECHARGESUCCESS',
            'pending_val' => 'SUCCESSPENDING,RECHARGESUCCESSPENDING',
            'failure_val' => 'RECHARGEFAILURE',
            'recharge_api' => [
                'method' => 'POST',
                'url' => $endpoint,
                'params' => 'cn=[mobile]&op=[opcode]&cir=[circlecode]&amt=[amount]&reqid=[order_id]&customerMobile=[mobile]&remitterName=[remitter_name]&paymentRefID=[payment_ref_id]&paymentMode=[payment_mode]&paymentAccountInfo=[payment_account_info]',
                'response_type' => 'JSON',
                'separator' => '',
                'status_key' => 'data.status',
                'txnid_key' => 'data.operatorRefNo',
                'live_id_key' => 'data.mobikwikStamp',
                'success_val' => 'SUCCESS,RECHARGESUCCESS',
                'pending_val' => 'SUCCESSPENDING,RECHARGESUCCESSPENDING',
                'failure_val' => 'RECHARGEFAILURE',
            ],
            'balance_api' => [
                'method' => 'POST',
                'url' => rtrim(env('MOBIKWIK_BASE_URL', 'https://alpha3.mobikwik.com'), '/') . '/recharge/v3/retailerBalance',
                'params' => '',
                'balance_key' => 'data.balance',
            ],
            'status_api' => [
                'method' => 'POST',
                'url' => rtrim(env('MOBIKWIK_BASE_URL', 'https://alpha3.mobikwik.com'), '/') . '/recharge/v3/retailerStatus',
                'params' => 'txId=[order_id]',
                'status_key' => 'data.status',
                'txnid_key' => 'data.operatorRefNo',
            ],
            'validation_api' => [
                'method' => 'POST',
                'url' => rtrim(env('MOBIKWIK_BASE_URL', 'https://alpha3.mobikwik.com'), '/') . '/recharge/v3/retailerValidation',
            ],
            'view_bill_api' => [
                'method' => 'POST',
                'url' => rtrim(env('MOBIKWIK_BASE_URL', 'https://alpha3.mobikwik.com'), '/') . '/recharge/v3/retailerViewbill',
            ],
        ];

        $routes = [
            ['AIRTEL', 'prepaid', '1'],
            ['JIO', 'prepaid', '140'],
            ['VI', 'prepaid', '338'],
            ['IDEA', 'prepaid', '338'],
            ['BSNL', 'prepaid', '3'],
            ['BSNL_STV', 'prepaid', '3'],
            ['BSNL_TOPUP', 'prepaid', '3'],
            ['AIRTEL_DTH', 'dth', '23'],
            ['DISHTV', 'dth', '18'],
            ['TATASKY', 'dth', '19'],
            ['TATAPLAY', 'dth', '19'],
            ['SUNDIRECT', 'dth', '22'],
            ['VIDEOCON', 'dth', '21'],
        ];

        foreach ($routes as [$code, $type, $apiCode]) {
            $operator = Operator::query()->where('code', $code)->first();
            if (! $operator) {
                continue;
            }

            $config = $commonConfig;
            $config['op_codes'] = [$code => $apiCode];

            OperatorRoute::query()->updateOrCreate(
                ['operator_code' => $code, 'recharge_type' => $type, 'api_provider' => $provider],
                [
                    'operator_id' => $operator->id,
                    'name' => "{$operator->name} via {$provider}",
                    'api_endpoint' => $endpoint,
                    'api_config' => $config,
                    'priority' => 5,
                    'success_rate' => 100,
                    'timeout_seconds' => 30,
                    'max_retries' => 3,
                    'is_active' => false,
                    'min_amount' => 1.00,
                    'max_amount' => 10000.00,
                ]
            );
        }
    }

    public function down(): void
    {
        OperatorRoute::query()
            ->where('api_provider', 'ColdPay Mobikwik')
            ->delete();
    }
};
