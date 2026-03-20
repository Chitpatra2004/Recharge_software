@extends('layouts.superadmin')
@section('title', 'Seller API Configuration')
@section('page-title', 'Seller API Configuration')

@push('head')
<style>
/* ─── PAGE LAYOUT ────────────────────────────────── */
.sa-config-grid { display: grid; grid-template-columns: 320px 1fr; gap: 20px; align-items: start; }
@media(max-width:960px) { .sa-config-grid { grid-template-columns: 1fr; } }

/* ─── SELLER SIDEBAR LIST ────────────────────────── */
.seller-list-item {
    display: flex; align-items: center; gap: 10px;
    padding: 11px 14px;
    border-radius: var(--rh-radius-sm);
    cursor: pointer;
    transition: background var(--rh-transition);
    border: 1px solid transparent;
    margin-bottom: 4px;
}
.seller-list-item:hover { background: var(--rh-brand-lt); }
.seller-list-item.selected { background: var(--rh-brand-lt); border-color: var(--rh-brand); }
.seller-list-avatar {
    width: 34px; height: 34px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700; color: #fff; flex-shrink: 0;
}
.seller-list-name { font-size: 13px; font-weight: 600; color: var(--rh-text); }
.seller-list-id   { font-size: 11px; color: var(--rh-muted); }
.seller-list-status { margin-left: auto; }

/* ─── API KEY CARD ────────────────────────────────── */
.api-key-card {
    border: 1px solid var(--rh-border);
    border-radius: var(--rh-radius);
    overflow: hidden;
    margin-bottom: 12px;
    transition: box-shadow var(--rh-transition);
}
.api-key-card:hover { box-shadow: 0 4px 18px rgba(0,0,0,.08); }
.api-key-header {
    display: flex; align-items: center; gap: 12px;
    padding: 14px 16px;
    background: #fafafa;
    border-bottom: 1px solid var(--rh-border);
}
.api-key-op-icon {
    width: 36px; height: 36px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; font-size: 11px; font-weight: 800; color: #fff;
}
.api-key-name   { font-size: 13.5px; font-weight: 700; color: var(--rh-text); }
.api-key-op     { font-size: 11.5px; color: var(--rh-muted); }
.api-key-body   { padding: 14px 16px; }
.api-key-field  { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; }
.api-key-field:last-child { margin-bottom: 0; }
.api-key-field-lbl { font-size: 11px; font-weight: 700; color: var(--rh-muted); width: 90px; flex-shrink: 0; text-transform: uppercase; }
.api-key-val {
    flex: 1; font-family: monospace; font-size: 12px;
    background: var(--rh-page); border: 1px solid var(--rh-border);
    border-radius: 6px; padding: 5px 10px; color: var(--rh-text-sub);
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.api-key-actions { display: flex; gap: 6px; }

/* ─── MODAL FIELDS ────────────────────────────────── */
.form-row { margin-bottom: 14px; }
.form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px; }

