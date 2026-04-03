@extends('layouts.user')
@section('title','Transactions — RechargeHub')
@section('page-title','Transaction History')

@push('head')
<style>
.tab-bar{display:flex;gap:3px;background:var(--card);border:1px solid var(--border);border-radius:10px;padding:4px;margin-bottom:20px;width:fit-content}
.tab-btn{padding:7px 18px;border-radius:7px;border:none;cursor:pointer;font-family:inherit;font-size:13px;font-weight:500;color:var(--muted);background:none;transition:all .15s}
.tab-btn.active{background:linear-gradient(135deg,var(--blue-dk),var(--indigo));color:#fff}
.filter-bar{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:18px;align-items:flex-end}
.finp{background:var(--card2);border:1px solid var(--border2);border-radius:8px;padding:9px 12px;font-size:13px;color:var(--text);outline:none;font-family:inherit}
.finp:focus{border-color:var(--blue)}
.finp::placeholder{color:var(--muted2)}
.ftable{width:100%;border-collapse:collapse;font-size:13px}
.ftable th{padding:10px 14px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--muted2);border-bottom:1px solid var(--border)}
.ftable td{padding:11px 14px;border-bottom:1px solid rgba(255,255,255,.04);vertical-align:middle}
.ftable tr:hover td{background:var(--card)}
.badge{display:inline-flex;align-items:center;gap:4px;font-size:10.5px;font-weight:600;padding:2px 9px;border-radius:20px}
.badge-success{background:rgba(16,185,129,.12);color:#34d399}
.badge-pending{background:rgba(245,158,11,.12);color:#fbbf24}
.badge-failed{background:rgba(239,68,68,.12);color:#f87171}
.badge-credit{background:rgba(16,185,129,.12);color:#34d399}
.badge-debit{background:rgba(239,68,68,.12);color:#f87171}
.pager{display:flex;gap:6px;justify-content:center;margin-top:16px;flex-wrap:wrap}
.pager button{padding:5px 12px;border-radius:6px;border:1px solid var(--border2);background:var(--card);color:var(--muted);cursor:pointer;font-size:12px;font-family:inherit;transition:all .15s}
.pager button:hover,.pager button.active{background:var(--blue);border-color:var(--blue);color:#fff}
.pager button:disabled{opacity:.35;cursor:default}
.empty-state{text-align:center;padding:50px 20px;color:var(--muted)}
.empty-state svg{width:44px;height:44px;margin:0 auto 12px;opacity:.35;display:block}
.amount-pos{color:#34d399;font-weight:600}
.amount-neg{color:#f87171;font-weight:600}
.txn-icon{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.txn-info{display:flex;align-items:center;gap:10px}
</style>
@endpush

@section('content')
<div class="breadcrumb">
    <a href="/user/dashboard">Dashboard</a>
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>Transactions</span>
</div>

{{-- Tab Bar --}}
<div class="tab-bar">
    <button class="tab-btn active" id="tab-recharge" onclick="switchTab('recharge')">Recharges</button>
    <button class="tab-btn" id="tab-bbps"     onclick="switchTab('bbps')">Bill Payments</button>
    <button class="tab-btn" id="tab-wallet"   onclick="switchTab('wallet')">Wallet</button>
</div>

{{-- ═══ RECHARGE TAB ═══ --}}
<div id="pane-recharge">
    <div class="card" style="overflow:hidden">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
            <div>
                <div class="card-title">Recharge History</div>
                <div class="card-sub">All mobile, DTH and data recharges</div>
            </div>
            <div class="filter-bar" style="margin:0">
                <select class="finp" id="r-status" onchange="loadRecharges(1)" style="min-width:120px">
                    <option value="">All Status</option>
                    <option value="success">Success</option>
                    <option value="pending">Pending</option>
                    <option value="failed">Failed</option>
                </select>
                <input type="date" class="finp" id="r-from" onchange="loadRecharges(1)">
                <input type="date" class="finp" id="r-to"   onchange="loadRecharges(1)">
                <button class="btn btn-outline" onclick="clearFilters('r')">Clear</button>
            </div>
        </div>
        <div id="r-table-wrap">
            <table class="ftable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Mobile / Account</th>
                        <th>Operator</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Txn ID</th>
                    </tr>
                </thead>
                <tbody id="r-tbody"><tr><td colspan="7" style="text-align:center;padding:30px;color:var(--muted)">Loading…</td></tr></tbody>
            </table>
        </div>
        <div id="r-pager" class="pager"></div>
    </div>
</div>

{{-- ═══ BBPS TAB ═══ --}}
<div id="pane-bbps" style="display:none">
    <div class="card" style="overflow:hidden">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
            <div>
                <div class="card-title">Bill Payment History</div>
                <div class="card-sub">Electricity, water, gas, DTH and more</div>
            </div>
            <div class="filter-bar" style="margin:0">
                <select class="finp" id="b-cat" onchange="loadBbps(1)" style="min-width:130px">
                    <option value="">All Categories</option>
                    <option value="electricity">Electricity</option>
                    <option value="water">Water</option>
                    <option value="gas">Gas</option>
                    <option value="dth">DTH</option>
                    <option value="broadband">Broadband</option>
                    <option value="landline">Landline</option>
                    <option value="insurance">Insurance</option>
                    <option value="loan">Loan EMI</option>
                    <option value="fastag">FASTag</option>
                </select>
                <select class="finp" id="b-status" onchange="loadBbps(1)" style="min-width:120px">
                    <option value="">All Status</option>
                    <option value="success">Success</option>
                    <option value="pending">Pending</option>
                    <option value="failed">Failed</option>
                </select>
                <input type="date" class="finp" id="b-from" onchange="loadBbps(1)">
                <input type="date" class="finp" id="b-to"   onchange="loadBbps(1)">
                <button class="btn btn-outline" onclick="clearFilters('b')">Clear</button>
            </div>
        </div>
        <div id="b-table-wrap">
            <table class="ftable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Biller</th>
                        <th>Consumer No.</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Txn ID</th>
                    </tr>
                </thead>
                <tbody id="b-tbody"><tr><td colspan="7" style="text-align:center;padding:30px;color:var(--muted)">Loading…</td></tr></tbody>
            </table>
        </div>
        <div id="b-pager" class="pager"></div>
    </div>
</div>

{{-- ═══ WALLET TAB ═══ --}}
<div id="pane-wallet" style="display:none">
    <div class="card" style="overflow:hidden">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
            <div>
                <div class="card-title">Wallet Transactions</div>
                <div class="card-sub">Credits, debits and adjustments</div>
            </div>
            <div class="filter-bar" style="margin:0">
                <select class="finp" id="w-type" onchange="loadWallet(1)" style="min-width:130px">
                    <option value="">All Types</option>
                    <option value="credit">Credit</option>
                    <option value="debit">Debit</option>
                </select>
                <input type="date" class="finp" id="w-from" onchange="loadWallet(1)">
                <input type="date" class="finp" id="w-to"   onchange="loadWallet(1)">
                <button class="btn btn-outline" onclick="clearFilters('w')">Clear</button>
            </div>
        </div>
        <div id="w-table-wrap">
            <table class="ftable">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Balance After</th>
                        <th>Date</th>
                        <th>Reference</th>
                    </tr>
                </thead>
                <tbody id="w-tbody"><tr><td colspan="6" style="text-align:center;padding:30px;color:var(--muted)">Loading…</td></tr></tbody>
            </table>
        </div>
        <div id="w-pager" class="pager"></div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let currentTab = 'recharge';
const rPage = {r:1, b:1, w:1};

function switchTab(t) {
    currentTab = t;
    ['recharge','bbps','wallet'].forEach(x => {
        document.getElementById('tab-'+x).classList.toggle('active', x===t);
        document.getElementById('pane-'+x).style.display = x===t ? '' : 'none';
    });
    if (t==='recharge' && !window._rLoaded) { loadRecharges(1); window._rLoaded=true; }
    if (t==='bbps'     && !window._bLoaded) { loadBbps(1);     window._bLoaded=true; }
    if (t==='wallet'   && !window._wLoaded) { loadWallet(1);   window._wLoaded=true; }
}

function clearFilters(pfx) {
    if (pfx==='r') { document.getElementById('r-status').value=''; document.getElementById('r-from').value=''; document.getElementById('r-to').value=''; loadRecharges(1); }
    if (pfx==='b') { document.getElementById('b-cat').value=''; document.getElementById('b-status').value=''; document.getElementById('b-from').value=''; document.getElementById('b-to').value=''; loadBbps(1); }
    if (pfx==='w') { document.getElementById('w-type').value=''; document.getElementById('w-from').value=''; document.getElementById('w-to').value=''; loadWallet(1); }
}

/* ── Helpers ── */
function fmtDate(s) {
    if (!s) return '—';
    const d = new Date(s);
    return d.toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'}) + ' ' +
           d.toLocaleTimeString('en-IN',{hour:'2-digit',minute:'2-digit'});
}
function fmtAmt(n) { return '₹' + Number(n||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2}); }
function statusBadge(s) {
    const map = {success:'badge-success',pending:'badge-pending',failed:'badge-failed'};
    return `<span class="badge ${map[s]||'badge-pending'}">${(s||'').toUpperCase()}</span>`;
}
function buildPager(pager, loadFn) {
    const last = pager.last_page || 1;
    const cur  = pager.current_page || 1;
    let h = '';
    if (last <= 1) return h;
    h += `<button ${cur<=1?'disabled':''} onclick="${loadFn}(${cur-1})">‹ Prev</button>`;
    for (let p = Math.max(1,cur-2); p <= Math.min(last,cur+2); p++)
        h += `<button class="${p===cur?'active':''}" onclick="${loadFn}(${p})">${p}</button>`;
    h += `<button ${cur>=last?'disabled':''} onclick="${loadFn}(${cur+1})">Next ›</button>`;
    return h;
}
function emptyRow(cols, msg='No records found.') {
    return `<tr><td colspan="${cols}"><div class="empty-state"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>${msg}</div></td></tr>`;
}

/* ── Recharges ── */
async function loadRecharges(page) {
    rPage.r = page;
    const status = document.getElementById('r-status').value;
    const from   = document.getElementById('r-from').value;
    const to     = document.getElementById('r-to').value;
    const params = new URLSearchParams({page, per_page:15});
    if (status) params.set('status', status);
    if (from)   params.set('date_from', from);
    if (to)     params.set('date_to', to);

    document.getElementById('r-tbody').innerHTML = '<tr><td colspan="7" style="text-align:center;padding:30px;color:var(--muted)">Loading…</td></tr>';
    try {
        const res = await apiFetch('/api/v1/transactions?' + params);
        const d   = await res.json();
        const rows = d.data || [];
        if (!rows.length) { document.getElementById('r-tbody').innerHTML = emptyRow(7); }
        else {
            document.getElementById('r-tbody').innerHTML = rows.map(t => `
                <tr>
                    <td style="color:var(--muted2)">#${t.id}</td>
                    <td>${t.mobile || t.mobile_number || '—'}</td>
                    <td>${t.operator_name || t.operator_code || t.operator || '—'}</td>
                    <td style="font-weight:600">${fmtAmt(t.amount)}</td>
                    <td>${statusBadge(t.status)}</td>
                    <td style="color:var(--muted2);font-size:12px">${fmtDate(t.created_at)}</td>
                    <td style="color:var(--muted2);font-size:11px;font-family:monospace">${t.operator_txn_id || t.transaction_id || '—'}</td>
                </tr>
            `).join('');
        }
        document.getElementById('r-pager').innerHTML = buildPager(d, 'loadRecharges');
    } catch(e) {
        document.getElementById('r-tbody').innerHTML = emptyRow(7, 'Failed to load. Try again.');
    }
}

/* ── BBPS ── */
async function loadBbps(page) {
    rPage.b = page;
    const cat    = document.getElementById('b-cat').value;
    const status = document.getElementById('b-status').value;
    const from   = document.getElementById('b-from').value;
    const to     = document.getElementById('b-to').value;
    const params = new URLSearchParams({page, per_page:15});
    if (cat)    params.set('category', cat);
    if (status) params.set('status', status);
    if (from)   params.set('date_from', from);
    if (to)     params.set('date_to', to);

    document.getElementById('b-tbody').innerHTML = '<tr><td colspan="7" style="text-align:center;padding:30px;color:var(--muted)">Loading…</td></tr>';
    try {
        const res = await apiFetch('/api/v1/bbps/history?' + params);
        const d   = await res.json();
        const rows = d.data || [];
        if (!rows.length) { document.getElementById('b-tbody').innerHTML = emptyRow(7); }
        else {
            document.getElementById('b-tbody').innerHTML = rows.map(t => `
                <tr>
                    <td style="color:var(--muted2)">#${t.id}</td>
                    <td>
                        <div style="font-weight:500">${t.biller_name || '—'}</div>
                        <div style="font-size:11px;color:var(--muted2);text-transform:capitalize">${t.biller_category || ''}</div>
                    </td>
                    <td style="font-family:monospace;font-size:12px">${t.consumer_number || '—'}</td>
                    <td style="font-weight:600">${fmtAmt(t.amount)}</td>
                    <td>${statusBadge(t.status)}</td>
                    <td style="color:var(--muted2);font-size:12px">${fmtDate(t.created_at)}</td>
                    <td style="color:var(--muted2);font-size:11px;font-family:monospace">${t.txn_id || '—'}</td>
                </tr>
            `).join('');
        }
        document.getElementById('b-pager').innerHTML = buildPager(d, 'loadBbps');
    } catch(e) {
        document.getElementById('b-tbody').innerHTML = emptyRow(7, 'Failed to load. Try again.');
    }
}

/* ── Wallet ── */
async function loadWallet(page) {
    rPage.w = page;
    const type = document.getElementById('w-type').value;
    const from = document.getElementById('w-from').value;
    const to   = document.getElementById('w-to').value;
    const params = new URLSearchParams({page, per_page:15});
    if (type) params.set('type', type);
    if (from) params.set('date_from', from);
    if (to)   params.set('date_to', to);

    document.getElementById('w-tbody').innerHTML = '<tr><td colspan="6" style="text-align:center;padding:30px;color:var(--muted)">Loading…</td></tr>';
    try {
        const res = await apiFetch('/api/v1/wallet/transactions?' + params);
        const d   = await res.json();
        const rows = d.data || [];
        if (!rows.length) { document.getElementById('w-tbody').innerHTML = emptyRow(6); }
        else {
            document.getElementById('w-tbody').innerHTML = rows.map(t => {
                const isCredit = (t.type || t.transaction_type || '') === 'credit';
                return `
                <tr>
                    <td><span class="badge ${isCredit?'badge-credit':'badge-debit'}">${isCredit?'CREDIT':'DEBIT'}</span></td>
                    <td>${t.description || t.remarks || '—'}</td>
                    <td class="${isCredit?'amount-pos':'amount-neg'}">${isCredit?'+':'−'}${fmtAmt(t.amount)}</td>
                    <td style="font-weight:500">${fmtAmt(t.balance_after)}</td>
                    <td style="color:var(--muted2);font-size:12px">${fmtDate(t.created_at)}</td>
                    <td style="color:var(--muted2);font-size:11px;font-family:monospace">${t.reference || t.txn_id || '—'}</td>
                </tr>`;
            }).join('');
        }
        document.getElementById('w-pager').innerHTML = buildPager(d, 'loadWallet');
    } catch(e) {
        document.getElementById('w-tbody').innerHTML = emptyRow(6, 'Failed to load. Try again.');
    }
}

/* ── Init ── */
loadRecharges(1);
window._rLoaded = true;
</script>
@endpush
