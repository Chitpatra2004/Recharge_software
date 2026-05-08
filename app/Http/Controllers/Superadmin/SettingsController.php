<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => SystemSetting::allKeyed(),
        ]);
    }

    public function saveGeneral(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'platform_name'    => 'required|string|max:100',
            'support_email'    => 'required|email|max:150',
            'support_phone'    => 'nullable|string|max:20',
            'timezone'         => 'required|string|max:50',
            'currency'         => 'required|string|max:10',
            'maintenance_mode' => 'required|in:0,1',
            'admin_multiple_sessions' => 'required|in:0,1',
        ]);

        SystemSetting::setMany($data);
        return response()->json(['success' => true, 'message' => 'General settings saved.']);
    }

    public function saveNotifications(Request $request): \Illuminate\Http\JsonResponse
    {
        $keys = [
            'notif_topup_request', 'notif_api_failure', 'notif_low_balance',
            'notif_new_admin', 'notif_daily_summary', 'notif_complaint_esc',
        ];
        $data = [];
        foreach ($keys as $key) {
            $data[$key] = $request->boolean($key) ? '1' : '0';
        }

        SystemSetting::setMany($data);
        return response()->json(['success' => true, 'message' => 'Notification settings saved.']);
    }

    public function saveFinance(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'min_wallet_balance'   => 'required|numeric|min:0',
            'auto_topup_threshold' => 'required|numeric|min:0',
            'max_single_recharge'  => 'required|numeric|min:1',
            'gst_on_commission'    => 'required|in:0,1',
        ]);

        SystemSetting::setMany($data);
        return response()->json(['success' => true, 'message' => 'Finance settings saved.']);
    }

    public function saveApi(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'api_timeout'            => 'required|integer|min:5|max:300',
            'auto_fallback'          => 'required|in:0,1',
            'rate_limit_per_seller'  => 'required|integer|min:1',
            'webhook_retry_attempts' => 'required|integer|min:0|max:10',
        ]);

        SystemSetting::setMany($data);
        return response()->json(['success' => true, 'message' => 'API settings saved.']);
    }

    public function saveSmtp(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'smtp_host'      => 'nullable|string|max:150',
            'smtp_port'      => 'nullable|integer',
            'smtp_username'  => 'nullable|string|max:150',
            'smtp_password'  => 'nullable|string|max:255',
            'sms_provider'   => 'nullable|string|max:50',
            'sms_api_key'    => 'nullable|string|max:255',
            'sms_sender_id'  => 'nullable|string|max:30',
        ]);

        SystemSetting::setMany($data);
        return response()->json(['success' => true, 'message' => 'SMTP / SMS settings saved.']);
    }
}
