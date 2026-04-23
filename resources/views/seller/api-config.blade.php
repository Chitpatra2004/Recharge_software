@extends('layouts.seller')
@section('title','API Setting')
@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">API Setting</h1>
        <p class="page-sub">Manage seller integration URLs, IP validation, API key, and documentation</p>
    </div>
</div>

<div id="config-wrap">
    <div style="text-align:center;padding:40px;color:#6b7280">Loading configuration...</div>
</div>

<div class="modal-overlay" id="intg-modal">
    <div class="modal-box" style="max-width:640px">
        <button class="modal-close" onclick="closeIntgModal()">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <h3 style="font-size:18px;font-weight:700;color:#1e293b;margin-bottom:6px">Submit Integration Request</h3>
        <p style="font-size:13px;color:#64748b;margin-bottom:20px">Provide your seller integration endpoints in the standard format. Admin will review the request before activation.</p>
        <div class="alert alert-danger" id="intg-error" style="display:none;margin-bottom:14px">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span id="intg-error-msg"></span>
        </div>
        <form id="intg-form">
            <div class="form-group">
                <label class="form-label">Website URL <span style="color:#ef4444">*</span></label>
                <input type="url" id="intg-website" class="form-control" placeholder="https://yourdomain.com" required>
            </div>
            <div class="form-group">
                <label class="form-label">Callback URL <span style="color:#ef4444">*</span></label>
                <input type="url" id="intg-callback" class="form-control" placeholder="https://yourdomain.com/recharge/callback" required>
            </div>
            <div class="form-group">
                <label class="form-label">Status Check URL <span style="color:#ef4444">*</span></label>
                <input type="url" id="intg-status-check" class="form-control" placeholder="https://yourdomain.com/recharge/status" required>
            </div>
            <div class="form-group">
                <label class="form-label">Dispute URL <span style="color:#ef4444">*</span></label>
                <input type="url" id="intg-dispute" class="form-control" placeholder="https://yourdomain.com/recharge/dispute" required>
            </div>
            <div class="form-group">
                <label class="form-label">Allowed Seller IPs <span style="color:#ef4444">*</span></label>
                <textarea id="intg-ips" class="form-control" rows="3" placeholder="203.0.113.42&#10;198.51.100.0/24" required></textarea>
                <small style="font-size:11.5px;color:#94a3b8;margin-top:5px;display:block">Use one IP or CIDR per line. These values will be applied to generated API keys.</small>
            </div>
            <div class="form-group">
                <label class="form-label">Your Site Username</label>
                <input type="text" id="intg-username" class="form-control" placeholder="Username we should use on your site">
            </div>
            <div class="form-group">
                <label class="form-label">Password Hint</label>
                <input type="text" id="intg-password-hint" class="form-control" placeholder="Optional hint only">
            </div>
            <div style="display:flex;gap:12px;margin-top:20px">
                <button type="button" onclick="closeIntgModal()" style="flex:1;padding:11px;border:1.5px solid #e2e8f0;background:#fff;color:#64748b;border-radius:9px;font-size:13.5px;font-weight:600;cursor:pointer">Cancel</button>
                <button type="submit" id="intg-btn" style="flex:2;padding:11px;background:linear-gradient(135deg,#10b981,#0d9488);color:#fff;border:none;border-radius:9px;font-size:13.5px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px">
                    <div class="spinner" id="intg-spinner" style="display:none"></div>
                    <span id="intg-btn-text">Submit Request</span>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="key-modal">
    <div class="modal-box" style="max-width:520px">
        <button class="modal-close" onclick="closeKeyModal()">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <h3 style="font-size:17px;font-weight:700;color:#1e293b;margin-bottom:8px">API Key Generated</h3>
        <p style="font-size:12.5px;color:#64748b;margin-bottom:14px">Copy and store this key securely. It will not be shown again.</p>
        <div class="code-box" style="display:flex;align-items:center;justify-content:space-between;gap:10px">
            <span id="key-value" style="font-family:monospace;font-size:13px;word-break:break-all"></span>
            <button onclick="copyApiKey()" id="copy-key-btn" style="background:rgba(255,255,255,.15);border:none;color:#94a3b8;border-radius:6px;padding:5px 12px;font-size:11.5px;font-weight:600;cursor:pointer">Copy</button>
        </div>
    </div>