/* ─── OPERATOR PILL SELECTOR ─────────────────────── */
.op-pills { display: flex; flex-wrap: wrap; gap: 7px; }
.op-pill {
    padding: 5px 12px; border-radius: 99px;
    border: 1px solid var(--rh-border);
    font-size: 12px; font-weight: 600;
    cursor: pointer; transition: all var(--rh-transition);
    background: var(--rh-card); color: var(--rh-text-sub);
}
.op-pill:hover, .op-pill.selected { background: var(--rh-brand); color: #fff; border-color: var(--rh-brand); }

/* ─── STAT MINI ───────────────────────────────────── */
.stat-mini { display: flex; gap: 12px; margin-bottom: 18px; }
.stat-mini-item {
    flex: 1; background: var(--rh-page);
    border: 1px solid var(--rh-border);
    border-radius: var(--rh-radius-sm);
    padding: 12px 14px;
}
.stat-mini-val { font-size: 20px; font-weight: 800; color: var(--rh-text); }
.stat-mini-lbl { font-size: 11px; color: var(--rh-muted); margin-top: 2px; }
</style>
@endpush

@section('content')
<div class="rh-breadcrumb">
    <a href="{{ route('superadmin.dashboard') }}">Dashboard</a>
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>Seller API Configuration</span>
</div>

<div class="rh-alert rh-alert-info" style="margin-bottom:18px">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <span>Each seller can have individual API credentials per operator. Configure, test, and manage their keys from this page. Keys are encrypted at rest.</span>
</div>

<div class="sa-config-grid">

    {{-- LEFT: Seller List --}}
    <div>
        <div class="rh-card">
            <div class="rh-card-header">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;color:var(--rh-brand)"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span class="rh-card-title">Registered Sellers</span>
                <span id="seller-count" style="margin-left:auto;font-size:11px;background:var(--rh-brand-lt);color:var(--rh-brand);padding:2px 8px;border-radius:99px;font-weight:700">247</span>
            </div>
            <div style="padding:10px">
                <!-- Search -->
                <div style="position:relative;margin-bottom:10px">
                    <input type="text" placeholder="Search seller…" id="sellerSearch" oninput="filterSellers(this.value)"
                        style="width:100%;padding:8px 10px 8px 32px;border:1px solid var(--rh-border);border-radius:var(--rh-radius-sm);font-family:inherit;font-size:12.5px;outline:none;background:var(--rh-page)">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                        style="position:absolute;left:9px;top:50%;transform:translateY(-50%);width:14px;height:14px;color:var(--rh-faint)">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <!-- Seller Items -->
                <div id="sellerList" style="max-height:460px;overflow-y:auto">
                    <!-- Injected by JS -->
                </div>
            </div>
        </div>
    </div>

    {{-- RIGHT: API Config Panel --}}
    <div id="configPanel">
        {{-- Empty State --}}
        <div id="emptyState" class="rh-card" style="padding:48px 24px;text-align:center">
            <div style="width:56px;height:56px;border-radius:14px;background:var(--rh-brand-lt);display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="width:28px;height:28px;color:var(--rh-brand)"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
            </div>
            <div style="font-size:15px;font-weight:700;color:var(--rh-text);margin-bottom:6px">Select a Seller</div>
            <div style="font-size:13px;color:var(--rh-muted)">Click a seller from the list to view and manage their API keys for each operator.</div>
        </div>

        {{-- Config Content (hidden until seller selected) --}}
        <div id="configContent" style="display:none">

            {{-- Seller Header --}}
            <div class="rh-card" style="margin-bottom:16px">
                <div style="padding:18px;display:flex;align-items:center;gap:14px">
                    <div id="cfgAvatar" class="seller-list-avatar" style="width:48px;height:48px;border-radius:12px;font-size:16px"></div>
                    <div>
                        <div id="cfgName" style="font-size:16px;font-weight:800;color:var(--rh-text)"></div>
                        <div id="cfgMeta" style="font-size:12.5px;color:var(--rh-muted);margin-top:2px"></div>
                    </div>
                    <div style="margin-left:auto;display:flex;gap:8px">
                        <button class="btn btn-outline btn-sm" onclick="openAddKeyModal()">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            Add API Key
                        </button>
                        <button class="btn btn-primary btn-sm" onclick="testAllKeys()">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Test All Keys
                        </button>
                    </div>
                </div>

                {{-- Mini Stats --}}
                <div style="padding:0 18px 18px">
                    <div class="stat-mini">
                        <div class="stat-mini-item">
                            <div class="stat-mini-val" id="cfgKeyCount">—</div>
                            <div class="stat-mini-lbl">API Keys</div>
                        </div>
                        <div class="stat-mini-item">
                            <div class="stat-mini-val" id="cfgActiveKeys" style="color:var(--rh-green)">—</div>
                            <div class="stat-mini-lbl">Active</div>
                        </div>
                        <div class="stat-mini-item">
                            <div class="stat-mini-val" id="cfgTodayRch">—</div>
                            <div class="stat-mini-lbl">Today's Recharges</div>
                        </div>
                        <div class="stat-mini-item">
                            <div class="stat-mini-val" id="cfgWallet" style="color:var(--rh-brand)">—</div>
                            <div class="stat-mini-lbl">Wallet Balance</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- API Keys List --}}
            <div class="rh-card">
                <div class="rh-card-header">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;color:var(--rh-brand)"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    <span class="rh-card-title">Configured API Keys</span>
                    <span id="cfgKeyBadge" class="badge badge-blue" style="margin-left:auto"></span>
                </div>
                <div id="keysList" style="padding:14px">
                    <!-- API key cards rendered by JS -->
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ───── ADD API KEY MODAL ───── --}}
<div class="rh-modal-overlay" id="addKeyModal">
    <div class="rh-modal" style="width:560px">
        <div class="rh-modal-hd">
            <div class="rh-modal-title">Add New API Key</div>
            <button class="rh-modal-close" onclick="closeAddKeyModal()">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="form-row">
            <label class="rh-label">Key Label / Name</label>
            <input type="text" id="mkLabel" class="rh-input" placeholder="e.g. Jio Primary Key">
        </div>

        <div class="form-row">
            <label class="rh-label">Operator</label>
            <div class="op-pills" id="mkOpPills">
                <span class="op-pill" onclick="selectOp(this,'JIO')">Jio</span>
                <span class="op-pill" onclick="selectOp(this,'AIRTEL')">Airtel</span>
                <span class="op-pill" onclick="selectOp(this,'VI')">Vi</span>
                <span class="op-pill" onclick="selectOp(this,'BSNL')">BSNL</span>
                <span class="op-pill" onclick="selectOp(this,'TATA')">Tata Sky</span>
                <span class="op-pill" onclick="selectOp(this,'DTH')">DTH Generic</span>
                <span class="op-pill" onclick="selectOp(this,'ELECT')">Electricity</span>
                <span class="op-pill" onclick="selectOp(this,'GAS')">Gas</span>
            </div>
        </div>

        <div class="form-row-2">
            <div>
                <label class="rh-label">API Endpoint URL</label>
                <input type="url" id="mkUrl" class="rh-input" placeholder="https://api.provider.com/v1">
            </div>
            <div>
                <label class="rh-label">API Version</label>
                <input type="text" id="mkVersion" class="rh-input" placeholder="v1">
            </div>
        </div>

        <div class="form-row">
            <label class="rh-label">API Key / Token</label>
            <div style="display:flex;gap:8px">
                <input type="password" id="mkKey" class="rh-input" placeholder="Paste API key here" style="flex:1">
                <button onclick="toggleKeyVis('mkKey',this)" class="btn btn-outline btn-sm">Show</button>
            </div>
        </div>

        <div class="form-row-2">
            <div>
                <label class="rh-label">Member ID / Username</label>
                <input type="text" id="mkMemberId" class="rh-input" placeholder="Member ID">
            </div>
            <div>
                <label class="rh-label">Secret / Password</label>
                <input type="password" id="mkSecret" class="rh-input" placeholder="Secret key">
            </div>
        </div>

        <div class="form-row">
            <label class="rh-label">Allowed IP (optional — leave blank for any)</label>
            <input type="text" id="mkIp" class="rh-input" placeholder="e.g. 203.0.113.42">
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:6px">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;font-weight:500">
                <label class="rh-toggle-wrap">
                    <input type="checkbox" class="rh-toggle-input" id="mkActive" checked>
                    <span class="rh-toggle"></span>
                </label>
                Enable immediately
            </label>
            <div style="display:flex;gap:8px">
                <button class="btn btn-outline btn-sm" onclick="testNewKey()">Test Key</button>
                <button class="btn btn-primary btn-sm" onclick="saveNewKey()">Save Key</button>
            </div>
        </div>

        <div id="mkTestResult" style="display:none;margin-top:12px;padding:10px 12px;border-radius:var(--rh-radius-sm);font-size:13px;font-family:monospace"></div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Demo Data ──────────────────────────────────────
