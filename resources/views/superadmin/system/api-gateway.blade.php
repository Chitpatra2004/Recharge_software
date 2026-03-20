@extends('layouts.superadmin')
@section('title', 'API Gateway')
@section('page-title', 'API Gateway')

@push('head')
<style>
.stat-grid-4 { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:20px; }
@media(max-width:1100px){ .stat-grid-4{grid-template-columns:repeat(2,1fr);} }
@media(max-width:560px){ .stat-grid-4{grid-template-columns:1fr;} }
.gateway-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px; }
@media(max-width:800px){ .gateway-grid{grid-template-columns:1fr;} }
.api-health-bar { height:6px; border-radius:99px; background:var(--rh-border); overflow:hidden; margin-top:4px; }
.api-health-fill { height:100%; border-radius:99px; transition:width .5s ease; }
</style>
@endpush

@section('content')

<div class="rh-breadcrumb">
    <a href="{{ route('superadmin.dashboard') }}">Dashboard</a>
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>API Gateway</span>
</div>

<div class="rh-alert rh-alert-ok" style="margin-bottom:20px">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <span><strong>All Systems Operational</strong> — 17 APIs active · Last checked 42 seconds ago</span>
    <button class="btn btn-sm btn-outline" style="margin-left:auto;font-size:11.5px">Refresh Status</button>
</div>

<div class="stat-grid-4">
    <div class="rh-stat">
        <div class="rh-stat-icon" style="background:linear-gradient(135deg,#4f46e5,#7c3aed)">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
        </div>
        <div class="rh-stat-body">
            <div class="rh-stat-label">Active APIs</div>
            <div class="rh-stat-val">17</div>
            <div class="rh-stat-sub">Across 6 operators</div>
        </div>
    </div>
    <div class="rh-stat">
        <div class="rh-stat-icon" style="background:linear-gradient(135deg,#059669,#10b981)">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <div class="rh-stat-body">
            <div class="rh-stat-label">Req/Min (live)</div>
            <div class="rh-stat-val" id="reqMin">—</div>
            <div class="rh-stat-sub">Real-time throughput</div>
        </div>
    </div>
    <div class="rh-stat">
        <div class="rh-stat-icon" style="background:linear-gradient(135deg,#d97706,#f59e0b)">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="rh-stat-body">
            <div class="rh-stat-label">Avg Response</div>
            <div class="rh-stat-val" id="avgResp">—</div>
            <div class="rh-stat-sub">Across all APIs</div>
        </div>
    </div>
    <div class="rh-stat">
        <div class="rh-stat-icon" style="background:linear-gradient(135deg,#dc2626,#f43f5e)">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <div class="rh-stat-body">
            <div class="rh-stat-label">Error Rate</div>
            <div class="rh-stat-val">1.3%</div>
            <div class="rh-stat-sub"><span class="rh-stat-dn">↓ 0.4%</span> vs last hour</div>
        </div>
    </div>
</div>

{{-- API Health Overview --}}
<div class="gateway-grid">
    <div class="rh-card">
        <div class="rh-card-header">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--rh-green)"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <span class="rh-card-title">API Health Status</span>
        </div>
        <div class="rh-card-body">
            @php
            $apis = [
                ['name'=>'Jio — Paytm API','uptime'=>'99.2%','resp'=>'1.1s','color'=>'#10b981','pct'=>99],
                ['name'=>'Jio — Razorpay API','uptime'=>'98.8%','resp'=>'1.3s','color'=>'#10b981','pct'=>99],
                ['name'=>'Airtel — Cashfree API','uptime'=>'99.5%','resp'=>'0.9s','color'=>'#10b981','pct'=>100],
                ['name'=>'Vi — Instamojo API','uptime'=>'96.1%','resp'=>'2.1s','color'=>'#f59e0b','pct'=>96],
                ['name'=>'BSNL — Custom API','uptime'=>'91.4%','resp'=>'3.2s','color'=>'#f59e0b','pct'=>91],
                ['name'=>'MTNL — API-1','uptime'=>'99.0%','resp'=>'1.4s','color'=>'#10b981','pct'=>99],
            ];
            @endphp
            @foreach($apis as $api)
            <div style="margin-bottom:14px">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:3px">
                    <span style="font-size:13px;font-weight:600;color:var(--rh-text)">{{ $api['name'] }}</span>
                    <div style="display:flex;gap:10px;align-items:center">
                        <span style="font-size:11.5px;color:var(--rh-muted)">{{ $api['resp'] }}</span>
                        <span style="font-size:12px;font-weight:700;color:{{ $api['pct']>=99 ? 'var(--rh-green)' : ($api['pct']>=97 ? 'var(--rh-amber)' : 'var(--rh-red)') }}">{{ $api['uptime'] }}</span>
                    </div>
                </div>
                <div class="api-health-bar">
                    <div class="api-health-fill" style="width:{{ $api['pct'] }}%;background:{{ $api['color'] }}"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="rh-card">
        <div class="rh-card-header">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--rh-brand)"><path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
            <span class="rh-card-title">Live Request Rate</span>
        </div>
        <div style="padding:18px"><canvas id="liveChart" height="140"></canvas></div>
    </div>
