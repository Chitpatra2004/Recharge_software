<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\ApiKeyController;
use App\Http\Controllers\Admin\ApiSwitchingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmployeeAuthController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\EmployeeProfileController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\AdminRechargeController;
use App\Http\Controllers\Admin\AdminWalletController;
use App\Http\Controllers\Admin\SellerController as AdminSellerController;
use App\Http\Controllers\Admin\SellerPaymentController as AdminSellerPaymentController;
use App\Http\Controllers\Admin\UserPaymentRequestController as AdminUserPaymentRequestController;
use App\Http\Controllers\Admin\OperatorApiSettingController;
use App\Http\Controllers\Admin\OperatorSettingController;
use App\Http\Controllers\Admin\PdrsAdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\TwoFactorController;
use App\Http\Controllers\Api\BuyerRechargeController;
use App\Http\Controllers\Api\ComplaintController;
use App\Http\Controllers\Api\OperatorController;
use App\Http\Controllers\Api\RechargeController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\BBPSController;
use App\Http\Controllers\Api\UserDashboardController;
use App\Http\Controllers\Api\UserPaymentRequestController;
use App\Http\Controllers\Seller\AuthController as SellerAuthController;
use App\Http\Controllers\Seller\ApiConfigController as SellerApiConfigController;
use App\Http\Controllers\Seller\DashboardController as SellerDashboardController;
use App\Http\Controllers\Seller\GstController as SellerGstController;
use App\Http\Controllers\Seller\PaymentController as SellerPaymentController;
use App\Http\Controllers\Seller\ReportController as SellerReportController;
use App\Http\Controllers\Seller\SalesController as SellerSalesController;
use App\Http\Controllers\Seller\DocumentController as SellerDocumentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Recharge Platform v1
|--------------------------------------------------------------------------
|
| Auth methods:
|   Sanctum token  →  Authorization: Bearer <token>   (web/app clients)
|   API Key        →  X-API-Key: <raw_key>             (machine-to-machine)
|
| Rate limits (defined in AppServiceProvider):
|   'api'      — 60 req/min per user/IP  (general)
|   'recharge' — 10 req/min per user/IP  (recharge endpoint)
|
| Middleware aliases:
|   'api.key'         — ApiKeyAuth (allows Sanctum fallthrough)
|   'api.key:scope'   — ApiKeyAuth + scope enforcement
|   'log.api'         — ApiRequestLogger
|
*/

// ── Employee authentication (public — no auth required) ────────────────────
// brute.force middleware tracks failed attempts per IP and locks accounts
Route::prefix('v1/employee/auth')
    ->middleware(['throttle:auth', 'brute.force:10,15', 'log.api'])
    ->group(function () {

    Route::post('/login',           [EmployeeAuthController::class, 'login']);
    Route::post('/2fa/verify-otp',  [EmployeeAuthController::class, 'verify2faOtp']);
    Route::post('/2fa/verify-totp', [EmployeeAuthController::class, 'verify2faTotp']);
    Route::post('/2fa/resend-otp',  [EmployeeAuthController::class, 'resend2faOtp']);

});