const SELLERS = [
    { id:'SL001', name:'Ravi Telecom', initials:'RV', color:'#4f46e5', city:'Mumbai', joined:'Jan 2024', keys:3, active:3, recharges:842, wallet:'₹41,200' },
    { id:'SL002', name:'StarConnect',  initials:'SC', color:'#059669', city:'Delhi',  joined:'Nov 2023', keys:2, active:2, recharges:611, wallet:'₹28,500' },
    { id:'SL003', name:'MobileNation', initials:'MN', color:'#d97706', city:'Pune',   joined:'Mar 2024', keys:4, active:3, recharges:488, wallet:'₹19,750' },
    { id:'SL004', name:'QuickRecharge',initials:'QR', color:'#0284c7', city:'Jaipur', joined:'Feb 2024', keys:1, active:1, recharges:392, wallet:'₹11,300' },
    { id:'SL005', name:'TopUpKing',    initials:'TK', color:'#7c3aed', city:'Kolkata',joined:'Dec 2023', keys:5, active:4, recharges:341, wallet:'₹22,800' },
    { id:'SL006', name:'FastRecharge', initials:'FR', color:'#db2777', city:'Chennai',joined:'Apr 2024', keys:2, active:1, recharges:289, wallet:'₹8,100'  },
    { id:'SL007', name:'RechargeExpress',initials:'RE',color:'#dc2626',city:'Hyderabad',joined:'Jan 2024',keys:3,active:3,recharges:241,wallet:'₹16,400'},
    { id:'SL008', name:'DigitalTopUp', initials:'DT', color:'#0891b2', city:'Ahmedabad',joined:'May 2024',keys:1,active:0,recharges:0,  wallet:'₹5,000'  },
];

