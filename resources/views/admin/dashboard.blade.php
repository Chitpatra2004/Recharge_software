@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

{{-- Announcement Banner --}}
<div class="announcement-banner" id="announcement" style="display:none">
    <div class="announcement-icon">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
        </svg>
    </div>
    <div class="announcement-text">
        <div class="announcement-title" id="ann-title">site work under process</div>
        <div class="announcement-sub" id="ann-sub">testing</div>
    </div>
    <button class="announcement-close" onclick="document.getElementById('announcement').style.display='none'">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>

{{-- Page Header --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
    <div>
        <h1 style="font-size:20px;font-weight:700;color:var(--text-primary)">Dashboard</h1>
        <div class="breadcrumb" style="margin-bottom:0;margin-top:3px">
            <a href="#">Pages</a>
            <svg class="breadcrumb-sep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
            <span>Dashboard</span>
        </div>
    </div>
    <div style="display:flex;gap:10px;align-items:center">
        <button class="btn btn-outline btn-sm" onclick="window.refreshDashboard()" id="refresh-btn">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Refresh
        </button>
        <button class="btn btn-primary btn-sm" onclick="exportReport()">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Export Report
        </button>
    </div>
</div>

{{-- Note Bar --}}
<div class="note-bar">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <span><strong>Note:</strong> Dashboard metrics are updated every 2 minutes via automated cron job. Complaint metrics are fetched live. Hover over amounts to see in words.</span>
</div>

{{-- ── STAT CARDS ──────────────────────────────────────────────────────── --}}
<div class="stats-grid">
    {{-- Total Recharge --}}
    <div class="stat-card blue">
        <div class="stat-header">
            <div class="stat-label">Total Recharge</div>
            <div class="stat-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>
        <div class="stat-value" id="stat-total-count" data-tooltip="">—</div>
        <div class="stat-amount" id="stat-total-amt">—</div>
        <div class="stat-footer">
            <span class="stat-pulse"></span>
            <span class="stat-updated" id="stat-total-upd">Updating…</span>
        </div>
    </div>

    {{-- Total Success --}}
    <div class="stat-card green">
        <div class="stat-header">
            <div class="stat-label">Total Success</div>
            <div class="stat-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <div class="stat-value" id="stat-success-count" data-tooltip="">—</div>
        <div class="stat-amount" id="stat-success-amt">—</div>
        <div class="stat-footer">
            <span class="stat-pulse"></span>
            <span class="stat-updated" id="stat-success-upd">Updating…</span>
        </div>
    </div>

    {{-- Total Pending --}}
    <div class="stat-card orange">
        <div class="stat-header">
            <div class="stat-label">Total Pending</div>
            <div class="stat-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <div class="stat-value" id="stat-pending-count" data-tooltip="">—</div>
        <div class="stat-amount" id="stat-pending-amt">—</div>
        <div class="stat-footer">
            <span class="stat-pulse"></span>
            <span class="stat-updated" id="stat-pending-upd">Updating…</span>
        </div>
    </div>

    {{-- Total Failure --}}
    <div class="stat-card red">
        <div class="stat-header">
            <div class="stat-label">Total Failure</div>
            <div class="stat-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <div class="stat-value" id="stat-failure-count" data-tooltip="">—</div>
        <div class="stat-amount" id="stat-failure-amt">—</div>
        <div class="stat-footer">
            <span class="stat-pulse"></span>
            <span class="stat-updated" id="stat-failure-upd">Updating…</span>
        </div>
    </div>
</div>

{{-- ── CHARTS ROW ──────────────────────────────────────────────────────── --}}
<div class="charts-grid">

    {{-- Company Comparison Chart --}}
    <div class="card">
        <div class="card-header" style="justify-content:space-between">
            <div style="display:flex;align-items:center;gap:8px">
                <div style="width:32px;height:32px;background:#ede9fe;border-radius:8px;display:flex;align-items:center;justify-content:center">
                    <svg fill="none" viewBox="0 0 24 24" stroke="#7c3aed" stroke-width="2" style="width:17px;height:17px">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <div class="card-title">Company Comparison</div>
                    <div style="font-size:11px;color:var(--text-muted)">Last 12 Hours</div>
                </div>
            </div>
            <div style="display:flex;gap:8px;align-items:center">
                <button class="btn btn-outline btn-sm" onclick="loadChart('hourly')" id="btn-hourly" style="font-size:11px;padding:4px 10px">12H</button>
                <button class="btn btn-outline btn-sm" onclick="loadChart('weekly')" id="btn-weekly" style="font-size:11px;padding:4px 10px">Weekly</button>
            </div>
        </div>
        <div class="card-body" style="padding:16px 20px">
            <div id="chart-loading" class="loading-overlay">
                <div class="spinner"></div> Loading chart…
            </div>
            <canvas id="companyChart" style="display:none;max-height:260px"></canvas>
        </div>
    </div>

    {{-- Transaction Status Donut --}}
    <div class="card">
        <div class="card-header" style="justify-content:space-between">
            <div style="display:flex;align-items:center;gap:8px">
                <div style="width:32px;height:32px;background:#dbeafe;border-radius:8px;display:flex;align-items:center;justify-content:center">
                    <svg fill="none" viewBox="0 0 24 24" stroke="#2563eb" stroke-width="2" style="width:17px;height:17px">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                    </svg>
                </div>
                <div>
                    <div class="card-title">Transaction Status</div>
                    <div style="font-size:11px;color:var(--text-muted)">Today</div>
                </div>
            </div>
            <span class="live-badge">Live</span>
        </div>
        <div class="card-body" style="position:relative;display:flex;flex-direction:column;align-items:center;padding:20px">
            <div id="donut-loading" class="loading-overlay">
                <div class="spinner"></div>
            </div>
            <div style="position:relative;width:200px;height:200px;display:none" id="donut-wrap">
                <canvas id="statusChart"></canvas>
                <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;pointer-events:none">
                    <div style="font-size:11px;color:var(--text-muted);font-weight:500">Total (Today)</div>
                    <div style="font-size:20px;font-weight:700;color:var(--text-primary);line-height:1.2" id="donut-total">—</div>
                </div>
            </div>
            <div id="donut-legend" style="width:100%;margin-top:16px;display:none">
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border)">
                    <div style="display:flex;align-items:center;gap:8px">
                        <span style="width:10px;height:10px;border-radius:50%;background:#10b981;display:inline-block"></span>
                        <span style="font-size:13px;font-weight:500">Success</span>
                    </div>
                    <div style="text-align:right">
                        <div style="font-size:13px;font-weight:700;color:#10b981" id="leg-success-count">—</div>
                        <div style="font-size:11px;color:var(--text-muted)" id="leg-success-amt">—</div>
                    </div>
                </div>
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border)">
                    <div style="display:flex;align-items:center;gap:8px">
                        <span style="width:10px;height:10px;border-radius:50%;background:#ef4444;display:inline-block"></span>
                        <span style="font-size:13px;font-weight:500">Failure</span>
                    </div>
                    <div style="text-align:right">
                        <div style="font-size:13px;font-weight:700;color:#ef4444" id="leg-failure-count">—</div>
                        <div style="font-size:11px;color:var(--text-muted)" id="leg-failure-amt">—</div>
                    </div>
                </div>
                <div style="display:flex;justify-content:space-between;padding:8px 0">
                    <div style="display:flex;align-items:center;gap:8px">
                        <span style="width:10px;height:10px;border-radius:50%;background:#f59e0b;display:inline-block"></span>
                        <span style="font-size:13px;font-weight:500">Pending</span>
                    </div>
                    <div style="text-align:right">
                        <div style="font-size:13px;font-weight:700;color:#f59e0b" id="leg-pending-count">—</div>
                        <div style="font-size:11px;color:var(--text-muted)" id="leg-pending-amt">—</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── BOTTOM GRID: Operator Health + Complaints ──────────────────────── --}}