// ── Employee authenticated routes ───────────────────────────────────────────
Route::prefix('v1/employee')
    ->middleware(['auth:employee', 'throttle:api', 'log.api', 'log.employee'])
    ->group(function () {

    Route::post('/auth/logout',     [EmployeeAuthController::class, 'logout']);
    Route::post('/auth/logout-all', [EmployeeAuthController::class, 'logoutAll']);
    Route::get('/auth/me',          [EmployeeAuthController::class, 'me']);

    // Admin dashboard & reports (reuse same controllers — employee guard ensures only employees reach here)
    Route::get('/dashboard',            [DashboardController::class, 'index']);
    Route::get('/dashboard/summary',    [DashboardController::class, 'summary']);
    Route::get('/dashboard/live',       [DashboardController::class, 'live']);
    Route::get('/dashboard/operators',  [DashboardController::class, 'operators']);
    Route::get('/dashboard/gateway',    [DashboardController::class, 'gateway']);
    Route::get('/dashboard/complaints', [DashboardController::class, 'complaints']);
    Route::get('/dashboard/chart',      [DashboardController::class, 'chart']);

    // Activity logs (admin-only)
    Route::get('/activity',                   [ActivityLogController::class, 'index']);
    Route::get('/activity/employees-list',    [ActivityLogController::class, 'employeesList']);
    Route::get('/api-logs',                   [ActivityLogController::class, 'apiLogs']);

    // Employee management (admin-only)
    Route::get('/employees',          [EmployeeController::class, 'index']);
    Route::post('/employees',         [EmployeeController::class, 'store']);
    Route::get('/employees/{id}',     [EmployeeController::class, 'show']);
    Route::put('/employees/{id}',     [EmployeeController::class, 'update']);
    Route::delete('/employees/{id}',  [EmployeeController::class, 'destroy']);

    // API key management — admin manages buyer/user API keys
    Route::get('/api-keys',              [ApiKeyController::class, 'index']);
    Route::post('/api-keys',             [ApiKeyController::class, 'store']);
    Route::delete('/api-keys/{id}',      [ApiKeyController::class, 'destroy']);
    Route::get('/users/search',          [ApiKeyController::class, 'searchUsers']);

    // Reports (accessible by employees/admins)
    Route::put('/wallet-transactions/{id}',  [ReportController::class, 'updateWalletTransaction']);
    Route::get('/account-ledger',            [ReportController::class, 'accountLedger']);
    Route::get('/reports/pending',           [ReportController::class, 'pending']);
    Route::get('/reports/recharges',     [ReportController::class, 'recharges']);
    Route::get('/reports/users',         [ReportController::class, 'users']);
    Route::get('/users/{id}',            [ReportController::class, 'showUser']);
    Route::get('/reports/operators',     [ReportController::class, 'operators']);
    Route::get('/reports/failures',      [ReportController::class, 'failures']);
    Route::get('/reports/payments',      [ReportController::class, 'payments']);
    Route::get('/reports/complaints',    [ReportController::class, 'complaints']);
    Route::get('/reports/wallets',       [ReportController::class, 'wallets']);

    // Recharge management
    Route::get('/recharges/{id}',        [AdminRechargeController::class, 'show']);
    Route::post('/recharges/{id}/resend',[AdminRechargeController::class, 'resend']);
    Route::post('/recharges/{id}/refund',[AdminRechargeController::class, 'refund']);
    Route::post('/recharges/{id}/success',[AdminRechargeController::class, 'markSuccess']);
    Route::post('/recharges/{id}/status', [AdminRechargeController::class, 'markStatus']);
    Route::post('/recharges/{id}/send-api', [AdminRechargeController::class, 'sendToApi']);

    // Employee profile (self)
    Route::get('/profile',               [EmployeeProfileController::class, 'show']);
    Route::put('/profile',               [EmployeeProfileController::class, 'update']);
    Route::put('/auth/password',         [EmployeeProfileController::class, 'changePassword']);

    // Employee 2FA setup
    Route::post('/2fa/send-otp',         [EmployeeProfileController::class, 'sendTfaOtp']);
    Route::post('/2fa/verify',           [EmployeeProfileController::class, 'verifyTfaOtp']);
    Route::post('/2fa/setup-totp',       [EmployeeProfileController::class, 'setupTotp']);
    Route::post('/2fa/enable-totp',      [EmployeeProfileController::class, 'enableTotp']);
    Route::delete('/2fa/disable',        [EmployeeProfileController::class, 'disableTfa']);

    // Employee sessions & activity (self)
    Route::get('/sessions',              [EmployeeProfileController::class, 'sessions']);
    Route::delete('/sessions/{id}',      [EmployeeProfileController::class, 'revokeSession']);
    Route::get('/login-history',         [EmployeeProfileController::class, 'loginHistory']);
    Route::get('/my-activity',           [EmployeeProfileController::class, 'activity']);
    Route::put('/preferences',           [EmployeeProfileController::class, 'savePreferences']);

    // Operator API provider management
    Route::get('/api-switching',                            [ApiSwitchingController::class, 'index']);
    Route::post('/api-switching/routes',                    [ApiSwitchingController::class, 'saveRoute']);

    Route::get('/operator-settings',                         [OperatorSettingController::class, 'index']);
    Route::post('/operator-settings',                        [OperatorSettingController::class, 'store']);
    Route::put('/operator-settings/{operator}',              [OperatorSettingController::class, 'update']);
    Route::patch('/operator-settings/{operator}/toggle',     [OperatorSettingController::class, 'toggle']);

    Route::get('/api-providers',                              [OperatorApiSettingController::class, 'listRoutes']);
    Route::post('/api-providers',                             [OperatorApiSettingController::class, 'storeRoute']);
    Route::put('/api-providers/{route}/basic',                [OperatorApiSettingController::class, 'updateBasic']);
    Route::patch('/api-providers/{route}/toggle',             [OperatorApiSettingController::class, 'toggle']);
    Route::delete('/api-providers/{route}',                   [OperatorApiSettingController::class, 'destroy']);

    // API Integration Portal — per-section config + live test
    Route::get('/api-providers/{route}/full-config',          [OperatorApiSettingController::class, 'fullConfig']);
    Route::put('/api-providers/{route}/credentials',          [OperatorApiSettingController::class, 'updateCredentials']);
    Route::put('/api-providers/{route}/recharge-api',         [OperatorApiSettingController::class, 'updateRechargeApi']);
    Route::put('/api-providers/{route}/balance-api',          [OperatorApiSettingController::class, 'updateBalanceApi']);
    Route::put('/api-providers/{route}/status-api',           [OperatorApiSettingController::class, 'updateStatusApi']);
    Route::put('/api-providers/{route}/complaint-api',        [OperatorApiSettingController::class, 'updateComplaintApi']);
    Route::put('/api-providers/{route}/callback',             [OperatorApiSettingController::class, 'updateCallback']);
    Route::put('/api-providers/{route}/op-codes',             [OperatorApiSettingController::class, 'updateOpCodes']);
    Route::get('/api-providers/{route}/test-balance',         [OperatorApiSettingController::class, 'testBalance']);
    Route::get('/api-providers/{route}/test-status',          [OperatorApiSettingController::class, 'testStatus']);
    Route::post('/api-providers/{route}/test-complaint',      [OperatorApiSettingController::class, 'testComplaint']);
    Route::put('/api-providers/{route}/margin',               [OperatorApiSettingController::class, 'updateMargin']);

    // PDRS admin utility calls (legacy — kept for backward compat)
    Route::get('/pdrs/{route}/balance',       [PdrsAdminController::class, 'balance']);
    Route::get('/pdrs/{route}/check-status',  [PdrsAdminController::class, 'checkStatus']);
    Route::post('/pdrs/{route}/complain',     [PdrsAdminController::class, 'raiseComplaint']);

});