</div>

<script>
let configData = null;

function closeIntgModal(){ document.getElementById('intg-modal').classList.remove('show'); }
function closeKeyModal(){ document.getElementById('key-modal').classList.remove('show'); }

function openIntgModal(){
    document.getElementById('intg-error').style.display = 'none';
    document.getElementById('intg-form').reset();
    document.getElementById('intg-modal').classList.add('show');
}

function copyApiKey(){
    const key = document.getElementById('key-value').textContent.trim();
    navigator.clipboard.writeText(key).then(() => {
        const btn = document.getElementById('copy-key-btn');
        btn.textContent = 'Copied!';
        setTimeout(() => btn.textContent = 'Copy', 1500);
    });
}

function fmtDate(value){
    if (!value) return '-';
    return new Date(value).toLocaleDateString('en-IN', { day:'2-digit', month:'short', year:'numeric' });
}

function copyText(id){
    const text = document.getElementById(id).textContent.trim();
    navigator.clipboard.writeText(text).then(() => {
        const btn = document.querySelector(`[onclick="copyText('${id}')"]`);
        if (btn) {
            const prev = btn.textContent;
            btn.textContent = 'Copied!';
            setTimeout(() => btn.textContent = prev, 1500);
        }
    });
}

function renderConfig(raw){
    const data = raw.data || raw;
    configData = data;
    const intg = data.integration;
    const saleAccess = data.sale_access || {};
    const apiKeyObj = data.api_key;
    const hasApiKey = !!apiKeyObj?.prefix;
    const isApproved = intg?.status === 'approved';
    const apiEnabled = saleAccess.api_status === 'enabled';

    let banner = '';
    if (!intg || intg.status === 'none') {
        banner = `<div style="background:#fffbeb;border:1.5px solid #fde68a;border-radius:12px;padding:16px 20px;display:flex;align-items:flex-start;gap:12px;margin-bottom:20px">
            <div style="flex:1">
                <div style="font-size:14px;font-weight:700;color:#92400e;margin-bottom:4px">Integration not configured</div>
                <div style="font-size:13px;color:#a16207">Submit your standard seller API URLs to request activation.</div>
            </div>
            <button onclick="openIntgModal()" style="background:#f59e0b;color:#fff;border:none;padding:9px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer">Submit Request</button>
        </div>`;
    } else if (intg.status === 'pending') {
        banner = `<div style="background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:12px;padding:16px 20px;margin-bottom:20px">
            <div style="font-size:14px;font-weight:700;color:#1d4ed8">Integration request under review</div>
            <div style="font-size:13px;color:#2563eb;margin-top:2px">Submitted on ${fmtDate(intg.created_at)}.</div>
        </div>`;
    } else if (intg.status === 'rejected') {
        banner = `<div style="background:#fff1f2;border:1.5px solid #fecdd3;border-radius:12px;padding:16px 20px;margin-bottom:20px">
            <div style="font-size:14px;font-weight:700;color:#be123c;margin-bottom:4px">Integration request rejected</div>
            <div style="font-size:13px;color:#9f1239;margin-bottom:12px">${intg.admin_notes || 'Please update the integration details and submit again.'}</div>
            <button onclick="openIntgModal()" style="background:#ef4444;color:#fff;border:none;padding:9px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer">Re-submit Request</button>
        </div>`;
    }

    const html = `
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:20px">
            <div class="card" style="padding:18px 20px">
                <div style="font-size:11.5px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">Integration Status</div>
                <div style="font-size:20px;font-weight:700;color:${intg?.status === 'approved' ? '#10b981' : intg?.status === 'pending' ? '#f59e0b' : '#ef4444'}">${(intg?.status || 'none').replace('_',' ').toUpperCase()}</div>
            </div>
            <div class="card" style="padding:18px 20px">
                <div style="font-size:11.5px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">Your API Switch</div>
                <div style="font-size:20px;font-weight:700;color:${apiEnabled ? '#10b981' : '#ef4444'}">${apiEnabled ? 'ENABLED' : 'DISABLED'}</div>
                ${isApproved ? `<button onclick="toggleApiStatus(this)" data-current="${saleAccess.api_status || 'disabled'}" style="margin-top:12px;width:100%;padding:8px;border:1.5px solid ${apiEnabled ? '#ef4444' : '#10b981'};background:${apiEnabled ? 'rgba(239,68,68,.08)' : 'rgba(16,185,129,.08)'};color:${apiEnabled ? '#ef4444' : '#10b981'};border-radius:8px;font-size:12.5px;font-weight:600;cursor:pointer">${apiEnabled ? 'Disable API' : 'Enable API'}</button>` : ''}
            </div>
            <div class="card" style="padding:18px 20px">
                <div style="font-size:11.5px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">Admin Status</div>
                <div style="font-size:20px;font-weight:700;color:${saleAccess.admin_status === 'enabled' ? '#10b981' : '#ef4444'}">${(saleAccess.admin_status || 'disabled').toUpperCase()}</div>
            </div>
        </div>
        ${banner}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Integration Details</h3></div>
                <div style="padding:20px 24px;display:flex;flex-direction:column;gap:14px">
                    <div><div style="font-size:11.5px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">Server IP (Whitelist This)</div><div class="code-box" style="display:flex;align-items:center;justify-content:space-between"><span id="server-ip">${data.server_ip || window.location.hostname}</span><button onclick="copyText('server-ip')" style="background:none;border:none;cursor:pointer;color:#10b981;font-size:12px;font-weight:600">Copy</button></div></div>
                    <div><div style="font-size:11.5px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">Base API URL</div><div class="code-box" style="display:flex;align-items:center;justify-content:space-between"><span id="base-url">${window.location.origin}/api/v1/buyer</span><button onclick="copyText('base-url')" style="background:none;border:none;cursor:pointer;color:#10b981;font-size:12px;font-weight:600">Copy</button></div></div>
                    <div><div style="font-size:11.5px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">Website URL</div><div class="code-box">${intg?.website_url || '-'}</div></div>
                    <div><div style="font-size:11.5px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">Callback URL</div><div class="code-box">${intg?.callback_url || '-'}</div></div>
                    <div><div style="font-size:11.5px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">Status Check URL</div><div class="code-box">${intg?.status_check_url || '-'}</div></div>
                    <div><div style="font-size:11.5px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">Dispute URL</div><div class="code-box">${intg?.dispute_url || '-'}</div></div>
                    <div><div style="font-size:11.5px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">Allowed Seller IPs</div><div class="code-box" style="white-space:pre-line">${intg?.allowed_ips || '-'}</div></div>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h3 class="card-title">API Credentials</h3></div>
                <div style="padding:20px 24px;display:flex;flex-direction:column;gap:14px">
                    <div>
                        <div style="font-size:11.5px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">API Key</div>
                        ${hasApiKey
                            ? `<div class="code-box" style="display:flex;align-items:center;justify-content:space-between"><span style="font-family:monospace;letter-spacing:1px">${apiKeyObj.prefix}****************</span><span style="font-size:11px;color:#94a3b8">Partial</span></div><div style="font-size:11.5px;color:#10b981;margin-top:5px">Active since ${fmtDate(apiKeyObj.created_at)}</div>`
                            : isApproved
                                ? `<div style="background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:10px;padding:14px;text-align:center"><div style="font-size:13px;color:#047857;margin-bottom:12px;font-weight:500">No API key yet. Generate one to start using the API.</div><button onclick="generateApiKey(this)" id="gen-key-btn" style="background:linear-gradient(135deg,#10b981,#0d9488);color:#fff;border:none;padding:10px 22px;border-radius:8px;font-size:13.5px;font-weight:700;cursor:pointer"><span id="gen-key-text">Generate API Key</span></button></div>`
                                : `<div style="background:#f1f5f9;border-radius:8px;padding:12px;font-size:13px;color:#64748b;text-align:center">Pending integration approval</div>`
                        }
                    </div>
                    <div><div style="font-size:11.5px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">Authentication Header</div><div class="code-box">X-API-Key: your_api_key_here</div></div>
                    <a href="${data.api_docs_url || '/seller/api-docs'}" style="display:inline-flex;align-items:center;justify-content:center;width:100%;padding:10px 14px;border-radius:9px;background:#eff6ff;color:#1d4ed8;font-size:13px;font-weight:700;text-decoration:none">Open Seller API Docs</a>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h3 class="card-title">Standard Endpoint Summary</h3></div>
            <div style="padding:20px 24px">
                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px">
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px"><div style="font-size:11.5px;font-weight:700;color:#374151;margin-bottom:4px">GET / POST Recharge API</div><div style="font-size:12px;color:#64748b">Submit recharge to platform</div></div>
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px"><div style="font-size:11.5px;font-weight:700;color:#374151;margin-bottom:4px">GET Callback URL</div><div style="font-size:12px;color:#64748b">Seller-side callback format</div></div>
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px"><div style="font-size:11.5px;font-weight:700;color:#374151;margin-bottom:4px">GET Status Check URL</div><div style="font-size:12px;color:#64748b">Seller-side status verification</div></div>
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px"><div style="font-size:11.5px;font-weight:700;color:#374151;margin-bottom:4px">GET Dispute URL</div><div style="font-size:12px;color:#64748b">Seller-side dispute format</div></div>
                </div>
            </div>
        </div>`;

    document.getElementById('config-wrap').innerHTML = html;
}

