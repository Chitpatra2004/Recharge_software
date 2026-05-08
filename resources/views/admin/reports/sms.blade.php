@extends('layouts.admin')

@section('title', 'SMS Report')
@section('page-title', 'SMS Report')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;gap:12px;flex-wrap:wrap">
    <div>
        <h1 style="font-size:20px;font-weight:700;color:var(--text-primary)">SMS Report</h1>
        <div class="breadcrumb" style="margin-bottom:0;margin-top:3px">
            <a href="/admin/dashboard">Dashboard</a>
            <svg class="breadcrumb-sep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span>Reports</span>
            <svg class="breadcrumb-sep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span>SMS Report</span>
        </div>
    </div>
    <button class="btn btn-outline btn-sm" onclick="loadSmsReport()">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        Refresh
    </button>
</div>

<div class="stats-grid" style="margin-bottom:18px">
    <div class="stat-card blue"><div class="stat-label">Total SMS</div><div class="stat-value" id="stat-total">0</div><div class="stat-footer">All time logs</div></div>
    <div class="stat-card green"><div class="stat-label">Sent</div><div class="stat-value" id="stat-sent">0</div><div class="stat-footer">Delivered to API</div></div>
    <div class="stat-card red"><div class="stat-label">Failed</div><div class="stat-value" id="stat-failed">0</div><div class="stat-footer">Failed API calls</div></div>
    <div class="stat-card orange"><div class="stat-label">Today</div><div class="stat-value" id="stat-today">0</div><div class="stat-footer">Today sent/attempted</div></div>
</div>

<div class="card" style="margin-bottom:18px">
    <div class="card-body" style="padding:16px 20px">
        <div style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
            <div>
                <label class="filter-label">From</label>
                <input type="date" id="f-from">
            </div>
            <div>
                <label class="filter-label">To</label>
                <input type="date" id="f-to">
            </div>
            <div>
                <label class="filter-label">Status</label>
                <select id="f-status">
                    <option value="">All</option>
                    <option value="sent">Sent</option>
                    <option value="failed">Failed</option>
                    <option value="pending">Pending</option>
                </select>
            </div>
            <div>
                <label class="filter-label">Purpose</label>
                <input type="text" id="f-purpose" placeholder="login verification">
            </div>
            <div style="flex:1;min-width:220px">
                <label class="filter-label">Search</label>
                <input type="text" id="f-search" placeholder="User name, mobile, message, provider id" style="width:100%">
            </div>
            <button class="btn btn-primary btn-sm" onclick="loadSmsReport(1)">Search</button>
            <button class="btn btn-outline btn-sm" onclick="clearFilters()">Clear</button>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>User</th>
                    <th>Mobile</th>
                    <th>Purpose</th>
                    <th>Message</th>
                    <th>Provider</th>
                    <th>Status</th>
                    <th>Provider ID</th>
                </tr>
            </thead>
            <tbody id="sms-body">
                <tr><td colspan="8" style="text-align:center;padding:24px;color:var(--text-muted)">Loading...</td></tr>
            </tbody>
        </table>
    </div>
    <div class="card-footer" id="sms-pager" style="gap:8px;justify-content:flex-end"></div>
</div>
@endsection

