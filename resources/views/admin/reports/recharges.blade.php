@extends('layouts.admin')

@section('title', 'Recharge Report')
@section('page-title', 'Recharge Report')

@section('content')

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
    <div>
        <h1 style="font-size:20px;font-weight:700;color:var(--text-primary)">Recharge Report</h1>
        <div class="breadcrumb" style="margin-bottom:0;margin-top:3px">
            <a href="/admin/dashboard">Dashboard</a>
            <svg class="breadcrumb-sep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
            <span>Reports</span>
            <svg class="breadcrumb-sep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
            <span>Recharge Report</span>
        </div>
    </div>
    <button class="btn btn-outline btn-sm" onclick="loadReport()">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        Refresh
    </button>
</div>

{{-- Filter Bar --}}
<div class="card" style="margin-bottom:20px">
    <div class="card-body" style="padding:16px 20px">
        <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end">
            <div>
                <label style="display:block;font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Date From</label>
                <input type="date" id="f-date-from" class="form-input" style="border:1px solid var(--border);border-radius:8px;padding:7px 12px;font-size:13px;color:var(--text-primary);background:#fff;outline:none">
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Date To</label>
                <input type="date" id="f-date-to" class="form-input" style="border:1px solid var(--border);border-radius:8px;padding:7px 12px;font-size:13px;color:var(--text-primary);background:#fff;outline:none">
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Operator</label>
                <input type="text" id="f-operator" placeholder="e.g. JIO" style="border:1px solid var(--border);border-radius:8px;padding:7px 12px;font-size:13px;color:var(--text-primary);background:#fff;outline:none;width:120px">
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Status</label>
                <select id="f-status" style="border:1px solid var(--border);border-radius:8px;padding:7px 12px;font-size:13px;color:var(--text-primary);background:#fff;outline:none;min-width:120px">
                    <option value="">All</option>
                    <option value="success">Success</option>
                    <option value="failed">Failed</option>
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="refunded">Refunded</option>
                </select>
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Mobile</label>
                <input type="text" id="f-mobile" placeholder="Mobile number" style="border:1px solid var(--border);border-radius:8px;padding:7px 12px;font-size:13px;color:var(--text-primary);background:#fff;outline:none;width:150px">
            </div>
            <button class="btn btn-primary btn-sm" onclick="loadReport()">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Apply Filters
            </button>
            <button class="btn btn-outline btn-sm" onclick="clearFilters()">Clear</button>
        </div>
    </div>
</div>

{{-- Summary Cards --}}
<div class="stats-grid" id="summary-cards">
    <div class="stat-card blue">
        <div class="stat-header">
            <div class="stat-label">Total Transactions</div>
            <div class="stat-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            </div>
        </div>
        <div class="stat-value" id="s-total-count">—</div>
        <div class="stat-amount" id="s-total-amt">—</div>
        <div class="stat-footer"><span class="stat-pulse"></span><span class="stat-updated">Report data</span></div>
    </div>
    <div class="stat-card green">
        <div class="stat-header">
            <div class="stat-label">Success Rate</div>
            <div class="stat-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div class="stat-value" id="s-success-rate">—</div>
        <div class="stat-amount" id="s-success-amt">—</div>
        <div class="stat-footer"><span class="stat-pulse"></span><span class="stat-updated">Success count</span></div>
    </div>
    <div class="stat-card orange">
        <div class="stat-header">
            <div class="stat-label">Total Amount</div>
            <div class="stat-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div class="stat-value" id="s-total-amount">—</div>
        <div class="stat-amount" id="s-pending-count">—</div>
        <div class="stat-footer"><span class="stat-pulse"></span><span class="stat-updated">Total recharge value</span></div>
    </div>
    <div class="stat-card red">
        <div class="stat-header">
            <div class="stat-label">Failed Transactions</div>
            <div class="stat-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div class="stat-value" id="s-fail-count">—</div>
        <div class="stat-amount" id="s-fail-amt">—</div>
        <div class="stat-footer"><span class="stat-pulse"></span><span class="stat-updated">Failed amount</span></div>
    </div>
</div>

