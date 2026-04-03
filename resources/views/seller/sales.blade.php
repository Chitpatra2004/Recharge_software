@extends('layouts.seller')
@section('title','Sales Transactions')
@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Sales Transactions</h1>
        <p class="page-sub">All recharge transactions processed via your API</p>
    </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom:20px">
    <div style="padding:16px 20px;display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end">
        <div style="display:flex;flex-direction:column;gap:4px">
            <label style="font-size:11.5px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.4px">From Date</label>
            <input type="date" id="f-from" class="form-control" style="width:150px">
        </div>
        <div style="display:flex;flex-direction:column;gap:4px">
            <label style="font-size:11.5px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.4px">To Date</label>
            <input type="date" id="f-to" class="form-control" style="width:150px">
        </div>
        <div style="display:flex;flex-direction:column;gap:4px">
            <label style="font-size:11.5px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.4px">Operator</label>
            <input type="text" id="f-operator" class="form-control" placeholder="e.g. AIRTEL" style="width:130px">
        </div>
        <div style="display:flex;flex-direction:column;gap:4px">
            <label style="font-size:11.5px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.4px">Status</label>
            <select id="f-status" class="form-control" style="width:130px">
                <option value="">All</option>
                <option value="success">Success</option>
                <option value="failed">Failed</option>
                <option value="pending">Pending</option>
                <option value="refunded">Refunded</option>
            </select>
        </div>
        <div style="display:flex;flex-direction:column;gap:4px">
            <label style="font-size:11.5px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.4px">Mobile</label>
            <input type="text" id="f-mobile" class="form-control" placeholder="Mobile no." style="width:140px" maxlength="10">
        </div>
        <button onclick="loadSales(1)" style="background:#10b981;color:#fff;border:none;padding:9px 20px;border-radius:9px;font-size:13.5px;font-weight:600;cursor:pointer;height:38px">Search</button>
        <button onclick="resetFilters()" style="background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;padding:9px 16px;border-radius:9px;font-size:13px;font-weight:500;cursor:pointer;height:38px">Reset</button>
    </div>
</div>

<!-- Summary -->
<div id="summary-strip" style="display:none;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px 16px;margin-bottom:16px;display:flex;gap:24px;flex-wrap:wrap"></div>

<!-- Table -->
<div class="card">
    <div id="table-wrap">
        <div style="text-align:center;padding:40px;color:#64748b">Loading…</div>
    </div>
    <div id="pagination" style="padding:16px 20px;display:flex;align-items:center;justify-content:space-between;border-top:1px solid #f1f5f9"></div>
</div>

<script>
let currentPage = 1;

function resetFilters(){
    ['f-from','f-to','f-operator','f-mobile'].forEach(id=>document.getElementById(id).value='');
    document.getElementById('f-status').value='';
    loadSales(1);
}

function loadSales(page){
    currentPage = page;
    const params = new URLSearchParams({ page });
    const from = document.getElementById('f-from').value;
    const to   = document.getElementById('f-to').value;
    const op   = document.getElementById('f-operator').value.trim();
    const st   = document.getElementById('f-status').value;
    const mob  = document.getElementById('f-mobile').value.trim();
    if(from) params.set('date_from', from);
    if(to)   params.set('date_to',   to);
    if(op)   params.set('operator',  op);
    if(st)   params.set('status',    st);
    if(mob)  params.set('mobile',    mob);

    document.getElementById('table-wrap').innerHTML = '<div style="text-align:center;padding:40px;color:#64748b">Loading…</div>';
    document.getElementById('pagination').innerHTML = '';

    apiFetch(`/api/v1/seller/sales?${params}`).then(data=>{
        renderTable(data);
    }).catch(()=>{
        document.getElementById('table-wrap').innerHTML = '<div style="text-align:center;padding:40px;color:#ef4444">Failed to load transactions.</div>';
    });
}

function renderTable(data){
    const rows = data.data || [];
    if(!rows.length){
        document.getElementById('table-wrap').innerHTML = '<div style="text-align:center;padding:40px;color:#64748b;font-size:14px">No transactions found for selected filters.</div>';
        document.getElementById('pagination').innerHTML = '';
        return;
    }
    let html = `<table class="table">
        <thead><tr>
            <th>Date/Time</th><th>Mobile</th><th>Operator</th><th>Circle</th><th>Amount</th><th>Txn ID</th><th>Your Ref</th><th>Status</th>
        </tr></thead><tbody>`;
    rows.forEach(t=>{
        html += `<tr>
            <td style="font-size:12.5px;white-space:nowrap">${fmtDate(t.created_at)}</td>
            <td style="font-weight:500">${t.mobile||t.mobile_number||'—'}</td>
            <td><span style="font-size:12px;font-weight:600">${t.operator_code||t.operator||'—'}</span></td>
            <td style="font-size:12px">${t.circle||'—'}</td>
            <td style="font-weight:600">₹${fmtMoney(t.amount)}</td>
            <td><span style="font-family:monospace;font-size:11.5px;color:#64748b">${t.operator_txn_id||t.transaction_id||'—'}</span></td>
            <td><span style="font-family:monospace;font-size:11.5px;color:#64748b">${t.external_ref||'—'}</span></td>
            <td>${statusBadge(t.status)}</td>
        </tr>`;
    });
    html += '</tbody></table>';
    document.getElementById('table-wrap').innerHTML = html;

    // Pagination
    const meta = data.meta || data;
    const total = meta.total||0, lastPage = meta.last_page||1, cp = meta.current_page||currentPage;
    let pag = `<div style="font-size:13px;color:#64748b">Showing ${((cp-1)*(meta.per_page||15))+1}–${Math.min(cp*(meta.per_page||15),total)} of ${total}</div>`;
    pag += `<div style="display:flex;gap:6px">`;
    for(let i=1;i<=lastPage;i++){
        if(lastPage>7 && i>2 && i<lastPage-1 && Math.abs(i-cp)>1){ pag+='<span style="padding:6px 4px;color:#64748b">…</span>'; i=cp>4?cp-1:lastPage-2; continue; }
        pag += `<button onclick="loadSales(${i})" style="padding:6px 11px;border-radius:7px;border:1.5px solid ${i===cp?'#10b981':'#e2e8f0'};background:${i===cp?'#10b981':'#fff'};color:${i===cp?'#fff':'#374151'};font-size:13px;font-weight:${i===cp?'700':'400'};cursor:pointer">${i}</button>`;
    }
    pag += `</div>`;
    document.getElementById('pagination').innerHTML = pag;
}

loadSales(1);
</script>
@endsection
