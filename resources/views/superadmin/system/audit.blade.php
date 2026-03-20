@extends('layouts.superadmin')
@section('title', 'Audit Log')
@section('page-title', 'Audit Log')

@push('head')
<style>
.stat-grid-3 { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:20px; }
@media(max-width:800px){ .stat-grid-3{grid-template-columns:repeat(2,1fr);} }
@media(max-width:500px){ .stat-grid-3{grid-template-columns:1fr;} }
.filter-bar { display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:16px; }
.event-dot { width:8px;height:8px;border-radius:50%;flex-shrink:0;margin-top:5px; }
</style>
@endpush

@section('content')

<div class="rh-breadcrumb">
    <a href="{{ route('superadmin.dashboard') }}">Dashboard</a>
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>Audit Log</span>
</div>

<div class="stat-grid-3">
    <div class="rh-stat">
        <div class="rh-stat-icon" style="background:linear-gradient(135deg,#4f46e5,#7c3aed)">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <div class="rh-stat-body">
            <div class="rh-stat-label">Events Today</div>
            <div class="rh-stat-val">1,284</div>
            <div class="rh-stat-sub">All user & system actions</div>
        </div>
    </div>
    <div class="rh-stat">
        <div class="rh-stat-icon" style="background:linear-gradient(135deg,#dc2626,#f43f5e)">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <div class="rh-stat-body">
            <div class="rh-stat-label">Critical Events</div>
            <div class="rh-stat-val">4</div>
            <div class="rh-stat-sub">Require attention</div>
        </div>
    </div>
    <div class="rh-stat">
        <div class="rh-stat-icon" style="background:linear-gradient(135deg,#059669,#10b981)">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        </div>
        <div class="rh-stat-body">
            <div class="rh-stat-label">Unique Actors</div>
            <div class="rh-stat-val">38</div>
            <div class="rh-stat-sub">Admins + SuperAdmins</div>
        </div>
    </div>
</div>

<div class="rh-card">
    <div class="rh-card-header">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--rh-brand)"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        <span class="rh-card-title">Audit Events</span>
        <span style="margin-left:8px;font-size:11px;color:var(--rh-muted)">Last 24 hours</span>
    </div>
    <div class="rh-card-body">
        <div class="filter-bar">
            <input type="text" class="rh-input" placeholder="Search events, actors…" style="flex:1;min-width:180px">
            <select class="rh-input" style="width:auto">
                <option>All Event Types</option>
                <option>Login / Auth</option>
                <option>API Changes</option>
                <option>User Management</option>
                <option>Finance</option>
                <option>Config Changes</option>
                <option>Critical</option>
            </select>
            <select class="rh-input" style="width:auto">
                <option>All Actors</option>
                <option>Super Admin</option>
                <option>Arjun Sharma</option>
                <option>Priya Nair</option>
            </select>
            <input type="date" class="rh-input" style="width:auto">
            <button class="btn btn-sm btn-outline">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export
            </button>
        </div>
        <div class="rh-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Actor</th>
                        <th>Role</th>
                        <th>Event</th>
                        <th>Details</th>
                        <th>IP Address</th>
                        <th>Severity</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $logs = [
                        ['time'=>'12:34:02 PM','actor'=>'Super Admin','role'=>'superadmin','event'=>'API Priority Changed','detail'=>'Jio API-1 priority set to 1 (was 2)','ip'=>'192.168.1.1','sev'=>'medium'],
                        ['time'=>'11:52:18 AM','actor'=>'Arjun Sharma','role'=>'admin','event'=>'Wallet Top-up Approved','detail'=>'₹50,000 approved for Ravi Telecom','ip'=>'103.21.58.12','sev'=>'info'],
                        ['time'=>'11:30:44 AM','actor'=>'Super Admin','role'=>'superadmin','event'=>'Broadcast Sent','detail'=>'System Maintenance Notice to 247 sellers','ip'=>'192.168.1.1','sev'=>'info'],
                        ['time'=>'10:14:09 AM','actor'=>'Priya Nair','role'=>'admin','event'=>'User Suspended','detail'=>'Seller "NetZone" account suspended','ip'=>'110.34.22.87','sev'=>'high'],
                        ['time'=>'09:45:31 AM','actor'=>'Super Admin','role'=>'superadmin','event'=>'Operator Switch','detail'=>'Airtel switched from API-2 to API-5','ip'=>'192.168.1.1','sev'=>'medium'],
                        ['time'=>'09:12:00 AM','actor'=>'Kiran Reddy','role'=>'admin','event'=>'Login','detail'=>'Successful login from Chrome/Windows','ip'=>'49.205.110.33','sev'=>'info'],
                        ['time'=>'08:31:45 AM','actor'=>'Unknown','role'=>'—','event'=>'Failed Login Attempt','detail'=>'3 failed attempts for admin@rechargerhub.in','ip'=>'178.91.45.22','sev'=>'critical'],
                    ];
                    @endphp
                    @foreach($logs as $log)
                    <tr>
                        <td style="font-family:monospace;font-size:12px;color:var(--rh-muted);white-space:nowrap">{{ $log['time'] }}</td>
                        <td style="font-weight:600;color:var(--rh-text)">{{ $log['actor'] }}</td>
                        <td>
                            <span class="badge {{ $log['role']==='superadmin' ? 'badge-purple' : ($log['role']==='admin' ? 'badge-blue' : 'badge-gray') }}">
                                {{ ucfirst($log['role']) }}
                            </span>
                        </td>
                        <td style="font-weight:500">{{ $log['event'] }}</td>
                        <td style="font-size:12px;color:var(--rh-muted);max-width:220px">{{ $log['detail'] }}</td>
                        <td style="font-family:monospace;font-size:11.5px;color:var(--rh-muted)">{{ $log['ip'] }}</td>
                        <td>
                            @if($log['sev']==='critical')
                                <span class="badge badge-red"><span class="badge-dot"></span>Critical</span>
                            @elseif($log['sev']==='high')
                                <span class="badge badge-amber"><span class="badge-dot"></span>High</span>
                            @elseif($log['sev']==='medium')
                                <span class="badge badge-blue">Medium</span>
                            @else
                                <span class="badge badge-gray">Info</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:16px;padding-top:12px;border-top:1px solid var(--rh-border)">
            <span style="font-size:12.5px;color:var(--rh-muted)">Showing 1–50 of 1,284 events</span>
            <div style="display:flex;gap:6px">
                <button class="btn btn-sm btn-outline" disabled>← Prev</button>
                <button class="btn btn-sm btn-primary">Next →</button>
            </div>
        </div>
    </div>
</div>

@endsection