<div class="complaints-grid">

    {{-- Operator Health Table --}}
    <div class="card">
        <div class="card-header" style="justify-content:space-between">
            <div style="display:flex;align-items:center;gap:8px">
                <div style="width:32px;height:32px;background:#fef3c7;border-radius:8px;display:flex;align-items:center;justify-content:center">
                    <svg fill="none" viewBox="0 0 24 24" stroke="#d97706" stroke-width="2" style="width:17px;height:17px">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div class="card-title">Operator Performance</div>
            </div>
            <span class="live-badge">Live</span>
        </div>
        <div class="card-body" style="padding:0">
            <div id="ops-loading" class="loading-overlay">
                <div class="spinner"></div> Loading operators…
            </div>
            <div class="table-wrap" id="ops-table-wrap" style="display:none">
                <table>
                    <thead>
                        <tr>
                            <th>Operator</th>
                            <th>Success</th>
                            <th>Failure</th>
                            <th>Pending</th>
                            <th>Success Rate</th>
                            <th>Avg. Resp.</th>
                        </tr>
                    </thead>
                    <tbody id="ops-tbody">
                        <tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:24px">No data available</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer" style="justify-content:flex-end">
            <a href="/admin/reports/operators" class="view-more">
                View All Operators
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </div>

    {{-- Complaints Panel --}}
    <div class="card">
        <div class="card-header" style="justify-content:space-between">
            <div style="display:flex;align-items:center;gap:8px">
                <div style="width:32px;height:32px;background:#ede9fe;border-radius:8px;display:flex;align-items:center;justify-content:center">
                    <svg fill="none" viewBox="0 0 24 24" stroke="#7c3aed" stroke-width="2" style="width:17px;height:17px">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div class="card-title">Complaints</div>
            </div>
            <a href="/admin/complaints" class="view-more">
                View More
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        <div class="card-body">
            <div id="comp-loading" class="loading-overlay" style="padding:20px">
                <div class="spinner"></div>
            </div>
            <div id="comp-content" style="display:none">
                {{-- Summary --}}
                <div class="complaints-summary">
                    <div class="complaint-stat total">
                        <div class="complaint-stat-icon">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <div class="complaint-stat-label">Total Complaints</div>
                        <div class="complaint-stat-value" id="comp-total">—</div>
                    </div>
                    <div class="complaint-stat solved">
                        <div class="complaint-stat-icon">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="complaint-stat-label">Solved</div>
                        <div class="complaint-stat-value" id="comp-solved" style="color:var(--accent-green)">—</div>
                    </div>
                </div>

                {{-- Complaint Categories --}}
                <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.7px;color:var(--text-muted);margin-bottom:10px;display:flex;justify-content:space-between;align-items:center">
                    <span>Complaint Categories (Today)</span>
                    <span class="live-badge">Live</span>
                </div>

                <div class="complaint-category">
                    <div class="cat-dot" style="background:#10b981"></div>
                    <div class="cat-label">Success</div>
                    <div>
                        <div class="cat-count" style="color:#10b981" id="cat-success-count">—</div>
                        <div class="cat-amount" id="cat-success-amt">—</div>
                    </div>
                </div>
                <div class="complaint-category">
                    <div class="cat-dot" style="background:#ef4444"></div>
                    <div class="cat-label">Failure</div>
                    <div>
                        <div class="cat-count" style="color:#ef4444" id="cat-failure-count">—</div>
                        <div class="cat-amount" id="cat-failure-amt">—</div>
                    </div>
                </div>
                <div class="complaint-category">
                    <div class="cat-dot" style="background:#f59e0b"></div>
                    <div class="cat-label">Pending</div>
                    <div>
                        <div class="cat-count" style="color:#f59e0b" id="cat-pending-count">—</div>
                        <div class="cat-amount" id="cat-pending-amt">—</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── RECENT TRANSACTIONS ─────────────────────────────────────────────── --}}
