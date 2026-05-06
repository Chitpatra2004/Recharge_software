@extends('layouts.admin')

@section('title', 'Operator Settings')
@section('page-title', 'Operator Settings')

@section('content')

<style>
.op-toolbar{background:#fff;border:1px solid var(--border);border-radius:8px;margin-bottom:18px}
.op-toolbar-head{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid var(--border)}
.op-filters{display:flex;gap:10px;padding:14px 18px;flex-wrap:wrap}
.op-filters input,.op-filters select{border:1px solid var(--border);border-radius:7px;padding:8px 11px;font-size:13px;background:#fff;color:var(--text-primary);outline:none}
.op-table-card{background:#fff;border:1px solid var(--border);border-radius:8px;overflow:hidden}
.op-group-title{background:#343a40;color:#fff;font-size:28px;font-weight:700;line-height:1;padding:8px 12px}
.op-table-wrap{overflow:auto}
.op-table{width:100%;border-collapse:collapse;min-width:1180px}
.op-table th{padding:12px 12px;text-align:left;font-size:12px;font-weight:800;text-transform:uppercase;color:#1f2937;background:#fff;border-bottom:1px solid var(--border)}
.op-table td{padding:12px;border-bottom:1px solid var(--border);font-size:14px;color:#111827;vertical-align:middle}
.op-table tbody tr:nth-child(odd) td{background:#f1f1f1}
.op-table input,.op-table select{border:1px solid #222;border-radius:3px;padding:7px 8px;font-size:14px;background:#fff;color:#000;min-width:88px}
.op-table select{min-width:100px}
.op-name{color:#005fd1;font-weight:800}
.op-actions{display:flex;gap:8px;align-items:center}
.op-link{border:none;background:transparent;color:#0068d9;font-weight:700;cursor:pointer;padding:4px}
.op-switch{position:relative;display:inline-block;width:68px;height:38px}
.op-switch input{display:none}
.op-slider{position:absolute;inset:0;cursor:pointer;background:#b91c1c;border-radius:999px;transition:.2s}
.op-slider:before{content:'';position:absolute;width:30px;height:30px;left:4px;top:4px;background:#fff;border-radius:50%;transition:.2s}
.op-switch input:checked + .op-slider{background:#078b06}
.op-switch input:checked + .op-slider:before{transform:translateX(30px)}
.op-modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:300;align-items:center;justify-content:center;padding:18px}
.op-modal-overlay.open{display:flex}
.op-modal{width:520px;max-width:96vw;background:#fff;border-radius:10px;box-shadow:0 18px 60px rgba(0,0,0,.25)}
.op-modal-head{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border)}
.op-modal-title{font-size:16px;font-weight:800}
.op-modal-body{padding:20px;display:grid;grid-template-columns:1fr 1fr;gap:14px}
.op-field.full{grid-column:1/-1}
.op-field label{display:block;font-size:12px;font-weight:700;color:var(--text-secondary);margin-bottom:5px;text-transform:uppercase}
.op-field input,.op-field select{width:100%;border:1px solid var(--border);border-radius:8px;padding:9px 12px;font-size:13px;outline:none;background:#fff}
.op-modal-foot{padding:14px 20px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:10px}
.op-alert{display:none;margin:0 20px 16px;padding:10px 12px;border-radius:8px;background:#fee2e2;color:#991b1b;font-size:13px}
@media(max-width:760px){.op-group-title{font-size:22px}.op-modal-body{grid-template-columns:1fr}}
</style>

<div class="op-toolbar">
    <div class="op-toolbar-head">
        <div style="font-size:13px;font-weight:800;color:#111827;text-transform:uppercase">Search Filters</div>
        <button class="btn btn-primary btn-sm" style="background:#10b900" onclick="openOperatorModal()">Add Operator</button>
    </div>
    <div class="op-filters">
        <input id="op-search" type="text" placeholder="Search operator..." oninput="filterOperators()">
        <select id="op-category-filter" onchange="filterOperators()">
            <option value="">All Services</option>
            <option value="mobile">Mobile</option>
            <option value="dth">DTH</option>
            <option value="broadband">Broadband</option>
            <option value="electricity">Electricity</option>
            <option value="gas">Gas</option>
            <option value="water">Water</option>
            <option value="insurance">Insurance</option>
            <option value="landline">Landline</option>
            <option value="loan">Loan EMI</option>
            <option value="fastag">FASTag</option>
            <option value="credit_card">Credit Card</option>
            <option value="municipal_tax">Municipal Tax</option>
            <option value="education">Education</option>
            <option value="subscription">Subscription</option>
        </select>
    </div>
</div>

<div class="op-table-card">
    <div class="op-group-title" id="op-group-title">Mobile</div>
    <div id="op-loading" class="loading-overlay"><div class="spinner"></div> Loading operators...</div>
    <div class="op-table-wrap" id="op-table-wrap" style="display:none">
        <table class="op-table">
            <thead>
                <tr>
                    <th>Operator Name</th>
                    <th>OP Code</th>
                    <th>Service Name</th>
                    <th>Status</th>
                    <th>Reroot Count</th>
                    <th>API</th>
                    <th>Min Comm</th>
                    <th>Max Comm</th>
                    <th>Min Amt</th>
                    <th>Maxamt</th>
                    <th>Act</th>
                </tr>
            </thead>
            <tbody id="op-tbody"></tbody>
        </table>
    </div>
</div>

<div id="op-modal" class="op-modal-overlay">
    <div class="op-modal">
        <div class="op-modal-head">
            <div class="op-modal-title">Add Operator</div>
            <button onclick="closeOperatorModal()" style="background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:22px;line-height:1">&times;</button>
        </div>
        <div class="op-modal-body">
            <div class="op-field full">
                <label>Operator Name *</label>
                <input id="new-name" type="text" placeholder="Airtel prepaid">
            </div>
            <div class="op-field">
                <label>OP Code *</label>
                <input id="new-code" type="text" placeholder="ATL">
            </div>
            <div class="op-field">
                <label>Service *</label>
                <select id="new-category">
                    <option value="mobile">Mobile</option>
                    <option value="dth">DTH</option>
                    <option value="broadband">Broadband</option>
                    <option value="electricity">Electricity</option>
                    <option value="gas">Gas</option>
                    <option value="water">Water</option>
                    <option value="insurance">Insurance</option>
                    <option value="landline">Landline</option>
                    <option value="loan">Loan EMI</option>
                    <option value="fastag">FASTag</option>
                    <option value="credit_card">Credit Card</option>
                    <option value="municipal_tax">Municipal Tax</option>
                    <option value="education">Education</option>
                    <option value="subscription">Subscription</option>
                </select>
            </div>
            <div class="op-field">
                <label>Min Comm</label>
                <input id="new-min-comm" type="number" step="0.01" value="0">
            </div>
            <div class="op-field">
                <label>Max Comm</label>
                <input id="new-max-comm" type="number" step="0.01" value="0">
            </div>
            <div class="op-field">
                <label>Min Amt</label>
                <input id="new-min-amt" type="number" step="0.01" value="10">
            </div>
            <div class="op-field">
                <label>Max Amt</label>
                <input id="new-max-amt" type="number" step="0.01" value="100000">
            </div>
            <div class="op-field full">
                <label>API</label>
                <select id="new-api"></select>
            </div>
        </div>
        <div id="op-modal-error" class="op-alert"></div>
        <div class="op-modal-foot">
            <button class="btn btn-outline btn-sm" onclick="closeOperatorModal()">Cancel</button>
            <button class="btn btn-primary btn-sm" id="op-save-btn" onclick="addOperator()">Save</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const OP_API = '/api/v1/employee/operator-settings';
let operators = [];
let apiProviders = [];

function esc(v) {
    return String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

function serviceName(category) {
    const map = {mobile:'Mobile', dth:'DTH', broadband:'Broadband', electricity:'Electricity', gas:'Gas', water:'Water', insurance:'Insurance', landline:'Landline', loan:'Loan EMI', fastag:'FASTag', credit_card:'Credit Card', municipal_tax:'Municipal Tax', education:'Education', subscription:'Subscription'};
    return map[category] || category || '-';
}

async function loadOperators() {
    document.getElementById('op-loading').style.display = 'flex';
    document.getElementById('op-table-wrap').style.display = 'none';
    const res = await apiFetch(OP_API);
    if (!res) return;
    const json = await res.json();
    operators = json.operators || [];
    apiProviders = json.api_providers || [];
    fillApiSelect(document.getElementById('new-api'));
    document.getElementById('op-loading').style.display = 'none';
    document.getElementById('op-table-wrap').style.display = 'block';
    filterOperators();
}

function fillApiSelect(select, selected = '') {
    select.innerHTML = '<option value="">Select</option>' + apiProviders.map(p => `<option value="${esc(p)}" ${p === selected ? 'selected' : ''}>${esc(p)}</option>`).join('');
}

function filterOperators() {
    const q = document.getElementById('op-search').value.toLowerCase();
    const cat = document.getElementById('op-category-filter').value;
    const rows = operators.filter(op => {
        const hit = [op.name, op.code, op.category].some(v => String(v || '').toLowerCase().includes(q));
        return hit && (!cat || op.category === cat);
    });
    document.getElementById('op-group-title').textContent = cat ? serviceName(cat) : (rows[0] ? serviceName(rows[0].category) : 'Operators');
    renderOperators(rows);
}

function renderOperators(rows) {
    const tbody = document.getElementById('op-tbody');
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="11" style="text-align:center;padding:28px;color:var(--text-muted)">No operators found.</td></tr>';
        return;
    }

    tbody.innerHTML = rows.map(op => `
        <tr id="op-row-${op.id}">
            <td><input type="hidden" data-field="category" value="${esc(op.category)}"><span class="op-name">${esc(op.name)}</span></td>
            <td>${esc(op.code)}</td>
            <td>${esc(serviceName(op.category))}</td>
            <td>
                <label class="op-switch">
                    <input type="checkbox" data-field="is_active" ${op.is_active ? 'checked' : ''}>
                    <span class="op-slider"></span>
                </label>
            </td>
            <td><input data-field="reroot_count" type="number" value="${Number(op.reroot_count || 0)}" readonly></td>
            <td><select data-field="api_provider"><option value="">Select</option>${apiProviders.map(p => `<option value="${esc(p)}" ${p === op.api_provider ? 'selected' : ''}>${esc(p)}</option>`).join('')}</select></td>
            <td><input data-field="min_comm" type="number" step="0.01" value="${Number(op.min_comm || 0).toFixed(2)}"></td>
            <td><input data-field="max_comm" type="number" step="0.01" value="${Number(op.max_comm || 0).toFixed(2)}"></td>
            <td><input data-field="min_amount" type="number" step="0.01" value="${Number(op.min_amount || 0)}"></td>
            <td><input data-field="max_amount" type="number" step="0.01" value="${Number(op.max_amount || 0)}"></td>
            <td>
                <div class="op-actions">
                    <button class="op-link" onclick="saveRow(${op.id})">Edit</button>
                </div>
            </td>
        </tr>
    `).join('');
}

function rowPayload(id) {
    const op = operators.find(x => x.id === id);
    const row = document.getElementById('op-row-' + id);
    const val = field => row.querySelector(`[data-field="${field}"]`);
    return {
        name: op.name,
        code: op.code,
        category: op.category,
        is_active: val('is_active').checked,
        api_provider: val('api_provider').value,
        min_comm: val('min_comm').value,
        max_comm: val('max_comm').value,
        min_amount: val('min_amount').value,
        max_amount: val('max_amount').value,
    };
}

async function saveRow(id) {
    const res = await apiFetch(`${OP_API}/${id}`, {
        method: 'PUT',
        body: JSON.stringify(rowPayload(id)),
    });
    const json = await res.json().catch(() => ({}));
    if (!res.ok) {
        alert(json.message || 'Operator update failed.');
        return;
    }
    showToast(json.message || 'Operator updated.');
    await loadOperators();
}

function openOperatorModal() {
    document.getElementById('new-name').value = '';
    document.getElementById('new-code').value = '';
    document.getElementById('new-category').value = 'mobile';
    document.getElementById('new-min-comm').value = '0';
    document.getElementById('new-max-comm').value = '0';
    document.getElementById('new-min-amt').value = '10';
    document.getElementById('new-max-amt').value = '100000';
    fillApiSelect(document.getElementById('new-api'));
    document.getElementById('op-modal-error').style.display = 'none';
    document.getElementById('op-modal').classList.add('open');
}

function closeOperatorModal() {
    document.getElementById('op-modal').classList.remove('open');
}

async function addOperator() {
    const body = {
        name: document.getElementById('new-name').value.trim(),
        code: document.getElementById('new-code').value.trim(),
        category: document.getElementById('new-category').value,
        is_active: true,
        api_provider: document.getElementById('new-api').value,
        min_comm: document.getElementById('new-min-comm').value,
        max_comm: document.getElementById('new-max-comm').value,
        min_amount: document.getElementById('new-min-amt').value,
        max_amount: document.getElementById('new-max-amt').value,
    };

    if (!body.name || !body.code) {
        showModalError('Operator name and OP code are required.');
        return;
    }

    const btn = document.getElementById('op-save-btn');
    btn.disabled = true;
    btn.textContent = 'Saving...';
    try {
        const res = await apiFetch(OP_API, {method:'POST', body:JSON.stringify(body)});
        const json = await res.json().catch(() => ({}));
        if (!res.ok) {
            showModalError(json.message || 'Operator save failed.');
            return;
        }
        closeOperatorModal();
        showToast(json.message || 'Operator added.');
        await loadOperators();
    } finally {
        btn.disabled = false;
        btn.textContent = 'Save';
    }
}

function showModalError(message) {
    const el = document.getElementById('op-modal-error');
    el.textContent = message;
    el.style.display = 'block';
}

function showToast(message) {
    const t = document.createElement('div');
    t.textContent = message;
    Object.assign(t.style, {position:'fixed',right:'22px',bottom:'22px',background:'#166534',color:'#fff',padding:'10px 16px',borderRadius:'8px',fontWeight:'700',fontSize:'13px',zIndex:9999});
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 2500);
}

document.getElementById('op-modal').addEventListener('click', e => {
    if (e.target.id === 'op-modal') closeOperatorModal();
});
document.addEventListener('DOMContentLoaded', loadOperators);
</script>
@endpush