// ── Public endpoints ────────────────────────────────────────────────────────
Route::prefix('v1')->middleware('log.api')->group(function () {

    // New user registration (returns Sanctum token on success)
    Route::post('/auth/register', [AuthController::class, 'register'])
         ->middleware('throttle:auth');

    // Password-based login — brute.force:10,15 = max 10 failures in 15-min window
    Route::post('/auth/login', [AuthController::class, 'login'])
         ->middleware(['throttle:auth', 'brute.force:10,15']);

    // 2FA verification (no auth token required — uses pending_token instead)
    Route::post('/auth/2fa/verify-otp',  [TwoFactorController::class, 'verifyOtp'])
         ->middleware('throttle:otp');
    Route::post('/auth/2fa/verify-totp', [TwoFactorController::class, 'verifyTotp'])
         ->middleware('throttle:otp');
    Route::post('/auth/2fa/resend-otp',  [TwoFactorController::class, 'resendOtp'])
         ->middleware('throttle:3,1');   // max 3 resends per minute

    // Forgot / Reset password
    Route::post('/auth/forgot-password',            [ForgotPasswordController::class, 'sendOtp'])
         ->middleware('throttle:auth');
    Route::post('/auth/forgot-password/verify-otp', [ForgotPasswordController::class, 'verifyOtp'])
         ->middleware('throttle:otp');
    Route::post('/auth/forgot-password/reset',      [ForgotPasswordController::class, 'resetPassword'])
         ->middleware('throttle:auth');

    // Operator callback webhook — secured by HMAC in controller
    Route::post('/recharge/callback',                     [RechargeController::class, 'callback'])->middleware('throttle:callback');
    // Per-seller callback URLs (sellerId is used for routing/logging only; HMAC still verified)
    Route::post('/recharge/callback/{sellerId}',          [RechargeController::class, 'callback'])->middleware('throttle:callback');
    Route::get('/recharge/callback/{sellerId}',           [RechargeController::class, 'pdrsCallback'])->middleware('throttle:callback');
    // PDRS sends GET callbacks: ?uniqueid={order_id}&status={}&transaction_id={}&...
    Route::get('/recharge/pdrs-callback',                 [RechargeController::class, 'pdrsCallback'])->middleware('throttle:callback');

});

