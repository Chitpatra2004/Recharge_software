@extends('layouts.admin')
@section('title', 'Pending Complaints')
@section('page-title', 'Pending Complaints')

@section('content')
<div class="breadcrumb">
    <a href="/admin/dashboard">Dashboard</a>
    <svg class="breadcrumb-sep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <a href="/admin/complaints">Complaints</a>
    <svg class="breadcrumb-sep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>Pending</span>
</div>

<div class="note-bar" style="margin-bottom:20px">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M12 3a9 9 0 100 18A9 9 0 0012 3z"/></svg>
    Showing open, in-progress, escalated, and waiting complaints. Critical priority rows are highlighted.
</div>

{{-- Filters --}}
<div class="card" style="margin-bottom:20px">
    <div class="card-body" style="padding:16px 20px">
        <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
            <div>
                <label style="font-size:11px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">Priority</label>
                <select id="f-priority" style="border:1px solid var(--border);border-radius:6px;padding:6px 10px;font-size:13px">
                    <option value="">All</option>
                    <option value="critical">Critical</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
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
            <button class="btn btn-primary btn-sm" onclick="loadPending()">Apply</button>
        </div>
    </div>
</div>

{{-- Stats --}}
<div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:20px">
    <div class="stat-card red"><div class="stat-header"><div class="stat-label">Critical</div></div><div class="stat-value" id="s-critical">—</div></div>
    <div class="stat-card orange"><div class="stat-header"><div class="stat-label">High</div></div><div class="stat-value" id="s-high">—</div></div>
    <div class="stat-card blue"><div class="stat-header"><div class="stat-label">Escalated</div></div><div class="stat-value" id="s-escalated">—</div></div>
    <div class="stat-card orange"><div class="stat-header"><div class="stat-label">SLA Breached</div></div><div class="stat-value" id="s-sla">—</div></div>
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
                    <th>SLA</th>
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

async function loadPending(page = 1) {
    const p = new URLSearchParams({ page, per_page: 30 });
    const priority = document.getElementById('f-priority').value;
    const type     = document.getElementById('f-type').value;
    if (priority) p.set('priority', priority);
    if (type)     p.set('type', type);

    const res = await apiFetch('/api/v1/admin/reports/complaints?' + p.toString());
    if (!res) return;
    const data = await res.json();

    // Count pending statuses
    const items = data.complaints?.data || data.complaints || [];
    const pending = items.filter(c => !['resolved','closed'].includes(c.status));

    let critical = 0, high = 0, escalated = 0, sla = 0;
    pending.forEach(c => {
        if (c.priority === 'critical') critical++;
        if (c.priority === 'high')     high++;
        if (c.status   === 'escalated') escalated++;
        if (c.sla_breached) sla++;
    });
    document.getElementById('s-critical').textContent  = critical;
    document.getElementById('s-high').textContent      = high;
    document.getElementById('s-escalated').textContent = escalated;
    document.getElementById('s-sla').textContent       = sla;

    document.getElementById('comp-tbody').innerHTML = pending.length
        ? pending.map(c => {
            const rowBg = c.priority === 'critical' ? 'background:#fff5f5' : c.priority === 'high' ? 'background:#fffbeb' : '';
            return `<tr style="${rowBg}">
                <td style="font-family:monospace;font-size:11px">${c.id||'—'}</td>
                <td>${c.user?.name || c.user_id || '—'}</td>
                <td style="font-size:12px">${(c.type||'—').replace(/_/g,' ')}</td>
                <td><span style="font-size:11px;font-weight:700;color:${PRIORITY_COLOR[c.priority]||'inherit'}">${(c.priority||'—').toUpperCase()}</span></td>
                <td><span style="font-size:11px;font-weight:500">${(c.status||'—').replace(/_/g,' ')}</span></td>
                <td>${fmtAgo(c.created_at)}</td>
                <td>${c.sla_breached ? '<span style="color:var(--accent-red);font-size:11px;font-weight:700">⚠ Breached</span>' : '<span style="color:var(--accent-green);font-size:11px">OK</span>'}</td>
            </tr>`;
        }).join('')
        : '<tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:24px">No pending complaints</td></tr>';

    const meta = data.complaints;
    const pag  = document.getElementById('pagination');
    if (meta?.last_page > 1) {
        let html = '';
        if (meta.current_page > 1) html += `<button class="btn btn-outline btn-sm" onclick="loadPending(${meta.current_page-1})">Prev</button>`;
        html += `<span style="font-size:12px;color:var(--text-muted)">Page ${meta.current_page} of ${meta.last_page}</span>`;
        if (meta.current_page < meta.last_page) html += `<button class="btn btn-outline btn-sm" onclick="loadPending(${meta.current_page+1})">Next</button>`;
        pag.innerHTML = html;
    } else { pag.innerHTML = ''; }
}

document.addEventListener('DOMContentLoaded', () => loadPending());
</script>
@endpush