const SAMPLE_KEYS = [
    { label:'Jio Primary',   op:'JIO',    opColor:'#4f46e5', url:'https://api.jiopay.in/v2',   key:'jio_k3y_****3a9f', memberId:'JIO_MBR_1291', active:true,  lastUsed:'2 min ago'  },
    { label:'Airtel Main',   op:'AIRTEL', opColor:'#dc2626', url:'https://gateway.airtel.com/api',key:'airt_k3y_****8b2c', memberId:'AT_USER_8821', active:true,  lastUsed:'18 min ago' },
    { label:'Vi Standard',   op:'VI',     opColor:'#d97706', url:'https://api.virecharge.com/v1', key:'vi_k3y_****4d7e',  memberId:'VI_ID_2201',   active:false, lastUsed:'3 days ago' },
];

let selectedSeller = null;
let selectedOp = '';

// ── Render Seller List ─────────────────────────────
function renderSellers(list) {
    const el = document.getElementById('sellerList');
    el.innerHTML = list.map(s => `
        <div class="seller-list-item ${selectedSeller?.id === s.id ? 'selected' : ''}" onclick="selectSeller('${s.id}')">
            <div class="seller-list-avatar" style="background:${s.color}">${s.initials}</div>
            <div>
                <div class="seller-list-name">${s.name}</div>
                <div class="seller-list-id">${s.id} · ${s.city}</div>
            </div>
            <div class="seller-list-status">
                <span class="badge ${s.active > 0 ? 'badge-green':'badge-gray'}" style="font-size:10px">${s.active > 0 ? 'Active':'No Keys'}</span>
            </div>
        </div>
    `).join('');
}
renderSellers(SELLERS);

function filterSellers(q) {
    const filtered = SELLERS.filter(s => s.name.toLowerCase().includes(q.toLowerCase()) || s.id.toLowerCase().includes(q.toLowerCase()));
    renderSellers(filtered);
}

// ── Select Seller ──────────────────────────────────
function selectSeller(id) {
    selectedSeller = SELLERS.find(s => s.id === id);
    renderSellers(SELLERS);

    document.getElementById('emptyState').style.display = 'none';
    document.getElementById('configContent').style.display = 'block';

    const av = document.getElementById('cfgAvatar');
    av.textContent = selectedSeller.initials;
    av.style.background = selectedSeller.color;

    document.getElementById('cfgName').textContent = selectedSeller.name;
    document.getElementById('cfgMeta').textContent = `${selectedSeller.id} · ${selectedSeller.city} · Member since ${selectedSeller.joined}`;
    document.getElementById('cfgKeyCount').textContent = selectedSeller.keys;
    document.getElementById('cfgActiveKeys').textContent = selectedSeller.active;
    document.getElementById('cfgTodayRch').textContent = selectedSeller.recharges.toLocaleString();
    document.getElementById('cfgWallet').textContent = selectedSeller.wallet;
    document.getElementById('cfgKeyBadge').textContent = selectedSeller.keys + ' keys';

    renderKeys();
}

// ── Render API Keys ────────────────────────────────
function renderKeys() {
    const wrap = document.getElementById('keysList');
    if (SAMPLE_KEYS.length === 0) {
        wrap.innerHTML = `<div style="text-align:center;padding:32px;color:var(--rh-muted);font-size:13px">No API keys configured yet. Click "Add API Key" to get started.</div>`;
        return;
    }
    wrap.innerHTML = SAMPLE_KEYS.map((k, i) => `
        <div class="api-key-card">
            <div class="api-key-header">
                <div class="api-key-op-icon" style="background:${k.opColor}">${k.op.slice(0,2)}</div>
                <div>
                    <div class="api-key-name">${k.label}</div>
                    <div class="api-key-op">${k.op} · Last used ${k.lastUsed}</div>
                </div>
                <div style="margin-left:auto;display:flex;align-items:center;gap:10px">
                    <label class="rh-toggle-wrap" title="${k.active ? 'Enabled':'Disabled'}">
                        <input type="checkbox" class="rh-toggle-input" ${k.active ? 'checked':''} onchange="toggleKey(${i},this.checked)">
                        <span class="rh-toggle"></span>
                    </label>
                    <span class="badge ${k.active ? 'badge-green':'badge-gray'}">${k.active ? 'Active':'Disabled'}</span>
                </div>
            </div>
            <div class="api-key-body">
                <div class="api-key-field">
                    <span class="api-key-field-lbl">Endpoint</span>
                    <span class="api-key-val">${k.url}</span>
                </div>
                <div class="api-key-field">
                    <span class="api-key-field-lbl">API Key</span>
                    <span class="api-key-val">${k.key}</span>
                    <div class="api-key-actions">
                        <button class="btn btn-outline btn-sm" onclick="copyVal('${k.key}')">Copy</button>
                        <button class="btn btn-outline btn-sm" onclick="testKey(${i})">Test</button>
                        <button class="btn btn-danger btn-sm" onclick="deleteKey(${i})">Delete</button>
                    </div>
                </div>
                <div class="api-key-field">
                    <span class="api-key-field-lbl">Member ID</span>
                    <span class="api-key-val">${k.memberId}</span>
                </div>
                <div id="test-result-${i}" style="display:none;margin-top:8px;padding:8px 12px;border-radius:var(--rh-radius-sm);font-size:12.5px;font-family:monospace"></div>
            </div>
        </div>
    `).join('');
}

