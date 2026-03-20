@extends('layouts.admin')
@section('title', 'Resolved Complaints')
@section('page-title', 'Resolved Complaints')

@section('content')
<div class="breadcrumb">
    <a href="/admin/dashboard">Dashboard</a>
    <svg class="breadcrumb-sep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <a href="/admin/complaints">Complaints</a>
    <svg class="breadcrumb-sep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>Resolved</span>
</div>

{{-- Filters --}}
<div class="card" style="margin-bottom:20px">
    <div class="card-body" style="padding:16px 20px">
        <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
            <div>
                <label style="font-size:11px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">Status</label>
                <select id="f-status" style="border:1px solid var(--border);border-radius:6px;padding:6px 10px;font-size:13px">
                    <option value="">Resolved &amp; Closed</option>
                    <option value="resolved">Resolved only</option>
                    <option value="closed">Closed only</option>
                </select>
            </div>
            <div>
                <label style="font-size:11px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">Type</label>
                <select id="f-type" style="border:1px solid var(--border);border-radius:6px;padding:6px 10px;font-size:13px">
                    <option value="">All Types</option>
                    <option value="recharge_failed">Recharge Failed</option>
                    <option value="balance_deducted">Balance Deducted</option>
                    <option value="wrong_recharge">Wrong Recharge</option>
                    <option value="refund">Refund</option>
                    <option value="operator_delay">Operator Delay</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div>
                <label style="font-size:11px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">Date From</label>
                <input type="date" id="f-from" style="border:1px solid var(--border);border-radius:6px;padding:6px 10px;font-size:13px">
            </div>
            <div>
                <label style="font-size:11px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">Date To</label>
                <input type="date" id="f-to" style="border:1px solid var(--border);border-radius:6px;padding:6px 10px;font-size:13px">
            </div>
            <button class="btn btn-primary btn-sm" onclick="loadResolved()">Apply</button>
            <button class="btn btn-outline btn-sm" onclick="clearFilters()">Clear</button>
        </div>
    </div>
</div>

{{-- Stats --}}
<div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:20px">
    <div class="stat-card green"><div class="stat-header"><div class="stat-label">Resolved</div></div><div class="stat-value" id="s-resolved">—</div></div>
    <div class="stat-card blue"><div class="stat-header"><div class="stat-label">Closed</div></div><div class="stat-value" id="s-closed">—</div></div>
    <div class="stat-card orange"><div class="stat-header"><div class="stat-label">Avg Resolution Time</div></div><div class="stat-value" id="s-avg" style="font-size:18px">—</div></div>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Type</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Opened</th>
                    <th>Resolved</th>
                </tr>
            </thead>
            <tbody id="comp-tbody">
                <tr><td colspan="7"><div class="loading-overlay"><div class="spinner"></div> Loading…</div></td></tr>
            </tbody>
        </table>
    </div>
    <div class="card-footer" id="pagination" style="gap:8px;justify-content:flex-end"></div>
</div>
@endsection

@push('scripts')
<script>
const PRIORITY_COLOR = { low:'var(--text-muted)', medium:'var(--accent-orange)', high:'var(--accent-red)', critical:'#7c3aed' };

async function loadResolved(page = 1) {
    const p = new URLSearchParams({ page, per_page: 30 });
    const status = document.getElementById('f-status').value;
    const type   = document.getElementById('f-type').value;
    const from   = document.getElementById('f-from').value;
    const to     = document.getElementById('f-to').value;
    if (status) p.set('status', status);
    else        p.set('status', 'resolved,closed');
    if (type) p.set('type', type);
    if (from) p.set('date_from', from);
    if (to)   p.set('date_to', to);

    const res = await apiFetch('/api/v1/admin/reports/complaints?' + p.toString());
    if (!res) return;
    const data = await res.json();

    const items    = data.complaints?.data || data.complaints || [];
    const resolved = items.filter(c => c.status === 'resolved').length;
    const closed   = items.filter(c => c.status === 'closed').length;
    document.getElementById('s-resolved').textContent = resolved;
    document.getElementById('s-closed').textContent   = closed;

    // Avg resolution time estimate
    const s = data.summary || {};
    document.getElementById('s-avg').textContent = s.avg_resolution_hours
        ? s.avg_resolution_hours + 'h'
        : '—';

    document.getElementById('comp-tbody').innerHTML = items.length
        ? items.map(c => `<tr>
            <td style="font-family:monospace;font-size:11px">${c.id||'—'}</td>
            <td>${c.user?.name || c.user_id || '—'}</td>
            <td style="font-size:12px">${(c.type||'—').replace(/_/g,' ')}</td>
            <td><span style="font-size:11px;font-weight:600;color:${PRIORITY_COLOR[c.priority]||'inherit'}">${c.priority||'—'}</span></td>
            <td><span class="txn-status success">${c.status||'—'}</span></td>
            <td>${fmtAgo(c.created_at)}</td>
            <td>${fmtAgo(c.resolved_at||c.updated_at)}</td>
          </tr>`).join('')
        : '<tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:24px">No resolved complaints in this range</td></tr>';

    const meta = data.complaints;
    const pag  = document.getElementById('pagination');
    if (meta?.last_page > 1) {
        let html = '';
        if (meta.current_page > 1) html += `<button class="btn btn-outline btn-sm" onclick="loadResolved(${meta.current_page-1})">Prev</button>`;
        html += `<span style="font-size:12px;color:var(--text-muted)">Page ${meta.current_page} of ${meta.last_page}</span>`;
        if (meta.current_page < meta.last_page) html += `<button class="btn btn-outline btn-sm" onclick="loadResolved(${meta.current_page+1})">Next</button>`;
        pag.innerHTML = html;
    } else { pag.innerHTML = ''; }
}

function clearFilters() {
    document.getElementById('f-status').value = '';
    document.getElementById('f-type').value   = '';
    document.getElementById('f-from').value   = '';
    document.getElementById('f-to').value     = '';
    loadResolved();
}

document.addEventListener('DOMContentLoaded', () => {
    const today = new Date().toISOString().slice(0,10);
    const month = new Date(Date.now() - 30*86400000).toISOString().slice(0,10);
    document.getElementById('f-from').value = month;
    document.getElementById('f-to').value   = today;
    loadResolved();
});
</script>
@endpush
