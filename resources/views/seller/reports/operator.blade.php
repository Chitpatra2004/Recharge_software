@extends('layouts.seller')
@section('title','Operator Report')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Operator Report</h1>
        <p class="page-sub">Your recharge summary broken down by operator</p>
    </div>
    <div style="display:flex;gap:8px">
        <select id="periodSel" onchange="loadData()" style="padding:7px 12px;border:1px solid var(--border);border-radius:8px;font-size:13px;background:#fff">
            <option value="today">Today</option>
            <option value="week" selected>This Week</option>
            <option value="month">This Month</option>
        </select>
        <button class="btn btn-outline" onclick="exportData()">Export</button>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px">
    <div class="stat-card blue"><div class="stat-value" id="sTotal">—</div><div class="stat-label">Total Txns</div></div>
    <div class="stat-card green"><div class="stat-value" id="sSuccess">—</div><div class="stat-label">Success</div></div>
    <div class="stat-card orange"><div class="stat-value" id="sPending">—</div><div class="stat-label">Pending</div></div>
    <div class="stat-card red"><div class="stat-value" id="sFailure">—</div><div class="stat-label">Failure</div></div>
</div>

<div class="card">
    <div class="card-header"><span style="font-weight:600">Operator-wise Breakdown</span></div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Operator</th>
                    <th>Total</th>
                    <th>Success</th>
                    <th>Pending</th>
                    <th>Failure</th>
                    <th>Amount (₹)</th>
                    <th>Success Rate</th>
                </tr>
            </thead>
            <tbody id="opBody"></tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
const DEMO = [
    {op:'Airtel', total:87, success:78, pending:6, failure:3, amount:17430, rate:89.7},
    {op:'Jio',    total:63, success:58, pending:4, failure:1, amount:18837, rate:92.1},
    {op:'Vi',     total:31, success:27, pending:3, failure:1, amount:4619,  rate:87.1},
    {op:'BSNL',   total:14, success:11, pending:2, failure:1, amount:1386,  rate:78.6},
    {op:'Idea',   total:8,  success:7,  pending:1, failure:0, amount:1176,  rate:87.5},
];
function loadData(){
    const total=DEMO.reduce((s,r)=>s+r.total,0);
    const succ=DEMO.reduce((s,r)=>s+r.success,0);
    const pend=DEMO.reduce((s,r)=>s+r.pending,0);
    const fail=DEMO.reduce((s,r)=>s+r.failure,0);
    document.getElementById('sTotal').textContent=total;
    document.getElementById('sSuccess').textContent=succ;
    document.getElementById('sPending').textContent=pend;
    document.getElementById('sFailure').textContent=fail;
    document.getElementById('opBody').innerHTML=DEMO.map(r=>{
        const rateClr=r.rate>=90?'#10b981':r.rate>=75?'#f59e0b':'#ef4444';
        return `<tr>
            <td style="font-weight:600">${r.op}</td>
            <td>${r.total}</td>
            <td style="color:#10b981;font-weight:600">${r.success}</td>
            <td style="color:#f59e0b;font-weight:600">${r.pending}</td>
            <td style="color:#ef4444;font-weight:600">${r.failure}</td>
            <td style="font-weight:600">₹${r.amount.toLocaleString('en-IN')}</td>
            <td><span style="color:${rateClr};font-weight:700">${r.rate}%</span></td>
        </tr>`;
    }).join('');
}
function exportData(){alert('Exporting operator report...');}
document.addEventListener('DOMContentLoaded',loadData);
</script>
@endpush
@endsection