async function toggleApiStatus(btn){
    const current = btn.dataset.current;
    const label = current === 'enabled' ? 'Disable' : 'Enable';
    if (!confirm(`${label} your API?`)) return;
    btn.disabled = true;
    btn.textContent = 'Updating...';
    try {
        await apiFetch('/api/v1/seller/api-config/toggle-api', { method:'PATCH' });
        loadConfig();
    } catch (err) {
        alert(err.message || 'Failed to toggle API status.');
    }
}

async function generateApiKey(btn){
    if (!confirm('Generate a new API key? Store it safely because the full key is shown only once.')) return;
    btn.disabled = true;
    const textEl = document.getElementById('gen-key-text');
    if (textEl) textEl.textContent = 'Generating...';
    try {
        const res = await apiFetch('/api/v1/auth/api-key', {
            method: 'POST',
            body: JSON.stringify({ name: 'Seller API Key', scopes: ['recharge:write','recharge:read','wallet:read'] }),
        });
        document.getElementById('key-value').textContent = res.api_key || res.key || '';
        document.getElementById('key-modal').classList.add('show');
        loadConfig();
    } catch (err) {
        alert(err.message || 'Failed to generate API key.');
        btn.disabled = false;
        if (textEl) textEl.textContent = 'Generate API Key';
    }
}