{{-- Daily Summary Table --}}
<div class="card" style="margin-bottom:20px">
    <div class="card-header" style="justify-content:space-between">
        <div class="card-title">Daily Summary</div>
        <span style="font-size:11px;color:var(--text-muted)" id="daily-meta">—</span>
    </div>
    <div class="card-body" style="padding:0">
        <div id="daily-loading" class="loading-overlay"><div class="spinner"></div> Loading…</div>
        <div class="table-wrap" id="daily-table-wrap" style="display:none">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Success</th>
                        <th>Failure</th>
                        <th>Pending</th>
                        <th>Amount</th>
                        <th>Success Rate</th>
                    </tr>
                </thead>
                <tbody id="daily-tbody">
                    <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:24px">No data</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Transactions Table --}}
<div class="card">
    <div class="card-header" style="justify-content:space-between">
        <div class="card-title">Transaction Details</div>
        <div style="display:flex;align-items:center;gap:10px">
            <span style="font-size:12px;color:var(--text-muted)" id="txn-pagination-info">—</span>
            <button class="btn btn-outline btn-sm" id="btn-prev" onclick="changePage(-1)" disabled>&#8592; Prev</button>
            <button class="btn btn-outline btn-sm" id="btn-next" onclick="changePage(1)">Next &#8594;</button>
        </div>
    </div>
    <div class="card-body" style="padding:0">
        <div id="txn-loading" class="loading-overlay"><div class="spinner"></div> Loading transactions…</div>
        <div class="table-wrap" id="txn-table-wrap" style="display:none">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Mobile</th>
                        <th>Operator</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Ref ID</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody id="txn-tbody">
                    <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:24px">No transactions found</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let currentPage = 1;
let totalPages  = 1;

function getFilters() {
    return {
        date_from:     document.getElementById('f-date-from').value,
        date_to:       document.getElementById('f-date-to').value,
        operator_code: document.getElementById('f-operator').value,
        status:        document.getElementById('f-status').value,
        mobile:        document.getElementById('f-mobile').value,
        page:          currentPage,
    };
}

function buildQuery(params) {
    return Object.entries(params).filter(([,v]) => v !== '' && v != null).map(([k,v]) => k + '=' + encodeURIComponent(v)).join('&');
}

function clearFilters() {
    ['f-date-from','f-date-to','f-operator','f-mobile'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('f-status').value = '';
    currentPage = 1;
    loadReport();
}

function changePage(dir) {
    currentPage = Math.max(1, Math.min(totalPages, currentPage + dir));
    loadTransactions();
}

async function loadReport() {
    currentPage = 1;
    await Promise.allSettled([loadSummary(), loadDaily(), loadTransactions()]);
}

async function loadSummary() {
    const q = buildQuery(getFilters());
    const res = await apiFetch('/api/v1/employee/reports/recharges?' + q);
    if (!res) return;
    const json = await res.json();
    const s = json.summary || json.data?.summary || {};

    const total = s.total_txns ?? s.total_count ?? s.total ?? 0;
    document.getElementById('s-total-count').textContent  = fmtNum(total);
    document.getElementById('s-total-amt').textContent    = fmtAmt(s.total_amount ?? 0);
    const rate = s.success_rate_pct ?? s.success_rate ?? (total ? ((s.success_count / total) * 100) : 0);
    document.getElementById('s-success-rate').textContent = Number(rate).toFixed(1) + '%';
    document.getElementById('s-success-amt').textContent  = fmtNum(s.success_count ?? 0) + ' success';
    document.getElementById('s-total-amount').textContent = fmtAmt(s.total_amount ?? 0);
    const pendingCount = (s.processing_count ?? 0) + (s.queued_count ?? 0);
    document.getElementById('s-pending-count').textContent= fmtNum(pendingCount) + ' pending';
    document.getElementById('s-fail-count').textContent   = fmtNum(s.failed_count ?? s.failure_count ?? 0);
    document.getElementById('s-fail-amt').textContent     = fmtAmt(s.failure_amount ?? s.failed_amount ?? 0);
}

async function loadDaily() {
    document.getElementById('daily-loading').style.display = 'flex';
    document.getElementById('daily-table-wrap').style.display = 'none';

    const q = buildQuery(getFilters());
    const res = await apiFetch('/api/v1/employee/reports/recharges?' + q);
    if (!res) return;
    const json = await res.json();
    const rows = json.daily || json.data?.daily || [];

    document.getElementById('daily-loading').style.display = 'none';
    document.getElementById('daily-table-wrap').style.display = 'block';
    document.getElementById('daily-meta').textContent = rows.length + ' days';

    const tbody = document.getElementById('daily-tbody');
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:24px">No daily data found</td></tr>';
        return;
    }
    tbody.innerHTML = rows.map(r => {
        const rate = r.success_rate ?? (r.total ? ((r.success / r.total) * 100) : 0);
        const rc = rate >= 90 ? '#10b981' : rate >= 70 ? '#f59e0b' : '#ef4444';
        return `<tr>
            <td style="font-weight:600">${r.date || '—'}</td>
            <td>${fmtNum(r.total)}</td>
            <td style="color:#10b981;font-weight:600">${fmtNum(r.success)}</td>
            <td style="color:#ef4444;font-weight:600">${fmtNum(r.failed)}</td>
            <td style="color:#f59e0b;font-weight:600">${fmtNum(r.pending)}</td>
            <td>${fmtAmt(r.amount ?? r.total_amount)}</td>
            <td><span style="color:${rc};font-weight:600">${Number(rate).toFixed(1)}%</span></td>
        </tr>`;
    }).join('');
}

