@extends('layouts.admin')

@section('title', 'API Configuration')
@section('page-title', 'API Configuration')

@section('content')

<style>
/* ── Page layout ── */
.api-search-bar{display:flex;gap:10px;align-items:center;margin-bottom:16px}
.api-search-bar input{border:1px solid var(--border);border-radius:8px;padding:9px 14px;font-size:13px;color:var(--text-primary);background:var(--card-bg);outline:none;width:280px}
.api-search-bar input:focus{border-color:var(--primary)}

/* ── Remark bar ── */
.remark-bar{display:flex;align-items:center;gap:10px;margin-bottom:16px;padding:10px 16px;background:var(--card-bg);border:1px solid var(--border);border-radius:var(--radius);font-size:13px;color:var(--text-secondary)}
.remark-bar input{border:1px solid var(--border);border-radius:6px;padding:6px 12px;font-size:13px;color:var(--text-primary);background:var(--bg-page);width:280px;outline:none}
.remark-bar input:focus{border-color:var(--primary)}

/* ── Table ── */
#api-table thead tr{background:#1565c0;color:#fff}
#api-table thead th{padding:10px 12px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;white-space:nowrap;border:none}
#api-table tbody tr{border-bottom:1px solid var(--border)}
#api-table tbody tr:hover{background:var(--bg-page)}
#api-table tbody td{padding:10px 12px;font-size:12.5px;color:var(--text-primary);vertical-align:middle;white-space:nowrap}

