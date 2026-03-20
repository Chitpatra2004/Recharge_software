@extends('layouts.superadmin')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@push('head')
<style>
/* ─── HERO BANNER ─────────────────────────────────── */
.rh-hero {
    background: linear-gradient(120deg, #4f46e5 0%, #7c3aed 50%, #db2777 100%);
    border-radius: var(--rh-radius);
    padding: 28px 32px;
    display: flex;
    align-items: center;
    gap: 24px;
    margin-bottom: 22px;
    position: relative;
    overflow: hidden;
}
.rh-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Ccircle cx='30' cy='30' r='28'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
}
.rh-hero-text { flex: 1; position: relative; }
.rh-hero-greeting { font-size: 24px; font-weight: 800; color: #fff; letter-spacing: -.5px; margin-bottom: 4px; }
.rh-hero-sub { font-size: 13.5px; color: rgba(255,255,255,.75); }
.rh-hero-actions { display: flex; gap: 10px; margin-top: 18px; }
.rh-hero-btn {
    padding: 8px 18px;
    border-radius: var(--rh-radius-sm);
    font-size: 13px; font-weight: 600;
    cursor: pointer; border: none;
    display: inline-flex; align-items: center; gap: 6px;
    text-decoration: none;
    transition: all var(--rh-transition);
}
.rh-hero-btn-white { background: #fff; color: var(--rh-brand); }
.rh-hero-btn-white:hover { background: #f0f0ff; }
.rh-hero-btn-ghost { background: rgba(255,255,255,.15); color: #fff; border: 1px solid rgba(255,255,255,.3); }
.rh-hero-btn-ghost:hover { background: rgba(255,255,255,.25); }
.rh-hero-btn svg { width: 14px; height: 14px; }
.rh-hero-clock { position: relative; z-index: 1; text-align: right; flex-shrink: 0; }
.rh-hero-time { font-size: 30px; font-weight: 800; color: #fff; font-variant-numeric: tabular-nums; letter-spacing: -1px; }
.rh-hero-date { font-size: 12.5px; color: rgba(255,255,255,.7); margin-top: 2px; }
.rh-hero-deco {
    position: absolute; right: 200px; top: -20px;
    width: 200px; height: 160px;
    opacity: .08;
}
.rh-hero-announce {
    display: flex; align-items: center; gap: 10px;
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.2);
    border-radius: var(--rh-radius-sm);
    padding: 9px 14px;
    margin-bottom: 18px;
    font-size: 13px; color: #fff;
    position: relative;
}
.rh-hero-announce svg { width: 16px; height: 16px; flex-shrink: 0; }
.rh-hero-announce-close { margin-left: auto; cursor: pointer; opacity: .7; background: none; border: none; color: #fff; }

/* ─── STAT GRID ───────────────────────────────────── */
.stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 20px; }
@media (max-width: 1100px) { .stat-grid { grid-template-columns: repeat(2,1fr); } }
@media (max-width: 600px)  { .stat-grid { grid-template-columns: 1fr; } }

/* ─── CHART GRID ──────────────────────────────────── */
.chart-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 20px; }
@media (max-width: 900px) { .chart-grid { grid-template-columns: 1fr; } }

/* ─── ACTIVITY & QUICK ACTIONS ───────────────────── */
.bottom-grid { display: grid; grid-template-columns: 1fr 340px; gap: 16px; }
@media (max-width: 900px) { .bottom-grid { grid-template-columns: 1fr; } }

/* ─── QUICK ACTION TILES ─────────────────────────── */
.qa-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.qa-tile {
    display: flex; align-items: center; gap: 10px;
    padding: 12px 14px;
    border-radius: var(--rh-radius-sm);
    border: 1px solid var(--rh-border);
    cursor: pointer; text-decoration: none;
    transition: all var(--rh-transition);
    background: var(--rh-card);
}
.qa-tile:hover { border-color: var(--rh-brand); background: var(--rh-brand-lt); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(79,70,229,.12); }
.qa-tile-icon { width: 36px; height: 36px; border-radius: 9px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.qa-tile-icon svg { width: 18px; height: 18px; color: #fff; }
.qa-tile-lbl { font-size: 12.5px; font-weight: 600; color: var(--rh-text); }
.qa-tile-sub { font-size: 11px; color: var(--rh-muted); }

/* ─── ACTIVITY FEED ──────────────────────────────── */
.act-item { display: flex; align-items: flex-start; gap: 12px; padding: 12px 0; border-bottom: 1px solid var(--rh-border); }
.act-item:last-child { border-bottom: none; }
.act-dot { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px; }
.act-dot svg { width: 15px; height: 15px; color: #fff; }
.act-text { font-size: 13px; color: var(--rh-text-sub); line-height: 1.4; }
.act-text strong { color: var(--rh-text); font-weight: 600; }
.act-time { font-size: 11px; color: var(--rh-muted); margin-top: 3px; }

/* ─── TOP SELLERS TABLE ──────────────────────────── */
.seller-row { display: flex; align-items: center; gap: 10px; padding: 9px 0; border-bottom: 1px solid var(--rh-border); }
.seller-row:last-child { border-bottom: none; }
.seller-rank { width: 20px; font-size: 11px; font-weight: 700; color: var(--rh-muted); text-align: center; flex-shrink: 0; }
.seller-avatar { width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; color: #fff; flex-shrink: 0; }
.seller-name { font-size: 13px; font-weight: 600; color: var(--rh-text); }
.seller-vol { font-size: 11px; color: var(--rh-muted); }
.seller-amount { margin-left: auto; font-size: 13px; font-weight: 700; color: var(--rh-text); }
</style>
@endpush

@section('content')

{{-- Hero --}}
<div class="rh-hero">
    {{-- Announcement inside hero --}}
    <div style="flex:1">
        <div id="heroAnnounce" style="display:flex;align-items:center;gap:10px;background:rgba(255,255,255,.13);border:1px solid rgba(255,255,255,.22);border-radius:var(--rh-radius-sm);padding:9px 14px;margin-bottom:16px;font-size:13px;color:#fff">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
            <span><strong>System Notice:</strong> Seller API Config & Operator Switching modules are now live.</span>
            <button onclick="document.getElementById('heroAnnounce').remove()" style="margin-left:auto;background:none;border:none;color:rgba(255,255,255,.7);cursor:pointer;font-size:18px;line-height:1">×</button>
        </div>
        <div class="rh-hero-greeting" id="heroGreeting">Good Morning, RechargeHub Admin!</div>
        <div class="rh-hero-sub">Here's your command overview for today — <span id="heroDate"></span></div>
        <div class="rh-hero-actions">
            <a href="{{ route('superadmin.seller-api-config') }}" class="rh-hero-btn rh-hero-btn-white">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                Seller API Config
            </a>
            <a href="{{ route('superadmin.operator-switching') }}" class="rh-hero-btn rh-hero-btn-ghost">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                Operator Switching
            </a>
        </div>
    </div>
    <div class="rh-hero-clock">
        <div class="rh-hero-time" id="heroClock">--:--</div>
        <div class="rh-hero-date" id="heroClockDate"></div>
        <div style="margin-top:10px;display:flex;justify-content:flex-end">
            <span id="systemStatus" style="display:inline-flex;align-items:center;gap:6px;background:rgba(16,185,129,.2);border:1px solid rgba(16,185,129,.35);border-radius:99px;padding:4px 12px;font-size:11.5px;font-weight:600;color:#6ee7b7">
                <span style="width:6px;height:6px;border-radius:50%;background:#10b981;display:inline-block"></span>
                All Systems Operational
            </span>
        </div>
    </div>
</div>

{{-- Stat Cards --}}
<div class="stat-grid">
    <div class="rh-stat">
        <div class="rh-stat-icon" style="background:linear-gradient(135deg,#4f46e5,#7c3aed)">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </div>
        <div class="rh-stat-body">
            <div class="rh-stat-label">Total Sellers</div>
            <div class="rh-stat-val" id="s-sellers">—</div>
            <div class="rh-stat-sub"><span class="rh-stat-up">↑ 3</span> added this week</div>
        </div>
    </div>
    <div class="rh-stat">
        <div class="rh-stat-icon" style="background:linear-gradient(135deg,#059669,#10b981)">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="rh-stat-body">
            <div class="rh-stat-label">Today's Revenue</div>
            <div class="rh-stat-val" id="s-revenue">—</div>
            <div class="rh-stat-sub"><span class="rh-stat-up">↑ 12%</span> vs yesterday</div>
        </div>
    </div>
    <div class="rh-stat">
        <div class="rh-stat-icon" style="background:linear-gradient(135deg,#d97706,#f59e0b)">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <div class="rh-stat-body">
            <div class="rh-stat-label">Recharges Today</div>
            <div class="rh-stat-val" id="s-recharges">—</div>
            <div class="rh-stat-sub"><span class="rh-stat-dn">↓ 2%</span> vs yesterday</div>
        </div>
    </div>
    <div class="rh-stat">
        <div class="rh-stat-icon" style="background:linear-gradient(135deg,#dc2626,#f43f5e)">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <div class="rh-stat-body">
            <div class="rh-stat-label">Pending Complaints</div>
            <div class="rh-stat-val" id="s-complaints">—</div>
            <div class="rh-stat-sub"><span class="rh-stat-up">↑ resolved 5</span> today</div>
        </div>
    </div>
</div>

{{-- Charts --}}
<div class="chart-grid">
    <div class="rh-card">
        <div class="rh-card-header">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--rh-brand)"><path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
            <span class="rh-card-title">Revenue & Recharges — Last 14 Days</span>
            <div style="margin-left:auto;display:flex;gap:14px">
                <span style="font-size:11.5px;color:var(--rh-muted);display:flex;align-items:center;gap:5px"><span style="width:10px;height:3px;background:var(--rh-brand);border-radius:2px;display:inline-block"></span>Revenue</span>
                <span style="font-size:11.5px;color:var(--rh-muted);display:flex;align-items:center;gap:5px"><span style="width:10px;height:3px;background:#10b981;border-radius:2px;display:inline-block"></span>Recharges</span>
            </div>
        </div>
        <div style="padding:18px"><canvas id="revenueChart" height="95"></canvas></div>
    </div>
    <div class="rh-card">
        <div class="rh-card-header">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--rh-purple)"><path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
            <span class="rh-card-title">Operator Share</span>
        </div>
        <div style="padding:18px;display:flex;flex-direction:column;align-items:center;gap:16px">
            <canvas id="operatorChart" width="160" height="160"></canvas>
            <div style="width:100%;display:flex;flex-direction:column;gap:6px" id="operatorLegend"></div>
        </div>
    </div>
</div>

{{-- Bottom Grid --}}
<div class="bottom-grid">

    {{-- Recent Activity --}}
    <div class="rh-card">
        <div class="rh-card-header">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--rh-sky)"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="rh-card-title">Recent Activity</span>
            <a href="{{ route('superadmin.audit') }}" style="margin-left:auto;font-size:12px;color:var(--rh-brand);text-decoration:none;font-weight:600">View all →</a>
        </div>
        <div style="padding:4px 18px 14px">
            <div class="act-item">
                <div class="act-dot" style="background:linear-gradient(135deg,#4f46e5,#7c3aed)"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg></div>
                <div><div class="act-text"><strong>Ravi Telecom</strong> added new API key for Jio operator</div><div class="act-time">2 minutes ago</div></div>
            </div>
            <div class="act-item">
                <div class="act-dot" style="background:linear-gradient(135deg,#8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg></div>
                <div><div class="act-text"><strong>Operator Switch:</strong> Airtel Primary switched from API-2 to API-5 by SuperAdmin</div><div class="act-time">18 minutes ago</div></div>
            </div>
            <div class="act-item">
                <div class="act-dot" style="background:linear-gradient(135deg,#059669,#10b981)"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                <div><div class="act-text"><strong>3,241 recharges</strong> processed successfully this morning</div><div class="act-time">1 hour ago</div></div>
            </div>
            <div class="act-item">
                <div class="act-dot" style="background:linear-gradient(135deg,#d97706,#f59e0b)"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg></div>
                <div><div class="act-text"><strong>₹18,500</strong> wallet top-up approved for seller <strong>StarConnect</strong></div><div class="act-time">2 hours ago</div></div>
            </div>
            <div class="act-item">
                <div class="act-dot" style="background:linear-gradient(135deg,#dc2626,#f43f5e)"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></div>
                <div><div class="act-text"><strong>API Failure Alert:</strong> BSNL API-3 returned 503 — auto-fallback activated</div><div class="act-time">3 hours ago</div></div>
            </div>
        </div>
    </div>

    {{-- Right Column --}}
    <div style="display:flex;flex-direction:column;gap:16px">

        {{-- Quick Actions --}}
        <div class="rh-card">
            <div class="rh-card-header">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--rh-brand)"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span class="rh-card-title">Quick Actions</span>
            </div>
            <div style="padding:14px">
                <div class="qa-grid">
                    <a href="{{ route('superadmin.seller-api-config') }}" class="qa-tile">
                        <div class="qa-tile-icon" style="background:linear-gradient(135deg,#4f46e5,#7c3aed)">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        </div>
                        <div><div class="qa-tile-lbl">Seller API</div><div class="qa-tile-sub">Configure</div></div>
                    </a>
                    <a href="{{ route('superadmin.operator-switching') }}" class="qa-tile">
                        <div class="qa-tile-icon" style="background:linear-gradient(135deg,#db2777,#f43f5e)">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        </div>
                        <div><div class="qa-tile-lbl">API Switch</div><div class="qa-tile-sub">Operators</div></div>
                    </a>
                    <a href="{{ route('superadmin.admins') }}" class="qa-tile">
                        <div class="qa-tile-icon" style="background:linear-gradient(135deg,#059669,#10b981)">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        </div>
                        <div><div class="qa-tile-lbl">Add Admin</div><div class="qa-tile-sub">Manage</div></div>
                    </a>
                    <a href="{{ route('superadmin.broadcast') }}" class="qa-tile">
                        <div class="qa-tile-icon" style="background:linear-gradient(135deg,#d97706,#f59e0b)">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                        </div>
                        <div><div class="qa-tile-lbl">Broadcast</div><div class="qa-tile-sub">Announce</div></div>
                    </a>
                    <a href="{{ route('superadmin.wallets') }}" class="qa-tile">
                        <div class="qa-tile-icon" style="background:linear-gradient(135deg,#0284c7,#38bdf8)">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        </div>
                        <div><div class="qa-tile-lbl">Wallets</div><div class="qa-tile-sub">Finance</div></div>
                    </a>
                    <a href="{{ route('superadmin.security') }}" class="qa-tile">
                        <div class="qa-tile-icon" style="background:linear-gradient(135deg,#374151,#6b7280)">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <div><div class="qa-tile-lbl">Security</div><div class="qa-tile-sub">Settings</div></div>
                    </a>
                </div>
            </div>
        </div>

        {{-- Top Sellers --}}
        <div class="rh-card">
            <div class="rh-card-header">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--rh-amber)"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span class="rh-card-title">Top Sellers Today</span>
            </div>
            <div style="padding:6px 18px 14px">
                <div class="seller-row">
                    <span class="seller-rank">1</span>
                    <div class="seller-avatar" style="background:linear-gradient(135deg,#4f46e5,#7c3aed)">RV</div>
                    <div><div class="seller-name">Ravi Telecom</div><div class="seller-vol">842 recharges</div></div>
                    <span class="seller-amount">₹1,24,300</span>
                </div>
                <div class="seller-row">
                    <span class="seller-rank">2</span>
                    <div class="seller-avatar" style="background:linear-gradient(135deg,#059669,#10b981)">SC</div>
                    <div><div class="seller-name">StarConnect</div><div class="seller-vol">611 recharges</div></div>
                    <span class="seller-amount">₹98,700</span>
                </div>
                <div class="seller-row">
                    <span class="seller-rank">3</span>
                    <div class="seller-avatar" style="background:linear-gradient(135deg,#d97706,#f59e0b)">MN</div>
                    <div><div class="seller-name">MobileNation</div><div class="seller-vol">488 recharges</div></div>
                    <span class="seller-amount">₹76,450</span>
                </div>
                <div class="seller-row">
                    <span class="seller-rank">4</span>
                    <div class="seller-avatar" style="background:linear-gradient(135deg,#0284c7,#38bdf8)">QR</div>
                    <div><div class="seller-name">QuickRecharge</div><div class="seller-vol">392 recharges</div></div>
                    <span class="seller-amount">₹61,200</span>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Clock ─────────────────────────────────────────
function updateClock() {
    const now = new Date();
    const h = now.getHours(), m = now.getMinutes();
    const ampm = h >= 12 ? 'PM' : 'AM';
    const hh = h % 12 || 12;
    document.getElementById('heroClock').textContent = `${hh}:${String(m).padStart(2,'0')} ${ampm}`;
    const days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    document.getElementById('heroClockDate').textContent = `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
    document.getElementById('heroDate').textContent = `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;

    // greeting
    const g = h < 12 ? 'Good Morning' : h < 17 ? 'Good Afternoon' : 'Good Evening';
    document.getElementById('heroGreeting').textContent = `${g}, RechargeHub Admin!`;
}
updateClock(); setInterval(updateClock, 1000);

// ── Fake Stats ────────────────────────────────────
setTimeout(() => {
    document.getElementById('s-sellers').textContent = '247';
    document.getElementById('s-revenue').textContent = '₹4.2L';
    document.getElementById('s-recharges').textContent = '8,341';
    document.getElementById('s-complaints').textContent = '12';
}, 400);

// ── Revenue Chart ──────────────────────────────────
const labels = Array.from({length:14}, (_,i) => {
    const d = new Date(); d.setDate(d.getDate() - (13 - i));
    return d.toLocaleDateString('en-IN',{day:'2-digit',month:'short'});
});
new Chart(document.getElementById('revenueChart'), {
    type: 'line',
    data: {
        labels,
        datasets: [
            {
                label: 'Revenue (₹K)',
                data: [210,195,280,320,290,340,380,310,420,395,450,410,480,520],
                borderColor: '#4f46e5', backgroundColor: 'rgba(79,70,229,.08)',
                tension: .4, fill: true, pointRadius: 3, pointBackgroundColor: '#4f46e5',
            },
            {
                label: 'Recharges',
                data: [620,580,810,950,870,1020,1100,890,1230,1150,1320,1200,1400,1520],
                borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,.06)',
                tension: .4, fill: true, pointRadius: 3, pointBackgroundColor: '#10b981',
                yAxisID: 'y2',
            }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: true,
        interaction: { mode: 'index', intersect: false },
        plugins: { legend: { display: false }, tooltip: { backgroundColor: '#1e293b', cornerRadius: 8, padding: 10 } },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 10 }, color: '#9ca3af' } },
            y: { grid: { color: '#f3f4f6' }, ticks: { font: { size: 10 }, color: '#9ca3af', callback: v => '₹'+v+'K' } },
            y2: { position: 'right', grid: { display: false }, ticks: { font: { size: 10 }, color: '#9ca3af' } }
        }
    }
});

