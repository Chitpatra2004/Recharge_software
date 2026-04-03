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
                <div style="font-size:13px;color:var(--muted);margin-bottom:12px">Use your API key to integrate RechargeHub into your own system.</div>
                <div id="api-key-display" style="background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:8px;padding:12px 14px;font-family:monospace;font-size:12px;color:var(--muted);word-break:break-all">
                    Generate a key to see it here.
                </div>
                <div id="api-key-note" style="font-size:11px;color:var(--red);margin-top:8px;display:none">Copy this key now — it will not be shown again.</div>
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
</script>
@endpush
