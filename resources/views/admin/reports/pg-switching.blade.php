@extends('layouts.admin')
@section('title','Payment Gateway Switching Report')
@section('page-title','Payment Gateway Switching Report')

@section('content')
<style>
.sw-head{display:flex;justify-content:space-between;gap:16px;margin-bottom:22px}.sw-title{font-size:20px;font-weight:900;color:var(--text-primary)}.sw-crumb{display:flex;gap:8px;color:#3b82f6;font-size:14px;margin-top:4px}.sw-card{background:var(--card-bg);border:1px solid var(--border);border-radius:8px;box-shadow:var(--shadow-sm);margin-bottom:18px}.sw-filter{display:flex;gap:10px;align-items:end;flex-wrap:wrap;padding:16px}.sw-field label{display:block;font-size:12px;color:var(--text-secondary);font-weight:800;margin-bottom:6px}.sw-field input{height:38px;border:1px solid var(--border);border-radius:6px;background:var(--card-bg);color:var(--text-primary);padding:0 12px}.sw-btn{height:38px;border:0;border-radius:6px;background:#3b82f6;color:#fff;font-weight:800;padding:0 16px;cursor:pointer}.sw-table{width:100%;min-width:900px;border-collapse:collapse}.sw-table th{background:#f8fafc;color:var(--text-primary);font-size:13px;text-align:left;padding:14px;border-bottom:1px solid var(--border)}.sw-table td{padding:14px;border-bottom:1px solid var(--border);color:var(--text-primary)}.sw-pill{display:inline-flex;border-radius:999px;padding:5px 10px;font-size:11px;font-weight:900}.sw-pill.primary{background:#d1fae5;color:#047857}.sw-pill.backup{background:#e0e7ff;color:#4338ca}.rate{height:8px;background:#e5e7eb;border-radius:999px;overflow:hidden;margin-top:6px}.rate span{display:block;height:100%;background:#10b981}
</style>

<div class="sw-head">
    <div><div class="sw-title">Payment Gateway Switching Report</div><div class="sw-crumb"><span>⌂ Dashboard</span><span>»</span><span>Reports</span><span>»</span><strong style="color:var(--text-primary)">PG Switching Report</strong></div></div>
    <button class="btn btn-outline" onclick="loadSwitching()">↻ Refresh</button>
</div>

<div class="sw-card">
    <div class="sw-filter">
        <div class="sw-field"><label>From Date</label><input id="fFrom" type="date"></div>
        <div class="sw-field"><label>To Date</label><input id="fTo" type="date"></div>
        <button class="sw-btn" onclick="loadSwitching()">Search</button>
    </div>
</div>

<div class="sw-card">
    <div class="table-wrap">
        <table class="sw-table">
            <thead><tr><th>Priority</th><th>PG Name</th><th>Switch Status</th><th>Total</th><th>Success</th><th>Pending</th><th>Failed</th><th>Success Rate</th><th>Total Amount</th><th>Last Used</th></tr></thead>
            <tbody id="swBody"><tr><td colspan="10" style="text-align:center;padding:28px;color:var(--text-muted)">Loading...</td></tr></tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
const API='/api/v1/employee/user-payment-requests/pg-switching-report';
function todayIso(offset=0){const d=new Date();d.setDate(d.getDate()+offset);return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`}
function esc(v){return String(v??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]))}
function pgLabel(v){return String(v||'').replace(/_/g,' ').toUpperCase()}
async function loadSwitching(){const p=new URLSearchParams();const f=document.getElementById('fFrom').value,t=document.getElementById('fTo').value;if(f)p.set('date_from',f);if(t)p.set('date_to',t);document.getElementById('swBody').innerHTML='<tr><td colspan="10" style="text-align:center;padding:28px;color:var(--text-muted)">Loading...</td></tr>';const res=await apiFetch(API+'?'+p.toString());const json=await res.json();renderRows(json.data||[])}
function renderRows(rows){const body=document.getElementById('swBody');if(!rows.length){body.innerHTML='<tr><td colspan="10" style="text-align:center;padding:28px;color:var(--text-muted)">No gateway switching data found</td></tr>';return}body.innerHTML=rows.map(r=>`<tr><td>#${r.priority}</td><td style="font-weight:900">${esc(pgLabel(r.pg_name))}</td><td><span class="sw-pill ${esc(r.switch_status)}">${esc(String(r.switch_status).toUpperCase())}</span></td><td>${fmtNum(r.total||0)}</td><td>${fmtNum(r.success_count||0)}</td><td>${fmtNum(r.pending_count||0)}</td><td>${fmtNum(r.failed_count||0)}</td><td><strong>${r.success_rate||0}%</strong><div class="rate"><span style="width:${Math.min(Number(r.success_rate||0),100)}%"></span></div></td><td>${fmtAmt(r.total_amount||0)}</td><td>${r.last_used_at?new Date(r.last_used_at).toLocaleString('en-IN'):'-'}</td></tr>`).join('')}
document.addEventListener('DOMContentLoaded',()=>{document.getElementById('fFrom').value=todayIso(-7);document.getElementById('fTo').value=todayIso();loadSwitching()});
</script>
@endpush