@push('head')
<style>
.filter-label{display:block;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px}
#f-from,#f-to,#f-status,#f-purpose,#f-search{border:1px solid var(--border);border-radius:8px;padding:8px 12px;font-size:13px;color:var(--text-primary);background:var(--card-bg);outline:none}
.sms-message{max-width:360px;white-space:normal;line-height:1.45;color:var(--text-secondary);font-size:12px}
.sms-badge{display:inline-flex;align-items:center;border-radius:999px;padding:3px 9px;font-size:11px;font-weight:700;text-transform:uppercase}
.sms-badge.sent{background:#dcfce7;color:#166534}.sms-badge.failed{background:#fee2e2;color:#991b1b}.sms-badge.pending{background:#fef3c7;color:#92400e}
</style>
@endpush

@push('scripts')
<script>
let smsPage = 1;

async function loadSmsReport(page = smsPage) {
    smsPage = page;
    const params = new URLSearchParams({ page, per_page: 20 });
    const from = document.getElementById('f-from').value;
    const to = document.getElementById('f-to').value;
    const status = document.getElementById('f-status').value;
    const purpose = document.getElementById('f-purpose').value.trim();
    const search = document.getElementById('f-search').value.trim();

    if (from) params.set('date_from', from);
    if (to) params.set('date_to', to);
    if (status) params.set('status', status);
    if (purpose) params.set('purpose', purpose);
    if (search) params.set('search', search);

    const body = document.getElementById('sms-body');
    body.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:24px;color:var(--text-muted)">Loading...</td></tr>';

    try {
        const res = await apiFetch('/api/v1/employee/reports/sms?' + params);
        const d = await res.json();
        if (!res.ok) throw new Error(d.message || 'Failed');

        setStats(d.stats || {});
        const rows = d.data?.data || [];
        if (!rows.length) {
            body.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:24px;color:var(--text-muted)">No SMS logs found</td></tr>';
            document.getElementById('sms-pager').innerHTML = '';
            return;
        }

        body.innerHTML = rows.map(rowHtml).join('');
        renderPager(d.data || {});
    } catch (e) {
        body.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:24px;color:var(--accent-red)">Failed to load SMS report</td></tr>';
    }
}

function rowHtml(r) {
    const user = r.user || {};
    const time = r.created_at ? new Date(r.created_at).toLocaleString('en-IN') : '-';
    const userText = user.name ? `${esc(user.name)}<div style="font-size:11px;color:var(--text-muted)">${esc(user.email || '')}</div>` : '-';
    const status = (r.status || 'pending').toLowerCase();

    return `<tr>
        <td style="font-size:12px;color:var(--text-muted)">${time}</td>
        <td>${userText}</td>
        <td style="font-family:monospace;font-size:12px">${esc(r.mobile || user.mobile || '-')}</td>
        <td style="font-size:12px">${esc(r.purpose || '-')}</td>
        <td><div class="sms-message">${esc(r.message || '-')}</div></td>
        <td style="font-size:12px">${esc(r.provider || '-')}</td>
        <td><span class="sms-badge ${status}">${esc(status)}</span></td>
        <td style="font-family:monospace;font-size:11px;color:var(--text-muted)">${esc(r.provider_message_id || '-')}</td>
    </tr>`;
}

function setStats(stats) {
    document.getElementById('stat-total').textContent = fmt(stats.total);
    document.getElementById('stat-sent').textContent = fmt(stats.sent);
    document.getElementById('stat-failed').textContent = fmt(stats.failed);
    document.getElementById('stat-today').textContent = fmt(stats.today);
}

function renderPager(pager) {
    const last = pager.last_page || 1;
    const curr = pager.current_page || 1;
    let html = '';
    if (last > 1) {
        if (curr > 1) html += `<button class="btn btn-outline btn-sm" onclick="loadSmsReport(${curr - 1})">Prev</button>`;
        html += `<span style="font-size:12px;color:var(--text-muted)">Page ${curr} of ${last}</span>`;
        if (curr < last) html += `<button class="btn btn-outline btn-sm" onclick="loadSmsReport(${curr + 1})">Next</button>`;
    }
    document.getElementById('sms-pager').innerHTML = html;
}

function clearFilters() {
    ['f-from', 'f-to', 'f-status', 'f-purpose', 'f-search'].forEach(id => document.getElementById(id).value = '');
    loadSmsReport(1);
}

function esc(s) {
    return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function fmt(n) {
    return Number(n || 0).toLocaleString('en-IN');
}

document.addEventListener('DOMContentLoaded', () => loadSmsReport(1));
</script>
@endpush
