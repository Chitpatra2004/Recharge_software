@extends('layouts.admin')
@section('title', 'Payment Request History')
@section('page-title', 'Payment Request History')

@section('content')
<style>
/* ── Nav breadcrumb ────────────────────────────────────── */
.prh-nav{display:flex;gap:6px;align-items:center;flex-wrap:wrap;padding:11px 16px;font-size:13px;border-bottom:1px solid var(--border)}
.prh-nav a{color:var(--accent-blue);text-decoration:none;font-weight:600}.prh-nav a:hover{text-decoration:underline}
.prh-nav .sep{color:var(--text-muted)}
/* ── Filters ────────────────────────────────────────────── */
.prh-filters{display:flex;gap:12px;align-items:flex-end;padding:18px 16px 22px;flex-wrap:wrap}
.prh-field label{display:block;font-size:12px;font-weight:600;color:var(--text-secondary);margin-bottom:5px;text-transform:uppercase;letter-spacing:.4px}
.prh-field input,.prh-field select{height:32px;border:1px solid var(--border);padding:3px 10px;font-size:13px;background:var(--card-bg);color:var(--text-primary);border-radius:6px;min-width:110px}
.prh-btn-search{background:#7c3aed;color:#fff;border:none;border-radius:6px;padding:7px 18px;font-weight:700;font-size:13px;cursor:pointer}
.prh-btn-export{background:#7c3aed;color:#fff;border:none;border-radius:6px;padding:7px 18px;font-weight:700;font-size:13px;cursor:pointer}
/* ── Table — !important overrides admin-layout thead th ── */
.prh-table-wrap{overflow-x:auto}
.prh-table{width:100%;border-collapse:collapse;min-width:1300px}
.prh-table thead th{
    color:#fff !important;
    background:#1a5fb4 !important;
    font-size:11.5px !important;
    font-weight:700 !important;
    padding:11px 10px !important;
    text-transform:uppercase !important;
    letter-spacing:.3px !important;
    border:1px solid #1a4f9c !important;
    white-space:nowrap;
    text-align:left !important;
}
.prh-table td{font-size:13px;padding:9px 10px;border-bottom:1px solid var(--border);vertical-align:middle;color:var(--text-primary)}
.prh-table tbody tr:nth-child(even) td{background:var(--bg-page)}
.prh-table tbody tr:hover td{background:#eff6ff}
/* ── Status badges ─────────────────────────────────────── */
.prh-badge-pending {background:#fef3c7;color:#92400e;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:700}
.prh-badge-approved{background:#d1fae5;color:#065f46;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:700}
.prh-badge-rejected{background:#fee2e2;color:#991b1b;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:700}
.prh-print-btn{background:#0068ce;color:#fff;border:none;border-radius:4px;padding:4px 10px;font-size:12px;font-weight:700;cursor:pointer}
.prh-pager{display:flex;justify-content:space-between;align-items:center;padding:10px 14px;border-top:1px solid var(--border);font-size:12px;color:var(--text-secondary)}
.proof-thumb-sm{width:38px;height:38px;border-radius:4px;object-fit:cover;border:1px solid var(--border);cursor:pointer;vertical-align:middle}
/* ── Print ─────────────────────────────────────────────── */
@media print{body *{visibility:hidden}#printArea,#printArea *{visibility:visible}#printArea{position:fixed;inset:0;padding:30px;background:#fff}}
</style>

{{-- Breadcrumb --}}
<div class="card" style="margin-bottom:16px;padding:0">
    <div class="prh-nav">
        <a href="{{ route('admin.reports.recharges') }}">Recharge List</a>
        <span class="sep">/</span>
        <a href="{{ route('admin.api-logs') }}">Log Inbox</a>
        <span class="sep">/</span>
        <a href="{{ route('admin.operators') }}">Operator Settings</a>
        <span class="sep">/</span>
        <a href="{{ route('admin.sellers') }}">Retailer List</a>
        <span class="sep">/</span>
        <a href="{{ route('admin.sellers') }}">Distributor List</a>
        <span class="sep">/</span>
        <span style="color:var(--text-primary);font-weight:600">Payment Request History</span>
    </div>
</div>

{{-- Search --}}
<div class="card" style="margin-bottom:16px">
    <div class="card-header" style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.5px">Search Report</div>
    <div class="prh-filters">
        <div class="prh-field"><label>From Date</label><input type="date" id="f-from"></div>
        <div class="prh-field"><label>To Date</label><input type="date" id="f-to"></div>
        <div class="prh-field"><label>Search Filter</label>
            <select id="f-status">
                <option value="">ALL</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved / Success</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
        <button class="prh-btn-search" onclick="loadHistory(1)">Search</button>
        <button class="prh-btn-export" onclick="exportCSV()">Export</button>
    </div>
</div>

{{-- History Table --}}
<div class="card">
    <div class="card-header" style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.5px">Payment Request History</div>
    <div class="prh-table-wrap">
        <table class="prh-table">
            <thead>
                <tr>
                    <th style="width:46px"></th>
                    <th>Payment Date</th>
                    <th>Request Id</th>
                    <th>UserName</th>
                    <th>UserType</th>
                    <th>Payment Type</th>
                    <th>Ref.Id / Branch</th>
                    <th>Amount</th>
                    <th>Remark</th>
                    <th>Status</th>
                    <th>Admin Remark</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="prh-body">
                <tr><td colspan="12" style="padding:20px;text-align:center;color:var(--text-muted)">Loading…</td></tr>
            </tbody>
        </table>
    </div>
    <div class="prh-pager" id="prh-pager"></div>
</div>

{{-- Print Modal --}}
<div id="printModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:800;align-items:center;justify-content:center;padding:20px">
    <div class="card" style="width:600px;max-width:95vw;max-height:90vh;overflow:auto">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
            <span style="font-weight:700;font-size:15px;color:var(--text-primary)">Payment Receipt</span>
            <div style="display:flex;gap:8px">
                <button class="btn btn-primary btn-sm" onclick="window.print()">Print</button>
                <button class="btn btn-outline btn-sm" onclick="closePrint()">Close</button>
            </div>
        </div>
        <div id="printArea" style="padding:24px 20px;font-size:13px;color:var(--text-primary)"></div>
    </div>
</div>

{{-- Proof Lightbox --}}
<div id="proofLightbox" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:900;align-items:center;justify-content:center;padding:20px" onclick="this.style.display='none'">
    <img id="proofImg" src="" alt="Proof" style="max-width:90vw;max-height:85vh;border-radius:6px">
</div>

@push('scripts')
<script>
const today = new Date().toISOString().slice(0,10);
document.getElementById('f-from').value = today;
document.getElementById('f-to').value   = today;

function esc(s){ return String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

let currentPage = 1, allRows = [];

function modeLabel(m){
    const map = {bank_transfer:'NEFT/IMPS', upi:'UPI', neft:'NEFT', rtgs:'RTGS', cheque:'CHEQUE', imps:'IMPS'};
    return map[(m||'').toLowerCase()] || (m||'—').toUpperCase();
}
function roleLabel(r){
    const map = {api_user:'APIUSER', retailer:'RETAILER', distributor:'DISTRIBUTOR', buyer:'BUYER', admin:'ADMIN'};
    return map[(r||'').toLowerCase()] || (r||'—').toUpperCase();
}
function statusBadge(s){
    if (s === 'approved') return '<span class="prh-badge-approved">Success</span>';
    if (s === 'rejected') return '<span class="prh-badge-rejected">Rejected</span>';
    return '<span class="prh-badge-pending">Pending</span>';
}

async function loadHistory(page){
    currentPage = page || 1;
    const from   = document.getElementById('f-from').value;
    const to     = document.getElementById('f-to').value;
    const status = document.getElementById('f-status').value;
    const p      = new URLSearchParams({ page: currentPage, per_page: 25 });
    if (from)   p.set('date_from', from);
    if (to)     p.set('date_to', to);
    if (status) p.set('status', status);

    document.getElementById('prh-body').innerHTML =
        '<tr><td colspan="12" style="padding:18px;text-align:center;color:#6b7280">Loading…</td></tr>';

    const res  = await apiFetch('/api/v1/employee/sellers/payment-requests/list?' + p);
    if (!res) return;
    const d    = await res.json();
    allRows    = d.data?.data || [];
    const meta = d.data || {};

    renderRows(allRows);
    renderPager(meta);
}

function renderRows(rows){
    const tbody = document.getElementById('prh-body');
    if (!rows.length){
        tbody.innerHTML = '<tr><td colspan="12" class="prh-no-rec">No Records Found</td></tr>';
        return;
    }
    tbody.innerHTML = rows.map(r => {
        const u = r.user || {};
        const d = r.created_at ? new Date(r.created_at) : null;
        const dtStr = d ? d.toLocaleString('en-IN',{day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:false}) : '—';
        const refBranch = [r.reference_number, r.bank_name].filter(Boolean).join(' / ');
        const proof = r.proof_image
            ? `<img src="/storage/${esc(r.proof_image)}" class="proof-thumb-sm" onclick="viewProof('/storage/${esc(r.proof_image)}')" onerror="this.style.display='none'">`
            : '<span style="font-size:11px;color:#9ca3af">—</span>';
        const dataStr = JSON.stringify(r).replace(/'/g, "\\'");
        return `<tr>
            <td style="text-align:center">${proof}</td>
            <td style="font-size:12px;white-space:nowrap">${dtStr}</td>
            <td style="font-weight:700;color:#0068ce">${r.id}</td>
            <td>
                <div style="font-weight:700;font-size:13px">${esc(u.mobile||'—')}</div>
                <div style="font-size:11.5px;color:#374151">${esc(u.name||'')}</div>
            </td>
            <td style="font-size:12px;font-weight:600;color:#7c3aed">${roleLabel(u.role)}</td>
            <td style="font-size:12.5px;font-weight:600">${modeLabel(r.payment_mode)}</td>
            <td style="font-family:monospace;font-size:12px">${esc(refBranch||'—')}</td>
            <td style="font-weight:700">${Number(r.amount||0).toFixed(2)}</td>
            <td style="font-size:12px;color:#6b7280;max-width:120px;word-break:break-word">${esc(r.notes||'—')}</td>
            <td>${statusBadge(r.status)}</td>
            <td style="font-size:12px;color:#374151;max-width:140px;word-break:break-word">${esc(r.admin_notes||'—')}</td>
            <td><button class="prh-print-btn" onclick='printReceipt(${JSON.stringify(r)})'>Print</button></td>
        </tr>`;
    }).join('');
}

function renderPager(meta){
    const pager = document.getElementById('prh-pager');
    if (!meta.total){ pager.innerHTML = ''; return; }
    const lp   = meta.last_page || 1;
    const curr = meta.current_page || currentPage;
    let btns   = '';
    if (curr > 1)  btns += `<button onclick="loadHistory(${curr-1})" style="padding:3px 10px;border-radius:3px;border:1px solid #d1d5db;background:#fff;cursor:pointer;font-size:12px">← Prev</button>`;
    btns += `<span style="padding:3px 10px;font-size:12px">Page ${curr} / ${lp}</span>`;
    if (curr < lp) btns += `<button onclick="loadHistory(${curr+1})" style="padding:3px 10px;border-radius:3px;border:1px solid #d1d5db;background:#fff;cursor:pointer;font-size:12px">Next →</button>`;
    pager.innerHTML = `<span>Showing ${meta.from||0}–${meta.to||0} of ${meta.total||0} records</span><div style="display:flex;gap:6px;align-items:center">${btns}</div>`;
}

function printReceipt(r){
    const u    = r.user || {};
    const d    = r.created_at ? new Date(r.created_at) : null;
    const dtStr = d ? d.toLocaleString('en-IN',{day:'2-digit',month:'long',year:'numeric',hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:true}) : '—';
    const refBranch = [r.reference_number, r.bank_name].filter(Boolean).join(' / ');
    const modeStr = modeLabel(r.payment_mode);
    const statusStr = r.status === 'approved' ? 'Success' : r.status === 'rejected' ? 'Rejected' : 'Pending';

    document.getElementById('printArea').innerHTML = `
        <div style="text-align:center;margin-bottom:18px;padding-bottom:14px;border-bottom:2px solid #1a5fb4">
            <div style="font-size:22px;font-weight:900;color:#1a5fb4;letter-spacing:1px">ColdPay</div>
            <div style="font-size:12px;color:#6b7280;margin-top:3px">Payment Confirmation Receipt</div>
        </div>
        <table style="width:100%;border-collapse:collapse;font-size:13px">
            <tr style="background:#f3f4f6"><td style="padding:8px 12px;font-weight:700;width:180px;color:#374151">Request ID</td><td style="padding:8px 12px;font-weight:700;color:#0068ce">#${r.id}</td></tr>
            <tr><td style="padding:8px 12px;font-weight:700;color:#374151">Date & Time</td><td style="padding:8px 12px">${dtStr}</td></tr>
            <tr style="background:#f3f4f6"><td style="padding:8px 12px;font-weight:700;color:#374151">Agent Name</td><td style="padding:8px 12px;font-weight:700">${esc(u.name||'—')}</td></tr>
            <tr><td style="padding:8px 12px;font-weight:700;color:#374151">Mobile</td><td style="padding:8px 12px">${esc(u.mobile||'—')}</td></tr>
            <tr style="background:#f3f4f6"><td style="padding:8px 12px;font-weight:700;color:#374151">User Type</td><td style="padding:8px 12px">${roleLabel(u.role)}</td></tr>
            <tr><td style="padding:8px 12px;font-weight:700;color:#374151">Payment Type</td><td style="padding:8px 12px;font-weight:700">${modeStr}</td></tr>
            <tr style="background:#f3f4f6"><td style="padding:8px 12px;font-weight:700;color:#374151">Ref.Id / Branch</td><td style="padding:8px 12px;font-family:monospace">${esc(refBranch||'—')}</td></tr>
            <tr><td style="padding:8px 12px;font-weight:700;color:#374151">Amount</td><td style="padding:8px 12px;font-size:18px;font-weight:900;color:#16a34a">₹${Number(r.amount||0).toFixed(2)}</td></tr>
            <tr style="background:#f3f4f6"><td style="padding:8px 12px;font-weight:700;color:#374151">Remark</td><td style="padding:8px 12px">${esc(r.notes||'—')}</td></tr>
            <tr><td style="padding:8px 12px;font-weight:700;color:#374151">Status</td><td style="padding:8px 12px"><strong style="color:${r.status==='approved'?'#16a34a':r.status==='rejected'?'#dc2626':'#d97706'}">${statusStr}</strong></td></tr>
            <tr style="background:#f3f4f6"><td style="padding:8px 12px;font-weight:700;color:#374151">Admin Remark</td><td style="padding:8px 12px">${esc(r.admin_notes||'—')}</td></tr>
        </table>
        <div style="margin-top:24px;padding-top:12px;border-top:1px dashed #d1d5db;text-align:center;font-size:11px;color:#9ca3af">
            This is a system-generated receipt. No signature required.
        </div>`;
    document.getElementById('printModal').style.display = 'flex';
}

function closePrint(){ document.getElementById('printModal').style.display = 'none'; }

function viewProof(url){
    document.getElementById('proofImg').src = url;
    document.getElementById('proofLightbox').style.display = 'flex';
}

function exportCSV(){
    if (!allRows.length){ alert('No data to export.'); return; }
    const headers = ['ID','Payment Date','UserName','Mobile','UserType','Payment Type','Ref.Id/Branch','Amount','Remark','Status','Admin Remark'];
    const rows = allRows.map(r => {
        const u = r.user || {};
        return [r.id, r.created_at, u.name||'', u.mobile||'', roleLabel(u.role), modeLabel(r.payment_mode),
                [r.reference_number, r.bank_name].filter(Boolean).join('/'), r.amount||0,
                r.notes||'', r.status||'', r.admin_notes||''];
    });
    const csv = [headers, ...rows].map(row => row.map(v => `"${String(v??'').replace(/"/g,'""')}"`).join(',')).join('\n');
    const a   = document.createElement('a');
    a.href    = 'data:text/csv;charset=utf-8,﻿' + encodeURIComponent(csv);
    a.download = 'payment-request-history-' + new Date().toISOString().slice(0,10) + '.csv';
    a.click();
}

document.addEventListener('DOMContentLoaded', () => loadHistory(1));
</script>
@endpush
@endsection
