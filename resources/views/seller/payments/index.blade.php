@extends('layouts.seller')
@section('title','Payment Requests')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Payment Requests</h1>
        <p class="page-sub">Submit wallet topup requests and track their status</p>
    </div>
    <button onclick="openModal()" style="display:inline-flex;align-items:center;gap:6px;background:#10b981;color:#fff;padding:9px 18px;border-radius:9px;font-size:13.5px;font-weight:600;border:none;cursor:pointer">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:15px;height:15px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        New Payment Request
    </button>
</div>

<!-- Stats -->
<div class="stats-grid" style="margin-bottom:20px">
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(245,158,11,.15)"><svg fill="none" viewBox="0 0 24 24" stroke="#f59e0b" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 1v8m0 0v1"/></svg></div>
        <div class="stat-body"><div class="stat-label">Wallet Balance</div><div class="stat-value" id="s-wallet">—</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(16,185,129,.15)"><svg fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        <div class="stat-body"><div class="stat-label">Approved (Month)</div><div class="stat-value" id="s-approved">—</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(239,68,68,.15)"><svg fill="none" viewBox="0 0 24 24" stroke="#ef4444" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        <div class="stat-body"><div class="stat-label">Pending Requests</div><div class="stat-value" id="s-pending">—</div></div>
    </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom:16px">
    <div style="padding:14px 20px;display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
        <div style="display:flex;flex-direction:column;gap:4px">
            <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.4px">Status</label>
            <select id="f-status" class="form-control" style="width:130px"><option value="">All</option><option value="pending">Pending</option><option value="approved">Approved</option><option value="rejected">Rejected</option></select>
        </div>
        <div style="display:flex;flex-direction:column;gap:4px">
            <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.4px">From</label>
            <input type="date" id="f-from" class="form-control" style="width:148px">
        </div>
        <div style="display:flex;flex-direction:column;gap:4px">
            <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.4px">To</label>
            <input type="date" id="f-to" class="form-control" style="width:148px">
        </div>
        <button onclick="loadPayments(1)" style="background:#10b981;color:#fff;border:none;padding:9px 18px;border-radius:9px;font-size:13px;font-weight:600;cursor:pointer;height:38px">Search</button>
    </div>
</div>

<div id="top-alert" style="display:none;padding:12px 16px;border-radius:10px;font-size:13px;margin-bottom:16px;align-items:center;gap:8px"></div>

<div class="card">
    <div id="table-wrap"><div style="text-align:center;padding:40px;color:#64748b">Loading…</div></div>
    <div id="pagination" style="padding:16px 20px;border-top:1px solid #f1f5f9"></div>
</div>

