@extends('layouts.superadmin')
@section('title', 'Security')
@section('page-title', 'Security')

@push('head')
<style>
.sec-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px; }
@media(max-width:800px){ .sec-grid{grid-template-columns:1fr;} }
.threat-row { display:flex;align-items:center;gap:12px;padding:11px 0;border-bottom:1px solid var(--rh-border); }
.threat-row:last-child { border-bottom:none; }
.threat-icon { width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.threat-icon svg { width:18px;height:18px;color:#fff; }
.sec-setting { display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--rh-border); }
.sec-setting:last-child { border-bottom:none; }
.sec-setting-info { flex:1; }
.sec-setting-lbl { font-size:13px;font-weight:600;color:var(--rh-text);margin-bottom:2px; }
.sec-setting-sub { font-size:11.5px;color:var(--rh-muted); }
</style>
@endpush

@section('content')

<div class="rh-breadcrumb">
    <a href="{{ route('superadmin.dashboard') }}">Dashboard</a>
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>Security</span>
</div>

{{-- Alert --}}
<div class="rh-alert rh-alert-warn" style="margin-bottom:20px">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
    <span><strong>Attention:</strong> 3 failed login attempts detected from IP 178.91.45.22 — auto-blocked for 30 minutes.</span>
    <button class="btn btn-sm btn-outline" style="margin-left:auto;font-size:11.5px">Review</button>
</div>

{{-- Stats --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px">
    <div class="rh-stat">
        <div class="rh-stat-icon" style="background:linear-gradient(135deg,#059669,#10b981)">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        </div>
        <div class="rh-stat-body">
            <div class="rh-stat-label">Security Score</div>
            <div class="rh-stat-val" style="color:var(--rh-green)">87/100</div>
            <div class="rh-stat-sub">Good — 3 recommendations</div>
        </div>
    </div>
    <div class="rh-stat">
        <div class="rh-stat-icon" style="background:linear-gradient(135deg,#dc2626,#f43f5e)">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
        </div>
        <div class="rh-stat-body">
            <div class="rh-stat-label">Blocked IPs</div>
            <div class="rh-stat-val">12</div>
            <div class="rh-stat-sub">Active blocks right now</div>
        </div>
    </div>
    <div class="rh-stat">
        <div class="rh-stat-icon" style="background:linear-gradient(135deg,#d97706,#f59e0b)">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        </div>
        <div class="rh-stat-body">
            <div class="rh-stat-label">Failed Logins (24h)</div>
            <div class="rh-stat-val">28</div>
            <div class="rh-stat-sub"><span class="rh-stat-dn">↑ 6</span> vs yesterday</div>
        </div>
    </div>
    <div class="rh-stat">
        <div class="rh-stat-icon" style="background:linear-gradient(135deg,#4f46e5,#7c3aed)">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
        </div>
        <div class="rh-stat-body">
            <div class="rh-stat-label">Active Sessions</div>
            <div class="rh-stat-val">43</div>
            <div class="rh-stat-sub">Admins + SuperAdmins</div>
        </div>
    </div>
</div>

<div class="sec-grid">

    {{-- Security Settings --}}
    <div class="rh-card">
        <div class="rh-card-header">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--rh-brand)"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span class="rh-card-title">Security Settings</span>
        </div>
        <div class="rh-card-body">
            @php
            $settings = [
                ['label'=>'Two-Factor Authentication (Admin)','sub'=>'Force 2FA for all admin logins','enabled'=>true],
                ['label'=>'IP Whitelist Enforcement','sub'=>'Restrict admin access to whitelisted IPs','enabled'=>false],
                ['label'=>'Auto Block on Failed Logins','sub'=>'Block IP after 5 failed attempts in 10 min','enabled'=>true],
                ['label'=>'Session Timeout (30 min)','sub'=>'Auto logout idle admin sessions','enabled'=>true],
                ['label'=>'API Request Rate Limiting','sub'=>'Limit seller API calls to 100 req/min','enabled'=>true],
                ['label'=>'Webhook Signature Validation','sub'=>'Validate HMAC signatures on all webhooks','enabled'=>true],
                ['label'=>'Admin Login Notifications','sub'=>'Email SuperAdmin on each admin login','enabled'=>false],
            ];
            @endphp
            @foreach($settings as $i=>$s)
            <div class="sec-setting">
                <div class="sec-setting-info">
                    <div class="sec-setting-lbl">{{ $s['label'] }}</div>
                    <div class="sec-setting-sub">{{ $s['sub'] }}</div>
                </div>
                <div class="rh-toggle-wrap" style="margin-left:16px;flex-shrink:0">
                    <input type="checkbox" class="rh-toggle-input" id="sec_{{ $i }}" {{ $s['enabled'] ? 'checked' : '' }}>
                    <label for="sec_{{ $i }}" class="rh-toggle"></label>
                </div>
            </div>
            @endforeach
            <div style="margin-top:14px">
                <button class="btn btn-md btn-primary">Save Security Settings</button>
            </div>
        </div>
    </div>

    {{-- Blocked IPs & Threats --}}
    <div style="display:flex;flex-direction:column;gap:16px">
        <div class="rh-card">
            <div class="rh-card-header">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--rh-red)"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                <span class="rh-card-title">Blocked IPs</span>
                <button class="btn btn-sm btn-outline" style="margin-left:auto;font-size:11px">+ Block IP</button>
            </div>
            <div class="rh-card-body">
                <div class="rh-table-wrap">
                    <table>
                        <thead>
                            <tr><th>IP Address</th><th>Reason</th><th>Blocked At</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            @php
                            $blocked = [
                                ['ip'=>'178.91.45.22','reason'=>'Failed Login x3','at'=>'2 min ago'],
                                ['ip'=>'45.33.32.156','reason'=>'Brute Force','at'=>'1 hour ago'],
                                ['ip'=>'192.0.78.24','reason'=>'Suspicious Activity','at'=>'3 hours ago'],
                                ['ip'=>'104.21.44.78','reason'=>'API Abuse','at'=>'1 day ago'],
                            ];
                            @endphp
                            @foreach($blocked as $b)
                            <tr>
                                <td style="font-family:monospace;font-size:12px">{{ $b['ip'] }}</td>
                                <td style="font-size:12px;color:var(--rh-muted)">{{ $b['reason'] }}</td>
                                <td style="font-size:11.5px;color:var(--rh-muted)">{{ $b['at'] }}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline" style="padding:4px 9px;font-size:11px;color:var(--rh-green);border-color:var(--rh-green)">Unblock</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="rh-card">
            <div class="rh-card-header">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--rh-amber)"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span class="rh-card-title">Recent Threat Events</span>
            </div>
            <div style="padding:8px 16px 14px">
                @php
                $threats = [
                    ['icon'=>'#dc2626','event'=>'Brute Force Detected','detail'=>'IP 45.33.32.156 — 12 attempts in 2 min','time'=>'1 hour ago','sev'=>'critical'],
                    ['icon'=>'#d97706','event'=>'Unusual Login Location','detail'=>'Kiran Reddy logged in from new city','time'=>'3 hours ago','sev'=>'medium'],
                    ['icon'=>'#d97706','event'=>'API Rate Limit Breached','detail'=>'Seller StarConnect exceeded 100 req/min','time'=>'5 hours ago','sev'=>'medium'],
                    ['icon'=>'#4f46e5','event'=>'Config Change','detail'=>'SuperAdmin changed session timeout to 30 min','time'=>'6 hours ago','sev'=>'info'],
                ];
                @endphp
                @foreach($threats as $t)
                <div class="threat-row">
                    <div class="threat-icon" style="background:{{ $t['icon'] }}20">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:{{ $t['icon'] }};width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div style="flex:1">
                        <div style="font-size:13px;font-weight:600;color:var(--rh-text)">{{ $t['event'] }}</div>
                        <div style="font-size:11.5px;color:var(--rh-muted)">{{ $t['detail'] }}</div>
                        <div style="font-size:11px;color:var(--rh-faint);margin-top:2px">{{ $t['time'] }}</div>
                    </div>
                    @if($t['sev']==='critical')
                        <span class="badge badge-red">Critical</span>
                    @elseif($t['sev']==='medium')
                        <span class="badge badge-amber">Medium</span>
                    @else
                        <span class="badge badge-gray">Info</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@endsection