<div class="card" style="margin-top:16px">
    <div class="card-header" style="justify-content:space-between">
        <div style="display:flex;align-items:center;gap:8px">
            <div style="width:32px;height:32px;background:#dbeafe;border-radius:8px;display:flex;align-items:center;justify-content:center">
                <svg fill="none" viewBox="0 0 24 24" stroke="#2563eb" stroke-width="2" style="width:17px;height:17px">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
            </div>
            <div>
                <div class="card-title">Recent Transactions</div>
                <div style="font-size:11px;color:var(--text-muted)">Today: <strong id="live-today-count">—</strong></div>
            </div>
        </div>
        <div style="display:flex;gap:8px;align-items:center">
            <span class="live-badge">Live</span>
            <a href="/admin/reports/recharges" class="view-more">
                View All
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </div>
    <div class="card-body" style="padding:0">
        <div id="live-loading" class="loading-overlay">
            <div class="spinner"></div> Loading transactions…
        </div>
        <ul class="txn-list" id="txn-list" style="display:none;padding:0 20px">
        </ul>
    </div>
</div>

@endsection

@push('scripts')
<script>
const OPERATOR_COLORS = {
    JIO:    ['#2563eb', '#dbeafe'],
    AIRTEL: ['#ef4444', '#fee2e2'],
    VI:     ['#f59e0b', '#fef3c7'],
    BSNL:   ['#f97316', '#ffedd5'],
    IDEA:   ['#8b5cf6', '#ede9fe'],
    DEFAULT:['#6b7280', '#f3f4f6'],
};

