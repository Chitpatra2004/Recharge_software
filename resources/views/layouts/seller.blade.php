<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Seller Portal') — RechargeHub</title>
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
        .badge-success { background:#d1fae5; color:#059669; }
        .badge-failed  { background:#fee2e2; color:#dc2626; }
        .badge-pending { background:#fef3c7; color:#d97706; }
        .badge-approved{ background:#d1fae5; color:#059669; }
        .badge-rejected{ background:#fee2e2; color:#dc2626; }
        .badge-processing { background:#e0e7ff; color:#4338ca; }
        .badge-info    { background:#dbeafe; color:#1d4ed8; }

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
        .modal-overlay.open { display:flex; }
        .modal { background:#fff; border-radius:var(--radius); width:100%; max-width:500px; box-shadow:0 20px 60px rgba(0,0,0,.2); }
        .modal-head { padding:20px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
        .modal-title { font-size:15px; font-weight:700; }
        .modal-close { background:none; border:none; cursor:pointer; color:var(--muted); padding:4px; border-radius:6px; }
        .modal-close:hover { color:var(--text); }
        .modal-close svg { width:18px; height:18px; }
        .modal-body { padding:20px; }
        .modal-footer { padding:16px 20px; border-top:1px solid var(--border); display:flex; gap:10px; justify-content:flex-end; }

        .form-group { margin-bottom:16px; }
        .form-label { font-size:12px; font-weight:600; color:var(--muted); display:block; margin-bottom:5px; }
        .form-input { width:100%; border:1px solid var(--border); border-radius:8px; padding:9px 12px; font-size:13px; font-family:inherit; background:#fff; color:var(--text); outline:none; }
        .form-input:focus { border-color:var(--blue); }
        select.form-input { cursor:pointer; }
        textarea.form-input { resize:vertical; min-height:80px; }

        @media(max-width:768px) { .sidebar { transform:translateX(-100%); } .sidebar.open { transform:none; } .main { margin-left:0; } .stats-grid { grid-template-columns:repeat(2,1fr); } .page-body { padding:16px; } }
        ::-webkit-scrollbar { width:5px; height:5px; } ::-webkit-scrollbar-track { background:transparent; } ::-webkit-scrollbar-thumb { background:var(--border); border-radius:10px; }
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
            <div class="sb-brand-name">RechargeHub</div>
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

        <div class="nav-section">API Setup</div>
        <a href="/seller/api-config" class="nav-item {{ request()->is('seller/api-config') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
            API Configuration
        </a>

        <div class="nav-section">Transactions</div>
        <a href="/seller/sales" class="nav-item {{ request()->is('seller/sales') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            Sales Transactions
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

<div class="main">
    <header class="topbar">
        <button class="topbar-btn" onclick="document.getElementById('sidebar').classList.toggle('open')" style="display:none" id="menu-toggle">
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

function getToken()  { return localStorage.getItem(SELLER_TOKEN_KEY); }
function getUser()   { try { return JSON.parse(localStorage.getItem(SELLER_USER_KEY)||'{}'); } catch { return {}; } }

function requireAuth() {
    if (!getToken()) { window.location.href = '/seller/login'; return false; }
    return true;
}

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
    if (res.status === 401) { window.location.href = '/seller/login'; return null; }
    if (res.status === 403) {
        const d = await res.json().catch(() => ({}));
        if (d.status === 'pending') window.location.href = '/seller/login';
        return null;
    }
    return res;
}

function fmtMoney(n) { return '₹' + Number(n||0).toLocaleString('en-IN', {minimumFractionDigits:2,maximumFractionDigits:2}); }
function fmtDate(s)  { if (!s) return '—'; const d = new Date(s); return d.toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'})+' '+d.toLocaleTimeString('en-IN',{hour:'2-digit',minute:'2-digit'}); }
function statusBadge(s) { const m={success:'badge-success',failed:'badge-failed',pending:'badge-pending',processing:'badge-processing',approved:'badge-approved',rejected:'badge-rejected',refunded:'badge-info'}; return `<span class="badge ${m[s]||'badge-info'}">${s}</span>`; }

function toggleSub(id, btn) {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.toggle('open');
    btn.classList.toggle('open');
}

function doLogout() {
    if (!confirm('Logout from seller portal?')) return;
    apiFetch('/api/v1/seller/auth/logout', { method:'POST' }).finally(() => {
        localStorage.removeItem(SELLER_TOKEN_KEY);
        localStorage.removeItem(SELLER_USER_KEY);
        window.location.href = '/seller/login';
    });
}

// Init: auth check + load user info
document.addEventListener('DOMContentLoaded', async () => {
    if (!requireAuth()) return;

    // Clock
    const clockEl = document.getElementById('clock');
    if (clockEl) setInterval(() => { clockEl.textContent = new Date().toLocaleTimeString('en-IN',{hour:'2-digit',minute:'2-digit',second:'2-digit'}); }, 1000);

    // User info from localStorage
    const u = getUser();
    if (u.name) {
        const nm = document.getElementById('sb-name');
        const av = document.getElementById('sb-avatar');
        if (nm) nm.textContent = u.name;
        if (av) av.textContent = u.name.charAt(0).toUpperCase();
    }

    // Fetch dashboard stats for sidebar (wallet balance + pending payments)
    const res = await apiFetch('/api/v1/seller/dashboard');
    if (res?.ok) {
        const d = await res.json();
        const stats = d.data?.stats || {};
        const balEl = document.getElementById('sb-balance');
        if (balEl) balEl.textContent = fmtMoney(stats.wallet_balance);
        if (stats.pending_payments > 0) {
            const pbEl = document.getElementById('sb-pending-payments');
            if (pbEl) { pbEl.textContent = stats.pending_payments; pbEl.style.display = 'inline-flex'; }
        }
    }

    // Mobile menu toggle visibility
    if (window.innerWidth <= 768) {
        const mt = document.getElementById('menu-toggle');
        if (mt) mt.style.display = 'flex';
    }
});
</script>

@stack('scripts')
</body>
</html>
