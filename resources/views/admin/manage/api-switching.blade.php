@extends('layouts.admin')

@section('title', 'API Switching')
@section('page-title', 'API Switching')

@section('content')

<style>
.sw-page{max-width:1320px;margin:0 auto}
.sw-hero{display:flex;align-items:center;justify-content:space-between;gap:18px;background:linear-gradient(135deg,#615cf6,#a855f7);border-radius:18px;padding:24px 28px;color:#fff;box-shadow:0 18px 45px rgba(99,102,241,.25);margin-bottom:28px}
.sw-hero-left{display:flex;align-items:center;gap:16px}
.sw-icon{width:56px;height:56px;border-radius:14px;background:rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center}
.sw-title{font-size:24px;font-weight:800;line-height:1.1}
.sw-sub{font-size:14px;color:rgba(255,255,255,.86);margin-top:4px}
.sw-stats{display:flex;gap:14px}
.sw-stat{min-width:150px;border:1px solid rgba(255,255,255,.18);background:rgba(255,255,255,.12);border-radius:14px;padding:14px 16px;display:flex;align-items:center;gap:12px}
.sw-stat-mark{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-weight:900}
.sw-stat-mark.green{background:#20d982}.sw-stat-mark.orange{background:#ff9f43}
.sw-stat-label{font-size:11px;font-weight:800;text-transform:uppercase;color:rgba(255,255,255,.78)}
.sw-stat-value{font-size:24px;font-weight:900;line-height:1}
.sw-tabs{display:inline-flex;background:#fff;border:1px solid var(--border);border-radius:15px;padding:7px;box-shadow:var(--shadow-sm);margin-bottom:22px}
.sw-tab{border:none;background:transparent;border-radius:10px;padding:12px 18px;font-size:13px;font-weight:800;color:#475569}
.sw-tab.active{background:#5b5ff2;color:#fff;box-shadow:0 8px 20px rgba(91,95,242,.25)}
.sw-panel{background:#fff;border:1px solid var(--border);border-radius:16px;overflow:hidden;margin-bottom:22px;box-shadow:var(--shadow-sm)}
.sw-panel-head{background:linear-gradient(135deg,#625df5,#8b50ee);color:#fff;padding:16px 20px;display:flex;align-items:center;justify-content:space-between}
.sw-panel-title{font-size:16px;font-weight:900;display:flex;align-items:center;gap:8px}
.sw-panel-body{padding:18px 20px}
.sw-filter-grid{display:grid;grid-template-columns:1fr 1.25fr .75fr;gap:16px;align-items:end}
.sw-field label{display:block;font-size:12px;font-weight:800;margin-bottom:8px;color:#111827}
.sw-field select{width:100%;border:1px solid #dbe2ff;border-radius:10px;padding:12px 14px;font-size:14px;background:#fff;color:#172033;outline:none}
.sw-btn{border:none;border-radius:10px;background:#5597f4;color:#fff;font-size:14px;font-weight:900;padding:13px 18px;cursor:pointer}
.sw-table-wrap{overflow:auto;padding:18px 20px}
.sw-table{width:100%;border-collapse:collapse;min-width:980px}
.sw-table th{background:#5d5ff0;color:#fff;padding:14px 12px;font-size:13px;text-transform:uppercase;text-align:left;border-right:1px solid rgba(255,255,255,.12)}
.sw-table th:first-child{border-top-left-radius:10px}.sw-table th:last-child{border-top-right-radius:10px;border-right:none}
.sw-table td{padding:14px 12px;border-bottom:1px solid #e9eef7;background:#f6fffb;color:#06152b;font-size:13px}
.sw-table.disabled td{background:#f8fafc;color:#475569}
.sw-table input{width:76px;border:1px solid #d6e0ff;border-radius:8px;padding:9px 10px;font-size:14px;text-align:center;background:#f8fbff;color:#06152b}
.sw-pill{display:inline-flex;align-items:center;border-radius:999px;padding:4px 12px;font-size:11px;font-weight:900;text-transform:uppercase;background:rgba(255,255,255,.22);color:#fff}
.sw-toggle{position:relative;display:inline-block;width:20px;height:20px}
.sw-toggle input{display:none}
.sw-toggle span{position:absolute;inset:0;border-radius:6px;background:#cbd5e1;border:2px solid #cbd5e1;cursor:pointer}
.sw-toggle input:checked + span{background:#17b987;border-color:#17b987}
.sw-save{border:none;background:#4f46e5;color:#fff;border-radius:8px;padding:8px 12px;font-weight:800;cursor:pointer}
.sw-muted{color:var(--text-muted);font-size:13px;text-align:center;padding:26px!important}
@media(max-width:900px){.sw-hero{align-items:flex-start;flex-direction:column}.sw-stats{width:100%;flex-wrap:wrap}.sw-filter-grid{grid-template-columns:1fr}}
</style>

<div class="sw-page">
    <div class="sw-hero">
        <div class="sw-hero-left">
            <div class="sw-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:30px;height:30px"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M7 7v10m10-10v10M5 17h14M9 11h6"/></svg>
            </div>
            <div>
                <div class="sw-title">Route Management</div>
                <div class="sw-sub">Configure API routing, priorities, and traffic switching rules</div>
            </div>
        </div>
        <div class="sw-stats">
            <div class="sw-stat">
                <div class="sw-stat-mark green">✓</div>
                <div><div class="sw-stat-label">Active APIs</div><div class="sw-stat-value" id="stat-active">0</div></div>
            </div>
            <div class="sw-stat">
                <div class="sw-stat-mark orange">⇄</div>
                <div><div class="sw-stat-label">Routes</div><div class="sw-stat-value" id="stat-routes">0</div></div>
            </div>
        </div>
    </div>

    <div class="sw-tabs">
        <button class="sw-tab active">API Priority Management</button>
        <button class="sw-tab">Custom API Routing</button>
        <button class="sw-tab">Company Management</button>
    </div>

    <div class="sw-panel">
        <div class="sw-panel-head">
            <div class="sw-panel-title">Select Operator</div>
        </div>
        <div class="sw-panel-body">
            <div class="sw-filter-grid">
                <div class="sw-field">
                    <label>Service Type</label>
                    <select id="service-type" onchange="fillOperatorSelect()"></select>
                </div>
                <div class="sw-field">
                    <label>Operator</label>
                    <select id="operator-id"></select>
                </div>
                <button class="sw-btn" onclick="loadSwitching()">Load API List</button>
            </div>
        </div>
    </div>

    <div class="sw-panel">
        <div class="sw-panel-head">
            <div class="sw-panel-title">✓ Active APIs</div>
            <span class="sw-pill">Enabled</span>
        </div>
        <div class="sw-table-wrap">
            <table class="sw-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>API Name</th>
                        <th>Pending Limit</th>
                        <th>Total Pending</th>
                        <th>Priority</th>
                        <th>Enable/Disable</th>
                        <th>Failure Limit</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="active-body"><tr><td class="sw-muted" colspan="8">Load an operator to view active APIs.</td></tr></tbody>
            </table>
        </div>
    </div>

    <div class="sw-panel">
        <div class="sw-panel-head">
            <div class="sw-panel-title">Disabled / Future Switch APIs</div>
            <span class="sw-pill">Available</span>
        </div>
        <div class="sw-table-wrap">
            <table class="sw-table disabled">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>API Name</th>
                        <th>Pending Limit</th>
                        <th>Total Pending</th>
                        <th>Priority</th>
                        <th>Enable/Disable</th>
                        <th>Failure Limit</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="disabled-body"><tr><td class="sw-muted" colspan="8">Disabled APIs will appear here.</td></tr></tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const SWITCH_API = '/api/v1/employee/api-switching';
let switchState = {services:[], operators:[], routes:[]};

function esc(v) {
    return String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

function serviceLabel(v) {
    const map = {mobile:'Mobile', dth:'DTH', broadband:'Broadband', electricity:'Electricity', gas:'Gas', water:'Water', insurance:'Insurance', landline:'Landline', loan:'Loan EMI', fastag:'FASTag', credit_card:'Credit Card', municipal_tax:'Municipal Tax', education:'Education', subscription:'Subscription'};
    return map[v] || v || '-';
}

async function bootSwitching() {
    const res = await apiFetch(SWITCH_API);
    if (!res) return;
    const json = await res.json();
    switchState = json;
    const svc = document.getElementById('service-type');
    svc.innerHTML = (json.services || []).map(s => `<option value="${esc(s)}">${esc(serviceLabel(s))}</option>`).join('');
    fillOperatorSelect(json.selected?.operator_id);
    renderSwitching(json);
}

function fillOperatorSelect(selectedId = null) {
    const service = document.getElementById('service-type').value;
    const ops = (switchState.operators || []).filter(o => !service || o.category === service);
    const select = document.getElementById('operator-id');
    select.innerHTML = ops.map(o => `<option value="${o.id}" ${Number(selectedId) === Number(o.id) ? 'selected' : ''}>${esc(o.name)}</option>`).join('');
}

async function loadSwitching() {
    const id = document.getElementById('operator-id').value;
    const url = `${SWITCH_API}?operator_id=${encodeURIComponent(id)}&recharge_type=prepaid`;
    const res = await apiFetch(url);
    if (!res) return;
    const json = await res.json();
    switchState = {...switchState, ...json};
    renderSwitching(json);
}

function renderSwitching(data) {
    document.getElementById('stat-active').textContent = data.stats?.active_apis ?? 0;
    document.getElementById('stat-routes').textContent = data.stats?.routes ?? 0;
    const active = (data.routes || []).filter(r => r.is_active);
    const disabled = (data.routes || []).filter(r => !r.is_active);
    renderRows('active-body', active);
    renderRows('disabled-body', disabled);
}

function renderRows(target, rows) {
    const tbody = document.getElementById(target);
    if (!rows.length) {
        tbody.innerHTML = `<tr><td class="sw-muted" colspan="8">${target === 'active-body' ? 'No active APIs enabled.' : 'No disabled APIs found.'}</td></tr>`;
        return;
    }
    tbody.innerHTML = rows.map((r, i) => `
        <tr data-api="${esc(r.api_name)}" data-operator="${r.operator_id}" data-type="${r.recharge_type}">
            <td>${i + 1}</td>
            <td><strong>${esc(r.api_name)}</strong></td>
            <td><input data-field="pending_limit" type="number" min="0" value="${Number(r.pending_limit || 0)}"></td>
            <td><input data-field="total_pending" type="number" value="${Number(r.total_pending || 0)}" readonly></td>
            <td><input data-field="priority" type="number" min="0" max="255" value="${Number(r.priority || 0)}"></td>
            <td>
                <label class="sw-toggle">
                    <input data-field="is_active" type="checkbox" ${r.is_active ? 'checked' : ''}>
                    <span></span>
                </label>
            </td>
            <td><input data-field="failure_limit" type="number" min="0" value="${Number(r.failure_limit || 0)}"></td>
            <td><button class="sw-save" onclick="saveSwitchRow(this)">Save</button></td>
        </tr>
    `).join('');
}

async function saveSwitchRow(btn) {
    const row = btn.closest('tr');
    const get = field => row.querySelector(`[data-field="${field}"]`);
    const body = {
        operator_id: row.dataset.operator,
        api_name: row.dataset.api,
        recharge_type: row.dataset.type,
        pending_limit: get('pending_limit').value,
        priority: get('priority').value,
        is_active: get('is_active').checked,
        failure_limit: get('failure_limit').value,
    };

    btn.disabled = true;
    btn.textContent = 'Saving...';
    try {
        const res = await apiFetch(`${SWITCH_API}/routes`, {method:'POST', body:JSON.stringify(body)});
        const json = await res.json().catch(() => ({}));
        if (!res.ok) {
            alert(json.message || 'Route save failed.');
            return;
        }
        showSwitchToast(json.message || 'Route saved.');
        await loadSwitching();
    } finally {
        btn.disabled = false;
        btn.textContent = 'Save';
    }
}

function showSwitchToast(message) {
    const t = document.createElement('div');
    t.textContent = message;
    Object.assign(t.style, {position:'fixed',right:'24px',bottom:'24px',background:'#166534',color:'#fff',borderRadius:'9px',padding:'11px 18px',fontSize:'13px',fontWeight:'800',zIndex:9999});
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 2600);
}

document.addEventListener('DOMContentLoaded', bootSwitching);
</script>
@endpush
