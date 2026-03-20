@extends('layouts.admin')

@section('title', 'Wallet Report')
@section('page-title', 'Wallet Report')

@section('content')

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
    <div>
        <h1 style="font-size:20px;font-weight:700;color:var(--text-primary)">Wallet Report</h1>
        <div class="breadcrumb" style="margin-bottom:0;margin-top:3px">
            <a href="/admin/dashboard">Dashboard</a>
            <svg class="breadcrumb-sep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span>Reports</span>
            <svg class="breadcrumb-sep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span>Wallet Report</span>
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
                <label style="display:block;font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Status</label>
                <select id="f-status" style="border:1px solid var(--border);border-radius:8px;padding:7px 12px;font-size:13px;color:var(--text-primary);background:#fff;outline:none;min-width:120px">
                    <option value="">All</option>
                    <option value="active">Active</option>
                    <option value="suspended">Suspended</option>
                    <option value="frozen">Frozen</option>
                </select>
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Role</label>
                <select id="f-role" style="border:1px solid var(--border);border-radius:8px;padding:7px 12px;font-size:13px;color:var(--text-primary);background:#fff;outline:none;min-width:120px">
                    <option value="">All Roles</option>
                    <option value="user">User</option>
                    <option value="retailer">Retailer</option>
                    <option value="distributor">Distributor</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Min Balance</label>
                <input type="number" id="f-min-balance" placeholder="0" style="border:1px solid var(--border);border-radius:8px;padding:7px 12px;font-size:13px;color:var(--text-primary);background:#fff;outline:none;width:100px">
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Max Balance</label>
                <input type="number" id="f-max-balance" placeholder="Any" style="border:1px solid var(--border);border-radius:8px;padding:7px 12px;font-size:13px;color:var(--text-primary);background:#fff;outline:none;width:100px">
            </div>
            <button class="btn btn-primary btn-sm" onclick="loadReport()">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Apply
            </button>
            <button class="btn btn-outline btn-sm" onclick="clearFilters()">Clear</button>
        </div>
    </div>
</div>

{{-- Summary Cards --}}
<div class="stats-grid" style="margin-bottom:20px">
    <div class="stat-card blue">
        <div class="stat-header"><div class="stat-label">Total Wallets</div>
            <div class="stat-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg></div>
        </div>
        <div class="stat-value" id="s-total-wallets">—</div>
        <div class="stat-amount" id="s-active-wallets">—</div>
        <div class="stat-footer"><span class="stat-pulse"></span><span class="stat-updated">Registered wallets</span></div>
    </div>
    <div class="stat-card green">
        <div class="stat-header"><div class="stat-label">Total Balance</div>
            <div class="stat-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        </div>
        <div class="stat-value" id="s-total-balance">—</div>
        <div class="stat-amount" id="s-avg-balance">—</div>
        <div class="stat-footer"><span class="stat-pulse"></span><span class="stat-updated">Combined balance</span></div>
    </div>
    <div class="stat-card orange">
        <div class="stat-header"><div class="stat-label">Total Credits</div>
            <div class="stat-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg></div>
        </div>
        <div class="stat-value" id="s-total-credits">—</div>
        <div class="stat-amount" id="s-credit-txns">—</div>
        <div class="stat-footer"><span class="stat-pulse"></span><span class="stat-updated">Wallet inflows</span></div>
    </div>
    <div class="stat-card red">
        <div class="stat-header"><div class="stat-label">Total Debits</div>
            <div class="stat-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/></svg></div>
        </div>
        <div class="stat-value" id="s-total-debits">—</div>
        <div class="stat-amount" id="s-debit-txns">—</div>
        <div class="stat-footer"><span class="stat-pulse"></span><span class="stat-updated">Wallet outflows</span></div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">
    {{-- Distribution Table --}}
    <div class="card">
        <div class="card-header"><div class="card-title">Balance Distribution</div></div>
        <div class="card-body" style="padding:0">
            <div id="dist-loading" class="loading-overlay"><div class="spinner"></div> Loading…</div>
            <div class="table-wrap" id="dist-table-wrap" style="display:none">
                <table>
                    <thead><tr><th>Range</th><th>Count</th><th>% of Total</th></tr></thead>
                    <tbody id="dist-tbody"></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Ledger --}}
    <div class="card">
        <div class="card-header"><div class="card-title">Ledger Summary</div></div>
        <div class="card-body" style="padding:0">
            <div id="ledger-loading" class="loading-overlay"><div class="spinner"></div> Loading…</div>
            <div class="table-wrap" id="ledger-table-wrap" style="display:none">
                <table>
                    <thead><tr><th>Date</th><th>Credits</th><th>Debits</th><th>Net</th></tr></thead>
                    <tbody id="ledger-tbody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Wallets Table --}}
<div class="card">
    <div class="card-header" style="justify-content:space-between">
        <div class="card-title">Wallet List</div>
        <span id="wallets-count" style="font-size:12px;color:var(--text-muted)">—</span>
    </div>
    <div class="card-body" style="padding:0">
        <div id="wallets-loading" class="loading-overlay"><div class="spinner"></div> Loading…</div>
        <div class="table-wrap" id="wallets-table-wrap" style="display:none">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Role</th>
                        <th>Balance</th>
                        <th>Total Credits</th>
                        <th>Total Debits</th>
                        <th>Status</th>
                        <th>Last Activity</th>
                    </tr>
                </thead>
                <tbody id="wallets-tbody"></tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function getFilters() {
    return {
        date_from:   document.getElementById('f-date-from').value,
        date_to:     document.getElementById('f-date-to').value,
        status:      document.getElementById('f-status').value,
        role:        document.getElementById('f-role').value,
        min_balance: document.getElementById('f-min-balance').value,
        max_balance: document.getElementById('f-max-balance').value,
    };
}

