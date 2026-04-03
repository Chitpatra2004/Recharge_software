@extends('layouts.admin')

@section('title', 'Payment Report')
@section('page-title', 'Payment Report')

@section('content')

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
    <div>
        <h1 style="font-size:20px;font-weight:700;color:var(--text-primary)">Payment Report</h1>
        <div class="breadcrumb" style="margin-bottom:0;margin-top:3px">
            <a href="/admin/dashboard">Dashboard</a>
            <svg class="breadcrumb-sep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span>Reports</span>
            <svg class="breadcrumb-sep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span>Payment Report</span>
        </div>
    </div>
    <button class="btn btn-outline btn-sm" onclick="loadReport()">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        Refresh
    </button>
</div>

{{-- Filter Bar --}}
<div class="card" style="margin-bottom:20px">
    <div class="card-body" style="padding:16px 20px">
        <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end">
            <div>
                <label style="display:block;font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Date From</label>
                <input type="date" id="f-date-from" style="border:1px solid var(--border);border-radius:8px;padding:7px 12px;font-size:13px;color:var(--text-primary);background:#fff;outline:none">
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Date To</label>
                <input type="date" id="f-date-to" style="border:1px solid var(--border);border-radius:8px;padding:7px 12px;font-size:13px;color:var(--text-primary);background:#fff;outline:none">
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Type</label>
                <select id="f-type" style="border:1px solid var(--border);border-radius:8px;padding:7px 12px;font-size:13px;color:var(--text-primary);background:#fff;outline:none;min-width:130px">
                    <option value="">All Types</option>
                    <option value="credit">Credit</option>
                    <option value="debit">Debit</option>
                    <option value="refund">Refund</option>
                    <option value="withdrawal">Withdrawal</option>
                </select>
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Status</label>
                <select id="f-status" style="border:1px solid var(--border);border-radius:8px;padding:7px 12px;font-size:13px;color:var(--text-primary);background:#fff;outline:none;min-width:120px">
                    <option value="">All</option>
                    <option value="success">Success</option>
                    <option value="pending">Pending</option>
                    <option value="failed">Failed</option>
                </select>
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
<div class="stats-grid" style="margin-bottom:20px">
    <div class="stat-card blue">
        <div class="stat-header"><div class="stat-label">Total Payments</div>
            <div class="stat-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg></div>
        </div>
        <div class="stat-value" id="s-total-count">—</div>
        <div class="stat-amount" id="s-total-amt">—</div>
        <div class="stat-footer"><span class="stat-pulse"></span><span class="stat-updated">Total transactions</span></div>
    </div>
    <div class="stat-card green">
        <div class="stat-header"><div class="stat-label">Total Credits</div>
            <div class="stat-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg></div>
        </div>
        <div class="stat-value" id="s-credit-amt">—</div>
        <div class="stat-amount" id="s-credit-count">—</div>
        <div class="stat-footer"><span class="stat-pulse"></span><span class="stat-updated">Inflow</span></div>
    </div>
    <div class="stat-card orange">
        <div class="stat-header"><div class="stat-label">Total Debits</div>
            <div class="stat-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/></svg></div>
        </div>
        <div class="stat-value" id="s-debit-amt">—</div>
        <div class="stat-amount" id="s-debit-count">—</div>
        <div class="stat-footer"><span class="stat-pulse"></span><span class="stat-updated">Outflow</span></div>
    </div>
    <div class="stat-card red">
        <div class="stat-header"><div class="stat-label">Net Cashflow</div>
            <div class="stat-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg></div>
        </div>
        <div class="stat-value" id="s-net-amt">—</div>
        <div class="stat-amount" id="s-refund-amt">—</div>
        <div class="stat-footer"><span class="stat-pulse"></span><span class="stat-updated">Credits minus debits</span></div>
    </div>
</div>

{{-- Daily Cashflow Table --}}
<div class="card" style="margin-bottom:20px">
    <div class="card-header" style="justify-content:space-between">
        <div class="card-title">Daily Cashflow</div>
        <span id="daily-count" style="font-size:12px;color:var(--text-muted)">—</span>
    </div>
    <div class="card-body" style="padding:0">
        <div id="daily-loading" class="loading-overlay"><div class="spinner"></div> Loading…</div>
        <div class="table-wrap" id="daily-table-wrap" style="display:none">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Credits</th>
                        <th>Debits</th>
                        <th>Refunds</th>
                        <th>Net</th>
                    </tr>
                </thead>
                <tbody id="daily-tbody"></tbody>
            </table>
        </div>
    </div>
</div>

{{-- Transaction Details --}}
<div class="card">
    <div class="card-header" style="justify-content:space-between">
        <div class="card-title">Payment Transactions</div>
        <span id="txn-count" style="font-size:12px;color:var(--text-muted)">—</span>
    </div>
    <div class="card-body" style="padding:0">
        <div id="txn-loading" class="loading-overlay"><div class="spinner"></div> Loading…</div>
        <div class="table-wrap" id="txn-table-wrap" style="display:none">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Reference</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody id="txn-tbody"></tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function getFilters() {
    return {
        date_from: document.getElementById('f-date-from').value,
        date_to:   document.getElementById('f-date-to').value,
        type:      document.getElementById('f-type').value,
        status:    document.getElementById('f-status').value,
    };
}