// ── Sanctum-authenticated routes (web/app users) ────────────────────────────
Route::prefix('v1')
    ->middleware(['auth:sanctum', 'throttle:api', 'log.api'])
    ->group(function () {

    // Auth
    Route::get('/auth/me',           [AuthController::class, 'me']);
    Route::post('/auth/logout',      [AuthController::class, 'logout']);
    Route::post('/auth/api-key',     [AuthController::class, 'generateApiKey']);

    // 2FA management (requires login)
    Route::post('/auth/2fa/setup-totp',  [TwoFactorController::class, 'setupTotp']);
    Route::post('/auth/2fa/enable-totp', [TwoFactorController::class, 'enableTotp']);
    Route::post('/auth/2fa/enable-otp',  [TwoFactorController::class, 'enableOtp']);
    Route::post('/auth/2fa/disable',     [TwoFactorController::class, 'disable']);

    // Recharge
    Route::post('/recharge',         [RechargeController::class, 'store'])
         ->middleware('throttle:recharge');
    Route::get('/recharge/{id}',     [RechargeController::class, 'show']);

    // Transaction history
    Route::get('/transactions',      [TransactionController::class, 'index']);
    Route::get('/transactions/{id}', [TransactionController::class, 'show']);

    // Wallet
    Route::get('/wallet/balance',       [WalletController::class, 'balance']);
    Route::get('/wallet/transactions',  [WalletController::class, 'transactions']);
    Route::post('/wallet/self-topup',   [WalletController::class, 'selfTopup'])->middleware('throttle:money');

    // Operators
    Route::get('/operators',        [OperatorController::class, 'index']);
    Route::get('/operators/detect', [OperatorController::class, 'detect']);

    // Complaints
    Route::get('/complaints',      [ComplaintController::class, 'index']);
    Route::post('/complaints',     [ComplaintController::class, 'store']);
    Route::get('/complaints/{id}', [ComplaintController::class, 'show']);

    // ── User Dashboard (enhanced stats + chart) ────────────────────────────
    Route::get('/dashboard', [UserDashboardController::class, 'index']);

    // ── BBPS (Bill Payment) ────────────────────────────────────────────────
    Route::get('/bbps/billers',          [BBPSController::class, 'billers']);
    Route::post('/bbps/fetch-bill',      [BBPSController::class, 'fetchBill']);
    Route::post('/bbps/pay',             [BBPSController::class, 'pay'])->middleware('throttle:recharge');
    Route::get('/bbps/history',          [BBPSController::class, 'history']);

    // ── User Payment Requests (add money with proof) ───────────────────────
    Route::get('/payment-requests',      [UserPaymentRequestController::class, 'index']);
    Route::post('/payment-requests',     [UserPaymentRequestController::class, 'store'])->middleware(['throttle:money', 'throttle:upload']);

    // ── Admin-only ─────────────────────────────────────────────────────────
    Route::middleware('can:admin')->group(function () {
        Route::post('/wallet/topup',         [WalletController::class, 'topup'])->middleware('throttle:money');
        Route::post('/recharge/{id}/refund', [RechargeController::class, 'refund'])->middleware('throttle:money');
    });

});

// ── Buyer / Partner API (API-Key authenticated) ─────────────────────────────
//
// All routes here:
//   • Require a valid X-API-Key header (scope-checked per route)
//   • Are rate-limited to 60 req/min globally + 10 req/min for recharge
//   • Are logged via ApiRequestLogger
//
Route::prefix('v1/buyer')
    ->middleware(['api.key', 'throttle:api', 'log.api'])
    ->group(function () {

    // POST|GET /api/v1/buyer/recharge
    // Scope: recharge:write — initiate a recharge
    Route::get('/recharge',
        [BuyerRechargeController::class, 'recharge']
    )->middleware(['api.key:recharge:write', 'throttle:recharge']);

    Route::post('/recharge',
        [BuyerRechargeController::class, 'recharge']
    )->middleware(['api.key:recharge:write', 'throttle:recharge']);

    // GET /api/v1/buyer/recharge/{txnId}
    // Scope: recharge:read — poll transaction status
    Route::get('/recharge/{txnId}',
        [BuyerRechargeController::class, 'status']
    )->middleware('api.key:recharge:read');

    // GET /api/v1/buyer/balance
    // Scope: wallet:read — check wallet balance
    Route::get('/balance',
        [BuyerRechargeController::class, 'balance']
    )->middleware('api.key:wallet:read');

    // GET /api/v1/buyer/transactions
    // Scope: recharge:read — paginated transaction history
    Route::get('/transactions',
        [BuyerRechargeController::class, 'transactions']
    )->middleware('api.key:recharge:read');

    // POST /api/v1/buyer/callback/register
    // Scope: recharge:write — register async callback URL
    Route::post('/callback/register',
        [BuyerRechargeController::class, 'registerCallback']
    )->middleware('api.key:recharge:write');

});

