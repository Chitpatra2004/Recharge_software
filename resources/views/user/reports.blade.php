@extends('layouts.user')
@section('title','Reports')
@section('page-title','Reports')

@section('content')
<div class="breadcrumb">
    <a href="/user/dashboard">Dashboard</a>
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>Reports</span>
</div>

{{-- Tabs --}}
<div style="display:flex;gap:4px;background:var(--card);border:1px solid var(--border);border-radius:12px;padding:5px;margin-bottom:20px;width:fit-content">
    <button class="tab-btn active" id="tab-txn"     onclick="switchTab('txn')">Transaction History</button>
    <button class="tab-btn"        id="tab-account" onclick="switchTab('account')">Account Report</button>
    <button class="tab-btn"        id="tab-topup"   onclick="switchTab('topup')">Topup Report</button>
</div>

{{-- ── TAB 1: Transaction History ── --}}
<div id="panel-txn">
    <div class="card" style="margin-bottom:16px">
        <div class="card-body" style="padding:14px 18px">
            <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
                <div>
                    <label class="flabel">From Date</label>
                    <input type="date" id="r1-from" class="finput" style="color-scheme:dark">
                </div>
                <div>
                    <label class="flabel">To Date</label>
                    <input type="date" id="r1-to" class="finput" style="color-scheme:dark">
                </div>
                <div>
                    <label class="flabel">Status</label>
                    <select id="r1-status" class="finput">
                        <option value="">All</option>
                        <option value="success">Success</option>
                        <option value="failed">Failed</option>
                        <option value="refunded">Refunded</option>
                        <option value="processing">Processing</option>
                    </select>
                </div>
                <div>
                    <label class="flabel">Operator</label>
                    <select id="r1-op" class="finput">
                        <option value="">All</option>
                        <option value="AIRTEL">Airtel</option>
                        <option value="JIO">Jio</option>
                        <option value="VI">Vi</option>
                        <option value="BSNL">BSNL</option>
                        <option value="TATA_SKY">Tata Play</option>
                    </select>
                </div>
                <button class="btn btn-primary btn-sm" onclick="loadR1()">Apply</button>
                <button class="btn btn-outline btn-sm" onclick="clearR1()">Clear</button>
                <button class="btn btn-outline btn-sm" onclick="downloadCSV('txn')" style="margin-left:auto">⬇ Download CSV</button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header" style="justify-content:space-between">
            <span class="card-title">Transaction History</span>
            <span id="r1-count" style="font-size:12px;color:var(--muted)"></span>
        </div>
        <div class="table-wrap">
            <table id="r1-table">
                <thead><tr><th>#</th><th>Mobile</th><th>Operator</th><th>Type</th><th>Amount</th><th>Status</th><th>Date</th><th>Receipt</th></tr></thead>
                <tbody id="r1-tbody"><tr><td colspan="8"><div class="loading"><div class="spinner"></div> Loading…</div></td></tr></tbody>
            </table>
        </div>
        <div class="card-footer" id="r1-pag" style="gap:8px;justify-content:flex-end"></div>
    </div>
</div>

{{-- ── TAB 2: Account Report ── --}}
<div id="panel-account" style="display:none">
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px">
        <div class="stat-card blue"><div class="stat-label">Total Recharges</div><div class="stat-value" id="ac-total">—</div><div class="stat-sub">All time</div></div>
        <div class="stat-card green"><div class="stat-label">Successful</div><div class="stat-value" id="ac-success">—</div></div>
        <div class="stat-card red"><div class="stat-label">Failed</div><div class="stat-value" id="ac-failed">—</div></div>
        <div class="stat-card orange"><div class="stat-label">Total Spent</div><div class="stat-value" id="ac-spent" style="font-size:18px">—</div></div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
        <div class="card">
            <div class="card-header"><span class="card-title">Account Details</span></div>
            <div class="card-body" id="ac-details" style="font-size:13px;display:flex;flex-direction:column;gap:12px">
                <div class="loading"><div class="spinner"></div> Loading…</div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><span class="card-title">Wallet Summary</span></div>
            <div class="card-body" id="ac-wallet" style="font-size:13px;display:flex;flex-direction:column;gap:12px">
                <div class="loading"><div class="spinner"></div> Loading…</div>
            </div>
        </div>
    </div>

    <div style="text-align:right;margin-bottom:16px">
        <button class="btn btn-outline btn-sm" onclick="printAccountReport()">🖨 Print Account Report</button>
    </div>

    <div class="card">
        <div class="card-header"><span class="card-title">Recent Activity (Last 10)</span></div>
        <div class="table-wrap">
            <table><thead><tr><th>#</th><th>Mobile</th><th>Operator</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
            <tbody id="ac-recent"><tr><td colspan="6"><div class="loading"><div class="spinner"></div></div></td></tr></tbody>
            </table>
        </div>
    </div>