// ── Demo / Fallback data (shown when API is not yet connected) ─────────────
const DEMO_SUMMARY = {
    total:   { count: 1247, amount: 312580 },
    success: { count: 1089, amount: 272480 },
    pending: { count: 98,   amount: 24500  },
    failure: { count: 60,   amount: 15600  },
};
const DEMO_CHART = {
    hourly: {
        labels: ['8AM','9AM','10AM','11AM','12PM','1PM','2PM','3PM','4PM','5PM','6PM','7PM'],
        success: [42,67,89,112,98,134,156,143,127,118,105,99],
        failure: [4,6,8,11,9,14,15,12,11,10,9,8],
        pending: [8,12,15,18,16,22,24,21,19,17,15,14],
    },
    weekly: {
        labels: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
        success: [820,910,760,1050,980,1120,1089],
        failure: [65,70,58,88,79,95,60],
        pending: [120,105,98,130,110,145,98],
    }
};
const DEMO_OPERATORS = [
    {name:'Airtel',  success_count:412, failure_count:22, pending_count:38, success_rate:91.6, avg_response_time:520},
    {name:'Jio',     success_count:387, failure_count:18, pending_count:31, success_rate:93.2, avg_response_time:380},
    {name:'Vi',      success_count:156, failure_count:12, pending_count:19, success_rate:87.1, avg_response_time:760},
    {name:'BSNL',    success_count:89,  failure_count:7,  pending_count:8,  success_rate:88.1, avg_response_time:950},
    {name:'Idea',    success_count:45,  failure_count:1,  pending_count:2,  success_rate:93.8, avg_response_time:430},
];
const DEMO_LIVE = {
    today_success: 1089, today_failure: 60, today_pending: 98,
    today_success_amount: 272480, today_failure_amount: 15600, today_pending_amount: 24500,
    recent_transactions: [
        {mobile:'9876543210',operator_name:'Airtel',amount:199,status:'success',created_at:new Date(Date.now()-60000).toISOString()},
        {mobile:'8765432109',operator_name:'Jio',   amount:299,status:'success',created_at:new Date(Date.now()-180000).toISOString()},
        {mobile:'7654321098',operator_name:'BSNL',  amount:99, status:'pending',created_at:new Date(Date.now()-300000).toISOString()},
        {mobile:'9988776655',operator_name:'Vi',    amount:49, status:'failure',created_at:new Date(Date.now()-420000).toISOString()},
        {mobile:'8877665544',operator_name:'Airtel',amount:599,status:'success',created_at:new Date(Date.now()-600000).toISOString()},
        {mobile:'9765432100',operator_name:'Jio',   amount:399,status:'success',created_at:new Date(Date.now()-780000).toISOString()},
        {mobile:'8654321009',operator_name:'Idea',  amount:149,status:'success',created_at:new Date(Date.now()-900000).toISOString()},
        {mobile:'9543210098',operator_name:'BSNL',  amount:239,status:'pending',created_at:new Date(Date.now()-1080000).toISOString()},
    ]
};
const DEMO_COMPLAINTS = {
    total: 38, solved: 29, pending: 9,
    today_categories: {
        success: {count:18, amount:4500},
        failure: {count:12, amount:3100},
        pending: {count:8,  amount:1900},
    }
};

