@extends('layouts.user')
@section('title','My Profile')
@section('page-title','My Profile')

@section('content')
<div class="breadcrumb"><a href="/user/dashboard">Dashboard</a><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg><span>Profile</span></div>

<div style="display:grid;grid-template-columns:280px 1fr;gap:16px;align-items:start">
    {{-- Profile Card --}}
    <div class="card">
        <div class="card-body" style="text-align:center">
            <div id="p-avatar" style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,var(--blue-dk),var(--indigo));display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:800;color:#fff;margin:0 auto 16px;box-shadow:0 8px 24px rgba(99,102,241,.35)">U</div>
            <div id="p-name" style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:4px">—</div>
            <div id="p-role" style="font-size:12px;font-weight:600;color:var(--muted);background:var(--card2);display:inline-block;padding:3px 12px;border-radius:20px;margin-bottom:14px">—</div>
            <div style="font-size:13px;color:var(--muted)" id="p-email">—</div>
            <div style="font-size:13px;color:var(--muted);margin-top:4px" id="p-mobile">—</div>
            <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--border)">
                <div style="font-size:12px;font-weight:700;color:var(--muted);margin-bottom:8px">Wallet Balance</div>
                <div style="font-size:24px;font-weight:800;color:#34d399" id="p-balance">—</div>
            </div>
        </div>
    </div>

    {{-- API Key + Settings --}}
    <div style="display:flex;flex-direction:column;gap:16px">
        <div class="card">
            <div class="card-header"><span class="card-title">Account Details</span></div>
            <div class="card-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                    <div>
                        <div style="font-size:11px;font-weight:600;color:var(--muted2);margin-bottom:4px">FULL NAME</div>
                        <div style="font-size:14px;color:var(--text)" id="d-name">—</div>
                    </div>
                    <div>
                        <div style="font-size:11px;font-weight:600;color:var(--muted2);margin-bottom:4px">EMAIL</div>
                        <div style="font-size:14px;color:var(--text)" id="d-email">—</div>
                    </div>
                    <div>
                        <div style="font-size:11px;font-weight:600;color:var(--muted2);margin-bottom:4px">MOBILE</div>
                        <div style="font-size:14px;color:var(--text)" id="d-mobile">—</div>
                    </div>
                    <div>
                        <div style="font-size:11px;font-weight:600;color:var(--muted2);margin-bottom:4px">ACCOUNT TYPE</div>
                        <div style="font-size:14px;color:var(--text)" id="d-role">—</div>
                    </div>
                    <div>
                        <div style="font-size:11px;font-weight:600;color:var(--muted2);margin-bottom:4px">USER ID</div>
                        <div style="font-size:14px;font-family:monospace;color:var(--muted)" id="d-id">—</div>
                    </div>
                    <div>
                        <div style="font-size:11px;font-weight:600;color:var(--muted2);margin-bottom:4px">STATUS</div>
                        <span class="badge success" id="d-status">active</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header" style="justify-content:space-between">
                <span class="card-title">API Key</span>
                <button class="btn btn-outline btn-sm" onclick="generateKey()">Generate New Key</button>
            </div>
            <div class="card-body">
                <div style="font-size:13px;color:var(--muted);margin-bottom:12px">Use your API key to integrate ColdPay into your own system.</div>
                <div id="api-key-display" style="background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:8px;padding:12px 14px;font-family:monospace;font-size:12px;color:var(--muted);word-break:break-all">
                    Generate a key to see it here.
                </div>
                <div id="api-key-note" style="font-size:11px;color:var(--red);margin-top:8px;display:none">Copy this key now — it will not be shown again.</div>
            </div>
        </div>

        {{-- 2FA Settings --}}
        <div class="card" id="tfa-card">
            <div class="card-header" style="justify-content:space-between;align-items:center">
                <span class="card-title">Two-Factor Authentication (2FA)</span>
                <span id="tfa-status-badge" style="font-size:11.5px;font-weight:700;padding:3px 10px;border-radius:20px;background:#f1f5f9;color:#64748b">Loading…</span>
            </div>
            <div class="card-body">
                <p style="font-size:13px;color:var(--muted);margin-bottom:16px">Add an extra layer of security to your account. Choose between a one-time SMS code or an authenticator app (TOTP).</p>

                {{-- Status row --}}
                <div id="tfa-current" style="background:var(--card2,#f8fafc);border:1px solid var(--border);border-radius:10px;padding:14px 16px;margin-bottom:16px;font-size:13px;display:none">
                    <div id="tfa-current-text" style="font-weight:600;color:var(--text)"></div>
                    <div style="margin-top:10px">
                        <button onclick="disable2fa()" style="padding:6px 14px;border-radius:7px;border:1px solid #ef4444;background:#fff;color:#ef4444;font-size:12px;font-weight:700;cursor:pointer">Disable 2FA</button>
                    </div>
                </div>

                {{-- TOTP setup flow --}}
                <div id="tfa-totp-setup" style="display:none;border:1px solid var(--border);border-radius:10px;padding:16px;margin-bottom:16px">
                    <div style="font-size:13px;font-weight:700;margin-bottom:10px">Setup Authenticator App (TOTP)</div>
                    <ol style="font-size:12.5px;color:var(--muted);margin:0 0 12px 16px;line-height:2">
                        <li>Install Google Authenticator or Microsoft Authenticator on your phone.</li>
                        <li>Scan the QR code below (or enter the secret key manually).</li>
                        <li>Enter the 6-digit code shown in the app to confirm.</li>
                    </ol>
                    <div id="tfa-qr-wrap" style="text-align:center;margin-bottom:12px;display:none">
                        <img id="tfa-qr-img" src="" alt="QR Code" style="width:180px;height:180px;border:4px solid #fff;border-radius:10px;box-shadow:0 2px 12px rgba(0,0,0,.12)">
                        <div style="margin-top:8px;font-size:11px;color:var(--muted)">Secret key: <code id="tfa-secret" style="background:var(--card2);padding:2px 6px;border-radius:4px;font-size:11px"></code></div>
                    </div>
                    <div style="display:flex;gap:10px;align-items:center">
                        <button id="tfa-qr-btn" onclick="startTotpSetup()" style="padding:8px 16px;border-radius:8px;border:none;background:#6366f1;color:#fff;font-size:13px;font-weight:700;cursor:pointer">Generate QR Code</button>
                        <div id="tfa-totp-verify-wrap" style="display:none;display:flex;gap:8px;align-items:center">
                            <input id="tfa-totp-code" type="text" maxlength="6" placeholder="6-digit code" style="width:130px;padding:8px 11px;border:1px solid var(--border);border-radius:8px;font-size:14px;font-family:monospace;letter-spacing:3px;text-align:center">
                            <button onclick="enableTotp()" style="padding:8px 16px;border-radius:8px;border:none;background:#10b981;color:#fff;font-size:13px;font-weight:700;cursor:pointer">Activate TOTP</button>
                        </div>
                    </div>
                </div>

                {{-- Action buttons (shown when 2FA is disabled) --}}
                <div id="tfa-actions" style="display:none;display:flex;gap:10px;flex-wrap:wrap">
                    <button onclick="showTotpSetup()" style="padding:8px 18px;border-radius:8px;border:1px solid #6366f1;background:#fff;color:#6366f1;font-size:13px;font-weight:700;cursor:pointer">
                        🔑 Setup Authenticator App
                    </button>
                    <button onclick="enableOtp()" style="padding:8px 18px;border-radius:8px;border:1px solid #0891b2;background:#fff;color:#0891b2;font-size:13px;font-weight:700;cursor:pointer">
                        📱 Enable SMS OTP
                    </button>
                </div>

                <div id="tfa-msg" style="margin-top:10px;font-size:12.5px;display:none"></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><span class="card-title">Danger Zone</span></div>
            <div class="card-body">
                <p style="font-size:13px;color:var(--muted);margin-bottom:14px">Logout of your account on this device.</p>
                <button class="btn" style="background:rgba(239,68,68,.15);color:#f87171;border:1px solid rgba(239,68,68,.25)" onclick="doLogout()">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Sign Out
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const u = getUserData();
    const fields = { 'p-name':u.name, 'p-email':u.email, 'p-mobile':u.mobile, 'p-role':(u.role||'').replace('_',' '), 'd-name':u.name, 'd-email':u.email, 'd-mobile':u.mobile, 'd-role':(u.role||'').replace('_',' '), 'd-id':u.id };
    Object.entries(fields).forEach(([id,val]) => { const el = document.getElementById(id); if(el&&val) el.textContent = val; });
    if (u.name) document.getElementById('p-avatar').textContent = u.name.charAt(0).toUpperCase();
    loadBalance();
});

