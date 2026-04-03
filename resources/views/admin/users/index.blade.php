@extends('layouts.admin')

@section('title', 'Users')
@section('page-title', 'Users')

@section('content')

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
    <div>
        <h1 style="font-size:20px;font-weight:700;color:var(--text-primary)">Users</h1>
        <div class="breadcrumb" style="margin-bottom:0;margin-top:3px">
            <a href="/admin/dashboard">Dashboard</a>
            <svg class="breadcrumb-sep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span>Users</span>
        </div>
    </div>
    <button class="btn btn-outline btn-sm" onclick="loadUsers()">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        Refresh
    </button>
</div>

{{-- Filter Bar --}}
<div class="card" style="margin-bottom:20px">
    <div class="card-body" style="padding:16px 20px">
        <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end">
            <div>
                <label style="display:block;font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Role</label>
                <select id="f-role" style="border:1px solid var(--border);border-radius:8px;padding:7px 12px;font-size:13px;color:var(--text-primary);background:#fff;outline:none;min-width:130px">
                    <option value="">All Roles</option>
                    <option value="retailer">Retailer</option>
                    <option value="buyer">Buyer</option>
                    <option value="distributor">Distributor</option>
                    <option value="api_user">API User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Status</label>
                <select id="f-status" style="border:1px solid var(--border);border-radius:8px;padding:7px 12px;font-size:13px;color:var(--text-primary);background:#fff;outline:none;min-width:120px">
                    <option value="">All</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Search</label>
                <input type="text" id="f-search" placeholder="Name, email, mobile…" style="border:1px solid var(--border);border-radius:8px;padding:7px 12px;font-size:13px;color:var(--text-primary);background:#fff;outline:none;width:220px">
            </div>
            <button class="btn btn-primary btn-sm" onclick="loadUsers()">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Search
            </button>
            <button class="btn btn-outline btn-sm" onclick="clearFilters()">Clear</button>
        </div>
    </div>
</div>

{{-- Summary Cards --}}
<div class="stats-grid" style="margin-bottom:20px">
    <div class="stat-card blue">
        <div class="stat-header"><div class="stat-label">Total Users</div>
            <div class="stat-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg></div>
        </div>
        <div class="stat-value" id="s-total">—</div>
        <div class="stat-amount" id="s-active">—</div>
        <div class="stat-footer"><span class="stat-pulse"></span><span class="stat-updated">Registered</span></div>
    </div>
    <div class="stat-card green">
        <div class="stat-header"><div class="stat-label">Active Users</div>
            <div class="stat-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        </div>
        <div class="stat-value" id="s-active-count">—</div>
        <div class="stat-amount" id="s-active-pct">—</div>
        <div class="stat-footer"><span class="stat-pulse"></span><span class="stat-updated">Active accounts</span></div>
    </div>
    <div class="stat-card orange">
        <div class="stat-header"><div class="stat-label">New This Month</div>
            <div class="stat-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg></div>
        </div>
        <div class="stat-value" id="s-new-month">—</div>
        <div class="stat-amount" id="s-new-today">—</div>
        <div class="stat-footer"><span class="stat-pulse"></span><span class="stat-updated">Registrations</span></div>
    </div>
    <div class="stat-card red">
        <div class="stat-header"><div class="stat-label">Suspended</div>
            <div class="stat-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg></div>
        </div>
        <div class="stat-value" id="s-suspended">—</div>
        <div class="stat-amount" id="s-inactive">—</div>
        <div class="stat-footer"><span class="stat-pulse"></span><span class="stat-updated">Suspended accounts</span></div>
    </div>
</div>

{{-- Users Table --}}
<div class="card">
    <div class="card-header" style="justify-content:space-between">
        <div class="card-title">User List</div>
        <div style="display:flex;align-items:center;gap:10px">
            <span style="font-size:12px;color:var(--text-muted)" id="page-info">—</span>
            <button class="btn btn-outline btn-sm" id="btn-prev" onclick="changePage(-1)" disabled>&#8592;</button>
            <button class="btn btn-outline btn-sm" id="btn-next" onclick="changePage(1)">&#8594;</button>
        </div>
    </div>
    <div class="card-body" style="padding:0">
        <div id="users-loading" class="loading-overlay"><div class="spinner"></div> Loading users…</div>
        <div class="table-wrap" id="users-table-wrap" style="display:none">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Balance</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="users-tbody">
                    <tr><td colspan="9" style="text-align:center;color:var(--text-muted);padding:24px">No users found</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let currentPage = 1;
let totalPages  = 1;