function opColor(name) {
    const key = (name || '').toUpperCase().replace(/[^A-Z]/g, '');
    for (const k of Object.keys(OPERATOR_COLORS)) {
        if (key.includes(k)) return OPERATOR_COLORS[k];
    }
    return OPERATOR_COLORS.DEFAULT;
}

// ── Charts ────────────────────────────────────────────────────────────────
let companyChart = null;
let statusChart  = null;

async function loadSummary() {
    let d = DEMO_SUMMARY;
    try {
        const res = await apiFetch('/api/v1/employee/dashboard/summary');
        if (res && res.ok) {
            const json = await res.json();
            d = json.data || json;
        }
    } catch(e) {}

    const now = new Date().toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' });
    const upd = 'Last updated: ' + now;

    // Total
    const total = d.total || d.total_recharge || d;
    document.getElementById('stat-total-count').textContent = fmtNum(total.count ?? d.total_count ?? DEMO_SUMMARY.total.count);
    document.getElementById('stat-total-count').setAttribute('data-tooltip', numToWords(total.amount ?? d.total_amount ?? DEMO_SUMMARY.total.amount));
    document.getElementById('stat-total-amt').textContent  = fmtAmt(total.amount ?? d.total_amount ?? DEMO_SUMMARY.total.amount);
    document.getElementById('stat-total-upd').textContent  = upd;

    // Success
    const succ = d.success || d.total_success || {};
    document.getElementById('stat-success-count').textContent = fmtNum(succ.count ?? d.success_count ?? DEMO_SUMMARY.success.count);
    document.getElementById('stat-success-count').setAttribute('data-tooltip', numToWords(succ.amount ?? d.success_amount ?? DEMO_SUMMARY.success.amount));
    document.getElementById('stat-success-amt').textContent   = fmtAmt(succ.amount ?? d.success_amount ?? DEMO_SUMMARY.success.amount);
    document.getElementById('stat-success-upd').textContent   = upd;

    // Pending
    const pend = d.pending || d.total_pending || {};
    document.getElementById('stat-pending-count').textContent = fmtNum(pend.count ?? d.pending_count ?? DEMO_SUMMARY.pending.count);
    document.getElementById('stat-pending-count').setAttribute('data-tooltip', numToWords(pend.amount ?? d.pending_amount ?? DEMO_SUMMARY.pending.amount));
    document.getElementById('stat-pending-amt').textContent   = fmtAmt(pend.amount ?? d.pending_amount ?? DEMO_SUMMARY.pending.amount);
    document.getElementById('stat-pending-upd').textContent   = upd;

    // Failure
    const fail = d.failure || d.total_failure || {};
    document.getElementById('stat-failure-count').textContent = fmtNum(fail.count ?? d.failure_count ?? DEMO_SUMMARY.failure.count);
    document.getElementById('stat-failure-count').setAttribute('data-tooltip', numToWords(fail.amount ?? d.failure_amount ?? DEMO_SUMMARY.failure.amount));
    document.getElementById('stat-failure-amt').textContent   = fmtAmt(fail.amount ?? d.failure_amount ?? DEMO_SUMMARY.failure.amount);
    document.getElementById('stat-failure-upd').textContent   = upd;
}

