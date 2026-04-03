@extends('layouts.admin')
@section('title','Pending Recharge Report')

@push('head')
<style>
.filter-bar{display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;margin-bottom:18px}
.filter-bar select,.filter-bar input{padding:7px 11px;border:1px solid var(--border);border-radius:8px;font-size:13px;background:#fff;color:#1e293b}
.filter-bar .btn{padding:7px 18px;font-size:13px}
.stat-row{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px}
@media(max-width:900px){.stat-row{grid-template-columns:1fr 1fr}}
.stat-card{background:var(--card-bg);border-radius:var(--radius);padding:16px 20px;box-shadow:var(--shadow-sm);border-left:4px solid transparent}
.stat-card.orange{border-color:var(--accent-orange)}
.stat-card.blue{border-color:var(--accent-blue)}
.stat-card.red{border-color:var(--accent-red)}
.stat-card.purple{border-color:var(--accent-purple)}
.stat-card .val{font-size:22px;font-weight:700;margin-bottom:2px;color:#1e293b}
.stat-card .lbl{font-size:11.5px;color:#64748b}
.badge-pending{background:#fef3c7;color:#92400e;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:600}
.badge-queued{background:#e0f2fe;color:#0369a1;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:600}
.badge-stuck{background:#fee2e2;color:#991b1b;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:600}
.badge-processing{background:#dbeafe;color:#1e40af;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:600}
.action-btn{padding:4px 10px;border-radius:6px;font-size:11.5px;font-weight:600;cursor:pointer;border:1px solid var(--border);background:#fff;color:#1e293b}
.action-btn:hover{background:var(--bg-page)}
.action-btn.refund{color:var(--accent-red);border-color:var(--accent-red)}
.txn-id{font-family:monospace;font-size:11.5px;color:#334155;background:#f1f5f9;padding:2px 6px;border-radius:4px}
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Pending Recharge Report</h1>
        <p class="page-sub">Transactions stuck in pending / queued / processing state</p>
    </div>
    <div style="display:flex;gap:8px">
        <button class="btn btn-outline" onclick="exportPending()">Export CSV</button>
        <button class="btn btn-primary" onclick="loadPending(1)">Refresh</button>
    </div>
</div>

<div class="stat-row">
    <div class="stat-card orange"><div class="val" id="sTotalPending">—</div><div class="lbl">Total Pending</div></div>
    <div class="stat-card blue"><div class="val" id="sPendingAmt">—</div><div class="lbl">Pending Amount (₹)</div></div>
    <div class="stat-card red"><div class="val" id="sStuck">—</div><div class="lbl">Stuck &gt; 30 min</div></div>
    <div class="stat-card purple"><div class="val" id="sProcessing">—</div><div class="lbl">Processing</div></div>
</div>

<div class="card">
    <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
        <span style="font-weight:600;font-size:14px">Pending Transactions</span>
        <div class="filter-bar" style="margin:0">
            <input type="text" id="fOperator" placeholder="Operator (e.g. Airtel)" style="width:140px">
            <select id="fAge">
                <option value="">Any Age</option>
                <option value="5">5+ min</option>
                <option value="10">10+ min</option>
                <option value="30">30+ min</option>
                <option value="60">1+ hour</option>
                <option value="360">6+ hours</option>
            </select>
            <input type="date" id="fDate" value="{{ date('Y-m-d') }}">
            <button class="btn btn-primary" onclick="loadPending(1)">Filter</button>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Txn ID</th>
                    <th>Mobile</th>
                    <th>Operator</th>
                    <th>Amount</th>
                    <th>Seller</th>
                    <th>Initiated</th>
                    <th>Age</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="pendingBody">
                <tr><td colspan="9" style="text-align:center;padding:30px;color:var(--text-muted)">Loading...</td></tr>
            </tbody>
        </table>
    </div>
    <div id="tablePager" style="display:flex;justify-content:space-between;align-items:center;padding:12px 16px;border-top:1px solid var(--border);font-size:12.5px;color:#64748b">
        <span id="pageInfo">—</span>
        <div id="pagerBtns" style="display:flex;gap:6px"></div>
    </div>
</div>

{{-- Refund Confirm Modal --}}
<div id="actionModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:500;align-items:center;justify-content:center">
    <div class="card" style="width:400px;max-width:95vw;padding:24px">
        <h3 id="modalTitle" style="font-size:15px;font-weight:700;margin-bottom:8px">Confirm Action</h3>
        <p id="modalMsg" style="font-size:13px;color:#64748b;margin-bottom:20px"></p>
        <div style="display:flex;gap:10px">
            <button class="btn btn-primary" id="modalConfirm">Confirm</button>
            <button class="btn btn-outline" onclick="document.getElementById('actionModal').style.display='none'">Cancel</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
const TOKEN = () => localStorage.getItem('emp_token');
const empFetch = (url, method='GET', body=null) =>
    fetch(url, {
        method,
        headers: {'Authorization': 'Bearer ' + TOKEN(), 'Content-Type': 'application/json', 'Accept': 'application/json'},
        ...(body ? {body: JSON.stringify(body)} : {})
    }).then(async r => { const d = await r.json(); if (!r.ok) throw new Error(d.message || 'Error'); return d; });

let currentPage = 1;
let lastMeta = {};
let rawRows = [];

function fmtMoney(n){ return Number(n||0).toLocaleString('en-IN', {minimumFractionDigits:2, maximumFractionDigits:2}); }
function fmtDate(d){ if(!d) return '—'; return new Date(d).toLocaleString('en-IN',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:true}); }

function ageLabel(min){
    min = parseInt(min) || 0;
    if(min < 60) return min + 'm';
    return Math.floor(min/60) + 'h ' + (min % 60) + 'm';
}

function statusBadge(status, age){
    if(status === 'queued')     return '<span class="badge-queued">Queued</span>';
    if(status === 'processing') return '<span class="badge-processing">Processing</span>';
    if(parseInt(age) > 30)      return '<span class="badge-stuck">Stuck</span>';
    return '<span class="badge-pending">Pending</span>';
}

function renderTable(rows){
    const tbody = document.getElementById('pendingBody');
    if (!rows || !rows.length) {
        tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;padding:30px;color:var(--text-muted)">No pending transactions found</td></tr>';
        return;
    }
    tbody.innerHTML = rows.map(r => {
        const age = parseInt(r.age_minutes) || 0;
        const isStuck = age > 30;
        return `<tr style="${isStuck ? 'background:#fff5f5' : ''}">
            <td><span class="txn-id">#${r.id}</span></td>
            <td style="font-weight:600;color:#1e293b">${r.mobile}</td>
            <td>${r.operator_code || '—'}</td>
            <td style="font-weight:700;color:#1e293b">₹${fmtMoney(r.amount)}</td>
            <td>
                <div style="font-weight:600;font-size:13px;color:#1e293b">${r.seller_name || '—'}</div>
                <div style="font-size:11px;color:#94a3b8">${r.seller_email || ''}</div>
            </td>
            <td style="color:#64748b;font-size:12.5px">${fmtDate(r.created_at)}</td>
            <td style="font-weight:700;color:${isStuck ? '#ef4444' : '#64748b'}">${ageLabel(age)}</td>
            <td>${statusBadge(r.status, age)}</td>
            <td style="display:flex;gap:5px">
                <button class="action-btn refund" onclick="doRefund(${r.id}, '${r.mobile}', ${r.amount})">Refund</button>
            </td>
        </tr>`;
    }).join('');
}

function renderPager(meta){
    document.getElementById('pageInfo').textContent =
        `Showing ${meta.from || 0}–${meta.to || 0} of ${meta.total || 0}`;
    const btns = document.getElementById('pagerBtns');
    const lp = meta.last_page || 1;
    let html = '';
    for (let i = 1; i <= lp; i++) {
        html += `<button onclick="loadPending(${i})" style="padding:5px 10px;border-radius:6px;border:1.5px solid ${i===currentPage?'#2563eb':'#e2e8f0'};background:${i===currentPage?'#2563eb':'#fff'};color:${i===currentPage?'#fff':'#374151'};font-size:12.5px;cursor:pointer">${i}</button>`;
    }
    btns.innerHTML = html;
}

function loadPending(page){
    currentPage = page || 1;
    const params = new URLSearchParams({ page: currentPage, per_page: 25 });
    const op  = document.getElementById('fOperator').value.trim();
    const age = document.getElementById('fAge').value;
    const dt  = document.getElementById('fDate').value;
    if (op)  params.set('operator_code', op);
    if (age) params.set('min_age', age);
    if (dt)  params.set('date', dt);

    document.getElementById('pendingBody').innerHTML =
        '<tr><td colspan="9" style="text-align:center;padding:30px;color:var(--text-muted)">Loading…</td></tr>';

    empFetch(`/api/v1/employee/reports/pending?${params}`)
        .then(res => {
            const pagination = res.data || {};
            rawRows = pagination.data || [];
            const stats = res.stats || {};

            document.getElementById('sTotalPending').textContent = stats.total || 0;
            document.getElementById('sPendingAmt').textContent   = '₹' + fmtMoney(stats.total_amount || 0);
            document.getElementById('sStuck').textContent        = stats.stuck || 0;
            document.getElementById('sProcessing').textContent   = stats.processing || 0;

            renderTable(rawRows);
            renderPager(pagination);
        })
        .catch(err => {
            document.getElementById('pendingBody').innerHTML =
                `<tr><td colspan="9" style="text-align:center;padding:30px;color:#ef4444">${err.message || 'Failed to load pending transactions.'}</td></tr>`;
        });
}

function doRefund(id, mobile, amount){
    const modal = document.getElementById('actionModal');
    document.getElementById('modalTitle').textContent = 'Initiate Refund';
    document.getElementById('modalMsg').textContent   =
        `Refund ₹${fmtMoney(amount)} to seller wallet for mobile ${mobile}? (Txn ID: ${id})`;
    modal.style.display = 'flex';

    document.getElementById('modalConfirm').onclick = () => {
        modal.style.display = 'none';
        empFetch(`/api/v1/employee/recharges/${id}/refund`, 'POST')
            .then(d => { alert('Refund successful: ' + (d.message || '')); loadPending(currentPage); })
            .catch(e => alert('Refund failed: ' + (e.message || 'Unknown error')));
    };
}

function exportPending(){
    if (!rawRows.length) { alert('No data to export.'); return; }
    const headers = ['ID','Mobile','Operator','Amount','Seller','Created At','Age (min)','Status'];
    const csvRows = rawRows.map(r =>
        [r.id, r.mobile, r.operator_code, r.amount, r.seller_name, r.created_at, r.age_minutes, r.status].join(',')
    );
    const csv = [headers.join(','), ...csvRows].join('\n');
    const a = document.createElement('a');
    a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
    a.download = 'pending_recharges_' + new Date().toISOString().slice(0,10) + '.csv';
    a.click();
}

document.addEventListener('DOMContentLoaded', () => loadPending(1));
</script>
@endpush
@endsection
