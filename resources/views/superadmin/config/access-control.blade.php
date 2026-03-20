@extends('layouts.superadmin')
@section('title', 'Access Control')
@section('page-title', 'Access Control')

@push('head')
<style>
.ac-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px; }
@media(max-width:800px){ .ac-grid{grid-template-columns:1fr;} }
.perm-group { margin-bottom:18px; }
.perm-group-title { font-size:11.5px;font-weight:700;color:var(--rh-muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px; }
.perm-row { display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--rh-border); }
.perm-row:last-child { border-bottom:none; }
.perm-lbl { font-size:13px;font-weight:500;color:var(--rh-text); }
.perm-checks { display:flex;gap:16px;align-items:center; }
.perm-check-lbl { font-size:11px;color:var(--rh-muted);display:flex;align-items:center;gap:5px;cursor:pointer;white-space:nowrap; }
.perm-check-lbl input[type=checkbox] { accent-color:var(--rh-brand);width:14px;height:14px;cursor:pointer; }
.role-tab { padding:6px 14px;border-radius:var(--rh-radius-sm);font-size:12.5px;font-weight:600;cursor:pointer;border:1px solid var(--rh-border);background:var(--rh-page);color:var(--rh-muted);transition:all var(--rh-transition); }
.role-tab.active,.role-tab:hover { background:var(--rh-brand);color:#fff;border-color:var(--rh-brand); }
</style>
@endpush

@section('content')

<div class="rh-breadcrumb">
    <a href="{{ route('superadmin.dashboard') }}">Dashboard</a>
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>Access Control</span>
</div>

<div class="rh-alert rh-alert-info" style="margin-bottom:20px">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    Configure role-based permissions for admin and seller accounts. Changes take effect immediately.
</div>

{{-- Role Summary --}}
<div class="ac-grid">
    <div class="rh-card">
        <div class="rh-card-header">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--rh-brand)"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span class="rh-card-title">Roles Overview</span>
        </div>
        <div class="rh-card-body">
            @php
            $roles = [
                ['name'=>'Super Admin','count'=>1,'color'=>'var(--rh-brand)','desc'=>'Full unrestricted access to all modules'],
                ['name'=>'Admin','count'=>14,'color'=>'#7c3aed','desc'=>'Manages sellers, wallets, complaints under their scope'],
                ['name'=>'Seller (User)','count'=>247,'color'=>'#059669','desc'=>'Access to recharge, wallet, reports, API docs'],
            ];
            @endphp
            @foreach($roles as $r)
            <div style="display:flex;align-items:center;gap:12px;padding:11px 0;border-bottom:1px solid var(--rh-border)">
                <div style="width:40px;height:40px;border-radius:10px;background:{{ $r['color'] }}20;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;color:{{ $r['color'] }}"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div style="flex:1">
                    <div style="font-size:13.5px;font-weight:700;color:var(--rh-text)">{{ $r['name'] }}</div>
                    <div style="font-size:11.5px;color:var(--rh-muted)">{{ $r['desc'] }}</div>
                </div>
                <span class="badge badge-blue">{{ $r['count'] }} {{ $r['count']===1?'account':'accounts' }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <div class="rh-card">
        <div class="rh-card-header">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--rh-amber)"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            <span class="rh-card-title">IP Whitelist (Admins)</span>
            <button class="btn btn-sm btn-outline" style="margin-left:auto;font-size:11px">+ Add IP</button>
        </div>
        <div class="rh-card-body">
            <div class="rh-alert rh-alert-warn">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                IP Whitelist is currently <strong>disabled</strong>. Enable in Security Settings.
            </div>
            <div class="rh-table-wrap">
                <table>
                    <thead><tr><th>IP Address</th><th>Label</th><th>Added</th><th></th></tr></thead>
                    <tbody>
                        @php $ips=[['ip'=>'192.168.1.0/24','lbl'=>'Office Network','added'=>'15 Jan 2026'],['ip'=>'103.21.58.12','lbl'=>'Arjun Home','added'=>'2 Feb 2026']]; @endphp
                        @foreach($ips as $ip)
                        <tr>
                            <td style="font-family:monospace;font-size:12px">{{ $ip['ip'] }}</td>
                            <td>{{ $ip['lbl'] }}</td>
                            <td style="font-size:11.5px;color:var(--rh-muted)">{{ $ip['added'] }}</td>
                            <td><button class="btn btn-sm btn-danger" style="padding:4px 8px;font-size:10.5px">Remove</button></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Permissions Matrix --}}
