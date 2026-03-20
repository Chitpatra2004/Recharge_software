@extends('layouts.seller')
@section('title','Account Ledger')
@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Account Ledger</h1>
        <p class="page-sub">All wallet credits and debits for your account</p>
    </div>
</div>

<!-- Balance Banner -->
<div style="background:linear-gradient(135deg,#10b981,#0d9488);border-radius:14px;padding:20px 24px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between">
    <div>
        <div style="font-size:12px;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Current Wallet Balance</div>
        <div id="wallet-balance" style="font-size:28px;font-weight:800;color:#fff">Loading…</div>
    </div>
    <a href="/seller/payments" style="background:rgba(255,255,255,.2);color:#fff;padding:9px 18px;border-radius:9px;font-size:13px;font-weight:600;text-decoration:none;border:1px solid rgba(255,255,255,.3)">+ Add Funds</a>
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
            <label style="font-size:11.5px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.4px">Type</label>
            <select id="f-type" class="form-control" style="width:130px">
                <option value="">All</option>
                <option value="credit">Credit</option>
                <option value="debit">Debit</option>
            </select>
        </div>
        <button onclick="loadLedger(1)" style="background:#10b981;color:#fff;border:none;padding:9px 20px;border-radius:9px;font-size:13.5px;font-weight:600;cursor:pointer;height:38px">Search</button>
    </div>
</div>

<div class="card">
    <div id="table-wrap"><div style="text-align:center;padding:40px;color:#64748b">Loading…</div></div>
    <div id="pagination" style="padding:16px 20px;border-top:1px solid #f1f5f9"></div>
</div>

<script>
let cp=1;
function loadLedger(page){
    cp=page||1;
    const params=new URLSearchParams({page:cp});
    const from=document.getElementById('f-from').value, to=document.getElementById('f-to').value, tp=document.getElementById('f-type').value;
    if(from) params.set('date_from',from); if(to) params.set('date_to',to); if(tp) params.set('type',tp);
    document.getElementById('table-wrap').innerHTML='<div style="text-align:center;padding:40px;color:#64748b">Loading…</div>';
    apiFetch(`/api/v1/seller/reports/ledger?${params}`).then(data=>{
        // Balance
        if(data.balance!==undefined) document.getElementById('wallet-balance').textContent='₹'+fmtMoney(data.balance);
        const rows=data.data||[];
        if(!rows.length){ document.getElementById('table-wrap').innerHTML='<div style="text-align:center;padding:30px;color:#64748b">No ledger entries found.</div>'; document.getElementById('pagination').innerHTML=''; return; }
        let t=`<table class="table"><thead><tr><th>Date</th><th>Description</th><th>Type</th><th>Amount</th><th>Balance After</th><th>Ref</th></tr></thead><tbody>`;
        rows.forEach(r=>{
            const isCredit=r.type==='credit';
            t+=`<tr>
                <td style="font-size:12.5px;white-space:nowrap">${fmtDate(r.created_at)}</td>
                <td style="font-size:13px;max-width:220px">${r.description||r.remarks||'—'}</td>
                <td><span class="${isCredit?'badge-success':'badge-danger'}" style="text-transform:capitalize">${r.type}</span></td>
                <td style="font-weight:700;color:${isCredit?'#10b981':'#ef4444'}">${isCredit?'+':'−'}₹${fmtMoney(r.amount)}</td>
                <td style="font-weight:500">₹${fmtMoney(r.balance_after||0)}</td>
                <td><span style="font-family:monospace;font-size:11.5px;color:#94a3b8">${r.reference||'—'}</span></td>
            </tr>`;
        });
        t+='</tbody></table>';
        document.getElementById('table-wrap').innerHTML=t;
        const meta=data.meta||data, lp=meta.last_page||1;
        let pag=`<div style="display:flex;align-items:center;justify-content:space-between">
            <span style="font-size:13px;color:#64748b">Total: ${meta.total||0} entries</span>
            <div style="display:flex;gap:6px">`;
        for(let i=1;i<=lp;i++) pag+=`<button onclick="loadLedger(${i})" style="padding:6px 11px;border-radius:7px;border:1.5px solid ${i===cp?'#10b981':'#e2e8f0'};background:${i===cp?'#10b981':'#fff'};color:${i===cp?'#fff':'#374151'};font-size:13px;cursor:pointer">${i}</button>`;
        pag+=`</div></div>`;
        document.getElementById('pagination').innerHTML=pag;
    }).catch(()=>{ document.getElementById('table-wrap').innerHTML='<div style="text-align:center;padding:40px;color:#ef4444">Failed to load ledger.</div>'; });
}
// Load balance separately
apiFetch('/api/v1/seller/reports/ledger?per_page=1').then(d=>{ if(d.balance!==undefined) document.getElementById('wallet-balance').textContent='₹'+fmtMoney(d.balance); }).catch(()=>{});
loadLedger(1);
</script>
@endsection
