@extends('layouts.superadmin')
@section('title', 'Wallets')
@section('page-title', 'Wallets')

@push('head')
<style>
.stat-grid-4 { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:20px; }
@media(max-width:1100px){ .stat-grid-4{grid-template-columns:repeat(2,1fr);} }
@media(max-width:560px){ .stat-grid-4{grid-template-columns:1fr;} }
.filter-bar { display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:16px; }
</style>
@endpush

@section('content')

<div class="rh-breadcrumb">
    <a href="{{ route('superadmin.dashboard') }}">Dashboard</a>
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>Wallets</span>
</div>

<div class="stat-grid-4">
    <div class="rh-stat">
        <div class="rh-stat-icon" style="background:linear-gradient(135deg,#4f46e5,#7c3aed)">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
        </div>
        <div class="rh-stat-body">
            <div class="rh-stat-label">Total Wallet Balance</div>
            <div class="rh-stat-val">₹84.2L</div>
            <div class="rh-stat-sub">Across all sellers</div>
        </div>
    </div>
    <div class="rh-stat">
        <div class="rh-stat-icon" style="background:linear-gradient(135deg,#059669,#10b981)">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        </div>
        <div class="rh-stat-body">
            <div class="rh-stat-label">Top-ups Today</div>
            <div class="rh-stat-val">₹6.8L</div>
            <div class="rh-stat-sub"><span class="rh-stat-up">↑ 14%</span> vs yesterday</div>
        </div>
    </div>
    <div class="rh-stat">
        <div class="rh-stat-icon" style="background:linear-gradient(135deg,#d97706,#f59e0b)">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="rh-stat-body">
            <div class="rh-stat-label">Pending Approvals</div>
            <div class="rh-stat-val">7</div>
            <div class="rh-stat-sub">₹2.1L pending review</div>
        </div>
    </div>
    <div class="rh-stat">
        <div class="rh-stat-icon" style="background:linear-gradient(135deg,#dc2626,#f43f5e)">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <div class="rh-stat-body">
            <div class="rh-stat-label">Low Balance Sellers</div>
            <div class="rh-stat-val">23</div>
            <div class="rh-stat-sub">Below ₹500 threshold</div>
        </div>
    </div>
</div>

{{-- Pending Top-up Approvals --}}
<div class="rh-card" style="margin-bottom:20px">
    <div class="rh-card-header">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--rh-amber)"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span class="rh-card-title">Pending Top-up Approvals</span>
        <span class="rh-nav-badge" style="margin-left:8px;background:#fef3c7;color:#92400e">7</span>
    </div>
    <div class="rh-card-body">
        <div class="rh-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Seller</th>
                        <th>Admin</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>UTR / Ref</th>
                        <th>Requested</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $pending = [
                        ['seller'=>'Ravi Telecom','admin'=>'Arjun Sharma','amount'=>'₹50,000','method'=>'NEFT','utr'=>'UTR202403200001','req'=>'10 min ago'],
                        ['seller'=>'StarConnect','admin'=>'Priya Nair','amount'=>'₹25,000','method'=>'UPI','utr'=>'TXN9823456789','req'=>'34 min ago'],
                        ['seller'=>'MobileNation','admin'=>'Rahul Verma','amount'=>'₹1,00,000','method'=>'IMPS','utr'=>'IMPS20240320XY','req'=>'1 hour ago'],
                    ];
                    @endphp
                    @foreach($pending as $p)
                    <tr>
                        <td style="font-weight:600">{{ $p['seller'] }}</td>
                        <td style="color:var(--rh-muted)">{{ $p['admin'] }}</td>
                        <td style="font-weight:700;font-size:14px;color:var(--rh-text)">{{ $p['amount'] }}</td>
                        <td><span class="badge badge-blue">{{ $p['method'] }}</span></td>
                        <td style="font-family:monospace;font-size:12px;color:var(--rh-muted)">{{ $p['utr'] }}</td>
                        <td style="color:var(--rh-muted)">{{ $p['req'] }}</td>
                        <td>
                            <div style="display:flex;gap:6px">
                                <button class="btn btn-sm btn-green" style="padding:5px 12px;font-size:11.5px">Approve</button>
                                <button class="btn btn-sm btn-danger" style="padding:5px 10px;font-size:11.5px">Reject</button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- All Wallets Table --}}
<div class="rh-card">
    <div class="rh-card-header">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--rh-brand)"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
        <span class="rh-card-title">All Seller Wallets</span>
    </div>
    <div class="rh-card-body">
        <div class="filter-bar">
            <input type="text" class="rh-input" placeholder="Search seller…" style="flex:1;min-width:160px">
            <select class="rh-input" style="width:auto">
                <option>All Balances</option>
                <option>Low Balance (&lt; ₹500)</option>
                <option>Medium (₹500–₹5K)</option>
                <option>High (&gt; ₹5K)</option>
            </select>
            <button class="btn btn-sm btn-outline">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export
            </button>
        </div>
        <div class="rh-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Seller</th>
                        <th>Admin</th>
                        <th>Balance</th>
                        <th>Last Top-up</th>
                        <th>Last Top-up Amt</th>
                        <th>Today's Usage</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $wallets = [
                        ['name'=>'Ravi Telecom','admin'=>'Arjun Sharma','bal'=>'₹12,400','last'=>'2 hours ago','lastamt'=>'₹50,000','usage'=>'₹8,200','status'=>'ok'],
                        ['name'=>'StarConnect','admin'=>'Priya Nair','bal'=>'₹8,900','last'=>'1 day ago','lastamt'=>'₹25,000','usage'=>'₹5,400','status'=>'ok'],
                        ['name'=>'MobileNation','admin'=>'Rahul Verma','bal'=>'₹5,200','last'=>'3 days ago','lastamt'=>'₹20,000','usage'=>'₹3,100','status'=>'ok'],
                        ['name'=>'QuickRecharge','admin'=>'Arjun Sharma','bal'=>'₹380','last'=>'7 days ago','lastamt'=>'₹10,000','usage'=>'₹290','status'=>'low'],
                        ['name'=>'NetZone','admin'=>'Kiran Reddy','bal'=>'₹120','last'=>'15 days ago','lastamt'=>'₹5,000','usage'=>'₹80','status'=>'low'],
                    ];
                    @endphp
                    @foreach($wallets as $w)
                    <tr>
                        <td style="font-weight:600;color:var(--rh-text)">{{ $w['name'] }}</td>
                        <td style="color:var(--rh-muted)">{{ $w['admin'] }}</td>
                        <td>
                            <span style="font-weight:800;font-size:14px;color:{{ $w['status']==='low' ? 'var(--rh-red)' : 'var(--rh-green)' }}">{{ $w['bal'] }}</span>
                        </td>
                        <td style="color:var(--rh-muted)">{{ $w['last'] }}</td>
                        <td>{{ $w['lastamt'] }}</td>
                        <td>{{ $w['usage'] }}</td>
                        <td>
                            @if($w['status']==='ok')
                                <span class="badge badge-green"><span class="badge-dot"></span>Healthy</span>
                            @else
                                <span class="badge badge-red"><span class="badge-dot"></span>Low</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-sm btn-primary" style="font-size:11.5px;padding:5px 12px">Top-up</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
