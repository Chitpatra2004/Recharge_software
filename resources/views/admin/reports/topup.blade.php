@extends('layouts.admin')
@section('title','Top-up Report')

@push('head')
<style>
.summary-strip{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px}
@media(max-width:900px){.summary-strip{grid-template-columns:1fr 1fr}}
.stat-card{background:var(--card-bg);border-radius:var(--radius);padding:16px 20px;box-shadow:var(--shadow-sm);border-left:4px solid transparent}
.stat-card.green{border-color:var(--accent-green)}
.stat-card.blue{border-color:var(--accent-blue)}
.stat-card.orange{border-color:var(--accent-orange)}
.stat-card.red{border-color:var(--accent-red)}
.stat-card .val{font-size:22px;font-weight:700;margin-bottom:2px}
.stat-card .lbl{font-size:11.5px;color:var(--text-secondary)}
.filter-bar{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:14px}
.filter-bar input,.filter-bar select{padding:7px 11px;border:1px solid var(--border);border-radius:8px;font-size:13px;background:#fff;color:var(--text-primary)}
.badge-approved{background:#d1fae5;color:#065f46;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:600}
.badge-pending{background:#fef3c7;color:#92400e;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:600}
.badge-rejected{background:#fee2e2;color:#991b1b;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:600}
.chart-wrap{background:var(--card-bg);border-radius:var(--radius);padding:20px;box-shadow:var(--shadow-sm);margin-bottom:18px}
.chart-title{font-size:13.5px;font-weight:600;margin-bottom:14px;color:var(--text-primary)}
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Top-up Report</h1>
        <p class="page-sub">Wallet top-up requests — history, approvals & trends</p>
    </div>
    <div style="display:flex;gap:8px">
        <select id="periodSelect" onchange="loadData()" style="padding:7px 12px;border:1px solid var(--border);border-radius:8px;font-size:13px;background:#fff">
            <option value="today">Today</option>
            <option value="week" selected>This Week</option>
            <option value="month">This Month</option>
        </select>
        <button class="btn btn-outline" onclick="exportReport()">Export CSV</button>
        <button class="btn btn-primary" onclick="loadData()">Refresh</button>
    </div>
</div>

<div class="summary-strip">
    <div class="stat-card green"><div class="val" id="sTotalTopup">—</div><div class="lbl">Total Top-up Amount</div></div>
    <div class="stat-card blue"><div class="val" id="sApproved">—</div><div class="lbl">Approved</div></div>
    <div class="stat-card orange"><div class="val" id="sPending">—</div><div class="lbl">Pending</div></div>
    <div class="stat-card red"><div class="val" id="sRejected">—</div><div class="lbl">Rejected</div></div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:14px;margin-bottom:18px">
    <div class="chart-wrap">
        <div class="chart-title">Top-up Trend (Last 7 Days)</div>
        <canvas id="topupChart" height="90"></canvas>
    </div>
    <div class="chart-wrap">
        <div class="chart-title">Payment Method Split</div>
        <canvas id="methodChart" height="90"></canvas>
    </div>
</div>

<div class="card">
    <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
        <span style="font-weight:600;font-size:14px">Top-up Requests</span>
        <div class="filter-bar" style="margin:0">
            <input type="text" id="searchReq" placeholder="Search seller / UTR / amount..." oninput="filterTable()">
            <select id="fStatus" onchange="filterTable()">
                <option value="">All Status</option>
                <option value="approved">Approved</option>
                <option value="pending">Pending</option>
                <option value="rejected">Rejected</option>
            </select>
            <select id="fMethod" onchange="filterTable()">
                <option value="">All Methods</option>
                <option value="NEFT">NEFT</option>
                <option value="IMPS">IMPS</option>
                <option value="UPI">UPI</option>
                <option value="Cash">Cash</option>
            </select>
        </div>
    </div>
    <div class="table-wrap">
        <table id="topupTable">
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Seller</th>
                    <th>Amount</th>
                    <th>Payment Method</th>
                    <th>UTR / Ref</th>
                    <th>Requested At</th>
                    <th>Approved By</th>
                    <th>Status</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody id="topupBody">
                <tr><td colspan="9" style="text-align:center;padding:30px;color:var(--text-muted)">Loading...</td></tr>
            </tbody>
        </table>
    </div>
    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 16px;border-top:1px solid var(--border);font-size:12.5px;color:var(--text-secondary)">
        <span id="tableInfo">—</span>
    </div>
</div>

@push('scripts')
<script>
const DEMO = [
    {id:'TOP20260001',seller:'RajeshTelecom',amt:50000,method:'NEFT',utr:'HDFC2600001234',dt:'20 Mar 2026, 10:00 AM',by:'Admin (You)',status:'approved',remark:'Verified'},
    {id:'TOP20260002',seller:'PriyaRecharge',amt:25000,method:'IMPS',utr:'ICIC2600005678',dt:'19 Mar 2026, 03:00 PM',by:'Admin (You)',status:'approved',remark:'Verified'},
    {id:'TOP20260003',seller:'SunilShop',amt:10000,method:'UPI',utr:'UPI2600009012',dt:'19 Mar 2026, 11:30 AM',by:'—',status:'pending',remark:'Awaiting confirmation'},
    {id:'TOP20260004',seller:'AmitStore',amt:20000,method:'NEFT',utr:'AXIS2600003456',dt:'18 Mar 2026, 02:45 PM',by:'Admin (You)',status:'approved',remark:'Verified'},
    {id:'TOP20260005',seller:'KiranMobile',amt:5000,method:'Cash',utr:'CSH-001',dt:'18 Mar 2026, 09:00 AM',by:'Admin (You)',status:'rejected',remark:'Cash payment not accepted'},
    {id:'TOP20260006',seller:'MohanTelecom',amt:15000,method:'IMPS',utr:'PNB2600007890',dt:'17 Mar 2026, 04:00 PM',by:'Admin (You)',status:'approved',remark:'Verified'},
    {id:'TOP20260007',seller:'AnilRecharge',amt:8000,method:'UPI',utr:'UPI2600001122',dt:'16 Mar 2026, 01:00 PM',by:'—',status:'pending',remark:'Pending bank check'},
    {id:'TOP20260008',seller:'RaniShop',amt:3000,method:'NEFT',utr:'KKBK2600002233',dt:'15 Mar 2026, 10:30 AM',by:'Admin (You)',status:'approved',remark:'Verified'},
];

let filtered=[...DEMO];

function filterTable(){
    const q=document.getElementById('searchReq').value.toLowerCase();
    const s=document.getElementById('fStatus').value;
    const m=document.getElementById('fMethod').value;
    filtered=DEMO.filter(r=>{
        const mq=!q||(r.seller.toLowerCase().includes(q)||r.utr.toLowerCase().includes(q)||String(r.amt).includes(q));
        const ms=!s||r.status===s;
        const mm=!m||r.method===m;
        return mq&&ms&&mm;
    });
    renderTable();
}

function badge(status){
    if(status==='approved')return '<span class="badge-approved">Approved</span>';
    if(status==='pending')return '<span class="badge-pending">Pending</span>';
    return '<span class="badge-rejected">Rejected</span>';
}

function renderTable(){
    const tbody=document.getElementById('topupBody');
    document.getElementById('tableInfo').textContent=`Showing ${filtered.length} of ${DEMO.length} requests`;
    if(!filtered.length){tbody.innerHTML='<tr><td colspan="9" style="text-align:center;padding:30px;color:var(--text-muted)">No records found</td></tr>';return;}
    tbody.innerHTML=filtered.map(r=>`<tr>
        <td style="font-family:monospace;font-size:12px">${r.id}</td>
        <td style="font-weight:600">${r.seller}</td>
        <td style="font-weight:700;color:var(--accent-green)">₹${r.amt.toLocaleString('en-IN')}</td>
        <td><span style="background:#f1f5f9;padding:2px 8px;border-radius:4px;font-size:12px">${r.method}</span></td>
        <td style="font-family:monospace;font-size:12px">${r.utr}</td>
        <td style="font-size:12px;color:var(--text-secondary)">${r.dt}</td>
        <td style="font-size:12px;color:var(--text-secondary)">${r.by}</td>
        <td>${badge(r.status)}</td>
        <td style="font-size:12px;color:var(--text-secondary)">${r.remark}</td>
    </tr>`).join('');
}

function loadData(){
    const approved=DEMO.filter(r=>r.status==='approved');
    const pending=DEMO.filter(r=>r.status==='pending');
    const rejected=DEMO.filter(r=>r.status==='rejected');
    const total=approved.reduce((s,r)=>s+r.amt,0);
    document.getElementById('sTotalTopup').textContent='₹'+total.toLocaleString('en-IN');
    document.getElementById('sApproved').textContent=approved.length+' (₹'+approved.reduce((s,r)=>s+r.amt,0).toLocaleString('en-IN')+')';
    document.getElementById('sPending').textContent=pending.length;
    document.getElementById('sRejected').textContent=rejected.length;
    filtered=[...DEMO];
    renderTable();
    renderCharts();
}

function renderCharts(){
    const ctx1=document.getElementById('topupChart').getContext('2d');
    new Chart(ctx1,{
        type:'bar',
        data:{
            labels:['14 Mar','15 Mar','16 Mar','17 Mar','18 Mar','19 Mar','20 Mar'],
            datasets:[{label:'Top-up (₹)',data:[20000,3000,8000,15000,25000,35000,50000],
                backgroundColor:'rgba(37,99,235,0.7)',borderRadius:4}]
        },
        options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{ticks:{callback:v=>'₹'+v.toLocaleString('en-IN')}}}}
    });

    const ctx2=document.getElementById('methodChart').getContext('2d');
    new Chart(ctx2,{
        type:'doughnut',
        data:{
            labels:['NEFT','IMPS','UPI','Cash'],
            datasets:[{data:[73000,40000,11000,5000],
                backgroundColor:['#2563eb','#7c3aed','#10b981','#f59e0b'],borderWidth:2}]
        },
        options:{responsive:true,plugins:{legend:{position:'bottom',labels:{font:{size:11}}}}}
    });
}

function exportReport(){
    const rows=['Request ID,Seller,Amount,Method,UTR,Date,Approved By,Status,Remark'];
    DEMO.forEach(r=>rows.push(`${r.id},"${r.seller}",${r.amt},${r.method},${r.utr},"${r.dt}","${r.by}",${r.status},"${r.remark}"`));
    const a=document.createElement('a');
    a.href='data:text/csv;charset=utf-8,'+encodeURIComponent(rows.join('\n'));
    a.download='topup_report.csv';a.click();
}

document.addEventListener('DOMContentLoaded',loadData);
</script>
@endpush
@endsection
