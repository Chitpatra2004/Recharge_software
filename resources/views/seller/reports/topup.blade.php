@extends('layouts.seller')
@section('title','Topup Report')
@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Topup Report</h1>
        <p class="page-sub">History of all wallet topup requests and credits</p>
    </div>
    <a href="/seller/payments" style="display:inline-flex;align-items:center;gap:6px;background:#10b981;color:#fff;padding:9px 18px;border-radius:9px;font-size:13.5px;font-weight:600;text-decoration:none">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:15px;height:15px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        New Payment Request
    </a>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom:20px">
    <div style="padding:16px 20px;display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
        <div style="display:flex;flex-direction:column;gap:4px">
            <label style="font-size:11.5px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.4px">From</label>
            <input type="date" id="f-from" class="form-control" style="width:150px">
        </div>
        <div style="display:flex;flex-direction:column;gap:4px">
            <label style="font-size:11.5px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.4px">To</label>
            <input type="date" id="f-to" class="form-control" style="width:150px">
        </div>
        <div style="display:flex;flex-direction:column;gap:4px">
            <label style="font-size:11.5px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.4px">Status</label>
            <select id="f-status" class="form-control" style="width:130px">
                <option value="">All</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
        <button onclick="loadReport(1)" style="background:#10b981;color:#fff;border:none;padding:9px 20px;border-radius:9px;font-size:13.5px;font-weight:600;cursor:pointer;height:38px">Search</button>
    </div>
</div>

<!-- Summary -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:20px" id="summary-cards">
    <div class="stat-card"><div class="stat-body"><div class="stat-label">Total Requests</div><div class="stat-value" id="sc-total">—</div></div></div>
    <div class="stat-card"><div class="stat-body"><div class="stat-label">Approved Amount</div><div class="stat-value" id="sc-approved">—</div></div></div>
    <div class="stat-card"><div class="stat-body"><div class="stat-label">Pending Amount</div><div class="stat-value" id="sc-pending">—</div></div></div>
</div>

<div class="card">
    <div id="table-wrap"><div style="text-align:center;padding:40px;color:#64748b">Loading…</div></div>
    <div id="pagination" style="padding:16px 20px;border-top:1px solid #f1f5f9"></div>
</div>

<script>
let cp = 1;
function loadReport(page){
    cp=page||1;
    const params=new URLSearchParams({page:cp});
    const from=document.getElementById('f-from').value, to=document.getElementById('f-to').value, st=document.getElementById('f-status').value;
    if(from) params.set('date_from',from); if(to) params.set('date_to',to); if(st) params.set('status',st);
    document.getElementById('table-wrap').innerHTML='<div style="text-align:center;padding:40px;color:#64748b">Loading…</div>';
    apiFetch(`/api/v1/seller/reports/topup?${params}`).then(data=>{
        const s=data.summary||{};
        document.getElementById('sc-total').textContent    = s.total_count||0;
        document.getElementById('sc-approved').textContent = '₹'+fmtMoney(s.approved_amount||0);
        document.getElementById('sc-pending').textContent  = '₹'+fmtMoney(s.pending_amount||0);
        const rows=data.data||[];
        if(!rows.length){ document.getElementById('table-wrap').innerHTML='<div style="text-align:center;padding:30px;color:#64748b">No records found.</div>'; document.getElementById('pagination').innerHTML=''; return; }
        let t=`<table class="table"><thead><tr><th>Date</th><th>Amount</th><th>Mode</th><th>Reference</th><th>Proof</th><th>Status</th><th>Admin Note</th></tr></thead><tbody>`;
        rows.forEach(r=>{
            t+=`<tr>
                <td style="font-size:12.5px;white-space:nowrap">${fmtDate(r.created_at)}</td>
                <td style="font-weight:700;color:#10b981">₹${fmtMoney(r.amount)}</td>
                <td><span style="font-size:12px;text-transform:capitalize">${(r.payment_mode||'—').replace('_',' ')}</span></td>
                <td><span style="font-family:monospace;font-size:12px">${r.reference_number||'—'}</span></td>
                <td>${r.proof_image?`<a href="/storage/${r.proof_image}" target="_blank" style="color:#10b981;font-size:12px;font-weight:600">View</a>`:'—'}</td>
                <td>${statusBadge(r.status)}</td>
                <td style="font-size:12.5px;color:#64748b;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${r.admin_notes||'—'}</td>
            </tr>`;
        });
        t+='</tbody></table>';
        document.getElementById('table-wrap').innerHTML=t;
        const meta=data.meta||data, lp=meta.last_page||1;
        let pag=`<div style="display:flex;align-items:center;justify-content:space-between">
            <span style="font-size:13px;color:#64748b">Total: ${meta.total||0} records</span>
            <div style="display:flex;gap:6px">`;
        for(let i=1;i<=lp;i++) pag+=`<button onclick="loadReport(${i})" style="padding:6px 11px;border-radius:7px;border:1.5px solid ${i===cp?'#10b981':'#e2e8f0'};background:${i===cp?'#10b981':'#fff'};color:${i===cp?'#fff':'#374151'};font-size:13px;font-weight:${i===cp?'700':'400'};cursor:pointer">${i}</button>`;
        pag+=`</div></div>`;
        document.getElementById('pagination').innerHTML=pag;
    }).catch(()=>{ document.getElementById('table-wrap').innerHTML='<div style="text-align:center;padding:40px;color:#ef4444">Failed to load.</div>'; });
}
loadReport(1);
</script>
@endsection