function toggleKey(i, active) {
    SAMPLE_KEYS[i].active = active;
    setTimeout(() => renderKeys(), 0);
}

function testKey(i) {
    const el = document.getElementById('test-result-'+i);
    el.style.display = 'block';
    el.style.background = '#eff6ff';
    el.style.color = '#1e40af';
    el.textContent = '⏳ Testing connection…';
    setTimeout(() => {
        const ok = Math.random() > .2;
        el.style.background = ok ? '#ecfdf5' : '#fef2f2';
        el.style.color = ok ? '#065f46' : '#991b1b';
        el.textContent = ok ? '✓ Connection successful — API responded in 124ms' : '✗ Connection failed — check credentials or endpoint URL';
    }, 1200);
}

function testAllKeys() {
    SAMPLE_KEYS.forEach((_,i) => testKey(i));
}

function deleteKey(i) {
    if (!confirm('Remove this API key? This cannot be undone.')) return;
    SAMPLE_KEYS.splice(i, 1);
    renderKeys();
}

function copyVal(val) {
    navigator.clipboard.writeText(val).then(() => alert('Copied to clipboard'));
}

// ── Add Key Modal ──────────────────────────────────
function openAddKeyModal()  { document.getElementById('addKeyModal').classList.add('open'); }
function closeAddKeyModal() { document.getElementById('addKeyModal').classList.remove('open'); }

function selectOp(el, op) {
    document.querySelectorAll('.op-pill').forEach(p => p.classList.remove('selected'));
    el.classList.add('selected');
    selectedOp = op;
}

function toggleKeyVis(inputId, btn) {
    const inp = document.getElementById(inputId);
    inp.type = inp.type === 'password' ? 'text' : 'password';
    btn.textContent = inp.type === 'password' ? 'Show' : 'Hide';
}

function testNewKey() {
    const el = document.getElementById('mkTestResult');
    el.style.display = 'block';
    el.style.background = '#eff6ff'; el.style.color = '#1e40af';
    el.textContent = '⏳ Testing new key…';
    setTimeout(() => {
        el.style.background = '#ecfdf5'; el.style.color = '#065f46';
        el.textContent = '✓ Connection successful — API key is valid';
    }, 1400);
}

function saveNewKey() {
    const label = document.getElementById('mkLabel').value.trim();
    const url   = document.getElementById('mkUrl').value.trim();
    const key   = document.getElementById('mkKey').value.trim();
    if (!label || !selectedOp || !key) { alert('Fill in label, operator, and API key.'); return; }

    const colors = { JIO:'#4f46e5', AIRTEL:'#dc2626', VI:'#d97706', BSNL:'#059669', TATA:'#7c3aed', DTH:'#0284c7', ELECT:'#db2777', GAS:'#374151' };
    SAMPLE_KEYS.push({
        label, op: selectedOp, opColor: colors[selectedOp] || '#374151',
        url: url || 'https://api.provider.com/v1',
        key: key.length > 8 ? key.slice(0,4)+'_k3y_****'+key.slice(-4) : '****',
        memberId: document.getElementById('mkMemberId').value || '—',
        active: document.getElementById('mkActive').checked,
        lastUsed: 'never',
    });
    closeAddKeyModal();
    renderKeys();
    // reset
    ['mkLabel','mkUrl','mkKey','mkMemberId','mkSecret','mkIp'].forEach(id => document.getElementById(id).value = '');
    selectedOp = '';
    document.querySelectorAll('.op-pill').forEach(p => p.classList.remove('selected'));
    document.getElementById('mkTestResult').style.display = 'none';
}
</script>
@endpush
