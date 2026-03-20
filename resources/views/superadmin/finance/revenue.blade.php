@extends('layouts.superadmin')
@section('title', 'Revenue')
@section('page-title', 'Revenue')

@push('head')
<style>
.stat-grid-4 { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:20px; }
@media(max-width:1100px){ .stat-grid-4{grid-template-columns:repeat(2,1fr);} }
@media(max-width:560px){ .stat-grid-4{grid-template-columns:1fr;} }
.chart-row { display:grid; grid-template-columns:2fr 1fr; gap:16px; margin-bottom:20px; }
@media(max-width:900px){ .chart-row{grid-template-columns:1fr;} }
.period-tabs { display:flex; gap:4px; }
.ptab { padding:5px 12px; border-radius:var(--rh-radius-sm); font-size:12px; font-weight:600; cursor:pointer; border:1px solid var(--rh-border); background:var(--rh-page); color:var(--rh-muted); transition:all var(--rh-transition); }
.ptab.active,.ptab:hover { background:var(--rh-brand); color:#fff; border-color:var(--rh-brand); }
</style>
@endpush

@section('content')

<div class="rh-breadcrumb">
    <a href="{{ route('superadmin.dashboard') }}">Dashboard</a>
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>Revenue</span>
</div>

<div class="stat-grid-4">
    <div class="rh-stat">
        <div class="rh-stat-icon" style="background:linear-gradient(135deg,#4f46e5,#7c3aed)">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="rh-stat-body">
            <div class="rh-stat-label">Today's Revenue</div>
            <div class="rh-stat-val">₹4.2L</div>
            <div class="rh-stat-sub"><span class="rh-stat-up">↑ 12%</span> vs yesterday</div>
        </div>
    </div>
    <div class="rh-stat">
        <div class="rh-stat-icon" style="background:linear-gradient(135deg,#059669,#10b981)">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
        </div>
        <div class="rh-stat-body">
            <div class="rh-stat-label">This Month</div>
            <div class="rh-stat-val">₹68.4L</div>
            <div class="rh-stat-sub"><span class="rh-stat-up">↑ 8%</span> vs last month</div>
        </div>
    </div>
    <div class="rh-stat">
        <div class="rh-stat-icon" style="background:linear-gradient(135deg,#d97706,#f59e0b)">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
        </div>
        <div class="rh-stat-body">
            <div class="rh-stat-label">This Year</div>
            <div class="rh-stat-val">₹5.8Cr</div>
            <div class="rh-stat-sub"><span class="rh-stat-up">↑ 23%</span> vs last year</div>
        </div>
    </div>
    <div class="rh-stat">
        <div class="rh-stat-icon" style="background:linear-gradient(135deg,#0284c7,#38bdf8)">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        </div>
        <div class="rh-stat-body">
            <div class="rh-stat-label">Avg. Per Transaction</div>
            <div class="rh-stat-val">₹147</div>
            <div class="rh-stat-sub">Based on last 30 days</div>
        </div>
    </div>
</div>

{{-- Charts --}}
<div class="chart-row">
    <div class="rh-card">
        <div class="rh-card-header">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--rh-brand)"><path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
            <span class="rh-card-title">Revenue Trend</span>
            <div style="margin-left:auto" class="period-tabs">
                <div class="ptab active" onclick="setPeriod(this,'7d')">7D</div>
                <div class="ptab" onclick="setPeriod(this,'30d')">30D</div>
                <div class="ptab" onclick="setPeriod(this,'90d')">90D</div>
                <div class="ptab" onclick="setPeriod(this,'1y')">1Y</div>
            </div>
        </div>
        <div style="padding:18px"><canvas id="revenueChart" height="100"></canvas></div>
    </div>
    <div class="rh-card">
        <div class="rh-card-header">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--rh-purple)"><path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
            <span class="rh-card-title">Revenue by Operator</span>
        </div>
        <div style="padding:18px;display:flex;flex-direction:column;align-items:center;gap:14px">
            <canvas id="opRevenueChart" width="160" height="160"></canvas>
            <div style="width:100%;display:flex;flex-direction:column;gap:6px" id="opRevLegend"></div>
        </div>
    </div>
</div>

