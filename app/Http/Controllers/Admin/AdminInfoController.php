<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminInfoController extends Controller
{
    private array $defaults = [
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

    public function show(): JsonResponse
    {
        $settings = [];
        foreach ($this->defaults as $key => $default) {
            $settings[$key] = SystemSetting::get($key, $default);
        }

        return response()->json(['data' => $settings]);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'recharge_request_timeout' => ['required', 'integer', 'min:3', 'max:120'],
            'recharge_connect_timeout' => ['required', 'integer', 'min:1', 'max:60'],
            'seller_callback_enabled' => ['required', 'in:0,1'],
            'seller_callback_timeout' => ['required', 'integer', 'min:3', 'max:120'],
            'seller_callback_instant' => ['required', 'in:0,1'],
            'seller_callback_late' => ['required', 'in:0,1'],
            'seller_callback_late_after_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'seller_notice_enabled' => ['required', 'in:0,1'],
            'seller_notice_title' => ['nullable', 'string', 'max:120'],
            'seller_notice_message' => ['nullable', 'string', 'max:1000'],
        ]);

        SystemSetting::setMany($data);

        return response()->json(['message' => 'Admin info settings saved.', 'data' => $data]);
    }
}
