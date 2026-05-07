@extends('layouts.admin')
@section('title', 'API Logs')
@section('page-title', 'API Logs')

@section('content')
<div class="breadcrumb">
    <a href="/admin/dashboard">Dashboard</a>
    <svg class="breadcrumb-sep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>API Logs</span>
</div>

<style>
.txn-log-card{background:var(--card-bg);border:1px solid var(--border);border-radius:10px;box-shadow:var(--shadow-sm);margin-bottom:18px}
.txn-log-head{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:16px 18px;border-bottom:1px solid var(--border)}
.txn-log-title{font-size:15px;font-weight:800;color:var(--text-primary)}
.txn-log-form{display:grid;grid-template-columns:180px minmax(220px,1fr) auto;gap:12px;padding:18px}
.txn-log-field label{display:block;font-size:11px;font-weight:700;color:var(--text-muted);margin-bottom:6px}
.txn-log-field input{width:100%;height:38px;border:1px solid var(--border);border-radius:7px;background:var(--card-bg);color:var(--text-primary);padding:0 12px;font-size:13px}
.txn-log-result{padding:0 18px 18px}
.txn-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:18px}
.txn-summary-item{display:flex;gap:12px;align-items:center;border:1px solid var(--border);border-radius:8px;background:var(--bg-page);padding:13px}
.txn-summary-icon{width:36px;height:36px;border-radius:8px;background:#eef2ff;color:#2563eb;display:flex;align-items:center;justify-content:center;font-weight:900}
.txn-summary-label{font-size:11px;color:var(--text-muted);font-weight:700}.txn-summary-value{font-size:13px;color:var(--text-primary);font-weight:800;margin-top:2px;word-break:break-word}
.txn-timeline{display:flex;gap:18px;overflow:auto;border:1px solid var(--border);border-radius:10px;padding:22px 18px;margin-bottom:18px}
.txn-timeline-item{min-width:165px;text-align:center}.txn-dot{width:13px;height:13px;border-radius:50%;background:#67e8f9;margin:0 auto 8px;box-shadow:0 0 0 8px rgba(103,232,249,.22)}
.txn-time{display:inline-block;background:#06a7d6;color:#fff;border-radius:6px;padding:5px 9px;font-size:11px;font-weight:800}.txn-label{margin-top:8px;border:1px solid #06a7d6;color:#0284c7;border-radius:5px;padding:6px 8px;font-size:11px;font-weight:700;background:rgba(6,182,212,.08)}
.txn-attempt{border:1px solid var(--border);border-radius:10px;overflow:hidden;margin-bottom:16px;background:var(--card-bg)}
.txn-attempt-head{display:flex;align-items:center;gap:10px;flex-wrap:wrap;background:linear-gradient(90deg,#5b6ff0,#7c3aed);color:#fff;padding:12px 14px}.txn-chip{border-radius:5px;padding:4px 8px;font-size:11px;font-weight:800;background:rgba(255,255,255,.18)}.txn-chip.orange{background:#fb923c;color:#111827}.txn-log-id{margin-left:auto;background:#fff;color:#111827;border-radius:5px;padding:4px 9px;font-size:11px;font-weight:800}
.txn-panes{display:grid;grid-template-columns:1fr 1fr}.txn-pane{padding:16px;border-right:1px solid var(--border)}.txn-pane:last-child{border-right:0}.txn-pane-title{display:flex;align-items:center;justify-content:space-between;font-size:14px;font-weight:900;margin-bottom:10px}.txn-pane.req .txn-pane-title{color:#3b82f6}.txn-pane.res .txn-pane-title{color:#22c55e}.txn-copy{border:1px solid currentColor;background:transparent;color:inherit;border-radius:5px;padding:5px 10px;font-size:12px;cursor:pointer}.txn-code{white-space:pre-wrap;word-break:break-word;font-family:Consolas,monospace;font-size:12px;line-height:1.55;color:var(--text-primary);margin:0}
@media(max-width:720px){.txn-log-form{grid-template-columns:1fr}.txn-summary{grid-template-columns:1fr}.txn-panes{grid-template-columns:1fr}.txn-pane{border-right:0;border-bottom:1px solid var(--border)}.txn-pane:last-child{border-bottom:0}}
</style>

<div class="txn-log-card">
    <div class="txn-log-head">
        <div>
            <div class="txn-log-title">Transaction Full API Log Search</div>
            <div style="font-size:12px;color:var(--text-muted);margin-top:2px">Search by date and mobile number or transaction ID to view complete request/response logs.</div>
        </div>
    </div>
    <div class="txn-log-form">
        <div class="txn-log-field"><label>Date</label><input id="txn-log-date" type="date"></div>
        <div class="txn-log-field"><label>Mobile Number / Transaction ID</label><input id="txn-log-q" type="text" placeholder="e.g. 9876543210 or 138751251"></div>
        <button class="btn btn-primary btn-sm" style="height:38px;align-self:end" onclick="searchTransactionLog()">Search Full Log</button>
    </div>
    <div class="txn-log-result" id="txn-log-result"></div>
</div>

<div class="card" style="margin-bottom:18px">
    <div class="card-body" style="padding:14px 18px">
        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
            <div>
                <label style="font-size:11px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">Reference / Txn ID</label>
                <input type="text" id="f-reference" placeholder="txn_id / ref_id / idempotency" style="border:1px solid var(--border);border-radius:7px;padding:6px 10px;font-size:13px;width:190px">
            </div>
            <div>
                <label style="font-size:11px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">Path</label>
                <input type="text" id="f-path" placeholder="/api/v1/recharge" style="border:1px solid var(--border);border-radius:7px;padding:6px 10px;font-size:13px;width:170px">
            </div>
            <div>
                <label style="font-size:11px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">Method</label>
                <select id="f-method" style="border:1px solid var(--border);border-radius:7px;padding:6px 10px;font-size:13px;min-width:110px">
                    <option value="">All</option>
                    <option value="GET">GET</option>
                    <option value="POST">POST</option>
                    <option value="PUT">PUT</option>
                    <option value="DELETE">DELETE</option>
                    <option value="PATCH">PATCH</option>
                </select>
            </div>
            <div>
                <label style="font-size:11px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">Status Code</label>
                <select id="f-status" style="border:1px solid var(--border);border-radius:7px;padding:6px 10px;font-size:13px;min-width:120px">
                    <option value="">All</option>
                    <option value="200">200</option>
                    <option value="201">201</option>
                    <option value="400">400</option>
                    <option value="401">401</option>
                    <option value="403">403</option>
                    <option value="404">404</option>
                    <option value="409">409</option>
                    <option value="422">422</option>
                    <option value="429">429</option>
                    <option value="500">500</option>
                    <option value="503">503</option>
                </select>
            </div>
            <div>
                <label style="font-size:11px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">Search</label>
                <input type="text" id="f-search" placeholder="user / ip / payload / error" style="border:1px solid var(--border);border-radius:7px;padding:6px 10px;font-size:13px;width:190px">
            </div>
            <div>
                <label style="font-size:11px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">From</label>
                <input type="date" id="f-from" style="border:1px solid var(--border);border-radius:7px;padding:6px 10px;font-size:13px">
            </div>
            <div>
                <label style="font-size:11px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">To</label>
                <input type="date" id="f-to" style="border:1px solid var(--border);border-radius:7px;padding:6px 10px;font-size:13px">
            </div>
            <button class="btn btn-primary btn-sm" onclick="loadApiLogs()">Apply</button>
            <button class="btn btn-outline btn-sm" onclick="clearApiLogFilters()">Clear</button>
        </div>
    </div>
</div>

<div class="stats-grid" style="grid-template-columns:repeat(4,1fr)">
    <div class="stat-card blue">
        <div>
            <div class="stat-label">Total Requests</div>
            <div class="stat-value" id="s-total">-</div>
        </div>
    </div>
    <div class="stat-card green">
        <div>
            <div class="stat-label">Success</div>
            <div class="stat-value" id="s-success">-</div>
        </div>
    </div>
    <div class="stat-card red">
        <div>
            <div class="stat-label">Errors</div>
            <div class="stat-value" id="s-errors">-</div>
        </div>
    </div>
    <div class="stat-card orange">
        <div>
            <div class="stat-label">Avg Response</div>
            <div class="stat-value" id="s-avg">-</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header" style="justify-content:space-between">
        <span class="card-title">Stored API Request Logs</span>
        <span id="api-log-count" style="font-size:12px;color:var(--text-muted)"></span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Request</th>
                    <th>User / Key</th>
                    <th>Reference</th>
                    <th>Status</th>
                    <th>Speed</th>
                    <th>Date / Time</th>
                </tr>
            </thead>
            <tbody id="api-log-tbody">
                <tr><td colspan="7"><div class="loading-overlay"><div class="spinner"></div> Loading...</div></td></tr>
            </tbody>
        </table>
    </div>
    <div class="card-footer" id="api-log-pagination" style="gap:8px;justify-content:flex-end"></div>
</div>

<div id="api-log-detail-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:400;align-items:center;justify-content:center;padding:20px">
    <div style="background:var(--card-bg,#fff);border-radius:14px;width:100%;max-width:720px;max-height:88vh;overflow-y:auto;box-shadow:0 24px 60px rgba(0,0,0,.28)">
        <div style="padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
            <h3 style="font-size:15px;font-weight:700;color:var(--text-primary)">API Log Detail</h3>
            <button onclick="closeApiLogDetail()" style="background:none;border:none;cursor:pointer;color:var(--text-muted)">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:18px;height:18px"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div style="padding:20px 22px" id="api-log-detail-body"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function apiStatusBadge(code){
    const n = Number(code || 0);
    let bg = '#e2e8f0', color = '#475569';
    if (n >= 200 && n < 300) { bg = '#d1fae5'; color = '#065f46'; }
    else if (n >= 400 && n < 500) { bg = '#fef3c7'; color = '#92400e'; }
    else if (n >= 500) { bg = '#fee2e2'; color = '#991b1b'; }
    return `<span style="background:${bg};color:${color};padding:3px 8px;border-radius:20px;font-size:11px;font-weight:700">${code || '-'}</span>`;
}

function escHtml(value){
    return String(value ?? '-')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function jsonPretty(v) {
    if (!v) return '';
    try { return JSON.stringify(typeof v === 'string' ? JSON.parse(v) : v, null, 2); }
    catch (e) { return String(v); }
}

function fmtLogDate(s) {
    if (!s) return 'N/A';
    const d = new Date(s);
    return isNaN(d) ? s : d.toLocaleString('en-IN', {day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit', second:'2-digit', hour12:true});
}

function copyTxnLog(id) {
    const el = document.getElementById(id);
    if (el) navigator.clipboard?.writeText(el.textContent || '');
}

function normalizeAttempt(a, tx, i) {
    const req = [];
    if (a.request_url) req.push(a.request_url);
    if (a.request_payload) req.push(jsonPretty(a.request_payload));
    return {
        id: a.id || 'N/A',
        number: i + 1,
        label: a.log_label || (i ? 'Status Check' : 'Initial Request'),
        api: `${a.api_provider || tx.api_provider || tx.route_name || tx.operator_code || ''}`.trim() || 'N/A',
        date: a.created_at || tx.created_at,
        request: req.join('\n') || 'No request log found',
        response: a.response_payload ? jsonPretty(a.response_payload) : (a.error_message || tx.failure_reason || 'No response captured'),
        status: a.status || tx.status || 'pending'
    };
}

function renderTransactionLog(tx, attempts, apiLogs) {
    const logs = attempts.length ? attempts.map((a, i) => normalizeAttempt(a, tx, i)) : [{
        id: 'N/A', number: 1, label: 'No recharge attempts found', api: tx.api_provider || tx.operator_code || 'N/A',
        date: tx.created_at, request: 'No request log found', response: jsonPretty(tx.operator_response) || tx.failure_reason || 'No response captured', status: tx.status || 'pending'
    }];
    const first = logs[0] || {};
    const last = logs[logs.length - 1] || {};
    const timeline = logs.map(l => `<div class="txn-timeline-item"><div class="txn-dot"></div><div class="txn-time">${escHtml(fmtLogDate(l.date))}</div><div class="txn-label">${escHtml(l.label)} · ${escHtml(l.status)}</div></div>`).join('');
    const cards = logs.map((l, i) => {
        const reqId = `txn-req-${tx.id}-${i}`;
        const resId = `txn-res-${tx.id}-${i}`;
        return `<div class="txn-attempt">
            <div class="txn-attempt-head"><strong>Log #${l.number}</strong><span class="txn-chip">${escHtml(fmtLogDate(l.date))}</span><span class="txn-chip">${escHtml(l.label)}</span><span class="txn-chip orange">${escHtml(l.status)}</span><span class="txn-log-id"># ID: ${escHtml(l.id)}</span></div>
            <div class="txn-panes">
                <div class="txn-pane req"><div class="txn-pane-title"><span>Request</span><button class="txn-copy" onclick="copyTxnLog('${reqId}')">Copy</button></div><pre class="txn-code" id="${reqId}">${escHtml(l.request)}</pre></div>
                <div class="txn-pane res"><div class="txn-pane-title"><span>Response</span><button class="txn-copy" onclick="copyTxnLog('${resId}')">Copy</button></div><pre class="txn-code" id="${resId}">${escHtml(l.response)}</pre></div>
            </div>
        </div>`;
    }).join('');
    const apiNote = apiLogs?.length ? `<div style="font-size:12px;color:var(--text-muted);margin:6px 0 16px">Related platform API request logs: ${apiLogs.length}</div>` : '';

    return `<div class="txn-summary">
        <div class="txn-summary-item"><div class="txn-summary-icon">ID</div><div><div class="txn-summary-label">Transaction ID</div><div class="txn-summary-value">${escHtml(tx.id)}</div></div></div>
        <div class="txn-summary-item"><div class="txn-summary-icon">☎</div><div><div class="txn-summary-label">Mobile</div><div class="txn-summary-value">${escHtml(tx.mobile || 'N/A')}</div></div></div>
        <div class="txn-summary-item"><div class="txn-summary-icon">API</div><div><div class="txn-summary-label">API</div><div class="txn-summary-value">${escHtml(first.api || 'N/A')}</div></div></div>
        <div class="txn-summary-item"><div class="txn-summary-icon">DT</div><div><div class="txn-summary-label">Created Date</div><div class="txn-summary-value">${escHtml(fmtLogDate(tx.created_at || first.date))}</div></div></div>
    </div><div class="txn-timeline">${timeline}</div>${apiNote}${cards}`;
}

async function searchTransactionLog() {
    const date = document.getElementById('txn-log-date').value;
    const q = document.getElementById('txn-log-q').value.trim();
    const box = document.getElementById('txn-log-result');
    if (!q) {
        box.innerHTML = '<div style="color:#dc2626;font-size:13px">Enter mobile number or transaction ID.</div>';
        return;
    }
    box.innerHTML = '<div style="padding:18px;color:var(--text-muted)">Loading transaction logs...</div>';

    let txnId = null;
    if (/^\d+$/.test(q)) {
        const directRes = await apiFetch('/api/v1/employee/recharges/' + encodeURIComponent(q));
        if (directRes.ok) {
            const directJson = await directRes.json().catch(() => ({}));
            const d = directJson.data || {};
            box.innerHTML = renderTransactionLog(d.transaction || {}, d.attempts || [], d.api_logs || []);
            return;
        }
    }

    {
        const p = new URLSearchParams({ per_page: 1 });
        if (/^\d{10,15}$/.test(q)) p.set('mobile', q);
        else p.set('mobile', q);
        if (date) {
            p.set('date_from', date);
            p.set('date_to', date);
        }
        const lookupRes = await apiFetch('/api/v1/employee/reports/recharges?' + p.toString());
        const lookupJson = await lookupRes.json().catch(() => ({}));
        const first = lookupJson.transactions?.data?.[0];
        txnId = first?.id;
    }

    if (!txnId) {
        box.innerHTML = '<div style="background:#fee2e2;color:#991b1b;border:1px solid #fecaca;border-radius:8px;padding:12px;font-size:13px">No transaction found for the selected date and search value.</div>';
        return;
    }

    const res = await apiFetch('/api/v1/employee/recharges/' + encodeURIComponent(txnId));
    const json = await res.json().catch(() => ({}));
    if (!res.ok) {
        box.innerHTML = `<div style="background:#fee2e2;color:#991b1b;border:1px solid #fecaca;border-radius:8px;padding:12px;font-size:13px">${escHtml(json.message || 'Transaction log not found.')}</div>`;
        return;
    }
    const d = json.data || {};
    box.innerHTML = renderTransactionLog(d.transaction || {}, d.attempts || [], d.api_logs || []);
}

async function loadApiLogs(page = 1) {
    document.getElementById('api-log-tbody').innerHTML =
        '<tr><td colspan="7"><div class="loading-overlay"><div class="spinner"></div> Loading...</div></td></tr>';

    const p = new URLSearchParams({ page, per_page: 50 });
    const filters = {
        reference_id: document.getElementById('f-reference').value.trim(),
        path: document.getElementById('f-path').value.trim(),
        method: document.getElementById('f-method').value,
        status_code: document.getElementById('f-status').value,
        search: document.getElementById('f-search').value.trim(),
        from: document.getElementById('f-from').value,
        to: document.getElementById('f-to').value,
    };

    Object.entries(filters).forEach(([k, v]) => { if (v) p.set(k, v); });

    const res = await apiFetch('/api/v1/employee/api-logs?' + p.toString());
    const json = await res.json();
    const meta = json.data || {};
    const rows = meta.data || [];
    const summary = meta.summary || {};

    document.getElementById('s-total').textContent = fmtNum(summary.total_requests || 0);
    document.getElementById('s-success').textContent = fmtNum(summary.success_requests || 0);
    document.getElementById('s-errors').textContent = fmtNum(summary.error_requests || 0);
    document.getElementById('s-avg').textContent = Math.round(summary.avg_response_time_ms || 0) + ' ms';
    document.getElementById('api-log-count').textContent = fmtNum(meta.total || 0) + ' records';

    if (!rows.length) {
        document.getElementById('api-log-tbody').innerHTML =
            '<tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:30px">No API logs found</td></tr>';
        document.getElementById('api-log-pagination').innerHTML = '';
        return;
    }

    document.getElementById('api-log-tbody').innerHTML = rows.map((r) => {
        const requestLabel = `<div style="font-weight:700;color:var(--text-primary)">${escHtml(r.method)} ${escHtml(r.path)}</div>
            <div style="font-size:11px;color:var(--text-muted);max-width:250px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${escHtml(r.query_string || r.ip_address || '-')}</div>`;
        const userLabel = `<div style="font-weight:600;color:var(--text-primary)">${escHtml(r.user_name || 'Guest / Token')}</div>
            <div style="font-size:11px;color:var(--text-muted)">${escHtml(r.user_email || r.api_key_prefix || 'No key')}</div>`;
        const reference = r.reference_id ? `<code style="font-size:11px;background:#f1f5f9;padding:2px 6px;border-radius:4px">${escHtml(r.reference_id)}</code>` : '<span style="color:var(--text-muted)">-</span>';
        const date = r.created_at ? new Date(r.created_at).toLocaleString('en-IN',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:true}) : '-';
        const encoded = encodeURIComponent(JSON.stringify(r));

        return `<tr style="cursor:pointer" onclick="showApiLogDetail(decodeURIComponent('${encoded}'))">
            <td style="font-family:monospace;font-size:11px;color:var(--text-muted)">${r.id}</td>
            <td>${requestLabel}</td>
            <td>${userLabel}</td>
            <td>${reference}</td>
            <td>${apiStatusBadge(r.status_code)}</td>
            <td style="font-weight:700;color:${Number(r.response_time_ms || 0) > 2000 ? '#dc2626' : '#2563eb'}">${fmtNum(r.response_time_ms || 0)} ms</td>
            <td style="font-size:11px;color:var(--text-muted)">${date}</td>
        </tr>`;
    }).join('');

    const lastPage = meta.last_page || 1;
    const currentPage = meta.current_page || page;
    const pag = document.getElementById('api-log-pagination');
    if (lastPage > 1) {
        let html = '';
        if (currentPage > 1) html += `<button class="btn btn-outline btn-sm" onclick="loadApiLogs(${currentPage - 1})">Prev</button>`;
        html += `<span style="font-size:12px;color:var(--text-muted)">Page ${currentPage} of ${lastPage}</span>`;
        if (currentPage < lastPage) html += `<button class="btn btn-outline btn-sm" onclick="loadApiLogs(${currentPage + 1})">Next</button>`;
        pag.innerHTML = html;
    } else {
        pag.innerHTML = '';
    }
}

function showApiLogDetail(raw) {
    const r = JSON.parse(raw);
    const payload = r.request_payload ? `<pre style="background:#0f172a;color:#e2e8f0;border-radius:10px;padding:12px;font-size:11px;overflow:auto;white-space:pre-wrap">${escHtml(r.request_payload)}</pre>` : '<div style="color:var(--text-muted)">No payload stored</div>';
    const errorBlock = r.error_message ? `<div style="background:#fee2e2;color:#991b1b;border:1px solid #fecaca;border-radius:10px;padding:10px 12px;font-size:12px;margin-top:14px"><strong>Error:</strong> ${escHtml(r.error_message)}</div>` : '';

    document.getElementById('api-log-detail-body').innerHTML = `
        <table style="width:100%;border-collapse:collapse;font-size:13px">
            ${detailRow('Log ID', r.id)}
            ${detailRow('Method', escHtml(r.method))}
            ${detailRow('Path', `<code>${escHtml(r.path)}</code>`)}
            ${detailRow('Query String', escHtml(r.query_string || '-'))}
            ${detailRow('Reference ID', escHtml(r.reference_id || '-'))}
            ${detailRow('Status Code', apiStatusBadge(r.status_code))}
            ${detailRow('Response Time', fmtNum(r.response_time_ms || 0) + ' ms')}
            ${detailRow('User', escHtml(r.user_name || '-'))}
            ${detailRow('User Email', escHtml(r.user_email || '-'))}
            ${detailRow('API Key', escHtml(r.api_key_prefix || '-'))}
            ${detailRow('IP Address', escHtml(r.ip_address || '-'))}
            ${detailRow('User Agent', `<div style="word-break:break-word">${escHtml(r.user_agent || '-')}</div>`)}
            ${detailRow('Request Size', fmtNum(r.request_size || 0) + ' bytes')}
            ${detailRow('Response Size', fmtNum(r.response_size || 0) + ' bytes')}
            ${detailRow('Created At', r.created_at ? new Date(r.created_at).toLocaleString('en-IN') : '-')}
        </table>
        <div style="font-size:12px;font-weight:700;color:var(--text-primary);margin-top:18px;margin-bottom:8px">Request Payload</div>
        ${payload}
        ${errorBlock}
    `;

    const overlay = document.getElementById('api-log-detail-overlay');
    overlay.style.display = 'flex';
    overlay.onclick = (e) => { if (e.target === overlay) closeApiLogDetail(); };
}

function detailRow(label, value) {
    return `<tr>
        <td style="padding:8px 0;color:var(--text-muted);font-weight:600;width:140px;vertical-align:top">${label}</td>
        <td style="padding:8px 0;color:var(--text-primary);font-weight:500">${value}</td>
    </tr>`;
}

function closeApiLogDetail() {
    document.getElementById('api-log-detail-overlay').style.display = 'none';
}

function clearApiLogFilters() {
    ['f-reference', 'f-path', 'f-method', 'f-status', 'f-search', 'f-from', 'f-to']
        .forEach(id => document.getElementById(id).value = '');
    loadApiLogs();
}

document.addEventListener('DOMContentLoaded', () => {
    const d = new Date();
    document.getElementById('txn-log-date').value = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
    loadApiLogs();
});
</script>
@endpush