document.getElementById('intg-form').addEventListener('submit', async e => {
    e.preventDefault();
    const body = {
        website_url: document.getElementById('intg-website').value.trim(),
        callback_url: document.getElementById('intg-callback').value.trim(),
        status_check_url: document.getElementById('intg-status-check').value.trim(),
        dispute_url: document.getElementById('intg-dispute').value.trim(),
        allowed_ips: document.getElementById('intg-ips').value.trim(),
        site_username: document.getElementById('intg-username').value.trim(),
        site_password_hint: document.getElementById('intg-password-hint').value.trim(),
    };

    document.getElementById('intg-btn').disabled = true;
    document.getElementById('intg-spinner').style.display = 'block';
    document.getElementById('intg-btn-text').textContent = 'Submitting...';
    document.getElementById('intg-error').style.display = 'none';

    try {
        await apiFetch('/api/v1/seller/api-config/integration', { method:'POST', body:JSON.stringify(body) });
        closeIntgModal();
        loadConfig();
    } catch (err) {
        document.getElementById('intg-error-msg').textContent = err.message || 'Submission failed.';
        document.getElementById('intg-error').style.display = 'flex';
    } finally {
        document.getElementById('intg-btn').disabled = false;
        document.getElementById('intg-spinner').style.display = 'none';
        document.getElementById('intg-btn-text').textContent = 'Submit Request';
    }
});

document.getElementById('intg-modal').addEventListener('click', e => { if (e.target === e.currentTarget) closeIntgModal(); });
document.getElementById('key-modal').addEventListener('click', e => { if (e.target === e.currentTarget) closeKeyModal(); });

function loadConfig(){
    document.getElementById('config-wrap').innerHTML = '<div style="text-align:center;padding:40px;color:#6b7280">Loading configuration...</div>';
    apiFetch('/api/v1/seller/api-config').then(renderConfig).catch(() => {
        document.getElementById('config-wrap').innerHTML = '<div style="text-align:center;padding:40px;color:#ef4444">Failed to load configuration.</div>';
    });
}

document.addEventListener('DOMContentLoaded', function(){ loadConfig(); });
</script>
@endsection
