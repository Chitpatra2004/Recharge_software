@extends('layouts.seller')
@section('title','Operator Report')
@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Operator Report</h1>
        <p class="page-sub">Recharge summary broken down by operator</p>
    </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom:20px">
    <div style="padding:16px 20px;display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
        <div style="display:flex;flex-direction:column;gap:4px">
            <label style="font-size:11.5px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.4px">From Date</label>
            <input type="date" id="f-from" class="form-control" style="width:150px">
        </div>
        <div style="display:flex;flex-direction:column;gap:4px">
            <label style="font-size:11.5px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.4px">To Date</label>
            <input type="date" id="f-to" class="form-control" style="width:150px">
        </div>
        <button onclick="loadData()" style="background:#10b981;color:#fff;border:none;padding:9px 20px;border-radius:9px;font-size:13.5px;font-weight:600;cursor:pointer;height:38px">Apply</button>
    </div>
</div>

<!-- Summary Stats -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px">
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(59,130,246,.15)"><svg fill="none" viewBox="0 0 24 24" stroke="#3b82f6" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></div>
        <div class="stat-body"><div class="stat-label">Total Txns</div><div class="stat-value" id="sTotal">—</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(16,185,129,.15)"><svg fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        <div class="stat-body"><div class="stat-label">Successful</div><div class="stat-value" id="sSuccess">—</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(239,68,68,.15)"><svg fill="none" viewBox="0 0 24 24" stroke="#ef4444" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        <div class="stat-body"><div class="stat-label">Failed</div><div class="stat-value" id="sFailure">—</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(245,158,11,.15)"><svg fill="none" viewBox="0 0 24 24" stroke="#f59e0b" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 1v8m0 0v1"/></svg></div>
        <div class="stat-body"><div class="stat-label">Total Volume</div><div class="stat-value" id="sVolume">—</div></div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">Operator-wise Breakdown</h3></div>
    <div id="op-wrap" style="padding:20px;text-align:center;color:#64748b">Loading…</div>
</div>

<script>
function loadData(){
    const from = document.getElementById('f-from').value;
    const to   = document.getElementById('f-to').value;
    const params = new URLSearchParams();
    if(from) params.set('date_from', from);
    if(to)   params.set('date_to', to);

    document.getElementById('op-wrap').innerHTML = '<div style="text-align:center;padding:20px;color:#64748b">Loading…</div>';
    ['sTotal','sSuccess','sFailure','sVolume'].forEach(id => document.getElementById(id).textContent = '—');

    apiFetch('/api/v1/seller/reports/operator?' + params).then(d => {
        const rows = d.operators || [];

        if(!rows.length){
            document.getElementById('op-wrap').innerHTML = '<div style="text-align:center;padding:30px;color:#64748b;font-size:13.5px">No operator data for selected period.</div>';
            ['sTotal','sSuccess','sFailure'].forEach(id => document.getElementById(id).textContent = '0');
            document.getElementById('sVolume').textContent = '₹0.00';
            return;
        }

        // Summary totals
        const totTxn  = rows.reduce((s,r) => s + (r.count||0), 0);
        const totSucc = rows.reduce((s,r) => s + (r.success||0), 0);
        const totFail = rows.reduce((s,r) => s + (r.failed||0), 0);
        const totVol  = rows.reduce((s,r) => s + parseFloat(r.total_amount||0), 0);
        document.getElementById('sTotal').textContent   = totTxn;
        document.getElementById('sSuccess').textContent = totSucc;
        document.getElementById('sFailure').textContent = totFail;
        document.getElementById('sVolume').textContent  = '₹' + fmtMoney(totVol);

        // Table
        let html = `<table class="table"><thead><tr>
            <th>Operator</th><th>Total</th><th>Success</th><th>Failed</th><th>Amount (₹)</th><th>Success Rate</th>
        </tr></thead><tbody>`;
        rows.forEach(r => {
            const total = r.count || 0;
            const succ  = r.success || 0;
            const rate  = total > 0 ? Math.round((succ/total)*100) : 0;
            const rateClr = rate >= 90 ? '#10b981' : rate >= 75 ? '#f59e0b' : '#ef4444';
            html += `<tr>
                <td style="font-weight:600">${r.operator||'—'}</td>
                <td>${total}</td>
                <td><span class="badge-success">${succ}</span></td>
                <td><span class="badge-danger">${r.failed||0}</span></td>
                <td style="font-weight:600">₹${fmtMoney(r.total_amount||0)}</td>
                <td>
                    <div style="display:flex;align-items:center;gap:8px">
                        <div style="flex:1;height:6px;background:#f1f5f9;border-radius:3px;overflow:hidden;min-width:60px"><div style="width:${rate}%;height:100%;background:${rateClr};border-radius:3px"></div></div>
                        <span style="font-size:12px;font-weight:700;color:${rateClr};min-width:34px">${rate}%</span>
                    </div>
                </td>
            </tr>`;
        });
        html += '</tbody></table>';
        document.getElementById('op-wrap').innerHTML = html;

    }).catch(() => {
        document.getElementById('op-wrap').innerHTML = '<div style="text-align:center;padding:30px;color:#ef4444;font-size:13.5px">Failed to load operator data.</div>';
    });
}

loadData();
</script>
@endsection
