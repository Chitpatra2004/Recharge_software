@extends('layouts.admin')
@section('title', 'Commission History')
@section('page-title', 'Commission History')

@section('content')
<div class="breadcrumb">
    <a href="/admin/dashboard">Dashboard</a>
    <svg class="breadcrumb-sep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>Commission History</span>
</div>

{{-- Filters --}}
<div class="card" style="margin-bottom:20px">
    <div class="card-body" style="padding:16px 20px">
        <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
            <div>
                <label style="font-size:11px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">Date From</label>
                <input type="date" id="f-date-from" style="border:1px solid var(--border);border-radius:6px;padding:6px 10px;font-size:13px">
            </div>
            <div>
                <label style="font-size:11px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">Date To</label>
                <input type="date" id="f-date-to" style="border:1px solid var(--border);border-radius:6px;padding:6px 10px;font-size:13px">
            </div>
            <div>
                <label style="font-size:11px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">Operator</label>
                <input type="text" id="f-operator" placeholder="AIRTEL, JIO…" style="border:1px solid var(--border);border-radius:6px;padding:6px 10px;font-size:13px;width:120px">
            </div>
            <button class="btn btn-primary btn-sm" onclick="loadHistory()">Apply</button>
            <button class="btn btn-outline btn-sm" onclick="clearFilters()">Clear</button>
        </div>
    </div>
</div>

{{-- Summary --}}
<div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:20px">
    <div class="stat-card green"><div class="stat-header"><div class="stat-label">Total Commission Earned</div></div><div class="stat-value" id="s-total" style="font-size:22px">—</div></div>
    <div class="stat-card blue"><div class="stat-header"><div class="stat-label">Transactions</div></div><div class="stat-value" id="s-txns">—</div></div>
    <div class="stat-card orange"><div class="stat-header"><div class="stat-label">Avg Commission / Txn</div></div><div class="stat-value" id="s-avg" style="font-size:22px">—</div></div>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Txn ID</th>
                    <th>Mobile</th>
                    <th>Operator</th>
                    <th>Amount</th>
                    <th>Commission %</th>
                    <th>Commission Earned</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody id="hist-tbody">
                <tr><td colspan="7"><div class="loading-overlay"><div class="spinner"></div> Loading…</div></td></tr>
            </tbody>
        </table>
    </div>
    <div class="card-footer" id="pagination" style="gap:8px;justify-content:flex-end"></div>
</div>
@endsection

@push('scripts')
<script>
async function loadHistory(page = 1) {
    const p = new URLSearchParams({ page, per_page: 50 });
    const df = document.getElementById('f-date-from').value;
    const dt = document.getElementById('f-date-to').value;
    const op = document.getElementById('f-operator').value.trim();
    if (df) p.set('date_from', df);
    if (dt) p.set('date_to', dt);
    if (op) p.set('operator_code', op);
    p.set('status', 'success');

    const res = await apiFetch('/api/v1/admin/reports/recharges?' + p.toString());
    if (!res) return;
    const data = await res.json();

    const s = data.summary || {};
    const totalComm = (s.total_amount || 0) * 0.025; // example 2.5% placeholder
    document.getElementById('s-total').textContent = fmtAmt(s.total_commission || totalComm);
    document.getElementById('s-txns').textContent  = fmtNum(s.total_count);
    const avg = s.total_count ? (s.total_commission || totalComm) / s.total_count : 0;
    document.getElementById('s-avg').textContent   = fmtAmt(avg);

    const txns = data.transactions?.data || data.transactions || [];
    document.getElementById('hist-tbody').innerHTML = txns.length
        ? txns.map(t => {
            const slabs = JSON.parse(localStorage.getItem('commission_slabs') || '[]');
            const slab = slabs.find(s => s.operator === (t.operator_code||'').toUpperCase()) || null;
            const pct  = slab?.commission_pct || 2.5;
            const comm = ((t.amount || 0) * pct / 100).toFixed(2);
            return `<tr>
                <td style="font-family:monospace;font-size:11px">${t.id||'—'}</td>
                <td>${t.mobile||'—'}</td>
                <td>${t.operator_code||'—'}</td>
                <td>${fmtAmt(t.amount)}</td>
                <td>${pct}%</td>
                <td style="color:var(--accent-green);font-weight:600">₹${comm}</td>
                <td>${fmtAgo(t.created_at)}</td>
            </tr>`;
        }).join('')
        : '<tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:24px">No commission records found</td></tr>';

    // Pagination
    const meta = data.transactions;
    const pag  = document.getElementById('pagination');
    if (meta?.last_page > 1) {
        let html = '';
        if (meta.current_page > 1) html += `<button class="btn btn-outline btn-sm" onclick="loadHistory(${meta.current_page-1})">Prev</button>`;
        html += `<span style="font-size:12px;color:var(--text-muted)">Page ${meta.current_page} of ${meta.last_page}</span>`;
        if (meta.current_page < meta.last_page) html += `<button class="btn btn-outline btn-sm" onclick="loadHistory(${meta.current_page+1})">Next</button>`;
        pag.innerHTML = html;
    } else { pag.innerHTML = ''; }
}

function clearFilters() {
    ['f-date-from','f-date-to','f-operator'].forEach(id => document.getElementById(id).value = '');
    loadHistory();
}

document.addEventListener('DOMContentLoaded', () => {
    const today = new Date().toISOString().slice(0,10);
    const month = new Date(Date.now() - 30*86400000).toISOString().slice(0,10);
    document.getElementById('f-date-from').value = month;
    document.getElementById('f-date-to').value   = today;
    loadHistory();
});
</script>
@endpush
