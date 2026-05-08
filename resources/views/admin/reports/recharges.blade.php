@extends('layouts.admin')

@section('title', 'Recharge Report')
@section('page-title', 'Recharge Report')

@section('content')
<style>
.rr-card{background:var(--card-bg);border:1px solid var(--border);border-radius:4px;margin-bottom:18px;color:var(--text-primary)}
.rr-head{padding:13px 14px;border-bottom:1px solid var(--border);font-size:12px;font-weight:800;text-transform:uppercase;color:var(--text-primary)}
.rr-filters{display:grid;grid-template-columns:100px 100px 90px 120px 120px 120px 185px 120px 120px auto auto;gap:12px;align-items:end;padding:20px 10px 22px}
.rr-field label{display:block;font-size:16px;font-weight:700;color:var(--text-secondary);margin-bottom:8px}.rr-field input,.rr-field select{width:100%;height:28px;border:1px solid var(--border);padding:3px 8px;font-size:12px;background:var(--card-bg);color:var(--text-primary)}
.rr-blue{background:#0068ce;color:#fff;border:none;border-radius:4px;padding:7px 12px;font-weight:700;cursor:pointer}.rr-green{background:#10b900;color:#fff;border:none;border-radius:4px;padding:7px 12px;font-weight:700;cursor:pointer}
.rr-table-wrap{overflow:auto;padding:0 6px 14px;-webkit-overflow-scrolling:touch}.rr-table{width:100%;border-collapse:collapse;min-width:1380px}.rr-table th{font-size:13px;font-weight:800;text-align:left;padding:14px 12px;border-top:1px solid var(--border);border-bottom:1px solid var(--border);color:var(--text-primary)}.rr-table td{font-size:14px;vertical-align:top;padding:13px 12px;border-bottom:1px solid var(--border);color:var(--text-primary)}
.rr-id{color:#005fd1;font-weight:700;cursor:pointer;line-height:1.5}.rr-agent{color:#2563dc;font-size:16px;font-weight:800}.rr-status-badge{font-weight:800;padding:2px 6px;border-radius:3px;display:inline-block}
.rr-status-pending{background:#f59e0b;color:#fff}
.rr-status-queued{background:#0ea5e9;color:#fff}
.rr-status-processing{background:#8b5cf6;color:#fff}
.rr-status-success{background:#16a34a;color:#fff}
.rr-status-failed{background:#dc2626;color:#fff}
.rr-actions{display:flex;flex-direction:column;gap:2px;width:70px}.rr-actions button{border:none;color:#fff;font-size:12px;font-weight:800;padding:7px 6px;border-radius:4px;cursor:pointer}.rr-a-blue{background:#0068ce}.rr-a-orange{background:#f59e0b}.rr-a-cyan{background:#14a3b8}
.rr-modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,.35);z-index:700;align-items:center;justify-content:center}.rr-box{background:var(--card-bg);color:var(--text-primary);border:1px solid var(--border);min-width:590px;max-width:96vw}.rr-box-head{display:flex;align-items:center;justify-content:space-between;padding:12px 12px;background:var(--card-bg);color:var(--text-secondary);font-size:20px;font-weight:800}.rr-box-body{padding:20px 12px}.rr-box-foot{display:flex;justify-content:flex-end;gap:6px;padding:16px 12px;background:var(--card-bg)}.rr-box textarea{width:400px;height:48px}.rr-box select{height:31px}.rr-box-foot button{border:none;color:#fff;border-radius:4px;padding:8px 13px;font-weight:800;cursor:pointer}
.rr-log .rr-box{width:1200px;max-width:98vw;max-height:88vh;overflow:auto;background:var(--card-bg)}.rr-log .rr-box-head{background:linear-gradient(90deg,#06b6d4,#0891b2);color:#03121f;font-size:20px}.api-log-body{padding:18px 16px;background:var(--card-bg)}.api-summary-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:22px}.api-summary-card{display:flex;align-items:center;gap:12px;background:var(--bg-page);border:1px solid var(--border);border-radius:8px;padding:14px}.api-summary-icon{width:40px;height:40px;border-radius:8px;display:flex;align-items:center;justify-content:center;background:#eef2ff;color:#2563eb;font-weight:900}.api-summary-label{font-size:12px;color:var(--text-secondary);margin-bottom:4px}.api-summary-value{font-size:14px;font-weight:800;color:var(--text-primary)}.api-section-title{font-size:15px;font-weight:900;color:var(--text-primary);margin:20px 0 4px}.api-section-sub{font-size:12px;color:var(--text-secondary);margin-bottom:14px}.api-timeline{display:flex;gap:28px;overflow-x:auto;border:1px solid var(--border);border-radius:10px;padding:28px 22px;margin-bottom:24px;background:var(--card-bg)}.api-timeline-item{min-width:180px;text-align:center;position:relative}.api-timeline-dot{width:14px;height:14px;border-radius:50%;background:#67e8f9;margin:0 auto 8px;box-shadow:0 0 0 10px rgba(103,232,249,.22)}.api-timeline-time{display:inline-block;background:#06a7d6;color:#fff;border-radius:6px;padding:5px 10px;font-size:12px;font-weight:800}.api-timeline-label{margin-top:8px;border:1px solid #06a7d6;color:#0284c7;border-radius:5px;padding:6px 8px;font-size:12px;font-weight:700;background:rgba(6,182,212,.08)}.api-log-card{border:1px solid var(--border);border-radius:10px;overflow:hidden;margin-bottom:18px;background:var(--card-bg);box-shadow:var(--shadow-sm)}.api-log-head{display:flex;align-items:center;gap:12px;flex-wrap:wrap;background:linear-gradient(90deg,#5b6ff0,#7c3aed);color:#fff;padding:13px 16px}.api-log-title{font-size:15px;font-weight:900}.api-chip{border-radius:5px;padding:4px 9px;font-size:11px;font-weight:800;background:rgba(255,255,255,.18)}.api-chip.orange{background:#fb923c;color:#111827}.api-log-id{margin-left:auto;background:#fff;color:#111827;border-radius:5px;padding:4px 10px;font-size:12px;font-weight:800}.api-log-grid{display:grid;grid-template-columns:1fr 1fr;gap:0}.api-pane{padding:18px;border-right:1px solid var(--border)}.api-pane:last-child{border-right:none}.api-pane-title{display:flex;align-items:center;justify-content:space-between;font-size:15px;font-weight:900;margin-bottom:12px}.api-pane.request .api-pane-title{color:#3b82f6}.api-pane.response .api-pane-title{color:#22c55e}.api-copy{border:1px solid currentColor;background:transparent;border-radius:5px;padding:5px 12px;font-size:12px;cursor:pointer;color:inherit}.api-code{white-space:pre-wrap;word-break:break-word;font-family:Consolas,monospace;font-size:12px;line-height:1.55;color:var(--text-primary);margin:0}
.rpt-head{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin:56px 0 22px}.rpt-title{display:flex;align-items:center;gap:10px;font-size:20px;font-weight:900;color:#000}.rpt-crumbs{display:flex;gap:8px;align-items:center;color:#3b82f6;font-size:14px;margin-top:4px}.rpt-top-actions{display:flex;gap:8px}.rpt-btn{border:0;border-radius:4px;padding:9px 13px;font-size:13px;cursor:pointer}.rpt-refresh{background:#a855f7;color:#fff}.rpt-export{background:#e8f1ff;color:#3b82f6}.rpt-alert{background:#eaf3ff;border:1px solid #cfe1ff;border-radius:4px;color:#3b82f6;padding:14px 16px;font-size:13px;margin-bottom:16px}.rpt-stats{display:grid;grid-template-columns:repeat(4,minmax(180px,1fr));gap:16px;margin-bottom:22px}.rpt-stat{background:var(--card-bg);border:1px solid var(--border);box-shadow:var(--shadow-sm);border-radius:10px;min-height:126px;display:flex;align-items:center;gap:22px;padding:26px 28px}.rpt-stat-icon{width:48px;height:48px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:900}.rpt-success .rpt-stat-icon{background:#2acb7f}.rpt-pending .rpt-stat-icon{background:#ec5aa2}.rpt-rerouted .rpt-stat-icon{background:#6654cf}.rpt-failed .rpt-stat-icon{background:#ef4444}.rpt-stat-label{color:#8b9ab5;font-weight:700}.rpt-stat-value{font-size:22px;font-weight:900;margin-top:4px}.rpt-success .rpt-stat-value{color:#20c878}.rpt-pending .rpt-stat-value{color:#fb8b45}.rpt-rerouted .rpt-stat-value{color:#4f8cff}.rpt-failed .rpt-stat-value{color:#ef4444}.rpt-stat-sub{font-size:12px;color:#8b9ab5;margin-top:2px}.rpt-filter{background:var(--card-bg);border:1px solid var(--border);border-radius:8px;box-shadow:var(--shadow-sm);padding:18px 16px;margin-bottom:22px}.rpt-filter-grid{display:grid;grid-template-columns:180px 180px 280px 140px 140px 180px 180px 190px;gap:10px;align-items:end}.rpt-field label{display:block;font-size:13px;font-weight:700;color:var(--text-primary);margin-bottom:8px}.rpt-control{width:100%;height:40px;border:1px solid var(--border);border-radius:6px;background:var(--card-bg);color:var(--text-primary);padding:0 12px;font-size:14px}.rpt-quick{display:grid;grid-template-columns:100px 1fr}.rpt-search-btn{height:42px;width:50px;border:0;border-radius:6px 0 0 6px;background:#4f8cff;color:#fff;font-size:20px;cursor:pointer}.rpt-reset-btn{height:42px;width:50px;border:1px solid var(--border);border-left:0;border-radius:0 6px 6px 0;background:var(--card-bg);color:var(--text-primary);font-size:18px;cursor:pointer}.rpt-table{min-width:1540px;border-collapse:collapse;background:var(--card-bg)}.rpt-table th{font-size:14px;font-weight:800;color:#000;text-align:left;padding:14px 18px;border-bottom:1px solid var(--border)}.rpt-table td{font-size:14px;color:#000;padding:16px 18px;border-bottom:1px solid var(--border);vertical-align:middle}.rpt-id{font-weight:900;color:#000;cursor:pointer}.rpt-muted{font-size:12px;color:#8b9ab5;margin-top:3px}.rpt-main{font-weight:900;color:#000}.rpt-amount{font-weight:900;color:#000}.rpt-status{display:inline-flex;border-radius:999px;padding:6px 12px;font-size:12px;font-weight:800}.rpt-status.success{background:#d1fae5;color:#008a55}.rpt-status.failed,.rpt-status.refunded{background:#fee2e2;color:#dc2626}.rpt-status.pending,.rpt-status.queued,.rpt-status.processing{background:#fde68a;color:#92400e}.rpt-service{display:flex;align-items:center;gap:10px}.rpt-service-icon{width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#6d5dd3;color:#fff}.rpt-opid{color:#db2777;font-size:12px}.rpt-action-group{display:flex}.rpt-action{border:0;background:#4f8cff;color:#fff;padding:8px 13px;font-weight:800;cursor:pointer}.rpt-action:first-child{border-radius:4px 0 0 4px}.rpt-action:last-child{border-radius:0 4px 4px 0;background:#3b82f6}.rpt-action.response-only{border-radius:4px}.rpt-field.state{display:none}
html[data-dark="1"] .rpt-title,html[data-dark="1"] .rpt-table th,html[data-dark="1"] .rpt-table td,html[data-dark="1"] .rpt-id,html[data-dark="1"] .rpt-main,html[data-dark="1"] .rpt-amount{color:var(--text-primary)}html[data-dark="1"] .rpt-alert{background:#10223d;border-color:#1d4ed8}html[data-dark="1"] .rpt-stat,html[data-dark="1"] .rpt-filter,html[data-dark="1"] .rpt-table{background:var(--card-bg)}
@media(max-width:1200px){.rr-filters{grid-template-columns:repeat(3,1fr)}}
@media(max-width:720px){
    .rr-card{border-radius:8px;margin-bottom:12px;max-width:100%;overflow:hidden}
    .rr-head{padding:10px 12px}
    .rr-filters{grid-template-columns:1fr;gap:10px;padding:12px}
    .rr-field label{font-size:12px;margin-bottom:5px}
    .rr-field input,.rr-field select{height:38px;font-size:14px}
    .rr-blue,.rr-green{width:100%;min-height:38px}
    .rr-table-wrap{padding:0 0 10px;max-width:100vw}
    .rr-table{min-width:1100px}
    .rr-table th,.rr-table td{font-size:12px;padding:10px 8px}
    .rr-box{min-width:0;width:calc(100vw - 24px);max-height:86vh;overflow:auto}
    .rr-box-body,.rr-box-foot{padding:12px}
    .rr-box textarea{width:100%}
    .rr-box-foot{flex-direction:column}
    .rr-box-foot button{width:100%;min-height:36px}
    .api-log-body{padding:12px}.api-summary-grid{grid-template-columns:1fr;gap:10px}.api-timeline{padding:22px 14px;gap:18px}.api-log-grid{grid-template-columns:1fr}.api-pane{border-right:none;border-bottom:1px solid var(--border);padding:14px}.api-pane:last-child{border-bottom:none}.api-log-id{margin-left:0}
    .rpt-head{margin-top:18px;align-items:flex-start}.rpt-title{font-size:18px}.rpt-crumbs{font-size:12px;flex-wrap:wrap}.rpt-stats{grid-template-columns:1fr}.rpt-stat{min-height:96px;padding:18px}.rpt-filter-grid{grid-template-columns:1fr}.rpt-table{min-width:1180px}.rpt-table th,.rpt-table td{font-size:12px;padding:12px 10px}
}
</style>

<div class="rpt-head">
    <div>
        <div class="rpt-title"><span style="color:#3b82f6">▦</span> Recharge Report</div>
        <div class="rpt-crumbs"><span>⌂ Dashboard</span><span>»</span><span>Reports</span><span>»</span><strong style="color:var(--text-primary)">Recharge Report</strong></div>
    </div>
    <div class="rpt-top-actions">
        <button class="rpt-btn rpt-refresh" onclick="loadReport()">↻ Refresh</button>
        <button class="rpt-btn rpt-export" onclick="exportReport()">⇩ Export</button>
    </div>
</div>

<div class="rpt-alert">ⓘ <strong>Data Updated:</strong> just now | <strong>Next Update:</strong> 25 seconds from now</div>

<div class="rpt-stats" id="rpt-stats">
    <div class="rpt-stat rpt-success"><div class="rpt-stat-icon">✓</div><div><div class="rpt-stat-label">Success</div><div class="rpt-stat-value">₹0.00</div><div class="rpt-stat-sub">0 transactions</div></div></div>
    <div class="rpt-stat rpt-pending"><div class="rpt-stat-icon">⌛</div><div><div class="rpt-stat-label">Pending</div><div class="rpt-stat-value">₹0.00</div><div class="rpt-stat-sub">0 waiting</div></div></div>
    <div class="rpt-stat rpt-rerouted"><div class="rpt-stat-icon">⌖</div><div><div class="rpt-stat-label">Re-routed</div><div class="rpt-stat-value">₹0.00</div><div class="rpt-stat-sub">0 retries</div></div></div>
    <div class="rpt-stat rpt-failed"><div class="rpt-stat-icon">△</div><div><div class="rpt-stat-label">Failed</div><div class="rpt-stat-value">₹0.00</div><div class="rpt-stat-sub">0 declined</div></div></div>
</div>

<div class="rpt-filter">
    <div class="rpt-filter-grid">
        <div class="rpt-field"><label>▣ From Date</label><input class="rpt-control" id="nf-from" type="date"></div>
        <div class="rpt-field"><label>▣ To Date</label><input class="rpt-control" id="nf-to" type="date"></div>
        <div class="rpt-field"><label>⌕ Quick Search</label><div class="rpt-quick"><select class="rpt-control" id="nf-search-type"><option>Mobile</option><option>ID</option></select><input class="rpt-control" id="nf-search" type="text"></div></div>
        <div class="rpt-field"><label>⌁ Status</label><select class="rpt-control" id="nf-status"><option value="">All</option><option value="pending">Pending</option><option value="queued">Queued</option><option value="processing">Processing</option><option value="success">Success</option><option value="failed">Failed</option><option value="refunded">Refunded</option></select></div>
        <div class="rpt-field"><label>⌘ API/Service</label><select class="rpt-control" id="nf-api"><option value="">All APIs</option></select></div>
        <div class="rpt-field"><label>▦ Service Type</label><select class="rpt-control" id="nf-service" onchange="filterOperatorsByService(this.value)"><option value="">All Services</option><optgroup label="Mobile"><option value="prepaid">Prepaid</option><option value="postpaid">Postpaid</option></optgroup><optgroup label="DTH & Internet"><option value="dth">DTH</option><option value="broadband">Broadband</option><option value="landline">Landline</option></optgroup><optgroup label="BBPS Services"><option value="electricity">Electricity</option><option value="gas">Gas</option><option value="water">Water</option><option value="insurance">Insurance</option><option value="loan">Loan Repayment</option><option value="fastag">FASTag</option><option value="credit_card">Credit Card</option><option value="municipal_tax">Municipal Tax</option><option value="education">Education Fee</option><option value="subscription">Subscription / OTT</option></optgroup></select></div>
        <div class="rpt-field"><label>▦ Operator</label><select class="rpt-control" id="nf-operator"><option value="">All Operators</option></select></div>
        <div class="rpt-field state"><label>⌖ State</label><select class="rpt-control" id="nf-state"><option value="">All States</option></select></div>
        <div style="display:flex"><button class="rpt-search-btn" onclick="syncNewFilters();loadReport()">⌕</button><button class="rpt-reset-btn" onclick="resetNewFilters()">↻</button></div>
    </div>
</div>

<div class="rr-card" style="display:none">
    <div class="rr-head">Search Filters</div>
    <div class="rr-filters">
        <div class="rr-field"><label>From Date</label><input id="f-from" type="date"></div>
        <div class="rr-field"><label>To Date</label><input id="f-to" type="date"></div>
        <div class="rr-field"><label>Status</label><select id="f-status"><option value="">ALL</option><option>pending</option><option>queued</option><option>processing</option><option>success</option><option>failed</option><option>refunded</option></select></div>
        <div class="rr-field"><label>Service</label><select id="f-service"><option value="">ALL</option><option value="prepaid">Prepaid</option><option value="postpaid">Postpaid</option><option value="dth">DTH</option><option value="broadband">Broadband</option><option value="landline">Landline</option><option value="electricity">Electricity</option><option value="gas">Gas</option><option value="water">Water</option><option value="insurance">Insurance</option><option value="loan">Loan Repayment</option><option value="fastag">FASTag</option><option value="credit_card">Credit Card</option><option value="municipal_tax">Municipal Tax</option><option value="education">Education Fee</option><option value="subscription">Subscription / OTT</option></select></div>
        <div class="rr-field"><label>Operator</label><select id="f-operator"><option value="">ALL</option></select></div>
        <div class="rr-field"><label>State</label><select id="f-state"><option value="">ALL</option></select></div>
        <div class="rr-field"><label>API</label><select id="f-api"><option value="">ALL</option></select></div>
        <div class="rr-field"><label>Number / Id</label><input id="f-search" type="text"></div>
        <div class="rr-field"><label>Data</label><select id="f-data"><option>LIVE</option><option>ALL</option></select></div>
        <button class="rr-blue" onclick="loadReport()">Submit</button>
        <button class="rr-green" onclick="exportReport()">Export</button>
    </div>
</div>

<div class="rr-card" style="border:0;box-shadow:none;background:transparent">
    <div class="rr-head" style="display:none">Recharge Report</div>
    <div style="padding:22px 6px 6px;color:#005fd1;font-size:13px"><span id="rr-count">0</span>&gt;Last ›</div>
    <div class="rr-table-wrap">
        <table class="rpt-table">
            <thead><tr><th>#</th><th>Recharge ID</th><th>Date & Time</th><th>Agent / Mobile</th><th>Mobile / Operator / State</th><th>Amount</th><th>Status</th><th>Service</th><th>Op ID</th><th>API</th><th>Actions</th></tr></thead>
            <tbody id="rr-body"><tr><td colspan="11" style="text-align:center;padding:30px;color:#777">Loading...</td></tr></tbody>
        </table>
    </div>
</div>

<div id="action-modal" class="rr-modal">
    <div class="rr-box">
        <div class="rr-box-head"><span>Action</span><button onclick="closeAction()" style="border:none;background:none;font-size:18px;color:#777;cursor:pointer">×</button></div>
        <div class="rr-box-body">
            <p>Username : <span id="am-user"></span></p>
            <p>Operator : <span id="am-op"></span></p>
            <p>Mobile Number : <span id="am-mobile"></span></p>
            <p>Amount : <span id="am-amount"></span></p>
            <p>Remarks or OperatorId or Transaction Id :</p>
            <textarea id="am-remarks"></textarea>
            <p>Recharge Ip:</p>
            <p>Send To Another API: <select id="am-api"></select> <button class="rr-blue" onclick="sendToApi()">Send</button></p>
        </div>
        <div class="rr-box-foot">
            <button style="background:#e83e58" onclick="doAction('refund')">Refund</button>
            <button style="background:#f59e0b" onclick="doAction('status')">Status</button>
            <button style="background:#0068ce" onclick="doAction('success')">Success</button>
            <button style="background:#f59e0b" onclick="doAction('resend')">Resend</button>
        </div>
    </div>
</div>

<div id="log-modal" class="rr-modal rr-log">
    <div class="rr-box">
        <div class="rr-box-head"><span id="log-title">Recharge Details & API Logs</span><button onclick="closeLog()" style="border:none;background:none;color:#fff;font-size:18px;cursor:pointer">×</button></div>
        <div id="log-body" class="api-log-body"></div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let rows=[], currentTxn=null, apiRoutes=[], allOperators=[];

const SERVICE_CATEGORY_MAP = {
    prepaid:      ['mobile'],
    postpaid:     ['mobile'],
    dth:          ['dth'],
    broadband:    ['broadband'],
    landline:     ['landline'],
    electricity:  ['electricity'],
    gas:          ['gas'],
    water:        ['water'],
    insurance:    ['insurance'],
    loan:         ['loan'],
    fastag:       ['fastag'],
    credit_card:  ['credit_card'],
    municipal_tax:['municipal_tax'],
    education:    ['education'],
    subscription: ['subscription'],
};

function filterOperatorsByService(service){
    const cats = SERVICE_CATEGORY_MAP[service] || null;
    const filtered = cats ? allOperators.filter(o => cats.includes(o.category)) : allOperators;
    const nfSel = document.getElementById('nf-operator');
    const fSel  = document.getElementById('f-operator');
    const opts  = filtered.map(o=>`<option value="${esc(o.code)}">${esc(o.name)}</option>`).join('');
    nfSel.innerHTML = '<option value="">All Operators</option>' + opts;
    fSel.innerHTML  = '<option value="">ALL</option>' + opts;
    nfSel.value = ''; fSel.value = '';
}
function todayLocalISO(){
    const d = new Date();
    const y = d.getFullYear();
    const m = String(d.getMonth()+1).padStart(2,'0');
    const day = String(d.getDate()).padStart(2,'0');
    return `${y}-${m}-${day}`;
}
const today = todayLocalISO();
document.getElementById('f-from').value=today; document.getElementById('f-to').value=today;
document.getElementById('nf-from').value=today; document.getElementById('nf-to').value=today;

function esc(s){return String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));}
function jsonText(v){if(!v)return ''; try{return JSON.stringify(typeof v==='string'?JSON.parse(v):v,null,2)}catch(e){return String(v)}}
function fmtDur(ms){
    if(ms == null || !isFinite(ms) || ms < 0) return '—';
    const sec = Math.max(0, Math.round(ms/1000));
    return `${sec} sec`;
}
function money(v){return '₹'+Number(v||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2})}
function syncNewFilters(){
    document.getElementById('f-from').value=document.getElementById('nf-from').value;
    document.getElementById('f-to').value=document.getElementById('nf-to').value;
    document.getElementById('f-status').value=document.getElementById('nf-status').value;
    document.getElementById('f-service').value=document.getElementById('nf-service').value;
    document.getElementById('f-operator').value=document.getElementById('nf-operator').value;
    document.getElementById('f-api').value=document.getElementById('nf-api').value;
    document.getElementById('f-search').value=document.getElementById('nf-search').value.trim();
}
function resetNewFilters(){
    ['nf-status','nf-service','nf-operator','nf-api','nf-search'].forEach(id=>document.getElementById(id).value='');
    document.getElementById('nf-from').value=today; document.getElementById('nf-to').value=today;
    filterOperatorsByService('');
    syncNewFilters(); loadReport();
}
function params(){const p=new URLSearchParams(); const map={date_from:'f-from',date_to:'f-to',status:'f-status',recharge_type:'f-service',operator_code:'f-operator',api_provider:'f-api'}; Object.entries(map).forEach(([k,id])=>{const v=document.getElementById(id).value;if(v)p.set(k,v)}); const s=document.getElementById('f-search').value.trim(); if(/^\d{10,15}$/.test(s))p.set('mobile',s); p.set('per_page',100); return p;}

async function bootFilters(){
    // Start report load immediately (don't block UI on dropdown APIs)
    loadReport();

    const [opsRes, apiRes] = await Promise.all([
        apiFetch('/api/v1/employee/operator-settings'),
        apiFetch('/api/v1/employee/api-providers')
    ]);
    const ops  = opsRes ? await opsRes.json() : {};
    const apis = apiRes ? await apiRes.json() : {};
    allOperators = ops.operators || [];
    filterOperatorsByService(document.getElementById('nf-service').value);
    apiRoutes = apis.routes || [];
    const providers = [...new Set(apiRoutes.map(r=>r.api_provider).filter(Boolean))];
    document.getElementById('f-api').innerHTML =
        '<option value="">ALL</option>' + providers.map(p=>`<option value="${esc(p)}">${esc(p)}</option>`).join('');
    document.getElementById('nf-api').innerHTML =
        '<option value="">All APIs</option>' + providers.map(p=>`<option value="${esc(p)}">${esc(p)}</option>`).join('');
    document.getElementById('am-api').innerHTML =
        '<option value="">select Api</option>' + apiRoutes.map(r=>`<option value="${r.id}">${esc(r.name)} - ${esc(r.api_provider)}</option>`).join('');
}

async function loadReport(){
    syncNewFilters();
    document.getElementById('rr-body').innerHTML='<tr><td colspan="11" style="text-align:center;padding:30px;color:#777">Loading...</td></tr>';
    const res=await apiFetch('/api/v1/employee/reports/recharges?'+params().toString()); if(!res)return;
    const data=await res.json(); rows=data.transactions?.data||[]; document.getElementById('rr-count').textContent=data.transactions?.total||rows.length; renderSummary(data.summary||{}); renderRows();
}

function renderSummary(summary){
    const fromRows = (statusList) => rows.filter(r => statusList.includes(r.status));
    const sum = list => list.reduce((t,r)=>t+Number(r.amount||0),0);
    const success = fromRows(['success']);
    const pending = fromRows(['pending','queued','processing']);
    const failed = fromRows(['failed','refunded']);
    const rerouted = rows.filter(r => Number(r.retry_count || 0) > 0);
    document.getElementById('rpt-stats').innerHTML = `
        <div class="rpt-stat rpt-success"><div class="rpt-stat-icon">✓</div><div><div class="rpt-stat-label">Success</div><div class="rpt-stat-value">${money(summary.success_amount ?? sum(success))}</div><div class="rpt-stat-sub">${summary.success_count ?? success.length} transactions</div></div></div>
        <div class="rpt-stat rpt-pending"><div class="rpt-stat-icon">⌛</div><div><div class="rpt-stat-label">Pending</div><div class="rpt-stat-value">${money(summary.pending_amount ?? sum(pending))}</div><div class="rpt-stat-sub">${summary.pending_count ?? pending.length} waiting</div></div></div>
        <div class="rpt-stat rpt-rerouted"><div class="rpt-stat-icon">⌖</div><div><div class="rpt-stat-label">Re-routed</div><div class="rpt-stat-value">${money(summary.rerouted_amount ?? sum(rerouted))}</div><div class="rpt-stat-sub">${summary.rerouted_count ?? rerouted.length} retries</div></div></div>
        <div class="rpt-stat rpt-failed"><div class="rpt-stat-icon">△</div><div><div class="rpt-stat-label">Failed</div><div class="rpt-stat-value">${money(summary.failed_amount ?? sum(failed))}</div><div class="rpt-stat-sub">${summary.failed_count ?? failed.length} declined</div></div></div>
    `;
}

function renderRows(){
    const body=document.getElementById('rr-body');
    if(!rows.length){body.innerHTML='<tr><td colspan="15" style="text-align:center;padding:30px;color:#777">No records found.</td></tr>';return}
    body.innerHTML=rows.map(r=>{
        const d=r.created_at?new Date(r.created_at):null;
        const p=r.processed_at?new Date(r.processed_at):null;
        const tatMs=(d&&p)?(p-d):null;
        const salePct=r.amount?((Number(r.commission||0)/Number(r.amount))*100):0, purchase=Number(r.operator_margin||0), profit=(Number(r.operator_margin||0)-Number(r.commission||0));
        const statusCls = r.status==='success' ? 'rr-status-success'
                        : (r.status==='failed'||r.status==='refunded') ? 'rr-status-failed'
                        : r.status==='queued' ? 'rr-status-queued'
                        : r.status==='processing' ? 'rr-status-processing'
                        : 'rr-status-pending';
        return `<tr>
            <td><div class="rr-id" onclick="openLog(${r.id})">${r.id}<br>API<br>no</div></td>
            <td>${esc(r.api_ref||'')}</td><td>${esc(r.operator_ref||'')}</td>
            <td style="white-space:nowrap;font-size:12px">${d?d.toISOString().slice(0,10):''}<br><span style="color:#64748b;font-weight:700">${d?d.toLocaleTimeString('en-IN',{hour12:false}):''}</span></td>
            <td style="white-space:nowrap;font-size:12px;font-weight:800">${fmtDur(tatMs)}</td>
            <td><span class="rr-agent">${esc(r.user_name||'')}</span></td>
            <td>${esc((r.operator_code||'')+' '+(r.recharge_type||''))}</td>
            <td>${esc(r.mobile)}<br>${esc(r.circle||'')}</td><td>${Number(r.amount||0).toFixed(2)}</td>
            <td><span class="rr-status-badge ${statusCls}">${esc(r.status||'')}</span></td>
            <td>${esc(r.api_provider||r.route_name||'')}</td>
            <td>${purchase.toFixed(3)}<br>${r.amount?((purchase/Number(r.amount))*100).toFixed(2):'0.00'}%</td>
            <td>${salePct.toFixed(2)} %</td><td>${profit.toFixed(2)}</td>
            <td><div class="rr-actions"><button class="rr-a-blue" onclick="openAction(${r.id})">Action</button><button class="rr-a-orange" onclick="openResponse(${r.id})">Response</button><button class="rr-a-cyan" onclick="openResponse(${r.id})">OfferResp</button></div></td>
        </tr>`;
    }).join('');
}

function renderRows(){
    const body=document.getElementById('rr-body');
    if(!rows.length){body.innerHTML='<tr><td colspan="11" style="text-align:center;padding:30px;color:#777">No records found.</td></tr>';return}
    body.innerHTML=rows.map((r,i)=>{
        const d=r.created_at?new Date(r.created_at):null;
        const status=String(r.status||'pending').toLowerCase();
        const service=r.recharge_type||'Mobile';
        const opState=[r.operator_code,r.circle].filter(Boolean).join(' / ');
        return `<tr>
            <td>${i+1}</td>
            <td><div class="rpt-id" onclick="openLog(${r.id})">${esc(r.id)}</div></td>
            <td><div class="rpt-main">${d?d.toLocaleDateString('en-IN').replaceAll('/','-'):''}</div><div class="rpt-muted">${d?d.toLocaleTimeString('en-IN',{hour12:false}):''}</div></td>
            <td><div class="rpt-main">${esc(r.user_name||'')}</div><div class="rpt-muted">${esc(r.user_mobile||r.user_phone||'')}</div></td>
            <td><div class="rpt-main">${esc(r.mobile)}</div><div class="rpt-muted">${esc(opState)}</div></td>
            <td class="rpt-amount">${money(r.amount)}</td>
            <td><span class="rpt-status ${esc(status)}">${esc(status.charAt(0).toUpperCase()+status.slice(1))}</span></td>
            <td><div class="rpt-service"><span class="rpt-service-icon">▯</span><div><div class="rpt-main">${esc(service.charAt(0).toUpperCase()+service.slice(1))}</div><div class="rpt-muted">${esc(r.circle||'')}</div></div></div></td>
            <td><span class="rpt-opid">${esc(r.operator_ref||'-')}</span></td>
            <td><div class="rpt-main">${esc(r.api_provider||r.route_name||'')}</div></td>
            <td><div class="rpt-action-group"><button class="rpt-action" onclick="openAction(${r.id})">⚙ Action</button><button class="rpt-action" onclick="openResponse(${r.id})">▣</button></div></td>
        </tr>`;
    }).join('');
}

function openAction(id){currentTxn=rows.find(r=>r.id===id); if(!currentTxn)return; document.getElementById('am-user').textContent=currentTxn.user_name||''; document.getElementById('am-op').textContent=(currentTxn.operator_code||'')+' '+(currentTxn.recharge_type||''); document.getElementById('am-mobile').textContent=currentTxn.mobile||''; document.getElementById('am-amount').textContent=Number(currentTxn.amount||0).toFixed(2); document.getElementById('am-remarks').value=''; document.getElementById('action-modal').style.display='flex';}
function closeAction(){document.getElementById('action-modal').style.display='none'}
function closeLog(){document.getElementById('log-modal').style.display='none'}

async function doAction(type){
    if(!currentTxn)return; const remarks=document.getElementById('am-remarks').value; let url=`/api/v1/employee/recharges/${currentTxn.id}/${type}`, body={remarks};
    if(type==='status'){const st=prompt('Enter status: pending, processing, success, failed, refunded', currentTxn.status||'pending'); if(!st)return; body.status=st;}
    const res=await apiFetch(url,{method:'POST',body:JSON.stringify(body)}); const j=await res.json().catch(()=>({})); alert(j.message||'Done'); closeAction(); loadReport();
}
async function sendToApi(){if(!currentTxn)return; const route_id=document.getElementById('am-api').value; if(!route_id){alert('Select API');return} const remarks=document.getElementById('am-remarks').value; const res=await apiFetch(`/api/v1/employee/recharges/${currentTxn.id}/send-api`,{method:'POST',body:JSON.stringify({route_id,remarks})}); const j=await res.json().catch(()=>({})); alert(j.message||'Sent'); closeAction(); loadReport();}

async function openLog(id){await loadLog(id,'Request/Response Log')}
async function openResponse(id){await loadLog(id,'Response')}
function fmtLogDate(s){ if(!s)return 'N/A'; const d=new Date(s); return isNaN(d)?s:d.toLocaleString('en-IN',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:true}); }
function copyLogText(id){ const el=document.getElementById(id); if(el) navigator.clipboard?.writeText(el.textContent||''); }
function normalizeLogAttempt(a,tx,i){ const payload=a.request_payload?jsonText(a.request_payload):''; const req=[]; if(a.request_url)req.push(a.request_url); if(payload)req.push(payload); return {id:a.id||'—',number:i+1,label:a.log_label||(i?'Status Check':'Initial Request'),api:`${a.api_provider||tx.api_provider||tx.route_name||tx.operator_code||''}`.trim()||'N/A',recharge:a.created_at||tx.created_at,request:req.join('\n')||'No request log found',response:a.response_payload?jsonText(a.response_payload):(a.error_message||tx.failure_reason||''),status:a.status||tx.status||'pending'}; }
function renderApiLogView(tx,logs){ const first=logs[0]||{}, last=logs[logs.length-1]||{}; const timeline=logs.map(l=>`<div class="api-timeline-item"><div class="api-timeline-dot"></div><div class="api-timeline-time">${esc(fmtLogDate(l.recharge))}</div><div class="api-timeline-label">${esc(l.label)} · ${esc(l.status)}</div></div>`).join(''); const cards=logs.map((l,i)=>{const reqId=`api-req-${tx.id}-${i}`,resId=`api-res-${tx.id}-${i}`; return `<div class="api-log-card"><div class="api-log-head"><div class="api-log-title">Log #${l.number}</div><span class="api-chip">${esc(fmtLogDate(l.recharge))}</span><span class="api-chip">${esc(l.label)}</span><span class="api-chip orange">${esc(l.status)}</span><span class="api-log-id"># ID: ${esc(l.id)}</span></div><div class="api-log-grid"><div class="api-pane request"><div class="api-pane-title"><span>Request</span><button class="api-copy" onclick="copyLogText('${reqId}')">Copy</button></div><pre class="api-code" id="${reqId}">${esc(l.request)}</pre></div><div class="api-pane response"><div class="api-pane-title"><span>Response</span><button class="api-copy" onclick="copyLogText('${resId}')">Copy</button></div><pre class="api-code" id="${resId}">${esc(l.response||'No response captured')}</pre></div></div></div>`;}).join(''); return `<div class="api-summary-grid"><div class="api-summary-card"><div class="api-summary-icon">ID</div><div><div class="api-summary-label">Operator ID</div><div class="api-summary-value">${esc(tx.operator_ref||tx.api_ref||'N/A')}</div></div></div><div class="api-summary-card"><div class="api-summary-icon">API</div><div><div class="api-summary-label">API</div><div class="api-summary-value">${esc(first.api||'N/A')}</div></div></div><div class="api-summary-card"><div class="api-summary-icon">DT</div><div><div class="api-summary-label">Created Date</div><div class="api-summary-value">${esc(fmtLogDate(tx.created_at||first.recharge))}</div></div></div><div class="api-summary-card"><div class="api-summary-icon">UP</div><div><div class="api-summary-label">Last Updated</div><div class="api-summary-value">${esc(fmtLogDate(tx.updated_at||last.recharge))}</div></div></div></div><div class="api-section-title">API Request/Response Logs</div><div class="api-section-sub">Total Logs: ${logs.length}</div><div class="api-timeline">${timeline}</div>${cards}`; }
async function loadLog(id,title){
    document.getElementById('log-title').textContent = 'Recharge Details & API Logs';
    document.getElementById('log-body').innerHTML = '<div style="padding:30px;color:var(--text-secondary)">Loading logs...</div>';
    document.getElementById('log-modal').style.display = 'flex';

    const res = await apiFetch(`/api/v1/employee/recharges/${id}`);
    const j   = await res.json();
    const d   = j.data || {};
    const tx  = d.transaction || {};
    const attempts = d.attempts || [];
    const logs = attempts.length ? attempts.map((a, i) => normalizeLogAttempt(a, tx, i)) : [{
        id: '—', number: 1, label: 'No logs found', api: tx.api_provider || tx.operator_code || 'N/A',
        recharge: tx.created_at, request: 'No request log found',
        response: jsonText(tx.operator_response) || tx.failure_reason || '', status: tx.status || 'pending'
    }];
    document.getElementById('log-body').innerHTML = renderApiLogView(tx, logs);
    return;
    attempts.length ? attempts.map(a => {
        const requestPayload = a.request_payload ? jsonText(a.request_payload) : '';
        const requestLines = [`[${(a.log_type || 'recharge').toUpperCase()}] ${a.log_label || ''}`.trim()];
        if (a.request_url) requestLines.push(a.request_url);
        if (requestPayload) requestLines.push(requestPayload);

        return {
            id: a.id,
            type: a.log_type || 'recharge',
            label: a.log_label || 'Recharge',
            api: `${a.api_provider || a.operator_code || ''}`.trim() || '—',
            recharge: a.created_at,
            request: requestLines.join('\\n'),
            response: a.response_payload ? jsonText(a.response_payload) : (a.error_message || ''),
        };
    }) : [{
        id: '—',
        type: 'none',
        label: 'No logs found',
        api: tx.api_provider || tx.operator_code || '—',
        recharge: tx.created_at,
        request: 'No request log found',
        response: jsonText(tx.operator_response) || tx.failure_reason || '',
    }];
    document.getElementById('log-body').innerHTML = logs.map(l =>
        `<tr><td>${esc(l.id)}</td><td>${esc(l.type)}</td><td>${esc(l.label)}</td><td>${esc(l.api)}</td><td>${esc(tx.id||'')}</td><td>${esc(l.recharge||'')}</td><td><pre>${esc(l.request)}</pre></td><td><pre>${esc(l.response)}</pre></td></tr>`
    ).join('');
}
function exportReport(){window.open('/api/v1/employee/reports/recharges?'+params().toString()+'&export=csv','_blank')}
document.addEventListener('DOMContentLoaded',bootFilters);
</script>
@endpush
