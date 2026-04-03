@extends('layouts.user')
@section('title','Dashboard')
@section('page-title','Dashboard')

@push('head')
<style>
/* ── Custom Select ─────────────────────────────────────────────────── */
.cs-wrap { position:relative; }
.cs-trigger {
    width:100%; background:var(--card2); border:1px solid var(--border2);
    border-radius:8px; padding:9px 12px; font-size:13px; color:var(--text);
    cursor:pointer; display:flex; align-items:center; justify-content:space-between;
    user-select:none; transition:border-color .15s;
}
.cs-trigger:hover { border-color:var(--blue); }
.cs-trigger.open  { border-color:var(--blue); border-bottom-color:transparent; border-radius:8px 8px 0 0; }
.cs-trigger svg   { width:14px;height:14px;color:var(--muted);flex-shrink:0;transition:transform .2s; }
.cs-trigger.open svg { transform:rotate(180deg); }
.cs-dropdown {
    position:absolute; top:100%; left:0; right:0; z-index:200;
    background:var(--card2); border:1px solid var(--blue);
    border-top:none; border-radius:0 0 8px 8px;
    max-height:210px; overflow-y:auto;
    display:none; flex-direction:column;
    box-shadow:0 8px 24px rgba(0,0,0,.45);
}
.cs-wrap.open .cs-dropdown { display:flex; }
.cs-option {
    padding:9px 12px; font-size:13px; color:var(--text);
    cursor:pointer; transition:background .12s; white-space:nowrap;
    display:flex; align-items:center; gap:8px;
}
.cs-option:hover    { background:rgba(255,255,255,.07); }
.cs-option.selected { background:rgba(59,130,246,.15); color:var(--blue); font-weight:600; }
.cs-option-muted { color:var(--muted); font-style:italic; }
/* scrollbar */
.cs-dropdown::-webkit-scrollbar { width:4px; }
.cs-dropdown::-webkit-scrollbar-track { background:transparent; }
.cs-dropdown::-webkit-scrollbar-thumb { background:rgba(255,255,255,.12); border-radius:4px; }

