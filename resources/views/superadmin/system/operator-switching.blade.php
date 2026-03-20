@extends('layouts.superadmin')
@section('title', 'Operator API Switching')
@section('page-title', 'Operator API Switching')

@push('head')
<style>
/* ─── OPERATOR TABS ──────────────────────────────── */
.op-tab-bar {
    display: flex; gap: 0;
    border: 1px solid var(--rh-border);
    border-radius: var(--rh-radius);
    overflow: hidden;
    background: #f9fafb;
    margin-bottom: 20px;
    flex-wrap: wrap;
}
.op-tab {
    flex: 1; padding: 11px 18px;
    font-size: 13px; font-weight: 600;
    cursor: pointer; border: none;
    background: transparent; color: var(--rh-muted);
    display: flex; align-items: center; justify-content: center; gap: 8px;
    transition: all var(--rh-transition);
    border-right: 1px solid var(--rh-border);
    min-width: 110px;
}
.op-tab:last-child { border-right: none; }
.op-tab:hover { background: var(--rh-brand-lt); color: var(--rh-brand); }
.op-tab.active { background: var(--rh-brand); color: #fff; }
.op-tab .op-dot { width: 8px; height: 8px; border-radius: 50%; }

/* ─── API PROVIDER CARDS ─────────────────────────── */
.provider-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 14px; }

.provider-card {
    border: 2px solid var(--rh-border);
    border-radius: var(--rh-radius);
    overflow: hidden;
    transition: all var(--rh-transition);
    position: relative;
    background: var(--rh-card);
}
.provider-card:hover { box-shadow: var(--rh-shadow-md); transform: translateY(-2px); }
.provider-card.primary { border-color: var(--rh-brand); box-shadow: 0 0 0 3px rgba(79,70,229,.12); }
.provider-card.backup  { border-color: var(--rh-green); box-shadow: 0 0 0 3px rgba(5,150,105,.10); }
.provider-card.inactive{ border-color: var(--rh-border); opacity: .75; }

