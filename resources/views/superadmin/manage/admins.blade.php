@extends('layouts.superadmin')
@section('title', 'Manage Admins')
@section('page-title', 'Manage Admins')

@push('head')
<style>
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; }
@media(max-width:700px){ .grid-2{grid-template-columns:1fr;} }
.filter-bar { display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:16px; }
.filter-bar .rh-input { width:auto; flex:1; min-width:160px; }
</style>
@endpush

@section('content')

{{-- Breadcrumb --}}
<div class="rh-breadcrumb">
    <a href="{{ route('superadmin.dashboard') }}">Dashboard</a>
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>Manage Admins</span>
</div>

{{-- Stats --}}
<div class="grid-2">
    <div class="rh-stat">
        <div class="rh-stat-icon" style="background:linear-gradient(135deg,#4f46e5,#7c3aed)">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        </div>
        <div class="rh-stat-body">
            <div class="rh-stat-label">Total Admins</div>
            <div class="rh-stat-val">14</div>
            <div class="rh-stat-sub"><span class="rh-stat-up">↑ 2</span> added this month</div>
        </div>
    </div>
    <div class="rh-stat">
        <div class="rh-stat-icon" style="background:linear-gradient(135deg,#059669,#10b981)">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </div>
        <div class="rh-stat-body">
            <div class="rh-stat-label">Active Admins</div>
            <div class="rh-stat-val">11</div>
            <div class="rh-stat-sub">3 inactive / suspended</div>
        </div>
    </div>
</div>

{{-- Table Card --}}
<div class="rh-card">
    <div class="rh-card-header">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--rh-brand)"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        <span class="rh-card-title">Admin Accounts</span>
        <div style="margin-left:auto;display:flex;gap:8px">
            <button class="btn btn-sm btn-outline" onclick="document.getElementById('addAdminModal').classList.add('open')">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Add Admin
            </button>
        </div>
    </div>
    <div class="rh-card-body">
        <div class="filter-bar">
            <input type="text" class="rh-input" placeholder="Search by name, email…" id="adminSearch">
            <select class="rh-input" style="width:auto">
                <option>All Status</option>
                <option>Active</option>
                <option>Inactive</option>
                <option>Suspended</option>
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
                        <th>Admin</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Sellers</th>
                        <th>Last Login</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="adminTableBody">
                    @php
                    $admins = [
                        ['id'=>1,'name'=>'Arjun Sharma','email'=>'arjun@rechargerhub.in','role'=>'Admin','sellers'=>42,'last'=>'2 hours ago','status'=>'active'],
                        ['id'=>2,'name'=>'Priya Nair','email'=>'priya@rechargerhub.in','role'=>'Admin','sellers'=>38,'last'=>'5 hours ago','status'=>'active'],
                        ['id'=>3,'name'=>'Rahul Verma','email'=>'rahul@rechargerhub.in','role'=>'Admin','sellers'=>29,'last'=>'1 day ago','status'=>'active'],
                        ['id'=>4,'name'=>'Sneha Patel','email'=>'sneha@rechargerhub.in','role'=>'Admin','sellers'=>17,'last'=>'3 days ago','status'=>'inactive'],
                        ['id'=>5,'name'=>'Kiran Reddy','email'=>'kiran@rechargerhub.in','role'=>'Admin','sellers'=>55,'last'=>'1 hour ago','status'=>'active'],
                    ];
                    @endphp
                    @foreach($admins as $a)
                    <tr>
                        <td style="color:var(--rh-muted);font-size:12px">{{ $a['id'] }}</td>
                        <td>
                            <div style="display:flex;align-items:center;gap:9px">
                                <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#4f46e5,#7c3aed);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;flex-shrink:0">
                                    {{ strtoupper(substr($a['name'],0,1).substr(strrchr($a['name'],' '),1,1)) }}
                                </div>
                                <span style="font-weight:600;color:var(--rh-text)">{{ $a['name'] }}</span>
                            </div>
                        </td>
                        <td>{{ $a['email'] }}</td>
                        <td><span class="badge badge-purple">{{ $a['role'] }}</span></td>
                        <td>{{ $a['sellers'] }}</td>
                        <td style="color:var(--rh-muted)">{{ $a['last'] }}</td>
                        <td>
                            @if($a['status']==='active')
                                <span class="badge badge-green"><span class="badge-dot"></span>Active</span>
                            @else
                                <span class="badge badge-gray"><span class="badge-dot"></span>Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;gap:6px">
                                <button class="btn btn-sm btn-outline" title="Edit">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button class="btn btn-sm btn-danger" title="Suspend" style="padding:7px 10px">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Admin Modal --}}
<div class="rh-modal-overlay" id="addAdminModal">
    <div class="rh-modal">
        <div class="rh-modal-hd">
            <span class="rh-modal-title">Add New Admin</span>
            <button class="rh-modal-close" onclick="document.getElementById('addAdminModal').classList.remove('open')">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div style="display:flex;flex-direction:column;gap:14px">
            <div>
                <label class="rh-label">Full Name</label>
                <input type="text" class="rh-input" placeholder="e.g. Arjun Sharma">
            </div>
            <div>
                <label class="rh-label">Email Address</label>
                <input type="email" class="rh-input" placeholder="admin@rechargerhub.in">
            </div>
            <div>
                <label class="rh-label">Password</label>
                <input type="password" class="rh-input" placeholder="Min 8 characters">
            </div>
            <div>
                <label class="rh-label">Role</label>
                <select class="rh-input">
                    <option>Admin</option>
                    <option>Super Admin</option>
                </select>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:6px">
                <button class="btn btn-sm btn-outline" onclick="document.getElementById('addAdminModal').classList.remove('open')">Cancel</button>
                <button class="btn btn-sm btn-primary">Create Admin</button>
            </div>
        </div>
    </div>
</div>

@endsection