</div>

{{-- Full API Table --}}
<div class="rh-card">
    <div class="rh-card-header">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--rh-brand)"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
        <span class="rh-card-title">All API Endpoints</span>
        <button class="btn btn-sm btn-primary" style="margin-left:auto">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Add API
        </button>
    </div>
    <div class="rh-card-body">
        <div class="rh-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Operator</th>
                        <th>Slot</th>
                        <th>Provider</th>
                        <th>URL</th>
                        <th>Success</th>
                        <th>Resp Time</th>
                        <th>Req Today</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Test</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $gatewayApis = [
                        ['op'=>'Jio','slot'=>'API-1','prov'=>'Paytm','url'=>'api.paytm.com/recharge','succ'=>'99.2%','resp'=>'1.1s','req'=>'3,241','prio'=>1,'status'=>'active'],
                        ['op'=>'Jio','slot'=>'API-2','prov'=>'Razorpay','url'=>'api.razorpay.com/jio','succ'=>'98.8%','resp'=>'1.3s','req'=>'412','prio'=>2,'status'=>'backup'],
                        ['op'=>'Airtel','slot'=>'API-1','prov'=>'Cashfree','url'=>'api.cashfree.com/air','succ'=>'99.5%','resp'=>'0.9s','req'=>'2,880','prio'=>1,'status'=>'active'],
                        ['op'=>'Vi','slot'=>'API-1','prov'=>'Instamojo','url'=>'api.instamojo.com/vi','succ'=>'96.1%','resp'=>'2.1s','req'=>'1,540','prio'=>1,'status'=>'active'],
                        ['op'=>'BSNL','slot'=>'API-1','prov'=>'Custom','url'=>'bsnl-api.rechargerhub.in','succ'=>'91.4%','resp'=>'3.2s','req'=>'890','prio'=>1,'status'=>'degraded'],
                    ];
                    @endphp
                    @foreach($gatewayApis as $g)
                    <tr>
                        <td><strong>{{ $g['op'] }}</strong></td>
                        <td><span class="badge badge-blue">{{ $g['slot'] }}</span></td>
                        <td>{{ $g['prov'] }}</td>
                        <td style="font-family:monospace;font-size:11px;color:var(--rh-muted)">{{ $g['url'] }}</td>
                        <td style="font-weight:700;color:{{ (float)$g['succ']>=99?'var(--rh-green)':((float)$g['succ']>=97?'var(--rh-amber)':'var(--rh-red)') }}">{{ $g['succ'] }}</td>
                        <td>{{ $g['resp'] }}</td>
                        <td>{{ $g['req'] }}</td>
                        <td style="font-weight:700">{{ $g['prio'] }}</td>
                        <td>
                            @if($g['status']==='active')
                                <span class="badge badge-green"><span class="badge-dot"></span>Active</span>
                            @elseif($g['status']==='backup')
                                <span class="badge badge-gray">Backup</span>
                            @else
                                <span class="badge badge-amber"><span class="badge-dot"></span>Degraded</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline" style="padding:5px 10px;font-size:11px">Ping</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
setTimeout(()=>{
    document.getElementById('reqMin').textContent='142';
    document.getElementById('avgResp').textContent='1.4s';
},400);

// Live requests chart
const liveLabels = Array.from({length:20},(_,i)=>`-${20-i}m`);
const liveChart = new Chart(document.getElementById('liveChart'),{
    type:'line',
    data:{
        labels:liveLabels,
        datasets:[{
            label:'Req/min',
            data:[95,110,128,142,135,148,155,139,162,170,158,165,173,180,168,175,182,178,185,142],
            borderColor:'#4f46e5',
            backgroundColor:'rgba(79,70,229,.08)',
            tension:.4, fill:true, pointRadius:0, borderWidth:2,
        }]
    },
    options:{
        responsive:true,maintainAspectRatio:true,
        plugins:{legend:{display:false},tooltip:{backgroundColor:'#1e293b',cornerRadius:8}},
        scales:{
            x:{grid:{display:false},ticks:{font:{size:9},color:'#9ca3af',maxTicksLimit:5}},
            y:{grid:{color:'#f3f4f6'},ticks:{font:{size:10},color:'#9ca3af'}}
        }
    }
});

// Simulate live updates
setInterval(()=>{
    const data = liveChart.data.datasets[0].data;
    data.shift();
    data.push(Math.floor(130+Math.random()*60));
    liveChart.update('none');
    document.getElementById('reqMin').textContent=data[data.length-1];
},3000);
</script>
@endpush
