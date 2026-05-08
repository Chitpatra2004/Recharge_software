@extends('layouts.admin')
@section('title', 'Permission Master')
@section('page-title', 'Permission Master')

@section('content')
<div class="breadcrumb">
    <a href="/admin/dashboard">Dashboard</a>
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <a href="/admin/employees">Employees</a>
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>Permission Master</span>
</div>

<div style="display:grid;grid-template-columns:360px 1fr;gap:18px;align-items:start">
    <div class="card">
        <div class="card-header"><div class="card-title">Create Group</div></div>
        <div class="card-body" style="display:grid;gap:12px">
            <div><label class="pm-label">Group Name</label><input class="pm-input" id="group-name" placeholder="e.g. PII Data"></div>
            <div><label class="pm-label">Description</label><input class="pm-input" id="group-desc" placeholder="What this group controls"></div>
            <div style="display:grid;grid-template-columns:1fr 120px;gap:10px">
                <div><label class="pm-label">Color</label><input class="pm-input" id="group-color" value="#2563eb"></div>
                <div><label class="pm-label">Order</label><input class="pm-input" id="group-order" type="number" value="100"></div>
            </div>
            <button class="btn btn-primary" onclick="saveGroup()">Create Group</button>
            <div id="group-msg" class="pm-msg"></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header" style="justify-content:space-between">
            <div>
                <div class="card-title">Create Permission</div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px">Mark PII permissions for sensitive customer data access.</div>
            </div>
            <button class="btn btn-outline btn-sm" onclick="loadPermissions()">Refresh</button>
        </div>
        <div class="card-body" style="display:grid;gap:12px">
            <div style="display:grid;grid-template-columns:220px 1fr 180px;gap:10px">
                <div><label class="pm-label">Group</label><select class="pm-input" id="perm-group"></select></div>
                <div><label class="pm-label">Permission Name</label><input class="pm-input" id="perm-name" placeholder="e.g. View Customer PAN"></div>
                <div><label class="pm-label">Key</label><input class="pm-input" id="perm-key" placeholder="pii:view_pan"></div>
            </div>
            <div><label class="pm-label">Description</label><input class="pm-input" id="perm-desc" placeholder="Explain exactly what this right allows"></div>
            <div style="display:flex;align-items:center;gap:18px;flex-wrap:wrap">
                <label class="pm-check"><input type="checkbox" id="perm-pii"> PII / customer sensitive data</label>
                <label class="pm-check"><input type="checkbox" id="perm-danger"> High-risk action</label>
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--text-secondary)">Order <input class="pm-input" id="perm-order" type="number" value="100" style="width:90px"></label>
                <button class="btn btn-primary" onclick="savePermission()">Create Permission</button>
            </div>
            <div id="perm-msg" class="pm-msg"></div>
        </div>
    </div>
</div>

<div class="card" style="margin-top:18px">
    <div class="card-header" style="justify-content:space-between">
        <div>
            <div class="card-title">Permission Groups</div>
            <div style="font-size:12px;color:var(--text-muted);margin-top:2px">Use these groups while assigning employee rights. You can give a full group and remove individual permissions later.</div>
        </div>
        <a href="/admin/employees" class="btn btn-outline btn-sm">Assign to Employees</a>
    </div>
    <div class="card-body">
        <div id="permission-groups" class="pm-groups">
            <div style="padding:34px;text-align:center;color:var(--text-muted)">Loading permissions...</div>
        </div>
    </div>
</div>

