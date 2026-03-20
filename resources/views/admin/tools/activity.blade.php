@extends('layouts.admin')
@section('title', 'Activity Logs')
@section('page-title', 'Activity Logs')

@section('content')
<div class="breadcrumb">
    <a href="/admin/dashboard">Dashboard</a>
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>Activity Logs</span>
</div>

{{-- Filters --}}
<div class="card" style="margin-bottom:18px">
    <div class="card-body" style="padding:14px 18px">
        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
            <div>
                <label style="font-size:11px;font-weight:600;color:var(--muted);display:block;margin-bottom:4px">Actor Type</label>
                <select id="f-actor-type" onchange="onActorTypeChange()" style="background:var(--card2);border:1px solid var(--border2);border-radius:7px;padding:6px 10px;font-size:13px;color:var(--text);outline:none">
                    <option value="">All Actors</option>
                    <option value="employee">Employee</option>
                    <option value="user">User</option>
                    <option value="system">System</option>
                </select>
            </div>
            <div id="emp-filter-wrap" style="display:none">
                <label style="font-size:11px;font-weight:600;color:var(--muted);display:block;margin-bottom:4px">Employee</label>
                <select id="f-employee" style="background:var(--card2);border:1px solid var(--border2);border-radius:7px;padding:6px 10px;font-size:13px;color:var(--text);outline:none;min-width:180px">
                    <option value="">All Employees</option>
                </select>
            </div>
            <div>
                <label style="font-size:11px;font-weight:600;color:var(--muted);display:block;margin-bottom:4px">Action</label>
                <input type="text" id="f-action" placeholder="e.g. auth.login" style="background:var(--card2);border:1px solid var(--border2);border-radius:7px;padding:6px 10px;font-size:13px;color:var(--text);outline:none;width:150px">
            </div>
            <div>
                <label style="font-size:11px;font-weight:600;color:var(--muted);display:block;margin-bottom:4px">Search</label>
                <input type="text" id="f-search" placeholder="IP / description…" style="background:var(--card2);border:1px solid var(--border2);border-radius:7px;padding:6px 10px;font-size:13px;color:var(--text);outline:none;width:150px">
            </div>
            <div>
                <label style="font-size:11px;font-weight:600;color:var(--muted);display:block;margin-bottom:4px">From Date</label>
                <input type="date" id="f-from" style="background:var(--card2);border:1px solid var(--border2);border-radius:7px;padding:6px 10px;font-size:13px;color:var(--text);outline:none;color-scheme:dark">
            </div>
            <div>
                <label style="font-size:11px;font-weight:600;color:var(--muted);display:block;margin-bottom:4px">To Date</label>
                <input type="date" id="f-to" style="background:var(--card2);border:1px solid var(--border2);border-radius:7px;padding:6px 10px;font-size:13px;color:var(--text);outline:none;color-scheme:dark">
            </div>
            <button class="btn btn-primary btn-sm" onclick="loadLogs()">Apply</button>
            <button class="btn btn-outline btn-sm" onclick="clearFilters()">Clear</button>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header" style="justify-content:space-between">
        <span class="card-title">Activity Logs</span>
        <span id="log-count" style="font-size:12px;color:var(--muted)"></span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Actor</th>
                    <th>Action</th>
                    <th>Description</th>
                    <th>IP Address</th>
                    <th>Date / Time</th>
                </tr>
            </thead>
            <tbody id="log-tbody">
                <tr><td colspan="6"><div class="loading"><div class="spinner"></div> Loading…</div></td></tr>
            </tbody>
        </table>
    </div>
    <div class="card-footer" id="log-pagination" style="gap:8px;justify-content:flex-end"></div>
</div>

{{-- Detail Modal --}}
<div id="detail-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:400;align-items:center;justify-content:center;padding:20px">
    <div style="background:var(--card);border-radius:14px;width:100%;max-width:560px;max-height:85vh;overflow-y:auto;box-shadow:0 24px 60px rgba(0,0,0,.5)">
        <div style="padding:18px 22px;border-bottom:1px solid var(--border2);display:flex;align-items:center;justify-content:space-between">
            <h3 style="font-size:14px;font-weight:700;color:var(--text)">Log Detail</h3>
            <button onclick="closeDetail()" style="background:none;border:none;cursor:pointer;color:var(--muted)">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:18px;height:18px"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div style="padding:20px 22px" id="detail-body"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let actorTypeChanged = false;