{{-- Revenue Table --}}
<div class="rh-card">
    <div class="rh-card-header">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--rh-brand)"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        <span class="rh-card-title">Daily Revenue Breakdown</span>
        <button class="btn btn-sm btn-outline" style="margin-left:auto">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Export CSV
        </button>
    </div>
    <div class="rh-card-body">
        <div class="rh-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Transactions</th>
                        <th>Gross Revenue</th>
                        <th>Commission Paid</th>
                        <th>Net Revenue</th>
                        <th>Success Rate</th>
                        <th>Trend</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $days = [
                        ['date'=>'Today, 20 Mar','tx'=>8341,'gross'=>'₹4,21,300','comm'=>'₹18,450','net'=>'₹4,02,850','rate'=>'98.7%','trend'=>'up'],
                        ['date'=>'19 Mar 2026','tx'=>7890,'gross'=>'₹3,98,200','comm'=>'₹17,200','net'=>'₹3,81,000','rate'=>'97.9%','trend'=>'up'],
                        ['date'=>'18 Mar 2026','tx'=>8100,'gross'=>'₹4,05,600','comm'=>'₹17,800','net'=>'₹3,87,800','rate'=>'98.2%','trend'=>'dn'],
                        ['date'=>'17 Mar 2026','tx'=>7640,'gross'=>'₹3,82,000','comm'=>'₹16,500','net'=>'₹3,65,500','rate'=>'97.5%','trend'=>'up'],
                        ['date'=>'16 Mar 2026','tx'=>7200,'gross'=>'₹3,60,000','comm'=>'₹15,600','net'=>'₹3,44,400','rate'=>'96.8%','trend'=>'up'],
                    ];
                    @endphp
                    @foreach($days as $d)
                    <tr>
                        <td style="font-weight:600">{{ $d['date'] }}</td>
                        <td>{{ number_format($d['tx']) }}</td>
                        <td style="font-weight:600;color:var(--rh-text)">{{ $d['gross'] }}</td>
                        <td style="color:var(--rh-red)">{{ $d['comm'] }}</td>
                        <td style="font-weight:700;color:var(--rh-green)">{{ $d['net'] }}</td>
                        <td>
                            <span style="font-weight:700;color:{{ (float)$d['rate'] >= 98 ? 'var(--rh-green)' : 'var(--rh-amber)' }}">{{ $d['rate'] }}</span>
                        </td>
                        <td>
                            @if($d['trend']==='up')
                                <span style="color:var(--rh-green);font-weight:700">↑</span>
                            @else
                                <span style="color:var(--rh-red);font-weight:700">↓</span>
                            @endif
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
const labels7 = Array.from({length:7},(_,i)=>{
    const d=new Date(); d.setDate(d.getDate()-(6-i));
    return d.toLocaleDateString('en-IN',{day:'2-digit',month:'short'});
});
const revChart = new Chart(document.getElementById('revenueChart'),{
    type:'bar',
    data:{
        labels:labels7,
        datasets:[{
            label:'Revenue (₹K)',
            data:[310,290,340,380,395,410,420],
            backgroundColor:'rgba(79,70,229,.75)',
            borderRadius:6,
        },{
            label:'Recharges',
            data:[890,870,1020,1100,1150,1200,1230],
            type:'line',
            borderColor:'#10b981',
            backgroundColor:'transparent',
            tension:.4, pointRadius:3, pointBackgroundColor:'#10b981',
            yAxisID:'y2',
        }]
    },
    options:{
        responsive:true,maintainAspectRatio:true,
        plugins:{legend:{display:false},tooltip:{backgroundColor:'#1e293b',cornerRadius:8,padding:10}},
        scales:{
            x:{grid:{display:false},ticks:{font:{size:10},color:'#9ca3af'}},
            y:{grid:{color:'#f3f4f6'},ticks:{font:{size:10},color:'#9ca3af',callback:v=>'₹'+v+'K'}},
            y2:{position:'right',grid:{display:false},ticks:{font:{size:10},color:'#9ca3af'}}
        }
    }
});
function setPeriod(el){
    document.querySelectorAll('.ptab').forEach(t=>t.classList.remove('active'));
    el.classList.add('active');
}
const opRevData=[
    {name:'Jio',value:42,color:'#4f46e5'},
    {name:'Airtel',value:31,color:'#dc2626'},
    {name:'Vi',value:16,color:'#d97706'},
    {name:'BSNL',value:8,color:'#059669'},
    {name:'Others',value:3,color:'#9ca3af'},
];
new Chart(document.getElementById('opRevenueChart'),{
    type:'doughnut',
    data:{labels:opRevData.map(o=>o.name),datasets:[{data:opRevData.map(o=>o.value),backgroundColor:opRevData.map(o=>o.color),borderWidth:2,borderColor:'#fff',hoverOffset:6}]},
    options:{responsive:false,cutout:'68%',plugins:{legend:{display:false}}}
});
const leg=document.getElementById('opRevLegend');
opRevData.forEach(o=>{
    leg.innerHTML+=`<div style="display:flex;align-items:center;justify-content:space-between;font-size:12px">
        <span style="display:flex;align-items:center;gap:7px"><span style="width:10px;height:10px;border-radius:3px;background:${o.color};display:inline-block"></span>${o.name}</span>
        <strong style="color:var(--rh-text)">${o.value}%</strong>
    </div>`;
});
</script>
@endpush
