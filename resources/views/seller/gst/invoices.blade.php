@extends('layouts.seller')
@section('title','GST Invoices')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">GST Invoices</h1>
        <p class="page-sub">Upload and manage your GST invoices</p>
    </div>
    <button onclick="openUploadModal()" style="display:inline-flex;align-items:center;gap:6px;background:#10b981;color:#fff;padding:9px 18px;border-radius:9px;font-size:13.5px;font-weight:600;border:none;cursor:pointer">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:15px;height:15px"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
        Upload Invoice
    </button>
</div>

<div style="background:#fffbeb;border:1.5px solid #fde68a;border-radius:10px;padding:12px 16px;margin-bottom:18px;display:flex;align-items:center;gap:10px;font-size:13px;color:#92400e">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    Upload your monthly GST invoices here. Admin will verify and mark them as approved.
</div>

<!-- Filters -->
<div class="card" style="margin-bottom:16px">
    <div style="padding:14px 20px;display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
        <div style="display:flex;flex-direction:column;gap:4px">
            <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.4px">From Date</label>
            <input type="date" id="f-from" class="form-control" style="width:148px">
        </div>
        <div style="display:flex;flex-direction:column;gap:4px">
            <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.4px">To Date</label>
            <input type="date" id="f-to" class="form-control" style="width:148px">
        </div>
        <button onclick="loadInvoices(1)" style="background:#10b981;color:#fff;border:none;padding:9px 18px;border-radius:9px;font-size:13px;font-weight:600;cursor:pointer;height:38px">Search</button>
    </div>
</div>

<div class="card">
    <div id="table-wrap"><div style="text-align:center;padding:40px;color:#64748b">Loading…</div></div>
    <div id="pagination" style="padding:16px 20px;border-top:1px solid #f1f5f9"></div>
</div>

<!-- Upload Modal -->
<div class="modal-overlay" id="upload-modal">
    <div class="modal-box" style="max-width:500px">
        <button class="modal-close" onclick="closeUploadModal()"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
        <h3 style="font-size:18px;font-weight:700;color:#1e293b;margin-bottom:6px">Upload GST Invoice</h3>
        <p style="font-size:13px;color:#64748b;margin-bottom:20px">Upload your GST invoice file for admin verification.</p>
        <div id="upload-alert" style="display:none;padding:10px 14px;border-radius:9px;font-size:13px;margin-bottom:14px;align-items:flex-start;gap:8px"></div>
        <form id="upload-form">
            <div class="form-group">
                <label class="form-label">Invoice Number <span style="color:#ef4444">*</span></label>
                <input type="text" id="g-invno" class="form-control" placeholder="e.g. GST/2026/03" required>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                <div class="form-group">
                    <label class="form-label">Invoice Date <span style="color:#ef4444">*</span></label>
                    <input type="date" id="g-date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Invoice Amount (₹) <span style="color:#ef4444">*</span></label>
                    <input type="number" id="g-amount" class="form-control" placeholder="Total invoice amount" min="1" required>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                <div class="form-group">
                    <label class="form-label">GST Amount (₹) <span style="color:#ef4444">*</span></label>
                    <input type="number" id="g-gst" class="form-control" placeholder="GST portion" min="0" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Period From <span style="color:#ef4444">*</span></label>
                    <input type="date" id="g-period-from" class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Period To <span style="color:#ef4444">*</span></label>
                <input type="date" id="g-period-to" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Invoice File (PDF/Image) <span style="color:#ef4444">*</span></label>
                <input type="file" id="g-file" accept=".pdf,image/*" required style="width:100%;padding:8px 12px;border:1.5px solid #e5e7eb;border-radius:9px;font-size:13px;background:#f9fafb">
                <small style="font-size:11.5px;color:#94a3b8;display:block;margin-top:4px">PDF, JPG, PNG · max 5MB</small>
            </div>
            <div style="display:flex;gap:12px;margin-top:8px">
                <button type="button" onclick="closeUploadModal()" style="flex:1;padding:11px;border:1.5px solid #e2e8f0;background:#fff;color:#64748b;border-radius:9px;font-size:13.5px;font-weight:600;cursor:pointer">Cancel</button>
                <button type="submit" id="g-btn" style="flex:2;padding:11px;background:linear-gradient(135deg,#10b981,#0d9488);color:#fff;border:none;border-radius:9px;font-size:13.5px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px">
                    <div id="g-spinner" style="display:none;width:16px;height:16px;border:2.5px solid rgba(255,255,255,.35);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite"></div>
                    <span id="g-btn-text">Upload Invoice</span>
                </button>
            </div>
        </form>
    </div>
</div>
<style>@keyframes spin{to{transform:rotate(360deg)}}</style>

<script>
let cp=1;
function openUploadModal(){ document.getElementById('upload-modal').classList.add('show'); document.getElementById('upload-form').reset(); document.getElementById('upload-alert').style.display='none'; }
function closeUploadModal(){ document.getElementById('upload-modal').classList.remove('show'); }
document.getElementById('upload-modal').addEventListener('click',e=>{ if(e.target===e.currentTarget) closeUploadModal(); });

