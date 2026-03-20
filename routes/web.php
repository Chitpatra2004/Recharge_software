<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// ── User Auth & Portal Web Routes ────────────────────────────────────────
Route::prefix('user')->name('user.')->group(function () {

    Route::get('/login',      fn () => view('user.auth.login'))->name('login');
    Route::get('/register',   fn () => view('user.auth.register'))->name('register');
    Route::get('/',           fn () => redirect()->route('user.dashboard'));
    Route::get('/dashboard',  fn () => view('user.dashboard'))->name('dashboard');
    Route::get('/recharges',  fn () => view('user.recharges'))->name('recharges');
    Route::get('/wallet',     fn () => view('user.wallet'))->name('wallet');
    Route::get('/complaints', fn () => view('user.complaints'))->name('complaints');
    Route::get('/profile',    fn () => view('user.profile'))->name('profile');
    Route::get('/reports',    fn () => view('user.reports'))->name('reports');
    Route::get('/api-docs',   fn () => view('user.api-docs'))->name('api-docs');

});

// ── Seller Portal Web Routes ─────────────────────────────────────────────
Route::prefix('seller')->name('seller.')->group(function () {
    Route::get('/register',         fn () => view('seller.auth.register'))->name('register');
    Route::get('/login',            fn () => view('seller.auth.login'))->name('login');
    Route::get('/',                 fn () => redirect()->route('seller.dashboard'));
    Route::get('/dashboard',        fn () => view('seller.dashboard'))->name('dashboard');
    Route::get('/api-config',       fn () => view('seller.api-config'))->name('api-config');
    Route::get('/sales',            fn () => view('seller.sales'))->name('sales');
    Route::get('/reports/account',  fn () => view('seller.reports.account'))->name('reports.account');
    Route::get('/reports/topup',    fn () => view('seller.reports.topup'))->name('reports.topup');
    Route::get('/reports/operator', fn () => view('seller.reports.operator'))->name('reports.operator');
    Route::get('/reports/ledger',   fn () => view('seller.reports.ledger'))->name('reports.ledger');
    Route::get('/payments',         fn () => view('seller.payments.index'))->name('payments');
    Route::get('/gst',              fn () => view('seller.gst.invoices'))->name('gst');
});

// ── Super Admin Command Center Routes ────────────────────────────────────
Route::prefix('superadmin')->name('superadmin.')->group(function () {

    // Auth
    Route::get('/',        fn () => redirect()->route('superadmin.dashboard'));
    Route::get('/login',   fn () => view('superadmin.auth.login'))->name('login');

    // Command Center
    Route::get('/dashboard', fn () => view('superadmin.dashboard'))->name('dashboard');

    // Management
    Route::get('/admins',    fn () => view('superadmin.manage.admins'))->name('admins');
    Route::get('/operators', fn () => view('superadmin.manage.operators'))->name('operators');
    Route::get('/users',     fn () => view('superadmin.manage.users'))->name('users');

    // Seller Tools (new)
    Route::get('/seller-api-config',  fn () => view('superadmin.sellers.api-config'))->name('seller-api-config');
    Route::get('/operator-switching', fn () => view('superadmin.system.operator-switching'))->name('operator-switching');

    // Finance
    Route::get('/revenue',     fn () => view('superadmin.finance.revenue'))->name('revenue');
    Route::get('/commissions', fn () => view('superadmin.finance.commissions'))->name('commissions');
    Route::get('/wallets',     fn () => view('superadmin.finance.wallets'))->name('wallets');

    // System
    Route::get('/api-gateway',   fn () => view('superadmin.system.api-gateway'))->name('api-gateway');
    Route::get('/vendor-apis',   fn () => view('superadmin.system.vendor-apis'))->name('vendor-apis');
    Route::get('/broadcast',     fn () => view('superadmin.system.broadcast'))->name('broadcast');
    Route::get('/audit',         fn () => view('superadmin.system.audit'))->name('audit');
    Route::get('/security',      fn () => view('superadmin.system.security'))->name('security');

    // Config
    Route::get('/settings',       fn () => view('superadmin.config.settings'))->name('settings');
    Route::get('/access-control', fn () => view('superadmin.config.access-control'))->name('access-control');

});

// ── Admin Panel Web Routes ───────────────────────────────────────────────
// These routes only serve Blade views. All data is loaded via JavaScript
// calling the existing API endpoints (routes/api.php) with Bearer tokens.
// Authentication is enforced client-side (JS checks localStorage token)
// and server-side by the API middleware on every data request.

Route::prefix('admin')->name('admin.')->group(function () {

    // Auth
    Route::get('/login',  fn () => view('admin.auth.login'))->name('login');

    // Redirect /admin → /admin/dashboard
    Route::get('/', fn () => redirect()->route('admin.dashboard'));

    // Main dashboard
    Route::get('/dashboard', fn () => view('admin.dashboard'))->name('dashboard');

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/recharges',      fn () => view('admin.reports.recharges'))->name('recharges');
        Route::get('/operators',      fn () => view('admin.reports.operators'))->name('operators');
        Route::get('/failures',       fn () => view('admin.reports.failures'))->name('failures');
        Route::get('/payments',       fn () => view('admin.reports.payments'))->name('payments');
        Route::get('/wallets',        fn () => view('admin.reports.wallets'))->name('wallets');
        Route::get('/complaints',     fn () => view('admin.reports.complaints'))->name('complaints');
        Route::get('/pending',        fn () => view('admin.reports.pending'))->name('pending');
        Route::get('/operator-codes', fn () => view('admin.reports.operator-codes'))->name('operator-codes');
        Route::get('/bank-accounts',  fn () => view('admin.reports.bank-accounts'))->name('bank-accounts');
        Route::get('/account',        fn () => view('admin.reports.account'))->name('account');
        Route::get('/topup',          fn () => view('admin.reports.topup'))->name('topup');
    });

    // Users
    Route::get('/users',     fn () => view('admin.users.index'))->name('users');

    // Manage
    Route::get('/operators', fn () => view('admin.manage.operators'))->name('operators');
    Route::get('/employees', fn () => view('admin.manage.employees'))->name('employees');
    Route::get('/api-keys',  fn () => view('admin.manage.api-keys'))->name('api-keys');

    // Commission
    Route::get('/commission/slab',    fn () => view('admin.commission.slab'))->name('commission.slab');
    Route::get('/commission/history', fn () => view('admin.commission.history'))->name('commission.history');

    // Profile & Security
    Route::get('/profile', fn () => view('admin.profile'))->name('profile');

    // API Developer Tools
    Route::get('/api-docs',        fn () => view('admin.api-docs'))->name('api-docs');
    Route::get('/api-integration', fn () => view('admin.api-integration'))->name('api-integration');

    // Tools
    Route::get('/todos',     fn () => view('admin.tools.todos'))->name('todos');
    Route::get('/reminders', fn () => view('admin.tools.reminders'))->name('reminders');
    Route::get('/exports',   fn () => view('admin.tools.exports'))->name('exports');
    Route::get('/activity',  fn () => view('admin.tools.activity'))->name('activity');

    // Complaints
    Route::get('/complaints',          fn () => view('admin.complaints.index'))->name('complaints');
    Route::get('/complaints/pending',  fn () => view('admin.complaints.pending'))->name('complaints.pending');
    Route::get('/complaints/resolved', fn () => view('admin.complaints.resolved'))->name('complaints.resolved');

    // Seller / API-user management
    Route::get('/sellers',                  fn () => view('admin.sellers.index'))->name('sellers');
    Route::get('/sellers/payment-requests', fn () => view('admin.sellers.payment-requests'))->name('sellers.payments');

});
