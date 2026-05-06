@extends('layouts.admin')
@section('title', 'Pending Recharge Report')
@section('page-title', 'Pending Recharge Report')

@section('content')
<style>
.rr-card{background:#fff;border:1px solid var(--border);border-radius:4px;margin-bottom:18px}
.rr-head{padding:13px 14px;border-bottom:1px solid var(--border);font-size:12px;font-weight:800;text-transform:uppercase;color:#111827}
.rr-filters{display:grid;grid-template-columns:100px 100px 110px 120px 130px 130px 120px 120px auto auto;gap:12px;align-items:end;padding:20px 10px 22px}
.rr-field label{display:block;font-size:16px;font-weight:700;color:#858da8;margin-bottom:8px}
.rr-field input,.rr-field select{width:100%;height:28px;border:1px solid #444;padding:3px 8px;font-size:12px;background:#fff}
.rr-blue{background:#0068ce;color:#fff;border:none;border-radius:4px;padding:7px 12px;font-weight:700;cursor:pointer}
.rr-green{background:#10b900;color:#fff;border:none;border-radius:4px;padding:7px 12px;font-weight:700;cursor:pointer}
.rr-table-wrap{overflow:auto;padding:0 6px 14px}
.rr-table{width:100%;border-collapse:collapse;min-width:1500px}
.rr-table th{font-size:13px;font-weight:800;text-align:left;padding:14px 12px;border-top:1px solid #d8dde6;border-bottom:1px solid #d8dde6;color:#111827}
.rr-table td{font-size:14px;vertical-align:top;padding:10px 8px;border-bottom:1px solid #d8dde6;color:#000}
.rr-id{color:#005fd1;font-weight:700;cursor:pointer;line-height:1.5}
.rr-agent{color:#2563dc;font-size:15px;font-weight:800}
.rr-status-badge{font-weight:800;padding:2px 6px;border-radius:3px;cursor:pointer;display:inline-block}
.rr-status-pending{background:#f59e0b;color:#fff}
.rr-status-queued{background:#0ea5e9;color:#fff}
.rr-status-processing{background:#8b5cf6;color:#fff}
.rr-status-success{background:#16a34a;color:#fff}
.rr-status-failed{background:#dc2626;color:#fff}
.rr-actions{display:flex;flex-direction:column;gap:3px;width:80px}
.rr-actions button{border:none;color:#fff;font-size:12px;font-weight:800;padding:6px 6px;border-radius:4px;cursor:pointer}
.rr-a-blue{background:#0068ce}
.rr-a-orange{background:#f59e0b}
.rr-inline-wrap{display:flex;flex-direction:column;gap:3px;min-width:130px}
.rr-inline-wrap input{height:26px;border:1px solid #aaa;padding:2px 6px;font-size:12px;width:100%}
.rr-inline-wrap select{height:26px;border:1px solid #aaa;padding:2px 4px;font-size:11px;width:100%}
.rr-inline-wrap button{background:#0068ce;color:#fff;border:none;font-size:11px;font-weight:800;padding:4px 0;border-radius:3px;cursor:pointer}
.rr-modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,.35);z-index:700;align-items:center;justify-content:center}
.rr-box{background:#f4f4f4;min-width:560px;max-width:96vw}
.rr-box-head{display:flex;align-items:center;justify-content:space-between;padding:12px;background:#fff;color:#858da8;font-size:20px;font-weight:800}
.rr-box-body{padding:20px 12px}
.rr-box-foot{display:flex;justify-content:flex-end;gap:6px;padding:16px 12px;background:#fff}
.rr-box textarea{width:400px;height:48px}
.rr-box select{height:31px}
.rr-box-foot button{border:none;color:#fff;border-radius:4px;padding:8px 13px;font-weight:800;cursor:pointer}
.rr-log .rr-box{width:1200px;max-width:98vw;max-height:88vh;overflow:auto;background:#fff}
.rr-log .rr-box-head{background:#1a2035;color:#fff}
.rr-log table{width:100%;border-collapse:collapse}
.rr-log th,.rr-log td{border-top:1px solid #d8dde6;padding:14px 12px;vertical-align:top;text-align:left}
.rr-log pre{white-space:pre-wrap;font-family:inherit;margin:0}
.rr-pager{display:flex;justify-content:space-between;align-items:center;padding:10px 14px;border-top:1px solid #d8dde6;font-size:12.5px;color:#64748b}
@media(max-width:1200px){.rr-filters{grid-template-columns:repeat(3,1fr)}}
</style>

{{-- Pending count badge --}}
<div style="margin-bottom:12px">
    <button style="background:#f59e0b;color:#fff;border:none;border-radius:4px;padding:7px 16px;font-weight:800;font-size:14px;cursor:default">
        Pending : <span id="pending-count">—</span>
    </button>
</div>

{{-- Search Filters --}}
<div class="rr-card">
    <div class="rr-head">Search Filters</div>
    <div class="rr-filters">
        <div class="rr-field"><label>From Date</label><input id="f-from" type="date"></div>
        <div class="rr-field"><label>To Date</label><input id="f-to" type="date"></div>
        <div class="rr-field"><label>USER</label><input id="f-user" type="text" placeholder="type username"></div>
        <div class="rr-field"><label>Status</label>
            <select id="f-status">
                <option value="">ALL</option>
                <option value="pending">Pending</option>
                <option value="queued">Queued</option>
                <option value="processing">Processing</option>
            </select>
        </div>
        <div class="rr-field"><label>Operator</label><select id="f-operator"><option value="">ALL</option></select></div>
        <div class="rr-field"><label>Number / Id</label><input id="f-search" type="text"></div>
        <div class="rr-field"><label>Data</label>
            <select id="f-data">
                <option value="all">ALL</option>
                <option value="live">LIVE (Today)</option>
                <option value="custom">Custom Date</option>
            </select>
        </div>
        <button class="rr-blue" onclick="loadReport()">Submit</button>
        <button class="rr-green" onclick="exportReport()">Export</button>
    </div>
</div>

{{-- Report Table --}}
<div class="rr-card">
    <div class="rr-head">Pending Recharge Report</div>
    <div style="padding:22px 6px 6px;color:#005fd1;font-size:13px"><span id="rr-count">0</span>&gt;</div>
    <div class="rr-table-wrap">
        <table class="rr-table">
            <thead>
                <tr>
                    <th>Rec.Id</th>
                    <th>Rec.<br>Date</th>
                    <th>Agent Name</th>
                    <th>opcode</th>
                    <th>Mobile No</th>
                    <th>Amt</th>
                    <th>otf</th>
                    <th>Status</th>
                    <th>API</th>
                    <th>OperatorId</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="rr-body">
                <tr><td colspan="11" style="text-align:center;padding:30px;color:#777">Loading...</td></tr>
            </tbody>
        </table>
    </div>
    <div class="rr-pager" id="rr-pager"></div>
</div>

{{-- Action Modal --}}
<div id="action-modal" class="rr-modal">
    <div class="rr-box">
        <div class="rr-box-head">
            <span>Action</span>
            <button onclick="closeAction()" style="border:none;background:none;font-size:18px;color:#777;cursor:pointer">×</button>
        </div>
        <div class="rr-box-body">
            <p>Username : <span id="am-user"></span></p>
            <p>Operator : <span id="am-op"></span></p>
            <p>Mobile Number : <span id="am-mobile"></span></p>
            <p>Amount : <span id="am-amount"></span></p>
            <p>Remarks or OperatorId or Transaction Id :</p>
            <textarea id="am-remarks"></textarea>
            <p>Recharge Ip: <span id="am-ip" style="font-size:12px;color:#64748b"></span></p>
        </div>
        <div class="rr-box-foot">
            <button style="background:#e83e58" onclick="doAction('refund')">Refund</button>
            <button style="background:#f59e0b" onclick="doAction('status')">Status</button>
            <button style="background:#0068ce" onclick="doAction('success')">Success</button>
            <button style="background:#f59e0b" onclick="doAction('resend')">Resend</button>
        </div>
    </div>
</div>

{{-- Log / Response Modal --}}
<div id="log-modal" class="rr-modal rr-log">
    <div class="rr-box">
        <div class="rr-box-head">
            <span id="log-title">Request/Response Log</span>
            <button onclick="closeLog()" style="border:none;background:none;color:#fff;font-size:18px;cursor:pointer">×</button>
        </div>
        <div style="padding:16px 14px">
            <table>
                <thead>
                    <tr>
                        <th>LogId</th><th>Type</th><th>Label</th><th>api</th><th>RechargeId</th>
                        <th>DateTime</th><th>Request</th><th>Response</th>
                    </tr>
                </thead>
                <tbody id="log-body"></tbody>
            </table>
        </div>
    </div>
</div>

{{-- Live Status Modal --}}
<div id="status-modal" class="rr-modal">
    <div class="rr-box" style="min-width:420px;max-width:550px">
        <div class="rr-box-head" style="background:#0068ce;color:#fff">
            <span>Transaction Status</span>
            <button onclick="closeStatusModal()" style="border:none;background:none;color:#fff;font-size:18px;cursor:pointer">×</button>
        </div>
        <div class="rr-box-body" id="status-body" style="min-height:80px"></div>
        <div class="rr-box-foot">
            <button style="background:#64748b" onclick="closeStatusModal()">Close</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let rows = [], currentTxn = null;
function todayLocalISO(){
    const d = new Date();
    const y = d.getFullYear();
    const m = String(d.getMonth()+1).padStart(2,'0');
    const day = String(d.getDate()).padStart(2,'0');
    return `${y}-${m}-${day}`;
}
const today = todayLocalISO();
document.getElementById('f-from').value = today;
document.getElementById('f-to').value   = today;

function esc(s){ return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }
function jsonText(v){ if (!v) return ''; try { return JSON.stringify(typeof v === 'string' ? JSON.parse(v) : v, null, 2); } catch(e){ return String(v); } }

function params(){
    const p    = new URLSearchParams();
    const data = document.getElementById('f-data').value;
    const user   = document.getElementById('f-user').value.trim();
    const status = document.getElementById('f-status').value;
    const op     = document.getElementById('f-operator').value;
    const search = document.getElementById('f-search').value.trim();

    if (data === 'live') {
        p.set('date_from', today);
        p.set('date_to',   today);
    } else if (data === 'custom') {
        const from = document.getElementById('f-from').value;
        const to   = document.getElementById('f-to').value;
        if (from) p.set('date_from', from);
        if (to)   p.set('date_to',   to);
    }
    // data === 'all' → no date filter → shows ALL pending regardless of date

    if (user)   p.set('user_name', user);
    if (status) p.set('status', status);
    if (op)     p.set('operator_code', op);
    if (search) p.set('search', search);
    p.set('per_page', 25);
    return p;
}

async function bootFilters(){
    // Start report load immediately (don't block UI on dropdown APIs)
    loadReport(1);

    const opsRes = await apiFetch('/api/v1/employee/operator-settings');
    const ops    = opsRes ? await opsRes.json() : {};
    document.getElementById('f-operator').innerHTML =
        '<option value="">ALL</option>' +
        (ops.operators || []).map(o => `<option value="${esc(o.code)}">${esc(o.name)}</option>`).join('');
}

let currentPage = 1, lastMeta = {};

async function loadReport(page){
    currentPage = page || currentPage;
    const p = params();
    p.set('page', currentPage);

    document.getElementById('rr-body').innerHTML =
        '<tr><td colspan="11" style="text-align:center;padding:30px;color:#777">Loading...</td></tr>';

    const res  = await apiFetch('/api/v1/employee/reports/pending?' + p.toString());
    if (!res) return;
    const data = await res.json();

    const pagination = data.data || {};
    rows     = pagination.data || [];
    lastMeta = { total: pagination.total, last_page: pagination.last_page, from: pagination.from, to: pagination.to };

    const stats = data.stats || {};
    document.getElementById('pending-count').textContent = stats.total || 0;
    document.getElementById('rr-count').textContent      = pagination.total || rows.length;

    renderRows();
    renderPager(lastMeta);
}

function renderRows(){
    const body = document.getElementById('rr-body');
    if (!rows.length){
        body.innerHTML = '<tr><td colspan="11" style="text-align:center;padding:30px;color:#777">No pending transactions found.</td></tr>';
        return;
    }

    body.innerHTML = rows.map(r => {
        const d      = r.created_at ? new Date(r.created_at) : null;
        const dateStr = d ? d.toISOString().slice(0,10) : '';
        const timeStr = d ? d.toLocaleTimeString('en-IN', {hour12:false}) : '';
        const opcode  = esc((r.operator_code || '') + ' ' + (r.recharge_type || '')).trim();
        const statusCls = r.status === 'queued' ? 'rr-status-queued'
                        : r.status === 'processing' ? 'rr-status-processing'
                        : 'rr-status-pending';
        const apiName   = esc(r.api_provider || r.route_name || '—');
        const operRef   = esc(r.operator_ref || '');
        const rowId     = `row-${r.id}`;

        return `<tr id="${rowId}">
            <td><div class="rr-id" onclick="openLog(${r.id})">${r.id}</div></td>
            <td style="font-size:12px;white-space:nowrap">${dateStr}<br>${timeStr}</td>
            <td>
                <div class="rr-agent">${esc(r.seller_name || '—')}</div>
                <div style="font-size:11px;color:#94a3b8">${esc(r.seller_email || '')}</div>
            </td>
            <td>${opcode || '—'}</td>
            <td style="font-weight:700">${esc(r.mobile)}</td>
            <td style="font-weight:700">${Number(r.amount || 0).toFixed(2)}</td>
            <td style="color:#64748b">${Number(r.commission || 0).toFixed(2)}</td>
            <td>
                <span class="rr-status-badge ${statusCls}" onclick="checkStatus(${r.id})" title="Click to check live status">
                    ${esc(r.status || 'pending')}
                </span>
            </td>
            <td style="font-size:12px">${apiName}</td>
            <td>
                <div class="rr-inline-wrap">
                    <input type="text" id="oid-${r.id}" value="${operRef}" placeholder="OperatorId">
                    <select id="api-${r.id}">
                        <option value="">-- Select Action --</option>
                        <option value="__success">✓ Mark Success</option>
                        <option value="__failed">✗ Mark Failed</option>
                    </select>
                    <button onclick="doRowSend(${r.id})">Submit</button>
                </div>
            </td>
            <td>
                <div class="rr-actions">
                    <button class="rr-a-blue" onclick="openAction(${r.id})">Action</button>
                    <button class="rr-a-orange" onclick="openResponse(${r.id})">Response</button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

function renderPager(meta){
    const pager = document.getElementById('rr-pager');
    if (!meta.total) { pager.innerHTML = ''; return; }
    const lp = meta.last_page || 1;
    // Rendering every page button gets slow on large datasets.
    // Show a small window + first/last + prev/next.
    const win = 2; // buttons around current
    const start = Math.max(1, currentPage - win);
    const end   = Math.min(lp, currentPage + win);

    const btn = (i, label = i) =>
        `<button onclick="loadReport(${i})" style="padding:4px 9px;border-radius:4px;border:1.5px solid ${i===currentPage?'#0068ce':'#d1d5db'};background:${i===currentPage?'#0068ce':'#fff'};color:${i===currentPage?'#fff':'#374151'};font-size:12px;cursor:pointer;margin:0 2px">${label}</button>`;

    let btns = '';
    if (currentPage > 1) btns += btn(currentPage - 1, 'Prev');
    btns += btn(1, '1');
    if (start > 2) btns += `<span style="margin:0 6px;color:#94a3b8">…</span>`;
    for (let i = start; i <= end; i++){
        if (i === 1 || i === lp) continue;
        btns += btn(i);
    }
    if (end < lp - 1) btns += `<span style="margin:0 6px;color:#94a3b8">…</span>`;
    if (lp > 1) btns += btn(lp, String(lp));
    if (currentPage < lp) btns += btn(currentPage + 1, 'Next');

    pager.innerHTML = `<span>Showing ${meta.from || 0}–${meta.to || 0} of ${meta.total || 0}</span><div style="display:flex;flex-wrap:wrap;gap:2px;justify-content:flex-end">${btns}</div>`;
}

// ── Inline row Submit ────────────────────────────────────────────────────────
async function doRowSend(id){
    const operatorId = document.getElementById(`oid-${id}`).value.trim();
    const action     = document.getElementById(`api-${id}`).value;

    if (!action){ alert('Select an action (Success or Failed).'); return; }

    let url, body;

    if (action === '__success') {
        url  = `/api/v1/employee/recharges/${id}/success`;
        body = { remarks: operatorId || null };
    } else if (action === '__failed') {
        url  = `/api/v1/employee/recharges/${id}/status`;
        body = { status: 'failed', remarks: operatorId || null };
    } else {
        alert('Unknown action.'); return;
    }

    const res = await apiFetch(url, { method: 'POST', body: JSON.stringify(body) });
    const j   = await res.json().catch(() => ({}));
    alert(j.message || 'Done');
    loadReport(currentPage);
}

// ── Action modal ─────────────────────────────────────────────────────────────
function openAction(id){
    currentTxn = rows.find(r => r.id === id);
    if (!currentTxn) return;
    document.getElementById('am-user').textContent   = currentTxn.seller_name || '';
    document.getElementById('am-op').textContent     = (currentTxn.operator_code || '') + ' ' + (currentTxn.recharge_type || '');
    document.getElementById('am-mobile').textContent = currentTxn.mobile || '';
    document.getElementById('am-amount').textContent = Number(currentTxn.amount || 0).toFixed(2);
    document.getElementById('am-ip').textContent     = '';
    document.getElementById('am-remarks').value      = '';
    document.getElementById('action-modal').style.display = 'flex';
}
function closeAction(){ document.getElementById('action-modal').style.display = 'none'; }

async function doAction(type){
    if (!currentTxn) return;
    const remarks = document.getElementById('am-remarks').value;
    let url  = `/api/v1/employee/recharges/${currentTxn.id}/${type}`;
    let body = { remarks };
    if (type === 'status'){
        const st = prompt('Enter status: pending, processing, success, failed, refunded', currentTxn.status || 'pending');
        if (!st) return;
        body.status = st;
    }
    const res = await apiFetch(url, { method: 'POST', body: JSON.stringify(body) });
    const j   = await res.json().catch(() => ({}));
    alert(j.message || 'Done');
    closeAction();
    loadReport(currentPage);
}

// ── Log / Response modal ─────────────────────────────────────────────────────
function closeLog(){ document.getElementById('log-modal').style.display = 'none'; }

async function openLog(id)     { await loadLog(id, 'Request/Response Log'); }
async function openResponse(id){ await loadLog(id, 'Response'); }

async function loadLog(id, title){
    document.getElementById('log-title').textContent = title;
    document.getElementById('log-body').innerHTML    = '<tr><td colspan="8">Loading...</td></tr>';
    document.getElementById('log-modal').style.display = 'flex';

    const res = await apiFetch(`/api/v1/employee/recharges/${id}`);
    const j   = await res.json();
    const d   = j.data || {};
    const tx  = d.transaction || {};
    const attempts = d.attempts || [];

    const logs = attempts.length
        ? attempts.map(a => {
            const requestPayload = a.request_payload ? jsonText(a.request_payload) : '';
            const requestLines = [`[${(a.log_type || 'recharge').toUpperCase()}] ${a.log_label || ''}`.trim()];
            if (a.request_url) requestLines.push(a.request_url);
            if (requestPayload) requestLines.push(requestPayload);
            return {
                id:       a.id,
                type:     a.log_type || 'recharge',
                label:    a.log_label || 'Recharge',
                api:      `${a.api_provider || a.operator_code || ''}`.trim() || '—',
                recharge: a.created_at,
                request:  requestLines.join('\n'),
                response: a.response_payload ? jsonText(a.response_payload) : (a.error_message || ''),
            };
          })
        : [{ id: '—', type: 'none', label: 'No logs found', api: tx.api_provider || tx.operator_code || '—', recharge: tx.created_at,
             request:  'No request log found',
             response: jsonText(tx.operator_response) || tx.failure_reason || '' }];

    document.getElementById('log-body').innerHTML = logs.map(l =>
        `<tr>
            <td>${esc(l.id)}</td>
            <td>${esc(l.type)}</td>
            <td>${esc(l.label)}</td>
            <td>${esc(l.api)}</td>
            <td>${esc(tx.id)}</td>
            <td>${esc(l.recharge || '')}</td>
            <td><pre>${esc(l.request)}</pre></td>
            <td><pre>${esc(l.response)}</pre></td>
        </tr>`
    ).join('');
}

// ── Live status check (click on status badge) ────────────────────────────────
function closeStatusModal(){ document.getElementById('status-modal').style.display = 'none'; }

async function checkStatus(id){
    document.getElementById('status-body').innerHTML =
        '<div style="text-align:center;padding:20px;color:#64748b">Checking status…</div>';
    document.getElementById('status-modal').style.display = 'flex';

    const res = await apiFetch(`/api/v1/employee/recharges/${id}`);
    const j   = await res.json().catch(() => ({}));
    const tx  = j.data?.transaction || {};
    const attempts = j.data?.attempts || [];
    const last = attempts[attempts.length - 1];

    const statusCls = tx.status === 'success' ? 'rr-status-success'
                    : (tx.status === 'failed' || tx.status === 'refunded') ? 'rr-status-failed'
                    : tx.status === 'queued' ? 'rr-status-queued'
                    : tx.status === 'processing' ? 'rr-status-processing'
                    : 'rr-status-pending';

    document.getElementById('status-body').innerHTML = `
        <table style="width:100%;font-size:13px;border-collapse:collapse">
            <tr><td style="color:#64748b;padding:7px 0;width:130px;font-weight:600">TXN ID</td>
                <td style="padding:7px 0;font-weight:700">#${esc(tx.id || id)}</td></tr>
            <tr><td style="color:#64748b;padding:7px 0;font-weight:600">Mobile</td>
                <td style="padding:7px 0">${esc(tx.mobile || '—')}</td></tr>
            <tr><td style="color:#64748b;padding:7px 0;font-weight:600">Operator</td>
                <td style="padding:7px 0">${esc((tx.operator_code || '') + ' ' + (tx.recharge_type || ''))}</td></tr>
            <tr><td style="color:#64748b;padding:7px 0;font-weight:600">Amount</td>
                <td style="padding:7px 0">₹${Number(tx.amount || 0).toFixed(2)}</td></tr>
            <tr><td style="color:#64748b;padding:7px 0;font-weight:600">Status</td>
                <td style="padding:7px 0"><span class="${statusCls}">${esc(tx.status || '—')}</span></td></tr>
            <tr><td style="color:#64748b;padding:7px 0;font-weight:600">Operator Ref</td>
                <td style="padding:7px 0;font-family:monospace">${esc(tx.operator_ref || tx.api_ref || '—')}</td></tr>
            <tr><td style="color:#64748b;padding:7px 0;font-weight:600">Retry Count</td>
                <td style="padding:7px 0">${tx.retry_count || 0}</td></tr>
            ${tx.failure_reason ? `<tr><td style="color:#64748b;padding:7px 0;font-weight:600">Failure</td>
                <td style="padding:7px 0;color:#b91c1c">${esc(tx.failure_reason)}</td></tr>` : ''}
            ${last ? `<tr><td style="color:#64748b;padding:7px 0;font-weight:600">Last Attempt</td>
                <td style="padding:7px 0">${esc(last.status || '—')} — ${esc(last.error_message || 'OK')}</td></tr>` : ''}
        </table>`;
}

// ── Export ───────────────────────────────────────────────────────────────────
function exportReport(){
    if (!rows.length){ alert('No data to export.'); return; }
    const headers = ['ID','Mobile','Operator','Type','Amount','Commission','Status','API','OperatorRef','Agent','Created At'];
    const csv = [headers.join(','), ...rows.map(r =>
        [r.id, r.mobile, r.operator_code, r.recharge_type, r.amount, r.commission,
         r.status, r.api_provider || r.route_name, r.operator_ref, r.seller_name, r.created_at].join(',')
    )].join('\n');
    const a = document.createElement('a');
    a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
    a.download = 'pending_recharges_' + today + '.csv';
    a.click();
}

document.addEventListener('DOMContentLoaded', bootFilters);
</script>
@endpush
