@extends('layouts.admin')
@section('title','Seller Payment Requests')

@push('head')
<style>
.summary-strip{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px}
@media(max-width:900px){.summary-strip{grid-template-columns:1fr 1fr}}
.stat-card{background:var(--card-bg);border-radius:var(--radius);padding:16px 20px;box-shadow:var(--shadow-sm);border-left:4px solid transparent}
.stat-card.blue{border-color:var(--accent-blue)}
.stat-card.green{border-color:var(--accent-green)}
.stat-card.orange{border-color:var(--accent-orange)}
.stat-card.red{border-color:var(--accent-red)}
.stat-card .val{font-size:22px;font-weight:700;margin-bottom:2px}
.stat-card .lbl{font-size:11.5px;color:var(--text-secondary)}
.filter-bar{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
.filter-bar input,.filter-bar select{padding:7px 11px;border:1px solid var(--border);border-radius:8px;font-size:13px;background:#fff}
.badge-approved{background:#d1fae5;color:#065f46;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:600}
.badge-pending{background:#fef3c7;color:#92400e;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:600}
.badge-rejected{background:#fee2e2;color:#991b1b;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:600}
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Seller Payment Requests</h1>
        <p class="page-sub">Review and process wallet top-up requests from sellers</p>
    </div>
    <div style="display:flex;gap:8px">
        <button class="btn btn-outline" onclick="exportData()">Export CSV</button>
        <button class="btn btn-primary" onclick="loadData()">Refresh</button>
    </div>
</div>

<div class="summary-strip">
    <div class="stat-card orange"><div class="val" id="sPendingCount">—</div><div class="lbl">Pending Requests</div></div>
    <div class="stat-card orange"><div class="val" id="sPendingAmt">—</div><div class="lbl">Pending Amount</div></div>
    <div class="stat-card green"><div class="val" id="sApprovedToday">—</div><div class="lbl">Approved Today</div></div>
    <div class="stat-card blue"><div class="val" id="sApprovedAmt">—</div><div class="lbl">Approved Amount Today</div></div>
</div>

{{-- Pending Requests (action needed) --}}
<div class="card" style="margin-bottom:18px">
    <div class="card-header" style="display:flex;align-items:center;justify-content:space-between">
        <span style="font-weight:600;font-size:14px">Pending Requests <span id="pendingBadge" style="background:#fef3c7;color:#92400e;padding:2px 9px;border-radius:20px;font-size:12px;margin-left:6px;font-weight:700">0</span></span>
        <button class="btn btn-primary btn-sm" onclick="approveAll()">Approve All</button>
    </div>
    <div class="table-wrap">
        <table id="pendingTable">
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Seller</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>UTR / Ref</th>
                    <th>Requested At</th>
                    <th>Bank Proof</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="pendingBody"></tbody>
        </table>
    </div>
</div>

{{-- History --}}
<div class="card">
    <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
        <span style="font-weight:600;font-size:14px">All Requests</span>
        <div class="filter-bar">
            <input type="text" id="searchReq" placeholder="Seller / UTR..." oninput="filterHistory()">
            <select id="fStatus" onchange="filterHistory()">
                <option value="">All Status</option>
                <option value="approved">Approved</option>
                <option value="pending">Pending</option>
                <option value="rejected">Rejected</option>
            </select>
            <select id="fSeller" onchange="filterHistory()">
                <option value="">All Sellers</option>
                <option>RajeshTelecom</option><option>PriyaRecharge</option>
                <option>SunilShop</option><option>AmitStore</option>
            </select>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Request ID</th><th>Seller</th><th>Amount</th><th>Method</th><th>UTR</th><th>Date</th><th>Processed By</th><th>Status</th></tr>
            </thead>
            <tbody id="historyBody"></tbody>
        </table>
    </div>
</div>

{{-- Reject Modal --}}
<div id="rejectModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:500;align-items:center;justify-content:center">
    <div class="card" style="width:420px;max-width:95vw;padding:24px">
        <h3 style="font-size:15px;font-weight:700;margin-bottom:12px">Reject Request</h3>
        <input type="hidden" id="rejectId">
        <div style="margin-bottom:14px">
            <label style="display:block;font-size:12px;font-weight:600;margin-bottom:5px">Reason for Rejection *</label>
            <select id="rejectReason" style="width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:8px;font-size:13px">
                <option value="">Select reason...</option>
                <option>UTR not found in bank statement</option>
                <option>Incorrect amount entered</option>
                <option>Duplicate request</option>
                <option>Cash payment not accepted</option>
                <option>Other</option>
            </select>
        </div>
        <div style="margin-bottom:16px">
            <label style="display:block;font-size:12px;font-weight:600;margin-bottom:5px">Additional remarks</label>
            <textarea id="rejectRemark" rows="3" placeholder="Optional details..." style="width:100%;padding:8px 11px;border:1px solid var(--border);border-radius:8px;font-size:13px;resize:vertical"></textarea>
        </div>
        <div style="display:flex;gap:10px">
            <button class="btn btn-danger" onclick="confirmReject()">Reject Request</button>
            <button class="btn btn-outline" onclick="document.getElementById('rejectModal').style.display='none'">Cancel</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
const ALL_REQUESTS = [
    {id:'TOP20260001',seller:'RajeshTelecom',amt:50000,method:'NEFT',utr:'HDFC2600001234',dt:'20 Mar 2026, 10:00 AM',by:'Admin (You)',status:'approved'},
    {id:'TOP20260002',seller:'PriyaRecharge',amt:25000,method:'IMPS',utr:'ICIC2600005678',dt:'19 Mar 2026, 03:00 PM',by:'Admin (You)',status:'approved'},
    {id:'TOP20260003',seller:'SunilShop',    amt:10000,method:'UPI', utr:'UPI2600009012', dt:'20 Mar 2026, 11:30 AM',by:'—',status:'pending'},
    {id:'TOP20260004',seller:'AmitStore',    amt:20000,method:'NEFT',utr:'AXIS2600003456',dt:'18 Mar 2026, 02:45 PM',by:'Admin (You)',status:'approved'},
    {id:'TOP20260005',seller:'RajeshTelecom',amt:15000,method:'IMPS',utr:'HDFC2600006789',dt:'20 Mar 2026, 09:15 AM',by:'—',status:'pending'},
    {id:'TOP20260006',seller:'PriyaRecharge',amt:5000, method:'Cash',utr:'CSH-001',        dt:'17 Mar 2026, 04:00 PM',by:'Admin (You)',status:'rejected'},
];

let historyFiltered=[...ALL_REQUESTS];

function badge(s){
    if(s==='approved')return '<span class="badge-approved">Approved</span>';
    if(s==='pending') return '<span class="badge-pending">Pending</span>';
    return '<span class="badge-rejected">Rejected</span>';
}

function renderPending(){
    const pending=ALL_REQUESTS.filter(r=>r.status==='pending');
    document.getElementById('pendingBadge').textContent=pending.length;
    document.getElementById('pendingBody').innerHTML=pending.length
        ?pending.map(r=>`<tr>
            <td style="font-family:monospace;font-size:12px">${r.id}</td>
            <td style="font-weight:600">${r.seller}</td>
            <td style="font-weight:700;color:var(--accent-green)">₹${r.amt.toLocaleString('en-IN')}</td>
            <td>${r.method}</td>
            <td style="font-family:monospace;font-size:12px">${r.utr}</td>
            <td style="font-size:12px;color:var(--text-secondary)">${r.dt}</td>
            <td><button style="padding:3px 8px;font-size:11px;border:1px solid var(--border);border-radius:5px;cursor:pointer;background:#fff">View Proof</button></td>
            <td style="display:flex;gap:5px">
                <button onclick="approveOne('${r.id}')" style="padding:4px 10px;border-radius:6px;font-size:11.5px;cursor:pointer;border:1px solid var(--accent-green);color:var(--accent-green);background:#fff;font-weight:600">Approve</button>
                <button onclick="showReject('${r.id}')" style="padding:4px 10px;border-radius:6px;font-size:11.5px;cursor:pointer;border:1px solid var(--accent-red);color:var(--accent-red);background:#fff">Reject</button>
            </td>
        </tr>`).join('')
        :'<tr><td colspan="8" style="text-align:center;padding:24px;color:var(--text-muted)">No pending requests</td></tr>';
}

function filterHistory(){
    const q=document.getElementById('searchReq').value.toLowerCase();
    const s=document.getElementById('fStatus').value;
    const sel=document.getElementById('fSeller').value;
    historyFiltered=ALL_REQUESTS.filter(r=>{
        return (!q||(r.seller.toLowerCase().includes(q)||r.utr.toLowerCase().includes(q)))
            &&(!s||r.status===s)&&(!sel||r.seller===sel);
    });
    renderHistory();
}

function renderHistory(){
    document.getElementById('historyBody').innerHTML=historyFiltered.map(r=>`<tr>
        <td style="font-family:monospace;font-size:12px">${r.id}</td>
        <td style="font-weight:600">${r.seller}</td>
        <td style="font-weight:700;color:var(--accent-green)">₹${r.amt.toLocaleString('en-IN')}</td>
        <td>${r.method}</td>
        <td style="font-family:monospace;font-size:12px">${r.utr}</td>
        <td style="font-size:12px;color:var(--text-secondary)">${r.dt}</td>
        <td style="font-size:12px;color:var(--text-secondary)">${r.by}</td>
        <td>${badge(r.status)}</td>
    </tr>`).join('');
}

function loadData(){
    const pending=ALL_REQUESTS.filter(r=>r.status==='pending');
    const approvedToday=ALL_REQUESTS.filter(r=>r.status==='approved'&&r.dt.includes('20 Mar'));
    document.getElementById('sPendingCount').textContent=pending.length;
    document.getElementById('sPendingAmt').textContent='₹'+pending.reduce((s,r)=>s+r.amt,0).toLocaleString('en-IN');
    document.getElementById('sApprovedToday').textContent=approvedToday.length;
    document.getElementById('sApprovedAmt').textContent='₹'+approvedToday.reduce((s,r)=>s+r.amt,0).toLocaleString('en-IN');
    renderPending();
    historyFiltered=[...ALL_REQUESTS];
    renderHistory();
}

function approveOne(id){
    if(!confirm('Approve this top-up request?'))return;
    const r=ALL_REQUESTS.find(x=>x.id===id);
    if(r){r.status='approved';r.by='Admin (You)';}
    loadData();
}

function approveAll(){
    const pending=ALL_REQUESTS.filter(r=>r.status==='pending');
    if(!pending.length){alert('No pending requests.');return;}
    if(!confirm(`Approve all ${pending.length} pending requests?`))return;
    pending.forEach(r=>{r.status='approved';r.by='Admin (You)';});
    loadData();
}

function showReject(id){
    document.getElementById('rejectId').value=id;
    document.getElementById('rejectReason').value='';
    document.getElementById('rejectRemark').value='';
    document.getElementById('rejectModal').style.display='flex';
}

function confirmReject(){
    const id=document.getElementById('rejectId').value;
    const reason=document.getElementById('rejectReason').value;
    if(!reason){alert('Please select a rejection reason.');return;}
    const r=ALL_REQUESTS.find(x=>x.id===id);
    if(r){r.status='rejected';r.by='Admin (You)';}
    document.getElementById('rejectModal').style.display='none';
    loadData();
}

function exportData(){alert('Exporting payment requests...');}
document.addEventListener('DOMContentLoaded',loadData);
</script>
@endpush
@endsection
