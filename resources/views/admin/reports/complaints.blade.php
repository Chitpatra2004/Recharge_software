@extends('layouts.admin')

@section('title', 'Complaint Report')
@section('page-title', 'Complaint Report')

@section('content')

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
    <div>
        <h1 style="font-size:20px;font-weight:700;color:var(--text-primary)">Complaint Report</h1>
        <div class="breadcrumb" style="margin-bottom:0;margin-top:3px">
            <a href="/admin/dashboard">Dashboard</a>
            <svg class="breadcrumb-sep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span>Reports</span>
            <svg class="breadcrumb-sep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span>Complaint Report</span>
        </div>
    </div>
    <button class="btn btn-outline btn-sm" onclick="loadReport()">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        Refresh
    </button>
</div>

{{-- Filter Bar --}}
<div class="card" style="margin-bottom:20px">
    <div class="card-body" style="padding:16px 20px">
        <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end">
            <div>
                <label style="display:block;font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Date From</label>
                <input type="date" id="f-date-from" style="border:1px solid var(--border);border-radius:8px;padding:7px 12px;font-size:13px;color:var(--text-primary);background:#fff;outline:none">
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Date To</label>
                <input type="date" id="f-date-to" style="border:1px solid var(--border);border-radius:8px;padding:7px 12px;font-size:13px;color:var(--text-primary);background:#fff;outline:none">
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Type</label>
                <select id="f-type" style="border:1px solid var(--border);border-radius:8px;padding:7px 12px;font-size:13px;color:var(--text-primary);background:#fff;outline:none;min-width:130px">
                    <option value="">All Types</option>
                    <option value="recharge_failed">Recharge Failed</option>
                    <option value="payment">Payment</option>
                    <option value="refund">Refund</option>
                    <option value="account">Account</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Priority</label>
                <select id="f-priority" style="border:1px solid var(--border);border-radius:8px;padding:7px 12px;font-size:13px;color:var(--text-primary);background:#fff;outline:none;min-width:120px">
                    <option value="">All</option>
                    <option value="critical">Critical</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                </select>
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Status</label>
                <select id="f-status" style="border:1px solid var(--border);border-radius:8px;padding:7px 12px;font-size:13px;color:var(--text-primary);background:#fff;outline:none;min-width:120px">
                    <option value="">All</option>
                    <option value="open">Open</option>
                    <option value="in_progress">In Progress</option>
                    <option value="resolved">Resolved</option>
                    <option value="closed">Closed</option>
                </select>
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">SLA Breached</label>
                <select id="f-sla" style="border:1px solid var(--border);border-radius:8px;padding:7px 12px;font-size:13px;color:var(--text-primary);background:#fff;outline:none;min-width:120px">
                    <option value="">All</option>
                    <option value="1">Breached</option>
                    <option value="0">On Time</option>
                </select>
            </div>
            <button class="btn btn-primary btn-sm" onclick="loadReport()">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Apply
            </button>
            <button class="btn btn-outline btn-sm" onclick="clearFilters()">Clear</button>
        </div>
    </div>
</div>

{{-- Summary Cards --}}
<div class="stats-grid" style="margin-bottom:20px">
    <div class="stat-card blue">
        <div class="stat-header"><div class="stat-label">Total Complaints</div>
            <div class="stat-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></div>
        </div>
        <div class="stat-value" id="s-total">—</div>
        <div class="stat-amount" id="s-open-count">—</div>
        <div class="stat-footer"><span class="stat-pulse"></span><span class="stat-updated">All complaints</span></div>
    </div>
    <div class="stat-card green">
        <div class="stat-header"><div class="stat-label">Resolved</div>
            <div class="stat-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        </div>
        <div class="stat-value" id="s-resolved">—</div>
        <div class="stat-amount" id="s-resolution-rate">—</div>
        <div class="stat-footer"><span class="stat-pulse"></span><span class="stat-updated">Resolution rate</span></div>
    </div>
    <div class="stat-card orange">
        <div class="stat-header"><div class="stat-label">Avg Resolution Time</div>
            <div class="stat-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        </div>
        <div class="stat-value" id="s-avg-time">—</div>
        <div class="stat-amount" id="s-sla-breached">—</div>
        <div class="stat-footer"><span class="stat-pulse"></span><span class="stat-updated">Average hours</span></div>
    </div>
    <div class="stat-card red">
        <div class="stat-header"><div class="stat-label">Critical Pending</div>
            <div class="stat-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></div>
        </div>
        <div class="stat-value" id="s-critical">—</div>
        <div class="stat-amount" id="s-sla-count">—</div>
        <div class="stat-footer"><span class="stat-pulse"></span><span class="stat-updated">Needs immediate attention</span></div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">
    {{-- By Type --}}
    <div class="card">
        <div class="card-header"><div class="card-title">Complaints by Type</div></div>
        <div class="card-body" style="padding:0">
            <div id="type-loading" class="loading-overlay"><div class="spinner"></div> Loading…</div>
            <div class="table-wrap" id="type-table-wrap" style="display:none">
                <table>
                    <thead><tr><th>Type</th><th>Count</th><th>Resolved</th><th>Pending</th><th>Resolution %</th></tr></thead>
                    <tbody id="type-tbody"></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Agent Workload --}}
    <div class="card">
        <div class="card-header"><div class="card-title">Agent Workload</div></div>
        <div class="card-body" style="padding:0">
            <div id="agent-loading" class="loading-overlay"><div class="spinner"></div> Loading…</div>
            <div class="table-wrap" id="agent-table-wrap" style="display:none">
                <table>
                    <thead><tr><th>Agent</th><th>Assigned</th><th>Resolved</th><th>Open</th><th>Avg Time (h)</th></tr></thead>
                    <tbody id="agent-tbody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Complaints Table --}}
