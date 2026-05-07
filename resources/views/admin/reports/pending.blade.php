@extends('layouts.admin')
@section('title', 'Pending Recharge Report')
@section('page-title', 'Pending Recharge Report')

@section('content')
<style>
.rr-card{background:var(--card-bg);border:1px solid var(--border);border-radius:4px;margin-bottom:18px;color:var(--text-primary)}
.rr-head{padding:13px 14px;border-bottom:1px solid var(--border);font-size:12px;font-weight:800;text-transform:uppercase;color:var(--text-primary)}
.rr-filters{display:grid;grid-template-columns:100px 100px 110px 120px 130px 130px 120px 120px auto auto;gap:12px;align-items:end;padding:20px 10px 22px}
.rr-field label{display:block;font-size:16px;font-weight:700;color:var(--text-secondary);margin-bottom:8px}
.rr-field input,.rr-field select{width:100%;height:28px;border:1px solid var(--border);padding:3px 8px;font-size:12px;background:var(--card-bg);color:var(--text-primary)}
.rr-blue{background:#0068ce;color:#fff;border:none;border-radius:4px;padding:7px 12px;font-weight:700;cursor:pointer}
.rr-green{background:#10b900;color:#fff;border:none;border-radius:4px;padding:7px 12px;font-weight:700;cursor:pointer}
.rr-table-wrap{overflow:auto;padding:0 6px 14px;-webkit-overflow-scrolling:touch}
.rr-table{width:100%;border-collapse:collapse;min-width:1500px}
.rr-table th{font-size:13px;font-weight:800;text-align:left;padding:14px 12px;border-top:1px solid var(--border);border-bottom:1px solid var(--border);color:var(--text-primary)}
.rr-table td{font-size:14px;vertical-align:top;padding:10px 8px;border-bottom:1px solid var(--border);color:var(--text-primary)}
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
.rr-inline-wrap input{height:26px;border:1px solid var(--border);padding:2px 6px;font-size:12px;width:100%;background:var(--card-bg);color:var(--text-primary)}
.rr-inline-wrap select{height:26px;border:1px solid var(--border);padding:2px 4px;font-size:11px;width:100%;background:var(--card-bg);color:var(--text-primary)}
.rr-inline-wrap button{background:#0068ce;color:#fff;border:none;font-size:11px;font-weight:800;padding:4px 0;border-radius:3px;cursor:pointer}
.rr-modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,.35);z-index:700;align-items:center;justify-content:center}
.rr-box{background:var(--card-bg);color:var(--text-primary);border:1px solid var(--border);min-width:560px;max-width:96vw}
.rr-box-head{display:flex;align-items:center;justify-content:space-between;padding:12px;background:var(--card-bg);color:var(--text-secondary);font-size:20px;font-weight:800}
.rr-box-body{padding:20px 12px}
.rr-box-foot{display:flex;justify-content:flex-end;gap:6px;padding:16px 12px;background:var(--card-bg)}
.rr-box textarea{width:400px;height:48px}
.rr-box select{height:31px}
.rr-box-foot button{border:none;color:#fff;border-radius:4px;padding:8px 13px;font-weight:800;cursor:pointer}
.rr-log .rr-box{width:1200px;max-width:98vw;max-height:88vh;overflow:auto;background:var(--card-bg)}
.rr-log .rr-box-head{background:linear-gradient(90deg,#06b6d4,#0891b2);color:#03121f;font-size:20px}
.api-log-body{padding:18px 16px;background:var(--card-bg)}
.api-summary-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:22px}
.api-summary-card{display:flex;align-items:center;gap:12px;background:var(--bg-page);border:1px solid var(--border);border-radius:8px;padding:14px}
.api-summary-icon{width:40px;height:40px;border-radius:8px;display:flex;align-items:center;justify-content:center;background:#eef2ff;color:#2563eb;font-weight:900}
.api-summary-label{font-size:12px;color:var(--text-secondary);margin-bottom:4px}.api-summary-value{font-size:14px;font-weight:800;color:var(--text-primary)}
.api-section-title{font-size:15px;font-weight:900;color:var(--text-primary);margin:20px 0 4px}.api-section-sub{font-size:12px;color:var(--text-secondary);margin-bottom:14px}
.api-timeline{display:flex;gap:28px;overflow-x:auto;border:1px solid var(--border);border-radius:10px;padding:28px 22px;margin-bottom:24px;background:var(--card-bg)}
.api-timeline-item{min-width:180px;text-align:center;position:relative}.api-timeline-dot{width:14px;height:14px;border-radius:50%;background:#67e8f9;margin:0 auto 8px;box-shadow:0 0 0 10px rgba(103,232,249,.22)}
.api-timeline-time{display:inline-block;background:#06a7d6;color:#fff;border-radius:6px;padding:5px 10px;font-size:12px;font-weight:800}.api-timeline-label{margin-top:8px;border:1px solid #06a7d6;color:#0284c7;border-radius:5px;padding:6px 8px;font-size:12px;font-weight:700;background:rgba(6,182,212,.08)}
.api-log-card{border:1px solid var(--border);border-radius:10px;overflow:hidden;margin-bottom:18px;background:var(--card-bg);box-shadow:var(--shadow-sm)}
.api-log-head{display:flex;align-items:center;gap:12px;flex-wrap:wrap;background:linear-gradient(90deg,#5b6ff0,#7c3aed);color:#fff;padding:13px 16px}.api-log-title{font-size:15px;font-weight:900}.api-chip{border-radius:5px;padding:4px 9px;font-size:11px;font-weight:800;background:rgba(255,255,255,.18)}.api-chip.orange{background:#fb923c;color:#111827}.api-log-id{margin-left:auto;background:#fff;color:#111827;border-radius:5px;padding:4px 10px;font-size:12px;font-weight:800}
.api-log-grid{display:grid;grid-template-columns:1fr 1fr;gap:0}.api-pane{padding:18px;border-right:1px solid var(--border)}.api-pane:last-child{border-right:none}.api-pane-title{display:flex;align-items:center;justify-content:space-between;font-size:15px;font-weight:900;margin-bottom:12px}.api-pane.request .api-pane-title{color:#3b82f6}.api-pane.response .api-pane-title{color:#22c55e}
.api-copy{border:1px solid currentColor;background:transparent;border-radius:5px;padding:5px 12px;font-size:12px;cursor:pointer;color:inherit}.api-code{white-space:pre-wrap;word-break:break-word;font-family:Consolas,monospace;font-size:12px;line-height:1.55;color:var(--text-primary);margin:0}
.rr-pager{display:flex;justify-content:space-between;align-items:center;padding:10px 14px;border-top:1px solid var(--border);font-size:12.5px;color:var(--text-secondary)}
.pr-page-head{margin:8px 0 24px}
.pr-title{display:flex;align-items:center;gap:10px;font-size:22px;font-weight:900;color:var(--text-primary)}
.pr-crumbs{display:flex;gap:8px;align-items:center;color:#3b82f6;font-size:14px;margin-top:2px}
.pr-panel{background:var(--card-bg);border:1px solid var(--border);border-radius:6px;box-shadow:var(--shadow-sm);margin-bottom:22px;padding:16px}
.pr-panel-title{display:flex;align-items:center;gap:8px;font-size:14px;font-weight:900;color:var(--text-primary);text-transform:uppercase;margin-bottom:26px}
.pr-stat-grid{display:grid;grid-template-columns:repeat(5,minmax(170px,1fr));gap:16px;overflow-x:auto;padding:8px 0 10px}
.pr-stat{min-height:108px;border:1px solid #bfdbfe;border-radius:10px;background:linear-gradient(135deg,#eff6ff,#eef5ff);display:flex;align-items:center;gap:16px;padding:16px;color:#111827}
.pr-stat:first-child{border-color:#86c8aa;background:linear-gradient(135deg,#e7f7ee,#eef8f2)}
.pr-stat-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:#bfdbfe;color:#1677ff;font-size:21px;font-weight:900;flex:0 0 auto}
.pr-stat:first-child .pr-stat-icon{background:#b7e3c9;color:#07945b}
.pr-stat-name{font-weight:900;font-size:14px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:150px}
.pr-stat-sub{font-size:13px;color:#7c8aa5;margin-top:4px}.pr-stat-amount{font-size:20px;font-weight:800;color:#4f8cff;margin-top:2px}.pr-stat:first-child .pr-stat-amount{color:#00c775}
.pr-loaded-filter{max-width:480px}.pr-loaded-label{display:flex;align-items:center;gap:6px;font-size:13px;font-weight:700;color:var(--text-primary);margin-bottom:10px}
.pr-searchbox{display:flex;border:1px solid var(--border);border-radius:4px;overflow:hidden;background:var(--card-bg)}.pr-searchbox span{width:38px;display:flex;align-items:center;justify-content:center;border-right:1px solid var(--border);color:#64748b}.pr-searchbox input{height:34px;border:0;outline:0;background:transparent;color:var(--text-primary);font-size:13px;width:100%;padding:0 10px}
.pr-record-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px}.pr-record-title{display:flex;align-items:center;gap:8px;font-size:22px;font-weight:700;color:var(--text-primary)}.pr-check-all{border:0;border-radius:5px;background:#22c55e;color:#fff;font-size:13px;font-weight:800;padding:8px 13px;cursor:pointer}
.pr-legend{background:#eff6ff;border:1px solid #d5e7ff;border-radius:4px;color:#3b82f6;padding:12px 14px;margin-bottom:16px;font-size:12px}.pr-legend-title{font-weight:900;margin-right:22px}.pr-legend-dot{display:inline-block;width:4px;height:22px;border-radius:999px;margin:0 18px 0 8px;vertical-align:middle}.pr-dot-pink{background:#ffd6e7}.pr-dot-grey{background:#e5e7eb}.pr-dot-orange{background:#f6d69b;width:20px;height:20px}
.pr-table{min-width:1420px;border-collapse:separate;border-spacing:0}.pr-table th{background:linear-gradient(180deg,#1677ff,#1157bd);color:#fff;font-size:14px;text-align:center;padding:14px 12px;border-right:1px solid rgba(255,255,255,.22);text-transform:uppercase}.pr-table th:first-child{border-radius:6px 0 0 0}.pr-table th:last-child{border-radius:0 6px 0 0}.pr-table td{background:rgba(255,255,255,.78);border-right:1px solid var(--border);border-bottom:1px solid var(--border);font-size:14px;color:var(--text-primary);padding:14px 16px;vertical-align:middle}.pr-row-old td{background:#f3f4f6}.pr-row-high td{background:#fff6e6}.pr-rec-link{color:#3b82f6;font-weight:800;cursor:pointer}.pr-date-main{font-weight:900;color:#000}.pr-date-sub{font-size:12px;color:#7c8aa5}.pr-agent-name{font-weight:800;color:#000}.pr-agent-phone{font-weight:800;color:#000}.pr-op-wrap{display:flex;align-items:center;gap:14px}.pr-op-icon{width:36px;height:36px;border-radius:999px;background:#6d5dd3;color:#fff;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 10px rgba(109,93,211,.32)}.pr-op-name{font-weight:900;color:#000}.pr-api-badge{display:inline-block;background:#3b82f6;color:#fff;border-radius:4px;padding:2px 8px;font-size:11px;font-weight:800;margin-top:4px}.pr-mobile{font-weight:800;color:#000}.pr-mobile:before{content:"\260E";color:#22c55e;margin-right:6px}.pr-amount{font-weight:900;color:#00c775}.pr-status-pill{display:inline-flex;align-items:center;gap:5px;border:1px solid #f59e0b;color:#b45309;background:#fff7ed;border-radius:6px;padding:6px 12px;font-size:12px;font-weight:800}.pr-operator-input{width:124px;height:34px;border:1px solid var(--border);border-radius:5px;background:var(--card-bg);color:var(--text-primary);padding:0 10px}.pr-action-grid{display:grid;grid-template-columns:58px 42px;gap:4px}.pr-action-grid button,.pr-action-grid select{height:28px;border-radius:4px;font-size:12px}.pr-action-select{border:1px solid #3b82f6;color:#3b82f6;background:var(--card-bg)}.pr-action-upload{border:0;background:#fb923c;color:#fff}.pr-action-copy{border:1px solid #3b82f6;color:#3b82f6;background:transparent}.pr-action-response{border:1px solid #a855f7;color:#a855f7;background:transparent}
html[data-dark="1"] .pr-stat,html[data-dark="1"] .pr-stat:first-child{background:#111827;border-color:#334155;color:var(--text-primary)}html[data-dark="1"] .pr-table td{background:#111827}html[data-dark="1"] .pr-row-old td{background:#172033}html[data-dark="1"] .pr-row-high td{background:#2b2112}html[data-dark="1"] .pr-date-main,html[data-dark="1"] .pr-agent-name,html[data-dark="1"] .pr-agent-phone,html[data-dark="1"] .pr-op-name,html[data-dark="1"] .pr-mobile{color:var(--text-primary)}html[data-dark="1"] .pr-legend{background:#10223d;border-color:#1d4ed8}
@media(max-width:1200px){.rr-filters{grid-template-columns:repeat(3,1fr)}}
@media(max-width:720px){
    .rr-card{border-radius:8px;margin-bottom:12px;max-width:100%;overflow:hidden}
    .rr-head{padding:10px 12px}
    .rr-filters{grid-template-columns:1fr;gap:10px;padding:12px}
    .rr-field label{font-size:12px;margin-bottom:5px}
    .rr-field input,.rr-field select{height:38px;font-size:14px;min-width:0}
    .rr-blue,.rr-green{width:100%;min-height:38px}
    .rr-table-wrap{padding:0 0 10px;max-width:100vw}
    .rr-table{min-width:1050px}
    .rr-table th,.rr-table td{font-size:12px;padding:10px 8px}
    .rr-inline-wrap{min-width:115px}
    .rr-box{min-width:0;width:calc(100vw - 24px);max-height:86vh;overflow:auto}
    .rr-box-body,.rr-box-foot{padding:12px}
    .rr-box textarea{width:100%}
    .rr-box-foot{flex-direction:column}
    .rr-box-foot button{width:100%;min-height:36px}
    .rr-pager{flex-direction:column;align-items:stretch;gap:8px}
    .api-log-body{padding:12px}
    .api-summary-grid{grid-template-columns:1fr;gap:10px}
    .api-timeline{padding:22px 14px;gap:18px}
    .api-log-grid{grid-template-columns:1fr}
    .api-pane{border-right:none;border-bottom:1px solid var(--border);padding:14px}
    .api-pane:last-child{border-bottom:none}
    .api-log-id{margin-left:0}
    .pr-title{font-size:18px}.pr-crumbs{font-size:12px;flex-wrap:wrap}.pr-panel{padding:12px;margin-bottom:14px}.pr-stat-grid{grid-template-columns:repeat(5,220px);gap:12px}.pr-record-head{align-items:flex-start;gap:10px}.pr-record-title{font-size:18px}.pr-check-all{white-space:nowrap}.pr-table{min-width:1180px}.pr-table th,.pr-table td{font-size:12px;padding:10px 8px}.pr-action-grid{grid-template-columns:52px 38px}.pr-legend{font-size:11px;line-height:1.8}
}
</style>

<div class="pr-page-head">
    <div class="pr-title"><span style="color:#fb923c">⌛</span> Pending Recharge Report</div>
    <div class="pr-crumbs"><span>⌂ Dashboard</span><span>»</span><span>Reports</span><span>»</span><strong style="color:var(--text-primary)">Pending Recharge Report</strong></div>
</div>

<div class="pr-panel">
    <div class="pr-panel-title"><span style="color:#3b82f6">▮</span> Operator Statistics</div>
    <div class="pr-stat-grid" id="operator-stats">
        <div class="pr-stat"><div class="pr-stat-icon">▦</div><div><div class="pr-stat-name">Total Pending</div><div class="pr-stat-sub">0 Pending</div><div class="pr-stat-amount">₹0.00</div></div></div>
    </div>
</div>

{{-- Pending count badge --}}
<div style="display:none;margin-bottom:12px">
    <button style="background:#f59e0b;color:#fff;border:none;border-radius:4px;padding:7px 16px;font-weight:800;font-size:14px;cursor:default">
        Pending : <span id="pending-count">—</span>
    </button>
</div>

{{-- Search Filters --}}
<div class="rr-card" style="display:none">
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

<div class="pr-panel">
    <div class="pr-panel-title" style="margin-bottom:26px"><span style="color:#3b82f6">▼</span> Record Filter</div>
    <div class="pr-loaded-filter">
        <label class="pr-loaded-label" for="loaded-search"><span style="color:#3b82f6">⌕</span> Filter loaded records</label>
        <div class="pr-searchbox"><span>⌘</span><input id="loaded-search" type="search" placeholder="Search loaded rows: mobile, ID, operator, agent, API, amount..."></div>
    </div>
</div>

{{-- Report Table --}}
<div class="pr-panel">
    <div class="pr-record-head">
        <div class="pr-record-title"><span>▦</span> Pending Recharge Records</div>
        <button class="pr-check-all" onclick="checkAllLoadedStatus()">⊙ Check All Status</button>
    </div>
    <div class="pr-legend">
        <span class="pr-legend-title">ⓘ Color Coding:</span>
        <span class="pr-legend-dot pr-dot-pink"></span> Pink: Re-root (edit_date = 3 or retry = yes)
        <span class="pr-legend-dot pr-dot-grey"></span> Grey: Old recharge (5+ minutes old)
        <span class="pr-legend-dot pr-dot-orange"></span> Orange: Big amount (&gt;₹500) pending &gt;30 min
    </div>
    <div class="rr-table-wrap">
        <table class="rr-table pr-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>▤ Rec.ID</th>
                    <th>▦ Date</th>
                    <th>▣ Agent</th>
                    <th>▦ Operator / API</th>
                    <th>▯ Mobile</th>
                    <th>₹ Amount</th>
                    <th>⌛ Status</th>
                    <th>⚿ Operator ID</th>
                    <th>⚙ Actions</th>
                </tr>
            </thead>
            <tbody id="rr-body">
                <tr><td colspan="10" style="text-align:center;padding:30px;color:#777">Loading...</td></tr>
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
            <span id="log-title">Recharge Details & API Logs</span>
            <button onclick="closeLog()" style="border:none;background:none;color:#fff;font-size:18px;cursor:pointer">×</button>
        </div>
        <div id="log-body" class="api-log-body"></div>
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
let loadedSearch = '';
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
document.addEventListener('input', e => {
    if (e.target && e.target.id === 'loaded-search') {
        loadedSearch = e.target.value.trim().toLowerCase();
        renderRows();
    }
});

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
        '<tr><td colspan="10" style="text-align:center;padding:30px;color:#777">Loading...</td></tr>';

    const res  = await apiFetch('/api/v1/employee/reports/pending?' + p.toString());
    if (!res) return;
    const data = await res.json();

    const pagination = data.data || {};
    rows     = pagination.data || [];
    lastMeta = { total: pagination.total, last_page: pagination.last_page, from: pagination.from, to: pagination.to };

    const stats = data.stats || {};
    const pendingCountEl = document.getElementById('pending-count');
    const rrCountEl = document.getElementById('rr-count');
    if (pendingCountEl) pendingCountEl.textContent = stats.total || 0;
    if (rrCountEl) rrCountEl.textContent = pagination.total || rows.length;

    renderOperatorStats(stats);
    renderRows();
    renderPager(lastMeta);
}

function money(v){ return '₹' + Number(v || 0).toFixed(2); }
function rowSearchText(r){
    return [r.id,r.mobile,r.operator_code,r.recharge_type,r.amount,r.status,r.api_provider,r.route_name,r.seller_name,r.seller_email,r.operator_ref]
        .map(v => String(v ?? '').toLowerCase()).join(' ');
}
function filteredRows(){
    return loadedSearch ? rows.filter(r => rowSearchText(r).includes(loadedSearch)) : rows;
}
function renderOperatorStats(stats){
    const map = {};
    rows.forEach(r => {
        const key = (r.operator_code || 'Unknown').trim() || 'Unknown';
        if (!map[key]) map[key] = {name:key, count:0, amount:0, type:r.recharge_type || ''};
        map[key].count += 1;
        map[key].amount += Number(r.amount || 0);
    });
    const totalCount = Number(stats.total || rows.length || 0);
    const totalAmount = Number(stats.total_amount || rows.reduce((sum,r)=>sum+Number(r.amount||0),0));
    const cards = [
        `<div class="pr-stat"><div class="pr-stat-icon">▦</div><div><div class="pr-stat-name">Total Pending</div><div class="pr-stat-sub">${totalCount} Pending</div><div class="pr-stat-amount">${money(totalAmount)}</div></div></div>`
    ].concat(Object.values(map).sort((a,b)=>b.count-a.count).slice(0,4).map(o =>
        `<div class="pr-stat"><div class="pr-stat-icon">▯</div><div><div class="pr-stat-name">${esc(o.name + (o.type ? ' - ' + o.type : ''))}</div><div class="pr-stat-sub">${o.count} Pending</div><div class="pr-stat-amount">${money(o.amount)}</div></div></div>`
    ));
    document.getElementById('operator-stats').innerHTML = cards.join('');
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

function renderRows(){
    const body = document.getElementById('rr-body');
    const viewRows = filteredRows();
    if (!viewRows.length){
        body.innerHTML = '<tr><td colspan="10" style="text-align:center;padding:30px;color:#777">No pending transactions found.</td></tr>';
        return;
    }
    body.innerHTML = viewRows.map((r, idx) => {
        const d = r.created_at ? new Date(r.created_at) : null;
        const dateStr = d ? d.toLocaleDateString('en-IN') : '';
        const timeStr = d ? d.toLocaleTimeString('en-IN', {hour:'2-digit', minute:'2-digit', hour12:true}) : '';
        const age = Number(r.age_minutes || 0);
        const ageText = age >= 60 ? `${Math.floor(age / 60)} hours ago` : `${age} minutes ago`;
        const opcode = esc((r.operator_code || '') + (r.recharge_type ? ' - ' + r.recharge_type : ''));
        const apiName = esc(r.api_provider || r.route_name || 'N/A');
        const operRef = esc(r.operator_ref || '');
        const rowClass = Number(r.amount || 0) > 500 && age > 30 ? 'pr-row-high' : (age > 5 ? 'pr-row-old' : '');
        return `<tr id="row-${r.id}" class="${rowClass}">
            <td style="text-align:center;color:#64748b">${idx + 1}</td>
            <td><div class="pr-rec-link" onclick="openLog(${r.id})">▧ ${r.id}</div></td>
            <td style="text-align:center;white-space:nowrap"><div class="pr-date-main">${dateStr}</div><div class="pr-date-sub">${timeStr}</div><div class="pr-date-sub">${ageText}</div></td>
            <td><div class="pr-agent-name">☻ ${esc(r.seller_name || 'N/A')}</div><div class="pr-agent-phone">${esc(r.seller_email || '')}</div></td>
            <td><div class="pr-op-wrap"><div class="pr-op-icon">▯</div><div><div class="pr-op-name">${opcode || 'N/A'}</div><div class="pr-api-badge">☁ ${apiName}</div></div></div></td>
            <td><span class="pr-mobile">${esc(r.mobile)}</span></td>
            <td class="pr-amount">${money(r.amount)}</td>
            <td><span class="pr-status-pill" onclick="checkStatus(${r.id})" title="Click to check live status">⌛ ${esc(r.status || 'Pending')}</span></td>
            <td>
                <input class="pr-operator-input" type="text" id="oid-${r.id}" value="${operRef}" placeholder="OperatorID">
                <select id="api-${r.id}" style="display:none"><option value="">-- Select Action --</option><option value="__success">Mark Success</option><option value="__failed">Mark Failed</option></select>
            </td>
            <td><div class="pr-action-grid">
                <select class="pr-action-select" onchange="document.getElementById('api-${r.id}').value=this.value"><option value="">⋮</option><option value="__success">Success</option><option value="__failed">Failed</option></select>
                <button class="pr-action-upload" onclick="doRowSend(${r.id})">↥</button>
                <button class="pr-action-copy" onclick="openAction(${r.id})">□</button>
                <button class="pr-action-response" onclick="openResponse(${r.id})">▣</button>
            </div></td>
        </tr>`;
    }).join('');
}

function checkAllLoadedStatus(){
    const ids = filteredRows().slice(0, 10).map(r => r.id);
    if (!ids.length) return;
    ids.forEach((id, i) => setTimeout(() => checkStatus(id), i * 350));
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

function fmtLogDate(s){ if(!s)return 'N/A'; const d=new Date(s); return isNaN(d)?s:d.toLocaleString('en-IN',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:true}); }
function copyLogText(id){ const el=document.getElementById(id); if(el) navigator.clipboard?.writeText(el.textContent||''); }
function normalizeLogAttempt(a,tx,i){ const payload=a.request_payload?jsonText(a.request_payload):''; const req=[]; if(a.request_url)req.push(a.request_url); if(payload)req.push(payload); return {id:a.id||'—',number:i+1,label:a.log_label||(i?'Status Check':'Initial Request'),api:`${a.api_provider||tx.api_provider||tx.route_name||tx.operator_code||''}`.trim()||'N/A',recharge:a.created_at||tx.created_at,request:req.join('\n')||'No request log found',response:a.response_payload?jsonText(a.response_payload):(a.error_message||tx.failure_reason||''),status:a.status||tx.status||'pending'}; }
function renderApiLogView(tx,logs){ const first=logs[0]||{}, last=logs[logs.length-1]||{}; const timeline=logs.map(l=>`<div class="api-timeline-item"><div class="api-timeline-dot"></div><div class="api-timeline-time">${esc(fmtLogDate(l.recharge))}</div><div class="api-timeline-label">${esc(l.label)} · ${esc(l.status)}</div></div>`).join(''); const cards=logs.map((l,i)=>{const reqId=`api-req-${tx.id}-${i}`,resId=`api-res-${tx.id}-${i}`; return `<div class="api-log-card"><div class="api-log-head"><div class="api-log-title">Log #${l.number}</div><span class="api-chip">${esc(fmtLogDate(l.recharge))}</span><span class="api-chip">${esc(l.label)}</span><span class="api-chip orange">${esc(l.status)}</span><span class="api-log-id"># ID: ${esc(l.id)}</span></div><div class="api-log-grid"><div class="api-pane request"><div class="api-pane-title"><span>Request</span><button class="api-copy" onclick="copyLogText('${reqId}')">Copy</button></div><pre class="api-code" id="${reqId}">${esc(l.request)}</pre></div><div class="api-pane response"><div class="api-pane-title"><span>Response</span><button class="api-copy" onclick="copyLogText('${resId}')">Copy</button></div><pre class="api-code" id="${resId}">${esc(l.response||'No response captured')}</pre></div></div></div>`;}).join(''); return `<div class="api-summary-grid"><div class="api-summary-card"><div class="api-summary-icon">ID</div><div><div class="api-summary-label">Operator ID</div><div class="api-summary-value">${esc(tx.operator_ref||tx.api_ref||'N/A')}</div></div></div><div class="api-summary-card"><div class="api-summary-icon">API</div><div><div class="api-summary-label">API</div><div class="api-summary-value">${esc(first.api||'N/A')}</div></div></div><div class="api-summary-card"><div class="api-summary-icon">DT</div><div><div class="api-summary-label">Created Date</div><div class="api-summary-value">${esc(fmtLogDate(tx.created_at||first.recharge))}</div></div></div><div class="api-summary-card"><div class="api-summary-icon">UP</div><div><div class="api-summary-label">Last Updated</div><div class="api-summary-value">${esc(fmtLogDate(tx.updated_at||last.recharge))}</div></div></div></div><div class="api-section-title">API Request/Response Logs</div><div class="api-section-sub">Total Logs: ${logs.length}</div><div class="api-timeline">${timeline}</div>${cards}`; }

async function loadLog(id, title){
    document.getElementById('log-title').textContent = 'Recharge Details & API Logs';
    document.getElementById('log-body').innerHTML    = '<div style="padding:30px;color:var(--text-secondary)">Loading logs...</div>';
    document.getElementById('log-modal').style.display = 'flex';

    const res = await apiFetch(`/api/v1/employee/recharges/${id}`);
    const j   = await res.json();
    const d   = j.data || {};
    const tx  = d.transaction || {};
    const attempts = d.attempts || [];

    const logs = attempts.length
        ? attempts.map((a, i) => normalizeLogAttempt(a, tx, i))
        /*
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
        */
        : [{ id:'—', number:1, label:'No logs found', api:tx.api_provider || tx.operator_code || 'N/A', recharge:tx.created_at, request:'No request log found', response:jsonText(tx.operator_response) || tx.failure_reason || '', status:tx.status || 'pending' }];

    document.getElementById('log-body').innerHTML = renderApiLogView(tx, logs);
    /*
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
    */
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