<style>
.pm-label{display:block;font-size:12px;font-weight:700;color:var(--text-secondary);margin-bottom:6px}
.pm-input{width:100%;border:1px solid var(--border);background:var(--card-bg);color:var(--text-primary);border-radius:8px;padding:9px 10px;font-size:13px;outline:none}
.pm-input:focus{border-color:var(--accent-blue);box-shadow:0 0 0 3px rgba(37,99,235,.12)}
.pm-check{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--text-primary);font-weight:600}
.pm-msg{display:none;font-size:13px;font-weight:600}
.pm-groups{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:14px}
.pm-group{border:1px solid var(--border);border-radius:12px;overflow:hidden;background:var(--card-bg)}
.pm-group-head{padding:14px 16px;border-bottom:1px solid var(--border);display:flex;align-items:flex-start;gap:10px}
.pm-dot{width:12px;height:12px;border-radius:50%;margin-top:4px;flex:0 0 12px}
.pm-group-name{font-weight:800;color:var(--text-primary);font-size:14px}
.pm-group-desc{font-size:12px;color:var(--text-muted);margin-top:2px}
.pm-perm{display:flex;justify-content:space-between;gap:12px;padding:12px 16px;border-bottom:1px solid var(--border)}
.pm-perm:last-child{border-bottom:none}
.pm-perm-name{font-size:13px;font-weight:700;color:var(--text-primary)}
.pm-perm-desc{font-size:11.5px;color:var(--text-muted);margin-top:2px}
.pm-badges{display:flex;gap:6px;align-items:flex-start;flex-wrap:wrap;justify-content:flex-end}
.pm-badge{font-size:10px;font-weight:800;border-radius:999px;padding:3px 7px;text-transform:uppercase}
.pm-badge.pii{background:#fee2e2;color:#b91c1c}
.pm-badge.risk{background:#fef3c7;color:#92400e}
@media(max-width:900px){div[style*="grid-template-columns:360px 1fr"]{grid-template-columns:1fr!important}}
</style>
@endsection

@push('scripts')
<script>
let permissionGroups = [];

function esc(v){return String(v ?? '').replace(/[&<>"']/g,s=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]))}
function showMsg(id, text, ok=true){const el=document.getElementById(id);el.textContent=text;el.style.color=ok?'#10b981':'#ef4444';el.style.display='block';setTimeout(()=>el.style.display='none',3500)}

async function loadPermissions(){
    const res = await apiFetch('/api/v1/employee/permissions');
    if(!res) return;
    const json = await res.json();
    permissionGroups = json.data?.groups || [];
    renderGroups();
    fillGroupSelect();
}

function fillGroupSelect(){
    const sel = document.getElementById('perm-group');
    sel.innerHTML = permissionGroups.map(g => `<option value="${g.id}">${esc(g.name)}</option>`).join('');
}

function renderGroups(){
    const root = document.getElementById('permission-groups');
    if(!permissionGroups.length){
        root.innerHTML = '<div style="padding:30px;text-align:center;color:var(--text-muted)">No permission groups found.</div>';
        return;
    }
    root.innerHTML = permissionGroups.map(group => `
        <div class="pm-group">
            <div class="pm-group-head">
                <span class="pm-dot" style="background:${esc(group.color || '#2563eb')}"></span>
                <div style="flex:1">
                    <div class="pm-group-name">${esc(group.name)}</div>
                    <div class="pm-group-desc">${esc(group.description || 'No description')}</div>
                </div>
            </div>
            ${(group.permissions || []).map(p => `
                <div class="pm-perm">
                    <div>
                        <div class="pm-perm-name">${esc(p.name)}</div>
                        <div class="pm-perm-desc"><code>${esc(p.key)}</code>${p.description ? ' - ' + esc(p.description) : ''}</div>
                    </div>
                    <div class="pm-badges">
                        ${p.is_pii ? '<span class="pm-badge pii">PII</span>' : ''}
                        ${p.is_dangerous ? '<span class="pm-badge risk">Risk</span>' : ''}
                    </div>
                </div>
            `).join('') || '<div style="padding:18px;color:var(--text-muted);font-size:13px">No permissions in this group.</div>'}
        </div>
    `).join('');
}

async function saveGroup(){
    const body = {
        name: document.getElementById('group-name').value.trim(),
        description: document.getElementById('group-desc').value.trim(),
        color: document.getElementById('group-color').value.trim() || '#2563eb',
        sort_order: Number(document.getElementById('group-order').value || 100),
    };
    if(!body.name){showMsg('group-msg','Group name is required.',false);return}
    const res = await apiFetch('/api/v1/employee/permissions/groups',{method:'POST',body:JSON.stringify(body)});
    const json = await res.json().catch(()=>({}));
    if(res.ok){showMsg('group-msg','Group created.');document.getElementById('group-name').value='';document.getElementById('group-desc').value='';loadPermissions();}
    else showMsg('group-msg',json.message || 'Could not create group.',false);
}

async function savePermission(){
    const body = {
        group_id: Number(document.getElementById('perm-group').value),
        name: document.getElementById('perm-name').value.trim(),
        key: document.getElementById('perm-key').value.trim(),
        description: document.getElementById('perm-desc').value.trim(),
        is_pii: document.getElementById('perm-pii').checked,
        is_dangerous: document.getElementById('perm-danger').checked,
        sort_order: Number(document.getElementById('perm-order').value || 100),
    };
    if(!body.group_id || !body.name){showMsg('perm-msg','Group and permission name are required.',false);return}
    const res = await apiFetch('/api/v1/employee/permissions',{method:'POST',body:JSON.stringify(body)});
    const json = await res.json().catch(()=>({}));
    if(res.ok){
        showMsg('perm-msg','Permission created.');
        ['perm-name','perm-key','perm-desc'].forEach(id=>document.getElementById(id).value='');
        document.getElementById('perm-pii').checked=false;
        document.getElementById('perm-danger').checked=false;
        loadPermissions();
    } else showMsg('perm-msg',json.message || 'Could not create permission.',false);
}

document.addEventListener('DOMContentLoaded', loadPermissions);
</script>
@endpush