/* ── Toggle switch ── */
.tog-wrap{display:inline-flex;align-items:center;cursor:pointer;gap:0}
.tog-track{position:relative;width:56px;height:26px;border-radius:13px;transition:background .2s;display:flex;align-items:center}
.tog-track.on{background:#1565c0}
.tog-track.off{background:#9e9e9e}
.tog-knob{position:absolute;width:20px;height:20px;border-radius:50%;background:#fff;top:3px;transition:left .2s;box-shadow:0 1px 3px rgba(0,0,0,.3)}
.tog-track.on .tog-knob{left:33px}
.tog-track.off .tog-knob{left:3px}
.tog-label{font-size:11px;font-weight:700;color:#fff;padding:0 6px;pointer-events:none;user-select:none}
.tog-track.on .tog-label.on-lbl{display:block}
.tog-track.on .tog-label.off-lbl{display:none}
.tog-track.off .tog-label.on-lbl{display:none}
.tog-track.off .tog-label.off-lbl{display:block}

/* ── Status badge ── */
.st-badge{display:inline-block;padding:3px 12px;border-radius:4px;font-size:12px;font-weight:700;cursor:pointer;border:none;color:#fff}
.st-badge.on{background:#2e7d32}
.st-badge.off{background:#c62828}

/* ── Action buttons ── */
.btn-api{display:inline-flex;align-items:center;padding:4px 12px;border-radius:4px;font-size:12px;font-weight:600;border:none;cursor:pointer;color:#fff}
.btn-api.blue{background:#1565c0}
.btn-api.blue:hover{background:#0d47a1}
.btn-api.green{background:#2e7d32}
.btn-api.green:hover{background:#1b5e20}
.btn-api.red{background:#c62828}
.btn-api.red:hover{background:#b71c1c}
.btn-api.grey{background:#616161}
.btn-api.grey:hover{background:#424242}

/* ── Modal ── */
.api-modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:300;align-items:center;justify-content:center}
.api-modal-overlay.open{display:flex}
.api-modal{background:var(--card-bg);border-radius:var(--radius);width:560px;max-width:95vw;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.3)}
.api-modal-head{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border)}
.api-modal-head h3{font-size:15px;font-weight:700;color:var(--text-primary)}
.api-modal-body{padding:20px}
.api-modal-body .field{margin-bottom:14px}
.api-modal-body label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;color:var(--text-secondary);margin-bottom:5px;letter-spacing:.4px}
.api-modal-body input,.api-modal-body select,.api-modal-body textarea{width:100%;border:1px solid var(--border);border-radius:8px;padding:9px 12px;font-size:13px;color:var(--text-primary);background:var(--bg-page);outline:none;box-sizing:border-box}
.api-modal-body input:focus,.api-modal-body select:focus,.api-modal-body textarea:focus{border-color:var(--primary)}
.api-modal-body textarea{resize:vertical;min-height:70px;font-family:monospace}
.api-modal-footer{padding:14px 20px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:10px}

/* ── Config panel (wide) ── */
.cfg-modal .api-modal{width:700px}
.cfg-banner{background:#c62828;color:#fff;padding:12px 20px;font-size:14px;font-weight:700;border-radius:var(--radius) var(--radius) 0 0}
.cfg-info-box{margin:16px 20px;border:1.5px solid #2e7d32;border-radius:8px;padding:12px 16px;font-size:12px;color:var(--text-primary);background:var(--bg-page)}
.cfg-info-box .info-row{display:flex;align-items:center;gap:8px;margin-bottom:6px}
.cfg-info-box .info-row:last-child{margin-bottom:0}
.cfg-info-box label{font-weight:700;min-width:110px;font-size:12px;color:var(--text-secondary)}
.cfg-info-box input[readonly]{border:none;background:transparent;color:var(--text-primary);font-size:12px;flex:1;padding:0;outline:none;cursor:default}
.cfg-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px 16px}
.cfg-section-title{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin:16px 0 8px;grid-column:1/-1;border-bottom:1px solid var(--border);padding-bottom:6px}

/* ── Margin modal ── */
.margin-modal .api-modal{width:360px}

html[data-dark="1"] .api-modal-body input,
html[data-dark="1"] .api-modal-body select,
html[data-dark="1"] .api-modal-body textarea{background:var(--card-bg)}
html[data-dark="1"] #api-table thead tr{background:#0d47a1}
html[data-dark="1"] .cfg-info-box input[readonly]{color:var(--text-primary)}
</style>

{{-- Header --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
    <div>
        <h1 style="font-size:20px;font-weight:700;color:var(--text-primary)">API Configuration</h1>
        <div class="breadcrumb" style="margin-bottom:0;margin-top:3px">
            <a href="/admin/dashboard">Dashboard</a>
            <svg class="breadcrumb-sep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span>Developers Options</span>
            <svg class="breadcrumb-sep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span>API Configuration</span>
        </div>
    </div>
</div>

{{-- Search bar --}}
<div class="api-search-bar">
    <input type="text" id="api-search" placeholder="Search…" oninput="filterApiTable()">
</div>

{{-- Remark bar --}}
<div class="remark-bar">
    <span>Enter Api Disable Remark</span>
    <input type="text" id="disable-remark" placeholder="Remark for disabling API…">
</div>

{{-- API List card --}}
<div class="card">
    <div class="card-header" style="justify-content:space-between">
        <div class="card-title">API LIST</div>
        <button class="btn btn-primary btn-sm" onclick="openAddModal()">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            ADD API
        </button>
    </div>
    <div class="card-body" style="padding:0">
        <div id="api-loading" class="loading-overlay"><div class="spinner"></div> Loading API providers…</div>
        <div id="api-table-wrap" class="table-wrap" style="display:none">
            <table id="api-table">
                <thead>
                    <tr>
                        <th>APIID</th>
                        <th>NAME</th>
                        <th>BALANCE</th>
                        <th>APIPARTNER</th>
                        <th>ADMINSTATUS</th>
                        <th>STATUS</th>
                        <th>API CONFIG</th>
                        <th>MARGIN</th>
                        <th>VALIDITY TILL</th>
                        <th>PURCHASE</th>
                        <th>AUTO RENEWAL</th>
                        <th>ACTION</th>
                    </tr>
                </thead>
                <tbody id="api-tbody"></tbody>
            </table>
        </div>
    </div>
</div>

{{-- ── ADD / EDIT modal ──────────────────────────────────────────────────── --}}
<div id="add-modal" class="api-modal-overlay">
    <div class="api-modal">
        <div class="api-modal-head">
            <h3 id="add-modal-title">Add API Provider</h3>
            <button onclick="closeAddModal()" style="background:none;border:none;cursor:pointer;color:var(--text-muted)">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:18px;height:18px"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="api-modal-body">
            <input type="hidden" id="add-id">
            <div class="field">
                <label>API Name *</label>
                <input type="text" id="add-name" placeholder="e.g. SWORLD">
            </div>
            <div class="field">
                <label>API Partner / Company *</label>
                <input type="text" id="add-provider" placeholder="e.g. Pdrs For Tradgo">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="field">
                    <label>Operator Code *</label>
                    <input type="text" id="add-opcode" placeholder="e.g. JIO">
                </div>
                <div class="field">
                    <label>Recharge Type *</label>
                    <select id="add-rtype">
                        <option value="prepaid">Prepaid</option>
                        <option value="postpaid">Postpaid</option>
                        <option value="dth">DTH</option>
                        <option value="bbps">BBPS</option>
                        <option value="fastag">Fastag</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>
            <div class="field">
                <label>Validity Till</label>
                <input type="date" id="add-validity">
            </div>
            <div class="field">
                <label>Purchase Status</label>
                <select id="add-purchase">
                    <option value="active">Active</option>
                    <option value="deactive">Deactive</option>
                </select>
            </div>
        </div>
        <div class="api-modal-footer">
            <button class="btn btn-outline btn-sm" onclick="closeAddModal()">Cancel</button>
            <button class="btn btn-primary btn-sm" id="add-save-btn" onclick="saveApi()">Save</button>
        </div>
    </div>
</div>

{{-- ── API CONFIG (Settings) modal ──────────────────────────────────────── --}}
<div id="cfg-modal" class="api-modal-overlay cfg-modal">
    <div class="api-modal">
        <div class="cfg-banner" id="cfg-banner">API Settings</div>
        <div class="cfg-info-box">
            <div class="info-row">
                <label>Callback URL</label>
                <input type="text" id="cfg-callback-url" readonly>
            </div>
            <div class="info-row">
                <label>IP Address</label>
                <input type="text" id="cfg-ip" readonly value="{{ request()->server('SERVER_ADDR', '—') }}">
            </div>
        </div>
        <div class="api-modal-body">
            <input type="hidden" id="cfg-id">

            {{-- Credentials section --}}
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:10px;border-bottom:1px solid var(--border);padding-bottom:6px">API Credentials</div>
            <div class="cfg-grid" style="margin-bottom:4px">
                <div class="field">
                    <label>Username</label>
                    <input type="text" id="cfg-username" placeholder="Your registered mobile / username" autocomplete="off">
                </div>
                <div class="field">
                    <label>API Token</label>
                    <input type="password" id="cfg-apitoken" placeholder="API token / key" autocomplete="new-password">
                    <div style="font-size:11px;color:var(--text-muted);margin-top:3px">Used as <code>[apitoken]</code> and <code>[password]</code> in params</div>
                </div>
            </div>

            <div class="cfg-grid">
                <div class="field">
                    <label>Method *</label>
                    <select id="cfg-method">
                        <option value="GET">GET</option>
                        <option value="POST">POST</option>
                    </select>
                </div>
                <div class="field">
                    <label>Response Type *</label>
                    <select id="cfg-rtype" onchange="toggleSep()">
                        <option value="JSON">JSON</option>
                        <option value="XML">XML</option>
                        <option value="PIPE">PIPE</option>
                        <option value="STRING">STRING</option>
                    </select>
                </div>
                <div class="field" style="grid-column:1/-1">
                    <label>Website URL *</label>
                    <input type="url" id="cfg-url" placeholder="https://pdrs.online/API2/RechargeNew">
                </div>
                <div class="field" style="grid-column:1/-1">
                    <label>Request Parameters</label>
                    <textarea id="cfg-params" rows="3" placeholder="username=[username]&token=[apitoken]&number=[number]&opcode=[opcode]&amount=[amount]&transid=[transid]&circlecode=*"></textarea>
                    <div style="font-size:11px;color:var(--text-muted);margin-top:4px">Placeholders: <code>[username]</code> <code>[apitoken]</code> <code>[number]</code> <code>[amount]</code> <code>[opcode]</code> <code>[transid]</code> <code>[circlecode]</code></div>
                </div>
                <div class="field" id="sep-field">
                    <label>Separator</label>
                    <input type="text" id="cfg-sep" placeholder="|">
                </div>
            </div>

            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin:16px 0 8px;border-bottom:1px solid var(--border);padding-bottom:6px">Response Field Keys</div>
            <div class="cfg-grid">
                <div class="field">
                    <label>Status Key *</label>
                    <input type="text" id="cfg-status-key" placeholder="status">
                </div>
                <div class="field">
                    <label>API TxnId Key *</label>
                    <input type="text" id="cfg-txnid-key" placeholder="txnid">
                </div>
                <div class="field">
                    <label>Live Id Key</label>
                    <input type="text" id="cfg-live-id-key" placeholder="liveid">
                </div>
                <div class="field">
                    <label>Balance Key</label>
                    <input type="text" id="cfg-balance-key" placeholder="balance">
                </div>
            </div>

            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin:16px 0 8px;border-bottom:1px solid var(--border);padding-bottom:6px">Status Values</div>
            <div class="cfg-grid">
                <div class="field">
                    <label>Success Value *</label>
                    <input type="text" id="cfg-success" placeholder="SUCCESS">
                </div>
                <div class="field">
                    <label>Pending Value</label>
                    <input type="text" id="cfg-pending" placeholder="PENDING">
                </div>
                <div class="field">
                    <label>Failure Value *</label>
                    <input type="text" id="cfg-failure" placeholder="FAILED">
                </div>
            </div>
        </div>
        <div class="api-modal-footer">
            <button class="btn btn-outline btn-sm" onclick="closeCfgModal()">Cancel</button>
            <button class="btn btn-primary btn-sm" id="cfg-save-btn" onclick="saveCfg()">Update</button>
        </div>
    </div>
</div>

{{-- ── Margin modal ──────────────────────────────────────────────────────── --}}
<div id="margin-modal" class="api-modal-overlay margin-modal">
    <div class="api-modal">
        <div class="api-modal-head">
            <h3>Set Margin</h3>
            <button onclick="closeMarginModal()" style="background:none;border:none;cursor:pointer;color:var(--text-muted)">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:18px;height:18px"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="api-modal-body">
            <input type="hidden" id="margin-id">
            <div class="field">
                <label>Margin % *</label>
                <input type="number" id="margin-val" min="0" max="100" step="0.01" placeholder="0.00">
            </div>
        </div>
        <div class="api-modal-footer">
            <button class="btn btn-outline btn-sm" onclick="closeMarginModal()">Cancel</button>
            <button class="btn btn-primary btn-sm" onclick="saveMargin()">Save Margin</button>
        </div>
    </div>
</div>

<script>
const API_BASE = '/api/v1/employee';
let _token = () => localStorage.getItem('emp_token') || '';
let _allRoutes = [];
let _disableRemark = '';

// ── Load ─────────────────────────────────────────────────────────────────────
async function loadProviders() {
    document.getElementById('api-loading').style.display = 'flex';
    document.getElementById('api-table-wrap').style.display = 'none';
    try {
        const r = await fetch(`${API_BASE}/api-providers`, {headers:{'Authorization':'Bearer '+_token(),'Accept':'application/json'}});
        const d = await r.json();
        _allRoutes = d.routes || [];
        renderTable(_allRoutes);
    } catch(e) {
        alert('Failed to load API providers.');
    } finally {
        document.getElementById('api-loading').style.display = 'none';
        document.getElementById('api-table-wrap').style.display = '';
    }
}

function renderTable(rows) {
    const tbody = document.getElementById('api-tbody');
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="12" style="text-align:center;color:var(--text-muted);padding:32px">No API providers found.</td></tr>';
        return;
    }
    tbody.innerHTML = rows.map(r => `
        <tr id="row-${r.id}">
            <td style="font-weight:700;color:#1565c0">${r.api_id}</td>
            <td style="font-weight:600">${esc(r.name)}</td>
            <td style="color:var(--text-muted)">${r.balance !== '' ? '₹' + r.balance : '—'}</td>
            <td>${esc(r.api_provider)}</td>
            <td>${toggleHtml(r.id, 'admin', r.is_active)}</td>
            <td>${statusBadge(r.id, r.api_status)}</td>
            <td><button class="btn-api blue" onclick="openCfgModal(${r.id})">Setting</button></td>
            <td><button class="btn-api blue" onclick="openMarginModal(${r.id}, ${r.margin})">Margin</button></td>
            <td>${esc(r.validity_till)}</td>
            <td><span style="font-size:12px;font-weight:600;color:${r.purchase === 'active' ? '#2e7d32' : '#9e9e9e'}">${r.purchase}</span></td>
            <td>${toggleHtml(r.id, 'renewal', r.auto_renewal)}</td>
            <td style="display:flex;gap:6px;align-items:center">
                <button class="btn-api green" onclick="checkBalance(${r.id},this)" title="Fetch live balance from PDRS">Bal</button>
                <button class="btn-api grey" onclick="openEditModal(${r.id})">Update</button>
                <span style="color:var(--border)">|</span>
                <button class="btn-api red" onclick="deleteProvider(${r.id}, '${esc(r.name)}')">Delete</button>
            </td>
        </tr>
    `).join('');
}

function toggleHtml(id, field, active) {
    const cls = active ? 'on' : 'off';
    return `<span class="tog-wrap" onclick="doToggle(${id},'${field}',this)">
        <span class="tog-track ${cls}">
            <span class="tog-label on-lbl">on</span>
            <span class="tog-label off-lbl">off</span>
            <span class="tog-knob"></span>
        </span>
    </span>`;
}

function statusBadge(id, active) {
    const cls  = active ? 'on' : 'off';
    const lbl  = active ? 'on' : 'off';
    return `<button class="st-badge ${cls}" onclick="doToggle(${id},'api',null,this)" data-id="${id}">${lbl}</button>`;
}

function filterApiTable() {
    const q = document.getElementById('api-search').value.toLowerCase();
    const filtered = _allRoutes.filter(r =>
        r.name.toLowerCase().includes(q) ||
        r.api_provider.toLowerCase().includes(q) ||
        r.api_id.toLowerCase().includes(q)
    );
    renderTable(filtered);
}

// ── Toggle ───────────────────────────────────────────────────────────────────
async function doToggle(id, field, togEl, badgeEl) {
    const remark = field === 'api' ? document.getElementById('disable-remark').value : '';
    try {
        const r = await fetch(`${API_BASE}/api-providers/${id}/toggle`, {
            method:'PATCH',
            headers:{'Authorization':'Bearer '+_token(),'Content-Type':'application/json','Accept':'application/json'},
            body: JSON.stringify({field, remark}),
        });
        const d = await r.json();
        if (!r.ok) { alert(d.message || 'Failed.'); return; }

        const route = _allRoutes.find(x => x.id === id);
        if (!route) return;

        if (field === 'admin') {
            route.is_active = d.value;
            if (togEl) {
                const track = togEl.querySelector('.tog-track');
                track.className = 'tog-track ' + (d.value ? 'on' : 'off');
            }
        } else if (field === 'api') {
            route.api_status = d.value;
            if (badgeEl) {
                badgeEl.className = 'st-badge ' + (d.value ? 'on' : 'off');
                badgeEl.textContent = d.value ? 'on' : 'off';
            }
        } else if (field === 'renewal') {
            route.auto_renewal = d.value;
            if (togEl) {
                const track = togEl.querySelector('.tog-track');
                track.className = 'tog-track ' + (d.value ? 'on' : 'off');
            }
        }
    } catch(e) { alert('Toggle failed.'); }
}

// ── Add / Edit modal ─────────────────────────────────────────────────────────
function openAddModal() {
    document.getElementById('add-id').value = '';
    document.getElementById('add-modal-title').textContent = 'Add API Provider';
    document.getElementById('add-name').value = '';
    document.getElementById('add-provider').value = '';
    document.getElementById('add-opcode').value = '';
    document.getElementById('add-rtype').value = 'prepaid';
    document.getElementById('add-validity').value = '';
    document.getElementById('add-purchase').value = 'active';
    document.getElementById('add-modal').classList.add('open');
}

function openEditModal(id) {
    const r = _allRoutes.find(x => x.id === id);
    if (!r) return;
    document.getElementById('add-id').value = id;
    document.getElementById('add-modal-title').textContent = 'Update API Provider';
    document.getElementById('add-name').value = r.name;
    document.getElementById('add-provider').value = r.api_provider;
    document.getElementById('add-opcode').value = r.operator_code || '';
    document.getElementById('add-rtype').value = r.recharge_type || 'prepaid';
    document.getElementById('add-validity').value = r.validity_till !== '0000-00-00' ? r.validity_till : '';
    document.getElementById('add-purchase').value = r.purchase || 'active';
    document.getElementById('add-modal').classList.add('open');
}

function closeAddModal() { document.getElementById('add-modal').classList.remove('open'); }

async function saveApi() {
    const id       = document.getElementById('add-id').value;
    const isEdit   = !!id;
    const body = {
        name:          document.getElementById('add-name').value.trim(),
        api_provider:  document.getElementById('add-provider').value.trim(),
        operator_code: document.getElementById('add-opcode').value.trim(),
        recharge_type: document.getElementById('add-rtype').value,
        validity_till: document.getElementById('add-validity').value || null,
        purchase:      document.getElementById('add-purchase').value,
    };
    if (!body.name || !body.operator_code) { alert('Name and Operator Code are required.'); return; }

    const btn = document.getElementById('add-save-btn');
    btn.disabled = true; btn.textContent = 'Saving…';
    try {
        const url    = isEdit ? `${API_BASE}/api-providers/${id}/basic` : `${API_BASE}/api-providers`;
        const method = isEdit ? 'PUT' : 'POST';
        const r = await fetch(url, {method, headers:{'Authorization':'Bearer '+_token(),'Content-Type':'application/json','Accept':'application/json'}, body: JSON.stringify(body)});
        const d = await r.json();
        if (!r.ok) { alert(d.message || 'Failed.'); return; }
        closeAddModal();
        await loadProviders();
    } catch(e) { alert('Request failed.'); }
    finally { btn.disabled = false; btn.textContent = isEdit ? 'Update' : 'Save'; }
}

// ── Delete ───────────────────────────────────────────────────────────────────
async function deleteProvider(id, name) {
    if (!confirm(`Delete API provider "${name}"? This cannot be undone.`)) return;
    try {
        const r = await fetch(`${API_BASE}/api-providers/${id}`, {method:'DELETE', headers:{'Authorization':'Bearer '+_token(),'Accept':'application/json'}});
        const d = await r.json();
        if (!r.ok) { alert(d.message || 'Delete failed.'); return; }
        _allRoutes = _allRoutes.filter(x => x.id !== id);
        renderTable(_allRoutes);
    } catch(e) { alert('Delete failed.'); }
}

// ── Config modal ─────────────────────────────────────────────────────────────
async function openCfgModal(id) {
    document.getElementById('cfg-id').value = id;
    const r2 = _allRoutes.find(x => x.id === id);
    document.getElementById('cfg-banner').textContent = (r2 ? r2.name : 'API') + ' — Settings';
    document.getElementById('cfg-callback-url').value = `${location.origin}/api/v1/recharge/callback?api_id=${id}`;
    document.getElementById('cfg-modal').classList.add('open');

    try {
        const r = await fetch(`${API_BASE}/operator-routes/${id}/api-setting`, {headers:{'Authorization':'Bearer '+_token(),'Accept':'application/json'}});
        const d = await r.json();
        if (!r.ok) return;
        const c = d.config;
        document.getElementById('cfg-username').value   = c.username || '';
        document.getElementById('cfg-apitoken').value   = '';           // never pre-fill passwords
        document.getElementById('cfg-method').value     = c.method;
        document.getElementById('cfg-url').value        = d.route.api_endpoint;
        document.getElementById('cfg-params').value     = c.request_params;
        document.getElementById('cfg-rtype').value      = c.response_type;
        document.getElementById('cfg-sep').value        = c.separator;
        document.getElementById('cfg-status-key').value   = c.status_key;
        document.getElementById('cfg-txnid-key').value    = c.txnid_key;
        document.getElementById('cfg-live-id-key').value  = c.live_id_key;
        document.getElementById('cfg-balance-key').value  = c.balance_key;
        document.getElementById('cfg-success').value    = c.success_val;
        document.getElementById('cfg-pending').value    = c.pending_val;
        document.getElementById('cfg-failure').value    = c.failure_val;
        toggleSep();
    } catch(e) {}
}

function closeCfgModal() { document.getElementById('cfg-modal').classList.remove('open'); }

function toggleSep() {
    const v = document.getElementById('cfg-rtype').value;
    document.getElementById('sep-field').style.display = (v === 'PIPE' || v === 'STRING') ? '' : 'none';
}

async function saveCfg() {
    const id  = document.getElementById('cfg-id').value;
    const tokenVal = document.getElementById('cfg-apitoken').value;
    const body = {
        api_endpoint:   document.getElementById('cfg-url').value.trim(),
        method:         document.getElementById('cfg-method').value,
        request_params: document.getElementById('cfg-params').value,
        response_type:  document.getElementById('cfg-rtype').value,
        separator:      document.getElementById('cfg-sep').value,
        username:       document.getElementById('cfg-username').value.trim(),
        api_token:      tokenVal || undefined,   // only send if changed
        status_key:     document.getElementById('cfg-status-key').value.trim(),
        txnid_key:      document.getElementById('cfg-txnid-key').value.trim(),
        live_id_key:    document.getElementById('cfg-live-id-key').value.trim(),
        balance_key:    document.getElementById('cfg-balance-key').value.trim(),
        success_val:    document.getElementById('cfg-success').value.trim(),
        pending_val:    document.getElementById('cfg-pending').value.trim(),
        failure_val:    document.getElementById('cfg-failure').value.trim(),
    };
    if (!body.api_token) delete body.api_token;   // don't overwrite with empty string
    if (!body.api_endpoint || !body.status_key || !body.txnid_key) {
        alert('URL, Status Key, and TxnId Key are required.');
        return;
    }
    const btn = document.getElementById('cfg-save-btn');
    btn.disabled = true; btn.textContent = 'Saving…';
    try {
        const r = await fetch(`${API_BASE}/operator-routes/${id}/api-setting`, {
            method:'PUT', headers:{'Authorization':'Bearer '+_token(),'Content-Type':'application/json','Accept':'application/json'}, body: JSON.stringify(body)
        });
        const d = await r.json();
        if (!r.ok) { alert(d.message || 'Failed.'); return; }
        closeCfgModal();
        showToast('API settings saved.');
    } catch(e) { alert('Request failed.'); }
    finally { btn.disabled = false; btn.textContent = 'Update'; }
}

// ── Margin modal ─────────────────────────────────────────────────────────────
function openMarginModal(id, margin) {
    document.getElementById('margin-id').value = id;
    document.getElementById('margin-val').value = margin;
    document.getElementById('margin-modal').classList.add('open');
}
function closeMarginModal() { document.getElementById('margin-modal').classList.remove('open'); }

async function saveMargin() {
    const id  = document.getElementById('margin-id').value;
    const val = document.getElementById('margin-val').value;
    try {
        const r = await fetch(`${API_BASE}/operator-routes/${id}/margin`, {
            method:'PUT', headers:{'Authorization':'Bearer '+_token(),'Content-Type':'application/json','Accept':'application/json'}, body: JSON.stringify({margin: val})
        });
        const d = await r.json();
        if (!r.ok) { alert(d.message || 'Failed.'); return; }
        const route = _allRoutes.find(x => x.id === parseInt(id));
        if (route) route.margin = parseFloat(val);
        closeMarginModal();
        showToast('Margin updated.');
    } catch(e) { alert('Request failed.'); }
}

// ── Balance check ─────────────────────────────────────────────────────────────
async function checkBalance(id, btn) {
    const orig = btn.textContent;
    btn.disabled = true; btn.textContent = '…';
    try {
        const r = await fetch(`${API_BASE}/pdrs/${id}/balance`, {headers:{'Authorization':'Bearer '+_token(),'Accept':'application/json'}});
        const d = await r.json();
        if (!r.ok) { showToast((d.message || 'Balance check failed.'), true); return; }
        const route = _allRoutes.find(x => x.id === id);
        if (route) {
            route.balance = d.balance;
            const row = document.getElementById('row-' + id);
            if (row) row.cells[2].textContent = '₹' + d.balance;
        }
        showToast(`Balance: ₹${d.balance}`);
    } catch(e) { showToast('Request failed.', true); }
    finally { btn.disabled = false; btn.textContent = orig; }
}

// ── Helpers ──────────────────────────────────────────────────────────────────
function esc(s) { return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

function showToast(msg, isError = false) {
    const t = document.createElement('div');
    t.textContent = msg;
    Object.assign(t.style, {position:'fixed',bottom:'24px',right:'24px',background: isError ? '#c62828' : '#1b5e20',color:'#fff',padding:'10px 20px',borderRadius:'8px',fontWeight:'600',fontSize:'13px',zIndex:9999,boxShadow:'0 4px 16px rgba(0,0,0,.2)'});
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3500);
}

// Close modals on overlay click
['add-modal','cfg-modal','margin-modal'].forEach(id => {
    document.getElementById(id).addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('open');
    });
});

// Initial load
toggleSep();
loadProviders();
</script>

@endsection
