@extends('layouts.admin')
@section('title', 'Manage Employees')
@section('page-title', 'Manage Employees')

@section('content')
<div class="breadcrumb">
    <a href="/admin/dashboard">Dashboard</a>
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>Manage Employees</span>
</div>

{{-- Top bar --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;gap:10px;flex-wrap:wrap">
    <div style="display:flex;gap:8px;flex-wrap:wrap">
        <input type="text" id="search-input" placeholder="Search name / email / code…" oninput="onSearch()"
            style="background:var(--card2);border:1px solid var(--border2);border-radius:7px;padding:7px 12px;font-size:13px;color:var(--text);outline:none;width:230px">
        <select id="f-role" onchange="loadEmployees()" style="background:var(--card2);border:1px solid var(--border2);border-radius:7px;padding:7px 10px;font-size:13px;color:var(--text);outline:none">
            <option value="">All Roles</option>
            <option value="super_admin">Super Admin</option>
            <option value="admin">Admin</option>
            <option value="manager">Manager</option>
            <option value="support">Support</option>
            <option value="finance">Finance</option>
        </select>
        <select id="f-status" onchange="loadEmployees()" style="background:var(--card2);border:1px solid var(--border2);border-radius:7px;padding:7px 10px;font-size:13px;color:var(--text);outline:none">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="suspended">Suspended</option>
        </select>
    </div>
    <button class="btn btn-primary btn-sm" onclick="openModal()">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:15px;height:15px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Add Employee
    </button>
</div>

<div class="card">
    <div class="card-header" style="justify-content:space-between">
        <span class="card-title">Employees</span>
        <span id="emp-count" style="font-size:12px;color:var(--muted)"></span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Email / Mobile</th>
                    <th>Department</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="emp-tbody">
                <tr><td colspan="8"><div class="loading"><div class="spinner"></div> Loading…</div></td></tr>
            </tbody>
        </table>
    </div>
    <div class="card-footer" id="emp-pagination" style="gap:8px;justify-content:flex-end"></div>
</div>

{{-- Add / Edit Modal --}}
<div id="emp-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:300;align-items:flex-start;justify-content:center;padding:30px 16px;overflow-y:auto">
    <div style="background:var(--card);border-radius:14px;width:100%;max-width:520px;box-shadow:0 24px 60px rgba(0,0,0,.4);margin:auto">
        <div style="padding:20px 24px;border-bottom:1px solid var(--border2);display:flex;align-items:center;justify-content:space-between">
            <h3 style="font-size:15px;font-weight:700;color:var(--text)" id="modal-title">Add Employee</h3>
            <button onclick="closeModal()" style="background:none;border:none;cursor:pointer;color:var(--muted)">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:18px;height:18px"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div style="padding:22px 24px;display:flex;flex-direction:column;gap:14px">

            {{-- Row 1: Name + Mobile --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div>
                    <label class="flabel">Full Name *</label>
                    <input type="text" id="m-name" placeholder="Rahul Sharma" class="finput">
                    <div class="ferr" id="err-name"></div>
                </div>
                <div>
                    <label class="flabel">Mobile</label>
                    <input type="tel" id="m-mobile" maxlength="10" placeholder="10-digit number" class="finput">
                    <div class="ferr" id="err-mobile"></div>
                </div>
            </div>

            {{-- Row 2: Email --}}
            <div>
                <label class="flabel">Email Address *</label>
                <input type="email" id="m-email" placeholder="employee@company.com" class="finput">
                <div class="ferr" id="err-email"></div>
            </div>

            {{-- Row 3: Department + Designation --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div>
                    <label class="flabel">Department</label>
                    <input type="text" id="m-department" placeholder="Operations" class="finput">
                    <div class="ferr" id="err-department"></div>
                </div>
                <div>
                    <label class="flabel">Designation</label>
                    <input type="text" id="m-designation" placeholder="Executive" class="finput">
                    <div class="ferr" id="err-designation"></div>
                </div>
            </div>

            {{-- Row 4: Role + Status --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div>
                    <label class="flabel">Role *</label>
                    <select id="m-role" class="finput">
                        <option value="support">Support</option>
                        <option value="finance">Finance</option>
                        <option value="manager">Manager</option>
                        <option value="admin">Admin</option>
                        <option value="super_admin">Super Admin</option>
                    </select>
                    <div class="ferr" id="err-role"></div>
                </div>
                <div>
                    <label class="flabel">Status</label>
                    <select id="m-status" class="finput">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>
            </div>

            {{-- Row 5: Password --}}
            <div>
                <label class="flabel">Password <span id="pwd-hint" style="font-weight:400;color:var(--muted)">(min 8 chars)</span></label>
                <div style="position:relative">
                    <input type="password" id="m-password" placeholder="Enter password" class="finput" style="padding-right:38px">
                    <button type="button" onclick="togglePwd()" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--muted)">
                        <svg id="eye-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
                <div class="ferr" id="err-password"></div>
            </div>

            {{-- Row 6: Max open complaints --}}
            <div>
                <label class="flabel">Max Open Complaints</label>
                <input type="number" id="m-maxcomp" min="0" max="9999" placeholder="e.g. 10" class="finput">
                <div class="ferr" id="err-maxcomp"></div>
            </div>

            {{-- Permissions --}}
            <div>
                <label class="flabel" style="margin-bottom:8px">Permissions</label>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px" id="perm-grid">
                    @foreach([
                        'view_users'       => 'View Users',
                        'edit_users'       => 'Edit Users',
                        'view_reports'     => 'View Reports',
                        'export_reports'   => 'Export Reports',
                        'manage_wallets'   => 'Manage Wallets',
                        'process_refunds'  => 'Process Refunds',
                        'handle_complaints'=> 'Handle Complaints',
                        'view_activity'    => 'View Activity Logs',
                    ] as $key => $label)
                    <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer;padding:6px 8px;border:1px solid var(--border2);border-radius:6px;color:var(--text)">
                        <input type="checkbox" class="perm-cb" value="{{ $key }}" style="accent-color:var(--primary)">
                        {{ $label }}
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Submit --}}
            <div style="display:flex;gap:10px;margin-top:4px">
                <button class="btn btn-primary" style="flex:1" onclick="saveEmployee()" id="save-btn">Save Employee</button>
                <button class="btn btn-outline" onclick="closeModal()">Cancel</button>
            </div>
            <div id="modal-err" style="display:none;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);border-radius:8px;padding:10px 12px;font-size:13px;color:#f87171"></div>
        </div>
    </div>
</div>

<style>
.flabel { font-size:12px; font-weight:600; color:var(--muted); display:block; margin-bottom:4px }
.finput  { width:100%; background:var(--card2); border:1px solid var(--border2); border-radius:7px; padding:8px 12px; font-size:13px; color:var(--text); outline:none; box-sizing:border-box }
.finput:focus { border-color:var(--primary); }
.ferr   { font-size:11px; color:#f87171; margin-top:3px; min-height:14px }
</style>
@endsection

@push('scripts')
<script>
let allEmployees = [];
let editingId    = null;
let searchTimer  = null;

// ── Load employees ────────────────────────────────────────────────────────────
async function loadEmployees(page = 1) {
    document.getElementById('emp-tbody').innerHTML =
        '<tr><td colspan="8"><div class="loading"><div class="spinner"></div> Loading…</div></td></tr>';

    const p = new URLSearchParams({ page, per_page: 20 });
    const search = document.getElementById('search-input').value.trim();
    const role   = document.getElementById('f-role').value;
    const status = document.getElementById('f-status').value;
    if (search) p.set('search', search);
    if (role)   p.set('role', role);
    if (status) p.set('status', status);

    const res = await apiFetch('/api/v1/employee/employees?' + p);
    if (!res) return;
    const json = await res.json();
    const rows = json.data?.data || json.data || [];
    const total = json.data?.total ?? null;

    document.getElementById('emp-count').textContent = total != null ? fmtNum(total) + ' employees' : '';

    if (!rows.length) {
        document.getElementById('emp-tbody').innerHTML =
            '<tr><td colspan="8" style="text-align:center;color:var(--muted);padding:30px">No employees found</td></tr>';
        document.getElementById('emp-pagination').innerHTML = '';
        return;
    }

    document.getElementById('emp-tbody').innerHTML = rows.map(e => `<tr>
        <td style="font-family:monospace;font-size:11px;color:var(--muted)">${e.employee_code||'—'}</td>
        <td><strong>${e.name||'—'}</strong>${e.designation?`<br><span style="font-size:11px;color:var(--muted)">${e.designation}</span>`:''}</td>
        <td style="font-size:12px">${e.email||'—'}${e.mobile?`<br><span style="color:var(--muted)">${e.mobile}</span>`:''}</td>
        <td style="font-size:12px;color:var(--muted)">${e.department||'—'}</td>
        <td>${roleBadge(e.role)}</td>
        <td>${statusBadge(e.status)}</td>
        <td style="font-size:11px;color:var(--muted)">${e.last_login_at ? new Date(e.last_login_at).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'}) : 'Never'}</td>
        <td style="white-space:nowrap">
            <button class="btn btn-outline btn-sm" style="margin-right:4px" onclick='openModal(${JSON.stringify(e).replace(/'/g,"\\'")} )'>Edit</button>
            <button class="btn btn-sm" style="background:rgba(239,68,68,.15);color:#f87171;border:1px solid rgba(239,68,68,.25)" onclick="deleteEmployee(${e.id},'${(e.name||'').replace(/'/g,"\\'")}')">Delete</button>
        </td>
    </tr>`).join('');

    // Pagination
    const meta     = json.data || {};
    const lastPage = meta.last_page || 1;
    const currPage = meta.current_page || page;
    const pag = document.getElementById('emp-pagination');
    if (lastPage > 1) {
        let h = '';
        if (currPage > 1) h += `<button class="btn btn-outline btn-sm" onclick="loadEmployees(${currPage-1})">← Prev</button>`;
        h += `<span style="font-size:12px;color:var(--muted)">Page ${currPage} of ${lastPage}</span>`;
        if (currPage < lastPage) h += `<button class="btn btn-outline btn-sm" onclick="loadEmployees(${currPage+1})">Next →</button>`;
        pag.innerHTML = h;
    } else pag.innerHTML = '';
}

function onSearch() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => loadEmployees(), 350);
}

// ── Role / Status badges ──────────────────────────────────────────────────────
function roleBadge(r) {
    const colors = {
        super_admin: '#7c3aed', admin: '#2563eb', manager: '#0891b2',
        support: '#059669', finance: '#d97706'
    };
    const bg = colors[r] || '#6b7280';
    return `<span style="background:${bg};color:#fff;font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;white-space:nowrap">${(r||'—').replace('_',' ').toUpperCase()}</span>`;
}
function statusBadge(s) {
    const cfg = { active:['#10b981','rgba(16,185,129,.15)'], inactive:['#6b7280','rgba(107,114,128,.15)'], suspended:['#ef4444','rgba(239,68,68,.15)'] };
    const [c, bg] = cfg[s] || ['#6b7280','rgba(107,114,128,.15)'];
    return `<span style="background:${bg};color:${c};font-size:11px;font-weight:600;padding:3px 9px;border-radius:20px;text-transform:capitalize">${s||'—'}</span>`;
}

// ── Modal ─────────────────────────────────────────────────────────────────────
function openModal(emp = null) {
    editingId = emp?.id ?? null;
    clearErrors();
    document.getElementById('modal-title').textContent = emp ? 'Edit Employee' : 'Add Employee';
    document.getElementById('m-name').value        = emp?.name        ?? '';
    document.getElementById('m-email').value       = emp?.email       ?? '';
    document.getElementById('m-mobile').value      = emp?.mobile      ?? '';
    document.getElementById('m-department').value  = emp?.department  ?? '';
    document.getElementById('m-designation').value = emp?.designation ?? '';
    document.getElementById('m-role').value        = emp?.role        ?? 'support';
    document.getElementById('m-status').value      = emp?.status      ?? 'active';
    document.getElementById('m-password').value    = '';
    document.getElementById('m-maxcomp').value     = emp?.max_open_complaints ?? '';

    // Password hint
    document.getElementById('pwd-hint').textContent = emp ? '(leave blank to keep current)' : '(min 8 chars)';

    // Permissions
    const perms = emp?.permissions || {};
    document.querySelectorAll('.perm-cb').forEach(cb => {
        cb.checked = perms[cb.value] === true;
    });

    document.getElementById('modal-err').style.display = 'none';
    const modal = document.getElementById('emp-modal');
    modal.style.display = 'flex';
    modal.onclick = e => { if (e.target === modal) closeModal(); };
}

function closeModal() {
    document.getElementById('emp-modal').style.display = 'none';
    editingId = null;
}

function clearErrors() {
    ['name','email','mobile','department','designation','role','password','maxcomp'].forEach(f => {
        const el = document.getElementById('err-' + f);
        if (el) el.textContent = '';
    });
}

function togglePwd() {
    const inp = document.getElementById('m-password');
    inp.type = inp.type === 'password' ? 'text' : 'password';
}

async function saveEmployee() {
    clearErrors();
    document.getElementById('modal-err').style.display = 'none';

    const permissions = {};
    document.querySelectorAll('.perm-cb:checked').forEach(cb => { permissions[cb.value] = true; });

    const body = {
        name:        document.getElementById('m-name').value.trim(),
        email:       document.getElementById('m-email').value.trim(),
        mobile:      document.getElementById('m-mobile').value.trim() || null,
        department:  document.getElementById('m-department').value.trim() || null,
        designation: document.getElementById('m-designation').value.trim() || null,
        role:        document.getElementById('m-role').value,
        status:      document.getElementById('m-status').value,
        permissions,
    };

    const maxComp = document.getElementById('m-maxcomp').value.trim();
    if (maxComp !== '') body.max_open_complaints = parseInt(maxComp);

    const pwd = document.getElementById('m-password').value;
    if (pwd) body.password = pwd;

    // Client-side checks
    if (!body.name)  { document.getElementById('err-name').textContent  = 'Name is required.'; return; }
    if (!body.email) { document.getElementById('err-email').textContent = 'Email is required.'; return; }
    if (!editingId && !pwd) { document.getElementById('err-password').textContent = 'Password is required for new employees.'; return; }

    const btn = document.getElementById('save-btn');
    btn.disabled = true;
    btn.textContent = 'Saving…';

    const url    = editingId ? `/api/v1/employee/employees/${editingId}` : '/api/v1/employee/employees';
    const method = editingId ? 'PUT' : 'POST';
    const res = await apiFetch(url, { method, body: JSON.stringify(body) });
    btn.disabled = false;
    btn.textContent = 'Save Employee';

    if (!res) return;

    if (res.ok) {
        closeModal();
        loadEmployees();
    } else {
        const err = await res.json();
        // Map field errors
        if (err.errors) {
            const map = {
                name:'err-name', email:'err-email', mobile:'err-mobile',
                department:'err-department', designation:'err-designation',
                role:'err-role', password:'err-password',
                max_open_complaints:'err-maxcomp',
            };
            Object.entries(err.errors).forEach(([field, msgs]) => {
                const elId = map[field];
                if (elId) document.getElementById(elId).textContent = Array.isArray(msgs) ? msgs[0] : msgs;
            });
        }
        const box = document.getElementById('modal-err');
        box.textContent = err.message || 'Failed to save employee.';
        box.style.display = 'block';
    }
}

async function deleteEmployee(id, name) {
    if (!confirm(`Delete employee "${name}"? This action cannot be undone.`)) return;
    const res = await apiFetch(`/api/v1/employee/employees/${id}`, { method: 'DELETE' });
    if (res?.ok) loadEmployees();
    else {
        const e = await res?.json();
        alert(e?.message || 'Failed to delete employee.');
    }
}

document.addEventListener('DOMContentLoaded', () => loadEmployees());
</script>
@endpush