<div class="rh-card">
    <div class="rh-card-header">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--rh-brand)"><path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
        <span class="rh-card-title">Permission Matrix</span>
        <div style="margin-left:auto;display:flex;gap:6px">
            <div class="role-tab active" onclick="document.querySelectorAll('.role-tab').forEach(t=>t.classList.remove('active'));this.classList.add('active')">Admin Role</div>
            <div class="role-tab" onclick="document.querySelectorAll('.role-tab').forEach(t=>t.classList.remove('active'));this.classList.add('active')">Seller Role</div>
        </div>
    </div>
    <div class="rh-card-body">

        @php
        $permGroups = [
            'Dashboard' => [
                ['name'=>'View Dashboard','view'=>true,'edit'=>false,'delete'=>false],
            ],
            'Users / Sellers' => [
                ['name'=>'View Sellers','view'=>true,'edit'=>false,'delete'=>false],
                ['name'=>'Add Sellers','view'=>false,'edit'=>true,'delete'=>false],
                ['name'=>'Edit / Suspend Sellers','view'=>false,'edit'=>true,'delete'=>true],
            ],
            'Wallet' => [
                ['name'=>'View Wallet Balances','view'=>true,'edit'=>false,'delete'=>false],
                ['name'=>'Request Top-up','view'=>false,'edit'=>true,'delete'=>false],
                ['name'=>'Approve Top-up','view'=>false,'edit'=>true,'delete'=>false],
            ],
            'Reports' => [
                ['name'=>'View Recharge Reports','view'=>true,'edit'=>false,'delete'=>false],
                ['name'=>'Export Reports','view'=>true,'edit'=>false,'delete'=>false],
            ],
            'Complaints' => [
                ['name'=>'View Complaints','view'=>true,'edit'=>false,'delete'=>false],
                ['name'=>'Resolve Complaints','view'=>false,'edit'=>true,'delete'=>false],
            ],
            'API Keys' => [
                ['name'=>'View API Keys','view'=>true,'edit'=>false,'delete'=>false],
                ['name'=>'Generate API Keys','view'=>false,'edit'=>true,'delete'=>false],
                ['name'=>'Revoke API Keys','view'=>false,'edit'=>false,'delete'=>true],
            ],
        ];
        @endphp

        @foreach($permGroups as $group => $perms)
        <div class="perm-group">
            <div class="perm-group-title">{{ $group }}</div>
            @foreach($perms as $p)
            <div class="perm-row">
                <span class="perm-lbl">{{ $p['name'] }}</span>
                <div class="perm-checks">
                    <label class="perm-check-lbl">
                        <input type="checkbox" {{ $p['view'] ? 'checked' : '' }}> View
                    </label>
                    <label class="perm-check-lbl">
                        <input type="checkbox" {{ $p['edit'] ? 'checked' : '' }}> Create/Edit
                    </label>
                    <label class="perm-check-lbl">
                        <input type="checkbox" {{ $p['delete'] ? 'checked' : '' }}> Delete
                    </label>
                </div>
            </div>
            @endforeach
        </div>
        @endforeach

        <div style="display:flex;gap:10px;margin-top:8px">
            <button class="btn btn-md btn-primary">Save Permissions</button>
            <button class="btn btn-md btn-outline">Reset to Defaults</button>
        </div>
    </div>
</div>

@endsection
