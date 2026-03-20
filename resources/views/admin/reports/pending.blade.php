@extends('layouts.admin')
@section('title','Pending Recharge Report')

@push('head')
<style>
.filter-bar{display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;margin-bottom:18px}
.filter-bar select,.filter-bar input{padding:7px 11px;border:1px solid var(--border);border-radius:8px;font-size:13px;background:#fff;color:var(--text-primary)}
.filter-bar .btn{padding:7px 18px;font-size:13px}
.stat-row{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px}
@media(max-width:900px){.stat-row{grid-template-columns:1fr 1fr}}
.stat-card{background:var(--card-bg);border-radius:var(--radius);padding:16px 20px;box-shadow:var(--shadow-sm);border-left:4px solid transparent}
.stat-card.orange{border-color:var(--accent-orange)}
.stat-card.blue{border-color:var(--accent-blue)}
.stat-card.red{border-color:var(--accent-red)}
.stat-card.purple{border-color:var(--accent-purple)}
.stat-card .val{font-size:22px;font-weight:700;margin-bottom:2px}
.stat-card .lbl{font-size:11.5px;color:var(--text-secondary)}
.badge-pending{background:#fef3c7;color:#92400e;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:600}
.badge-stuck{background:#fee2e2;color:#991b1b;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:600}
.badge-processing{background:#dbeafe;color:#1e40af;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:600}
.action-btn{padding:4px 10px;border-radius:6px;font-size:11.5px;font-weight:600;cursor:pointer;border:1px solid var(--border);background:#fff;color:var(--text-primary)}
.action-btn:hover{background:var(--bg-page)}
.action-btn.retry{color:var(--accent-blue);border-color:var(--accent-blue)}
.action-btn.refund{color:var(--accent-red);border-color:var(--accent-red)}
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Pending Recharge Report</h1>
        <p class="page-sub">Transactions stuck in pending / processing state</p>
    </div>
    <div style="display:flex;gap:8px">
        <button class="btn btn-outline" onclick="exportPending()">Export CSV</button>
        <button class="btn btn-primary" onclick="loadPending()">Refresh</button>
    </div>
</div>

<div class="stat-row" id="summaryStats">
    <div class="stat-card orange"><div class="val" id="sTotalPending">—</div><div class="lbl">Total Pending</div></div>
    <div class="stat-card blue"><div class="val" id="sPendingAmt">—</div><div class="lbl">Pending Amount (₹)</div></div>
    <div class="stat-card red"><div class="val" id="sStuck">—</div><div class="lbl">Stuck > 30 min</div></div>
    <div class="stat-card purple"><div class="val" id="sProcessing">—</div><div class="lbl">Processing</div></div>
</div>

<div class="card">
    <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
        <span style="font-weight:600;font-size:14px">Pending Transactions</span>
        <div class="filter-bar" style="margin:0">
            <select id="fOperator">
                <option value="">All Operators</option>
                <option>Airtel</option><option>Jio</option><option>BSNL</option><option>Vi</option><option>Idea</option>
            </select>
            <select id="fAge">
                <option value="">Any Age</option>
                <option value="10">10+ min</option>
                <option value="30">30+ min</option>
                <option value="60">1+ hour</option>
                <option value="360">6+ hours</option>
            </select>
            <input type="date" id="fDate" value="{{ date('Y-m-d') }}">
            <button class="btn btn-primary" onclick="loadPending()">Filter</button>
        </div>
    </div>
    <div class="table-wrap">
        <table id="pendingTable">
            <thead>
                <tr>
                    <th>Txn ID</th>
                    <th>Mobile</th>
                    <th>Operator</th>
                    <th>Amount</th>
                    <th>Seller</th>
                    <th>Initiated</th>
                    <th>Age</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="pendingBody">
                <tr><td colspan="9" style="text-align:center;padding:30px;color:var(--text-muted)">Loading...</td></tr>
            </tbody>
        </table>
    </div>
    <div id="tablePager" style="display:flex;justify-content:space-between;align-items:center;padding:12px 16px;border-top:1px solid var(--border);font-size:12.5px;color:var(--text-secondary)">
        <span id="pageInfo">—</span>
        <div style="display:flex;gap:6px">
            <button class="action-btn" id="btnPrev" onclick="changePage(-1)">&#8592; Prev</button>
            <button class="action-btn" id="btnNext" onclick="changePage(1)">Next &#8594;</button>
        </div>
    </div>
</div>

{{-- Retry / Refund Confirm Modal --}}
<div id="actionModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:500;align-items:center;justify-content:center">
    <div class="card" style="width:400px;max-width:95vw;padding:24px">
        <h3 id="modalTitle" style="font-size:15px;font-weight:700;margin-bottom:8px">Confirm Action</h3>
        <p id="modalMsg" style="font-size:13px;color:var(--text-secondary);margin-bottom:20px"></p>
        <div style="display:flex;gap:10px">
            <button class="btn btn-primary" id="modalConfirm">Confirm</button>
            <button class="btn btn-outline" onclick="document.getElementById('actionModal').style.display='none'">Cancel</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentPage = 1;
const perPage = 15;
let allRows = [];

const DEMO = [
    {id:'TXN2600001',mobile:'9876543210',op:'Airtel',amt:199,seller:'RajeshTelecom',time:'10:23 AM',age:42,status:'stuck'},
    {id:'TXN2600002',mobile:'8765432109',op:'Jio',amt:299,seller:'PriyaRecharge',time:'10:45 AM',age:20,status:'processing'},
    {id:'TXN2600003',mobile:'7654321098',op:'BSNL',amt:99,seller:'SunilShop',time:'09:12 AM',age:108,status:'stuck'},
    {id:'TXN2600004',mobile:'9988776655',op:'Vi',amt:49,seller:'AmitStore',time:'11:02 AM',age:8,status:'pending'},
    {id:'TXN2600005',mobile:'8877665544',op:'Idea',amt:149,seller:'RajeshTelecom',time:'11:15 AM',age:3,status:'pending'},
    {id:'TXN2600006',mobile:'9765432100',op:'Airtel',amt:599,seller:'SunilShop',time:'08:55 AM',age:136,status:'stuck'},
    {id:'TXN2600007',mobile:'8654321009',op:'Jio',amt:399,seller:'PriyaRecharge',time:'10:58 AM',age:12,status:'processing'},
    {id:'TXN2600008',mobile:'9543210098',op:'BSNL',amt:239,seller:'AmitStore',time:'09:44 AM',age:77,status:'stuck'},
];

function ageLabel(min){
    if(min<60) return min+'m';
    return Math.floor(min/60)+'h '+(min%60)+'m';
}

function renderTable(){
    const op = document.getElementById('fOperator').value;
    const age= parseInt(document.getElementById('fAge').value)||0;
    let rows = allRows.filter(r=>(!op||r.op===op)&&(r.age>=age));
    const total=rows.length;
    const start=(currentPage-1)*perPage;
    const slice=rows.slice(start,start+perPage);
    document.getElementById('pageInfo').textContent=`Showing ${start+1}–${Math.min(start+perPage,total)} of ${total}`;
    document.getElementById('btnPrev').disabled=currentPage===1;
    document.getElementById('btnNext').disabled=start+perPage>=total;
    const tbody=document.getElementById('pendingBody');
    if(!slice.length){tbody.innerHTML='<tr><td colspan="9" style="text-align:center;padding:30px;color:var(--text-muted)">No pending transactions found</td></tr>';return;}
    tbody.innerHTML=slice.map(r=>{
        const badge=r.status==='stuck'?'<span class="badge-stuck">Stuck</span>':r.status==='processing'?'<span class="badge-processing">Processing</span>':'<span class="badge-pending">Pending</span>';
        return `<tr>
            <td style="font-family:monospace;font-size:12px">${r.id}</td>
            <td>${r.mobile}</td>
            <td>${r.op}</td>
            <td style="font-weight:600">₹${r.amt}</td>
            <td>${r.seller}</td>
            <td style="color:var(--text-secondary)">${r.time}</td>
            <td style="color:${r.age>30?'var(--accent-red)':'var(--text-secondary)'}"><b>${ageLabel(r.age)}</b></td>
            <td>${badge}</td>
            <td style="display:flex;gap:5px">
                <button class="action-btn retry" onclick="doAction('retry','${r.id}','${r.mobile}',${r.amt})">Retry</button>
                <button class="action-btn refund" onclick="doAction('refund','${r.id}','${r.mobile}',${r.amt})">Refund</button>
            </td>
        </tr>`;
    }).join('');
}

function loadPending(){
    allRows=DEMO;
    const stuck=allRows.filter(r=>r.status==='stuck').length;
    const proc=allRows.filter(r=>r.status==='processing').length;
    const amt=allRows.reduce((s,r)=>s+r.amt,0);
    document.getElementById('sTotalPending').textContent=allRows.length;
    document.getElementById('sPendingAmt').textContent='₹'+amt.toLocaleString('en-IN');
    document.getElementById('sStuck').textContent=stuck;
    document.getElementById('sProcessing').textContent=proc;
    currentPage=1;
    renderTable();
}

function changePage(dir){currentPage+=dir;renderTable();}

function doAction(type,id,mobile,amt){
    const modal=document.getElementById('actionModal');
    document.getElementById('modalTitle').textContent=type==='retry'?'Retry Recharge':'Initiate Refund';
    document.getElementById('modalMsg').textContent=type==='retry'
        ?`Retry recharge of ₹${amt} for ${mobile}? (Txn: ${id})`
        :`Refund ₹${amt} to seller wallet for ${mobile}? (Txn: ${id})`;
    modal.style.display='flex';
    document.getElementById('modalConfirm').onclick=()=>{
        modal.style.display='none';
        alert(type==='retry'?`Retry queued for ${id}`:`Refund initiated for ${id}`);
    };
}

function exportPending(){
    const rows=['Txn ID,Mobile,Operator,Amount,Seller,Time,Age (min),Status'];
    allRows.forEach(r=>rows.push(`${r.id},${r.mobile},${r.op},${r.amt},${r.seller},${r.time},${r.age},${r.status}`));
    const a=document.createElement('a');
    a.href='data:text/csv;charset=utf-8,'+encodeURIComponent(rows.join('\n'));
    a.download='pending_recharges.csv';a.click();
}

document.addEventListener('DOMContentLoaded',loadPending);
</script>
@endpush
@endsection
