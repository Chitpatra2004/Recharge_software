@extends('layouts.superadmin')
@section('title', 'Commissions')
@section('page-title', 'Commissions')

@push('head')
<style>
.stat-grid-3 { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:20px; }
@media(max-width:800px){ .stat-grid-3{grid-template-columns:repeat(2,1fr);} }
@media(max-width:500px){ .stat-grid-3{grid-template-columns:1fr;} }
.slab-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px; }
@media(max-width:700px){ .slab-grid{grid-template-columns:1fr;} }
</style>
@endpush

@section('content')

<div class="rh-breadcrumb">
    <a href="{{ route('superadmin.dashboard') }}">Dashboard</a>
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>Commissions</span>
</div>

<div class="stat-grid-3">
    <div class="rh-stat">
        <div class="rh-stat-icon" style="background:linear-gradient(135deg,#4f46e5,#7c3aed)">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
        </div>
        <div class="rh-stat-body">
            <div class="rh-stat-label">Total Paid Today</div>
            <div class="rh-stat-val">₹18,450</div>
            <div class="rh-stat-sub"><span class="rh-stat-up">↑ 5%</span> vs yesterday</div>
        </div>
    </div>
    <div class="rh-stat">
        <div class="rh-stat-icon" style="background:linear-gradient(135deg,#059669,#10b981)">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="rh-stat-body">
            <div class="rh-stat-label">This Month</div>
            <div class="rh-stat-val">₹3.24L</div>
            <div class="rh-stat-sub">Across 247 sellers</div>
        </div>
    </div>
    <div class="rh-stat">
        <div class="rh-stat-icon" style="background:linear-gradient(135deg,#d97706,#f59e0b)">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <div class="rh-stat-body">
            <div class="rh-stat-label">Avg Commission Rate</div>
            <div class="rh-stat-val">4.38%</div>
            <div class="rh-stat-sub">Across all operators</div>
        </div>
    </div>
</div>

{{-- Commission Slabs --}}
<div class="slab-grid">
    <div class="rh-card">
        <div class="rh-card-header">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--rh-brand)"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <span class="rh-card-title">Commission Slabs — Sellers</span>
            <button class="btn btn-sm btn-outline" style="margin-left:auto;font-size:11.5px">Edit Slabs</button>
        </div>
        <div class="rh-card-body">
            <div class="rh-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Operator</th>
                            <th>Slab Type</th>
                            <th>Rate</th>
                            <th>Flat (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $slabs = [
                            ['op'=>'Jio','type'=>'Percentage','rate'=>'4.5%','flat'=>'—'],
                            ['op'=>'Airtel','type'=>'Percentage','rate'=>'4.2%','flat'=>'—'],
                            ['op'=>'Vi','type'=>'Percentage','rate'=>'3.8%','flat'=>'—'],
                            ['op'=>'BSNL','type'=>'Flat','rate'=>'—','flat'=>'₹5'],
                            ['op'=>'MTNL','type'=>'Flat','rate'=>'—','flat'=>'₹4'],
                            ['op'=>'DTH','type'=>'Percentage','rate'=>'5.0%','flat'=>'—'],
                        ];
                        @endphp
                        @foreach($slabs as $s)
                        <tr>
                            <td><strong>{{ $s['op'] }}</strong></td>
                            <td><span class="badge {{ $s['type']==='Percentage' ? 'badge-blue' : 'badge-purple' }}">{{ $s['type'] }}</span></td>
                            <td style="font-weight:600;color:var(--rh-green)">{{ $s['rate'] }}</td>
                            <td style="font-weight:600;color:var(--rh-text)">{{ $s['flat'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="rh-card">
        <div class="rh-card-header">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--rh-purple)"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
            <span class="rh-card-title">Admin Commission Slabs</span>
            <button class="btn btn-sm btn-outline" style="margin-left:auto;font-size:11.5px">Edit</button>
        </div>
        <div class="rh-card-body">
            <div class="rh-alert rh-alert-info">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Admins earn a spread between seller commission and actual API cost.
            </div>
            <div class="rh-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Operator</th>
                            <th>Seller Pays</th>
                            <th>API Cost</th>
                            <th>Admin Spread</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $aspread = [
                            ['op'=>'Jio','seller'=>'4.5%','cost'=>'2.8%','spread'=>'1.7%'],
                            ['op'=>'Airtel','seller'=>'4.2%','cost'=>'2.5%','spread'=>'1.7%'],
                            ['op'=>'Vi','seller'=>'3.8%','cost'=>'2.2%','spread'=>'1.6%'],
                            ['op'=>'BSNL','seller'=>'₹5','cost'=>'₹3.2','spread'=>'₹1.8'],
                        ];
                        @endphp
                        @foreach($aspread as $a)
                        <tr>
                            <td><strong>{{ $a['op'] }}</strong></td>
                            <td style="color:var(--rh-muted)">{{ $a['seller'] }}</td>
                            <td style="color:var(--rh-red)">{{ $a['cost'] }}</td>
                            <td style="font-weight:700;color:var(--rh-green)">{{ $a['spread'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Top Earners --}}
<div class="rh-card">
    <div class="rh-card-header">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--rh-amber)"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        <span class="rh-card-title">Top Commission Earners — This Month</span>
    </div>
    <div class="rh-card-body">
        <div class="rh-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Seller</th>
                        <th>Recharges</th>
                        <th>Volume</th>
                        <th>Commission Earned</th>
                        <th>Rate Applied</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $earners = [
                        ['rank'=>1,'name'=>'Ravi Telecom','recharges'=>8421,'vol'=>'₹12,42,000','comm'=>'₹55,890','rate'=>'4.5%'],
                        ['rank'=>2,'name'=>'StarConnect','recharges'=>6110,'vol'=>'₹9,87,000','comm'=>'₹41,454','rate'=>'4.2%'],
                        ['rank'=>3,'name'=>'MobileNation','recharges'=>4880,'vol'=>'₹7,64,500','comm'=>'₹29,051','rate'=>'3.8%'],
                        ['rank'=>4,'name'=>'QuickRecharge','recharges'=>3920,'vol'=>'₹6,12,000','comm'=>'₹27,540','rate'=>'4.5%'],
                        ['rank'=>5,'name'=>'NetZone','recharges'=>2100,'vol'=>'₹3,40,000','comm'=>'₹17,000','rate'=>'5.0%'],
                    ];
                    @endphp
                    @foreach($earners as $e)
                    <tr>
                        <td>
                            <span style="font-weight:800;color:{{ $e['rank']<=3 ? 'var(--rh-amber)' : 'var(--rh-muted)' }};font-size:14px">{{ $e['rank'] }}</span>
                        </td>
                        <td style="font-weight:600;color:var(--rh-text)">{{ $e['name'] }}</td>
                        <td>{{ number_format($e['recharges']) }}</td>
                        <td>{{ $e['vol'] }}</td>
                        <td style="font-weight:700;color:var(--rh-green)">{{ $e['comm'] }}</td>
                        <td><span class="badge badge-blue">{{ $e['rate'] }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
