@extends('layouts.seller')
@section('title','API Tools')
@section('page-title','API Tools')
@section('content')

<style>
.at-info-box{background:linear-gradient(135deg,#1e3a5f,#2563eb);border-radius:14px;padding:18px 22px;color:#fff;margin-bottom:22px}
.at-info-label{font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;opacity:.65;display:block;margin-bottom:3px}
.at-info-val{font-family:monospace;font-size:12.5px;font-weight:600;word-break:break-all;background:rgba(255,255,255,.12);padding:5px 10px;border-radius:6px;cursor:pointer;display:inline-block;margin-top:2px}
.at-info-val:hover{background:rgba(255,255,255,.2)}
.at-field{margin-bottom:14px}
.at-field label{display:block;font-size:12px;font-weight:600;color:#334155;margin-bottom:5px}
.at-field input,.at-field textarea{width:100%;padding:10px 13px;border:1.5px solid #cbd5e1;border-radius:9px;font-size:13px;font-family:inherit;background:#fff;color:#0f172a;outline:none;transition:border-color .15s}
.at-field input:focus,.at-field textarea:focus{border-color:#2563eb}
.at-field textarea{resize:vertical;min-height:80px}
.at-hint{font-size:11.5px;color:var(--muted);margin-top:4px}
.token-box{background:#0f172a;border-radius:10px;padding:14px 16px;display:flex;align-items:center;gap:10px;margin-top:10px}
.token-val{font-family:monospace;font-size:12.5px;color:#86efac;flex:1;word-break:break-all;line-height:1.5}
.token-copy{background:rgba(255,255,255,.1);border:none;color:#94a3b8;border-radius:6px;padding:5px 12px;font-size:11.5px;cursor:pointer;transition:all .15s;white-space:nowrap}
.token-copy:hover{background:rgba(255,255,255,.2);color:#fff}
.intg-status-row{display:flex;align-items:center;gap:8px;padding:9px 13px;border-radius:9px;font-size:13px;font-weight:600;margin-bottom:14px}
.dot{width:9px;height:9px;border-radius:50%;flex-shrink:0}
@media(max-width:900px){.at-two-col{grid-template-columns:1fr!important}}
</style>

<div class="page-header">
    <div>
        <h1 class="page-title">API Tools</h1>
        <p class="page-sub">Generate your token then configure integration settings</p>
    </div>
</div>

<div id="atAlert" style="margin-bottom:16px"></div>

<div class="at-two-col" style="display:grid;grid-template-columns:1fr 1fr;gap:22px;align-items:start">

    {{-- LEFT: Token Generation (Step 1) --}}
    <div>
        {{-- Platform Info --}}
        <div class="at-info-box">
            <div style="font-size:11px;font-weight:700;opacity:.65;text-transform:uppercase;letter-spacing:.6px;margin-bottom:14px">Platform Credentials</div>
            <div style="margin-bottom:12px">
                <span class="at-info-label">Our Server IP</span>
                <span class="at-info-val" id="platServerIp" onclick="copyText(this)">—</span>
            </div>
            <div>
                <span class="at-info-label">Your Unique Callback URL</span>
                <span class="at-info-val" id="platCallbackUrl" onclick="copyText(this)" style="font-size:11px">—</span>
            </div>
            <div style="font-size:11px;opacity:.45;margin-top:10px">Click any value to copy</div>
        </div>

        {{-- Step 1: Token --}}
        <div class="card">
            <div class="card-header">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--blue)"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                <span class="card-title">Step 1 — Generate API Token</span>
            </div>
            <div class="card-body">
                <div id="keyStatusRow" style="display:none" class="intg-status-row">
                    <div class="dot" id="keyStatusDot"></div>
                    <span id="keyStatusText"></span>
                </div>

                <div id="tokenResult" style="display:none;margin-bottom:14px">
                    <div style="font-size:12px;font-weight:600;color:#059669;margin-bottom:6px;display:flex;align-items:center;gap:6px">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Token generated — copy now, shown only once!
                    </div>
                    <div class="token-box">
                        <span class="token-val" id="tokenValue"></span>
                        <button class="token-copy" onclick="copyToken()">Copy</button>
                    </div>
                </div>

                <p style="font-size:13px;color:var(--muted);margin-bottom:16px;line-height:1.55">
                    Generate your API access token. You can regenerate it at any time — the old token will be revoked immediately.
                </p>
                <button class="btn btn-primary" onclick="generateToken()" id="genTokenBtn" style="width:100%">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    Generate Token
                </button>
            </div>
        </div>
    </div>

    {{-- RIGHT: Integration Settings (Step 2) --}}
    <div class="card">
        <div class="card-header">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--blue)"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
            <span class="card-title">Step 2 — Integration Settings</span>
            <span id="intgBadge" style="margin-left:auto;font-size:11px;font-weight:700;padding:2px 9px;border-radius:20px"></span>
        </div>
        <div class="card-body">
            <div class="at-field">
                <label>Website URL *</label>
                <input type="url" id="at-website" placeholder="https://yourdomain.com">
            </div>
            <div class="at-field">
                <label>Callback URL * <span style="font-size:11px;color:var(--muted)">(we POST results here)</span></label>
                <input type="url" id="at-callback" placeholder="https://yourdomain.com/recharge/callback">
            </div>
            <div class="at-field">
                <label>Allowed IPs *</label>
                <textarea id="at-ips" placeholder="One IP per line&#10;e.g. 203.0.113.42"></textarea>
                <div class="at-hint">Enter your server IP(s). Only these IPs can use the API.</div>
            </div>
            <button class="btn btn-primary" onclick="saveIntegration()" id="saveIntBtn" style="width:100%">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                Save &amp; Submit for Approval
            </button>
            <div class="at-hint" style="margin-top:8px">After saving, admin will review and activate your integration.</div>
        </div>
    </div>

</div>

@push('scripts')
<script>
if (!requireAuth()) { /* blocked */ }

async function loadConfig() {
    try {
        const res = await fetch('/api/v1/seller/api-config', {
            headers: { 'Authorization': 'Bearer ' + getToken(), 'Accept': 'application/json' }
        });
        const d = await res.json();
        if (!res.ok) return;
        const data = d.data || {};
        const intg = data.integration || {};

        document.getElementById('platServerIp').textContent    = data.server_ip   || '—';
        document.getElementById('platCallbackUrl').textContent = data.callback_url || '—';

        // Prefill integration form
        if (intg.website_url)  document.getElementById('at-website').value  = intg.website_url;
        if (intg.callback_url) document.getElementById('at-callback').value = intg.callback_url;
        if (intg.allowed_ips)  document.getElementById('at-ips').value      = intg.allowed_ips;

        // Integration status badge
        const stMap = {
            none:     { bg:'#f1f5f9', color:'#64748b', text:'Not submitted' },
            pending:  { bg:'#fef3c7', color:'#92400e', text:'Pending Review' },
            approved: { bg:'#d1fae5', color:'#065f46', text:'Approved' },
            rejected: { bg:'#fee2e2', color:'#991b1b', text:'Rejected — re-submit' },
        };
        const st = stMap[intg.status || 'none'] || stMap.none;
        const badge = document.getElementById('intgBadge');
        badge.textContent = st.text;
        badge.style.background = st.bg;
        badge.style.color = st.color;

        // Existing API key status
        if (data.api_key) {
            const row = document.getElementById('keyStatusRow');
            row.style.display = 'flex';
            row.style.background = '#d1fae5';
            document.getElementById('keyStatusDot').style.background = '#10b981';
            document.getElementById('keyStatusText').style.color = '#065f46';
            document.getElementById('keyStatusText').textContent = 'Active token — prefix: ' + (data.api_key.prefix || '—');
            document.getElementById('genTokenBtn').textContent = 'Regenerate Token';
        }
    } catch (e) {
        showAlert('danger', 'Failed to load configuration.');
    }
}

async function generateToken() {
    if (!confirm('Generate a new API token? Any existing token will be revoked.')) return;
    const btn = document.getElementById('genTokenBtn');
    btn.disabled = true; btn.textContent = 'Generating…';
    try {
        const res = await fetch('/api/v1/seller/api-config/generate-token', {
            method: 'POST',
            headers: { 'Authorization': 'Bearer ' + getToken(), 'Accept': 'application/json', 'Content-Type': 'application/json' },
        });
        const d = await res.json();
        if (res.ok) {
            document.getElementById('tokenValue').textContent = d.api_key || '';
            document.getElementById('tokenResult').style.display = 'block';

            const row = document.getElementById('keyStatusRow');
            row.style.display = 'flex';
            row.style.background = '#d1fae5';
            document.getElementById('keyStatusDot').style.background = '#10b981';
            document.getElementById('keyStatusText').style.color = '#065f46';
            document.getElementById('keyStatusText').textContent = 'Active token — prefix: ' + (d.key_prefix || '—');

            btn.textContent = 'Regenerate Token';
            btn.disabled = false;
        } else {
            showAlert('danger', d.message || 'Token generation failed.');
            btn.disabled = false; btn.textContent = 'Generate Token';
        }
    } catch (e) {
        showAlert('danger', 'Network error. Please try again.');
        btn.disabled = false; btn.textContent = 'Generate Token';
    }
}

async function saveIntegration() {
    const website     = document.getElementById('at-website').value.trim();
    const callback    = document.getElementById('at-callback').value.trim();
    const allowed_ips = document.getElementById('at-ips').value.trim();
    if (!website || !callback || !allowed_ips) {
        showAlert('warning', 'Website URL, Callback URL, and Allowed IPs are required.');
        return;
    }
    const btn = document.getElementById('saveIntBtn');
    btn.disabled = true; btn.textContent = 'Saving…';

    try {
        let res = await fetch('/api/v1/seller/api-config/integration', {
            method: 'PATCH',
            headers: { 'Authorization': 'Bearer ' + getToken(), 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ website_url: website, callback_url: callback, allowed_ips, status_check_url: '', dispute_url: '' }),
        });
        if (res.status === 404) {
            res = await fetch('/api/v1/seller/api-config/integration', {
                method: 'POST',
                headers: { 'Authorization': 'Bearer ' + getToken(), 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ website_url: website, callback_url: callback, allowed_ips, status_check_url: '', dispute_url: '' }),
            });
        }
        const d = await res.json();
        if (res.ok) {
            showAlert('success', d.message || 'Integration saved. Awaiting admin approval.');
            loadConfig();
        } else {
            const errs = d.errors ? Object.values(d.errors).flat().join(' ') : (d.message || 'Save failed.');
            showAlert('danger', errs);
        }
    } catch (e) {
        showAlert('danger', 'Network error. Please try again.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg> Save &amp; Submit for Approval`;
    }
}

function copyToken() {
    const val = document.getElementById('tokenValue').textContent.trim();
    navigator.clipboard.writeText(val).then(() => {
        const btn = document.querySelector('.token-copy');
        const orig = btn.textContent;
        btn.textContent = 'Copied!'; btn.style.color = '#86efac';
        setTimeout(() => { btn.textContent = orig; btn.style.color = ''; }, 1500);
    });
}

function copyText(el) {
    const text = el.textContent.trim();
    if (!text || text === '—') return;
    navigator.clipboard.writeText(text).then(() => {
        const orig = el.textContent;
        el.textContent = 'Copied!';
        setTimeout(() => { el.textContent = orig; }, 1200);
    });
}

function showAlert(type, msg) {
    document.getElementById('atAlert').innerHTML = `<div class="alert alert-${type}">${msg}</div>`;
    setTimeout(() => { document.getElementById('atAlert').innerHTML = ''; }, 6000);
}

loadConfig();
</script>
@endpush
@endsection
