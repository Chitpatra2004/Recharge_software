<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Super Admin') — RechargeHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @stack('head')
    <style>
    /* ═══════════════════════════════════════════════════════
       RECHARGERHUB SUPER ADMIN — SIGNATURE LIGHT THEME
    ═══════════════════════════════════════════════════════ */
    :root {
        --rh-brand:        #4f46e5;   /* indigo-600        */
        --rh-brand-dk:     #3730a3;   /* indigo-800        */
        --rh-brand-lt:     #eef2ff;   /* indigo-50         */
        --rh-brand-mid:    #6366f1;   /* indigo-500        */
        --rh-green:        #059669;
        --rh-red:          #dc2626;
        --rh-amber:        #d97706;
        --rh-sky:          #0284c7;
        --rh-purple:       #7c3aed;
        --rh-pink:         #db2777;

        --rh-page:         #f4f6fb;
        --rh-sidebar:      #ffffff;
        --rh-card:         #ffffff;
        --rh-border:       #e5e7eb;
        --rh-border-dk:    #d1d5db;

        --rh-text:         #111827;
        --rh-text-sub:     #374151;
        --rh-muted:        #6b7280;
        --rh-faint:        #9ca3af;

        --rh-sidebar-w:    240px;
        --rh-topbar-h:     60px;
        --rh-radius:       12px;
        --rh-radius-sm:    8px;
        --rh-shadow:       0 1px 4px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.04);
        --rh-shadow-md:    0 4px 20px rgba(0,0,0,.10);
        --rh-transition:   .2s cubic-bezier(.4,0,.2,1);
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
        background: var(--rh-page);
        color: var(--rh-text);
        font-size: 13.5px;
        line-height: 1.55;
        min-height: 100vh;
    }

    /* ─── SIDEBAR ──────────────────────────────────────── */
    .rh-sidebar {
        position: fixed;
        top: 0; left: 0; bottom: 0;
        width: var(--rh-sidebar-w);
        background: var(--rh-sidebar);
        border-right: 1px solid var(--rh-border);
        display: flex;
        flex-direction: column;
        z-index: 200;
        overflow-y: auto;
        scrollbar-width: none;
        transition: transform var(--rh-transition);
    }
    .rh-sidebar::-webkit-scrollbar { display: none; }

    /* brand */
    .rh-brand {
        padding: 0 18px;
        height: var(--rh-topbar-h);
        display: flex;
        align-items: center;
        gap: 10px;
        border-bottom: 1px solid var(--rh-border);
        flex-shrink: 0;
    }
    .rh-brand-logo {
        width: 34px; height: 34px;
        background: var(--rh-brand);
        border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .rh-brand-logo svg { width: 18px; height: 18px; color: #fff; }
    .rh-brand-name { font-size: 14.5px; font-weight: 800; color: var(--rh-text); letter-spacing: -.3px; }
    .rh-brand-name span { color: var(--rh-brand); }
    .rh-brand-badge {
        margin-left: auto;
        font-size: 9px; font-weight: 700;
        background: var(--rh-brand-lt);
        color: var(--rh-brand);
        padding: 2px 6px; border-radius: 5px;
        text-transform: uppercase; letter-spacing: .05em;
    }

    /* nav */
    .rh-nav { flex: 1; padding: 10px 10px 10px; display: flex; flex-direction: column; gap: 2px; }
    .rh-nav-section { margin-top: 10px; margin-bottom: 2px; }
    .rh-nav-section-lbl {
        font-size: 10px; font-weight: 700;
        color: var(--rh-faint);
        text-transform: uppercase;
        letter-spacing: .1em;
        padding: 4px 10px;
    }
    .rh-nav-item {
        display: flex; align-items: center; gap: 10px;
        padding: 9px 12px;
        border-radius: var(--rh-radius-sm);
        font-size: 13px; font-weight: 500;
        color: var(--rh-muted);
        text-decoration: none;
        transition: all var(--rh-transition);
        position: relative;
    }
    .rh-nav-item svg { width: 17px; height: 17px; flex-shrink: 0; transition: color var(--rh-transition); }
    .rh-nav-item:hover { background: var(--rh-brand-lt); color: var(--rh-brand); }
    .rh-nav-item:hover svg { color: var(--rh-brand); }
    .rh-nav-item.active {
        background: var(--rh-brand);
        color: #fff;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(79,70,229,.3);
    }
    .rh-nav-item.active svg { color: #fff; }
    .rh-nav-badge {
        margin-left: auto;
        font-size: 10px; font-weight: 700;
        background: rgba(79,70,229,.12);
        color: var(--rh-brand);
        padding: 1px 7px; border-radius: 99px;
        min-width: 22px; text-align: center;
    }
    .rh-nav-item.active .rh-nav-badge { background: rgba(255,255,255,.25); color: #fff; }

    /* sidebar footer */
    .rh-sidebar-footer {
        border-top: 1px solid var(--rh-border);
        padding: 12px 14px;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .rh-footer-profile {
        display: flex; align-items: center; gap: 10px;
        padding: 8px 10px;
        border-radius: var(--rh-radius-sm);
        cursor: pointer;
        transition: background var(--rh-transition);
    }
    .rh-footer-profile:hover { background: var(--rh-brand-lt); }
    .rh-avatar {
        width: 32px; height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--rh-brand), var(--rh-purple));
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 700; color: #fff;
        flex-shrink: 0;
    }
    .rh-footer-name { font-size: 12.5px; font-weight: 600; color: var(--rh-text); }
    .rh-footer-role { font-size: 10.5px; color: var(--rh-muted); }
    .rh-footer-logout {
        display: flex; align-items: center; gap: 8px;
        padding: 7px 10px;
        border-radius: var(--rh-radius-sm);
        font-size: 12.5px; font-weight: 500;
        color: var(--rh-red);
        cursor: pointer;
        transition: background var(--rh-transition);
        text-decoration: none;
    }
    .rh-footer-logout:hover { background: #fef2f2; }
    .rh-footer-logout svg { width: 15px; height: 15px; }

    /* ─── TOPBAR ──────────────────────────────────────── */
    .rh-topbar {
        position: fixed;
        top: 0;
        left: var(--rh-sidebar-w);
        right: 0;
        height: var(--rh-topbar-h);
        background: var(--rh-card);
        border-bottom: 1px solid var(--rh-border);
        display: flex;
        align-items: center;
        padding: 0 24px;
        gap: 14px;
        z-index: 100;
    }
    .rh-topbar-title {
        font-size: 15px; font-weight: 700; color: var(--rh-text);
        flex: 1;
    }
    .rh-topbar-actions { display: flex; align-items: center; gap: 8px; }
    .rh-icon-btn {
        width: 36px; height: 36px;
        border-radius: var(--rh-radius-sm);
        border: 1px solid var(--rh-border);
        background: var(--rh-page);
        display: flex; align-items: center; justify-content: center;
        cursor: pointer;
        transition: all var(--rh-transition);
        position: relative;
    }
    .rh-icon-btn svg { width: 17px; height: 17px; color: var(--rh-muted); }
    .rh-icon-btn:hover { background: var(--rh-brand-lt); border-color: var(--rh-brand); }
    .rh-icon-btn:hover svg { color: var(--rh-brand); }
    .rh-notif-dot {
        position: absolute; top: 6px; right: 6px;
        width: 7px; height: 7px;
        background: var(--rh-red);
        border-radius: 50%;
        border: 1.5px solid var(--rh-card);
    }
    .rh-topbar-divider { width: 1px; height: 24px; background: var(--rh-border); }
    .rh-topbar-user {
        display: flex; align-items: center; gap: 8px;
        padding: 4px 10px 4px 4px;
        border-radius: var(--rh-radius-sm);
        cursor: pointer;
        transition: background var(--rh-transition);
        border: 1px solid transparent;
    }
    .rh-topbar-user:hover { background: var(--rh-brand-lt); border-color: var(--rh-brand); }
    .rh-topbar-user-info { line-height: 1.3; }
    .rh-topbar-user-name { font-size: 12.5px; font-weight: 700; color: var(--rh-text); }
    .rh-topbar-user-role { font-size: 10.5px; color: var(--rh-muted); }

    /* breadcrumb */
    .rh-breadcrumb {
        display: flex; align-items: center; gap: 6px;
        font-size: 12px; color: var(--rh-muted);
        margin-bottom: 18px;
    }
    .rh-breadcrumb a { color: var(--rh-muted); text-decoration: none; transition: color var(--rh-transition); }
    .rh-breadcrumb a:hover { color: var(--rh-brand); }
    .rh-breadcrumb svg { width: 12px; height: 12px; color: var(--rh-faint); }
    .rh-breadcrumb span { color: var(--rh-brand); font-weight: 600; }

    /* ─── MAIN CONTENT ────────────────────────────────── */
    .rh-main {
        margin-left: var(--rh-sidebar-w);
        padding-top: var(--rh-topbar-h);
        min-height: 100vh;
    }
    .rh-page {
        padding: 24px;
        max-width: 1400px;
    }

    /* ─── CARDS ───────────────────────────────────────── */
    .rh-card {
        background: var(--rh-card);
        border: 1px solid var(--rh-border);
        border-radius: var(--rh-radius);
        box-shadow: var(--rh-shadow);
    }
    .rh-card-header {
        display: flex; align-items: center;
        padding: 14px 18px;
        border-bottom: 1px solid var(--rh-border);
        gap: 10px;
    }
    .rh-card-title { font-size: 13.5px; font-weight: 700; color: var(--rh-text); }
    .rh-card-body { padding: 18px; }

    /* ─── STAT CARDS ──────────────────────────────────── */
    .rh-stat {
        background: var(--rh-card);
        border: 1px solid var(--rh-border);
        border-radius: var(--rh-radius);
        box-shadow: var(--rh-shadow);
        padding: 18px;
        display: flex;
        align-items: flex-start;
        gap: 14px;
    }
    .rh-stat-icon {
        width: 44px; height: 44px;
        border-radius: 11px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .rh-stat-icon svg { width: 22px; height: 22px; color: #fff; }
    .rh-stat-body { flex: 1; min-width: 0; }
    .rh-stat-label { font-size: 11.5px; font-weight: 600; color: var(--rh-muted); margin-bottom: 4px; }
    .rh-stat-val { font-size: 22px; font-weight: 800; color: var(--rh-text); letter-spacing: -.5px; line-height: 1; }
    .rh-stat-sub { font-size: 11px; color: var(--rh-muted); margin-top: 5px; display: flex; align-items: center; gap: 4px; }
    .rh-stat-up { color: var(--rh-green); }
    .rh-stat-dn { color: var(--rh-red); }

    /* ─── BUTTONS ─────────────────────────────────────── */
    .btn { display: inline-flex; align-items: center; gap: 6px; font-family: inherit; font-weight: 600; border: none; cursor: pointer; text-decoration: none; transition: all var(--rh-transition); white-space: nowrap; }
    .btn-sm { padding: 7px 14px; font-size: 12.5px; border-radius: var(--rh-radius-sm); }
    .btn-md { padding: 9px 18px; font-size: 13px; border-radius: var(--rh-radius-sm); }
    .btn-primary { background: var(--rh-brand); color: #fff; box-shadow: 0 2px 8px rgba(79,70,229,.3); }
    .btn-primary:hover { background: var(--rh-brand-dk); }
    .btn-outline { background: transparent; color: var(--rh-text-sub); border: 1px solid var(--rh-border); }
    .btn-outline:hover { border-color: var(--rh-brand); color: var(--rh-brand); background: var(--rh-brand-lt); }
    .btn-danger { background: var(--rh-red); color: #fff; }
    .btn-danger:hover { background: #b91c1c; }
    .btn-green { background: var(--rh-green); color: #fff; }
    .btn-green:hover { background: #047857; }
    .btn svg { width: 14px; height: 14px; }

    /* ─── TABLE ───────────────────────────────────────── */
    .rh-table-wrap { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; }
    th { padding: 10px 14px; text-align: left; font-size: 11px; font-weight: 700; color: var(--rh-muted); text-transform: uppercase; letter-spacing: .06em; background: #f9fafb; border-bottom: 1px solid var(--rh-border); }
    td { padding: 11px 14px; font-size: 13px; color: var(--rh-text-sub); border-bottom: 1px solid var(--rh-border); }
    tr:last-child td { border-bottom: none; }
    tbody tr:hover { background: #fafafa; }

    /* ─── BADGES ──────────────────────────────────────── */
    .badge { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 700; padding: 2px 9px; border-radius: 99px; }
    .badge-green   { background: #d1fae5; color: #065f46; }
    .badge-red     { background: #fee2e2; color: #991b1b; }
    .badge-amber   { background: #fef3c7; color: #92400e; }
    .badge-blue    { background: #dbeafe; color: #1e40af; }
    .badge-purple  { background: #ede9fe; color: #5b21b6; }
    .badge-gray    { background: #f3f4f6; color: #374151; }
    .badge-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }

    /* ─── FORM ────────────────────────────────────────── */
    .rh-input {
        width: 100%; padding: 8px 12px;
        border: 1px solid var(--rh-border);
        border-radius: var(--rh-radius-sm);
        font-family: inherit; font-size: 13px; color: var(--rh-text);
        background: #fff; outline: none;
        transition: border-color var(--rh-transition);
    }
    .rh-input:focus { border-color: var(--rh-brand); box-shadow: 0 0 0 3px rgba(79,70,229,.1); }
    .rh-label { display: block; font-size: 11.5px; font-weight: 700; color: var(--rh-muted); margin-bottom: 5px; text-transform: uppercase; letter-spacing: .05em; }

    /* ─── ALERT BAR ───────────────────────────────────── */
    .rh-alert { display: flex; align-items: flex-start; gap: 10px; padding: 12px 16px; border-radius: var(--rh-radius-sm); font-size: 13px; margin-bottom: 18px; }
    .rh-alert svg { width: 16px; height: 16px; flex-shrink: 0; margin-top: 1px; }
    .rh-alert-info  { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }
    .rh-alert-warn  { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }
    .rh-alert-ok    { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }
    .rh-alert-err   { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }

    /* ─── TOGGLE ──────────────────────────────────────── */
    .rh-toggle-wrap { position: relative; display: inline-flex; align-items: center; }
    .rh-toggle-input { opacity: 0; width: 0; height: 0; position: absolute; }
    .rh-toggle {
        width: 42px; height: 24px;
        background: var(--rh-border-dk);
        border-radius: 12px;
        cursor: pointer;
        position: relative;
        transition: background var(--rh-transition);
        display: block;
    }
    .rh-toggle::after {
        content: '';
        position: absolute;
        width: 18px; height: 18px;
        border-radius: 50%;
        background: #fff;
        top: 3px; left: 3px;
        transition: transform var(--rh-transition);
        box-shadow: 0 1px 4px rgba(0,0,0,.2);
    }
    .rh-toggle-input:checked + .rh-toggle { background: var(--rh-brand); }
    .rh-toggle-input:checked + .rh-toggle::after { transform: translateX(18px); }

    /* ─── MODAL ───────────────────────────────────────── */
    .rh-modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.35); z-index: 500; align-items: center; justify-content: center; backdrop-filter: blur(2px); }
    .rh-modal-overlay.open { display: flex; }
    .rh-modal { background: var(--rh-card); border-radius: var(--rh-radius); padding: 24px; width: 500px; max-width: 95vw; box-shadow: var(--rh-shadow-md); animation: fadeUp .2s ease; }
    .rh-modal-hd { display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; }
    .rh-modal-title { font-size: 15px; font-weight: 800; }
    .rh-modal-close { background: none; border: none; cursor: pointer; color: var(--rh-muted); padding: 4px; border-radius: 6px; }
    .rh-modal-close:hover { background: var(--rh-brand-lt); color: var(--rh-brand); }
    .rh-modal-close svg { width: 18px; height: 18px; }

    /* ─── LOADING ─────────────────────────────────────── */
    .rh-spinner { width: 18px; height: 18px; border: 2px solid var(--rh-brand-lt); border-top-color: var(--rh-brand); border-radius: 50%; animation: spin .7s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }
    @keyframes fadeUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

    /* ─── MOBILE ──────────────────────────────────────── */
    @media (max-width: 768px) {
        .rh-sidebar { transform: translateX(-100%); }
        .rh-sidebar.open { transform: translateX(0); box-shadow: var(--rh-shadow-md); }
        .rh-main, .rh-topbar { margin-left: 0; left: 0; }
    }

    /* ─── NATIVE FORM CONTROLS — light mode (superadmin is light) ─── */
    select,
    input[type="date"],
    input[type="time"],
    input[type="datetime-local"],
    input[type="month"],
    input[type="week"] { color-scheme: light; }
    select option { background: #ffffff; color: #111827; }
    </style>
</head>
<body>

<!-- ═══════════ SIDEBAR ═══════════ -->
<aside class="rh-sidebar" id="rhSidebar">

    <!-- Brand -->
    <div class="rh-brand">
        <div class="rh-brand-logo">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <div class="rh-brand-name">Recharge<span>Hub</span></div>
        <span class="rh-brand-badge">SA</span>
    </div>

    <!-- Navigation -->
    <nav class="rh-nav">

        <!-- Main -->
        <div class="rh-nav-section">
            <div class="rh-nav-section-lbl">Main</div>
            <a href="{{ route('superadmin.dashboard') }}" class="rh-nav-item {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                Dashboard
            </a>
        </div>

        <!-- Management -->
        <div class="rh-nav-section">
            <div class="rh-nav-section-lbl">Management</div>
            <a href="{{ route('superadmin.admins') }}" class="rh-nav-item {{ request()->routeIs('superadmin.admins') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Admins
            </a>
            <a href="{{ route('superadmin.operators') }}" class="rh-nav-item {{ request()->routeIs('superadmin.operators') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                Operators
            </a>
            <a href="{{ route('superadmin.users') }}" class="rh-nav-item {{ request()->routeIs('superadmin.users') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Users
            </a>
        </div>

        <!-- Seller Tools (new) -->
        <div class="rh-nav-section">
            <div class="rh-nav-section-lbl">Seller Tools</div>
            <a href="{{ route('superadmin.seller-api-config') }}" class="rh-nav-item {{ request()->routeIs('superadmin.seller-api-config') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                Seller API Config
                <span class="rh-nav-badge">New</span>
            </a>
            <a href="{{ route('superadmin.operator-switching') }}" class="rh-nav-item {{ request()->routeIs('superadmin.operator-switching') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                Operator Switching
                <span class="rh-nav-badge">New</span>
            </a>
        </div>

        <!-- Finance -->
        <div class="rh-nav-section">
            <div class="rh-nav-section-lbl">Finance</div>
            <a href="{{ route('superadmin.revenue') }}" class="rh-nav-item {{ request()->routeIs('superadmin.revenue') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Revenue
            </a>
            <a href="{{ route('superadmin.commissions') }}" class="rh-nav-item {{ request()->routeIs('superadmin.commissions') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                Commissions
            </a>
            <a href="{{ route('superadmin.wallets') }}" class="rh-nav-item {{ request()->routeIs('superadmin.wallets') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                Wallets
            </a>
        </div>

        <!-- System -->
        <div class="rh-nav-section">
            <div class="rh-nav-section-lbl">System</div>
            <a href="{{ route('superadmin.api-gateway') }}" class="rh-nav-item {{ request()->routeIs('superadmin.api-gateway') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                API Gateway
            </a>
            <a href="{{ route('superadmin.vendor-apis') }}" class="rh-nav-item {{ request()->routeIs('superadmin.vendor-apis') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/></svg>
                Vendor APIs
                <span class="rh-nav-badge">New</span>
            </a>
            <a href="{{ route('superadmin.broadcast') }}" class="rh-nav-item {{ request()->routeIs('superadmin.broadcast') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                Broadcast
            </a>
            <a href="{{ route('superadmin.audit') }}" class="rh-nav-item {{ request()->routeIs('superadmin.audit') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Audit Log
            </a>
            <a href="{{ route('superadmin.security') }}" class="rh-nav-item {{ request()->routeIs('superadmin.security') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                Security
            </a>
        </div>

        <!-- Config -->
        <div class="rh-nav-section">
            <div class="rh-nav-section-lbl">Config</div>
            <a href="{{ route('superadmin.settings') }}" class="rh-nav-item {{ request()->routeIs('superadmin.settings') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Settings
            </a>
            <a href="{{ route('superadmin.access-control') }}" class="rh-nav-item {{ request()->routeIs('superadmin.access-control') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                Access Control
            </a>
        </div>

    </nav>

    <!-- Footer -->
    <div class="rh-sidebar-footer">
        <div class="rh-footer-profile">
            <div class="rh-avatar">SA</div>
            <div>
                <div class="rh-footer-name">Super Admin</div>
                <div class="rh-footer-role">Full Access</div>
            </div>
        </div>
        <a href="{{ route('superadmin.login') }}" class="rh-footer-logout">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            Sign Out
        </a>
    </div>
</aside>

<!-- ═══════════ TOPBAR ═══════════ -->
<header class="rh-topbar">
    <!-- Mobile toggle -->
    <button onclick="document.getElementById('rhSidebar').classList.toggle('open')" style="background:none;border:none;cursor:pointer;padding:4px;display:none" id="mobileMenuBtn">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;color:var(--rh-muted)"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>

    <div class="rh-topbar-title">@yield('page-title', 'Dashboard')</div>

    <div class="rh-topbar-actions">
        <!-- Search -->
        <div style="position:relative">
            <input type="text" placeholder="Quick search…" style="padding:7px 12px 7px 34px;border:1px solid var(--rh-border);border-radius:var(--rh-radius-sm);font-family:inherit;font-size:12.5px;background:var(--rh-page);color:var(--rh-text);outline:none;width:180px" onfocus="this.style.borderColor='var(--rh-brand)';this.style.width='220px'" onblur="this.style.borderColor='';this.style.width='180px'">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="position:absolute;left:9px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:var(--rh-faint)"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>

        <!-- Notifications -->
        <div class="rh-icon-btn" title="Notifications">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            <span class="rh-notif-dot"></span>
        </div>

        <!-- Help -->
        <div class="rh-icon-btn" title="Help">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>

        <div class="rh-topbar-divider"></div>

        <!-- User -->
        <div class="rh-topbar-user">
            <div class="rh-avatar" style="width:30px;height:30px;font-size:11px">SA</div>
            <div class="rh-topbar-user-info">
                <div class="rh-topbar-user-name">Super Admin</div>
                <div class="rh-topbar-user-role">Full Access</div>
            </div>
        </div>
    </div>
</header>

<!-- ═══════════ MAIN ═══════════ -->
<main class="rh-main">
    <div class="rh-page">
        @yield('content')
    </div>
</main>

@stack('scripts')
<script>
// Mobile sidebar
const mobileBtn = document.getElementById('mobileMenuBtn');
if (window.innerWidth <= 768) mobileBtn.style.display = 'flex';
window.addEventListener('resize', () => {
    mobileBtn.style.display = window.innerWidth <= 768 ? 'flex' : 'none';
});
document.addEventListener('click', e => {
    const sb = document.getElementById('rhSidebar');
    if (sb.classList.contains('open') && !sb.contains(e.target) && e.target !== mobileBtn) {
        sb.classList.remove('open');
    }
});
</script>
</body>
</html>