// ── Admin dashboard endpoints ───────────────────────────────────────────────
//
// Real-time analytics. Poll intervals are returned in each response.
// Caching: file driver (local) / redis (production) — same code, no change.
//
Route::prefix('v1/admin/dashboard')
    ->middleware(['auth:sanctum', 'can:admin', 'throttle:api', 'log.api'])
    ->group(function () {

    Route::get('/',           [DashboardController::class, 'index']);      // full dashboard
    Route::get('/summary',    [DashboardController::class, 'summary']);    // KPI cards
    Route::get('/live',       [DashboardController::class, 'live']);       // live feed
    Route::get('/operators',  [DashboardController::class, 'operators']); // operator health
    Route::get('/gateway',    [DashboardController::class, 'gateway']);    // wallet + API stats
    Route::get('/complaints', [DashboardController::class, 'complaints']); // pending complaints
    Route::get('/chart',      [DashboardController::class, 'chart']);      // hourly + weekly
    Route::delete('/cache',   [DashboardController::class, 'flushCache']); // manual cache flush

});

// ── Admin reporting endpoints ───────────────────────────────────────────────
//
// Require:  Sanctum auth + admin role + 'log.api' logging
// All reports accept common filters: date_from, date_to, user_id, per_page
//
Route::prefix('v1/admin/reports')
    ->middleware(['auth:sanctum', 'can:admin', 'throttle:api', 'log.api'])
    ->group(function () {

    Route::get('/users',      [ReportController::class, 'users']);      // GET /api/v1/admin/reports/users
    Route::get('/recharges',  [ReportController::class, 'recharges']);  // GET /api/v1/admin/reports/recharges
    Route::get('/operators',  [ReportController::class, 'operators']);  // GET /api/v1/admin/reports/operators
    Route::get('/failures',   [ReportController::class, 'failures']);   // GET /api/v1/admin/reports/failures
    Route::get('/payments',   [ReportController::class, 'payments']);   // GET /api/v1/admin/reports/payments
    Route::get('/complaints', [ReportController::class, 'complaints']); // GET /api/v1/admin/reports/complaints
    Route::get('/wallets',    [ReportController::class, 'wallets']);    // GET /api/v1/admin/reports/wallets

});

// ── Seller Portal Auth (public) ─────────────────────────────────────────────
Route::prefix('v1/seller/auth')
    ->middleware(['throttle:auth', 'log.api'])
    ->group(function () {
    Route::post('/register', [SellerAuthController::class, 'register']);
    Route::post('/login',    [SellerAuthController::class, 'login'])->middleware('brute.force:10,15');
});

