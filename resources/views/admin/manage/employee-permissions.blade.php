@extends('layouts.admin')
@section('title', 'Employee Permissions')
@section('page-title', 'Employee Permissions')

@section('content')
<div class="breadcrumb">
    <a href="/admin/dashboard">Dashboard</a>
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <a href="/admin/employees">Employees</a>
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>Permissions</span>
</div>

<div id="page-loading" style="text-align:center;padding:60px 0">
    <div class="spinner"></div>
    <div style="margin-top:10px;color:var(--muted);font-size:13px">Loading employee…</div>
</div>

<div id="page-content" style="display:none">

    {{-- Employee Info Card --}}
    <div class="card" style="margin-bottom:20px">
        <div style="padding:20px 24px;display:flex;align-items:center;gap:18px;flex-wrap:wrap">
            <div id="emp-avatar" style="width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,var(--primary,#2563eb),#6366f1);display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:800;color:#fff;flex-shrink:0">E</div>
            <div style="flex:1;min-width:0">
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:4px">
                    <span id="emp-name" style="font-size:17px;font-weight:700;color:var(--text)">—</span>
                    <span id="emp-role-badge"></span>
                    <span id="emp-status-badge"></span>
                </div>
                <div style="font-size:13px;color:var(--muted);display:flex;gap:16px;flex-wrap:wrap">
                    <span id="emp-email">—</span>
                    <span id="emp-dept">—</span>
                    <span id="emp-code" style="font-family:monospace;font-size:12px">—</span>
                </div>
            </div>
            <div id="admin-note" style="display:none;background:rgba(99,102,241,.12);border:1px solid rgba(99,102,241,.3);border-radius:10px;padding:10px 16px;font-size:12px;color:#818cf8;max-width:280px">
                <strong>Admin / Super Admin</strong> — All permissions granted automatically. Granular settings below are ignored for this role.
            </div>
            <a href="/admin/employees" class="btn btn-outline btn-sm">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Back
            </a>
        </div>
    </div>

    {{-- Permission Groups --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px;margin-bottom:20px" id="perm-groups">

        {{-- User Management --}}
        <div class="card perm-group" data-group="users">
            <div class="card-header">
                <div class="perm-group-icon" style="background:rgba(37,99,235,.15)">
                    <svg fill="none" viewBox="0 0 24 24" stroke="#2563eb" stroke-width="2" style="width:18px;height:18px"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <div class="card-title">User Management</div>
                    <div style="font-size:11px;color:var(--muted);margin-top:1px">Access to user accounts</div>
                </div>
                <button class="group-toggle-all btn btn-outline btn-sm" style="margin-left:auto" onclick="toggleGroup('users')">Select All</button>
            </div>
            <div class="card-body" style="padding:14px 20px;display:flex;flex-direction:column;gap:0">
                <div class="perm-row" data-perm="view_users">
                    <div class="perm-info">
                        <div class="perm-name">View Users</div>
                        <div class="perm-desc">Browse and search user accounts</div>
                    </div>
                    <label class="toggle"><input type="checkbox" class="perm-cb" value="view_users"><span class="toggle-slider"></span></label>
                </div>
                <div class="perm-row" data-perm="edit_users">
                    <div class="perm-info">
                        <div class="perm-name">Edit Users</div>
                        <div class="perm-desc">Modify user details and status</div>
                    </div>
                    <label class="toggle"><input type="checkbox" class="perm-cb" value="edit_users"><span class="toggle-slider"></span></label>
                </div>
            </div>
        </div>

        {{-- Reports & Analytics --}}
        <div class="card perm-group" data-group="reports">
            <div class="card-header">
                <div class="perm-group-icon" style="background:rgba(8,145,178,.15)">
                    <svg fill="none" viewBox="0 0 24 24" stroke="#0891b2" stroke-width="2" style="width:18px;height:18px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <div>
                    <div class="card-title">Reports & Analytics</div>
                    <div style="font-size:11px;color:var(--muted);margin-top:1px">Transaction and system reports</div>
                </div>
                <button class="group-toggle-all btn btn-outline btn-sm" style="margin-left:auto" onclick="toggleGroup('reports')">Select All</button>
            </div>
            <div class="card-body" style="padding:14px 20px;display:flex;flex-direction:column;gap:0">
                <div class="perm-row" data-perm="view_reports">
                    <div class="perm-info">
                        <div class="perm-name">View Reports</div>
                        <div class="perm-desc">Access recharge and payment reports</div>
                    </div>
                    <label class="toggle"><input type="checkbox" class="perm-cb" value="view_reports"><span class="toggle-slider"></span></label>
                </div>
                <div class="perm-row" data-perm="export_reports">
                    <div class="perm-info">
                        <div class="perm-name">Export Reports</div>
                        <div class="perm-desc">Download reports as CSV / Excel</div>
                    </div>
                    <label class="toggle"><input type="checkbox" class="perm-cb" value="export_reports"><span class="toggle-slider"></span></label>
                </div>
                <div class="perm-row" data-perm="view_activity">
                    <div class="perm-info">
                        <div class="perm-name">View Activity Logs</div>
                        <div class="perm-desc">See system and employee activity</div>
                    </div>
                    <label class="toggle"><input type="checkbox" class="perm-cb" value="view_activity"><span class="toggle-slider"></span></label>
                </div>
            </div>
        </div>

        {{-- Financial Operations --}}
        <div class="card perm-group" data-group="finance">
            <div class="card-header">
                <div class="perm-group-icon" style="background:rgba(217,119,6,.15)">
                    <svg fill="none" viewBox="0 0 24 24" stroke="#d97706" stroke-width="2" style="width:18px;height:18px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div class="card-title">Financial Operations</div>
                    <div style="font-size:11px;color:var(--muted);margin-top:1px">Wallet and refund management</div>
                </div>
                <button class="group-toggle-all btn btn-outline btn-sm" style="margin-left:auto" onclick="toggleGroup('finance')">Select All</button>
            </div>
            <div class="card-body" style="padding:14px 20px;display:flex;flex-direction:column;gap:0">
                <div class="perm-row" data-perm="manage_wallets">
                    <div class="perm-info">
                        <div class="perm-name">Manage Wallets</div>
                        <div class="perm-desc">View and top-up user wallets</div>
                    </div>
                    <label class="toggle"><input type="checkbox" class="perm-cb" value="manage_wallets"><span class="toggle-slider"></span></label>
                </div>
                <div class="perm-row" data-perm="process_refunds">
                    <div class="perm-info">
                        <div class="perm-name">Process Refunds</div>
                        <div class="perm-desc">Approve and initiate recharge refunds</div>
                    </div>
                    <label class="toggle"><input type="checkbox" class="perm-cb" value="process_refunds"><span class="toggle-slider"></span></label>
                </div>
            </div>
        </div>

        {{-- Customer Support --}}
        <div class="card perm-group" data-group="support">
            <div class="card-header">
                <div class="perm-group-icon" style="background:rgba(5,150,105,.15)">
                    <svg fill="none" viewBox="0 0 24 24" stroke="#059669" stroke-width="2" style="width:18px;height:18px"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <div>
                    <div class="card-title">Customer Support</div>
                    <div style="font-size:11px;color:var(--muted);margin-top:1px">Complaint and ticket management</div>
                </div>
                <button class="group-toggle-all btn btn-outline btn-sm" style="margin-left:auto" onclick="toggleGroup('support')">Select All</button>
            </div>
            <div class="card-body" style="padding:14px 20px;display:flex;flex-direction:column;gap:0">
                <div class="perm-row" data-perm="handle_complaints">
                    <div class="perm-info">
                        <div class="perm-name">Handle Complaints</div>
                        <div class="perm-desc">View and respond to complaints</div>
                    </div>
                    <label class="toggle"><input type="checkbox" class="perm-cb" value="handle_complaints"><span class="toggle-slider"></span></label>
                </div>
            </div>
        </div>

    </div>{{-- /perm-groups --}}

    {{-- Save Bar --}}
    <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
        <button class="btn btn-primary" id="save-btn" onclick="savePermissions()">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:15px;height:15px"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            Save Permissions
        </button>
        <a href="/admin/employees" class="btn btn-outline">Cancel</a>
        <div id="save-msg" style="display:none;font-size:13px;font-weight:500"></div>
    </div>

</div>{{-- /page-content --}}

<style>
.perm-group-icon {
    width:42px; height:42px; border-radius:10px;
    display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.perm-row {
    display:flex; align-items:center; justify-content:space-between; gap:12px;
    padding:11px 0; border-bottom:1px solid var(--border2);
}
.perm-row:last-child { border-bottom:none; }
.perm-info { flex:1; min-width:0; }
.perm-name  { font-size:13px; font-weight:600; color:var(--text); }
.perm-desc  { font-size:11px; color:var(--muted); margin-top:2px; }
.perm-badges{display:flex;gap:5px;margin-top:6px;flex-wrap:wrap}
.perm-badge{font-size:9.5px;font-weight:800;border-radius:999px;padding:2px 6px;text-transform:uppercase}
.perm-badge.pii{background:#fee2e2;color:#b91c1c}
.perm-badge.risk{background:#fef3c7;color:#92400e}

/* Toggle switch */
.toggle { position:relative; display:inline-block; width:40px; height:22px; flex-shrink:0; cursor:pointer; }
.toggle input { opacity:0; width:0; height:0; }
.toggle-slider {
    position:absolute; inset:0; background:#cbd5e1; border-radius:22px;
    transition:background .2s;
}
.toggle-slider::before {
    content:''; position:absolute; width:16px; height:16px; border-radius:50%;
    background:#fff; left:3px; top:3px; transition:transform .2s;
    box-shadow:0 1px 3px rgba(0,0,0,.2);
}
.toggle input:checked + .toggle-slider { background:var(--primary,#2563eb); }
.toggle input:checked + .toggle-slider::before { transform:translateX(18px); }
</style>
@endsection

@push('scripts')
<script>
const EMP_ID = {{ $employeeId ?? 'null' }};
let permissionGroups = [];

function esc(v){return String(v ?? '').replace(/[&<>"']/g,s=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]))}

async function loadPermissionMaster() {
    const res = await apiFetch('/api/v1/employee/permissions');
    if (!res) return;
    const json = await res.json();
    permissionGroups = json.data?.groups || [];
    renderPermissionGroups();
}

function renderPermissionGroups() {
    const root = document.getElementById('perm-groups');
    if (!permissionGroups.length) {
        root.innerHTML = '<div class="card" style="padding:24px;text-align:center;color:var(--text-muted)">No permission groups found. Create permissions from <a href="/admin/permissions">Permission Master</a>.</div>';
        return;
    }

    root.innerHTML = permissionGroups.map(group => `
        <div class="card perm-group" data-group="${esc(group.key)}">
            <div class="card-header">
                <div class="perm-group-icon" style="background:${esc(group.color || '#2563eb')}22">
                    <span style="width:14px;height:14px;border-radius:50%;background:${esc(group.color || '#2563eb')};display:block"></span>
                </div>
                <div>
                    <div class="card-title">${esc(group.name)}</div>
                    <div style="font-size:11px;color:var(--muted);margin-top:1px">${esc(group.description || 'Custom permission group')}</div>
                </div>
                <button class="group-toggle-all btn btn-outline btn-sm" style="margin-left:auto" onclick="toggleGroup('${esc(group.key)}')">Select All</button>
            </div>
            <div class="card-body" style="padding:14px 20px;display:flex;flex-direction:column;gap:0">
                ${(group.permissions || []).map(permission => `
                    <div class="perm-row" data-perm="${esc(permission.key)}">
                        <div class="perm-info">
                            <div class="perm-name">${esc(permission.name)}</div>
                            <div class="perm-desc">${esc(permission.description || permission.key)}</div>
                            <div class="perm-badges">
                                ${permission.is_pii ? '<span class="perm-badge pii">PII</span>' : ''}
                                ${permission.is_dangerous ? '<span class="perm-badge risk">High Risk</span>' : ''}
                            </div>
                        </div>
                        <label class="toggle"><input type="checkbox" class="perm-cb" value="${esc(permission.key)}"><span class="toggle-slider"></span></label>
                    </div>
                `).join('') || '<div style="font-size:13px;color:var(--muted);padding:16px 0">No permissions inside this group.</div>'}
            </div>
        </div>
    `).join('');

    document.querySelectorAll('.perm-cb').forEach(cb => {
        cb.addEventListener('change', () => {
            const group = cb.closest('.perm-group')?.dataset.group;
            if (group) syncGroupBtn(group);
        });
    });
}

// ── Load employee ────────────────────────────────────────────────────────────
async function loadEmployee() {
    if (!EMP_ID) { window.location.href = '/admin/employees'; return; }

    await loadPermissionMaster();

    const res = await apiFetch('/api/v1/employee/employees/' + EMP_ID);
    if (!res) return;
    const json = await res.json();
    const emp  = json.data || json.employee || json;

    // Fill info card
    document.getElementById('emp-avatar').textContent = (emp.name || 'E').charAt(0).toUpperCase();
    document.getElementById('emp-name').textContent   = emp.name  || '—';
    document.getElementById('emp-email').textContent  = emp.email || '—';
    document.getElementById('emp-dept').textContent   = [emp.department, emp.designation].filter(Boolean).join(' · ') || '—';
    document.getElementById('emp-code').textContent   = emp.employee_code || '';

    const roleColors = { super_admin:'#7c3aed', admin:'#2563eb', manager:'#0891b2', support:'#059669', finance:'#d97706' };
    const rc = roleColors[emp.role] || '#6b7280';
    document.getElementById('emp-role-badge').innerHTML =
        `<span style="background:${rc};color:#fff;font-size:10px;font-weight:700;padding:2px 9px;border-radius:20px">${(emp.role||'').replace('_',' ').toUpperCase()}</span>`;

    const sc = { active:'#10b981', inactive:'#6b7280', suspended:'#ef4444' };
    const sbg = { active:'rgba(16,185,129,.15)', inactive:'rgba(107,114,128,.15)', suspended:'rgba(239,68,68,.15)' };
    document.getElementById('emp-status-badge').innerHTML =
        `<span style="background:${sbg[emp.status]||sbg.inactive};color:${sc[emp.status]||sc.inactive};font-size:11px;font-weight:600;padding:3px 9px;border-radius:20px;text-transform:capitalize">${emp.status||'—'}</span>`;

    // Show admin note if admin/super_admin
    if (emp.role === 'admin' || emp.role === 'super_admin') {
        document.getElementById('admin-note').style.display = 'block';
    }

    // Apply current permissions
    const perms = emp.permissions || {};
    document.querySelectorAll('.perm-cb').forEach(cb => {
        cb.checked = perms[cb.value] === true;
    });

    // Sync "Select All" button labels
    document.querySelectorAll('.perm-group').forEach(g => syncGroupBtn(g.dataset.group));

    document.getElementById('page-loading').style.display = 'none';
    document.getElementById('page-content').style.display  = 'block';
}

// ── Toggle entire group ──────────────────────────────────────────────────────
function toggleGroup(group) {
    const card  = document.querySelector(`.perm-group[data-group="${group}"]`);
    const cbs   = card.querySelectorAll('.perm-cb');
    const allOn = [...cbs].every(cb => cb.checked);
    cbs.forEach(cb => { cb.checked = !allOn; });
    syncGroupBtn(group);
}

function syncGroupBtn(group) {
    const card = document.querySelector(`.perm-group[data-group="${group}"]`);
    if (!card) return;
    const cbs   = card.querySelectorAll('.perm-cb');
    const allOn = [...cbs].every(cb => cb.checked);
    const btn   = card.querySelector('.group-toggle-all');
    if (btn) btn.textContent = allOn ? 'Deselect All' : 'Select All';
}

// ── Save ─────────────────────────────────────────────────────────────────────
async function savePermissions() {
    const permissions = {};
    document.querySelectorAll('.perm-cb').forEach(cb => {
        permissions[cb.value] = cb.checked ? true : false;
    });

    const btn = document.getElementById('save-btn');
    const msg = document.getElementById('save-msg');
    btn.disabled = true;
    btn.textContent = 'Saving…';
    msg.style.display = 'none';

    const res = await apiFetch('/api/v1/employee/employees/' + EMP_ID, {
        method: 'PUT',
        body: JSON.stringify({ permissions }),
    });
    btn.disabled = false;
    btn.innerHTML = `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:15px;height:15px"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Save Permissions`;

    if (!res) return;
    if (res.ok) {
        msg.textContent = '✓ Permissions saved successfully.';
        msg.style.color = '#10b981';
    } else {
        const err = await res.json();
        msg.textContent = err.message || 'Failed to save.';
        msg.style.color = '#f87171';
    }
    msg.style.display = 'block';
    setTimeout(() => { msg.style.display = 'none'; }, 4000);
}

document.addEventListener('DOMContentLoaded', loadEmployee);
</script>
@endpush