async function loadChart(type = 'hourly') {
    document.getElementById('chart-loading').style.display = 'flex';
    document.getElementById('companyChart').style.display  = 'none';
    document.getElementById('btn-hourly').classList.toggle('btn-primary', type === 'hourly');
    document.getElementById('btn-weekly').classList.toggle('btn-primary', type === 'weekly');
    document.getElementById('btn-hourly').classList.toggle('btn-outline', type !== 'hourly');
    document.getElementById('btn-weekly').classList.toggle('btn-outline', type !== 'weekly');

    let chartData = DEMO_CHART[type] || DEMO_CHART.hourly;
    try {
        const res = await apiFetch('/api/v1/employee/dashboard/chart?type=' + type);
        if (res && res.ok) {
            const json = await res.json();
            chartData = json.data?.[type] || json.data?.hourly || json.data || chartData;
        }
    } catch(e) {}

    // Build datasets from operator_breakdown or labels
    const labels   = chartData.labels || chartData.hours || chartData.dates || [];
    const datasets = [];

    if (chartData.operator_breakdown) {
        for (const [op, values] of Object.entries(chartData.operator_breakdown)) {
            const [clr] = opColor(op);
            datasets.push({
                label: op, data: values, borderColor: clr,
                backgroundColor: clr + '22', borderWidth: 2, tension: .4,
                pointRadius: 3, pointHoverRadius: 5, fill: false,
            });
        }
    } else if (chartData.datasets) {
        chartData.datasets.forEach(ds => {
            const [clr] = opColor(ds.label || '');
            datasets.push({ ...ds, borderColor: clr, backgroundColor: clr + '22', tension: .4, borderWidth: 2, pointRadius: 3, fill: false });
        });
    } else {
        // success/failure/pending arrays (demo or API)
        datasets.push({ label: 'Success', data: chartData.success || [], borderColor: '#10b981', backgroundColor: '#10b98122', tension: .4, borderWidth: 2, pointRadius: 3, fill: false });
        datasets.push({ label: 'Failure', data: chartData.failure || [], borderColor: '#ef4444', backgroundColor: '#ef444422', tension: .4, borderWidth: 2, pointRadius: 3, fill: false });
        datasets.push({ label: 'Pending', data: chartData.pending || [], borderColor: '#f59e0b', backgroundColor: '#f59e0b22', tension: .4, borderWidth: 2, pointRadius: 3, fill: false });
    }

    document.getElementById('chart-loading').style.display = 'none';
    document.getElementById('companyChart').style.display  = 'block';

    if (companyChart) companyChart.destroy();
    companyChart = new Chart(document.getElementById('companyChart'), {
        type: 'line',
        data: { labels, datasets },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                    labels: { boxWidth: 10, font: { size: 12, family: 'Inter' }, padding: 16 }
                },
                tooltip: { mode: 'index', intersect: false }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11 }, maxTicksLimit: 12 } },
                y: { grid: { color: '#f0f4f8' }, ticks: { font: { size: 11 } } }
            },
            interaction: { mode: 'index', intersect: false }
        }
    });
}

async function loadStatusChart() {
    let d = DEMO_LIVE;
    try {
        const res = await apiFetch('/api/v1/employee/dashboard/live');
        if (res && res.ok) { const json = await res.json(); d = json.data || d; }
    } catch(e) {}

    const sCount = d.today_success ?? d.success ?? 0;
    const fCount = d.today_failure ?? d.failure ?? 0;
    const pCount = d.today_pending ?? d.pending ?? 0;
    const total  = sCount + fCount + pCount;

    const sAmt = d.today_success_amount ?? d.success_amount ?? 0;
    const fAmt = d.today_failure_amount ?? d.failure_amount ?? 0;
    const pAmt = d.today_pending_amount ?? d.pending_amount ?? 0;

    document.getElementById('donut-loading').style.display = 'none';
    document.getElementById('donut-wrap').style.display    = 'flex';
    document.getElementById('donut-legend').style.display  = 'block';
    document.getElementById('donut-total').textContent     = fmtNum(total);

    // Legend
    document.getElementById('leg-success-count').textContent = fmtNum(sCount);
    document.getElementById('leg-success-amt').textContent   = fmtAmt(sAmt);
    document.getElementById('leg-failure-count').textContent = fmtNum(fCount);
    document.getElementById('leg-failure-amt').textContent   = fmtAmt(fAmt);
    document.getElementById('leg-pending-count').textContent = fmtNum(pCount);
    document.getElementById('leg-pending-amt').textContent   = fmtAmt(pAmt);

    // Live Today count in recent txn header
    document.getElementById('live-today-count').textContent = fmtNum(total);

    if (statusChart) statusChart.destroy();
    statusChart = new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Success', 'Failure', 'Pending'],
            datasets: [{
                data: [sCount, fCount, pCount],
                backgroundColor: ['#10b981', '#ef4444', '#f59e0b'],
                borderWidth: 0,
                hoverOffset: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            cutout: '72%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' ' + ctx.label + ': ' + fmtNum(ctx.raw)
                    }
                }
            }
        }
    });
}