// ── Operator Donut ────────────────────────────────
const opData = [
    { name: 'Jio', value: 38, color: '#4f46e5' },
    { name: 'Airtel', value: 28, color: '#dc2626' },
    { name: 'Vi', value: 18, color: '#d97706' },
    { name: 'BSNL', value: 10, color: '#059669' },
    { name: 'Others', value: 6, color: '#9ca3af' },
];
new Chart(document.getElementById('operatorChart'), {
    type: 'doughnut',
    data: {
        labels: opData.map(o => o.name),
        datasets: [{ data: opData.map(o => o.value), backgroundColor: opData.map(o => o.color), borderWidth: 2, borderColor: '#fff', hoverOffset: 6 }]
    },
    options: { responsive: false, cutout: '68%', plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => ` ${c.label}: ${c.parsed}%` } } } }
});
const leg = document.getElementById('operatorLegend');
opData.forEach(o => {
    leg.innerHTML += `<div style="display:flex;align-items:center;justify-content:space-between;font-size:12px">
        <span style="display:flex;align-items:center;gap:7px"><span style="width:10px;height:10px;border-radius:3px;background:${o.color};display:inline-block"></span>${o.name}</span>
        <strong style="color:var(--rh-text)">${o.value}%</strong>
    </div>`;
});
</script>
@endpush