function getFilters() {
    return {
        role:   document.getElementById('f-role').value,
        status: document.getElementById('f-status').value,
        search: document.getElementById('f-search').value,
        page:   currentPage,
    };
}

function buildQuery(p) {
    return Object.entries(p).filter(([,v]) => v !== '' && v != null).map(([k,v]) => k+'='+encodeURIComponent(v)).join('&');
}

function clearFilters() {
    document.getElementById('f-search').value = '';
    document.getElementById('f-role').value = '';
    document.getElementById('f-status').value = '';
    currentPage = 1;
    loadUsers();
}

function changePage(dir) {
    currentPage = Math.max(1, Math.min(totalPages, currentPage + dir));
    loadUsers();
}

async function loadUsers() {
    document.getElementById('users-loading').style.display = 'flex';
    document.getElementById('users-table-wrap').style.display = 'none';

    const q = buildQuery(getFilters());
    const res = await apiFetch('/api/v1/admin/reports/users?' + q);
    if (!res) return;
    const json = await res.json();

    // API returns: {report, summary:{total_users,active_users,...}, users:{data:[],total,last_page,...}}
    const s     = json.summary || {};
    const pager = json.users   || {};
    const users = pager.data   || [];

    totalPages = pager.last_page ?? 1;
    document.getElementById('page-info').textContent = `Page ${currentPage}/${totalPages} (${fmtNum(pager.total ?? users.length)} total)`;
    document.getElementById('btn-prev').disabled = currentPage <= 1;
    document.getElementById('btn-next').disabled = currentPage >= totalPages;

    document.getElementById('s-total').textContent        = fmtNum(s.total_users ?? pager.total ?? users.length);
    document.getElementById('s-active').textContent       = fmtNum(s.active_users ?? 0) + ' active';
    document.getElementById('s-active-count').textContent = fmtNum(s.active_users ?? 0);
    const pct = (s.total_users && s.active_users) ? ((s.active_users / s.total_users) * 100).toFixed(1) + '%' : '—';
    document.getElementById('s-active-pct').textContent   = pct + ' of total';
    document.getElementById('s-new-month').textContent    = fmtNum(s.new_this_month ?? 0);
    document.getElementById('s-new-today').textContent    = fmtNum(s.new_today ?? 0) + ' today';
    document.getElementById('s-suspended').textContent    = fmtNum(s.suspended_users ?? 0);
    document.getElementById('s-inactive').textContent     = fmtNum(s.inactive_users ?? 0) + ' inactive';

    document.getElementById('users-loading').style.display = 'none';
    document.getElementById('users-table-wrap').style.display = 'block';

    const tbody = document.getElementById('users-tbody');
    if (!users.length) {
        tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;color:var(--text-muted);padding:24px">No users found</td></tr>';
        return;
    }
    tbody.innerHTML = users.map(u => {
        const st = (u.status || 'active').toLowerCase();
        const stBg = st === 'active' ? '#d1fae5' : st === 'suspended' ? '#fee2e2' : '#fef3c7';
        const stC  = st === 'active' ? '#059669' : st === 'suspended' ? '#dc2626' : '#d97706';
        const initials = (u.name || u.user_name || '?').charAt(0).toUpperCase();
        return `<tr>
            <td style="font-size:12px;color:var(--text-muted)">#${u.id || '—'}</td>
            <td>
                <div style="display:flex;align-items:center;gap:8px">
                    <div style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,var(--accent-blue),#7c3aed);color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0">${initials}</div>
                    <strong>${u.name || u.user_name || '—'}</strong>
                </div>
            </td>
            <td style="font-size:12px">${u.email || '—'}</td>
            <td style="font-size:12px">${u.mobile || u.phone || '—'}</td>
            <td><span style="background:var(--bg-page);padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600">${u.role || '—'}</span></td>
            <td><span style="background:${stBg};color:${stC};font-size:11px;font-weight:600;padding:2px 8px;border-radius:20px">${st}</span></td>
            <td style="font-weight:600;color:var(--accent-blue)">${fmtAmt(u.wallet_balance ?? u.balance)}</td>
            <td style="font-size:12px;color:var(--text-muted)">${u.created_at ? new Date(u.created_at).toLocaleDateString('en-IN') : '—'}</td>
            <td>
                <div style="display:flex;gap:6px">
                    <button class="btn btn-outline btn-sm" onclick="viewUser(${u.id})" title="View">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

function viewUser(id) {
    // Navigate to user detail page if it exists
    window.location.href = '/admin/users/' + id;
}

document.addEventListener('DOMContentLoaded', loadUsers);
</script>
@endpush