async function loadBalance() {
    const res = await apiFetch('/api/v1/wallet/balance');
    if (res?.ok) { const d = await res.json(); document.getElementById('p-balance').textContent = fmtAmt(d.balance); }
}

async function generateKey() {
    const res = await apiFetch('/api/v1/auth/api-key', { method:'POST' });
    if (!res) return;
    const data = await res.json();
    const key  = data.api_key || data.key || 'key_generated';
    document.getElementById('api-key-display').textContent = key;
    document.getElementById('api-key-display').style.color = '#34d399';
    document.getElementById('api-key-note').style.display  = 'block';
}

// ── 2FA ──────────────────────────────────────────────────────────────────────
let _tfaMethod = 'none';

async function load2fa() {
    const res = await apiFetch('/api/v1/auth/me');
    if (!res || !res.ok) return;
    const data = await res.json();
    _tfaMethod = data.two_factor_method || 'none';
    render2fa(_tfaMethod);
}

function render2fa(method) {
    const badge   = document.getElementById('tfa-status-badge');
    const current = document.getElementById('tfa-current');
    const actions = document.getElementById('tfa-actions');
    const setup   = document.getElementById('tfa-totp-setup');
    const currentTxt = document.getElementById('tfa-current-text');

    // Hide all first
    current.style.display = 'none';
    actions.style.display = 'none';
    setup.style.display   = 'none';
    tfaMsg('');

    if (method === 'totp') {
        badge.textContent = '✓ TOTP Active';
        badge.style.background = '#d1fae5'; badge.style.color = '#065f46';
        currentTxt.textContent = '🔐 Authenticator App (TOTP) is enabled. Your account is secured with a time-based one-time password.';
        current.style.display = 'block';
    } else if (method === 'otp') {
        badge.textContent = '✓ SMS OTP Active';
        badge.style.background = '#dbeafe'; badge.style.color = '#1e40af';
        currentTxt.textContent = '📱 SMS OTP is enabled. A one-time code will be sent to your mobile number at each login.';
        current.style.display = 'block';
    } else {
        badge.textContent = '✗ Not Enabled';
        badge.style.background = '#fee2e2'; badge.style.color = '#991b1b';
        actions.style.display = 'flex';
    }
}