</div>

{{-- ── TAB 3: Topup Report ── --}}
<div id="panel-topup" style="display:none">
    <div class="card" style="margin-bottom:16px">
        <div class="card-body" style="padding:14px 18px">
            <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
                <div>
                    <label class="flabel">From Date</label>
                    <input type="date" id="r3-from" class="finput" style="color-scheme:dark">
                </div>
                <div>
                    <label class="flabel">To Date</label>
                    <input type="date" id="r3-to" class="finput" style="color-scheme:dark">
                </div>
                <button class="btn btn-primary btn-sm" onclick="loadR3()">Apply</button>
                <button class="btn btn-outline btn-sm" onclick="document.getElementById('r3-from').value='';document.getElementById('r3-to').value='';loadR3()">Clear</button>
                <button class="btn btn-outline btn-sm" onclick="downloadCSV('topup')" style="margin-left:auto">⬇ Download CSV</button>
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px">
        <div class="stat-card blue"><div class="stat-label">Total Topups</div><div class="stat-value" id="tp-count">—</div></div>
        <div class="stat-card green"><div class="stat-label">Total Credited</div><div class="stat-value" id="tp-credit" style="font-size:18px">—</div></div>
        <div class="stat-card orange"><div class="stat-label">Total Debited</div><div class="stat-value" id="tp-debit" style="font-size:18px">—</div></div>
    </div>

    <div class="card">
        <div class="card-header" style="justify-content:space-between">
            <span class="card-title">Wallet Transactions</span>
            <span id="r3-count" style="font-size:12px;color:var(--muted)"></span>
        </div>
        <div class="table-wrap">
            <table id="r3-table">
                <thead><tr><th>Type</th><th>Amount</th><th>Description</th><th>Balance After</th><th>Date</th><th>Receipt</th></tr></thead>
                <tbody id="r3-tbody"><tr><td colspan="6"><div class="loading"><div class="spinner"></div> Loading…</div></td></tr></tbody>
            </table>
        </div>
        <div class="card-footer" id="r3-pag" style="gap:8px;justify-content:flex-end"></div>
    </div>
</div>
@endsection

