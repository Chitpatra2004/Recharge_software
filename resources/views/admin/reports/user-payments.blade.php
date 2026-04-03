@extends('layouts.admin')
@section('title','User Payment Requests')

@push('head')
<style>
.summary-strip{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px}
@media(max-width:900px){.summary-strip{grid-template-columns:1fr 1fr}}
.kpi{background:var(--card-bg);border-radius:var(--radius);padding:16px 20px;box-shadow:var(--shadow-sm);border-left:4px solid transparent}
.kpi.orange{border-color:var(--accent-orange)}.kpi.green{border-color:var(--accent-green)}
.kpi.blue{border-color:var(--accent-blue)}.kpi.red{border-color:var(--accent-red)}
.kpi .val{font-size:22px;font-weight:700;margin-bottom:2px;color:var(--text-primary)}
.kpi .lbl{font-size:11.5px;color:var(--text-secondary)}
.filter-bar{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
.filter-bar input,.filter-bar select{padding:7px 11px;border:1px solid var(--border);border-radius:8px;font-size:13px;background:#fff;color:var(--text-primary)}
.badge-approved{background:#d1fae5;color:#065f46;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:600}
.badge-pending{background:#fef3c7;color:#92400e;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:600}
.badge-rejected{background:#fee2e2;color:#991b1b;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:600}
.proof-thumb{width:44px;height:44px;border-radius:6px;object-fit:cover;border:1px solid var(--border);cursor:pointer}
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">User Payment Requests</h1>
        <p class="page-sub">Review and approve wallet top-up requests from users</p>
    </div>
    <div style="display:flex;gap:8px">
        <button class="btn btn-outline" onclick="exportCSV()">Export CSV</button>
        <button class="btn btn-primary"  onclick="loadData()">Refresh</button>
    </div>
</div>

{{-- KPI Strip --}}
<div class="summary-strip">
    <div class="kpi orange"><div class="val" id="kPendingCount">—</div><div class="lbl">Pending Requests</div></div>
    <div class="kpi orange"><div class="val" id="kPendingAmt">—</div><div class="lbl">Pending Amount</div></div>
    <div class="kpi green"><div class="val" id="kApprovedToday">—</div><div class="lbl">Approved Today</div></div>
    <div class="kpi blue"><div class="val" id="kApprovedTodayAmt">—</div><div class="lbl">Credited Today</div></div>
</div>

{{-- Pending Requests --}}
<div class="card" style="margin-bottom:18px">
    <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
        <span style="font-weight:600;font-size:14px">
            Pending Requests
            <span id="pendingBadge" style="background:#fef3c7;color:#92400e;padding:2px 9px;border-radius:20px;font-size:12px;margin-left:6px;font-weight:700">0</span>
        </span>
        <button class="btn btn-primary btn-sm" onclick="approveAll()">Approve All Pending</button>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Submitted</th>
                    <th>User</th>
                    <th>Amount</th>
                    <th>Mode</th>
                    <th>Reference / UTR</th>
                    <th>UPI ID</th>
                    <th>Notes</th>
                    <th>Proof</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="pendingBody">
                <tr><td colspan="9" style="text-align:center;padding:24px;color:var(--text-muted)">Loading…</td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- All Requests --}}
<div class="card">
    <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
        <span style="font-weight:600;font-size:14px">All Requests</span>
        <div class="filter-bar">
            <input type="text"  id="fSearch"   placeholder="User / Reference…" oninput="loadHistory(1)" style="min-width:180px">
            <select id="fStatus" onchange="loadHistory(1)">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
            <input type="date" id="fFrom" onchange="loadHistory(1)">
            <input type="date" id="fTo"   onchange="loadHistory(1)">
            <button class="btn btn-outline btn-sm" onclick="clearFilters()">Clear</button>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Submitted</th>
                    <th>User</th>
                    <th>Amount</th>
                    <th>Mode</th>
                    <th>Reference</th>
                    <th>Payment Date</th>
                    <th>Status</th>
                    <th>Admin Notes</th>
                    <th>Processed</th>
                </tr>
            </thead>
            <tbody id="historyBody">
                <tr><td colspan="10" style="text-align:center;padding:24px;color:var(--text-muted)">Loading…</td></tr>
            </tbody>
        </table>
    </div>
    <div class="card-footer" id="histPager" style="gap:8px;justify-content:flex-end"></div>
</div>

{{-- Reject Modal --}}
<div id="rejectModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:500;align-items:center;justify-content:center">
    <div class="card" style="width:440px;max-width:95vw;padding:24px;position:relative">
        <h3 style="font-size:15px;font-weight:700;margin-bottom:14px">Reject Payment Request</h3>
        <input type="hidden" id="rejectId">
        <div style="margin-bottom:14px">
            <label style="display:block;font-size:12px;font-weight:600;margin-bottom:5px;color:var(--text-secondary)">Reason <span style="color:var(--accent-red)">*</span></label>
            <select id="rejectReason" style="width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:8px;font-size:13px">
                <option value="">Select reason…</option>
                <option>UTR not found in bank statement</option>
                <option>Incorrect amount — proof shows different amount</option>
                <option>Duplicate request</option>
                <option>Payment not received from registered account</option>
                <option>Invalid / fake proof uploaded</option>
                <option>Other (see notes)</option>
            </select>
        </div>
        <div style="margin-bottom:16px">
            <label style="display:block;font-size:12px;font-weight:600;margin-bottom:5px;color:var(--text-secondary)">Additional Notes</label>
            <textarea id="rejectNotes" rows="3" placeholder="Optional extra details…" style="width:100%;padding:8px 11px;border:1px solid var(--border);border-radius:8px;font-size:13px;resize:vertical;box-sizing:border-box"></textarea>
        </div>
        <div style="display:flex;gap:10px">
            <button class="btn btn-danger" onclick="confirmReject()">Reject Request</button>
            <button class="btn btn-outline" onclick="closeRejectModal()">Cancel</button>
        </div>
    </div>
</div>

{{-- Proof Lightbox --}}
<div id="proofLightbox" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:600;align-items:center;justify-content:center;padding:20px" onclick="this.style.display='none'">
    <img id="proofImg" src="" alt="Proof" style="max-width:90vw;max-height:85vh;border-radius:8px;box-shadow:0 20px 60px rgba(0,0,0,.5)">
</div>

@endsection

@push('scripts')
<script>
const BASE = '/api/v1/employee/user-payment-requests';
let allData = [], histPage = 1;

/* ── Load all data ── */
async function loadData() {
    loadPending();
    loadHistory(1);
}

/* ── Pending requests ── */
async function loadPending() {
    document.getElementById('pendingBody').innerHTML = '<tr><td colspan="9" style="text-align:center;padding:20px;color:var(--text-muted)">Loading…</td></tr>';
    try {
        const res = await apiFetch(BASE + '?status=pending&per_page=100');
        const d   = await res.json();
        const rows = d.data?.data || [];
        const s    = d.stats || {};

        // KPIs
        document.getElementById('kPendingCount').textContent    = fmtNum(s.pending_count || 0);
        document.getElementById('kPendingAmt').textContent      = fmtAmt(s.pending_amount || 0);
        document.getElementById('kApprovedToday').textContent   = fmtNum(s.approved_today || 0);
        document.getElementById('kApprovedTodayAmt').textContent = fmtAmt(s.approved_today_amount || 0);
        document.getElementById('pendingBadge').textContent     = rows.length;

        if (!rows.length) {
            document.getElementById('pendingBody').innerHTML = '<tr><td colspan="9" style="text-align:center;padding:24px;color:var(--text-muted)">No pending requests</td></tr>';
            return;
        }
        document.getElementById('pendingBody').innerHTML = rows.map(r => pendingRow(r)).join('');
    } catch(e) {
        document.getElementById('pendingBody').innerHTML = '<tr><td colspan="9" style="text-align:center;padding:20px;color:var(--accent-red)">Failed to load</td></tr>';
    }
}

function pendingRow(r) {
    const dt   = r.created_at ? new Date(r.created_at).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'}) : '—';
    const user = r.user || {};
    const mode = (r.payment_mode||'').replace(/_/g,' ').toUpperCase();
    const proof = r.proof_image
        ? `<img src="/storage/${r.proof_image}" class="proof-thumb" onclick="viewProof('/storage/${r.proof_image}')" onerror="this.src='';this.alt='PDF'">`
        : '<span style="font-size:11px;color:var(--text-muted)">—</span>';
    return `<tr>
        <td style="font-size:12px;color:var(--text-muted)">${dt}</td>
        <td>
            <div style="font-weight:600;font-size:13px">${esc(user.name||'—')}</div>
            <div style="font-size:11px;color:var(--text-muted)">${esc(user.email||'')}</div>
            <div style="font-size:11px;color:var(--text-muted)">${esc(user.mobile||'')}</div>
        </td>
        <td style="font-weight:700;font-size:15px;color:var(--accent-green)">₹${parseFloat(r.amount||0).toFixed(2)}</td>
        <td style="font-size:12px;font-weight:600">${mode}</td>
        <td style="font-family:monospace;font-size:12px">${esc(r.reference_number||'—')}</td>
        <td style="font-size:12px;color:var(--text-muted)">${esc(r.upi_id||'—')}</td>
        <td style="font-size:12px;color:var(--text-muted);max-width:150px">${esc(r.notes||'—')}</td>
        <td>${proof}</td>
        <td>
            <div style="display:flex;gap:6px;flex-wrap:nowrap">
                <button class="btn btn-primary btn-sm" onclick="approve(${r.id})">✓ Approve</button>
                <button class="btn btn-danger  btn-sm" onclick="openReject(${r.id})">✗ Reject</button>
            </div>
        </td>
    </tr>`;
}

/* ── History ── */
async function loadHistory(page) {
    histPage = page;
    const status = document.getElementById('fStatus').value;
    const search = document.getElementById('fSearch').value;
    const from   = document.getElementById('fFrom').value;
    const to     = document.getElementById('fTo').value;
    const params = new URLSearchParams({ page, per_page: 20 });
    if (status) params.set('status', status);
    if (search) params.set('search', search);
    if (from)   params.set('date_from', from);
    if (to)     params.set('date_to', to);

    document.getElementById('historyBody').innerHTML = '<tr><td colspan="10" style="text-align:center;padding:24px;color:var(--text-muted)">Loading…</td></tr>';
    try {
        const res  = await apiFetch(BASE + '?' + params);
        const d    = await res.json();
        const rows = d.data?.data || [];
        allData    = rows;

        if (!rows.length) {
            document.getElementById('historyBody').innerHTML = '<tr><td colspan="10" style="text-align:center;padding:24px;color:var(--text-muted)">No records found</td></tr>';
            document.getElementById('histPager').innerHTML = '';
            return;
        }

        document.getElementById('historyBody').innerHTML = rows.map(r => {
            const dt      = r.created_at ? new Date(r.created_at).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'}) : '—';
            const pdDate  = r.payment_date ? new Date(r.payment_date).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'}) : '—';
            const procAt  = r.processed_at ? new Date(r.processed_at).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'}) : '—';
            const user    = r.user || {};
            const mode    = (r.payment_mode||'').replace(/_/g,' ').toUpperCase();
            const badgeClass = {approved:'badge-approved',pending:'badge-pending',rejected:'badge-rejected'}[r.status] || 'badge-pending';
            return `<tr>
                <td style="font-family:monospace;font-size:11px;color:var(--text-muted)">#${r.id}</td>
                <td style="font-size:12px;color:var(--text-muted)">${dt}</td>
                <td>
                    <div style="font-weight:600;font-size:13px">${esc(user.name||'—')}</div>
                    <div style="font-size:11px;color:var(--text-muted)">${esc(user.email||'')}</div>
                </td>
                <td style="font-weight:700">₹${parseFloat(r.amount||0).toFixed(2)}</td>
                <td style="font-size:12px">${mode}</td>
                <td style="font-family:monospace;font-size:11px">${esc(r.reference_number||'—')}</td>
                <td style="font-size:12px;color:var(--text-muted)">${pdDate}</td>
                <td><span class="${badgeClass}">${(r.status||'').toUpperCase()}</span></td>
                <td style="font-size:12px;color:var(--text-muted);max-width:180px">${esc(r.admin_notes||'—')}</td>
                <td style="font-size:12px;color:var(--text-muted)">${procAt}</td>
            </tr>`;
        }).join('');

        // Pager
        const pager   = d.data || {};
        const last    = pager.last_page || 1;
        const curr    = pager.current_page || page;
        let pagerHtml = '';
        if (last > 1) {
            if (curr > 1) pagerHtml += `<button class="btn btn-outline btn-sm" onclick="loadHistory(${curr-1})">← Prev</button>`;
            pagerHtml += `<span style="font-size:12px;color:var(--text-muted)">Page ${curr} of ${last}</span>`;
            if (curr < last) pagerHtml += `<button class="btn btn-outline btn-sm" onclick="loadHistory(${curr+1})">Next →</button>`;
        }
        document.getElementById('histPager').innerHTML = pagerHtml;
    } catch(e) {
        document.getElementById('historyBody').innerHTML = '<tr><td colspan="10" style="text-align:center;padding:20px;color:var(--accent-red)">Failed to load</td></tr>';
    }
}

/* ── Approve ── */
async function approve(id) {
    if (!confirm('Approve this payment request and credit wallet?')) return;
    try {
        const res = await apiFetch(BASE + '/' + id + '/approve', { method: 'POST', body: JSON.stringify({ admin_notes: 'Approved by admin' }) });
        const d   = await res.json();
        if (res.ok) {
            showToast(d.message || 'Approved!', 'success');
            loadData();
        } else {
            showToast(d.message || 'Error approving request', 'error');
        }
    } catch(e) { showToast('Network error', 'error'); }
}

/* ── Approve All ── */
async function approveAll() {
    const pendingRows = document.querySelectorAll('#pendingBody tr');
    if (!pendingRows.length || pendingRows[0].cells.length < 2) return;
    if (!confirm('Approve ALL pending requests and credit user wallets?')) return;

    try {
        const res  = await apiFetch(BASE + '?status=pending&per_page=200');
        const d    = await res.json();
        const rows = d.data?.data || [];
        if (!rows.length) { showToast('No pending requests', 'info'); return; }
        let ok = 0;
        for (const r of rows) {
            const ar = await apiFetch(BASE + '/' + r.id + '/approve', { method: 'POST', body: JSON.stringify({ admin_notes: 'Bulk approved' }) });
            if (ar.ok) ok++;
        }
        showToast(ok + ' requests approved and wallets credited!', 'success');
        loadData();
    } catch(e) { showToast('Error during bulk approve', 'error'); }
}

/* ── Reject ── */
function openReject(id) {
    document.getElementById('rejectId').value = id;
    document.getElementById('rejectReason').value = '';
    document.getElementById('rejectNotes').value  = '';
    const m = document.getElementById('rejectModal');
    m.style.display = 'flex';
    m.onclick = e => { if (e.target === m) closeRejectModal(); };
}
function closeRejectModal() { document.getElementById('rejectModal').style.display = 'none'; }

async function confirmReject() {
    const id     = document.getElementById('rejectId').value;
    const reason = document.getElementById('rejectReason').value;
    const notes  = document.getElementById('rejectNotes').value.trim();
    if (!reason) { alert('Please select a rejection reason.'); return; }
    const adminNotes = reason + (notes ? '. ' + notes : '');
    try {
        const res = await apiFetch(BASE + '/' + id + '/reject', { method: 'POST', body: JSON.stringify({ admin_notes: adminNotes }) });
        const d   = await res.json();
        if (res.ok) {
            closeRejectModal();
            showToast('Request rejected.', 'success');
            loadData();
        } else {
            showToast(d.message || 'Rejection failed', 'error');
        }
    } catch(e) { showToast('Network error', 'error'); }
}

/* ── Helpers ── */
function viewProof(url) {
    document.getElementById('proofImg').src = url;
    const lb = document.getElementById('proofLightbox');
    lb.style.display = 'flex';
}
function clearFilters() {
    ['fSearch','fFrom','fTo'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('fStatus').value = '';
    loadHistory(1);
}
function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function fmtNum(n) { return Number(n||0).toLocaleString('en-IN'); }
function fmtAmt(n) { return '₹'+Number(n||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2}); }
function showToast(msg, type='success') {
    const t = document.createElement('div');
    const bg = type==='success'?'#10b981':type==='error'?'#ef4444':'#3b82f6';
    t.style.cssText = `position:fixed;bottom:24px;right:24px;background:${bg};color:#fff;padding:12px 18px;border-radius:10px;font-size:13px;font-weight:500;z-index:9999;box-shadow:0 8px 24px rgba(0,0,0,.25);animation:fadeInUp .25s ease`;
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 4000);
}

function exportCSV() {
    if (!allData.length) { alert('Load data first.'); return; }
    const headers = ['ID','User Name','Email','Amount','Mode','Reference','Payment Date','Status','Admin Notes','Processed At'];
    const rows = allData.map(r => {
        const u = r.user || {};
        return [r.id, u.name||'', u.email||'', r.amount||0, r.payment_mode||'', r.reference_number||'', r.payment_date||'', r.status||'', r.admin_notes||'', r.processed_at||''];
    });
    const csv = [headers, ...rows].map(r => r.map(v => `"${String(v??'').replace(/"/g,'""')}"`).join(',')).join('\n');
    const a = document.createElement('a');
    a.href = 'data:text/csv;charset=utf-8,\uFEFF' + encodeURIComponent(csv);
    a.download = 'user-payment-requests-' + new Date().toISOString().slice(0,10) + '.csv';
    a.click();
}

/* ── Init ── */
document.addEventListener('DOMContentLoaded', loadData);
</script>
@endpush