function showTotpSetup() {
    document.getElementById('tfa-actions').style.display = 'none';
    document.getElementById('tfa-totp-setup').style.display = 'block';
}

async function startTotpSetup() {
    const btn = document.getElementById('tfa-qr-btn');
    btn.disabled = true; btn.textContent = 'Generating…';
    const res = await apiFetch('/api/v1/auth/2fa/setup-totp', { method:'POST' });
    btn.disabled = false; btn.textContent = 'Regenerate QR';
    if (!res) return;
    const data = await res.json();
    document.getElementById('tfa-qr-img').src = data.qr_url || '';
    document.getElementById('tfa-secret').textContent = data.secret || '';
    document.getElementById('tfa-qr-wrap').style.display = 'block';
    document.getElementById('tfa-totp-verify-wrap').style.display = 'flex';
    document.getElementById('tfa-totp-code').value = '';
    document.getElementById('tfa-totp-code').focus();
}

async function enableTotp() {
    const code = document.getElementById('tfa-totp-code').value.trim();
    if (code.length !== 6) { tfaMsg('Please enter the 6-digit code from your app.', true); return; }
    const res = await apiFetch('/api/v1/auth/2fa/enable-totp', { method:'POST', body: JSON.stringify({ code }) });
    if (!res) return;
    const data = await res.json();
    if (res.ok) { tfaMsg(data.message || 'TOTP enabled!', false); _tfaMethod='totp'; render2fa('totp'); }
    else         { tfaMsg(data.message || 'Invalid code.', true); }
}

async function enableOtp() {
    if (!confirm('Enable SMS OTP? A one-time code will be sent to your mobile at each login.')) return;
    const res = await apiFetch('/api/v1/auth/2fa/enable-otp', { method:'POST' });
    if (!res) return;
    const data = await res.json();
    if (res.ok) { tfaMsg(data.message || 'SMS OTP enabled!', false); _tfaMethod='otp'; render2fa('otp'); }
    else         { tfaMsg(data.message || 'Failed.', true); }
}

async function disable2fa() {
    if (!confirm('Disable 2FA? Your account will be less secure.')) return;
    const res = await apiFetch('/api/v1/auth/2fa/disable', { method:'POST' });
    if (!res) return;
    const data = await res.json();
    if (res.ok) { tfaMsg(data.message || '2FA disabled.', false); _tfaMethod='none'; render2fa('none'); }
    else         { tfaMsg(data.message || 'Failed.', true); }
}

function tfaMsg(msg, isError = false) {
    const el = document.getElementById('tfa-msg');
    if (!msg) { el.style.display='none'; return; }
    el.textContent = msg;
    el.style.display = 'block';
    el.style.color = isError ? '#ef4444' : '#10b981';
    el.style.fontWeight = '600';
}

load2fa();
</script>
@endpush