<div class="card">
    <div class="card-header" style="justify-content:space-between">
        <div class="card-title">Complaint Records</div>
        <span id="comp-count" style="font-size:12px;color:var(--text-muted)">—</span>
    </div>
    <div class="card-body" style="padding:0">
        <div id="comp-loading" class="loading-overlay"><div class="spinner"></div> Loading…</div>
        <div class="table-wrap" id="comp-table-wrap" style="display:none">
            <table>
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>User</th>
                        <th>Type</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>SLA</th>
                        <th>Assigned To</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody id="comp-tbody"></tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function getFilters() {
    return {
        date_from:    document.getElementById('f-date-from').value,
        date_to:      document.getElementById('f-date-to').value,
        type:         document.getElementById('f-type').value,
        priority:     document.getElementById('f-priority').value,
        status:       document.getElementById('f-status').value,
        sla_breached: document.getElementById('f-sla').value,
    };
}

function buildQuery(p) {
    return Object.entries(p).filter(([,v]) => v !== '' && v != null).map(([k,v]) => k+'='+encodeURIComponent(v)).join('&');
}

function clearFilters() {
    ['f-date-from','f-date-to'].forEach(id => document.getElementById(id).value = '');
    ['f-type','f-priority','f-status','f-sla'].forEach(id => document.getElementById(id).value = '');
    loadReport();
}

const PRIORITY_COLORS = { critical: ['#fee2e2','#dc2626'], high: ['#ffedd5','#ea580c'], medium: ['#fef3c7','#d97706'], low: ['#f0fdf4','#16a34a'] };
const STATUS_COLORS   = { open: ['#dbeafe','#1d4ed8'], in_progress: ['#fef3c7','#d97706'], resolved: ['#d1fae5','#059669'], closed: ['#f3f4f6','#6b7280'] };

function pBadge(p) {
    const c = PRIORITY_COLORS[(p||'').toLowerCase()] || ['#f3f4f6','#6b7280'];
    return `<span style="background:${c[0]};color:${c[1]};font-size:11px;font-weight:600;padding:2px 8px;border-radius:20px">${p||'—'}</span>`;
}

function sBadge(s) {
    const c = STATUS_COLORS[(s||'').toLowerCase()] || ['#f3f4f6','#6b7280'];
    return `<span style="background:${c[0]};color:${c[1]};font-size:11px;font-weight:600;padding:2px 8px;border-radius:20px">${(s||'—').replace('_',' ')}</span>`;
}