<!-- New Payment Modal -->
<div class="modal-overlay" id="payment-modal">
    <div class="modal-box" style="max-width:520px">
        <button class="modal-close" onclick="closeModal()"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
        <h3 style="font-size:18px;font-weight:700;color:#1e293b;margin-bottom:6px">New Payment Request</h3>
        <p style="font-size:13px;color:#64748b;margin-bottom:20px">Submit your wallet topup. Admin will verify and credit your account within 30 minutes.</p>
        <div id="modal-alert" style="display:none;padding:10px 14px;border-radius:9px;font-size:13px;margin-bottom:14px;align-items:flex-start;gap:8px"></div>
        <form id="payment-form">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                <div class="form-group">
                    <label class="form-label">Amount (₹) <span style="color:#ef4444">*</span></label>
                    <input type="number" id="p-amount" class="form-control" placeholder="e.g. 5000" min="100" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Payment Mode <span style="color:#ef4444">*</span></label>
                    <select id="p-mode" class="form-control" required>
                        <option value="">Select mode</option>
                        <option value="upi">UPI</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="neft">NEFT</option>
                        <option value="rtgs">RTGS</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Reference / UTR Number <span style="color:#ef4444">*</span></label>
                <input type="text" id="p-ref" class="form-control" placeholder="Transaction reference / UTR number" required>
            </div>
            <div class="form-group">
                <label class="form-label">Payment Proof <span style="color:#ef4444">*</span></label>
                <input type="file" id="p-proof" accept="image/*,.pdf" required style="width:100%;padding:8px 12px;border:1.5px solid #e5e7eb;border-radius:9px;font-size:13px;background:#f9fafb">
                <small style="font-size:11.5px;color:#94a3b8;display:block;margin-top:4px">JPG, PNG, PDF · max 5MB</small>
            </div>
            <div class="form-group">
                <label class="form-label">Notes <span style="color:#94a3b8;font-weight:400">(optional)</span></label>
                <textarea id="p-notes" class="form-control" rows="2" placeholder="Any additional details…" style="resize:vertical"></textarea>
            </div>
            <div style="display:flex;gap:12px;margin-top:8px">
                <button type="button" onclick="closeModal()" style="flex:1;padding:11px;border:1.5px solid #e2e8f0;background:#fff;color:#64748b;border-radius:9px;font-size:13.5px;font-weight:600;cursor:pointer">Cancel</button>
                <button type="submit" id="p-btn" style="flex:2;padding:11px;background:linear-gradient(135deg,#10b981,#0d9488);color:#fff;border:none;border-radius:9px;font-size:13.5px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px">
                    <div id="p-spinner" style="display:none;width:16px;height:16px;border:2.5px solid rgba(255,255,255,.35);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite;flex-shrink:0"></div>
                    <span id="p-btn-text">Submit Request</span>
                </button>
            </div>
        </form>
    </div>
</div>
<style>@keyframes spin{to{transform:rotate(360deg)}}</style>

<script>
let cp=1;
function openModal(){ document.getElementById('payment-modal').classList.add('show'); document.getElementById('payment-form').reset(); document.getElementById('modal-alert').style.display='none'; }
function closeModal(){ document.getElementById('payment-modal').classList.remove('show'); }
document.getElementById('payment-modal').addEventListener('click',e=>{ if(e.target===e.currentTarget) closeModal(); });

function showModalAlert(msg,type='error'){
    const a=document.getElementById('modal-alert');
    a.innerHTML=`<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>${msg}</span>`;
    a.style.cssText=`display:flex;padding:10px 14px;border-radius:9px;font-size:13px;margin-bottom:14px;align-items:flex-start;gap:8px;background:${type==='error'?'#fff1f2':'#f0fdf4'};border:1.5px solid ${type==='error'?'#fecdd3':'#bbf7d0'};color:${type==='error'?'#be123c':'#15803d'}`;
}

document.getElementById('payment-form').addEventListener('submit',async e=>{
    e.preventDefault(); document.getElementById('modal-alert').style.display='none';
    const amount=document.getElementById('p-amount').value, mode=document.getElementById('p-mode').value,
          ref=document.getElementById('p-ref').value.trim(), proof=document.getElementById('p-proof').files[0],
          notes=document.getElementById('p-notes').value.trim();
    if(!amount||!mode||!ref||!proof){ showModalAlert('Fill all required fields including proof.'); return; }
    if(proof.size>5*1024*1024){ showModalAlert('Proof must be under 5MB.'); return; }
    const fd=new FormData();
    fd.append('amount',amount); fd.append('payment_mode',mode); fd.append('reference_number',ref);
    fd.append('proof_image',proof); if(notes) fd.append('notes',notes);
    document.getElementById('p-btn').disabled=true; document.getElementById('p-spinner').style.display='block'; document.getElementById('p-btn-text').textContent='Submitting…';
    try{
        const res=await fetch('/api/v1/seller/payments',{method:'POST',headers:{'Authorization':'Bearer '+localStorage.getItem('seller_token'),'Accept':'application/json'},body:fd});
        const data=await res.json();
        if(res.ok){ closeModal(); const ta=document.getElementById('top-alert'); ta.innerHTML='✓ Payment request submitted! Admin will review shortly.'; ta.style.cssText='display:flex;padding:12px 16px;border-radius:10px;font-size:13px;margin-bottom:16px;background:#f0fdf4;border:1.5px solid #bbf7d0;color:#15803d'; setTimeout(()=>ta.style.display='none',6000); loadPayments(1); }
        else{ const msg=data.errors?Object.values(data.errors).flat().join(' '):data.message||'Submission failed.'; showModalAlert(msg); }
    }catch{ showModalAlert('Network error. Try again.'); }
    finally{ document.getElementById('p-btn').disabled=false; document.getElementById('p-spinner').style.display='none'; document.getElementById('p-btn-text').textContent='Submit Request'; }
});

