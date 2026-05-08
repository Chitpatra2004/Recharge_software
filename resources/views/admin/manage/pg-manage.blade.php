@extends('layouts.admin')
@section('title','PG Manage')
@section('page-title','PG Manage')

@section('content')
<style>
/* ── Layout ─────────────────────────────────────────────────────────── */
.pgm-top{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:22px}
.pgm-title-wrap .pgm-title{font-size:20px;font-weight:900;color:var(--text-primary);display:flex;align-items:center;gap:8px}
.pgm-crumb{display:flex;gap:6px;color:#3b82f6;font-size:13px;margin-top:4px;align-items:center}
.pgm-actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.pgm-save-status{font-size:12px;font-weight:800;color:#10b981;background:#d1fae5;border-radius:6px;padding:6px 12px}
.pgm-btn{height:36px;border:1px solid var(--border);border-radius:6px;background:var(--card-bg);color:var(--text-primary);font-size:12px;font-weight:800;padding:0 14px;cursor:pointer;display:flex;align-items:center;gap:5px}
.pgm-btn-primary{background:#3b82f6;color:#fff;border-color:#3b82f6}

/* ── Stats ───────────────────────────────────────────────────────────── */
.pgm-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:22px}
.pgm-stat{background:var(--card-bg);border:1px solid var(--border);border-radius:12px;padding:22px 20px;display:flex;align-items:center;gap:16px}
.pgm-stat-icon{width:46px;height:46px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0}
.pgm-stat-icon.blue{background:#eff6ff;color:#3b82f6}
.pgm-stat-icon.green{background:#d1fae5;color:#059669}
.pgm-stat-icon.red{background:#fee2e2;color:#dc2626}
.pgm-stat-icon.orange{background:#fff7ed;color:#ea580c}
.pgm-stat-label{font-size:12px;color:var(--text-secondary);font-weight:700;margin-bottom:4px}
.pgm-stat-value{font-size:22px;font-weight:900;color:var(--text-primary);line-height:1}
.pgm-stat-sub{font-size:11px;margin-top:4px;font-weight:700}
.pgm-stat-sub.green{color:#059669}.pgm-stat-sub.red{color:#dc2626}.pgm-stat-sub.orange{color:#ea580c}

/* ── PG Performance ──────────────────────────────────────────────────── */
.pgm-perf{background:var(--card-bg);border:1px solid var(--border);border-radius:10px;margin-bottom:22px}
.pgm-perf-head{display:flex;align-items:center;gap:10px;padding:16px 18px;border-bottom:1px solid var(--border);font-weight:900;font-size:15px;color:var(--text-primary)}
.pgm-perf-updated{font-size:12px;font-weight:700;color:var(--text-secondary)}
.pgm-perf-table{width:100%;border-collapse:collapse;min-width:700px}
.pgm-perf-table th{font-size:12px;font-weight:800;color:var(--text-secondary);text-transform:uppercase;padding:12px 18px;text-align:left;border-bottom:1px solid var(--border);background:var(--bg-page)}
.pgm-perf-table td{padding:14px 18px;border-bottom:1px solid var(--border);color:var(--text-primary);font-size:13px;vertical-align:middle}
.pgm-pg-name{display:flex;align-items:center;gap:10px;font-weight:900}
.pgm-pg-icon{width:34px;height:34px;border-radius:50%;background:#eff6ff;color:#3b82f6;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:900}
.pgm-success{color:#059669;font-weight:900}.pgm-failed{color:#dc2626;font-weight:900}.pgm-pending{color:#ea580c;font-weight:900}
.pgm-sub{font-size:11px;color:var(--text-secondary);margin-top:2px}

/* ── Service Configuration ───────────────────────────────────────────── */
.pgm-cfg-header{background:linear-gradient(135deg,#6366f1,#a855f7);border-radius:12px 12px 0 0;padding:20px 22px;display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:4px}
.pgm-cfg-icon{width:52px;height:52px;border-radius:12px;background:rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center;font-size:22px;color:#fff;flex-shrink:0}
.pgm-cfg-title{font-size:18px;font-weight:900;color:#fff}
.pgm-cfg-sub{font-size:12px;color:rgba(255,255,255,.82);margin-top:2px}
.pgm-cfg-save-row{display:flex;align-items:center;gap:8px}
.pgm-cfg-name{height:36px;border:1px solid rgba(255,255,255,.3);border-radius:6px;background:rgba(255,255,255,.12);color:#fff;padding:0 12px;font-size:13px;font-weight:700;min-width:180px}
.pgm-cfg-name::placeholder{color:rgba(255,255,255,.6)}
.pgm-cfg-save-btn{height:36px;background:#10b981;color:#fff;border:none;border-radius:6px;padding:0 16px;font-weight:800;font-size:13px;cursor:pointer}

.pgm-cfg-body{background:var(--card-bg);border:1px solid var(--border);border-radius:0 0 12px 12px;margin-bottom:22px}
.pgm-cfg-search-row{display:flex;align-items:center;gap:12px;padding:14px 16px;border-bottom:1px solid var(--border);flex-wrap:wrap}
.pgm-cfg-search{flex:1;min-width:200px;height:38px;border:1px solid var(--border);border-radius:8px;padding:0 14px 0 36px;font-size:13px;background:var(--card-bg);color:var(--text-primary)}
.pgm-cfg-search-wrap{position:relative;flex:1;min-width:200px}
.pgm-cfg-search-wrap::before{content:'⌕';position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-secondary);font-size:16px}
.pgm-cfg-counts{display:flex;gap:10px;flex-shrink:0}
.pgm-count-chip{font-size:12px;font-weight:800;padding:5px 10px;border-radius:6px}
.pgm-count-chip.total{background:#f1f5f9;color:#475569}
.pgm-count-chip.configured{background:#d1fae5;color:#047857}
.pgm-count-chip.default{background:#fff7ed;color:#c2410c}

.pgm-chips-wrap{padding:12px 16px;border-bottom:1px solid var(--border);display:flex;flex-wrap:wrap;gap:8px}
.pgm-chip{display:inline-flex;align-items:center;gap:5px;padding:6px 12px;border-radius:20px;font-size:12px;font-weight:800;cursor:pointer;border:2px solid transparent;transition:all .15s}
.pgm-chip.configured{background:#d1fae5;color:#047857;border-color:#a7f3d0}
.pgm-chip.default{background:#fff7ed;color:#c2410c;border-color:#fed7aa}
.pgm-chip.active{border-color:#6366f1!important;box-shadow:0 0 0 2px rgba(99,102,241,.25)}
.pgm-chip-icon{font-size:10px}

/* ── Rules panel ─────────────────────────────────────────────────────── */
.pgm-rules-panel{padding:18px 16px}
.pgm-rules-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:8px}
.pgm-rules-title{font-size:16px;font-weight:900;color:var(--text-primary);display:flex;align-items:center;gap:8px}
.pgm-cfg-badge{font-size:11px;font-weight:800;padding:3px 10px;border-radius:999px}
.pgm-cfg-badge.configured{background:#d1fae5;color:#047857}
.pgm-cfg-badge.default{background:#fff7ed;color:#c2410c}
.pgm-add-rule-btn{height:34px;background:#6366f1;color:#fff;border:none;border-radius:7px;padding:0 14px;font-size:13px;font-weight:800;cursor:pointer}

.pgm-rule-card{border:1.5px solid var(--border);border-radius:10px;margin-bottom:14px;overflow:hidden}
.pgm-rule-card-head{display:flex;align-items:center;gap:8px;padding:12px 14px;background:var(--bg-page);border-bottom:1px solid var(--border);flex-wrap:wrap}
.pgm-rule-badge{font-size:11px;font-weight:800;padding:3px 9px;border-radius:5px}
.pgm-rule-badge.number{background:#e0e7ff;color:#4338ca}
.pgm-rule-badge.active{background:#d1fae5;color:#047857}
.pgm-rule-badge.inactive{background:#fee2e2;color:#b91c1c}
.pgm-rule-badge.priority{background:#fef3c7;color:#92400e}
.pgm-rule-badge.type{background:#ede9fe;color:#6d28d9}
.pgm-rule-del{margin-left:auto;background:#ef4444;color:#fff;border:none;border-radius:6px;padding:5px 12px;font-size:12px;font-weight:800;cursor:pointer}
.pgm-rule-body{padding:14px}
.pgm-rule-fields{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px}
.pgm-field label{display:block;font-size:12px;font-weight:800;color:var(--text-secondary);margin-bottom:5px}
.pgm-field input,.pgm-field select{width:100%;height:36px;border:1px solid var(--border);border-radius:6px;background:var(--card-bg);color:var(--text-primary);padding:0 10px;font-size:13px}
.pgm-sub-types-title{font-size:13px;font-weight:900;color:var(--text-primary);margin-bottom:10px}
.pgm-sub-card{border:1px solid var(--border);border-radius:8px;margin-bottom:10px;overflow:hidden}
.pgm-sub-head{display:flex;align-items:center;gap:8px;padding:10px 12px;background:var(--bg-page);border-bottom:1px solid var(--border)}
.pgm-sub-toggle{position:relative;width:36px;height:20px;flex-shrink:0}
.pgm-sub-toggle input{opacity:0;width:0;height:0;position:absolute}
.pgm-sub-toggle-track{position:absolute;inset:0;background:#cbd5e1;border-radius:999px;cursor:pointer;transition:.2s}
.pgm-sub-toggle input:checked+.pgm-sub-toggle-track{background:#3b82f6}
.pgm-sub-toggle-track::after{content:'';position:absolute;top:3px;left:3px;width:14px;height:14px;background:#fff;border-radius:50%;transition:.2s}
.pgm-sub-toggle input:checked+.pgm-sub-toggle-track::after{left:19px}
.pgm-sub-name{font-size:13px;font-weight:800;color:var(--text-primary);text-transform:uppercase}
.pgm-sub-status{font-size:11px;font-weight:800;padding:2px 8px;border-radius:4px}
.pgm-sub-status.active{background:#d1fae5;color:#047857}
.pgm-sub-status.inactive{background:#fee2e2;color:#b91c1c}
.pgm-ranges{padding:12px}
.pgm-ranges-title{font-size:12px;font-weight:800;color:var(--text-secondary);margin-bottom:8px}
.pgm-range-row{display:grid;grid-template-columns:1fr 1fr 2fr 2fr auto;gap:8px;align-items:end;margin-bottom:8px;padding:10px;background:var(--bg-page);border-radius:6px;border:1px solid var(--border)}
.pgm-range-del{height:34px;background:#ef4444;color:#fff;border:none;border-radius:5px;padding:0 12px;font-size:12px;font-weight:800;cursor:pointer}
.pgm-add-range-btn{height:30px;background:transparent;color:#6366f1;border:1.5px dashed #6366f1;border-radius:6px;padding:0 12px;font-size:12px;font-weight:800;cursor:pointer;margin-top:6px}

.pgm-empty{text-align:center;padding:40px 20px;color:var(--text-secondary)}
.pgm-empty-icon{font-size:36px;margin-bottom:10px}

@media(max-width:900px){
    .pgm-stats{grid-template-columns:1fr 1fr}
    .pgm-top{flex-direction:column}
    .pgm-rule-fields{grid-template-columns:1fr}
    .pgm-range-row{grid-template-columns:1fr 1fr;row-gap:8px}
    .pgm-range-del{grid-column:1/-1}
}
@media(max-width:560px){.pgm-stats{grid-template-columns:1fr}}
</style>

{{-- ── TOP HEADER ──────────────────────────────────────────────────────── --}}
<div class="pgm-top">
    <div class="pgm-title-wrap">
        <div class="pgm-title">💳 PG Manage</div>
        <div class="pgm-crumb"><span>⌂ Dashboard</span><span>»</span><span>Manage</span><span>»</span><strong style="color:var(--text-primary)">PG Manage</strong></div>
    </div>
    <div class="pgm-actions">
        <span class="pgm-save-status" id="pgm-status">✓ All changes saved</span>
        <button class="pgm-btn" onclick="loadLocal()">📄 Load Local</button>
        <button class="pgm-btn" onclick="loadServer()">☁ Load Server</button>
        <button class="pgm-btn" onclick="saveLocal()">💾 Save Local</button>
        <button class="pgm-btn pgm-btn-primary" onclick="saveServer()">💾 Save</button>
    </div>
</div>

{{-- ── STATS CARDS ────────────────────────────────────────────────────── --}}
<div class="pgm-stats" id="pgm-stats">
    <div class="pgm-stat"><div class="pgm-stat-icon blue">₹</div><div><div class="pgm-stat-label">Total Amount</div><div class="pgm-stat-value" id="pgm-total-amt">₹0</div><div class="pgm-stat-sub">Overall transactions</div></div></div>
    <div class="pgm-stat"><div class="pgm-stat-icon green">✓</div><div><div class="pgm-stat-label">Successful</div><div class="pgm-stat-value" id="pgm-success-amt" style="color:#059669">₹0</div><div class="pgm-stat-sub green" id="pgm-success-rate">0.00% success rate</div></div></div>
    <div class="pgm-stat"><div class="pgm-stat-icon red">✗</div><div><div class="pgm-stat-label">Failed</div><div class="pgm-stat-value" id="pgm-failed-amt" style="color:#dc2626">₹0</div><div class="pgm-stat-sub red" id="pgm-failed-rate">0.00% failure rate</div></div></div>
    <div class="pgm-stat"><div class="pgm-stat-icon orange">⌛</div><div><div class="pgm-stat-label">Pending / Other</div><div class="pgm-stat-value" id="pgm-pending-amt" style="color:#ea580c">₹0</div><div class="pgm-stat-sub orange" id="pgm-pending-rate">0.00% pending rate</div></div></div>
</div>

{{-- ── PAYMENT GATEWAY PERFORMANCE ────────────────────────────────────── --}}
<div class="pgm-perf">
    <div class="pgm-perf-head">
        ↗ Payment Gateway Performance
        <span class="pgm-perf-updated" id="pgm-perf-updated">(Updated: 00:00:00)</span>
    </div>
    <div style="overflow:auto">
        <table class="pgm-perf-table">
            <thead><tr><th>Payment Gateway</th><th>Total Amount</th><th>Success</th><th>Failure</th><th>Pending / Other</th></tr></thead>
            <tbody id="pgm-perf-body"><tr><td colspan="5" style="text-align:center;padding:28px;color:var(--text-muted)">Loading...</td></tr></tbody>
        </table>
    </div>
</div>

{{-- ── SERVICE CONFIGURATION ───────────────────────────────────────────── --}}
<div class="pgm-cfg-header">
    <div style="display:flex;align-items:center;gap:14px">
        <div class="pgm-cfg-icon">⚙</div>
        <div>
            <div class="pgm-cfg-title">Service Configuration</div>
            <div class="pgm-cfg-sub">⚡ Configure payment gateway rules</div>
        </div>
    </div>
    <div class="pgm-cfg-save-row">
        <input class="pgm-cfg-name" id="cfg-name" placeholder="Default Configuration" value="Default Configuration">
        <button class="pgm-cfg-save-btn" onclick="saveServer()">💾 Save</button>
    </div>
</div>

<div class="pgm-cfg-body">
    <div class="pgm-cfg-search-row">
        <div class="pgm-cfg-search-wrap">
            <input class="pgm-cfg-search" id="pgm-search" placeholder="Search service types..." oninput="renderChips()">
        </div>
        <div class="pgm-cfg-counts">
            <span class="pgm-count-chip total">≡ <span id="cnt-total">30</span> Services</span>
            <span class="pgm-count-chip configured">✓ <span id="cnt-configured">0</span> Configured</span>
            <span class="pgm-count-chip default">↻ <span id="cnt-default">30</span> Using Default</span>
        </div>
    </div>
    <div class="pgm-chips-wrap" id="pgm-chips"></div>
    <div id="pgm-rules-panel"></div>
</div>

@endsection

@push('scripts')
<script>
const PG_REPORT_API = '/api/v1/employee/user-payment-requests/pg-report';
const PG_PROVIDERS_API = '/api/v1/employee/api-providers';
const LOCAL_KEY = 'pgm_config_v1';
const SERVER_KEY = 'pgm_config_server_v1';

const SERVICE_TYPES = [
    {slug:'mobile_recharge',label:'Mobile Recharge'},
    {slug:'dth',label:'DTH'},
    {slug:'postpaid',label:'Postpaid'},
    {slug:'electricity',label:'Electricity'},
    {slug:'gas',label:'Gas'},
    {slug:'water',label:'Water'},
    {slug:'fastag',label:'Fastag'},
    {slug:'credit_card',label:'Credit Card'},
    {slug:'loan_repayment',label:'Loan Repayment'},
    {slug:'insurance',label:'Insurance'},
    {slug:'google_play',label:'Google Play'},
    {slug:'piped_gas',label:'Piped Gas'},
    {slug:'dmt',label:'DMT'},
    {slug:'special',label:'Special'},
    {slug:'housing_society',label:'Housing Society'},
    {slug:'municipal_tax',label:'Municipal Tax'},
    {slug:'landline',label:'Landline'},
    {slug:'subscription_fee',label:'Subscription Fee'},
    {slug:'municipal_services',label:'Municipal Services'},
    {slug:'hospital',label:'Hospital'},
    {slug:'education',label:'Education'},
    {slug:'clubs_associations',label:'Clubs & Associations'},
    {slug:'cable_tv',label:'Cable TV'},
    {slug:'broadband',label:'Broadband'},
    {slug:'bus',label:'Bus'},
    {slug:'flight',label:'Flight'},
    {slug:'gift_card',label:'Gift Card'},
    {slug:'paytm_promo',label:'Paytm Promo'},
    {slug:'add_money',label:'Add Money'},
    {slug:'shopping',label:'Shopping'},
];

const SUB_TYPES_MAP = {
    intent:   ['upi'],
    collect:  ['upi','card','netbanking','wallet','emi'],
    redirect: ['upi','card','netbanking','wallet'],
};

let config = {};       // { slug: { rules: [...] } }
let currentSlug = null;
let pgProviders = [];  // ['PINEUPI-356593', 'ZAAKPAY', ...]
let perfUpdateTimer = 0;

/* ── Utility ──────────────────────────────────────────────────────────── */
function esc(v){return String(v??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]))}
function fmtAmt(n){const v=Number(n||0);if(v>=10000000)return '₹'+(v/10000000).toFixed(2)+' Cr';if(v>=100000)return '₹'+(v/100000).toFixed(2)+' L';if(v>=1000)return '₹'+(v/1000).toFixed(2)+'K';return '₹'+v.toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2})}
function fmtPct(n){return Number(n||0).toFixed(2)+'%'}
function setStatus(msg,ok=true){const el=document.getElementById('pgm-status');el.textContent=msg;el.style.background=ok?'#d1fae5':'#fee2e2';el.style.color=ok?'#047857':'#b91c1c'}
function markDirty(){setStatus('● Unsaved changes',false)}

/* ── Stats & Performance loading ──────────────────────────────────────── */
let perfStart = Date.now();
function startPerfTimer(){
    clearInterval(perfUpdateTimer);
    perfStart = Date.now();
    perfUpdateTimer = setInterval(()=>{
        const diff = Math.floor((Date.now()-perfStart)/1000);
        const h = String(Math.floor(diff/3600)).padStart(2,'0');
        const m = String(Math.floor((diff%3600)/60)).padStart(2,'0');
        const s = String(diff%60).padStart(2,'0');
        document.getElementById('pgm-perf-updated').textContent = `(Updated: ${h}:${m}:${s})`;
    }, 1000);
}

async function loadStats(){
    try{
        const today = new Date().toISOString().slice(0,10);
        const res = await apiFetch(`${PG_REPORT_API}?date_from=${today}&date_to=${today}&per_page=500`);
        const json = await res.json();
        const gateways = json.gateways || [];
        const rows = json.data?.data || [];

        const totalAmt  = gateways.reduce((s,g)=>s+Number(g.amount||0),0);
        const successAmt= gateways.filter(g=>g.status==='approved').reduce((s,g)=>s+Number(g.amount||0),0);
        const failedAmt = gateways.filter(g=>g.status==='rejected').reduce((s,g)=>s+Number(g.amount||0),0);
        const pendingAmt= totalAmt - successAmt - failedAmt;
        const successPct= totalAmt ? (successAmt/totalAmt*100) : 0;
        const failedPct = totalAmt ? (failedAmt/totalAmt*100)  : 0;
        const pendingPct= totalAmt ? (pendingAmt/totalAmt*100) : 0;

        document.getElementById('pgm-total-amt').textContent   = fmtAmt(totalAmt);
        document.getElementById('pgm-success-amt').textContent = fmtAmt(successAmt);
        document.getElementById('pgm-failed-amt').textContent  = fmtAmt(failedAmt);
        document.getElementById('pgm-pending-amt').textContent = fmtAmt(pendingAmt);
        document.getElementById('pgm-success-rate').textContent= fmtPct(successPct)+' success rate';
        document.getElementById('pgm-failed-rate').textContent = fmtPct(failedPct)+' failure rate';
        document.getElementById('pgm-pending-rate').textContent= fmtPct(pendingPct)+' pending rate';

        renderPerfTable(gateways);
        startPerfTimer();
    }catch(e){
        document.getElementById('pgm-perf-body').innerHTML='<tr><td colspan="5" style="text-align:center;padding:20px;color:#dc2626">Failed to load stats</td></tr>';
    }
}

function renderPerfTable(gateways){
    const body = document.getElementById('pgm-perf-body');
    if(!gateways.length){body.innerHTML='<tr><td colspan="5" style="text-align:center;padding:20px;color:var(--text-muted)">No gateway data</td></tr>';return}
    // Group by pg_name
    const byPg = {};
    gateways.forEach(g=>{
        const k = g.pg_name||'Unknown';
        if(!byPg[k])byPg[k]={total:0,success:0,failed:0,pending:0};
        const amt = Number(g.amount||0);
        byPg[k].total += amt;
        if(g.status==='approved') byPg[k].success += amt;
        else if(g.status==='rejected') byPg[k].failed += amt;
        else byPg[k].pending += amt;
    });
    body.innerHTML = Object.entries(byPg).map(([name,d])=>{
        const spct = d.total ? (d.success/d.total*100).toFixed(2) : '0.00';
        const fpct = d.total ? (d.failed/d.total*100).toFixed(2)  : '0.00';
        const ppct = d.total ? (d.pending/d.total*100).toFixed(2) : '0.00';
        return `<tr>
            <td><div class="pgm-pg-name"><div class="pgm-pg-icon">${esc(name.charAt(0))}</div>${esc(name)}</div></td>
            <td style="font-weight:900">${fmtAmt(d.total)}</td>
            <td><div class="pgm-success">${fmtAmt(d.success)}</div><div class="pgm-sub">(${spct}%)</div></td>
            <td><div class="pgm-failed">${fmtAmt(d.failed)}</div><div class="pgm-sub">(${fpct}%)</div></td>
            <td><div class="pgm-pending">${fmtAmt(d.pending)}</div><div class="pgm-sub">(${ppct}%)</div></td>
        </tr>`;
    }).join('');
}

/* ── PG Providers ────────────────────────────────────────────────────── */
async function loadProviders(){
    try{
        const res = await apiFetch(PG_PROVIDERS_API);
        const json = await res.json();
        pgProviders = [...new Set((json.routes||[]).map(r=>r.api_provider).filter(Boolean))];
    }catch(e){ pgProviders = []; }
}

function pgOptions(selected=''){
    return '<option value="">-- None --</option>'+pgProviders.map(p=>`<option value="${esc(p)}"${p===selected?' selected':''}>${esc(p)}</option>`).join('');
}

/* ── Config ──────────────────────────────────────────────────────────── */
function isConfigured(slug){ return !!(config[slug]?.rules?.length); }

function saveLocal(){
    localStorage.setItem(LOCAL_KEY, JSON.stringify({name:document.getElementById('cfg-name').value, config}));
    setStatus('✓ Saved locally');
}
function loadLocal(){
    const raw = localStorage.getItem(LOCAL_KEY);
    if(!raw){alert('No local config found');return}
    const obj = JSON.parse(raw);
    config = obj.config || {};
    if(obj.name) document.getElementById('cfg-name').value = obj.name;
    renderChips(); renderRulesPanel(); setStatus('✓ Loaded from local');
}
function saveServer(){
    // Store in localStorage with server key (server endpoint can be added later)
    localStorage.setItem(SERVER_KEY, JSON.stringify({name:document.getElementById('cfg-name').value, config}));
    saveLocal();
    setStatus('✓ All changes saved');
}
function loadServer(){
    const raw = localStorage.getItem(SERVER_KEY);
    if(!raw){alert('No server config found. Save first.');return}
    const obj = JSON.parse(raw);
    config = obj.config || {};
    if(obj.name) document.getElementById('cfg-name').value = obj.name;
    renderChips(); renderRulesPanel(); setStatus('✓ Loaded from server');
}

/* ── Chips ────────────────────────────────────────────────────────────── */
function renderChips(){
    const q = document.getElementById('pgm-search').value.toLowerCase();
    const filtered = SERVICE_TYPES.filter(s=>s.label.toLowerCase().includes(q));
    const cfgCount = SERVICE_TYPES.filter(s=>isConfigured(s.slug)).length;
    document.getElementById('cnt-total').textContent = SERVICE_TYPES.length;
    document.getElementById('cnt-configured').textContent = cfgCount;
    document.getElementById('cnt-default').textContent = SERVICE_TYPES.length - cfgCount;
    document.getElementById('pgm-chips').innerHTML = filtered.map(s=>{
        const cfg = isConfigured(s.slug);
        const active = currentSlug===s.slug ? ' active' : '';
        const icon = cfg ? '✓' : '↻';
        return `<span class="pgm-chip ${cfg?'configured':'default'}${active}" onclick="selectService('${s.slug}')">${icon} ${esc(s.label)}</span>`;
    }).join('');
}

function selectService(slug){
    currentSlug = slug;
    renderChips();
    renderRulesPanel();
}

/* ── Rules panel ─────────────────────────────────────────────────────── */
function renderRulesPanel(){
    const panel = document.getElementById('pgm-rules-panel');
    if(!currentSlug){panel.innerHTML='<div class="pgm-empty"><div class="pgm-empty-icon">⚙</div><div>Select a service type to configure PG rules</div></div>';return}
    const svc = SERVICE_TYPES.find(s=>s.slug===currentSlug);
    const rules = config[currentSlug]?.rules || [];
    const configured = isConfigured(currentSlug);
    panel.innerHTML = `
        <div class="pgm-rules-panel">
            <div class="pgm-rules-header">
                <div class="pgm-rules-title">
                    ◈ ${esc(svc?.label||currentSlug)}
                    <span class="pgm-cfg-badge ${configured?'configured':'default'}">${configured?'✓ Configured':'↻ Using Default'}</span>
                </div>
                <button class="pgm-add-rule-btn" onclick="addRule()">⊕ Add Rule</button>
            </div>
            ${rules.length ? rules.map((r,i)=>renderRuleCard(r,i)).join('') : '<div class="pgm-empty"><div class="pgm-empty-icon">📋</div><div>No rules yet. Click "Add Rule" to start.</div></div>'}
        </div>`;
}

function renderRuleCard(rule, idx){
    const types = ['intent','collect','redirect'];
    const subKeys = SUB_TYPES_MAP[rule.type||'intent'] || ['upi'];
    const subCards = subKeys.map(st=>{
        const sub = rule.sub_types?.[st] || {active:false, ranges:[]};
        return renderSubCard(idx, st, sub);
    }).join('');
    return `
    <div class="pgm-rule-card">
        <div class="pgm-rule-card-head">
            <span class="pgm-rule-badge number">Rule ${idx+1}</span>
            <span class="pgm-rule-badge ${rule.active?'active':'inactive'}">${rule.active?'Active':'Inactive'}</span>
            <span class="pgm-rule-badge priority">Priority: ${rule.priority||1}</span>
            <span class="pgm-rule-badge type">Type: ${String(rule.type||'intent').charAt(0).toUpperCase()+String(rule.type||'intent').slice(1)}</span>
            <button class="pgm-rule-del" onclick="deleteRule(${idx})">🗑 Delete</button>
        </div>
        <div class="pgm-rule-body">
            <div class="pgm-rule-fields">
                <div class="pgm-field">
                    <label>Priority</label>
                    <input type="number" min="1" value="${rule.priority||1}" onchange="updateRule(${idx},'priority',parseInt(this.value)||1)">
                </div>
                <div class="pgm-field">
                    <label>Type</label>
                    <select onchange="updateRule(${idx},'type',this.value)">
                        ${types.map(t=>`<option value="${t}"${(rule.type||'intent')===t?' selected':''}>${t.charAt(0).toUpperCase()+t.slice(1)}</option>`).join('')}
                    </select>
                </div>
            </div>
            <div class="pgm-sub-types-title">Sub Types</div>
            ${subCards}
        </div>
    </div>`;
}

function renderSubCard(ruleIdx, subType, sub){
    const ranges = sub.ranges || [];
    const rangeRows = ranges.map((r,ri)=>renderRangeRow(ruleIdx, subType, ri, r)).join('');
    return `
    <div class="pgm-sub-card">
        <div class="pgm-sub-head">
            <label class="pgm-sub-toggle">
                <input type="checkbox" ${sub.active?'checked':''} onchange="updateSubType(${ruleIdx},'${subType}','active',this.checked)">
                <span class="pgm-sub-toggle-track"></span>
            </label>
            <span class="pgm-sub-name">${esc(subType)}</span>
            <span class="pgm-sub-status ${sub.active?'active':'inactive'}">${sub.active?'Active':'Inactive'}</span>
        </div>
        <div class="pgm-ranges">
            <div class="pgm-ranges-title">Amount Ranges</div>
            <div id="ranges-${ruleIdx}-${subType}">${rangeRows}</div>
            <button class="pgm-add-range-btn" onclick="addRange(${ruleIdx},'${subType}')">+ Add Range</button>
        </div>
    </div>`;
}

function renderRangeRow(ruleIdx, subType, rangeIdx, range){
    return `
    <div class="pgm-range-row" id="range-${ruleIdx}-${subType}-${rangeIdx}">
        <div class="pgm-field"><label>Min Amount</label><input type="number" value="${range.min||10}" onchange="updateRange(${ruleIdx},'${subType}',${rangeIdx},'min',parseFloat(this.value)||0)"></div>
        <div class="pgm-field"><label>Max Amount</label><input type="number" value="${range.max||100000}" onchange="updateRange(${ruleIdx},'${subType}',${rangeIdx},'max',parseFloat(this.value)||0)"></div>
        <div class="pgm-field"><label>Primary API</label><select onchange="updateRange(${ruleIdx},'${subType}',${rangeIdx},'primary',this.value)">${pgOptions(range.primary||'')}</select></div>
        <div class="pgm-field"><label>Fallback API</label><select onchange="updateRange(${ruleIdx},'${subType}',${rangeIdx},'fallback',this.value)">${pgOptions(range.fallback||'')}</select></div>
        <button class="pgm-range-del" onclick="deleteRange(${ruleIdx},'${subType}',${rangeIdx})">🗑 Delete</button>
    </div>`;
}

/* ── Config mutations ────────────────────────────────────────────────── */
function ensureService(){
    if(!config[currentSlug]) config[currentSlug] = {rules:[]};
}
function addRule(){
    ensureService();
    config[currentSlug].rules.push({priority:config[currentSlug].rules.length+1,type:'intent',active:true,sub_types:{upi:{active:true,ranges:[{min:10,max:100000,primary:'',fallback:''}]}}});
    markDirty(); renderChips(); renderRulesPanel();
}
function deleteRule(idx){
    if(!confirm('Delete this rule?'))return;
    ensureService();
    config[currentSlug].rules.splice(idx,1);
    if(!config[currentSlug].rules.length) delete config[currentSlug];
    markDirty(); renderChips(); renderRulesPanel();
}
function updateRule(idx,key,val){
    ensureService();
    config[currentSlug].rules[idx][key]=val;
    // Re-init sub_types if type changed
    if(key==='type'){
        const subKeys = SUB_TYPES_MAP[val]||['upi'];
        const existing = config[currentSlug].rules[idx].sub_types||{};
        const newSubs = {};
        subKeys.forEach(st=>{ newSubs[st] = existing[st]||{active:false,ranges:[]}; });
        config[currentSlug].rules[idx].sub_types = newSubs;
    }
    markDirty(); renderRulesPanel();
}
function updateSubType(ruleIdx,subType,key,val){
    ensureService();
    if(!config[currentSlug].rules[ruleIdx].sub_types) config[currentSlug].rules[ruleIdx].sub_types={};
    if(!config[currentSlug].rules[ruleIdx].sub_types[subType]) config[currentSlug].rules[ruleIdx].sub_types[subType]={active:false,ranges:[]};
    config[currentSlug].rules[ruleIdx].sub_types[subType][key]=val;
    markDirty(); renderRulesPanel();
}
function addRange(ruleIdx,subType){
    ensureService();
    if(!config[currentSlug].rules[ruleIdx].sub_types) config[currentSlug].rules[ruleIdx].sub_types={};
    if(!config[currentSlug].rules[ruleIdx].sub_types[subType]) config[currentSlug].rules[ruleIdx].sub_types[subType]={active:false,ranges:[]};
    config[currentSlug].rules[ruleIdx].sub_types[subType].ranges.push({min:10,max:100000,primary:'',fallback:''});
    markDirty(); renderRulesPanel();
}
function deleteRange(ruleIdx,subType,rangeIdx){
    ensureService();
    config[currentSlug].rules[ruleIdx].sub_types[subType].ranges.splice(rangeIdx,1);
    markDirty(); renderRulesPanel();
}
function updateRange(ruleIdx,subType,rangeIdx,key,val){
    ensureService();
    config[currentSlug].rules[ruleIdx].sub_types[subType].ranges[rangeIdx][key]=val;
    markDirty();
}

/* ── Boot ────────────────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', async ()=>{
    // Load config from localStorage if available
    const saved = localStorage.getItem(LOCAL_KEY);
    if(saved){ try{ const o=JSON.parse(saved); config=o.config||{}; if(o.name) document.getElementById('cfg-name').value=o.name; }catch(e){} }

    renderChips();
    renderRulesPanel();

    await Promise.all([loadStats(), loadProviders()]);
    renderChips();
});
</script>
@endpush