// ── Seller Portal (Sanctum authenticated + seller.role guard) ───────────────
Route::prefix('v1/seller')
    ->middleware(['auth:sanctum', 'seller.role', 'throttle:api', 'log.api'])
    ->group(function () {

    Route::post('/auth/logout', [SellerAuthController::class, 'logout']);
    Route::get('/auth/me',      [SellerAuthController::class, 'me']);

    Route::get('/dashboard',               [SellerDashboardController::class, 'index']);

    Route::get('/api-config',                              [SellerApiConfigController::class, 'config']);
    Route::patch('/api-config/notification-settings',     [SellerApiConfigController::class, 'updateNotificationSettings']);
    Route::post('/api-config/integration',                [SellerApiConfigController::class, 'submitIntegration']);
    Route::patch('/api-config/integration',               [SellerApiConfigController::class, 'updateIntegrationDetails']);
    Route::patch('/api-config/toggle-api',                [SellerApiConfigController::class, 'toggleApiStatus']);
    Route::post('/api-config/generate-token',             [SellerApiConfigController::class, 'generateToken']);

    Route::get('/sales', [SellerSalesController::class, 'index']);

    Route::get('/reports/account',  [SellerReportController::class, 'account']);
    Route::get('/reports/topup',    [SellerReportController::class, 'topup']);
    Route::get('/reports/operator', [SellerReportController::class, 'operator']);
    Route::get('/reports/ledger',   [SellerReportController::class, 'ledger']);
    Route::get('/reports/my-commission', [SellerReportController::class, 'myCommission']);

    Route::get('/payments',  [SellerPaymentController::class, 'index']);
    Route::post('/payments', [SellerPaymentController::class, 'store'])->middleware(['throttle:money', 'throttle:upload']);

    Route::get('/gst',  [SellerGstController::class, 'index']);
    Route::post('/gst', [SellerGstController::class, 'store'])->middleware('throttle:upload');

    Route::get('/documents',        [SellerDocumentController::class, 'status']);
    Route::post('/documents/upload', [SellerDocumentController::class, 'upload'])->middleware('throttle:upload');
});

// ── Admin: Seller Management ────────────────────────────────────────────────
Route::prefix('v1/employee/sellers')
    ->middleware(['auth:employee', 'throttle:api', 'log.api', 'log.employee'])
    ->group(function () {

    // Payment requests first (specific path before {id} wildcard)
    Route::get('/payment-requests/list',          [AdminSellerPaymentController::class, 'index']);
    Route::post('/payment-requests/{id}/approve', [AdminSellerPaymentController::class, 'approve'])->middleware('throttle:money');
    Route::post('/payment-requests/{id}/reject',  [AdminSellerPaymentController::class, 'reject'])->middleware('throttle:money');

    // Integration decisions
    Route::post('/integrations/{id}/decision', [AdminSellerController::class, 'integrationDecision']);

    // Seller CRUD + actions
    Route::get('/',                         [AdminSellerController::class, 'index']);
    Route::get('/{id}',                     [AdminSellerController::class, 'show']);
    Route::patch('/{id}',                   [AdminSellerController::class, 'update']);
    Route::get('/{id}/commissions',         [AdminSellerController::class, 'commissions']);
    Route::put('/{id}/commissions',         [AdminSellerController::class, 'updateCommissions']);
    Route::get('/{id}/document/{type}',     [AdminSellerController::class, 'viewDocument']);
    Route::post('/{id}/approve',            [AdminSellerController::class, 'approve']);
    Route::post('/{id}/reject',             [AdminSellerController::class, 'reject']);
    Route::post('/{id}/api-setting',   [AdminSellerController::class, 'updateApiSetting']);
    Route::post('/{id}/login-as',      [AdminSellerController::class, 'loginAs']);

    // Wallet management
    Route::post('/{id}/wallet/adjust',              [AdminWalletController::class, 'adjust'])->middleware('throttle:money');
    Route::get('/{id}/wallet/transactions',         [AdminWalletController::class, 'transactions']);

    // API config management for a seller (update integration URLs + generate key)
    Route::put('/{id}/api-config/integration',      [AdminSellerController::class, 'updateIntegration']);
    Route::post('/{id}/api-config/generate-key',    [AdminSellerController::class, 'generateSellerApiKey']);
});

// ── Admin: User Impersonation ───────────────────────────────────────────────
Route::prefix('v1/employee/users')
    ->middleware(['auth:employee', 'throttle:api', 'log.api', 'log.employee'])
    ->group(function () {
    Route::post('/{id}/login-as', [AdminWalletController::class, 'loginAsUser']);
});

// ── Admin: User Payment Requests (Add Money) ────────────────────────────────
Route::prefix('v1/employee/user-payment-requests')
    ->middleware(['auth:employee', 'throttle:api', 'log.api', 'log.employee'])
    ->group(function () {

    Route::get('/',                    [AdminUserPaymentRequestController::class, 'index']);
    Route::post('/{id}/approve',       [AdminUserPaymentRequestController::class, 'approve'])->middleware('throttle:money');
    Route::post('/{id}/reject',        [AdminUserPaymentRequestController::class, 'reject'])->middleware('throttle:money');
});