function loadPayments(page){
    cp=page||1;
    const params=new URLSearchParams({page:cp});
    const st=el('f-status').value, from=el('f-from').value, to=el('f-to').value;
    if(st) params.set('status',st); if(from) params.set('date_from',from); if(to) params.set('date_to',to);
    el('table-wrap').innerHTML='<div style="text-align:center;padding:40px;color:#64748b">Loading…</div>';
    apiFetch(`/api/v1/seller/payments?${params}`).then(data=>{
        // Stats
        if(data.summary){ el('s-wallet').textContent='₹'+fmtMoney(data.summary.wallet_balance||0); el('s-approved').textContent='₹'+fmtMoney(data.summary.approved_month||0); el('s-pending').textContent=data.summary.pending_count||0; }
        const rows=data.data||[];
        if(!rows.length){ el('table-wrap').innerHTML='<div style="text-align:center;padding:30px;color:#64748b">No requests found.</div>'; el('pagination').innerHTML=''; return; }
        let t=`<table class="table"><thead><tr><th>Date</th><th>Amount</th><th>Mode</th><th>Reference</th><th>Proof</th><th>Status</th><th>Processed</th><th>Admin Note</th></tr></thead><tbody>`;
        rows.forEach(r=>{ t+=`<tr>
            <td style="font-size:12.5px;white-space:nowrap">${fmtDate(r.created_at)}</td>
            <td style="font-weight:700;color:#10b981;font-size:15px">₹${fmtMoney(r.amount)}</td>
            <td><span style="font-size:12px;text-transform:capitalize;background:#f1f5f9;padding:3px 8px;border-radius:5px">${(r.payment_mode||'—').replace('_',' ')}</span></td>
            <td><span style="font-family:monospace;font-size:12px">${r.reference_number||'—'}</span></td>
            <td>${r.proof_image?`<a href="/storage/${r.proof_image}" target="_blank" style="color:#10b981;font-size:12px;font-weight:600;text-decoration:none">📎 View</a>`:'—'}</td>
            <td>${statusBadge(r.status)}</td>
            <td style="font-size:12.5px;color:#64748b">${r.processed_at?fmtDate(r.processed_at):'—'}</td>
            <td style="font-size:12.5px;color:#64748b;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="${r.admin_notes||''}">${r.admin_notes||'—'}</td>
        </tr>`; });
        t+='</tbody></table>'; el('table-wrap').innerHTML=t;
        const meta=data.meta||data, lp=meta.last_page||1;
        let pag=`<div style="display:flex;align-items:center;justify-content:space-between"><span style="font-size:13px;color:#64748b">Total: ${meta.total||0} requests</span><div style="display:flex;gap:6px">`;
        for(let i=1;i<=lp;i++) pag+=`<button onclick="loadPayments(${i})" style="padding:6px 11px;border-radius:7px;border:1.5px solid ${i===cp?'#10b981':'#e2e8f0'};background:${i===cp?'#10b981':'#fff'};color:${i===cp?'#fff':'#374151'};font-size:13px;cursor:pointer">${i}</button>`;
        pag+=`</div></div>`; el('pagination').innerHTML=pag;
    }).catch(()=>{ el('table-wrap').innerHTML='<div style="text-align:center;padding:40px;color:#ef4444">Failed to load.</div>'; });
}

// Load wallet balance for stats
apiFetch('/api/v1/seller/dashboard').then(d=>{ el('s-wallet').textContent='₹'+fmtMoney(d.wallet_balance||0); el('s-pending').textContent=d.pending_payments||0; }).catch(()=>{});
loadPayments(1);
</script>
@endsection