function onActorTypeChange() {
    const v = document.getElementById('f-actor-type').value;
    document.getElementById('emp-filter-wrap').style.display = v === 'employee' ? 'block' : 'none';
}

async function loadEmployeeOptions() {
    const res = await apiFetch('/api/v1/employee/activity/employees-list');
    if (!res?.ok) return;
    const data = await res.json();
    const sel = document.getElementById('f-employee');
    (data.employees || []).forEach(e => {
        const opt = document.createElement('option');
        opt.value = e.id;
        opt.textContent = `${e.name} (${e.employee_code})`;
        sel.appendChild(opt);
    });
}

async function loadLogs(page = 1) {
    document.getElementById('log-tbody').innerHTML =
        '<tr><td colspan="6"><div class="loading"><div class="spinner"></div> Loading…</div></td></tr>';

    const p = new URLSearchParams({ page, per_page: 50 });
    const actorType = document.getElementById('f-actor-type').value;
    const empId     = document.getElementById('f-employee').value;
    const action    = document.getElementById('f-action').value.trim();
    const search    = document.getElementById('f-search').value.trim();
    const from      = document.getElementById('f-from').value;
    const to        = document.getElementById('f-to').value;

    if (actorType) p.set('actor_type', actorType);
    if (empId)     p.set('actor_id', empId);
    if (action)    p.set('action', action);
    if (search)    p.set('search', search);
    if (from)      p.set('from', from);
    if (to)        p.set('to', to);

    const res = await apiFetch('/api/v1/employee/activity?' + p);
    if (!res) return;
    const json = await res.json();
    const rows  = json.data?.data || [];
    const total = json.data?.total ?? null;

    document.getElementById('log-count').textContent = total != null ? fmtNum(total) + ' records' : '';

    if (!rows.length) {
        document.getElementById('log-tbody').innerHTML =
            '<tr><td colspan="6" style="text-align:center;color:var(--muted);padding:30px">No logs found</td></tr>';
        document.getElementById('log-pagination').innerHTML = '';
        return;
    }

    document.getElementById('log-tbody').innerHTML = rows.map((r, i) => {
        const date = r.created_at ? new Date(r.created_at).toLocaleString('en-IN') : '—';
        const actor = actorLabel(r);
        const actionBadge = actionColor(r.action);
        return `<tr style="cursor:pointer" onclick='showDetail(${JSON.stringify(r).replace(/'/g, "\\'")})'>
            <td style="font-family:monospace;font-size:11px;color:var(--muted)">${r.id}</td>
            <td>${actor}</td>
            <td><span style="background:${actionBadge.bg};color:${actionBadge.color};font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;white-space:nowrap">${r.action||'—'}</span></td>
            <td style="font-size:12px;color:var(--muted);max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="${(r.description||'').replace(/"/g,'&quot;')}">${r.description||'—'}</td>
            <td style="font-family:monospace;font-size:11px;color:var(--muted)">${r.ip_address||'—'}</td>
            <td style="font-size:11px;color:var(--muted)">${date}</td>
        </tr>`;
    }).join('');

    // Pagination
    const meta     = json.data || {};
    const lastPage = meta.last_page || 1;
    const currPage = meta.current_page || page;
    const pag = document.getElementById('log-pagination');
    if (lastPage > 1) {
        let h = '';
        if (currPage > 1) h += `<button class="btn btn-outline btn-sm" onclick="event.stopPropagation();loadLogs(${currPage-1})">← Prev</button>`;
        h += `<span style="font-size:12px;color:var(--muted)">Page ${currPage} of ${lastPage}</span>`;
        if (currPage < lastPage) h += `<button class="btn btn-outline btn-sm" onclick="event.stopPropagation();loadLogs(${currPage+1})">Next →</button>`;
        pag.innerHTML = h;
    } else pag.innerHTML = '';
}

