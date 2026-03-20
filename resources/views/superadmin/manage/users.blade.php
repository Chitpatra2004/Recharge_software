@extends('layouts.superadmin')
@section('title', 'Manage Users')
@section('page-title', 'Manage Users')

@push('head')
<style>
.stat-grid-3 { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:20px; }
@media(max-width:800px){ .stat-grid-3{grid-template-columns:repeat(2,1fr);} }
@media(max-width:500px){ .stat-grid-3{grid-template-columns:1fr;} }
.filter-bar { display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:16px; }
</style>
@endpush

@section('content')

<div class="rh-breadcrumb">
    <a href="{{ route('superadmin.dashboard') }}">Dashboard</a>
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>Manage Users</span>
</div>

<div class="stat-grid-3">
    <div class="rh-stat">
        <div class="rh-stat-icon" style="background:linear-gradient(135deg,#4f46e5,#7c3aed)">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </div>
        <div class="rh-stat-body">
            <div class="rh-stat-label">Total Users (Sellers)</div>
            <div class="rh-stat-val">247</div>
            <div class="rh-stat-sub"><span class="rh-stat-up">↑ 12</span> this month</div>
        </div>
    </div>
    <div class="rh-stat">
        <div class="rh-stat-icon" style="background:linear-gradient(135deg,#059669,#10b981)">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="rh-stat-body">
            <div class="rh-stat-label">Active Users</div>
            <div class="rh-stat-val">219</div>
            <div class="rh-stat-sub">Logged in within 30 days</div>
        </div>
    </div>
    <div class="rh-stat">
        <div class="rh-stat-icon" style="background:linear-gradient(135deg,#d97706,#f59e0b)">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="rh-stat-body">
            <div class="rh-stat-label">Pending KYC</div>
            <div class="rh-stat-val">18</div>
            <div class="rh-stat-sub">Awaiting verification</div>
        </div>
    </div>
</div>

<div class="rh-card">
    <div class="rh-card-header">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--rh-brand)"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        <span class="rh-card-title">All Sellers / Users</span>
        <span style="margin-left:8px;font-size:11px;color:var(--rh-muted)">247 total</span>
    </div>
    <div class="rh-card-body">
        <div class="filter-bar">
            <input type="text" class="rh-input" placeholder="Search name, email, mobile…" style="flex:1;min-width:180px">
            <select class="rh-input" style="width:auto">
                <option>All Status</option>
                <option>Active</option>
                <option>Inactive</option>
                <option>Suspended</option>
                <option>KYC Pending</option>
            </select>
            <select class="rh-input" style="width:auto">
                <option>All Admins</option>
                <option>Arjun Sharma</option>
                <option>Priya Nair</option>
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
                        <th>#</th>
                        <th>Seller</th>
                        <th>Mobile</th>
                        <th>Admin</th>
                        <th>Wallet</th>
                        <th>Total Recharges</th>
                        <th>Joined</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $users = [
                        ['id'=>1,'name'=>'Ravi Telecom','mobile'=>'9876543210','admin'=>'Arjun Sharma','wallet'=>'₹12,400','recharges'=>8421,'joined'=>'15 Jan 2024','status'=>'active'],
                        ['id'=>2,'name'=>'StarConnect','mobile'=>'9823456789','admin'=>'Priya Nair','wallet'=>'₹8,900','recharges'=>6110,'joined'=>'20 Feb 2024','status'=>'active'],
                        ['id'=>3,'name'=>'MobileNation','mobile'=>'9765432198','admin'=>'Rahul Verma','wallet'=>'₹5,200','recharges'=>4880,'joined'=>'5 Mar 2024','status'=>'active'],
                        ['id'=>4,'name'=>'QuickRecharge','mobile'=>'9654321987','admin'=>'Arjun Sharma','wallet'=>'₹2,100','recharges'=>3920,'joined'=>'12 Apr 2024','status'=>'inactive'],
                        ['id'=>5,'name'=>'NetZone','mobile'=>'9543219876','admin'=>'Kiran Reddy','wallet'=>'₹450','recharges'=>980,'joined'=>'30 May 2024','status'=>'kyc'],
                    ];
                    @endphp
                    @foreach($users as $u)
                    <tr>
                        <td style="color:var(--rh-muted);font-size:12px">{{ $u['id'] }}</td>
                        <td>
                            <div style="display:flex;align-items:center;gap:9px">
                                <div style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#4f46e5,#7c3aed);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#fff;flex-shrink:0">
                                    {{ strtoupper(substr($u['name'],0,2)) }}
                                </div>
                                <span style="font-weight:600;color:var(--rh-text)">{{ $u['name'] }}</span>
                            </div>
                        </td>
                        <td>{{ $u['mobile'] }}</td>
                        <td style="color:var(--rh-muted)">{{ $u['admin'] }}</td>
                        <td style="font-weight:600">{{ $u['wallet'] }}</td>
                        <td>{{ number_format($u['recharges']) }}</td>
                        <td style="color:var(--rh-muted)">{{ $u['joined'] }}</td>
                        <td>
                            @if($u['status']==='active')
                                <span class="badge badge-green"><span class="badge-dot"></span>Active</span>
                            @elseif($u['status']==='kyc')
                                <span class="badge badge-amber"><span class="badge-dot"></span>KYC Pending</span>
                            @else
                                <span class="badge badge-gray"><span class="badge-dot"></span>Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;gap:6px">
                                <button class="btn btn-sm btn-outline" style="padding:5px 10px;font-size:11px">View</button>
                                <button class="btn btn-sm btn-danger" style="padding:5px 10px;font-size:11px">Suspend</button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{-- Pagination --}}
        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:16px;padding-top:12px;border-top:1px solid var(--rh-border)">
            <span style="font-size:12.5px;color:var(--rh-muted)">Showing 1–20 of 247 users</span>
            <div style="display:flex;gap:6px">
                <button class="btn btn-sm btn-outline" disabled>← Prev</button>
                <button class="btn btn-sm btn-primary">Next →</button>
            </div>
        </div>
    </div>
</div>

@endsection