async function loadOperators() {
    let ops = DEMO_OPERATORS;
    try {
        const res = await apiFetch('/api/v1/employee/dashboard/operators');
        if (res && res.ok) { const json = await res.json(); ops = json.data || ops; }
    } catch(e) {}

    document.getElementById('ops-loading').style.display   = 'none';
    document.getElementById('ops-table-wrap').style.display = 'block';

    const tbody = document.getElementById('ops-tbody');
    if (!ops.length) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:24px">No operator data available</td></tr>';
        return;
    }

    tbody.innerHTML = ops.map(op => {
        const [clr, bgClr] = opColor(op.name || op.operator_name || '');
        const rate = op.success_rate ?? op.successRate ?? 0;
        const rateColor = rate >= 90 ? '#10b981' : rate >= 70 ? '#f59e0b' : '#ef4444';
        const name = op.name || op.operator_name || op.code || '—';
        const avg  = op.avg_response_time ? Math.round(op.avg_response_time) + 'ms' : '—';
        return `<tr>
            <td>
                <div style="display:flex;align-items:center;gap:8px">
                    <div style="width:28px;height:28px;border-radius:6px;background:${bgClr};color:${clr};font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center">${name.charAt(0)}</div>
                    <span style="font-weight:600">${name}</span>
                </div>
            </td>
            <td style="color:#10b981;font-weight:600">${fmtNum(op.success_count ?? op.success)}</td>
            <td style="color:#ef4444;font-weight:600">${fmtNum(op.failure_count ?? op.failure)}</td>
            <td style="color:#f59e0b;font-weight:600">${fmtNum(op.pending_count ?? op.pending)}</td>
            <td>
                <div style="display:flex;align-items:center;gap:8px">
                    <div class="progress" style="flex:1;width:80px">
                        <div class="progress-bar" style="width:${Math.min(100, rate)}%;background:${rateColor}"></div>
                    </div>
                    <span style="font-size:12px;font-weight:600;color:${rateColor};min-width:36px">${Number(rate).toFixed(1)}%</span>
                </div>
            </td>
            <td style="color:var(--text-muted)">${avg}</td>
        </tr>`;
    }).join('');
}

async function loadComplaints() {
    let d = DEMO_COMPLAINTS;
    try {
        const res = await apiFetch('/api/v1/employee/dashboard/complaints');
        if (res && res.ok) { const json = await res.json(); d = json.data || d; }
    } catch(e) {}

    document.getElementById('comp-loading').style.display = 'none';
    document.getElementById('comp-content').style.display = 'block';

    document.getElementById('comp-total').textContent  = fmtNum(d.total ?? d.total_complaints);
    document.getElementById('comp-solved').textContent = fmtNum(d.solved ?? d.resolved);

    // Sidebar badge
    const pending = d.pending ?? d.pending_count ?? 0;
    const sbBadge = document.getElementById('sb-complaint-count');
    if (sbBadge) sbBadge.textContent = pending > 0 ? pending : '0';

    // Categories (today)
    const cat = d.today_categories || d.categories || {};
    document.getElementById('cat-success-count').textContent = fmtNum(cat.success?.count ?? cat.success);
    document.getElementById('cat-success-amt').textContent   = fmtAmt(cat.success?.amount);
    document.getElementById('cat-failure-count').textContent = fmtNum(cat.failure?.count ?? cat.failure);
    document.getElementById('cat-failure-amt').textContent   = fmtAmt(cat.failure?.amount);
    document.getElementById('cat-pending-count').textContent = fmtNum(cat.pending?.count ?? cat.pending);
    document.getElementById('cat-pending-amt').textContent   = fmtAmt(cat.pending?.amount);

    // Notification dot
    if (pending > 0) {
        document.getElementById('notif-dot').style.display = 'block';
    }
}