function showUploadAlert(msg,type='error'){
    const a=document.getElementById('upload-alert');
    a.innerHTML=`<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>${msg}</span>`;
    a.style.cssText=`display:flex;padding:10px 14px;border-radius:9px;font-size:13px;margin-bottom:14px;align-items:flex-start;gap:8px;background:${type==='error'?'#fff1f2':'#f0fdf4'};border:1.5px solid ${type==='error'?'#fecdd3':'#bbf7d0'};color:${type==='error'?'#be123c':'#15803d'}`;
}

document.getElementById('upload-form').addEventListener('submit',async e=>{
    e.preventDefault(); document.getElementById('upload-alert').style.display='none';
    const invno=document.getElementById('g-invno').value.trim(), date=document.getElementById('g-date').value,
          amount=document.getElementById('g-amount').value, gst=document.getElementById('g-gst').value,
          pfrom=document.getElementById('g-period-from').value, pto=document.getElementById('g-period-to').value,
          file=document.getElementById('g-file').files[0];
    if(!invno||!date||!amount||!gst||!pfrom||!pto||!file){ showUploadAlert('Please fill all required fields.'); return; }
    if(file.size>5*1024*1024){ showUploadAlert('File must be under 5MB.'); return; }
    const fd=new FormData();
    fd.append('invoice_number',invno); fd.append('invoice_date',date); fd.append('amount',amount);
    fd.append('gst_amount',gst); fd.append('period_from',pfrom); fd.append('period_to',pto); fd.append('file',file);
    document.getElementById('g-btn').disabled=true; document.getElementById('g-spinner').style.display='block'; document.getElementById('g-btn-text').textContent='Uploading…';
    try{
        const res=await fetch('/api/v1/seller/gst',{method:'POST',headers:{'Authorization':'Bearer '+localStorage.getItem('seller_token'),'Accept':'application/json'},body:fd});
        const data=await res.json();
        if(res.ok){ closeUploadModal(); loadInvoices(1); }
        else{ const msg=data.errors?Object.values(data.errors).flat().join(' '):data.message||'Upload failed.'; showUploadAlert(msg); }
    }catch{ showUploadAlert('Network error. Try again.'); }
    finally{ document.getElementById('g-btn').disabled=false; document.getElementById('g-spinner').style.display='none'; document.getElementById('g-btn-text').textContent='Upload Invoice'; }
});

function loadInvoices(page){
    cp=page||1;
    const params=new URLSearchParams({page:cp});
    const from=el('f-from').value, to=el('f-to').value;
    if(from) params.set('date_from',from); if(to) params.set('date_to',to);
    el('table-wrap').innerHTML='<div style="text-align:center;padding:40px;color:#64748b">Loading…</div>';
    apiFetch(`/api/v1/seller/gst?${params}`).then(data=>{
        const rows=data.data||[];
        if(!rows.length){ el('table-wrap').innerHTML='<div style="text-align:center;padding:30px;color:#64748b">No invoices found.</div>'; el('pagination').innerHTML=''; return; }
        let t=`<table class="table"><thead><tr><th>Invoice No.</th><th>Date</th><th>Period</th><th>Amount</th><th>GST Amount</th><th>File</th><th>Uploaded</th></tr></thead><tbody>`;
        rows.forEach(r=>{
            t+=`<tr>
                <td><span style="font-family:monospace;font-size:12px;font-weight:600">${r.invoice_number||'—'}</span></td>
                <td style="font-size:12.5px">${r.invoice_date||'—'}</td>
                <td style="font-size:12.5px;color:#64748b">${r.period_from||''}  –  ${r.period_to||''}</td>
                <td style="font-weight:600">₹${fmtMoney(r.amount||0)}</td>
                <td style="font-weight:500;color:#f59e0b">₹${fmtMoney(r.gst_amount||0)}</td>
                <td>${r.file_path?`<a href="/storage/${r.file_path}" target="_blank" style="color:#10b981;font-size:12px;font-weight:600;text-decoration:none">📎 View</a>`:'—'}</td>
                <td style="font-size:12.5px;color:#64748b;white-space:nowrap">${fmtDate(r.created_at)}</td>
            </tr>`;
        });
        t+='</tbody></table>'; el('table-wrap').innerHTML=t;
        const meta=data.meta||data, lp=meta.last_page||1;
        let pag=`<div style="display:flex;align-items:center;justify-content:space-between"><span style="font-size:13px;color:#64748b">Total: ${meta.total||0} invoices</span><div style="display:flex;gap:6px">`;
        for(let i=1;i<=lp;i++) pag+=`<button onclick="loadInvoices(${i})" style="padding:6px 11px;border-radius:7px;border:1.5px solid ${i===cp?'#10b981':'#e2e8f0'};background:${i===cp?'#10b981':'#fff'};color:${i===cp?'#fff':'#374151'};font-size:13px;cursor:pointer">${i}</button>`;
        pag+=`</div></div>`; el('pagination').innerHTML=pag;
    }).catch(()=>{ el('table-wrap').innerHTML='<div style="text-align:center;padding:40px;color:#ef4444">Failed to load invoices.</div>'; });
}
loadInvoices(1);
</script>
@endsection