function buildQuery(p) {
    return Object.entries(p).filter(([,v]) => v !== '' && v != null).map(([k,v]) => k+'='+encodeURIComponent(v)).join('&');
}

function clearFilters() {
    ['f-date-from','f-date-to','f-min-balance','f-max-balance'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('f-status').value = '';
    document.getElementById('f-role').value = '';
    loadReport();
}

async function loadReport() {
    const q = buildQuery(getFilters());
    const res = await apiFetch('/api/v1/admin/reports/wallets?' + q);
    if (!res) return;
    const json = await res.json();
    const d = json.data || {};
    const s = d.summary || {};

    document.getElementById('s-total-wallets').textContent  = fmtNum(s.total_wallets ?? 0);
    document.getElementById('s-active-wallets').textContent = fmtNum(s.active_wallets ?? 0) + ' active';
    document.getElementById('s-total-balance').textContent  = fmtAmt(s.total_balance ?? 0);
    document.getElementById('s-avg-balance').textContent    = fmtAmt(s.avg_balance ?? 0) + ' avg';
    document.getElementById('s-total-credits').textContent  = fmtAmt(s.total_credits ?? 0);
    document.getElementById('s-credit-txns').textContent    = fmtNum(s.credit_count ?? 0) + ' txns';
    document.getElementById('s-total-debits').textContent   = fmtAmt(s.total_debits ?? 0);
    document.getElementById('s-debit-txns').textContent     = fmtNum(s.debit_count ?? 0) + ' txns';

    renderDistribution(d.distribution || []);
    renderLedger(d.ledger || []);
    renderWallets(d.wallets || []);
}

function renderDistribution(rows) {
    document.getElementById('dist-loading').style.display = 'none';
    document.getElementById('dist-table-wrap').style.display = 'block';
    const total = rows.reduce((s, r) => s + (r.count || 0), 0);
    const tbody = document.getElementById('dist-tbody');
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;color:var(--text-muted);padding:24px">No data</td></tr>';
        return;
    }
    tbody.innerHTML = rows.map(r => {
        const pct = total ? ((r.count / total) * 100).toFixed(1) : '0.0';
        return `<tr>
            <td>${r.range || r.label || '—'}</td>
            <td style="font-weight:600">${fmtNum(r.count)}</td>
            <td>
                <div style="display:flex;align-items:center;gap:6px">
                    <div class="progress" style="flex:1;min-width:60px"><div class="progress-bar" style="width:${pct}%;background:var(--accent-blue)"></div></div>
                    <span style="font-size:11px;color:var(--text-muted)">${pct}%</span>
                </div>
            </td>
        </tr>`;
    }).join('');
}

function renderLedger(rows) {
    document.getElementById('ledger-loading').style.display = 'none';
    document.getElementById('ledger-table-wrap').style.display = 'block';
    const tbody = document.getElementById('ledger-tbody');
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:var(--text-muted);padding:24px">No ledger data</td></tr>';
        return;
    }
    tbody.innerHTML = rows.map(r => {
        const net = (r.credits ?? 0) - (r.debits ?? 0);
        const nc = net >= 0 ? '#10b981' : '#ef4444';
        return `<tr>
            <td style="font-weight:600">${r.date || '—'}</td>
            <td style="color:#10b981">${fmtAmt(r.credits)}</td>
            <td style="color:#ef4444">${fmtAmt(r.debits)}</td>
            <td style="color:${nc};font-weight:700">${fmtAmt(net)}</td>
        </tr>`;
    }).join('');
}

function renderWallets(rows) {
    document.getElementById('wallets-loading').style.display = 'none';
    document.getElementById('wallets-table-wrap').style.display = 'block';
    document.getElementById('wallets-count').textContent = fmtNum(rows.length) + ' wallets';
    const tbody = document.getElementById('wallets-tbody');
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--text-muted);padding:24px">No wallets found</td></tr>';
        return;
    }
    tbody.innerHTML = rows.map((w, i) => {
        const st = (w.status || 'active').toLowerCase();
        const stBg = st === 'active' ? '#d1fae5' : st === 'suspended' ? '#fee2e2' : '#fef3c7';
        const stC  = st === 'active' ? '#059669' : st === 'suspended' ? '#dc2626' : '#d97706';
        return `<tr>
            <td style="color:var(--text-muted)">${i+1}</td>
            <td><strong>${w.user_name || w.name || '—'}</strong><br><small style="color:var(--text-muted)">${w.email || ''}</small></td>
            <td><span style="background:var(--bg-page);padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600">${w.role || '—'}</span></td>
            <td style="font-weight:700;color:var(--accent-blue)">${fmtAmt(w.balance)}</td>
            <td style="color:#10b981">${fmtAmt(w.total_credits)}</td>
            <td style="color:#ef4444">${fmtAmt(w.total_debits)}</td>
            <td><span style="background:${stBg};color:${stC};font-size:11px;font-weight:600;padding:2px 8px;border-radius:20px">${st}</span></td>
            <td style="font-size:12px;color:var(--text-muted)">${w.last_transaction_at ? fmtAgo(w.last_transaction_at) : '—'}</td>
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