async function loadReport() {
    const q = buildQuery(getFilters());
    const res = await apiFetch('/api/v1/admin/reports/complaints?' + q);
    if (!res) return;
    const json = await res.json();
    const d = json.data || {};
    const s = d.summary || {};

    document.getElementById('s-total').textContent         = fmtNum(s.total ?? 0);
    document.getElementById('s-open-count').textContent    = fmtNum(s.open ?? 0) + ' open';
    document.getElementById('s-resolved').textContent      = fmtNum(s.resolved ?? 0);
    const rRate = s.total ? ((s.resolved / s.total) * 100).toFixed(1) : '0.0';
    document.getElementById('s-resolution-rate').textContent = rRate + '% resolution rate';
    document.getElementById('s-avg-time').textContent      = (s.avg_resolution_hours ?? '—') + 'h';
    document.getElementById('s-sla-breached').textContent  = fmtNum(s.sla_breached ?? 0) + ' SLA breached';
    document.getElementById('s-critical').textContent      = fmtNum(s.critical_pending ?? 0);
    document.getElementById('s-sla-count').textContent     = fmtNum(s.sla_breached ?? 0) + ' SLA breaches';

    renderByType(d.by_type || []);
    renderAgentWorkload(d.agent_workload || []);
    renderComplaints(d.complaints || d.data || []);
}

function renderByType(rows) {
    document.getElementById('type-loading').style.display = 'none';
    document.getElementById('type-table-wrap').style.display = 'block';
    const tbody = document.getElementById('type-tbody');
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:24px">No data</td></tr>';
        return;
    }
    tbody.innerHTML = rows.map(r => {
        const rate = r.total ? ((r.resolved / r.total) * 100).toFixed(1) : '0.0';
        return `<tr>
            <td style="font-weight:500">${(r.type || '—').replace('_',' ')}</td>
            <td>${fmtNum(r.total)}</td>
            <td style="color:#10b981;font-weight:600">${fmtNum(r.resolved)}</td>
            <td style="color:#f59e0b;font-weight:600">${fmtNum(r.pending ?? (r.total - r.resolved))}</td>
            <td><span style="color:${Number(rate)>=70?'#10b981':Number(rate)>=40?'#f59e0b':'#ef4444'};font-weight:600">${rate}%</span></td>
        </tr>`;
    }).join('');
}

function renderAgentWorkload(rows) {
    document.getElementById('agent-loading').style.display = 'none';
    document.getElementById('agent-table-wrap').style.display = 'block';
    const tbody = document.getElementById('agent-tbody');
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:24px">No agent data</td></tr>';
        return;
    }
    tbody.innerHTML = rows.map(r => `<tr>
        <td style="font-weight:600">${r.agent_name || r.name || '—'}</td>
        <td>${fmtNum(r.assigned)}</td>
        <td style="color:#10b981;font-weight:600">${fmtNum(r.resolved)}</td>
        <td style="color:#f59e0b;font-weight:600">${fmtNum(r.open ?? (r.assigned - r.resolved))}</td>
        <td style="color:var(--text-muted)">${r.avg_hours ?? '—'}</td>
    </tr>`).join('');
}

function renderComplaints(rows) {
    document.getElementById('comp-loading').style.display = 'none';
    document.getElementById('comp-table-wrap').style.display = 'block';
    document.getElementById('comp-count').textContent = fmtNum(rows.length) + ' records';
    const tbody = document.getElementById('comp-tbody');
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--text-muted);padding:24px">No complaints found</td></tr>';
        return;
    }
    tbody.innerHTML = rows.map(r => {
        const slaBreached = r.sla_breached;
        const slaBg = slaBreached ? '#fee2e2' : '#d1fae5';
        const slaC  = slaBreached ? '#dc2626' : '#059669';
        const slaTxt= slaBreached ? 'Breached' : 'On Time';
        return `<tr>
            <td style="font-weight:700;color:var(--accent-blue)">#${r.id || r.complaint_id || '—'}</td>
            <td>${r.user_name || r.user?.name || '—'}</td>
            <td style="font-size:12px">${(r.type || '—').replace('_',' ')}</td>
            <td>${pBadge(r.priority)}</td>
            <td>${sBadge(r.status)}</td>
            <td><span style="background:${slaBg};color:${slaC};font-size:11px;font-weight:600;padding:2px 8px;border-radius:20px">${slaTxt}</span></td>
            <td style="font-size:12px;color:var(--text-muted)">${r.assigned_to || r.agent_name || '—'}</td>
            <td style="font-size:12px;color:var(--text-muted)">${r.created_at ? new Date(r.created_at).toLocaleString('en-IN',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:true}) : '—'}</td>
        </tr>`;
    }).join('');
}

const today = new Date();
const week  = new Date(today); week.setDate(week.getDate() - 7);
document.getElementById('f-date-to').value   = today.toISOString().slice(0,10);
document.getElementById('f-date-from').value = week.toISOString().slice(0,10);

document.addEventListener('DOMContentLoaded', loadReport);
</script>
@endpush
