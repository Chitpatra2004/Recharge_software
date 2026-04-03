@extends('layouts.seller')
@section('title','Account Report')
@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Account Report</h1>
        <p class="page-sub">Your account performance summary and statistics</p>
    </div>
</div>

<!-- Date Filters -->
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
        <button onclick="loadReport()" style="background:#10b981;color:#fff;border:none;padding:9px 20px;border-radius:9px;font-size:13.5px;font-weight:600;cursor:pointer;height:38px">Apply</button>
    </div>
</div>

<div id="report-wrap">
    <div style="text-align:center;padding:40px;color:#64748b">Loading report…</div>
</div>

<script>
function loadReport(){
    const from = document.getElementById('f-from').value;
    const to   = document.getElementById('f-to').value;
    const params = new URLSearchParams();
    if(from) params.set('date_from',from);
    if(to)   params.set('date_to',to);
    document.getElementById('report-wrap').innerHTML='<div style="text-align:center;padding:40px;color:#64748b">Loading…</div>';
    apiFetch(`/api/v1/seller/reports/account?${params}`).then(renderReport).catch(()=>{
        document.getElementById('report-wrap').innerHTML='<div style="text-align:center;padding:40px;color:#ef4444">Failed to load report.</div>';
    });
}

function renderReport(d){
    const s = d.data || {};
    const html = `
    <div class="stats-grid" style="margin-bottom:20px">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(16,185,129,.15)"><svg fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg></div>
            <div class="stat-body"><div class="stat-label">Total Recharges</div><div class="stat-value">${s.total_recharges||0}</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(59,130,246,.15)"><svg fill="none" viewBox="0 0 24 24" stroke="#3b82f6" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 1v8m0 0v1"/></svg></div>
            <div class="stat-body"><div class="stat-label">Total Volume</div><div class="stat-value">₹${fmtMoney(s.total_amount||0)}</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(16,185,129,.15)"><svg fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            <div class="stat-body"><div class="stat-label">Successful</div><div class="stat-value">${s.success_count||0}</div><div class="stat-sub">Wallet: ₹${fmtMoney(s.wallet_balance||0)}</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(239,68,68,.15)"><svg fill="none" viewBox="0 0 24 24" stroke="#ef4444" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            <div class="stat-body"><div class="stat-label">Failed</div><div class="stat-value">${s.failed_count||0}</div><div class="stat-sub">Success rate: ${s.success_rate||0}%</div></div>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h3 class="card-title">Daily Breakdown</h3></div>
        ${renderDailyTable(d.daily||[])}
    </div>`;
    document.getElementById('report-wrap').innerHTML = html;
}

function renderDailyTable(rows){
    if(!rows.length) return '<div style="text-align:center;padding:30px;color:#64748b;font-size:13.5px">No data for selected period.</div>';
    let t = `<table class="table"><thead><tr><th>Date</th><th>Total</th><th>Success</th><th>Failed</th><th>Volume (₹)</th><th>Success Rate</th></tr></thead><tbody>`;
    rows.forEach(r=>{
        const rate = r.total > 0 ? Math.round((r.success/r.total)*100) : 0;
        t += `<tr>
            <td style="font-weight:500">${r.date||'—'}</td>
            <td>${r.total||0}</td>
            <td><span class="badge-success">${r.success||0}</span></td>
            <td><span class="badge-danger">${r.failed||0}</span></td>
            <td style="font-weight:600">₹${fmtMoney(r.amount||0)}</td>
            <td>
                <div style="display:flex;align-items:center;gap:8px">
                    <div style="flex:1;height:6px;background:#f1f5f9;border-radius:3px;overflow:hidden"><div style="width:${rate}%;height:100%;background:#10b981;border-radius:3px"></div></div>
                    <span style="font-size:12px;font-weight:600;color:#374151;min-width:32px">${rate}%</span>
                </div>
            </td>
        </tr>`;
    });
    t += '</tbody></table>';
    return t;
}

loadReport();
</script>
@endsection
