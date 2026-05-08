@extends('layouts.admin')
@section('title','Payment Gateway Report')
@section('page-title','Payment Gateway Report')

@section('content')
<style>
.pg-head{display:flex;justify-content:space-between;gap:16px;align-items:flex-start;margin-bottom:24px}.pg-title{font-size:20px;font-weight:900;color:var(--text-primary);display:flex;gap:10px;align-items:center}.pg-crumb{display:flex;gap:8px;color:#3b82f6;font-size:14px;margin-top:4px}.pg-actions{display:flex;gap:10px}.pg-card{background:var(--card-bg);border:1px solid var(--border);border-radius:8px;box-shadow:var(--shadow-sm);margin-bottom:22px}.pg-card-head{padding:18px 20px;border-bottom:1px solid var(--border);font-weight:900;color:var(--text-primary);display:flex;gap:10px;align-items:center}.pg-summary{display:grid;grid-template-columns:repeat(6,260px);gap:14px;overflow:auto;padding:22px 18px}.pg-stat{border-radius:10px;border-left:4px solid #10b981;background:var(--card-bg);box-shadow:var(--shadow-sm);padding:18px;display:flex;gap:14px;align-items:center}.pg-stat.pending{border-color:#fb923c}.pg-stat.failed{border-color:#ef4444}.pg-stat.other{border-color:#64748b}.pg-icon{width:48px;height:48px;border-radius:7px;background:#d1fae5;color:#10b981;display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:900}.pg-stat.pending .pg-icon{background:#ffedc2;color:#fb923c}.pg-stat.failed .pg-icon{background:#ffd7dc;color:#ef4444}.pg-stat.other .pg-icon{background:#e5e7eb;color:#64748b}.pg-stat-title{font-size:13px;font-weight:900;color:var(--text-primary);text-transform:uppercase}.pg-stat-count{font-size:20px;font-weight:900;margin-top:6px;color:var(--text-primary)}.pg-stat-amt{font-size:15px;color:#8b9ab5;font-weight:800;margin-top:2px}.pg-filter{padding:28px 32px}.pg-filter-title{display:flex;gap:8px;align-items:center;font-weight:900;margin-bottom:18px}.pg-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px}.pg-field label{display:block;font-size:12px;color:var(--text-secondary);font-weight:900;text-transform:uppercase;margin-bottom:7px}.pg-control{width:100%;height:39px;border:1px solid var(--border);border-radius:6px;background:var(--card-bg);color:var(--text-primary);padding:0 12px}.pg-search-row{display:grid;grid-template-columns:120px 1fr 1fr auto auto;gap:10px;margin-top:14px;align-items:end}.pg-btn{height:38px;border:0;border-radius:5px;padding:0 16px;font-weight:800;cursor:pointer}.pg-btn.primary{background:#3b82f6;color:#fff}.pg-btn.reset{background:transparent;color:#a855f7;border:1px solid #a855f7}.pg-table{width:100%;min-width:1200px;border-collapse:collapse}.pg-table th{background:#f8fafc;color:var(--text-primary);font-size:13px;text-align:left;padding:14px;border-bottom:1px solid var(--border)}.pg-table td{padding:14px;border-bottom:1px solid var(--border);color:var(--text-primary);font-size:13px;vertical-align:middle}.pg-order{font-family:monospace;font-weight:900;color:#2563eb}.pg-status{display:inline-flex;border-radius:999px;padding:5px 10px;font-size:11px;font-weight:900}.pg-status.approved{background:#d1fae5;color:#047857}.pg-status.pending{background:#fef3c7;color:#b45309}.pg-status.rejected{background:#fee2e2;color:#b91c1c}@media(max-width:900px){.pg-head{flex-direction:column}.pg-grid,.pg-search-row{grid-template-columns:1fr}.pg-summary{grid-template-columns:repeat(6,230px)}}
</style>

<div class="pg-head">
    <div>
        <div class="pg-title">▣ Payment Gateway Report</div>
        <div class="pg-crumb"><span>⌂ Dashboard</span><span>»</span><span>Reports</span><span>»</span><strong style="color:var(--text-primary)">Payment Gateway Report</strong></div>
    </div>
    <div class="pg-actions">
        <button class="btn btn-outline" onclick="exportReport()">⇩ Export Report</button>
        <button class="btn btn-outline" onclick="toggleAuto()">⚙ Auto-Download</button>
    </div>
</div>

<div class="pg-card">
    <div class="pg-card-head">⌖ Summary Statistics (Today)</div>
    <div class="pg-summary" id="pgSummary"></div>
</div>

<div class="pg-card">
    <div class="pg-card-head">▣ Report</div>
    <div class="pg-filter">
        <div class="pg-filter-title">⌁ Filter <span style="background:#e8f1ff;color:#3b82f6;border-radius:4px;padding:2px 7px;font-size:11px">Basic</span></div>
        <div class="pg-grid">
            <div class="pg-field"><label>Status</label><select class="pg-control" id="fStatus"><option value="">-- All --</option><option value="success">Success</option><option value="pending">Pending</option><option value="failed">Failed</option></select></div>
            <div class="pg-field"><label>API Gateway</label><select class="pg-control" id="fPg"><option value="">-- All --</option></select></div>
            <div class="pg-field"><label>Payment Mode</label><select class="pg-control" id="fMode"><option value="">-- All --</option></select></div>
        </div>
        <div class="pg-search-row">
            <div class="pg-field"><label>Search</label><select class="pg-control" id="fSearchType"><option value="order">Order ID</option><option value="mobile">Mobile</option><option value="rrn">Bank RRN</option></select></div>
            <input class="pg-control" id="fSearch" placeholder="Enter value">
            <div class="pg-field"><label>Date Range</label><input class="pg-control" id="fDate" type="date"></div>
            <button class="pg-btn primary" onclick="loadPgReport()">⌕ Search</button>
            <button class="pg-btn reset" onclick="resetFilters()">↻ Reset</button>
        </div>
    </div>
</div>

<div class="pg-card">
    <div class="table-wrap">
        <table class="pg-table">
            <thead><tr><th>#</th><th>Order ID</th><th>User / Mobile</th><th>PG Name</th><th>Mode</th><th>Amount</th><th>Status</th><th>Bank RRN</th><th>Payment Date</th><th>Processed</th></tr></thead>
            <tbody id="pgBody"><tr><td colspan="10" style="text-align:center;padding:28px;color:var(--text-muted)">Loading...</td></tr></tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
const API='/api/v1/employee/user-payment-requests/pg-report';
function todayIso(){const d=new Date();return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`}
function esc(v){return String(v??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]))}
function pgLabel(v){return String(v||'').replace(/_/g,' ').toUpperCase()}
function statusLabel(v){return v==='approved'?'Success':v==='rejected'?'Failed':(v||'Pending')}
function query(){const p=new URLSearchParams({per_page:100});const date=document.getElementById('fDate').value;if(date){p.set('date_from',date);p.set('date_to',date)}const st=document.getElementById('fStatus').value,pg=document.getElementById('fPg').value,mode=document.getElementById('fMode').value,search=document.getElementById('fSearch').value.trim();if(st)p.set('status',st);if(pg||mode)p.set('pg_name',pg||mode);if(search)p.set('search',search);return p}
async function loadPgReport(){document.getElementById('pgBody').innerHTML='<tr><td colspan="10" style="text-align:center;padding:28px;color:var(--text-muted)">Loading...</td></tr>';const res=await apiFetch(API+'?'+query().toString());const json=await res.json();const rows=json.data?.data||[];renderSummary(json.gateways||[]);renderRows(rows);fillPgOptions(rows,json.gateways||[])}
function renderSummary(rows){const box=document.getElementById('pgSummary');if(!rows.length){box.innerHTML='<div style="padding:20px;color:var(--text-muted)">No summary found</div>';return}box.innerHTML=rows.map(r=>{const cls=r.status==='approved'?'success':r.status==='pending'?'pending':r.status==='rejected'?'failed':'other';return `<div class="pg-stat ${cls}"><div class="pg-icon">${cls==='success'?'✓':cls==='pending'?'+':cls==='failed'?'△':'⚠'}</div><div><div class="pg-stat-title">${esc(pgLabel(r.pg_name))} ${esc(statusLabel(r.status))}</div><div class="pg-stat-count">${fmtNum(r.count||0)}</div><div class="pg-stat-amt">${fmtAmt(r.amount||0)}</div></div></div>`}).join('')}
function renderRows(rows){const body=document.getElementById('pgBody');if(!rows.length){body.innerHTML='<tr><td colspan="10" style="text-align:center;padding:28px;color:var(--text-muted)">No PG records found</td></tr>';return}body.innerHTML=rows.map((r,i)=>{const u=r.user||{};return `<tr><td>${i+1}</td><td class="pg-order">${esc(r.site_order_id)}</td><td><strong>${esc(u.name||'-')}</strong><div style="font-size:12px;color:var(--text-muted)">${esc(u.mobile||u.email||'-')}</div></td><td>${esc(r.pg_name)}</td><td>${esc(pgLabel(r.payment_mode))}</td><td style="font-weight:900">${fmtAmt(r.amount||0)}</td><td><span class="pg-status ${esc(r.status)}">${esc(statusLabel(r.status))}</span></td><td style="font-family:monospace">${esc(r.bank_rrn||'-')}</td><td>${r.payment_date?new Date(r.payment_date).toLocaleDateString('en-IN'):'-'}</td><td>${r.processed_at?new Date(r.processed_at).toLocaleString('en-IN'):'-'}</td></tr>`}).join('')}
function fillPgOptions(rows,summary){const current=document.getElementById('fPg').value;const vals=[...new Set([...(summary||[]).map(r=>r.pg_name),...rows.map(r=>r.payment_mode)].filter(Boolean))];document.getElementById('fPg').innerHTML='<option value="">-- All --</option>'+vals.map(v=>`<option value="${esc(v)}">${esc(pgLabel(v))}</option>`).join('');document.getElementById('fMode').innerHTML='<option value="">-- All --</option>'+vals.map(v=>`<option value="${esc(v)}">${esc(pgLabel(v))}</option>`).join('');document.getElementById('fPg').value=current}
function resetFilters(){['fStatus','fPg','fMode','fSearch'].forEach(id=>document.getElementById(id).value='');document.getElementById('fDate').value=todayIso();loadPgReport()}
function exportReport(){window.open(API+'?'+query().toString()+'&export=csv','_blank')}
function toggleAuto(){alert('Auto-download setting will be connected when a scheduled PG export is enabled.')}
document.addEventListener('DOMContentLoaded',()=>{document.getElementById('fDate').value=todayIso();loadPgReport()});
</script>
@endpush