async function loadTransactions() {
    document.getElementById('txn-loading').style.display = 'flex';
    document.getElementById('txn-table-wrap').style.display = 'none';

    const q = buildQuery(getFilters());
    const res = await apiFetch('/api/v1/employee/reports/recharges?' + q);
    if (!res) return;
    const json = await res.json();
    const pg   = json.transactions || json.data?.transactions || {};
    const txns = pg.data || [];
    const meta = { last_page: pg.last_page, total: pg.total, per_page: pg.per_page, current_page: pg.current_page };

    totalPages = meta.last_page ?? meta.total_pages ?? 1;
    const total = meta.total ?? txns.length;
    document.getElementById('txn-pagination-info').textContent = `Page ${currentPage} of ${totalPages} (${fmtNum(total)} records)`;
    document.getElementById('btn-prev').disabled = currentPage <= 1;
    document.getElementById('btn-next').disabled = currentPage >= totalPages;

    document.getElementById('txn-loading').style.display = 'none';
    document.getElementById('txn-table-wrap').style.display = 'block';

    const tbody = document.getElementById('txn-tbody');
    if (!txns.length) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:24px">No transactions found</td></tr>';
        return;
    }
    const offset = (currentPage - 1) * (meta.per_page ?? 20);
    tbody.innerHTML = txns.map((tx, i) => {
        const st = (tx.status || '').toLowerCase();
        const sc = st === 'success' ? '#10b981' : st === 'failed' || st === 'refunded' ? '#ef4444' : '#f59e0b';
        const bg = st === 'success' ? '#d1fae5' : st === 'failed' || st === 'refunded' ? '#fee2e2' : '#fef3c7';
        return `<tr>
            <td style="color:var(--text-muted)">${offset + i + 1}</td>
            <td style="font-weight:600">${tx.mobile || tx.mobile_number || '—'}</td>
            <td>${tx.operator_name || tx.operator_code || '—'}</td>
            <td style="font-weight:600">${fmtAmt(tx.amount)}</td>
            <td><span style="background:${bg};color:${sc};font-size:11px;font-weight:600;padding:2px 8px;border-radius:20px">${tx.status || '—'}</span></td>
            <td style="font-size:12px;color:var(--text-muted)">${tx.reference_id || tx.ref_id || '—'}</td>
            <td style="font-size:12px;color:var(--text-muted)">${tx.created_at ? new Date(tx.created_at).toLocaleString('en-IN',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:true}) : '—'}</td>
        </tr>`;
    }).join('');
}

// Set default dates (last 7 days)
const today = new Date();
const week  = new Date(today); week.setDate(week.getDate() - 7);
document.getElementById('f-date-to').value   = today.toISOString().slice(0, 10);
document.getElementById('f-date-from').value = week.toISOString().slice(0, 10);

document.addEventListener('DOMContentLoaded', loadReport);
</script>
@endpush
