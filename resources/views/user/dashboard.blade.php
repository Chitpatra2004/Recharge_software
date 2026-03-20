@extends('layouts.user')
@section('title','Dashboard')
@section('page-title','Dashboard')

@section('content')
{{-- Welcome bar --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
    <div>
        <h1 style="font-size:20px;font-weight:700;color:#fff" id="welcome-msg">Welcome back!</h1>
        <p style="font-size:13px;color:var(--muted);margin-top:3px">Here's an overview of your account activity.</p>
    </div>
    <a href="/user/recharges" class="btn btn-primary">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        New Recharge
    </a>
</div>

{{-- Stat Cards --}}
<div class="stats-grid">
    <div class="stat-card blue">
        <div class="stat-label">Wallet Balance</div>
        <div class="stat-value" id="s-balance">—</div>
        <div class="stat-sub">Available to use</div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Total Recharges</div>
        <div class="stat-value" id="s-total">—</div>
        <div class="stat-sub">This month</div>
    </div>
    <div class="stat-card orange">
        <div class="stat-label">Total Spent</div>
        <div class="stat-value" id="s-spent" style="font-size:20px">—</div>
        <div class="stat-sub">This month</div>
    </div>
    <div class="stat-card red">
        <div class="stat-label">Open Complaints</div>
        <div class="stat-value" id="s-complaints">—</div>
        <div class="stat-sub">Pending resolution</div>
    </div>
</div>

{{-- Quick Recharge + Recent Transactions --}}
<div style="display:grid;grid-template-columns:340px 1fr;gap:16px">

    {{-- Quick Recharge --}}
    <div class="card">
        <div class="card-header"><span class="card-title">Quick Recharge</span></div>
        <div class="card-body" style="display:flex;flex-direction:column;gap:14px">
            <div>
                <label style="font-size:12px;font-weight:600;color:var(--muted);display:block;margin-bottom:5px">Mobile Number</label>
                <input type="tel" id="q-mobile" placeholder="10-digit number" maxlength="10"
                    style="width:100%;background:var(--card2);border:1px solid var(--border2);border-radius:8px;padding:9px 12px;font-size:13px;color:var(--text);outline:none;font-family:inherit">
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:var(--muted);display:block;margin-bottom:5px">Operator</label>
                <select id="q-operator" style="width:100%;background:var(--card2);border:1px solid var(--border2);border-radius:8px;padding:9px 12px;font-size:13px;color:var(--text);outline:none;font-family:inherit">
                    <option value="">Select operator</option>
                    <option value="AIRTEL">Airtel</option>
                    <option value="JIO">Jio</option>
                    <option value="VI">Vi (Vodafone)</option>
                    <option value="BSNL">BSNL</option>
                    <option value="TATAPLAY">Tata Play (DTH)</option>
                    <option value="DISHTV">Dish TV</option>
                    <option value="SUNDIRECT">Sun Direct</option>
                </select>
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:var(--muted);display:block;margin-bottom:5px">Type</label>
                <select id="q-type" style="width:100%;background:var(--card2);border:1px solid var(--border2);border-radius:8px;padding:9px 12px;font-size:13px;color:var(--text);outline:none;font-family:inherit">
                    <option value="prepaid">Prepaid</option>
                    <option value="postpaid">Postpaid</option>
                    <option value="dth">DTH</option>
                </select>
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:var(--muted);display:block;margin-bottom:5px">Amount (₹)</label>
                <input type="number" id="q-amount" placeholder="e.g. 299" min="1"
                    style="width:100%;background:var(--card2);border:1px solid var(--border2);border-radius:8px;padding:9px 12px;font-size:13px;color:var(--text);outline:none;font-family:inherit">
            </div>
            <div id="recharge-msg" style="display:none;font-size:13px;border-radius:8px;padding:10px 12px"></div>
            <button class="btn btn-primary" style="width:100%;justify-content:center" onclick="doQuickRecharge()" id="recharge-btn">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:15px;height:15px"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Recharge Now
            </button>
        </div>
    </div>

    {{-- Recent Transactions --}}
    <div class="card">
        <div class="card-header" style="justify-content:space-between">
            <span class="card-title">Recent Transactions</span>
            <a href="/user/recharges" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Mobile</th><th>Operator</th><th>Amount</th><th>Status</th><th>When</th></tr></thead>
                <tbody id="recent-tbody"><tr><td colspan="5"><div class="loading"><div class="spinner"></div> Loading…</div></td></tr></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const u = getUserData();
    if (u.name) document.getElementById('welcome-msg').textContent = 'Welcome back, ' + u.name.split(' ')[0] + '!';
    loadStats();
    loadRecent();
});