async function loadLiveFeed() {
    let d = DEMO_LIVE;
    try {
        const res = await apiFetch('/api/v1/employee/dashboard/live');
        if (res && res.ok) { const json = await res.json(); d = json.data || d; }
    } catch(e) {}

    document.getElementById('live-loading').style.display = 'none';
    document.getElementById('txn-list').style.display     = 'block';

    const txns = d.recent_transactions || d.transactions || d.feed || [];
    const list = document.getElementById('txn-list');

    if (!txns.length) {
        list.innerHTML = '<li style="padding:20px;text-align:center;color:var(--text-muted);font-size:13px">No recent transactions</li>';
        return;
    }

    list.innerHTML = txns.slice(0, 10).map(tx => {
        const [clr] = opColor(tx.operator_name || tx.operator_code || '');
        const statusClass = (tx.status || '').toLowerCase();
        const mobile = tx.mobile || tx.mobile_number || '—';
        const op     = tx.operator_name || tx.operator_code || '—';
        const amt    = tx.amount ? fmtAmt(tx.amount) : '—';
        return `<li class="txn-item">
            <div class="txn-avatar" style="background:${clr}">${(op).charAt(0)}</div>
            <div class="txn-info">
                <div class="txn-mobile">${mobile}</div>
                <div class="txn-operator">${op} · ${fmtAgo(tx.created_at)}</div>
            </div>
            <div style="text-align:right">
                <div class="txn-amount" style="color:${statusClass === 'success' ? '#10b981' : statusClass === 'failure' ? '#ef4444' : '#f59e0b'}">${amt}</div>
                <span class="txn-status ${statusClass}">${(tx.status || '—').toLowerCase()}</span>
            </div>
        </li>`;
    }).join('');
}

// ── Main load / refresh ───────────────────────────────────────────────────
async function loadAll() {
    document.getElementById('announcement').style.display = 'flex';

    await Promise.allSettled([
        loadSummary(),
        loadChart('hourly'),
        loadStatusChart(),
        loadOperators(),
        loadComplaints(),
        loadLiveFeed(),
    ]);
}

window.refreshDashboard = function() {
    const btn = document.getElementById('refresh-btn');
    btn.disabled = true;
    btn.innerHTML = `<div class="spinner" style="border-top-color:var(--accent-blue);width:14px;height:14px"></div> Refreshing…`;

    Promise.allSettled([
        loadSummary(),
        loadStatusChart(),
        loadComplaints(),
        loadLiveFeed(),
        loadOperators(),
    ]).finally(() => {
        btn.disabled = false;
        btn.innerHTML = `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:15px;height:15px"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Refresh`;
    });
};

// ── Auto-polling ──────────────────────────────────────────────────────────
// Summary: every 30s, Live: every 10s, Complaints: every 60s
setInterval(loadSummary,     30_000);
setInterval(loadStatusChart, 10_000);
setInterval(loadLiveFeed,    10_000);
setInterval(loadComplaints,  60_000);
setInterval(loadOperators,   60_000);

// ── Export stub ───────────────────────────────────────────────────────────
function exportReport() {
    window.location.href = '/admin/reports/recharges';
}

// ── Boot ──────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', loadAll);
</script>
@endpush
