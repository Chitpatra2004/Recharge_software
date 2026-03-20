@extends('layouts.superadmin')
@section('title', 'Manage Operators')
@section('page-title', 'Manage Operators')

@push('head')
<style>
.op-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:20px; }
@media(max-width:900px){ .op-grid{grid-template-columns:repeat(2,1fr);} }
@media(max-width:560px){ .op-grid{grid-template-columns:1fr;} }
.op-card {
    background:var(--rh-card); border:1px solid var(--rh-border);
    border-radius:var(--rh-radius); box-shadow:var(--rh-shadow);
    padding:18px; display:flex; flex-direction:column; gap:10px;
}
.op-logo { width:44px;height:44px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:800;color:#fff; }
.op-name { font-size:14px;font-weight:700;color:var(--rh-text); }
.op-sub  { font-size:11.5px;color:var(--rh-muted); }
.op-meta { display:flex;justify-content:space-between;align-items:center;padding-top:10px;border-top:1px solid var(--rh-border); }
</style>
@endpush

@section('content')

<div class="rh-breadcrumb">
    <a href="{{ route('superadmin.dashboard') }}">Dashboard</a>
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>Manage Operators</span>
</div>

{{-- Operator Cards --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
    <h2 style="font-size:14px;font-weight:700;color:var(--rh-text)">Telecom Operators</h2>
    <button class="btn btn-sm btn-primary" onclick="document.getElementById('addOpModal').classList.add('open')">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Add Operator
    </button>
</div>

@php
$ops = [
    ['name'=>'Jio','code'=>'JIO','color'=>'linear-gradient(135deg,#4f46e5,#7c3aed)','apis'=>5,'active'=>4,'share'=>38,'status'=>'active'],
    ['name'=>'Airtel','code'=>'AIR','color'=>'linear-gradient(135deg,#dc2626,#f43f5e)','apis'=>4,'active'=>4,'share'=>28,'status'=>'active'],
    ['name'=>'Vi (Vodafone)','code'=>'VI','color'=>'linear-gradient(135deg,#d97706,#f59e0b)','apis'=>3,'active'=>2,'share'=>18,'status'=>'degraded'],
    ['name'=>'BSNL','code'=>'BSN','color'=>'linear-gradient(135deg,#059669,#10b981)','apis'=>2,'active'=>1,'share'=>10,'status'=>'degraded'],
    ['name'=>'MTNL','code'=>'MTN','color'=>'linear-gradient(135deg,#0284c7,#38bdf8)','apis'=>1,'active'=>1,'share'=>4,'status'=>'active'],
    ['name'=>'Others','code'=>'OTH','color'=>'linear-gradient(135deg,#374151,#6b7280)','apis'=>2,'active'=>2,'share'=>2,'status'=>'active'],
];
@endphp

<div class="op-grid">
    @foreach($ops as $op)
    <div class="op-card">
        <div style="display:flex;align-items:center;gap:12px">
            <div class="op-logo" style="background:{{ $op['color'] }}">{{ $op['code'] }}</div>
            <div>
                <div class="op-name">{{ $op['name'] }}</div>
                <div class="op-sub">{{ $op['apis'] }} APIs configured · {{ $op['active'] }} active</div>
            </div>
        </div>
        <div style="background:var(--rh-page);border-radius:var(--rh-radius-sm);padding:10px 12px;display:flex;justify-content:space-between;align-items:center">
            <div>
                <div style="font-size:10.5px;color:var(--rh-muted);font-weight:600;text-transform:uppercase;letter-spacing:.05em">Traffic Share</div>
                <div style="font-size:20px;font-weight:800;color:var(--rh-text)">{{ $op['share'] }}%</div>
            </div>
            <div style="width:60px;height:60px">
                <svg viewBox="0 0 36 36">
                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="#f3f4f6" stroke-width="3.5"/>
                    <circle cx="18" cy="18" r="15.9" fill="none" stroke-width="3.5"
                        stroke="{{ explode(',', explode('(', $op['color'])[1])[0] }}"
                        stroke-dasharray="{{ $op['share'] }} {{ 100 - $op['share'] }}"
                        stroke-dashoffset="25"
                        stroke-linecap="round"
                        transform="rotate(-90 18 18)"/>
                </svg>
            </div>
        </div>
        <div class="op-meta">
            @if($op['status']==='active')
                <span class="badge badge-green"><span class="badge-dot"></span>Operational</span>
            @else
                <span class="badge badge-amber"><span class="badge-dot"></span>Degraded</span>
            @endif
            <div style="display:flex;gap:6px">
                <a href="{{ route('superadmin.operator-switching') }}" class="btn btn-sm btn-outline" style="font-size:11.5px;padding:5px 10px">Switch API</a>
                <button class="btn btn-sm btn-primary" style="font-size:11.5px;padding:5px 10px">Edit</button>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- All Operator APIs Table --}}
<div class="rh-card">
    <div class="rh-card-header">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--rh-brand)"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
        <span class="rh-card-title">All API Endpoints</span>
        <span class="rh-nav-badge" style="margin-left:8px">17</span>
    </div>
    <div class="rh-card-body">
        <div class="rh-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Operator</th>
                        <th>API Slot</th>
                        <th>Provider</th>
                        <th>Endpoint</th>
                        <th>Success Rate</th>
                        <th>Avg Speed</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $apis = [
                        ['op'=>'Jio','slot'=>'API-1','provider'=>'Paytm','ep'=>'api.paytm.com/recharge','success'=>'99.2%','speed'=>'1.1s','status'=>'primary'],
                        ['op'=>'Jio','slot'=>'API-2','provider'=>'Razorpay','ep'=>'api.razorpay.com/jio','success'=>'98.8%','speed'=>'1.3s','status'=>'backup'],
                        ['op'=>'Airtel','slot'=>'API-1','provider'=>'Cashfree','ep'=>'api.cashfree.com/airtel','success'=>'99.5%','speed'=>'0.9s','status'=>'primary'],
                        ['op'=>'Vi','slot'=>'API-1','provider'=>'Instamojo','ep'=>'api.instamojo.com/vi','success'=>'96.1%','speed'=>'2.1s','status'=>'primary'],
                        ['op'=>'BSNL','slot'=>'API-1','provider'=>'Custom','ep'=>'bsnl-api.rechargerhub.in','success'=>'91.4%','speed'=>'3.2s','status'=>'primary'],
                    ];
                    @endphp
                    @foreach($apis as $api)
                    <tr>
                        <td><strong>{{ $api['op'] }}</strong></td>
                        <td><span class="badge badge-blue">{{ $api['slot'] }}</span></td>
                        <td>{{ $api['provider'] }}</td>
                        <td style="font-family:monospace;font-size:12px;color:var(--rh-muted)">{{ $api['ep'] }}</td>
                        <td>
                            <span style="font-weight:700;color:{{ (float)$api['success'] >= 99 ? 'var(--rh-green)' : ((float)$api['success'] >= 97 ? 'var(--rh-amber)' : 'var(--rh-red)') }}">{{ $api['success'] }}</span>
                        </td>
                        <td>{{ $api['speed'] }}</td>
                        <td>
                            @if($api['status']==='primary')
                                <span class="badge badge-green">Primary</span>
                            @else
                                <span class="badge badge-gray">Backup</span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;gap:6px">
                                <button class="btn btn-sm btn-outline" style="padding:5px 10px;font-size:11.5px">Test</button>
                                <button class="btn btn-sm btn-outline" style="padding:5px 10px;font-size:11.5px">Edit</button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Operator Modal --}}
<div class="rh-modal-overlay" id="addOpModal">
    <div class="rh-modal">
        <div class="rh-modal-hd">
            <span class="rh-modal-title">Add Operator</span>
            <button class="rh-modal-close" onclick="document.getElementById('addOpModal').classList.remove('open')">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div style="display:flex;flex-direction:column;gap:14px">
            <div><label class="rh-label">Operator Name</label><input type="text" class="rh-input" placeholder="e.g. Jio"></div>
            <div><label class="rh-label">Operator Code</label><input type="text" class="rh-input" placeholder="e.g. JIO"></div>
            <div><label class="rh-label">Default API Endpoint</label><input type="text" class="rh-input" placeholder="https://"></div>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:6px">
                <button class="btn btn-sm btn-outline" onclick="document.getElementById('addOpModal').classList.remove('open')">Cancel</button>
                <button class="btn btn-sm btn-primary">Create Operator</button>
            </div>
        </div>
    </div>
</div>

@endsection