async function loadStats() {
    const [walRes, txnRes, cmpRes] = await Promise.all([
        apiFetch('/api/v1/wallet/balance'),
        apiFetch('/api/v1/transactions?per_page=1'),
        apiFetch('/api/v1/complaints?per_page=1'),
    ]);
    if (walRes?.ok) { const d = await walRes.json(); document.getElementById('s-balance').textContent = fmtAmt(d.balance); }
    if (txnRes?.ok) { const d = await txnRes.json(); document.getElementById('s-total').textContent = fmtNum(d.total||d.meta?.total||0); }
    if (cmpRes?.ok) { const d = await cmpRes.json(); document.getElementById('s-complaints').textContent = fmtNum(d.total||0); }
    // spent (sum from transactions — approximate)
    const res2 = await apiFetch('/api/v1/wallet/transactions?per_page=100');
    if (res2?.ok) {
        const d = await res2.json();
        const txns = d.data||d.transactions||[];
        const spent = txns.filter(t=>t.type==='debit'||t.type==='reserve').reduce((s,t)=>s+parseFloat(t.amount||0),0);
        document.getElementById('s-spent').textContent = fmtAmt(spent);
    }
}

async function loadRecent() {
    const res = await apiFetch('/api/v1/transactions?per_page=8');
    if (!res) return;
    const data = await res.json();
    const txns = data.data || data.transactions || [];
    document.getElementById('recent-tbody').innerHTML = txns.length
        ? txns.map(t=>`<tr>
            <td style="font-weight:600">${t.mobile||'—'}</td>
            <td>${t.operator_code||t.operator||'—'}</td>
            <td>${fmtAmt(t.amount)}</td>
            <td><span class="badge ${t.status==='success'?'success':t.status==='failed'?'failure':'pending'}">${t.status||'—'}</span></td>
            <td>${fmtAgo(t.created_at)}</td>
          </tr>`).join('')
        : '<tr><td colspan="5" style="text-align:center;color:var(--muted);padding:24px">No transactions yet</td></tr>';
}

async function doQuickRecharge() {
    const mobile   = document.getElementById('q-mobile').value.trim();
    const operator = document.getElementById('q-operator').value;
    const type     = document.getElementById('q-type').value;
    const amount   = document.getElementById('q-amount').value;
    const msg      = document.getElementById('recharge-msg');

    msg.style.display = 'none';
    if (!mobile||mobile.length!==10) { showMsg('Enter a valid 10-digit mobile number.','error'); return; }
    if (!operator) { showMsg('Please select an operator.','error'); return; }
    if (!amount||amount<1) { showMsg('Enter a valid amount.','error'); return; }

    document.getElementById('recharge-btn').disabled = true;
    const res = await apiFetch('/api/v1/recharge', { method:'POST', body: JSON.stringify({ mobile, operator_code:operator, recharge_type:type, amount:parseFloat(amount) }) });
    document.getElementById('recharge-btn').disabled = false;
    if (!res) return;
    const data = await res.json();
    if (res.ok) {
        showMsg('Recharge initiated! Txn ID: '+(data.txn_id||data.id||'—'),'success');
        loadStats(); loadRecent();
    } else {
        showMsg(data.message||'Recharge failed. Please try again.','error');
    }
}

function showMsg(txt, type) {
    const m = document.getElementById('recharge-msg');
    m.textContent = txt;
    m.style.cssText = `display:block;font-size:13px;border-radius:8px;padding:10px 12px;background:${type==='success'?'rgba(16,185,129,.1)':'rgba(239,68,68,.1)'};border:1px solid ${type==='success'?'rgba(16,185,129,.25)':'rgba(239,68,68,.25)'};color:${type==='success'?'#6ee7b7':'#fca5a5'}`;
}
</script>
@endpush