/* ── Type Toggle ───────────────────────────────────────────────────── */
.type-tabs { display:flex; gap:0; border:1px solid var(--border2); border-radius:8px; overflow:hidden; }
.type-tab  {
    flex:1; padding:8px 4px; font-size:12px; font-weight:600; text-align:center;
    background:var(--card2); border:none; color:var(--muted); cursor:pointer;
    transition:all .15s; font-family:inherit;
}
.type-tab + .type-tab { border-left:1px solid var(--border2); }
.type-tab.active { background:var(--blue); color:#fff; }

/* ── Plan Chips ────────────────────────────────────────────────────── */
.plans-grid {
    display:grid; grid-template-columns:1fr 1fr; gap:6px;
    max-height:200px; overflow-y:auto; padding-right:2px;
}
.plans-grid::-webkit-scrollbar { width:3px; }
.plans-grid::-webkit-scrollbar-thumb { background:rgba(255,255,255,.12); border-radius:4px; }
.plan-chip {
    padding:8px 10px; border:1.5px solid var(--border2); border-radius:8px;
    cursor:pointer; transition:all .15s; background:var(--card);
    text-align:left; font-family:inherit;
}
.plan-chip:hover { border-color:var(--blue); background:rgba(59,130,246,.08); }
.plan-chip.selected { border-color:var(--blue); background:rgba(59,130,246,.12); }
.plan-chip-amt  { font-size:14px; font-weight:700; color:var(--text); }
.plan-chip-val  { font-size:10px; font-weight:600; color:var(--blue); margin:2px 0; }
.plan-chip-desc { font-size:10px; color:var(--muted); line-height:1.4; }
.plans-empty    { text-align:center; color:var(--muted); font-size:12px; padding:16px 0; grid-column:span 2; }
</style>
@endpush

@section('content')

{{-- Welcome bar --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
    <div>
        <h1 style="font-size:20px;font-weight:700;color:var(--text)" id="welcome-msg">Welcome back!</h1>
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
        <div class="stat-label">Today's Recharges</div>
        <div class="stat-value" id="s-today-count">—</div>
        <div class="stat-sub" id="s-today-amt">—</div>
    </div>
    <div class="stat-card orange">
        <div class="stat-label">This Month Spent</div>
        <div class="stat-value" id="s-month-amt" style="font-size:20px">—</div>
        <div class="stat-sub" id="s-month-count">— transactions</div>
    </div>
    <div class="stat-card red">
        <div class="stat-label">Open Complaints</div>
        <div class="stat-value" id="s-complaints">—</div>
        <div class="stat-sub">Pending resolution</div>
    </div>
</div>

{{-- Quick Recharge + Recent Transactions --}}
<div style="display:grid;grid-template-columns:320px 1fr;gap:16px">

    {{-- Quick Recharge --}}
    <div class="card">
        <div class="card-header"><span class="card-title">Quick Recharge</span></div>
        <div class="card-body" style="display:flex;flex-direction:column;gap:13px">

            {{-- Mobile --}}
            <div>
                <label style="font-size:12px;font-weight:600;color:var(--muted);display:block;margin-bottom:5px">Mobile Number</label>
                <input type="tel" id="q-mobile" placeholder="10-digit number" maxlength="10"
                    style="width:100%;background:var(--card2);border:1px solid var(--border2);border-radius:8px;padding:9px 12px;font-size:13px;color:var(--text);outline:none;font-family:inherit;transition:border-color .15s"
                    onfocus="this.style.borderColor='var(--blue)'" onblur="this.style.borderColor='var(--border2)'">
            </div>

            {{-- Operator — custom dropdown --}}
            <div>
                <label style="font-size:12px;font-weight:600;color:var(--muted);display:block;margin-bottom:5px">Operator</label>
                <div class="cs-wrap" id="cs-operator">
                    <div class="cs-trigger" onclick="toggleCS('cs-operator')" id="cs-operator-trigger">
                        <span id="cs-operator-label" style="color:var(--muted)">Select operator</span>
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div class="cs-dropdown" id="cs-operator-drop">
                        <div class="cs-option cs-option-muted" onclick="pickOperator('','Select operator')">— Select operator —</div>
                        <div class="cs-option" onclick="pickOperator('AIRTEL','Airtel')">
                            <span style="width:8px;height:8px;border-radius:50%;background:#e8281a;flex-shrink:0"></span> Airtel
                        </div>
                        <div class="cs-option" onclick="pickOperator('JIO','Jio')">
                            <span style="width:8px;height:8px;border-radius:50%;background:#0070c0;flex-shrink:0"></span> Jio
                        </div>
                        <div class="cs-option" onclick="pickOperator('VI','Vi (Vodafone)')">
                            <span style="width:8px;height:8px;border-radius:50%;background:#ee1b24;flex-shrink:0"></span> Vi (Vodafone)
                        </div>
                        <div class="cs-option" onclick="pickOperator('BSNL','BSNL')">
                            <span style="width:8px;height:8px;border-radius:50%;background:#f59e0b;flex-shrink:0"></span> BSNL
                        </div>
                        <div class="cs-option" style="border-top:1px solid var(--border);margin-top:4px;padding-top:10px;color:var(--muted);font-size:11px;font-weight:600;letter-spacing:.4px;cursor:default">DTH / CABLE</div>
                        <div class="cs-option" onclick="pickOperator('TATAPLAY','Tata Play (DTH)')">
                            <span style="width:8px;height:8px;border-radius:50%;background:#6d28d9;flex-shrink:0"></span> Tata Play
                        </div>
                        <div class="cs-option" onclick="pickOperator('DISHTV','Dish TV')">
                            <span style="width:8px;height:8px;border-radius:50%;background:#f97316;flex-shrink:0"></span> Dish TV
                        </div>
                        <div class="cs-option" onclick="pickOperator('SUNDIRECT','Sun Direct')">
                            <span style="width:8px;height:8px;border-radius:50%;background:#eab308;flex-shrink:0"></span> Sun Direct
                        </div>
                    </div>
                </div>
                <input type="hidden" id="q-operator" value="">
            </div>

            {{-- Type toggle --}}
            <div>
                <label style="font-size:12px;font-weight:600;color:var(--muted);display:block;margin-bottom:5px">Type</label>
                <div class="type-tabs">
                    <button class="type-tab active" data-type="prepaid"  onclick="pickType(this)">Prepaid</button>
                    <button class="type-tab"        data-type="postpaid" onclick="pickType(this)">Postpaid</button>
                    <button class="type-tab"        data-type="dth"      onclick="pickType(this)">DTH</button>
                </div>
                <input type="hidden" id="q-type" value="prepaid">
            </div>

            {{-- Plans section --}}
            <div id="plans-section" style="display:none">
                <label style="font-size:12px;font-weight:600;color:var(--muted);display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
                    <span>Popular Plans</span>
                    <span id="plans-op-name" style="color:var(--blue);font-size:11px"></span>
                </label>
                <div class="plans-grid" id="plans-grid"></div>
            </div>

            {{-- Amount --}}
            <div>
                <label style="font-size:12px;font-weight:600;color:var(--muted);display:block;margin-bottom:5px">Amount (₹)</label>
                <input type="number" id="q-amount" placeholder="e.g. 299" min="1"
                    style="width:100%;background:var(--card2);border:1px solid var(--border2);border-radius:8px;padding:9px 12px;font-size:13px;color:var(--text);outline:none;font-family:inherit;transition:border-color .15s"
                    onfocus="this.style.borderColor='var(--blue)'" onblur="this.style.borderColor='var(--border2)'">
            </div>

            <div id="recharge-msg" style="display:none;font-size:13px;border-radius:8px;padding:10px 12px"></div>
            <button class="btn btn-primary" style="width:100%;justify-content:center" onclick="doQuickRecharge()" id="recharge-btn">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:15px;height:15px"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Recharge Now
            </button>
        </div>
    </div>

    {{-- Right column --}}
    <div style="display:flex;flex-direction:column;gap:16px">
        {{-- 7-day Chart --}}
        <div class="card">
            <div class="card-header" style="justify-content:space-between">
                <span class="card-title">7-Day Activity</span>
                <a href="/user/transactions" class="btn btn-outline btn-sm">View All</a>
            </div>
            <div style="padding:0 4px 8px">
                <canvas id="week-chart" height="120" style="width:100%"></canvas>
            </div>
        </div>

        {{-- Recent Transactions --}}
        <div class="card">
            <div class="card-header" style="justify-content:space-between">
                <span class="card-title">Recent Transactions</span>
                <a href="/user/transactions" class="btn btn-outline btn-sm">View All</a>
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Mobile</th><th>Operator</th><th>Amount</th><th>Status</th><th>When</th></tr></thead>
                    <tbody id="recent-tbody"><tr><td colspan="5"><div class="loading"><div class="spinner"></div> Loading…</div></td></tr></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
/* ── Operator Plans Data ───────────────────────────────────────────── */
const OPERATOR_PLANS = {
    AIRTEL: [
        { amount:149,  validity:'28 days',  desc:'Unlimited calls + 1GB/day' },
        { amount:299,  validity:'28 days',  desc:'Unlimited calls + 2GB/day' },
        { amount:399,  validity:'56 days',  desc:'Unlimited calls + 2GB/day' },
        { amount:499,  validity:'56 days',  desc:'Unlimited + 2.5GB/day + HBO' },
        { amount:599,  validity:'84 days',  desc:'Unlimited calls + 2GB/day' },
        { amount:839,  validity:'84 days',  desc:'Unlimited calls + 3GB/day' },
    ],
    JIO: [
        { amount:149,  validity:'28 days',  desc:'Unlimited calls + 1GB/day' },
        { amount:299,  validity:'28 days',  desc:'Unlimited calls + 2GB/day' },
        { amount:395,  validity:'84 days',  desc:'Unlimited calls + 2GB/day' },
        { amount:479,  validity:'84 days',  desc:'Unlimited + 2GB/day + OTT' },
        { amount:555,  validity:'84 days',  desc:'Unlimited calls + 3GB/day' },
        { amount:2999, validity:'365 days', desc:'Unlimited + 2.5GB/day + OTT' },
    ],
    VI: [
        { amount:149,  validity:'28 days',  desc:'Unlimited calls + 1.5GB/day' },
        { amount:299,  validity:'28 days',  desc:'Unlimited calls + 2GB/day' },
        { amount:398,  validity:'56 days',  desc:'Unlimited calls + 2GB/day' },
        { amount:599,  validity:'84 days',  desc:'Unlimited calls + 2GB/day' },
        { amount:719,  validity:'84 days',  desc:'Unlimited + 2.5GB/day + Weekend' },
        { amount:899,  validity:'84 days',  desc:'Weekend 4GB + Unlimited calls' },
    ],
    BSNL: [
        { amount:107,  validity:'23 days',  desc:'Unlimited calls + 1GB/day' },
        { amount:197,  validity:'28 days',  desc:'Unlimited calls + 2GB/day' },
        { amount:247,  validity:'28 days',  desc:'Unlimited + 2GB/day + SMS' },
        { amount:397,  validity:'90 days',  desc:'Unlimited calls + 1.5GB/day' },
        { amount:599,  validity:'90 days',  desc:'Unlimited calls + 2GB/day' },
        { amount:799,  validity:'180 days', desc:'Unlimited calls + 2GB/day' },
    ],
    TATAPLAY: [
        { amount:130,  validity:'1 month',  desc:'Basic SD Pack — 100+ channels' },
        { amount:200,  validity:'1 month',  desc:'HD Pack — 300+ channels' },
        { amount:350,  validity:'1 month',  desc:'Sports Pack HD' },
        { amount:500,  validity:'1 month',  desc:'Premium HD — 500+ channels' },
    ],
    DISHTV: [
        { amount:153,  validity:'1 month',  desc:'Basic SD — 100+ channels' },
        { amount:215,  validity:'1 month',  desc:'Super Family HD' },
        { amount:306,  validity:'1 month',  desc:'Sports Add-on Pack' },
        { amount:459,  validity:'1 month',  desc:'Premium HD Pack' },
    ],
    SUNDIRECT: [
        { amount:155,  validity:'1 month',  desc:'Basic Pack — SD channels' },
        { amount:250,  validity:'1 month',  desc:'Sports Pack' },
        { amount:399,  validity:'1 month',  desc:'Premium Pack HD' },
    ],
};

/* ── Custom Select helpers ─────────────────────────────────────────── */
let _activeCS = null;

function toggleCS(id) {
    const wrap = document.getElementById(id);
    const isOpen = wrap.classList.contains('open');
    closeAllCS();
    if (!isOpen) {
        wrap.classList.add('open');
        wrap.querySelector('.cs-trigger').classList.add('open');
        _activeCS = id;
    }
}
function closeAllCS() {
    document.querySelectorAll('.cs-wrap.open').forEach(w => {
        w.classList.remove('open');
        w.querySelector('.cs-trigger').classList.remove('open');
    });
    _activeCS = null;
}
document.addEventListener('click', e => {
    if (!e.target.closest('.cs-wrap')) closeAllCS();
});

function pickOperator(val, label) {
    document.getElementById('q-operator').value = val;
    const lbl = document.getElementById('cs-operator-label');
    lbl.textContent = label;
    lbl.style.color = val ? 'var(--text)' : 'var(--muted)';
    // mark selected option
    document.querySelectorAll('#cs-operator-drop .cs-option').forEach(o => {
        o.classList.toggle('selected', o.getAttribute('onclick') && o.getAttribute('onclick').includes(`'${val}'`));
    });
    closeAllCS();
    loadPlans(val);
}

function pickType(btn) {
    document.querySelectorAll('.type-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('q-type').value = btn.dataset.type;
}

/* ── Plans section ─────────────────────────────────────────────────── */
function loadPlans(op) {
    const section = document.getElementById('plans-section');
    const grid    = document.getElementById('plans-grid');
    const opName  = document.getElementById('plans-op-name');

    if (!op || !OPERATOR_PLANS[op]) { section.style.display = 'none'; return; }

    const plans = OPERATOR_PLANS[op];
    opName.textContent = document.getElementById('cs-operator-label').textContent;
    grid.innerHTML = plans.map((p, i) => `
        <button class="plan-chip" onclick="selectPlan(this, ${p.amount})" data-idx="${i}">
            <div class="plan-chip-amt">₹${p.amount}</div>
            <div class="plan-chip-val">${p.validity}</div>
            <div class="plan-chip-desc">${p.desc}</div>
        </button>`).join('');
    section.style.display = 'block';
}

function selectPlan(btn, amount) {
    document.querySelectorAll('.plan-chip').forEach(c => c.classList.remove('selected'));
    btn.classList.add('selected');
    document.getElementById('q-amount').value = amount;
    document.getElementById('q-amount').style.borderColor = 'var(--blue)';
}

/* ── Dashboard data ────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    const u = getUserData();
    if (u && u.name) document.getElementById('welcome-msg').textContent = 'Welcome back, ' + u.name.split(' ')[0] + '!';
    loadDashboard();
});

async function loadDashboard() {
    try {
        const res = await apiFetch('/api/v1/dashboard');
        if (!res || !res.ok) { fallbackLoad(); return; }
        const d = await res.json();
        const s = d.stats || {};

        document.getElementById('s-balance').textContent     = fmtAmt(s.wallet_balance || 0);
        document.getElementById('s-today-count').textContent = fmtNum(s.today_count || 0);
        document.getElementById('s-today-amt').textContent   = fmtAmt(s.today_amount || 0) + ' spent today';
        document.getElementById('s-month-amt').textContent   = fmtAmt(s.month_amount || 0);
        document.getElementById('s-month-count').textContent = fmtNum(s.month_count || 0) + ' transactions';
        document.getElementById('s-complaints').textContent  = fmtNum(s.open_complaints || 0);

        const txns = d.recent_transactions || [];
        document.getElementById('recent-tbody').innerHTML = txns.length
            ? txns.map(t=>`<tr>
                <td style="font-weight:600">${t.mobile||t.mobile_number||'—'}</td>
                <td>${t.operator_code||t.operator||'—'}</td>
                <td>${fmtAmt(t.amount)}</td>
                <td><span class="badge ${t.status==='success'?'success':t.status==='failed'?'failure':'pending'}">${t.status||'—'}</span></td>
                <td>${fmtAgo(t.created_at)}</td>
              </tr>`).join('')
            : '<tr><td colspan="5" style="text-align:center;color:var(--muted);padding:24px">No transactions yet</td></tr>';

        renderChart(d.chart || []);
    } catch(e) { fallbackLoad(); }
}

async function fallbackLoad() {
    try {
        const res = await apiFetch('/api/v1/wallet/balance');
        if (res && res.ok) { const d = await res.json(); document.getElementById('s-balance').textContent = fmtAmt(d.balance||0); }
    } catch(e) {}
}

/* ── Chart ─────────────────────────────────────────────────────────── */
function renderChart(data) {
    const canvas = document.getElementById('week-chart');
    if (!canvas || !data.length) return;
    const ctx = canvas.getContext('2d');
    const W = canvas.offsetWidth || 400, H = 120;
    canvas.width  = W * devicePixelRatio;
    canvas.height = H * devicePixelRatio;
    canvas.style.width = W+'px'; canvas.style.height = H+'px';
    ctx.scale(devicePixelRatio, devicePixelRatio);

    const counts = data.map(d => d.count || 0);
    const labels = data.map(d => new Date(d.date).toLocaleDateString('en-IN',{weekday:'short'}));
    const max = Math.max(...counts, 1);
    const pad = {top:10, bottom:28, left:24, right:12};
    const bw  = (W - pad.left - pad.right) / counts.length;

    ctx.strokeStyle = 'rgba(255,255,255,.05)';
    ctx.lineWidth = 1;
    [0,.5,1].forEach(f => {
        const y = pad.top + (H - pad.top - pad.bottom) * (1 - f);
        ctx.beginPath(); ctx.moveTo(pad.left, y); ctx.lineTo(W - pad.right, y); ctx.stroke();
    });

    const accentBlue = getComputedStyle(document.documentElement).getPropertyValue('--blue').trim() || '#3b82f6';
    counts.forEach((c, i) => {
        const barH = (H - pad.top - pad.bottom) * (c / max);
        const x = pad.left + i * bw + bw * .15;
        const y = pad.top + (H - pad.top - pad.bottom) - barH;
        const grad = ctx.createLinearGradient(0, y, 0, y + barH);
        grad.addColorStop(0, accentBlue + 'cc');
        grad.addColorStop(1, accentBlue + '44');
        ctx.fillStyle = grad;
        const r = 4;
        ctx.beginPath();
        ctx.moveTo(x+r, y); ctx.lineTo(x+bw*.7-r, y);
        ctx.quadraticCurveTo(x+bw*.7, y, x+bw*.7, y+r);
        ctx.lineTo(x+bw*.7, y+barH); ctx.lineTo(x, y+barH); ctx.lineTo(x, y+r);
        ctx.quadraticCurveTo(x, y, x+r, y); ctx.fill();

        ctx.fillStyle = 'rgba(148,163,184,.7)';
        ctx.font = '10px Inter,system-ui,sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText(labels[i], x + bw*.35, H - 8);
        if (c > 0) {
            ctx.fillStyle = '#94a3b8';
            ctx.font = '9px Inter,system-ui,sans-serif';
            ctx.fillText(c, x + bw*.35, y - 3);
        }
    });
}

/* ── Quick Recharge ────────────────────────────────────────────────── */
async function doQuickRecharge() {
    const mobile   = document.getElementById('q-mobile').value.trim();
    const operator = document.getElementById('q-operator').value;
    const type     = document.getElementById('q-type').value;
    const amount   = document.getElementById('q-amount').value;

    if (!mobile || mobile.length !== 10) { showMsg('Enter a valid 10-digit mobile number.','error'); return; }
    if (!operator)  { showMsg('Please select an operator.','error'); return; }
    if (!amount || amount < 1) { showMsg('Enter a valid amount.','error'); return; }

    const btn = document.getElementById('recharge-btn');
    btn.disabled = true;
    const idempotency_key = 'rh_' + Date.now() + '_' + Math.random().toString(36).slice(2, 10);
    const res = await apiFetch('/api/v1/recharge', {
        method:'POST',
        body: JSON.stringify({ mobile, operator_code:operator, recharge_type:type, amount:parseFloat(amount), idempotency_key })
    });
    btn.disabled = false;
    if (!res) return;
    const data = await res.json();
    if (res.ok) {
        showMsg('✓ Recharge initiated! Txn ID: '+(data.txn_id||data.id||'—'), 'success');
        loadDashboard();
    } else {
        showMsg(data.message || 'Recharge failed. Please try again.', 'error');
    }
}

function showMsg(txt, type) {
    const m = document.getElementById('recharge-msg');
    m.textContent = txt;
    m.style.cssText = `display:block;font-size:13px;border-radius:8px;padding:10px 12px;` +
        (type==='success'
            ? 'background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.25);color:#6ee7b7'
            : 'background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#fca5a5');
}
</script>
@endpush