@push('head')
<style>
.tab-btn{background:none;border:none;color:var(--muted);font-size:13px;font-weight:600;padding:8px 18px;border-radius:8px;cursor:pointer;font-family:inherit;transition:all .15s;white-space:nowrap}
.tab-btn:hover{background:var(--card2);color:var(--text)}
.tab-btn.active{background:linear-gradient(135deg,var(--blue-dk),var(--indigo));color:#fff}
.flabel{font-size:11px;font-weight:600;color:var(--muted);display:block;margin-bottom:4px}
.finput{background:var(--card2);border:1px solid var(--border2);border-radius:7px;padding:6px 10px;font-size:13px;color:var(--text);outline:none;font-family:inherit;color-scheme:dark}
.finput option{background:var(--card2,#1e2538);color:var(--text,#e2e8f0)}
.det-row{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--border)}
.det-row:last-child{border-bottom:none}
.det-label{color:var(--muted);font-size:12px}
.det-val{font-weight:600;color:var(--text)}
</style>
@endpush

@push('scripts')
<script>
let r1Data = [], r3Data = [];

/* ── Tabs ─────────────────────────────── */
function switchTab(tab) {
    ['txn','account','topup'].forEach(t => {
        document.getElementById('panel-' + t).style.display = t === tab ? 'block' : 'none';
        document.getElementById('tab-' + t).classList.toggle('active', t === tab);
    });
    if (tab === 'txn')     loadR1();
    if (tab === 'account') loadAccount();
    if (tab === 'topup')   loadR3();
}

/* ── Tab 1: Transaction History ─────── */
function clearR1() {
    ['r1-from','r1-to','r1-status','r1-op'].forEach(id => document.getElementById(id).value = '');
    loadR1();
}

async function loadR1(page = 1) {
    document.getElementById('r1-tbody').innerHTML = '<tr><td colspan="8"><div class="loading"><div class="spinner"></div> Loading…</div></td></tr>';
    const p = new URLSearchParams({ page, per_page: 50 });
    const status = document.getElementById('r1-status').value;
    const op     = document.getElementById('r1-op').value;
    const from   = document.getElementById('r1-from').value;
    const to     = document.getElementById('r1-to').value;
    if (status) p.set('status', status);
    if (op)     p.set('operator_code', op);
    if (from)   p.set('from', from);
    if (to)     p.set('to', to);

    const res = await apiFetch('/api/v1/transactions?' + p);
    if (!res) return;
    const data = await res.json();
    const txns = data.data?.data || data.data || [];
    r1Data = txns;
    const total = data.data?.total ?? data.total ?? null;
    document.getElementById('r1-count').textContent = total != null ? fmtNum(total) + ' records' : '';

    document.getElementById('r1-tbody').innerHTML = txns.length
        ? txns.map(t => {
            const sc = t.status==='success'?'success':t.status==='failed'?'failure':'pending';
            const date = t.created_at ? new Date(t.created_at).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'}) : '—';
            return `<tr>
                <td style="font-family:monospace;font-size:11px;color:var(--muted)">#${t.id}</td>
                <td style="font-weight:600">${t.mobile||'—'}</td>
                <td>${t.operator_code||'—'}</td>
                <td style="font-size:12px;text-transform:capitalize">${t.recharge_type||'—'}</td>
                <td style="font-weight:700">₹${parseFloat(t.amount||0).toFixed(2)}</td>
                <td><span class="badge ${sc}">${t.status}</span></td>
                <td style="font-size:12px;color:var(--muted)">${date}</td>
                <td>${t.status==='success'||t.status==='refunded'
                    ? `<button onclick='printTxnReceipt(${JSON.stringify(t)})' style="background:rgba(16,185,129,.15);border:1px solid rgba(16,185,129,.25);color:#34d399;font-size:11px;font-weight:600;padding:4px 10px;border-radius:6px;cursor:pointer">Receipt</button>`
                    : '<span style="font-size:11px;color:var(--muted2)">—</span>'}</td>
            </tr>`;
        }).join('')
        : '<tr><td colspan="8" style="text-align:center;color:var(--muted);padding:30px">No transactions found</td></tr>';

    const meta = data.data || data;
    buildPagination('r1-pag', meta.current_page || page, meta.last_page || 1, 'loadR1');
}

/* ── Tab 2: Account Report ────────────── */
async function loadAccount() {
    const [txnRes, walRes] = await Promise.all([
        apiFetch('/api/v1/transactions?per_page=10'),
        apiFetch('/api/v1/wallet/balance'),
    ]);
    const user = getUserData();

    if (txnRes?.ok) {
        const d    = await txnRes.json();
        const txns = d.data?.data || d.data || [];
        const total = d.data?.total ?? d.total ?? 0;
        let success = 0, failed = 0, spent = 0;
        txns.forEach(t => {
            if (t.status === 'success') { success++; spent += parseFloat(t.amount||0); }
            if (t.status === 'failed')  failed++;
        });
        document.getElementById('ac-total').textContent   = fmtNum(total);
        document.getElementById('ac-success').textContent = fmtNum(success);
        document.getElementById('ac-failed').textContent  = fmtNum(failed);
        document.getElementById('ac-spent').textContent   = fmtAmt(spent);

        document.getElementById('ac-details').innerHTML = `
            <div class="det-row"><span class="det-label">Name</span><span class="det-val">${user.name||'—'}</span></div>
            <div class="det-row"><span class="det-label">Email</span><span class="det-val">${user.email||'—'}</span></div>
            <div class="det-row"><span class="det-label">Mobile</span><span class="det-val">${user.mobile||'—'}</span></div>
            <div class="det-row"><span class="det-label">Role</span><span class="det-val" style="text-transform:capitalize">${(user.role||'—').replace('_',' ')}</span></div>
            <div class="det-row"><span class="det-label">User ID</span><span class="det-val" style="font-family:monospace">#${user.id||'—'}</span></div>`;

        document.getElementById('ac-recent').innerHTML = txns.length
            ? txns.map(t => {
                const sc = t.status==='success'?'success':t.status==='failed'?'failure':'pending';
                const date = t.created_at ? new Date(t.created_at).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'}) : '—';
                return `<tr>
                    <td style="font-family:monospace;font-size:11px;color:var(--muted)">#${t.id}</td>
                    <td style="font-weight:600">${t.mobile||'—'}</td>
                    <td>${t.operator_code||'—'}</td>
                    <td style="font-weight:700">₹${parseFloat(t.amount||0).toFixed(2)}</td>
                    <td><span class="badge ${sc}">${t.status}</span></td>
                    <td style="font-size:12px;color:var(--muted)">${date}</td>
                </tr>`;
            }).join('')
            : '<tr><td colspan="6" style="text-align:center;color:var(--muted);padding:20px">No transactions yet</td></tr>';
    }

    if (walRes?.ok) {
        const d = await walRes.json();
        document.getElementById('ac-wallet').innerHTML = `
            <div class="det-row"><span class="det-label">Current Balance</span><span class="det-val" style="color:var(--green);font-size:16px">${fmtAmt(d.balance)}</span></div>
            <div class="det-row"><span class="det-label">Reserved</span><span class="det-val">${fmtAmt(d.reserved_balance||0)}</span></div>
            <div class="det-row"><span class="det-label">Total Recharged</span><span class="det-val">${fmtAmt(d.total_recharged||0)}</span></div>
            <div class="det-row"><span class="det-label">Total Topped Up</span><span class="det-val">${fmtAmt(d.total_topup||0)}</span></div>
            <div class="det-row"><span class="det-label">Status</span><span class="det-val" style="color:var(--green)">${d.status||'active'}</span></div>`;
    }
}

function printAccountReport() {
    const user = getUserData();
    const w = window.open('', '_blank', 'width=700,height=800');
    const rows = Array.from(document.querySelectorAll('#ac-recent tr')).map(r => r.outerHTML).join('');
    const detRows = document.getElementById('ac-details').innerHTML;
    const walRows = document.getElementById('ac-wallet').innerHTML;
    w.document.write(`<!DOCTYPE html><html><head><title>Account Report</title>
    <style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:Arial,sans-serif;padding:30px;color:#111}
    h1{font-size:22px;font-weight:800;color:#2563eb;margin-bottom:4px}
    h2{font-size:15px;font-weight:700;margin:20px 0 10px;color:#374151}
    .det-row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f1f5f9;font-size:13px}
    .det-label{color:#6b7280}.det-val{font-weight:600}
    table{width:100%;border-collapse:collapse;font-size:12px;margin-top:10px}
    th{background:#f8fafc;padding:8px;text-align:left;font-weight:600;color:#374151;border-bottom:1px solid #e5e7eb}
    td{padding:8px;border-bottom:1px solid #f1f5f9}
    .grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}
    .footer{text-align:center;margin-top:30px;font-size:11px;color:#9ca3af}
    @media print{body{padding:0}}</style></head><body>
    <h1>RechargeHub — Account Report</h1>
    <div style="font-size:12px;color:#6b7280;margin-bottom:20px">Generated: ${new Date().toLocaleString('en-IN')}</div>
    <div class="grid">
    <div><h2>Account Details</h2>${detRows}</div>
    <div><h2>Wallet Summary</h2>${walRows}</div>
    </div>
    <h2>Recent Transactions</h2>
    <table><thead><tr><th>#</th><th>Mobile</th><th>Operator</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead><tbody>${rows}</tbody></table>
    <div class="footer">RechargeHub • Account Report • ${new Date().toLocaleDateString('en-IN')}</div>
    <script>window.onload=()=>{window.print()}<\/script></body></html>`);
    w.document.close();
}

/* ── Tab 3: Topup Report ─────────────── */
async function loadR3(page = 1) {
    document.getElementById('r3-tbody').innerHTML = '<tr><td colspan="6"><div class="loading"><div class="spinner"></div> Loading…</div></td></tr>';
    const p = new URLSearchParams({ page, per_page: 50 });
    const from = document.getElementById('r3-from').value;
    const to   = document.getElementById('r3-to').value;

    const res = await apiFetch('/api/v1/wallet/transactions?' + p);
    if (!res) return;
    const d    = await res.json();
    const txns = d.data?.data || d.data || [];
    r3Data = txns;

    let creditTotal = 0, debitTotal = 0, topupCount = 0;
    txns.forEach(t => {
        if (t.type === 'credit' || t.type === 'release') { creditTotal += parseFloat(t.amount||0); topupCount++; }
        else debitTotal += parseFloat(t.amount||0);
    });
    document.getElementById('tp-count').textContent  = fmtNum(topupCount);
    document.getElementById('tp-credit').textContent = fmtAmt(creditTotal);
    document.getElementById('tp-debit').textContent  = fmtAmt(debitTotal);

    const total = d.data?.total ?? d.total ?? txns.length;
    document.getElementById('r3-count').textContent = fmtNum(total) + ' records';

    const typeColor = { credit:'var(--green)', debit:'var(--red)', reserve:'var(--orange)', release:'var(--blue)', reversal:'#a78bfa' };

    document.getElementById('r3-tbody').innerHTML = txns.length
        ? txns.map(t => {
            const isCredit = t.type==='credit'||t.type==='release';
            const date = t.created_at ? new Date(t.created_at).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'}) : '—';
            return `<tr>
                <td><span style="font-size:11px;font-weight:700;color:${typeColor[t.type]||'#fff'}">${(t.type||'').toUpperCase()}</span></td>
                <td style="font-weight:700;color:${isCredit?'var(--green)':'var(--red)'}">${isCredit?'+':'−'}₹${parseFloat(t.amount||0).toFixed(2)}</td>
                <td style="color:var(--muted);font-size:12px">${t.description||'—'}</td>
                <td style="font-weight:600">${fmtAmt(t.balance_after)}</td>
                <td style="font-size:12px;color:var(--muted)">${date}</td>
                <td><button onclick='printTopupReceipt(${JSON.stringify(t)})' style="background:rgba(99,102,241,.15);border:none;color:#a5b4fc;font-size:11px;font-weight:600;padding:4px 10px;border-radius:6px;cursor:pointer">Receipt</button></td>
            </tr>`;
        }).join('')
        : '<tr><td colspan="6" style="text-align:center;color:var(--muted);padding:30px">No wallet transactions found</td></tr>';

    const meta = d.data || d;
    buildPagination('r3-pag', meta.current_page || page, meta.last_page || 1, 'loadR3');
}

/* ── Shared: Pagination ───────────────── */
function buildPagination(id, curr, last, fn) {
    const pag = document.getElementById(id);
    if (last > 1) {
        let h = '';
        if (curr > 1) h += `<button class="btn btn-outline btn-sm" onclick="${fn}(${curr-1})">← Prev</button>`;
        h += `<span style="font-size:12px;color:var(--muted)">Page ${curr} of ${last}</span>`;
        if (curr < last) h += `<button class="btn btn-outline btn-sm" onclick="${fn}(${curr+1})">Next →</button>`;
        pag.innerHTML = h;
    } else pag.innerHTML = '';
}

/* ── Receipt printers ─────────────────── */
function printTxnReceipt(txn) {
    const user = getUserData();
    const date = txn.created_at ? new Date(txn.created_at).toLocaleString('en-IN') : '—';
    const w = window.open('', '_blank', 'width=480,height=680');
    w.document.write(`<!DOCTYPE html><html><head><title>Receipt #${txn.id}</title>
    <style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:Arial,sans-serif;padding:30px;color:#111}
    .header{text-align:center;margin-bottom:24px}.brand{font-size:22px;font-weight:800;color:#2563eb}
    .amount{font-size:36px;font-weight:900;margin:12px 0}.status{color:#059669;font-weight:600;font-size:14px}
    table{width:100%;border-collapse:collapse;margin-top:20px;font-size:13px}
    td{padding:10px 0;border-bottom:1px solid #f1f5f9}td:last-child{text-align:right;font-weight:600}
    .footer{text-align:center;margin-top:24px;font-size:11px;color:#9ca3af}
    @media print{body{padding:0}}</style></head><body>
    <div class="header"><div class="brand">RechargeHub</div>
    <div style="font-size:12px;color:#6b7280;margin-top:4px">Transaction Receipt</div></div>
    <div style="text-align:center"><div class="amount">₹${parseFloat(txn.amount||0).toFixed(2)}</div>
    <div class="status">${(txn.status||'').toUpperCase()}</div></div>
    <table>
    <tr><td style="color:#6b7280">Transaction ID</td><td>#${txn.id}</td></tr>
    <tr><td style="color:#6b7280">Mobile</td><td>${txn.mobile||'—'}</td></tr>
    <tr><td style="color:#6b7280">Operator</td><td>${txn.operator_code||'—'}</td></tr>
    <tr><td style="color:#6b7280">Type</td><td>${(txn.recharge_type||'—').toUpperCase()}</td></tr>
    <tr><td style="color:#6b7280">Amount</td><td>₹${parseFloat(txn.amount||0).toFixed(2)}</td></tr>
    ${txn.operator_ref?`<tr><td style="color:#6b7280">Operator Ref</td><td>${txn.operator_ref}</td></tr>`:''}
    <tr><td style="color:#6b7280">Date & Time</td><td>${date}</td></tr>
    <tr><td style="color:#6b7280">Account</td><td>${user.name||'—'}</td></tr>
    </table>
    <div class="footer">Thank you for using RechargeHub</div>
    <script>window.onload=()=>{window.print()}<\/script></body></html>`);
    w.document.close();
}

function printTopupReceipt(txn) {
    const user = getUserData();
    const date = txn.created_at ? new Date(txn.created_at).toLocaleString('en-IN') : '—';
    const isCredit = txn.type==='credit'||txn.type==='release';
    const w = window.open('', '_blank', 'width=480,height=600');
    w.document.write(`<!DOCTYPE html><html><head><title>Wallet Receipt</title>
    <style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:Arial,sans-serif;padding:30px;color:#111}
    .brand{font-size:22px;font-weight:800;color:#2563eb;text-align:center;margin-bottom:4px}
    .sub{font-size:12px;color:#6b7280;text-align:center;margin-bottom:20px}
    .amount{font-size:36px;font-weight:900;text-align:center;color:${isCredit?'#059669':'#dc2626'};margin:10px 0}
    table{width:100%;border-collapse:collapse;margin-top:20px;font-size:13px}
    td{padding:10px 0;border-bottom:1px solid #f1f5f9}td:last-child{text-align:right;font-weight:600}
    .footer{text-align:center;margin-top:24px;font-size:11px;color:#9ca3af}
    @media print{body{padding:0}}</style></head><body>
    <div class="brand">RechargeHub</div>
    <div class="sub">Wallet Transaction Receipt</div>
    <div class="amount">${isCredit?'+':'−'}₹${parseFloat(txn.amount||0).toFixed(2)}</div>
    <div style="text-align:center;font-size:13px;font-weight:600;color:#374151">${(txn.type||'').toUpperCase()}</div>
    <table>
    <tr><td style="color:#6b7280">Reference</td><td>${txn.txn_id||txn.id||'—'}</td></tr>
    <tr><td style="color:#6b7280">Type</td><td>${(txn.type||'').toUpperCase()}</td></tr>
    <tr><td style="color:#6b7280">Amount</td><td>${isCredit?'+':'−'}₹${parseFloat(txn.amount||0).toFixed(2)}</td></tr>
    <tr><td style="color:#6b7280">Balance After</td><td>₹${parseFloat(txn.balance_after||0).toFixed(2)}</td></tr>
    <tr><td style="color:#6b7280">Description</td><td>${txn.description||'—'}</td></tr>
    <tr><td style="color:#6b7280">Date & Time</td><td>${date}</td></tr>
    <tr><td style="color:#6b7280">Account</td><td>${user.name||'—'}</td></tr>
    </table>
    <div class="footer">RechargeHub • Wallet Receipt</div>
    <script>window.onload=()=>{window.print()}<\/script></body></html>`);
    w.document.close();
}

/* ── CSV Download ─────────────────────── */
function downloadCSV(type) {
    let headers, rows, filename;
    if (type === 'txn') {
        headers = ['ID','Mobile','Operator','Type','Amount','Status','Date'];
        rows = r1Data.map(t => [t.id, t.mobile, t.operator_code, t.recharge_type, t.amount, t.status, t.created_at]);
        filename = 'transaction-history.csv';
    } else {
        headers = ['Type','Amount','Description','Balance After','Date'];
        rows = r3Data.map(t => [t.type, t.amount, t.description, t.balance_after, t.created_at]);
        filename = 'wallet-topup-report.csv';
    }
    const csv = [headers, ...rows].map(r => r.map(v => `"${v ?? ''}"`).join(',')).join('\n');
    const a = document.createElement('a');
    a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
    a.download = filename;
    a.click();
}

/* ── Init ─────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => loadR1());
</script>
@endpush