function buildQuery(p) {
    return Object.entries(p).filter(([,v]) => v).map(([k,v]) => k+'='+encodeURIComponent(v)).join('&');
}

function clearFilters() {
    ['f-date-from','f-date-to'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('f-type').value = '';
    document.getElementById('f-status').value = '';
    loadReport();
}

async function loadReport() {
    const q = buildQuery(getFilters());
    const res = await apiFetch('/api/v1/admin/reports/payments?' + q);
    if (!res) return;
    const json = await res.json();
    const d = json.data || {};
    const s = d.summary || {};

    document.getElementById('s-total-count').textContent  = fmtNum(s.total_count ?? 0);
    document.getElementById('s-total-amt').textContent    = fmtAmt(s.total_amount ?? 0);
    document.getElementById('s-credit-amt').textContent   = fmtAmt(s.credit_amount ?? s.total_credits ?? 0);
    document.getElementById('s-credit-count').textContent = fmtNum(s.credit_count ?? 0) + ' credits';
    document.getElementById('s-debit-amt').textContent    = fmtAmt(s.debit_amount ?? s.total_debits ?? 0);
    document.getElementById('s-debit-count').textContent  = fmtNum(s.debit_count ?? 0) + ' debits';
    const net = (s.credit_amount ?? 0) - (s.debit_amount ?? 0);
    document.getElementById('s-net-amt').textContent      = fmtAmt(net);
    document.getElementById('s-refund-amt').textContent   = fmtAmt(s.refund_amount ?? 0) + ' refunds';

    renderDaily(d.daily_cashflow || d.daily || []);
    renderTransactions(d.transactions || []);
}

function renderDaily(rows) {
    document.getElementById('daily-loading').style.display = 'none';
    document.getElementById('daily-table-wrap').style.display = 'block';
    document.getElementById('daily-count').textContent = rows.length + ' days';
    const tbody = document.getElementById('daily-tbody');
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:24px">No data</td></tr>';
        return;
    }
    tbody.innerHTML = rows.map(r => {
        const net = (r.credits ?? r.credit_amount ?? 0) - (r.debits ?? r.debit_amount ?? 0);
        const nc = net >= 0 ? '#10b981' : '#ef4444';
        return `<tr>
            <td style="font-weight:600">${r.date || '—'}</td>
            <td>${fmtNum(r.total)}</td>
            <td style="color:#10b981;font-weight:600">${fmtAmt(r.credits ?? r.credit_amount)}</td>
            <td style="color:#ef4444;font-weight:600">${fmtAmt(r.debits ?? r.debit_amount)}</td>
            <td style="color:#f59e0b">${fmtAmt(r.refunds ?? r.refund_amount)}</td>
            <td style="color:${nc};font-weight:700">${fmtAmt(net)}</td>
        </tr>`;
    }).join('');
}

function renderTransactions(rows) {
    document.getElementById('txn-loading').style.display = 'none';
    document.getElementById('txn-table-wrap').style.display = 'block';
    document.getElementById('txn-count').textContent = fmtNum(rows.length) + ' records';
    const tbody = document.getElementById('txn-tbody');
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:24px">No transactions</td></tr>';
        return;
    }
    tbody.innerHTML = rows.map((r, i) => {
        const st = (r.status || '').toLowerCase();
        const bg = st === 'success' ? '#d1fae5' : st === 'pending' ? '#fef3c7' : '#fee2e2';
        const sc = st === 'success' ? '#059669' : st === 'pending' ? '#d97706' : '#dc2626';
        const type = (r.type || '').toLowerCase();
        const tc = type === 'credit' ? '#10b981' : type === 'debit' ? '#ef4444' : '#f59e0b';
        return `<tr>
            <td style="color:var(--text-muted)">${i+1}</td>
            <td>${r.user_name || r.user?.name || '—'}</td>
            <td><span style="color:${tc};font-weight:600;font-size:12px">${r.type || '—'}</span></td>
            <td style="font-weight:700">${fmtAmt(r.amount)}</td>
            <td><span style="background:${bg};color:${sc};font-size:11px;font-weight:600;padding:2px 8px;border-radius:20px">${r.status || '—'}</span></td>
            <td style="font-size:12px;color:var(--text-muted)">${r.reference_id || r.transaction_id || '—'}</td>
            <td style="font-size:12px;color:var(--text-muted)">${r.created_at ? new Date(r.created_at).toLocaleString('en-IN',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:true}) : '—'}</td>
        </tr>`;
    }).join('');
}

const today = new Date();
const week  = new Date(today); week.setDate(week.getDate() - 7);
document.getElementById('f-date-to').value   = today.toISOString().slice(0,10);
document.getElementById('f-date-from').value = week.toISOString().slice(0,10);

document.addEventListener('DOMContentLoaded', loadReport);
</script>
@endpush