function actorLabel(r) {
    if (r.actor_type === 'employee') {
        const name = r.actor_name || `#${r.actor_id}`;
        const code = r.actor_code || '';
        return `<div style="font-size:12px;font-weight:600;color:var(--text)">${name}</div>
                <div style="font-size:10px;color:var(--muted)">${code} · <span style="color:#6366f1">employee</span></div>`;
    }
    if (r.actor_type === 'user') {
        return `<div style="font-size:12px;font-weight:600;color:var(--text)">User #${r.actor_id||'?'}</div>
                <div style="font-size:10px;color:#10b981">user</div>`;
    }
    return `<div style="font-size:12px;color:var(--muted)">System</div>`;
}

function actionColor(action) {
    if (!action) return { bg: 'rgba(107,114,128,.15)', color: '#9ca3af' };
    if (action.startsWith('auth.'))    return { bg: 'rgba(99,102,241,.15)',  color: '#a5b4fc' };
    if (action.startsWith('wallet.'))  return { bg: 'rgba(16,185,129,.15)',  color: '#6ee7b7' };
    if (action.startsWith('recharge')) return { bg: 'rgba(245,158,11,.15)',  color: '#fcd34d' };
    if (action.startsWith('admin.'))   return { bg: 'rgba(239,68,68,.15)',   color: '#fca5a5' };
    if (action.startsWith('complaint'))return { bg: 'rgba(14,165,233,.15)',  color: '#7dd3fc' };
    return { bg: 'rgba(107,114,128,.15)', color: '#9ca3af' };
}

function showDetail(r) {
    let props = '';
    try {
        const p = typeof r.properties === 'string' ? JSON.parse(r.properties) : r.properties;
        if (p && Object.keys(p).length) {
            props = `<pre style="background:var(--card2);border-radius:8px;padding:12px;font-size:11px;overflow-x:auto;white-space:pre-wrap;color:var(--muted);margin-top:10px">${JSON.stringify(p, null, 2)}</pre>`;
        }
    } catch {}

    document.getElementById('detail-body').innerHTML = `
        <table style="width:100%;font-size:13px;border-collapse:collapse">
            ${drow('Log ID', r.id)}
            ${drow('Actor Type', r.actor_type || '—')}
            ${drow('Actor', r.actor_name ? `${r.actor_name} (${r.actor_code||''})` : `#${r.actor_id||'?'}`)}
            ${drow('Action', `<code style="background:var(--card2);padding:2px 6px;border-radius:4px;font-size:12px">${r.action||'—'}</code>`)}
            ${drow('Description', r.description || '—')}
            ${r.subject_type ? drow('Subject', `${r.subject_type} #${r.subject_id}`) : ''}
            ${drow('IP Address', r.ip_address || '—')}
            ${r.url ? drow('URL', `<code style="font-size:11px;word-break:break-all">${r.method||''} ${r.url}</code>`) : ''}
            ${drow('Date / Time', r.created_at ? new Date(r.created_at).toLocaleString('en-IN') : '—')}
        </table>
        ${props ? `<div style="font-size:11px;font-weight:600;color:var(--muted);margin-top:14px;margin-bottom:2px">Properties</div>${props}` : ''}`;

    const ov = document.getElementById('detail-overlay');
    ov.style.display = 'flex';
    ov.onclick = e => { if (e.target === ov) closeDetail(); };
}

function drow(label, val) {
    return `<tr>
        <td style="padding:8px 0;color:var(--muted);font-weight:500;width:130px;vertical-align:top">${label}</td>
        <td style="padding:8px 0;color:var(--text);font-weight:600">${val}</td>
    </tr>`;
}

function closeDetail() {
    document.getElementById('detail-overlay').style.display = 'none';
}

function clearFilters() {
    ['f-actor-type','f-employee','f-action','f-search','f-from','f-to'].forEach(id => {
        document.getElementById(id).value = '';
    });
    document.getElementById('emp-filter-wrap').style.display = 'none';
    loadLogs();
}

document.addEventListener('DOMContentLoaded', () => {
    loadEmployeeOptions();
    loadLogs();
});
</script>
@endpush
