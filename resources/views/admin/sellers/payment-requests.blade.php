@extends('layouts.admin')
@section('title', 'Payment Request')
@section('page-title', 'Payment Request')

@section('content')
<style>
/* ── Filters ────────────────────────────────────────────── */
.pr-filters{display:flex;gap:14px;align-items:flex-end;padding:18px 16px 22px;flex-wrap:wrap}
.pr-field label{display:block;font-size:12px;font-weight:600;color:var(--text-secondary);margin-bottom:5px;text-transform:uppercase;letter-spacing:.4px}
.pr-field input,.pr-field select{height:32px;border:1px solid var(--border);padding:3px 10px;font-size:13px;background:var(--card-bg);color:var(--text-primary);border-radius:6px;min-width:110px}
.pr-btn-search{background:#0068ce;color:#fff;border:none;border-radius:6px;padding:7px 22px;font-weight:700;font-size:13px;cursor:pointer}

/* ── Table — !important to override admin-layout thead th rules ── */
.pr-table-wrap{overflow-x:auto}
.pr-table{width:100%;border-collapse:collapse;min-width:1380px}
.pr-table thead th{
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
.pr-table td{font-size:13px;padding:9px 10px;border-bottom:1px solid var(--border);vertical-align:middle;color:var(--text-primary)}
.pr-table tbody tr:hover td{background:#f0f6ff}

/* ── Status badges ─────────────────────────────────────── */
.pr-badge-pending {background:#fef3c7;color:#92400e;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:700}
.pr-badge-approved{background:#d1fae5;color:#065f46;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:700}
.pr-badge-rejected{background:#fee2e2;color:#991b1b;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:700}

/* ── Inline inputs in table ────────────────────────────── */
.pr-inline{height:28px;border:1px solid var(--border);padding:2px 8px;font-size:12px;border-radius:4px;width:100px;background:var(--card-bg);color:var(--text-primary)}

/* ── Action buttons ────────────────────────────────────── */
.pr-act-approve{background:#16a34a;color:#fff;border:none;border-radius:4px;padding:5px 10px;font-size:12px;font-weight:700;cursor:pointer;white-space:nowrap;display:block;width:74px;margin-bottom:3px}
.pr-act-reject {background:#dc2626;color:#fff;border:none;border-radius:4px;padding:5px 10px;font-size:12px;font-weight:700;cursor:pointer;white-space:nowrap;display:block;width:74px}

/* ── Pager ─────────────────────────────────────────────── */
.pr-pager{display:flex;justify-content:space-between;align-items:center;padding:10px 14px;border-top:1px solid var(--border);font-size:12px;color:var(--text-secondary)}

/* ── Proof thumb ───────────────────────────────────────── */
.proof-thumb{width:38px;height:38px;border-radius:4px;object-fit:cover;border:1px solid var(--border);cursor:pointer;vertical-align:middle}
</style>

{{-- Search Filters --}}
<div class="card" style="margin-bottom:16px">
    <div class="card-header" style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.5px">Search Filters</div>
    <div class="pr-filters">
        <div class="pr-field"><label>From Date</label><input type="date" id="f-from"></div>
        <div class="pr-field"><label>To Date</label><input type="date" id="f-to"></div>
        <div class="pr-field"><label>Bank</label>
            <select id="f-bank"><option value="">ALL</option></select>
        </div>
        <button class="pr-btn-search" onclick="loadData(1)">Search</button>
    </div>
</div>

{{-- Table Card --}}
<div class="card">
    <div class="card-header" style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.5px">
        Payment Request List
    </div>
    <div class="pr-table-wrap">
        <table class="pr-table">
            <thead>
                <tr>
                    <th style="width:44px"></th>
                    <th>Payment Date</th>
                    <th>ID</th>
                    <th>Agent Name</th>
                    <th>Wallet Type</th>
                    <th>Ref.Id / Branch</th>
                    <th>Amount</th>
                    <th>Conf.Amount</th>
                    <th>TXN.PWD</th>
                    <th>Status</th>
                    <th>Admin Remark</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="pr-body">
                <tr><td colspan="12" style="padding:16px;color:var(--text-muted);text-align:center">Loading…</td></tr>
            </tbody>
        </table>
    </div>
    <div class="pr-pager" id="pr-pager"></div>
</div>

{{-- Reject Modal --}}
<div id="rejectModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:700;align-items:center;justify-content:center">
    <div class="card" style="width:440px;max-width:95vw;padding:24px">
        <h3 style="font-size:15px;font-weight:700;margin-bottom:14px;color:var(--text-primary)">Reject Payment Request</h3>
        <input type="hidden" id="rejectId">
        <div style="margin-bottom:12px">
            <label style="display:block;font-size:12px;font-weight:600;margin-bottom:5px;color:var(--text-secondary)">Reason <span style="color:#dc2626">*</span></label>
            <select id="rejectReason" style="width:100%;padding:8px 10px;border:1px solid var(--border);border-radius:6px;font-size:13px;background:var(--card-bg);color:var(--text-primary)">
                <option value="">Select reason…</option>
                <option>UTR not found in bank statement</option>
                <option>Incorrect amount entered</option>
                <option>Duplicate request</option>
                <option>Cash payment not accepted</option>
                <option>Payment not received</option>
                <option>Other</option>
            </select>
        </div>
        <div style="margin-bottom:16px">
            <label style="display:block;font-size:12px;font-weight:600;margin-bottom:5px;color:var(--text-secondary)">Additional Notes</label>
            <textarea id="rejectNotes" rows="3" placeholder="Optional…" style="width:100%;padding:8px 10px;border:1px solid var(--border);border-radius:6px;font-size:13px;resize:vertical;box-sizing:border-box;background:var(--card-bg);color:var(--text-primary)"></textarea>
        </div>
        <div style="display:flex;gap:10px">
            <button class="btn btn-danger" onclick="confirmReject()">Reject Request</button>
            <button class="btn btn-outline" onclick="closeReject()">Cancel</button>
        </div>
    </div>
</div>

{{-- Proof Lightbox --}}
<div id="proofLightbox" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:800;align-items:center;justify-content:center;padding:20px" onclick="this.style.display='none'">
    <img id="proofImg" src="" alt="Proof" style="max-width:90vw;max-height:85vh;border-radius:8px">
</div>

@push('scripts')
<script>
const today = new Date().toISOString().slice(0,10);
document.getElementById('f-from').value = today;
document.getElementById('f-to').value   = today;

function esc(s){ return String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

let currentPage = 1;

async function bootFilters(){
    const res = await apiFetch('/api/v1/employee/sellers/payment-requests/list?per_page=1');
    if (!res) { loadData(1); return; }
    const d   = await res.json();
    const banks = d.banks || [];
    const sel   = document.getElementById('f-bank');
    sel.innerHTML = '<option value="">ALL</option>' +
        banks.map(b => `<option value="${esc(b)}">${esc(b)}</option>`).join('');
    loadData(1);
}

async function loadData(page){
    currentPage = page || 1;
    const from = document.getElementById('f-from').value;
    const to   = document.getElementById('f-to').value;
    const bank = document.getElementById('f-bank').value;
    const p    = new URLSearchParams({ page: currentPage, per_page: 25 });
    if (from) p.set('date_from', from);
    if (to)   p.set('date_to', to);
    if (bank) p.set('bank_name', bank);

    document.getElementById('pr-body').innerHTML =
        '<tr><td colspan="12" style="padding:18px;color:var(--text-muted);text-align:center">Loading…</td></tr>';

    const res  = await apiFetch('/api/v1/employee/sellers/payment-requests/list?' + p);
    if (!res) return;
    const d    = await res.json();
    const rows = d.data?.data || [];
    const meta = d.data || {};

    renderRows(rows);
    renderPager(meta);
}

function modeLabel(m){
    const map = {bank_transfer:'BANK TRF',upi:'UPI',neft:'NEFT',rtgs:'RTGS',cheque:'CHEQUE',imps:'IMPS'};
    return map[(m||'').toLowerCase()] || (m||'—').toUpperCase();
}
function roleLabel(r){
    const map = {api_user:'APIUSER',retailer:'RETAILER',distributor:'DISTRIBUTOR',buyer:'BUYER',admin:'ADMIN'};
    return map[(r||'').toLowerCase()] || (r||'—').toUpperCase();
}
function statusBadge(s){
    if (s==='approved') return '<span class="pr-badge-approved">Approved</span>';
    if (s==='rejected') return '<span class="pr-badge-rejected">Rejected</span>';
    return '<span class="pr-badge-pending">Pending</span>';
}

function renderRows(rows){
    const tbody = document.getElementById('pr-body');
    if (!rows.length){
        tbody.innerHTML = `<tr><td colspan="12" style="background:#ef4444;color:#fff;padding:12px 16px;font-weight:600">No Records Found</td></tr>`;
        return;
    }
    tbody.innerHTML = rows.map(r => {
        const u = r.user || {};
        const d = r.created_at ? new Date(r.created_at) : null;
        const dtStr = d
            ? d.toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'})
              + '<br><span style="font-size:11px;color:var(--text-muted)">'
              + d.toLocaleTimeString('en-IN',{hour12:false}) + '</span>'
            : '—';
        const refBranch = [r.reference_number, r.bank_name].filter(Boolean).join(' / ');
        const proof = r.proof_image
            ? `<img src="/storage/${esc(r.proof_image)}" class="proof-thumb" onclick="viewProof('/storage/${esc(r.proof_image)}')" onerror="this.style.display='none'">`
            : '<span style="font-size:11px;color:var(--text-muted)">—</span>';
        const isPending = r.status === 'pending';
        const confInput = isPending
            ? `<input type="text" class="pr-inline" id="conf-${r.id}" value="${esc(r.amount||'')}" placeholder="Conf.Amt">`
            : `<span style="font-size:12.5px">${Number(r.amount||0).toFixed(2)}</span>`;
        const txnInput = isPending
            ? `<input type="text" class="pr-inline" id="txn-${r.id}" placeholder="TXN.PWD">`
            : `<span style="font-size:11.5px;color:var(--text-muted)">—</span>`;
        const actions = isPending
            ? `<button class="pr-act-approve" onclick="approve(${r.id})">Approve</button><button class="pr-act-reject" onclick="openReject(${r.id})">Reject</button>`
            : `<span style="font-size:11.5px;color:var(--text-muted);font-style:italic">${r.status}</span>`;

        return `<tr>
            <td style="text-align:center">${proof}</td>
            <td style="font-size:12.5px">${dtStr}</td>
            <td style="font-weight:700;color:#0068ce">#${r.id}</td>
            <td>
                <div style="font-weight:700;font-size:13px;color:var(--text-primary)">${esc(u.name||'—')}</div>
                <div style="font-size:11px;color:var(--text-muted)">${esc(u.mobile||'')}</div>
                <div style="font-size:10.5px;color:var(--text-muted)">${roleLabel(u.role)}</div>
            </td>
            <td style="font-size:12.5px;font-weight:600">${modeLabel(r.payment_mode)}</td>
            <td style="font-family:monospace;font-size:12px;color:var(--text-secondary)">${esc(refBranch||'—')}</td>
            <td style="font-weight:700;color:var(--text-primary)">${Number(r.amount||0).toFixed(2)}</td>
            <td>${confInput}</td>
            <td>${txnInput}</td>
            <td>${statusBadge(r.status)}</td>
            <td style="font-size:12px;color:var(--text-secondary);max-width:140px;word-break:break-word">${esc(r.admin_notes||'—')}</td>
            <td style="min-width:80px">${actions}</td>
        </tr>`;
    }).join('');
}

function renderPager(meta){
    const pager = document.getElementById('pr-pager');
    if (!meta.total){ pager.innerHTML = ''; return; }
    const lp = meta.last_page || 1;
    let btns = '';
    for (let i = 1; i <= lp && i <= 15; i++){
        const active = i === currentPage;
        btns += `<button onclick="loadData(${i})" style="padding:3px 9px;border-radius:4px;border:1.5px solid ${active?'#0068ce':'var(--border)'};background:${active?'#0068ce':'var(--card-bg)'};color:${active?'#fff':'var(--text-primary)'};font-size:12px;cursor:pointer;margin:0 2px">${i}</button>`;
    }
    pager.innerHTML = `<span>Showing ${meta.from||0}–${meta.to||0} of ${meta.total||0} records</span><div>${btns}</div>`;
}

async function approve(id){
    const txnRef  = (document.getElementById(`txn-${id}`)?.value || '').trim();
    const confAmt = (document.getElementById(`conf-${id}`)?.value || '').trim();
    if (!txnRef){ alert('Please enter TXN.PWD before approving.'); return; }
    if (!confirm(`Approve payment #${id}?\nTXN REF: ${txnRef}\nConf Amount: ${confAmt || '—'}`)) return;

    const res = await apiFetch(`/api/v1/employee/sellers/payment-requests/${id}/approve`, {
        method: 'POST', body: JSON.stringify({ txn_ref: txnRef })
    });
    const d = await res.json().catch(() => ({}));
    if (res.ok){ showToast(d.message || 'Approved!', 'success'); loadData(currentPage); }
    else        { alert(d.message || 'Approval failed.'); }
}

function openReject(id){
    document.getElementById('rejectId').value     = id;
    document.getElementById('rejectReason').value = '';
    document.getElementById('rejectNotes').value  = '';
    document.getElementById('rejectModal').style.display = 'flex';
}
function closeReject(){ document.getElementById('rejectModal').style.display = 'none'; }

async function confirmReject(){
    const id     = document.getElementById('rejectId').value;
    const reason = document.getElementById('rejectReason').value;
    const notes  = document.getElementById('rejectNotes').value.trim();
    if (!reason){ alert('Please select a rejection reason.'); return; }
    const fullNotes = reason + (notes ? ' — ' + notes : '');
    const res = await apiFetch(`/api/v1/employee/sellers/payment-requests/${id}/reject`, {
        method: 'POST', body: JSON.stringify({ notes: fullNotes })
    });
    const d = await res.json().catch(() => ({}));
    if (res.ok){ closeReject(); showToast('Request rejected.', 'info'); loadData(currentPage); }
    else        { alert(d.message || 'Rejection failed.'); }
}

function viewProof(url){
    document.getElementById('proofImg').src = url;
    document.getElementById('proofLightbox').style.display = 'flex';
}

function showToast(msg, type){
    const bg = type==='success'?'#16a34a':type==='error'?'#dc2626':'#0068ce';
    const t  = document.createElement('div');
    t.style.cssText = `position:fixed;bottom:24px;right:24px;background:${bg};color:#fff;padding:12px 20px;border-radius:8px;font-size:13px;font-weight:600;z-index:9999;box-shadow:0 8px 24px rgba(0,0,0,.25)`;
    t.textContent   = msg;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3500);
}

document.addEventListener('DOMContentLoaded', bootFilters);
</script>
@endpush
@endsection
