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
                    <th>Submitted</th>
                    <th>Seller</th>
                    <th>Amount</th>
                    <th>Mode</th>
                    <th>Reference</th>
                    <th>Notes</th>
                    <th>Proof</th>
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
                <tr><th>Submitted</th><th>Seller</th><th>Amount</th><th>Mode</th><th>Reference</th><th>Processed At</th><th>By</th><th>Status</th></tr>
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
const TOKEN = ()=>localStorage.getItem('emp_token');
const empFetch = (url,method='GET',body=null)=>fetch(url,{method,headers:{'Authorization':'Bearer '+TOKEN(),'Content-Type':'application/json','Accept':'application/json'},...(body?{body:JSON.stringify(body)}:{})}).then(async r=>{const d=await r.json();if(!r.ok)throw new Error(d.message||'Error');return d;});
function fmtMoney(n){return Number(n||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2});}
function fmtDate(d){if(!d)return'—';const dt=new Date(d);return dt.toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'});}
function badge(s){
    if(s==='approved')return '<span class="badge-approved">Approved</span>';
    if(s==='pending') return '<span class="badge-pending">Pending</span>';
    return '<span class="badge-rejected">Rejected</span>';
}

let histPage=1;

function loadData(){
    empFetch('/api/v1/employee/sellers/payment-requests/list?status=pending&per_page=100').then(data=>{
        const pending=(data.data&&data.data.data)||[];
        document.getElementById('pendingBadge').textContent=pending.length;
        document.getElementById('sPendingCount').textContent=pending.length;
        document.getElementById('sPendingAmt').textContent='₹'+fmtMoney(pending.reduce((s,r)=>s+(r.amount||0),0));
        document.getElementById('pendingBody').innerHTML=pending.length
            ?pending.map(r=>`<tr>
                <td style="font-size:12.5px">${fmtDate(r.created_at)}</td>
                <td style="font-weight:600">${(r.user&&r.user.name)||'—'}</td>
                <td style="font-weight:700;color:#10b981">₹${fmtMoney(r.amount)}</td>
                <td style="text-transform:capitalize">${(r.payment_mode||'—').replace('_',' ')}</td>
                <td style="font-family:monospace;font-size:12px">${r.reference_number||'—'}</td>
                <td style="font-size:12px;color:var(--text-secondary)">${r.notes||'—'}</td>
                <td>${r.proof_image?`<a href="/storage/${r.proof_image}" target="_blank" style="padding:3px 8px;font-size:11.5px;border:1px solid var(--border);border-radius:5px;background:#fff;text-decoration:none;color:var(--text-primary)">📎 View</a>`:'—'}</td>
                <td style="display:flex;gap:5px">
                    <button onclick="approveOne(${r.id})" style="padding:4px 10px;border-radius:6px;font-size:11.5px;cursor:pointer;border:1px solid #10b981;color:#10b981;background:#fff;font-weight:600">Approve</button>
                    <button onclick="showReject(${r.id})" style="padding:4px 10px;border-radius:6px;font-size:11.5px;cursor:pointer;border:1px solid #ef4444;color:#ef4444;background:#fff">Reject</button>
                </td>
            </tr>`).join('')
            :'<tr><td colspan="8" style="text-align:center;padding:24px;color:var(--text-muted)">No pending requests</td></tr>';
    }).catch(e=>{ document.getElementById('pendingBody').innerHTML=`<tr><td colspan="8" style="text-align:center;padding:24px;color:#ef4444">${e.message||'Failed to load.'}</td></tr>`; });

    loadHistory(histPage);

    // Update today stats
    empFetch('/api/v1/employee/sellers/payment-requests/list?status=approved&per_page=100').then(data=>{
        const today=new Date().toDateString();
        const todayRows=((data.data&&data.data.data)||[]).filter(r=>new Date(r.processed_at).toDateString()===today);
        document.getElementById('sApprovedToday').textContent=todayRows.length;
        document.getElementById('sApprovedAmt').textContent='₹'+fmtMoney(todayRows.reduce((s,r)=>s+(r.amount||0),0));
    }).catch(()=>{});
}

function loadHistory(page){
    histPage=page||1;
    const params=new URLSearchParams({page:histPage});
    const q=document.getElementById('searchReq').value.trim(), s=document.getElementById('fStatus').value;
    if(q) params.set('search',q); if(s) params.set('status',s);
    document.getElementById('historyBody').innerHTML='<tr><td colspan="8" style="text-align:center;padding:24px;color:var(--text-muted)">Loading…</td></tr>';
    empFetch(`/api/v1/employee/sellers/payment-requests/list?${params}`).then(data=>{
        const pagination=data.data||{};
        const rows=pagination.data||[];
        document.getElementById('historyBody').innerHTML=rows.length
            ?rows.map(r=>`<tr>
                <td style="font-size:12.5px">${fmtDate(r.created_at)}</td>
                <td style="font-weight:600">${(r.user&&r.user.name)||'—'}</td>
                <td style="font-weight:700;color:#10b981">₹${fmtMoney(r.amount)}</td>
                <td style="text-transform:capitalize">${(r.payment_mode||'—').replace('_',' ')}</td>
                <td style="font-family:monospace;font-size:12px">${r.reference_number||'—'}</td>
                <td style="font-size:12px;color:var(--text-secondary)">${fmtDate(r.processed_at)}</td>
                <td style="font-size:12px;color:var(--text-secondary)">${r.admin_notes||'—'}</td>
                <td>${badge(r.status)}</td>
            </tr>`).join('')
            :'<tr><td colspan="8" style="text-align:center;padding:24px;color:var(--text-muted)">No requests found</td></tr>';
    }).catch(()=>{ document.getElementById('historyBody').innerHTML='<tr><td colspan="8" style="text-align:center;padding:24px;color:#ef4444">Failed to load.</td></tr>'; });
}

function filterHistory(){ loadHistory(1); }

function approveOne(id){
    if(!confirm('Approve this payment request? The seller wallet will be credited automatically.'))return;
    empFetch(`/api/v1/employee/sellers/payment-requests/${id}/approve`,'POST').then(()=>{ showToast('Payment approved and wallet credited!','success'); loadData(); }).catch(e=>alert(e.message||'Failed.'));
}

function approveAll(){
    const pending=document.querySelectorAll('#pendingBody tr').length;
    if(!confirm(`Approve all pending requests?`))return;
    // No bulk endpoint — collect IDs and approve sequentially
    alert('Use individual approve buttons for each request.');
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
    const remark=document.getElementById('rejectRemark').value.trim();
    if(!reason){alert('Please select a rejection reason.');return;}
    const notes=reason+(remark?' — '+remark:'');
    empFetch(`/api/v1/employee/sellers/payment-requests/${id}/reject`,'POST',{notes}).then(()=>{ document.getElementById('rejectModal').style.display='none'; showToast('Request rejected.','info'); loadData(); }).catch(e=>alert(e.message||'Failed.'));
}

function showToast(msg,type){
    const t=document.createElement('div');
    t.style.cssText=`position:fixed;bottom:24px;right:24px;z-index:9999;padding:12px 20px;border-radius:10px;font-size:13.5px;font-weight:600;color:#fff;background:${type==='success'?'#10b981':'#64748b'};box-shadow:0 4px 20px rgba(0,0,0,.2);animation:slideUp .2s ease`;
    t.textContent=msg; document.body.appendChild(t); setTimeout(()=>t.remove(),3500);
}

function exportData(){ window.open('/api/v1/employee/sellers/payment-requests/list?export=csv','_blank'); }
document.addEventListener('DOMContentLoaded',loadData);
</script>
@endpush
@endsection
