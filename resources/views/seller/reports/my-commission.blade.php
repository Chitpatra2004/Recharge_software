@extends('layouts.seller')
@section('title','My Commission')
@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">My Commission</h1>
        <p class="page-sub">Operator-wise commission settings and earned commission</p>
    </div>
</div>

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
        <button onclick="loadData()" style="background:#2563eb;color:#fff;border:none;padding:9px 20px;border-radius:9px;font-size:13.5px;font-weight:600;cursor:pointer;height:38px">Apply</button>
        <button onclick="document.getElementById('f-from').value='';document.getElementById('f-to').value='';loadData()" style="background:#f1f5f9;color:#374151;border:1px solid #e2e8f0;padding:9px 16px;border-radius:9px;font-size:13px;cursor:pointer;height:38px">Reset</button>
    </div>
</div>

<div class="stats-grid" style="margin-bottom:18px">
    <div class="stat-card blue">
        <div class="stat-icon" style="background:rgba(37,99,235,.12)">
            <svg fill="none" viewBox="0 0 24 24" stroke="var(--blue)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
        </div>
        <div class="stat-body"><div class="stat-label">Operators</div><div class="stat-value" id="sOperators">-</div></div>
    </div>
    <div class="stat-card orange">
        <div class="stat-icon" style="background:rgba(245,158,11,.12)">
            <svg fill="none" viewBox="0 0 24 24" stroke="var(--orange)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="stat-body"><div class="stat-label">Active Commission</div><div class="stat-value" id="sActive">-</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(37,99,235,.12)">
            <svg fill="none" viewBox="0 0 24 24" stroke="var(--blue)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 1v8m0 0v1"/></svg>
        </div>
        <div class="stat-body"><div class="stat-label">Success Amount</div><div class="stat-value" id="sAmount">-</div></div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon" style="background:rgba(16,185,129,.12)">
            <svg fill="none" viewBox="0 0 24 24" stroke="var(--green)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
        </div>
        <div class="stat-body"><div class="stat-label">Earned Commission</div><div class="stat-value" id="sCommission">-</div></div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">Commission Details</h3></div>
    <div id="commission-wrap" style="overflow-x:auto">
        <div style="text-align:center;padding:40px;color:#64748b">Loading...</div>
    </div>
</div>


<script>
function loadData(){
    const params = new URLSearchParams();
    const from = document.getElementById('f-from').value;
    const to = document.getElementById('f-to').value;
    if(from) params.set('date_from', from);
    if(to) params.set('date_to', to);

    document.getElementById('commission-wrap').innerHTML = '<div style="text-align:center;padding:40px;color:#64748b">Loading...</div>';
    apiFetch('/api/v1/seller/reports/my-commission?' + params).then(d => {
        const s = d.summary || {};
        document.getElementById('sOperators').textContent = s.operators || 0;
        document.getElementById('sActive').textContent = s.active || 0;
        document.getElementById('sAmount').textContent = 'Rs.' + fmtMoney(s.total_success_amount || 0);
        document.getElementById('sCommission').textContent = 'Rs.' + fmtMoney(s.total_earned_commission || 0);
        renderTable(d.data || []);
    }).catch(() => {
        document.getElementById('commission-wrap').innerHTML = '<div style="text-align:center;padding:40px;color:#ef4444">Failed to load commission report.</div>';
    });
}

function renderTable(rows){
    if(!rows.length){
        document.getElementById('commission-wrap').innerHTML = '<div style="text-align:center;padding:30px;color:#64748b">No commission settings found.</div>';
        return;
    }

    let html = `<table><thead><tr>
        <th>#</th><th>Service</th><th>Operator</th><th>Commission</th><th>Status</th><th>Success Txns</th><th>Success Amount</th><th>Earned</th>
    </tr></thead><tbody>`;
    rows.forEach((r, idx) => {
        const type = r.commission_type === 'flat' ? 'Flat' : 'Percentage';
        const value = r.commission_type === 'flat' ? 'Rs.' + fmtMoney(r.commission || 0) : fmtMoney(r.commission || 0) + '%';
        html += `<tr>
            <td style="color:#94a3b8">${idx + 1}</td>
            <td style="text-transform:capitalize">${r.category || '-'}</td>
            <td><strong>${r.operator_name || r.operator_code}</strong><div style="font-size:11px;color:#94a3b8">${r.operator_code || ''}</div></td>
            <td><strong>${value}</strong><div style="font-size:11px;color:#64748b">${type}</div></td>
            <td>${r.is_active ? '<span class="badge-success">Active</span>' : '<span class="badge-failed">Blocked</span>'}</td>
            <td>${r.success_count || 0}</td>
            <td style="font-weight:700">Rs.${fmtMoney(r.success_amount || 0)}</td>
            <td style="font-weight:800;color:#10b981">Rs.${fmtMoney(r.earned_commission || 0)}</td>
        </tr>`;
    });
    html += '</tbody></table>';
    document.getElementById('commission-wrap').innerHTML = html;
}

loadData();
</script>
@endsection
