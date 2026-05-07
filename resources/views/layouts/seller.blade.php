<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Seller Portal') — ColdPay</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --sidebar-bg: #0f1729;
            --sidebar-hover: #1a2540;
            --sidebar-active: #2563eb;
            --sidebar-text: #94a3b8;
            --sidebar-width: 230px;
            --blue: #2563eb;
            --green: #10b981;
            --orange: #f59e0b;
            --red: #ef4444;
            --purple: #7c3aed;
            --bg: #f1f5f9;
            --card: #ffffff;
            --text: #1e293b;
            --muted: #64748b;
            --border: #e2e8f0;
            --shadow: 0 1px 3px rgba(0,0,0,.08);
            --radius: 12px;
        }
        body { font-family:'Inter',sans-serif; background:var(--bg); color:var(--text); min-height:100vh; display:flex; font-size:14px; }

        /* ── Sidebar ── */
        .sidebar { width:var(--sidebar-width); min-height:100vh; background:var(--sidebar-bg); display:flex; flex-direction:column; position:fixed; top:0; left:0; bottom:0; z-index:100; overflow-y:auto; scrollbar-width:none; }
        .sidebar::-webkit-scrollbar { display:none; }
        .sb-brand { padding:20px 20px 16px; border-bottom:1px solid rgba(255,255,255,.07); display:flex; align-items:center; gap:10px; }
        .sb-brand-icon { width:36px; height:36px; background:var(--blue); border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .sb-brand-icon svg { width:20px; height:20px; color:#fff; }
        .sb-brand-name { font-size:14px; font-weight:700; color:#fff; }
        .sb-brand-sub { font-size:10px; color:#64748b; text-transform:uppercase; letter-spacing:.6px; }
        .sb-balance { margin:12px 16px; background:rgba(37,99,235,.15); border:1px solid rgba(37,99,235,.3); border-radius:10px; padding:10px 14px; }
        .sb-balance-label { font-size:10px; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:.5px; margin-bottom:2px; }
        .sb-balance-amount { font-size:18px; font-weight:700; color:#60a5fa; }
        .sb-nav { padding:8px 0; flex:1; }
        .nav-section { padding:12px 20px 4px; font-size:9.5px; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:#475569; }
        .nav-item { display:flex; align-items:center; gap:10px; padding:9px 20px; color:var(--sidebar-text); text-decoration:none; font-size:13px; font-weight:500; transition:all .15s; position:relative; cursor:pointer; border:none; background:none; width:100%; text-align:left; }
        .nav-item:hover { background:var(--sidebar-hover); color:#fff; }
        .nav-item.active { background:var(--sidebar-hover); color:#fff; }
        .nav-item.active::before { content:''; position:absolute; left:0; top:6px; bottom:6px; width:3px; background:var(--blue); border-radius:0 3px 3px 0; }
        .nav-item svg { width:16px; height:16px; flex-shrink:0; opacity:.7; }
        .nav-item.active svg, .nav-item:hover svg { opacity:1; }
        .nav-badge { margin-left:auto; background:var(--orange); color:#fff; font-size:10px; font-weight:700; padding:1px 7px; border-radius:20px; }
        .nav-badge.blue { background:var(--blue); }
        .nav-badge.green { background:var(--green); }
        .nav-chevron { margin-left:auto; width:14px; height:14px; opacity:.5; transition:transform .2s; }
        .nav-item.open .nav-chevron { transform:rotate(90deg); }
        .nav-submenu { display:none; background:rgba(0,0,0,.15); }
        .nav-submenu.open { display:block; }
        .nav-submenu .nav-item { padding-left:44px; font-size:12.5px; }
        .sb-footer { padding:14px 16px; border-top:1px solid rgba(255,255,255,.07); }
        .sb-user { display:flex; align-items:center; gap:10px; }
        .sb-avatar { width:34px; height:34px; border-radius:50%; background:linear-gradient(135deg,var(--blue),var(--purple)); display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:700; color:#fff; flex-shrink:0; }
        .sb-user-info { flex:1; min-width:0; }
        .sb-user-name { font-size:12.5px; font-weight:600; color:#fff; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .sb-user-role { font-size:11px; color:#64748b; }
        .sb-logout { background:none; border:none; cursor:pointer; color:#64748b; padding:4px; border-radius:6px; transition:color .15s; }
        .sb-logout:hover { color:var(--red); }
        .sb-logout svg { width:16px; height:16px; }

        /* ── Main ── */
        .main { margin-left:var(--sidebar-width); flex:1; min-height:100vh; display:flex; flex-direction:column; }
        .topbar { background:#fff; border-bottom:1px solid var(--border); padding:0 28px; height:54px; display:flex; align-items:center; gap:12px; position:sticky; top:0; z-index:50; }
        .topbar-title { font-size:15px; font-weight:600; color:var(--text); flex:1; }
        .topbar-btn { width:34px; height:34px; border:1px solid var(--border); border-radius:8px; background:none; cursor:pointer; display:flex; align-items:center; justify-content:center; color:var(--muted); transition:all .15s; }
        .topbar-btn:hover { background:var(--bg); color:var(--text); }
        .topbar-btn svg { width:16px; height:16px; }
        .page-body { padding:24px 28px; flex:1; }

        /* ── Components ── */
        .card { background:var(--card); border-radius:var(--radius); box-shadow:var(--shadow); border:1px solid var(--border); }
        .card-header { padding:16px 20px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:10px; }
        .card-title { font-size:14px; font-weight:600; }
        .card-body { padding:20px; }
        .card-footer { padding:12px 20px; border-top:1px solid var(--border); display:flex; align-items:center; }

        .stats-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:20px; }
        .stat-card { background:var(--card); border-radius:var(--radius); padding:18px 20px; border:1px solid var(--border); box-shadow:var(--shadow); }
        .stat-label { font-size:12px; font-weight:500; color:var(--muted); margin-bottom:8px; display:flex; align-items:center; gap:6px; }
        .stat-label svg { width:14px; height:14px; }
        .stat-value { font-size:24px; font-weight:700; letter-spacing:-.5px; }
        .stat-sub { font-size:12px; color:var(--muted); margin-top:4px; }
        .stat-card.blue .stat-value { color:var(--blue); }
        .stat-card.green .stat-value { color:var(--green); }
        .stat-card.orange .stat-value { color:var(--orange); }
        .stat-card.red .stat-value { color:var(--red); }

        .breadcrumb { display:flex; align-items:center; gap:6px; font-size:12px; color:var(--muted); margin-bottom:20px; }
        .breadcrumb a { color:var(--blue); text-decoration:none; font-weight:500; }
        .breadcrumb svg { width:12px; height:12px; }

        .btn { display:inline-flex; align-items:center; gap:6px; padding:8px 16px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; border:none; transition:all .15s; text-decoration:none; font-family:inherit; }
        .btn svg { width:14px; height:14px; }
        .btn-primary { background:var(--blue); color:#fff; }
        .btn-primary:hover { background:#1d4ed8; }
        .btn-outline { background:#fff; color:var(--text); border:1px solid var(--border); }
        .btn-outline:hover { background:var(--bg); }
        .btn-sm { padding:6px 12px; font-size:12px; }
        .btn-danger { background:rgba(239,68,68,.1); color:var(--red); border:1px solid rgba(239,68,68,.2); }
        .btn-danger:hover { background:rgba(239,68,68,.2); }
        .btn-success { background:rgba(16,185,129,.1); color:var(--green); border:1px solid rgba(16,185,129,.2); }

        .table-wrap { overflow-x:auto; }
        table { width:100%; border-collapse:collapse; }
        thead th { padding:10px 14px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.6px; color:var(--muted); background:var(--bg); border-bottom:1px solid var(--border); }
        tbody td { padding:12px 14px; font-size:13px; color:var(--text); border-bottom:1px solid var(--border); }
        tbody tr:last-child td { border-bottom:none; }
        tbody tr:hover td { background:#f8faff; }

        .badge { display:inline-flex; align-items:center; font-size:10px; font-weight:600; padding:2px 8px; border-radius:20px; }
        .badge-success    { background:#d1fae5; color:#059669; }
        .badge-failed     { background:#fee2e2; color:#dc2626; }
        .badge-refunded   { background:#fee2e2; color:#dc2626; }
        .badge-pending    { background:#fef3c7; color:#d97706; }
        .badge-queued     { background:#fef3c7; color:#d97706; }
        .badge-approved   { background:#d1fae5; color:#059669; }
        .badge-rejected   { background:#fee2e2; color:#dc2626; }
        .badge-processing { background:#e0e7ff; color:#4338ca; }
        .badge-info       { background:#dbeafe; color:#1d4ed8; }

        .spinner { width:18px; height:18px; border:2px solid var(--border); border-top-color:var(--blue); border-radius:50%; animation:spin .7s linear infinite; }
        @keyframes spin { to { transform:rotate(360deg); } }
        .loading { display:flex; align-items:center; justify-content:center; padding:40px; gap:10px; color:var(--muted); font-size:13px; }

        .filters { display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-bottom:16px; }
        .finput { border:1px solid var(--border); border-radius:8px; padding:7px 12px; font-size:13px; font-family:inherit; background:#fff; color:var(--text); outline:none; }
        .finput:focus { border-color:var(--blue); }

        .alert { padding:12px 16px; border-radius:10px; font-size:13px; display:flex; align-items:flex-start; gap:10px; margin-bottom:16px; }
        .alert svg { width:16px; height:16px; flex-shrink:0; margin-top:1px; }
        .alert-warning { background:#fef3c7; border:1px solid #fde68a; color:#92400e; }
        .alert-info    { background:#dbeafe; border:1px solid #bfdbfe; color:#1e40af; }
        .alert-success { background:#d1fae5; border:1px solid #a7f3d0; color:#065f46; }
        .alert-danger  { background:#fee2e2; border:1px solid #fecaca; color:#991b1b; }

        .code-box { background:#1e293b; color:#e2e8f0; border-radius:8px; padding:14px 16px; font-family:monospace; font-size:12.5px; position:relative; overflow-x:auto; }
        .copy-btn { position:absolute; top:8px; right:8px; background:rgba(255,255,255,.1); border:none; color:#94a3b8; border-radius:6px; padding:4px 10px; font-size:11px; cursor:pointer; transition:all .15s; }
        .copy-btn:hover { background:rgba(255,255,255,.2); color:#fff; }

        /* Modal */
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:500; align-items:center; justify-content:center; padding:20px; }
        .modal-overlay.open, .modal-overlay.show { display:flex; }
        .modal, .modal-box { background:#fff; border-radius:var(--radius); width:100%; max-width:500px; box-shadow:0 20px 60px rgba(0,0,0,.2); max-height:90vh; overflow-y:auto; padding:28px; position:relative; }
        .modal-head { padding:20px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
        .modal-title { font-size:15px; font-weight:700; }
        .modal-close { position:absolute; top:16px; right:16px; background:none; border:none; cursor:pointer; color:var(--muted); padding:4px; border-radius:6px; line-height:0; }
        .modal-close:hover { color:var(--text); background:var(--bg); }
        .modal-close svg { width:18px; height:18px; }
        .modal-body { padding:20px; }
        .modal-footer { padding:16px 20px; border-top:1px solid var(--border); display:flex; gap:10px; justify-content:flex-end; }
        .form-control { width:100%; border:1.5px solid #e5e7eb; border-radius:9px; padding:9px 12px; font-size:13.5px; font-family:inherit; background:#fff; color:var(--text); outline:none; transition:border-color .15s; }
        .form-control:focus { border-color:var(--blue); }
        .page-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:12px; }
        .page-title { font-size:20px; font-weight:700; color:var(--text); }
        .page-sub { font-size:13px; color:var(--muted); margin-top:3px; }
        .stat-icon { width:44px; height:44px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .stat-icon svg { width:22px; height:22px; }
        .stat-card { display:flex; align-items:center; gap:14px; }

        .form-group { margin-bottom:16px; }
        .form-label { font-size:12px; font-weight:600; color:var(--muted); display:block; margin-bottom:5px; }
        .form-input { width:100%; border:1px solid var(--border); border-radius:8px; padding:9px 12px; font-size:13px; font-family:inherit; background:#fff; color:var(--text); outline:none; }
        .form-input:focus { border-color:var(--blue); }
        select.form-input { cursor:pointer; }
        textarea.form-input { resize:vertical; min-height:80px; }

        @media(max-width:768px) {
            .sidebar { transform:translateX(-100%); transition:transform .25s ease; }
            .sidebar.open { transform:none; box-shadow:4px 0 20px rgba(0,0,0,.4); }
            .main { margin-left:0; }
            .stats-grid { grid-template-columns:repeat(2,1fr); }
            .page-body { padding:16px; }
            .topbar { padding:0 16px; }
            .page-header { flex-direction:column; align-items:flex-start; gap:10px; }
            #menu-toggle { display:flex !important; }
        }
        @media(max-width:480px) {
            .stats-grid { grid-template-columns:1fr; }
            .modal-overlay { padding:10px; }
        }
        .sb-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:99; }
        .sb-overlay.show { display:block; }
        ::-webkit-scrollbar { width:5px; height:5px; } ::-webkit-scrollbar-track { background:transparent; } ::-webkit-scrollbar-thumb { background:var(--border); border-radius:10px; }

        /* ── Dropdowns — always light theme for seller portal ── */
        select,
        input[type="date"],
        input[type="time"],
        input[type="datetime-local"],
        input[type="month"],
        input[type="week"] {
            color-scheme: light;
            background: #ffffff;
            color: var(--text, #1e293b);
            border-color: var(--border, #e2e8f0);
        }
        select option { background: #ffffff; color: #1e293b; }
    </style>
    @stack('head')
</head>
<body>

<aside class="sidebar" id="sidebar">
    <div class="sb-brand">
        <div class="sb-brand-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <div>
            <div class="sb-brand-name">ColdPay</div>
            <div class="sb-brand-sub">Seller Portal</div>
        </div>
    </div>

    <div class="sb-balance">
        <div class="sb-balance-label">Wallet Balance</div>
        <div class="sb-balance-amount" id="sb-balance">₹—</div>
    </div>

    <nav class="sb-nav">
        <div class="nav-section">Overview</div>
        <a href="/seller/dashboard" class="nav-item {{ request()->is('seller/dashboard') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Dashboard
        </a>
        <a href="/seller/documents" class="nav-item {{ request()->is('seller/documents') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            My Documents
        </a>
        <a href="/seller/api-tools" class="nav-item {{ request()->is('seller/api-tools') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
            API Tools
        </a>

        <div class="nav-section">Transactions</div>
        <a href="/seller/sales" class="nav-item {{ request()->is('seller/sales') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            Sales Transactions
        </a>
        <a href="/seller/sales?status=pending" class="nav-item {{ request()->is('seller/sales') && request('status') === 'pending' ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Pending Recharges
            <span class="nav-badge blue" id="sb-pending-recharges" style="display:none">0</span>
        </a>

        <div class="nav-section">Reports</div>
        <button class="nav-item {{ request()->is('seller/reports*') ? 'open' : '' }}" onclick="toggleSub('rep-sub',this)">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Reports
            <svg class="nav-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </button>
        <div class="nav-submenu {{ request()->is('seller/reports*') ? 'open' : '' }}" id="rep-sub">
            <a href="/seller/reports/account"  class="nav-item {{ request()->is('seller/reports/account')  ? 'active' : '' }}">Account Report</a>
            <a href="/seller/reports/topup"    class="nav-item {{ request()->is('seller/reports/topup')    ? 'active' : '' }}">Topup Report</a>
            <a href="/seller/reports/operator" class="nav-item {{ request()->is('seller/reports/operator') ? 'active' : '' }}">Operator Report</a>
            <a href="/seller/reports/ledger"   class="nav-item {{ request()->is('seller/reports/ledger')   ? 'active' : '' }}">Account Ledger</a>
            <a href="/seller/reports/my-commission" class="nav-item {{ request()->is('seller/reports/my-commission') ? 'active' : '' }}">My Commission</a>
            <a href="/seller/reports/api-configuration" class="nav-item {{ request()->is('seller/reports/api-configuration') || request()->is('seller/api-setting') || request()->is('seller/api-config') ? 'active' : '' }}" style="border-top:1px solid rgba(255,255,255,.07);margin-top:4px">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                API Configuration
            </a>
        </div>

        <div class="nav-section">Finance</div>
        <a href="/seller/payments" class="nav-item {{ request()->is('seller/payments') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            Payment Requests
            <span class="nav-badge" id="sb-pending-payments" style="display:none">0</span>
        </a>
        <a href="/seller/gst" class="nav-item {{ request()->is('seller/gst') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            GST Invoices
        </a>
    </nav>

    <div class="sb-footer">
        <div class="sb-user">
            <div class="sb-avatar" id="sb-avatar">—</div>
            <div class="sb-user-info">
                <div class="sb-user-name" id="sb-name">Loading…</div>
                <div class="sb-user-role">API Seller</div>
            </div>
            <button class="sb-logout" onclick="doLogout()" title="Logout">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            </button>
        </div>
    </div>
</aside>

<div class="sb-overlay" id="sb-overlay" onclick="toggleSidebar()"></div>
<div class="main">
    <header class="topbar">
        <button class="topbar-btn" onclick="toggleSidebar()" id="menu-toggle" style="display:none">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <div class="topbar-title">@yield('page-title','Dashboard')</div>
        <div style="display:flex;gap:8px;align-items:center">
            <div style="font-size:12px;color:var(--muted);background:var(--bg);padding:4px 12px;border-radius:6px;border:1px solid var(--border)" id="clock">—</div>
        </div>
    </header>

    <div class="page-body">
        @yield('content')
    </div>
</div>

<script>
const SELLER_TOKEN_KEY = 'seller_token';
const SELLER_USER_KEY  = 'seller_user';
const SELLER_USER_FALLBACK_KEY = 'seller_data';
const SELLER_IMPERSONATE_KEY = 'rh_seller_impersonate_token';

/* ── Shortcuts ── */
const el = id => document.getElementById(id);
function getToken() { return localStorage.getItem(SELLER_TOKEN_KEY); }
function getUser()  {
    try {
        const primary = localStorage.getItem(SELLER_USER_KEY);
        if (primary) return JSON.parse(primary);

        const fallback = localStorage.getItem(SELLER_USER_FALLBACK_KEY);
        return fallback ? JSON.parse(fallback) : {};
    } catch {
        return {};
    }
}
function requireAuth() { if (!getToken()) { window.location.href = '/seller/login'; return false; } return true; }

/* ── Admin impersonation: consume token sent from admin panel ── */
(function () {
    function applySellerImpersonation(payload) {
        if (!payload || !payload.token) return;
        localStorage.setItem(SELLER_TOKEN_KEY, payload.token);
        if (payload.user) {
            const userJson = JSON.stringify(payload.user);
            localStorage.setItem(SELLER_USER_KEY, userJson);
            localStorage.setItem(SELLER_USER_FALLBACK_KEY, userJson);
        }
        window.location.reload();
    }

    try {
        window.addEventListener('message', function (e) {
            if (e.origin !== window.location.origin) return;
            if (e.data && e.data.type === 'rh_seller_impersonate' && e.data.token) {
                applySellerImpersonation(e.data);
            }
        });

        const raw = localStorage.getItem(SELLER_IMPERSONATE_KEY);
        if (!raw) return;

        const payload = JSON.parse(raw);
        if (payload && payload.token && payload.exp > Date.now()) {
            localStorage.removeItem(SELLER_IMPERSONATE_KEY);
            localStorage.setItem(SELLER_TOKEN_KEY, payload.token);
            if (payload.user) {
                const userJson = JSON.stringify(payload.user);
                localStorage.setItem(SELLER_USER_KEY, userJson);
                localStorage.setItem(SELLER_USER_FALLBACK_KEY, userJson);
            }
        } else {
            localStorage.removeItem(SELLER_IMPERSONATE_KEY);
        }
    } catch (_) {}
})();

/* ── apiFetch — returns parsed JSON or throws ── */
async function apiFetch(url, options = {}) {
    const token = getToken();
    const res = await fetch(url, {
        ...options,
        headers: {
            'Authorization': 'Bearer ' + token,
            'Accept': 'application/json',
            ...(options.body && !(options.body instanceof FormData) ? { 'Content-Type': 'application/json' } : {}),
            ...(options.headers || {}),
        },
    });
    if (res.status === 401) { window.location.href = '/seller/login'; throw new Error('Unauthenticated'); }
    const data = await res.json().catch(() => ({}));
    if (res.status === 403) { if (data.status === 'pending') window.location.href = '/seller/login'; throw new Error(data.message || 'Forbidden'); }
    if (!res.ok) throw new Error(data.message || 'Request failed (' + res.status + ')');
    return data;
}

/* ── Helpers ── */
function fmtMoney(n) { return Number(n||0).toLocaleString('en-IN', {minimumFractionDigits:2, maximumFractionDigits:2}); }
function fmtDate(s)  { if (!s) return '—'; const d = new Date(s); return d.toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'})+' '+d.toLocaleTimeString('en-IN',{hour:'2-digit',minute:'2-digit'}); }
function statusBadge(s) { const m={success:'badge-success',failed:'badge-failed',refunded:'badge-refunded',pending:'badge-pending',queued:'badge-queued',processing:'badge-processing',approved:'badge-approved',rejected:'badge-rejected'}; return `<span class="badge ${m[s]||'badge-info'}">${(s||'').replace(/_/g,' ')}</span>`; }

/* ── Sidebar submenu ── */
function toggleSub(id, btn) {
    const sub = el(id);
    if (!sub) return;
    sub.classList.toggle('open');
    btn.classList.toggle('open');
}

/* ── Mobile sidebar toggle ── */
function toggleSidebar() {
    const sb = el('sidebar'), ov = el('sb-overlay');
    const open = sb.classList.toggle('open');
    if (ov) ov.classList.toggle('show', open);
}

/* ── Session lock ── */
// 30 min inactivity → lock. No auto-logout; user must enter password to unlock.
function closeOpenSellerModal() {
    document.querySelectorAll('.modal-overlay.open, .modal-overlay.show').forEach(modal => {
        modal.classList.remove('open', 'show');
    });
}

document.addEventListener('click', e => {
    const modal = e.target.closest?.('.modal-overlay');
    if (modal && e.target === modal) {
        modal.classList.remove('open', 'show');
    }
});

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closeOpenSellerModal();
    }
});

const SELLER_LOCK_SESSION_KEY = 'seller_screen_locked';
const SELLER_LOCK_AFTER_MS    = 30 * 60 * 1000;
let _sLockTimer = null;
let _sIsLocked  = false;

function initSellerSession() {
    ['mousemove','mousedown','keydown','touchstart','scroll','click'].forEach(e =>
        document.addEventListener(e, resetSellerIdleTimers, { passive: true })
    );
    if (sessionStorage.getItem(SELLER_LOCK_SESSION_KEY)) {
        _sIsLocked = false;
        sellerLockScreen();
    } else {
        resetSellerIdleTimers();
    }
}

function resetSellerIdleTimers() {
    if (_sIsLocked) return;
    clearTimeout(_sLockTimer);
    _sLockTimer = setTimeout(sellerLockScreen, SELLER_LOCK_AFTER_MS);
}

function sellerLockScreen() {
    if (_sIsLocked) return;
    _sIsLocked = true;
    clearTimeout(_sLockTimer);
    sessionStorage.setItem(SELLER_LOCK_SESSION_KEY, '1');
    const u = getUser();
    const lockOverlay = el('seller-lock-overlay');
    if (!lockOverlay) return;
    el('seller-lock-name').textContent   = u.name  || 'Seller';
    el('seller-lock-email').textContent  = u.email || '';
    el('seller-lock-role').textContent   = (u.role || 'api_user').replace(/_/g,' ').toUpperCase();
    el('seller-lock-avatar').textContent = (u.name || 'S').charAt(0).toUpperCase();
    el('seller-lock-password').value     = '';
    el('seller-lock-error').style.display = 'none';
    lockOverlay.style.display = 'flex';
    el('seller-lock-password').focus();
    const cd = el('seller-lock-countdown');
    if (cd) cd.textContent = '';
}

function _sellerSessionLogout() {
    sessionStorage.removeItem(SELLER_LOCK_SESSION_KEY);
    _sIsLocked = false;
    localStorage.removeItem(SELLER_TOKEN_KEY);
    localStorage.removeItem(SELLER_USER_KEY);
    localStorage.removeItem(SELLER_USER_FALLBACK_KEY);
    window.location.href = '/seller/login';
}

async function sellerUnlockScreen() {
    const pwd   = el('seller-lock-password').value;
    const errEl = el('seller-lock-error');
    const btn   = el('seller-lock-unlock-btn');
    errEl.style.display = 'none';
    if (!pwd) { errEl.textContent = 'Enter your password to unlock.'; errEl.style.display = 'block'; return; }
    btn.disabled = true;
    btn.textContent = 'Verifying…';
    const u = getUser();
    try {
        const res = await fetch('/api/v1/seller/auth/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ email: u.email, password: pwd, device_name: 'web' })
        });
        if (res.ok) {
            const data = await res.json();
            if (data.token) localStorage.setItem(SELLER_TOKEN_KEY, data.token);
            if (data.user) { localStorage.setItem(SELLER_USER_KEY, JSON.stringify(data.user)); localStorage.setItem(SELLER_USER_FALLBACK_KEY, JSON.stringify(data.user)); }
            sessionStorage.removeItem(SELLER_LOCK_SESSION_KEY);
            el('seller-lock-overlay').style.display = 'none';
            clearInterval(_sCountdownInterval);
            _sIsLocked = false;
            resetSellerIdleTimers();
        } else {
            errEl.textContent = 'Incorrect password. Please try again.';
            errEl.style.display = 'block';
        }
    } catch { errEl.textContent = 'Network error. Try again.'; errEl.style.display = 'block'; }
    btn.disabled = false;
    btn.textContent = 'Unlock';
}

/* ── Logout ── */
function doLogout() {
    if (!confirm('Logout from seller portal?')) return;
    apiFetch('/api/v1/seller/auth/logout', { method:'POST' }).catch(()=>{}).finally(() => {
        sessionStorage.removeItem(SELLER_LOCK_SESSION_KEY);
        localStorage.removeItem(SELLER_TOKEN_KEY);
        localStorage.removeItem(SELLER_USER_KEY);
        localStorage.removeItem(SELLER_USER_FALLBACK_KEY);
        window.location.href = '/seller/login';
    });
}

/* ── Init ── */
document.addEventListener('DOMContentLoaded', async () => {
    if (!requireAuth()) return;

    // Clock
    const clockEl = el('clock');
    if (clockEl) setInterval(() => { clockEl.textContent = new Date().toLocaleTimeString('en-IN',{hour:'2-digit',minute:'2-digit',second:'2-digit'}); }, 1000);

    // User info
    const u = getUser();
    if (u.name) {
        const nm = el('sb-name'), av = el('sb-avatar');
        if (nm) nm.textContent = u.name;
        if (av) av.textContent = u.name.charAt(0).toUpperCase();
    }

    // Responsive hamburger
    const checkMobile = () => { const mt = el('menu-toggle'); if (mt) mt.style.display = window.innerWidth <= 768 ? 'flex' : 'none'; };
    checkMobile();
    window.addEventListener('resize', checkMobile);

    // Sidebar wallet balance
    try {
        const d = await apiFetch('/api/v1/seller/dashboard');
        const stats = d.data?.stats || {};
        const balEl = el('sb-balance');
        const pendingRechargeEl = el('sb-pending-recharges');
        if (balEl) balEl.textContent = '₹' + fmtMoney(stats.wallet_balance);
        if (stats.pending_recharges > 0 && pendingRechargeEl) {
            pendingRechargeEl.textContent = stats.pending_recharges;
            pendingRechargeEl.style.display = 'inline-flex';
        }
        if (stats.pending_payments > 0) {
            const pbEl = el('sb-pending-payments');
            if (pbEl) { pbEl.textContent = stats.pending_payments; pbEl.style.display = 'inline-flex'; }
        }
    } catch(e) { /* silent — non-critical */ }

    initSellerSession();
});
</script>

<!-- ── SELLER LOCK SCREEN ─────────────────────────────────────────────────── -->
<div id="seller-lock-overlay" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(15,23,42,.94);backdrop-filter:blur(24px);align-items:center;justify-content:center">
    <div style="text-align:center;width:100%;max-width:340px;padding:20px">
        <div id="seller-lock-avatar" style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#2563eb,#6366f1);display:flex;align-items:center;justify-content:center;font-size:30px;font-weight:800;color:#fff;margin:0 auto 16px;box-shadow:0 8px 32px rgba(37,99,235,.4)">S</div>
        <div id="seller-lock-name"  style="font-size:18px;font-weight:700;color:#fff;margin-bottom:4px">Seller</div>
        <div id="seller-lock-role"  style="font-size:11px;font-weight:600;color:#6366f1;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px">API SELLER</div>
        <div id="seller-lock-email" style="font-size:12px;color:#64748b;margin-bottom:20px"></div>
        <div style="font-size:13px;color:#94a3b8;margin-bottom:20px">Session locked due to inactivity.</div>
        <input id="seller-lock-password" type="password" placeholder="Enter password to unlock"
               style="width:100%;padding:12px 16px;border-radius:10px;border:1px solid #334155;background:#1e293b;color:#fff;font-size:14px;outline:none;letter-spacing:2px;margin-bottom:10px"
               onkeydown="if(event.key==='Enter') sellerUnlockScreen()">
        <div id="seller-lock-error" style="display:none;font-size:12px;color:#ef4444;margin-bottom:10px"></div>
        <button id="seller-lock-unlock-btn" onclick="sellerUnlockScreen()"
                style="width:100%;padding:12px;background:linear-gradient(135deg,#2563eb,#6366f1);color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;margin-bottom:10px">Unlock</button>
        <button onclick="_sellerSessionLogout()"
                style="width:100%;padding:10px;background:transparent;color:#64748b;border:1px solid #334155;border-radius:10px;font-size:13px;cursor:pointer">Sign Out</button>
        <div id="seller-lock-countdown" style="font-size:11px;color:#475569;margin-top:16px"></div>
    </div>
</div>

@stack('scripts')
</body>
</html>
