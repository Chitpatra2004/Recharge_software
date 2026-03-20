@extends('layouts.seller')
@section('title','Payments')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Payments & Top-up</h1>
        <p class="page-sub">Request wallet top-up and view payment history</p>
    </div>
    <button class="btn btn-primary" onclick="showTopupModal()">+ Request Top-up</button>
</div>

<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:18px">
    <div class="stat-card blue"><div class="stat-value" id="sWalBal">—</div><div class="stat-label">Wallet Balance</div></div>
    <div class="stat-card green"><div class="stat-value" id="sTotalAdded">—</div><div class="stat-label">Total Added (Month)</div></div>
    <div class="stat-card orange"><div class="stat-value" id="sPendingReq">—</div><div class="stat-label">Pending Requests</div></div>
</div>

<div class="card">
    <div class="card-header" style="justify-content:space-between">
        <span style="font-weight:600">Top-up History</span>
        <button class="btn btn-outline btn-sm" onclick="exportData()">Export</button>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Request ID</th><th>Amount</th><th>Method</th><th>UTR / Ref</th><th>Requested</th><th>Status</th><th>Remarks</th></tr>
            </thead>
            <tbody id="payBody"></tbody>
        </table>
    </div>
</div>

{{-- Top-up Modal --}}
<div id="topupModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:500;align-items:center;justify-content:center">
    <div class="card" style="width:440px;max-width:95vw;padding:24px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h3 style="font-size:16px;font-weight:700">Request Wallet Top-up</h3>
            <button onclick="document.getElementById('topupModal').style.display='none'" style="background:none;border:none;cursor:pointer;font-size:18px;color:var(--text-muted)">&times;</button>
        </div>
        <div class="rh-alert" style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;padding:12px;margin-bottom:16px;font-size:13px;color:#0369a1">
            Transfer to our bank account and submit UTR here. Top-up processed within 30 min.
        </div>
        <div style="margin-bottom:14px">
            <label style="display:block;font-size:12.5px;font-weight:600;margin-bottom:5px">Amount (₹) *</label>
            <input type="number" id="topupAmt" placeholder="Minimum ₹500" min="500" style="width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px">
        </div>
        <div style="margin-bottom:14px">
            <label style="display:block;font-size:12.5px;font-weight:600;margin-bottom:5px">Payment Method *</label>
            <select id="topupMethod" style="width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px">
                <option value="">Select method...</option>
                <option>NEFT</option><option>IMPS</option><option>UPI</option>
            </select>
        </div>
        <div style="margin-bottom:14px">
            <label style="display:block;font-size:12.5px;font-weight:600;margin-bottom:5px">UTR / Reference Number *</label>
            <input type="text" id="topupUTR" placeholder="12-digit UTR or UPI ref" style="width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px">
        </div>
        <div style="display:flex;gap:10px">
            <button class="btn btn-primary" onclick="submitTopup()">Submit Request</button>
            <button class="btn btn-outline" onclick="document.getElementById('topupModal').style.display='none'">Cancel</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
const DEMO_PAY=[
    {id:'TOP20260001',amt:50000,method:'NEFT',utr:'HDFC2600001234',dt:'20 Mar 2026',status:'approved',remark:'Verified & credited'},
    {id:'TOP20260002',amt:25000,method:'IMPS',utr:'ICIC2600005678',dt:'15 Mar 2026',status:'approved',remark:'Verified & credited'},
    {id:'TOP20260003',amt:10000,method:'UPI',utr:'UPI2600009012', dt:'19 Mar 2026',status:'pending', remark:'Awaiting admin approval'},
];

function badge(s){
    if(s==='approved') return '<span style="background:#d1fae5;color:#065f46;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:600">Approved</span>';
    if(s==='pending')  return '<span style="background:#fef3c7;color:#92400e;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:600">Pending</span>';
    return '<span style="background:#fee2e2;color:#991b1b;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:600">Rejected</span>';
}

function loadData(){
    document.getElementById('sWalBal').textContent='₹12,450';
    document.getElementById('sTotalAdded').textContent='₹85,000';
    document.getElementById('sPendingReq').textContent=DEMO_PAY.filter(r=>r.status==='pending').length;
    document.getElementById('payBody').innerHTML=DEMO_PAY.map(r=>`<tr>
        <td style="font-family:monospace;font-size:12px">${r.id}</td>
        <td style="font-weight:700;color:var(--accent-green)">₹${r.amt.toLocaleString('en-IN')}</td>
        <td>${r.method}</td>
        <td style="font-family:monospace;font-size:12px">${r.utr}</td>
        <td style="font-size:12px;color:var(--text-secondary)">${r.dt}</td>
        <td>${badge(r.status)}</td>
        <td style="font-size:12px;color:var(--text-secondary)">${r.remark}</td>
    </tr>`).join('');
}

function showTopupModal(){document.getElementById('topupModal').style.display='flex';}

function submitTopup(){
    const amt=document.getElementById('topupAmt').value;
    const method=document.getElementById('topupMethod').value;
    const utr=document.getElementById('topupUTR').value;
    if(!amt||!method||!utr){alert('Please fill all required fields.');return;}
    document.getElementById('topupModal').style.display='none';
    alert('Top-up request submitted successfully! You will be notified once approved.');
}

function exportData(){alert('Exporting payment history...');}
document.addEventListener('DOMContentLoaded',loadData);
</script>
@endpush
@endsection