.provider-header {
    display: flex; align-items: center; gap: 12px;
    padding: 14px 16px;
    background: #fafafa;
    border-bottom: 1px solid var(--rh-border);
}
.provider-logo {
    width: 40px; height: 40px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 800; color: #fff;
    flex-shrink: 0;
}
.provider-name { font-size: 14px; font-weight: 700; color: var(--rh-text); }
.provider-sub  { font-size: 11.5px; color: var(--rh-muted); margin-top: 1px; }
.provider-role-badge {
    margin-left: auto;
    font-size: 11px; font-weight: 700;
    padding: 3px 10px; border-radius: 99px;
}
.role-primary  { background: var(--rh-brand-lt); color: var(--rh-brand); }
.role-backup   { background: #d1fae5; color: #065f46; }
.role-inactive { background: #f3f4f6; color: var(--rh-muted); }

.provider-body { padding: 14px 16px; }
.prov-stat-row { display: flex; gap: 10px; margin-bottom: 12px; }
.prov-stat { flex: 1; background: var(--rh-page); border-radius: var(--rh-radius-sm); padding: 8px 10px; text-align: center; }
.prov-stat-val { font-size: 15px; font-weight: 800; color: var(--rh-text); }
.prov-stat-lbl { font-size: 10px; color: var(--rh-muted); margin-top: 1px; }

.prov-info-row { display: flex; justify-content: space-between; font-size: 12px; color: var(--rh-muted); padding: 4px 0; border-bottom: 1px solid #f3f4f6; }
.prov-info-row:last-child { border-bottom: none; }
.prov-info-val { font-weight: 600; color: var(--rh-text-sub); font-family: monospace; font-size: 11.5px; }

.provider-footer {
    padding: 10px 16px;
    border-top: 1px solid var(--rh-border);
    display: flex; gap: 8px; align-items: center;
}

/* ─── DRAG HANDLE / ORDER ─────────────────────────── */
.priority-badge {
    position: absolute; top: 10px; left: 10px;
    width: 22px; height: 22px;
    background: var(--rh-brand);
    color: #fff;
    border-radius: 50%;
    font-size: 11px; font-weight: 800;
    display: flex; align-items: center; justify-content: center;
    z-index: 2;
}

/* ─── SWITCH LOG ─────────────────────────────────── */
.log-row { display: flex; align-items: flex-start; gap: 12px; padding: 10px 0; border-bottom: 1px solid var(--rh-border); }
.log-row:last-child { border-bottom: none; }
.log-icon { width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.log-icon svg { width: 14px; height: 14px; color: #fff; }
.log-text { font-size: 12.5px; color: var(--rh-text-sub); }
.log-time { font-size: 11px; color: var(--rh-faint); margin-top: 2px; }

/* ─── HEALTH METERS ──────────────────────────────── */
.health-meter-wrap { height: 6px; background: #f3f4f6; border-radius: 3px; overflow: hidden; margin-top: 4px; }
.health-meter-bar  { height: 100%; border-radius: 3px; transition: width .6s ease; }

/* ─── GLOBAL RULE CARD ───────────────────────────── */
.rule-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 0; border-bottom: 1px solid var(--rh-border);
}
.rule-row:last-child { border-bottom: none; }
.rule-name { font-size: 13px; font-weight: 600; color: var(--rh-text); }
.rule-desc { font-size: 11.5px; color: var(--rh-muted); margin-top: 2px; }
</style>
@endpush

@section('content')
<div class="rh-breadcrumb">
    <a href="{{ route('superadmin.dashboard') }}">Dashboard</a>
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>Operator API Switching</span>
</div>

<div class="rh-alert rh-alert-warn" style="margin-bottom:18px">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
    <span><strong>Live Control:</strong> Switching the Primary API provider affects all active recharges immediately. Auto-fallback is ON — if Primary fails, Backup is used automatically. Changes are logged in Audit.</span>
</div>

{{-- Global Controls --}}
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;margin-bottom:20px">
    <div class="rh-card" style="padding:16px 18px">
        <div style="font-size:11px;font-weight:700;color:var(--rh-muted);text-transform:uppercase;margin-bottom:8px">Auto-Failover</div>
        <div style="display:flex;align-items:center;gap:10px">
            <label class="rh-toggle-wrap">
                <input type="checkbox" class="rh-toggle-input" id="autoFailover" checked>
                <span class="rh-toggle"></span>
            </label>
            <span style="font-size:13px;font-weight:600;color:var(--rh-text)" id="autoFailoverLbl">Enabled</span>
        </div>
        <div style="font-size:11.5px;color:var(--rh-muted);margin-top:6px">Auto-switch to backup on 3 consecutive failures</div>
    </div>
    <div class="rh-card" style="padding:16px 18px">
        <div style="font-size:11px;font-weight:700;color:var(--rh-muted);text-transform:uppercase;margin-bottom:8px">Failover Threshold</div>
        <div style="display:flex;align-items:center;gap:8px">
            <input type="number" id="failoverThreshold" value="3" min="1" max="10"
                style="width:60px;padding:6px 8px;border:1px solid var(--rh-border);border-radius:var(--rh-radius-sm);font-family:inherit;font-size:14px;font-weight:700;text-align:center;outline:none">
            <span style="font-size:13px;color:var(--rh-muted)">failures before switch</span>
        </div>
    </div>
    <div class="rh-card" style="padding:16px 18px">
        <div style="font-size:11px;font-weight:700;color:var(--rh-muted);text-transform:uppercase;margin-bottom:8px">Health Check Interval</div>
        <div style="display:flex;align-items:center;gap:8px">
            <select id="healthInterval"
                style="padding:6px 10px;border:1px solid var(--rh-border);border-radius:var(--rh-radius-sm);font-family:inherit;font-size:13px;outline:none;background:#fff">
                <option value="30">Every 30s</option>
                <option value="60" selected>Every 1 min</option>
                <option value="300">Every 5 min</option>
                <option value="600">Every 10 min</option>
            </select>
        </div>
        <div style="font-size:11.5px;color:var(--rh-muted);margin-top:6px">Ping each provider endpoint</div>
    </div>
</div>

{{-- Operator Tabs --}}
<div class="op-tab-bar" id="opTabBar">
    <button class="op-tab active" onclick="switchOpTab(this,'JIO')" data-op="JIO">
        <span class="op-dot" style="background:#4f46e5"></span> Jio
    </button>
    <button class="op-tab" onclick="switchOpTab(this,'AIRTEL')" data-op="AIRTEL">
        <span class="op-dot" style="background:#dc2626"></span> Airtel
    </button>
    <button class="op-tab" onclick="switchOpTab(this,'VI')" data-op="VI">
        <span class="op-dot" style="background:#d97706"></span> Vi
    </button>
    <button class="op-tab" onclick="switchOpTab(this,'BSNL')" data-op="BSNL">
        <span class="op-dot" style="background:#059669"></span> BSNL
    </button>
    <button class="op-tab" onclick="switchOpTab(this,'DTH')" data-op="DTH">
        <span class="op-dot" style="background:#7c3aed"></span> DTH
    </button>
    <button class="op-tab" onclick="switchOpTab(this,'UTILITY')" data-op="UTILITY">
        <span class="op-dot" style="background:#0284c7"></span> Utility
    </button>
</div>

{{-- Provider Cards --}}
<div class="provider-grid" id="providerGrid">
    <!-- Rendered by JS -->
</div>

{{-- Bottom Row: Rules + Switch Log --}}
<div style="display:grid;grid-template-columns:1fr 380px;gap:16px;margin-top:20px">

    {{-- Switching Rules --}}
    <div class="rh-card">
        <div class="rh-card-header">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;color:var(--rh-brand)"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <span class="rh-card-title">Auto-Switch Rules</span>
            <button class="btn btn-primary btn-sm" style="margin-left:auto" onclick="addRule()">+ Add Rule</button>
        </div>
        <div style="padding:6px 18px 16px">
            <div class="rule-row">
                <div>
                    <div class="rule-name">Response timeout &gt; 10s</div>
                    <div class="rule-desc">Switch to next provider if response exceeds threshold</div>
                </div>
                <div style="display:flex;align-items:center;gap:10px">
                    <label class="rh-toggle-wrap"><input type="checkbox" class="rh-toggle-input" checked><span class="rh-toggle"></span></label>
                    <span class="badge badge-green">ON</span>
                </div>
            </div>
            <div class="rule-row">
                <div>
                    <div class="rule-name">Failure rate &gt; 15% in 5 min</div>
                    <div class="rule-desc">Switch when rolling failure rate crosses threshold</div>
                </div>
                <div style="display:flex;align-items:center;gap:10px">
                    <label class="rh-toggle-wrap"><input type="checkbox" class="rh-toggle-input" checked><span class="rh-toggle"></span></label>
                    <span class="badge badge-green">ON</span>
                </div>
            </div>
            <div class="rule-row">
                <div>
                    <div class="rule-name">HTTP 5xx error from provider</div>
                    <div class="rule-desc">Immediate switch on server error response</div>
                </div>
                <div style="display:flex;align-items:center;gap:10px">
                    <label class="rh-toggle-wrap"><input type="checkbox" class="rh-toggle-input" checked><span class="rh-toggle"></span></label>
                    <span class="badge badge-green">ON</span>
                </div>
            </div>
            <div class="rule-row">
                <div>
                    <div class="rule-name">Daily quota exceeded</div>
                    <div class="rule-desc">Auto-rotate when provider daily limit is reached</div>
                </div>
                <div style="display:flex;align-items:center;gap:10px">
                    <label class="rh-toggle-wrap"><input type="checkbox" class="rh-toggle-input"><span class="rh-toggle"></span></label>
                    <span class="badge badge-gray">OFF</span>
                </div>
            </div>
            <div class="rule-row">
                <div>
                    <div class="rule-name">Scheduled maintenance window</div>
                    <div class="rule-desc">Proactively switch before known downtime</div>
                </div>
                <div style="display:flex;align-items:center;gap:10px">
                    <label class="rh-toggle-wrap"><input type="checkbox" class="rh-toggle-input"><span class="rh-toggle"></span></label>
                    <span class="badge badge-gray">OFF</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Switch Log --}}
    <div class="rh-card">
        <div class="rh-card-header">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;color:var(--rh-amber)"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="rh-card-title">Switch History</span>
            <a href="{{ route('superadmin.audit') }}" style="margin-left:auto;font-size:12px;color:var(--rh-brand);text-decoration:none;font-weight:600">Full log →</a>
        </div>
        <div style="padding:6px 18px 14px">
            <div class="log-row">
                <div class="log-icon" style="background:#4f46e5"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg></div>
                <div>
                    <div class="log-text"><strong>Airtel</strong>: API-2 → API-5 switched by <strong>SuperAdmin</strong></div>
                    <div class="log-time">Today 5:34 PM · Manual</div>
                </div>
            </div>
            <div class="log-row">
                <div class="log-icon" style="background:#dc2626"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></div>
                <div>
                    <div class="log-text"><strong>BSNL</strong>: Auto-failover triggered — API-3 timed out</div>
                    <div class="log-time">Today 2:12 PM · Auto-failover</div>
                </div>
            </div>
            <div class="log-row">
                <div class="log-icon" style="background:#059669"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                <div>
                    <div class="log-text"><strong>Jio</strong>: Restored to primary API-1 after maintenance</div>
                    <div class="log-time">Yesterday 10:00 AM · Scheduled</div>
                </div>
            </div>
            <div class="log-row">
                <div class="log-icon" style="background:#d97706"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg></div>
                <div>
                    <div class="log-text"><strong>Vi</strong>: API-4 → API-1, failure rate exceeded 20%</div>
                    <div class="log-time">Yesterday 8:43 AM · Auto-failover</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Confirm Switch Modal --}}
<div class="rh-modal-overlay" id="switchModal">
    <div class="rh-modal" style="width:480px">
        <div class="rh-modal-hd">
            <div class="rh-modal-title" id="switchModalTitle">Confirm API Switch</div>
            <button class="rh-modal-close" onclick="closeSwitchModal()">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="rh-alert rh-alert-warn" style="margin-bottom:16px">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span id="switchModalBody">This action will immediately route all recharges through the selected provider.</span>
        </div>
        <div style="margin-bottom:14px">
            <label class="rh-label">Reason for switch (optional)</label>
            <textarea id="switchReason" class="rh-input" style="resize:none;height:70px" placeholder="e.g. Maintenance window, better success rate…"></textarea>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:8px">
            <button class="btn btn-outline btn-sm" onclick="closeSwitchModal()">Cancel</button>
            <button class="btn btn-primary btn-sm" id="switchConfirmBtn" onclick="confirmSwitch()">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                Confirm Switch
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Data ──────────────────────────────────────────
const OPS = {
    JIO:    { name:'Jio',     color:'#4f46e5' },
    AIRTEL: { name:'Airtel',  color:'#dc2626' },
    VI:     { name:'Vi',      color:'#d97706' },
    BSNL:   { name:'BSNL',   color:'#059669' },
    DTH:    { name:'DTH',    color:'#7c3aed' },
    UTILITY:{ name:'Utility', color:'#0284c7' },
};

const PROVIDERS = {
    JIO: [
        { id:'JIO_API1', name:'JioPay Gateway',    version:'v3', uptime:99.8, latency:82,  successRate:98.7, reqToday:3241, role:'primary'  },
        { id:'JIO_API2', name:'NxtPay Jio Bridge', version:'v2', uptime:97.2, latency:145, successRate:95.1, reqToday:0,    role:'backup'   },
        { id:'JIO_API3', name:'PayXpress Jio',     version:'v1', uptime:94.1, latency:210, successRate:91.4, reqToday:0,    role:'inactive' },
    ],
    AIRTEL: [
        { id:'AIRTEL_API1', name:'Airtel Money API',   version:'v2', uptime:98.1, latency:95,  successRate:97.3, reqToday:0,    role:'primary'  },
        { id:'AIRTEL_API2', name:'SafePay Airtel',     version:'v1', uptime:96.5, latency:130, successRate:94.8, reqToday:1180, role:'backup'   },
        { id:'AIRTEL_API3', name:'QuickPay Airtel',    version:'v3', uptime:99.1, latency:78,  successRate:99.1, reqToday:0,    role:'inactive' },
        { id:'AIRTEL_API4', name:'AirtelX Enterprise', version:'v2', uptime:97.8, latency:110, successRate:96.2, reqToday:0,    role:'inactive' },
    ],
    VI: [
        { id:'VI_API1', name:'Vi Connect API',  version:'v2', uptime:96.2, latency:120, successRate:94.5, reqToday:820, role:'primary' },
        { id:'VI_API2', name:'ViPay Gateway',   version:'v1', uptime:93.5, latency:195, successRate:90.2, reqToday:0,   role:'backup'  },
    ],
    BSNL: [
        { id:'BSNL_API1', name:'BSNL Direct API',  version:'v1', uptime:92.3, latency:220, successRate:89.5, reqToday:0,  role:'backup'   },
        { id:'BSNL_API2', name:'TeleCom BSNL',     version:'v2', uptime:88.1, latency:280, successRate:86.0, reqToday:0,  role:'inactive' },
        { id:'BSNL_API3', name:'ReliNet BSNL',     version:'v1', uptime:85.0, latency:310, successRate:82.4, reqToday:290,role:'primary'  },
    ],
    DTH: [
        { id:'DTH_API1', name:'DTHPay Master', version:'v2', uptime:97.5, latency:105, successRate:96.8, reqToday:412, role:'primary' },
        { id:'DTH_API2', name:'SkyConnect DTH', version:'v1', uptime:95.1, latency:142, successRate:93.2, reqToday:0, role:'backup' },
    ],
    UTILITY: [
        { id:'UTIL_API1', name:'UtiliPay Core',  version:'v3', uptime:98.8, latency:88, successRate:98.1, reqToday:156, role:'primary' },
        { id:'UTIL_API2', name:'BillBridge API', version:'v2', uptime:96.4, latency:112, successRate:95.6, reqToday:0, role:'backup' },
    ],
};

let currentOp   = 'JIO';
let pendingSwitch = null;

// ── Operator Tab ───────────────────────────────────
function switchOpTab(btn, op) {
    document.querySelectorAll('.op-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    currentOp = op;
    renderProviders(op);
}

// ── Render Providers ───────────────────────────────
function renderProviders(op) {
    const list = PROVIDERS[op] || [];
    const opInfo = OPS[op];
    const grid = document.getElementById('providerGrid');

    grid.innerHTML = list.map((p, i) => `
        <div class="provider-card ${p.role}" id="card_${p.id}">
            <span class="priority-badge" style="background:${p.role==='primary'?'var(--rh-brand)':p.role==='backup'?'var(--rh-green)':'#9ca3af'}">${i+1}</span>
            <div class="provider-header">
                <div class="provider-logo" style="background:${opInfo.color}">${p.name.slice(0,2).toUpperCase()}</div>
                <div>
                    <div class="provider-name">${p.name}</div>
                    <div class="provider-sub">API ${p.version} · ID: ${p.id}</div>
                </div>
                <span class="provider-role-badge role-${p.role}">${p.role.charAt(0).toUpperCase()+p.role.slice(1)}</span>
            </div>
            <div class="provider-body">
                <div class="prov-stat-row">
                    <div class="prov-stat">
                        <div class="prov-stat-val" style="color:${p.uptime>97?'var(--rh-green)':p.uptime>93?'var(--rh-amber)':'var(--rh-red)'}">${p.uptime}%</div>
                        <div class="prov-stat-lbl">Uptime</div>
                    </div>
                    <div class="prov-stat">
                        <div class="prov-stat-val" style="color:${p.latency<120?'var(--rh-green)':p.latency<200?'var(--rh-amber)':'var(--rh-red)'}">${p.latency}ms</div>
                        <div class="prov-stat-lbl">Latency</div>
                    </div>
                    <div class="prov-stat">
                        <div class="prov-stat-val" style="color:${p.successRate>96?'var(--rh-green)':p.successRate>90?'var(--rh-amber)':'var(--rh-red)'}">${p.successRate}%</div>
                        <div class="prov-stat-lbl">Success</div>
                    </div>
                </div>
                <div class="prov-info-row">
                    <span>Requests today</span>
                    <span class="prov-info-val">${p.reqToday.toLocaleString()}</span>
                </div>
                <div class="prov-info-row" style="margin-top:8px">
                    <span style="font-size:11px;color:var(--rh-muted)">Success rate</span>
                </div>
                <div class="health-meter-wrap">
                    <div class="health-meter-bar" style="width:${p.successRate}%;background:${p.successRate>96?'var(--rh-green)':p.successRate>90?'var(--rh-amber)':'var(--rh-red)'}"></div>
                </div>
            </div>
            <div class="provider-footer">
                ${p.role !== 'primary' ? `<button class="btn btn-primary btn-sm" onclick="openSwitchModal('${op}','${p.id}','primary')">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    Make Primary
                </button>` : `<span class="badge badge-green"><span class="badge-dot"></span> Active Primary</span>`}
                ${p.role !== 'backup' && p.role !== 'primary' ? `<button class="btn btn-outline btn-sm" onclick="openSwitchModal('${op}','${p.id}','backup')">Set as Backup</button>` : ''}
                <button class="btn btn-outline btn-sm" style="margin-left:auto" onclick="pingProvider('${p.id}')">Ping</button>
                <span id="ping_${p.id}" style="font-size:11px;color:var(--rh-muted)"></span>
            </div>
        </div>
    `).join('');

    // Add "Add Provider" card
    grid.innerHTML += `
        <div style="border:2px dashed var(--rh-border);border-radius:var(--rh-radius);display:flex;flex-direction:column;align-items:center;justify-content:center;padding:32px;gap:10px;cursor:pointer;transition:all var(--rh-transition);background:transparent" onclick="addProvider()" onmouseover="this.style.borderColor='var(--rh-brand)';this.style.background='var(--rh-brand-lt)'" onmouseout="this.style.borderColor='var(--rh-border)';this.style.background='transparent'">
            <div style="width:44px;height:44px;border-radius:11px;background:var(--rh-brand-lt);display:flex;align-items:center;justify-content:center">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:22px;height:22px;color:var(--rh-brand)"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            </div>
            <div style="font-size:13px;font-weight:600;color:var(--rh-brand)">Add API Provider</div>
            <div style="font-size:11.5px;color:var(--rh-muted)">Register a new ${OPS[op].name} API endpoint</div>
        </div>
    `;
}
renderProviders('JIO');

// ── Ping Provider ──────────────────────────────────
function pingProvider(id) {
    const el = document.getElementById('ping_'+id);
    el.style.color = 'var(--rh-muted)';
    el.textContent = '⏳ pinging…';
    setTimeout(() => {
        const ms = Math.floor(Math.random()*200)+60;
        const ok = Math.random() > .1;
        el.style.color = ok ? 'var(--rh-green)' : 'var(--rh-red)';
        el.textContent = ok ? `✓ ${ms}ms` : '✗ timeout';
    }, 1000 + Math.random()*800);
}

// ── Switch Modal ───────────────────────────────────
let switchContext = {};

function openSwitchModal(op, providerId, role) {
    const p = (PROVIDERS[op]||[]).find(x => x.id === providerId);
    const opName = OPS[op]?.name || op;
    switchContext = { op, providerId, role };

    document.getElementById('switchModalTitle').textContent = `Switch ${opName} ${role === 'primary' ? 'Primary':'Backup'} Provider`;
    document.getElementById('switchModalBody').textContent = `"${p?.name}" will be set as the ${role} API for ${opName}. ${role==='primary'?'All live recharges route through this immediately.':'Backup is used only when primary fails.'}`;
    document.getElementById('switchModal').classList.add('open');
}

function closeSwitchModal() { document.getElementById('switchModal').classList.remove('open'); }

function confirmSwitch() {
    const { op, providerId, role } = switchContext;
    const list = PROVIDERS[op];
    if (!list) return;

    // Update roles
    list.forEach(p => {
        if (p.role === role) p.role = 'inactive';
        if (p.id === providerId) p.role = role;
    });

    // Assign reqToday to newly primary
    list.forEach(p => {
        if (p.id === providerId && role === 'primary') {
            const total = list.reduce((s,x) => s + x.reqToday, 0) || 1200;
            p.reqToday = total;
            list.filter(x => x.id !== providerId).forEach(x => x.reqToday = 0);
        }
    });

    closeSwitchModal();
    renderProviders(op);

    // Toast
    showToast(`✓ Switched ${OPS[op]?.name} ${role} to ${(PROVIDERS[op].find(x=>x.id===providerId))?.name}`, 'green');
}

function addProvider() {
    showToast('Add provider form coming soon — configure in Seller API Config module', 'blue');
}

function addRule() {
    showToast('Rule builder coming soon.', 'blue');
}

// ── Auto-failover toggle label ─────────────────────
document.getElementById('autoFailover').addEventListener('change', function() {
    document.getElementById('autoFailoverLbl').textContent = this.checked ? 'Enabled' : 'Disabled';
});

// ── Toast ──────────────────────────────────────────
function showToast(msg, color) {
    const colors = { green:'#059669', blue:'#4f46e5', red:'#dc2626' };
    const t = document.createElement('div');
    t.style.cssText = `position:fixed;bottom:24px;right:24px;background:${colors[color]||'#1e293b'};color:#fff;padding:12px 18px;border-radius:10px;font-size:13px;font-weight:600;z-index:1000;box-shadow:0 8px 24px rgba(0,0,0,.18);animation:fadeUp .25s ease;max-width:360px`;
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3200);
}
</script>
@endpush
