@extends('layouts.admin')
@section('title', 'All Complaints')
@section('page-title', 'All Complaints')

@section('content')
<div class="breadcrumb">
    <a href="/admin/dashboard">Dashboard</a>
    <svg class="breadcrumb-sep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>All Complaints</span>
</div>

{{-- Filters --}}
<div class="card" style="margin-bottom:20px">
    <div class="card-body" style="padding:16px 20px">
        <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
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
                <label style="font-size:11px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">Priority</label>
                <select id="f-priority" style="border:1px solid var(--border);border-radius:6px;padding:6px 10px;font-size:13px">
                    <option value="">All Priorities</option>
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="critical">Critical</option>
                </select>
            </div>
            <div>
                <label style="font-size:11px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">Status</label>
                <select id="f-status" style="border:1px solid var(--border);border-radius:6px;padding:6px 10px;font-size:13px">
                    <option value="">All Statuses</option>
                    <option value="open">Open</option>
                    <option value="in_progress">In Progress</option>
                    <option value="waiting_on_operator">Waiting on Operator</option>
                    <option value="waiting_on_user">Waiting on User</option>
                    <option value="resolved">Resolved</option>
                    <option value="closed">Closed</option>
                    <option value="escalated">Escalated</option>
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
            <button class="btn btn-primary btn-sm" onclick="loadComplaints()">Apply</button>
            <button class="btn btn-outline btn-sm" onclick="clearFilters()">Clear</button>
        </div>
    </div>
</div>

{{-- Summary Cards --}}
<div class="stats-grid" style="margin-bottom:20px">
    <div class="stat-card blue"><div class="stat-header"><div class="stat-label">Total</div></div><div class="stat-value" id="s-total">—</div></div>
    <div class="stat-card orange"><div class="stat-header"><div class="stat-label">Open</div></div><div class="stat-value" id="s-open">—</div></div>
    <div class="stat-card red"><div class="stat-header"><div class="stat-label">Escalated</div></div><div class="stat-value" id="s-escalated">—</div></div>
    <div class="stat-card green"><div class="stat-header"><div class="stat-label">Resolved</div></div><div class="stat-value" id="s-resolved">—</div></div>
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
                    <th>Created</th>
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
const STATUS_COLOR   = { open:'pending', in_progress:'pending', resolved:'success', closed:'success', escalated:'failure', waiting_on_operator:'pending', waiting_on_user:'pending' };

async function loadComplaints(page = 1) {
    const p = new URLSearchParams({ page, per_page: 30 });
    const type     = document.getElementById('f-type').value;
    const priority = document.getElementById('f-priority').value;
    const status   = document.getElementById('f-status').value;
    const from     = document.getElementById('f-from').value;
    const to       = document.getElementById('f-to').value;
    if (type)     p.set('type', type);
    if (priority) p.set('priority', priority);
    if (status)   p.set('status', status);
    if (from)     p.set('date_from', from);
    if (to)       p.set('date_to', to);

    const res = await apiFetch('/api/v1/admin/reports/complaints?' + p.toString());
    if (!res) return;
    const data = await res.json();

    const s = data.summary || {};
    document.getElementById('s-total').textContent     = fmtNum(s.total);
    document.getElementById('s-open').textContent      = fmtNum(s.open);
    document.getElementById('s-escalated').textContent = fmtNum(s.escalated);
    document.getElementById('s-resolved').textContent  = fmtNum(s.resolved);

    const items = data.complaints?.data || data.complaints || [];
    document.getElementById('comp-tbody').innerHTML = items.length
        ? items.map(c => `<tr>
            <td style="font-family:monospace;font-size:11px">${c.id||'—'}</td>
            <td>${c.user?.name || c.user_id || '—'}</td>
            <td style="font-size:12px">${(c.type||'—').replace(/_/g,' ')}</td>
            <td><span style="font-size:11px;font-weight:600;color:${PRIORITY_COLOR[c.priority]||'inherit'}">${c.priority||'—'}</span></td>
            <td><span class="txn-status ${STATUS_COLOR[c.status]||'pending'}">${(c.status||'—').replace(/_/g,' ')}</span></td>
            <td>${fmtAgo(c.created_at)}</td>
            <td>${c.sla_breached ? '<span style="color:var(--accent-red);font-size:11px;font-weight:600">Breached</span>' : '<span style="color:var(--accent-green);font-size:11px">OK</span>'}</td>
          </tr>`).join('')
        : '<tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:24px">No complaints found</td></tr>';

    const meta = data.complaints;
    const pag  = document.getElementById('pagination');
    if (meta?.last_page > 1) {
        let html = '';
        if (meta.current_page > 1) html += `<button class="btn btn-outline btn-sm" onclick="loadComplaints(${meta.current_page-1})">Prev</button>`;
        html += `<span style="font-size:12px;color:var(--text-muted)">Page ${meta.current_page} of ${meta.last_page}</span>`;
        if (meta.current_page < meta.last_page) html += `<button class="btn btn-outline btn-sm" onclick="loadComplaints(${meta.current_page+1})">Next</button>`;
        pag.innerHTML = html;
    } else { pag.innerHTML = ''; }
}

function clearFilters() {
    ['f-type','f-priority','f-status'].forEach(id => document.getElementById(id).value = '');
    ['f-from','f-to'].forEach(id => document.getElementById(id).value = '');
    loadComplaints();
}

document.addEventListener('DOMContentLoaded', () => loadComplaints());
</script>
@endpush
