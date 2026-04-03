@extends('layouts.seller')
@section('title','API Configuration')
@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">API Configuration</h1>
        <p class="page-sub">Manage your API integration and credentials</p>
    </div>
</div>

<div id="config-wrap">
    <div style="text-align:center;padding:40px;color:#6b7280">Loading configuration…</div>
</div>

<!-- Integration Request Modal -->
<div class="modal-overlay" id="intg-modal">
    <div class="modal-box" style="max-width:540px">
        <button class="modal-close" onclick="closeIntgModal()">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <h3 style="font-size:18px;font-weight:700;color:#1e293b;margin-bottom:6px">Submit Integration Request</h3>
        <p style="font-size:13px;color:#64748b;margin-bottom:20px">Provide your website details to request API access. Admin will review within 1–2 business days.</p>
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
                <small style="font-size:11.5px;color:#94a3b8;margin-top:5px;display:block">We'll POST recharge status updates to this URL</small>
            </div>
            <div class="form-group">
                <label class="form-label">Your Site Username <span style="color:#ef4444">*</span></label>
                <input type="text" id="intg-username" class="form-control" placeholder="Username we should use on your site" required>
            </div>
            <div class="form-group">
                <label class="form-label">Password Hint <span style="color:#94a3b8;font-weight:400">(optional)</span></label>
                <input type="text" id="intg-password-hint" class="form-control" placeholder="A hint to your site password (not stored as-is)">
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

<script>
let configData = null;

function closeIntgModal(){ document.getElementById('intg-modal').classList.remove('show'); }

function openIntgModal(){
    document.getElementById('intg-error').style.display='none';
    document.getElementById('intg-form').reset();
    document.getElementById('intg-modal').classList.add('show');
}

function renderConfig(raw){
    // API returns {data: {server_ip, api_key: {prefix,...}, integration: {...}}}
    const data = raw.data || raw;
    configData = data;
    const intg      = data.integration;
    const apiKeyObj = data.api_key;
    const hasApiKey = !!apiKeyObj?.prefix;
    let html = '';

    // Integration status banner
    if(!intg || intg.status==='none'){
        html += `<div class="alert-warning" style="background:#fffbeb;border:1.5px solid #fde68a;border-radius:12px;padding:16px 20px;display:flex;align-items:flex-start;gap:12px;margin-bottom:20px">
            <svg fill="none" viewBox="0 0 24 24" stroke="#f59e0b" stroke-width="2" style="width:20px;height:20px;flex-shrink:0;margin-top:1px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div style="flex:1">
                <div style="font-size:14px;font-weight:600;color:#92400e;margin-bottom:4px">Integration Not Configured</div>
                <div style="font-size:13px;color:#a16207">Submit an integration request to receive your API credentials and start processing recharges.</div>
            </div>
            <button onclick="openIntgModal()" style="background:#f59e0b;color:#fff;border:none;padding:9px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;white-space:nowrap">Submit Request</button>
        </div>`;
    } else if(intg.status==='pending'){
        html += `<div style="background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:12px;padding:16px 20px;display:flex;align-items:center;gap:12px;margin-bottom:20px">
            <svg fill="none" viewBox="0 0 24 24" stroke="#3b82f6" stroke-width="2" style="width:20px;height:20px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div><div style="font-size:14px;font-weight:600;color:#1d4ed8">Integration Request Under Review</div><div style="font-size:13px;color:#2563eb;margin-top:2px">Submitted on ${fmtDate(intg.created_at)}. Admin will review within 1–2 business days.</div></div>
        </div>`;
    } else if(intg.status==='rejected'){
        html += `<div style="background:#fff1f2;border:1.5px solid #fecdd3;border-radius:12px;padding:16px 20px;margin-bottom:20px">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px">
                <svg fill="none" viewBox="0 0 24 24" stroke="#ef4444" stroke-width="2" style="width:20px;height:20px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div style="font-size:14px;font-weight:600;color:#be123c">Integration Request Rejected</div>
            </div>
            ${intg.admin_notes?`<div style="font-size:13px;color:#9f1239;margin-bottom:12px">Admin note: ${intg.admin_notes}</div>`:''}
            <button onclick="openIntgModal()" style="background:#ef4444;color:#fff;border:none;padding:9px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer">Re-submit Request</button>
        </div>`;
    }

    // Server Details
    html += `<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Server Details</h3></div>
            <div style="padding:20px 24px;display:flex;flex-direction:column;gap:14px">
                <div>
                    <div style="font-size:11.5px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">Server IP (Whitelist This)</div>
                    <div class="code-box" style="display:flex;align-items:center;justify-content:space-between">
                        <span id="server-ip">${data.server_ip||window.location.hostname}</span>
                        <button onclick="copyText('server-ip')" style="background:none;border:none;cursor:pointer;color:#10b981;font-size:12px;font-weight:600">Copy</button>
                    </div>
                </div>
                <div>
                    <div style="font-size:11.5px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">Base API URL</div>
                    <div class="code-box" style="display:flex;align-items:center;justify-content:space-between">
                        <span id="base-url">${window.location.origin}/api/v1/buyer</span>
                        <button onclick="copyText('base-url')" style="background:none;border:none;cursor:pointer;color:#10b981;font-size:12px;font-weight:600">Copy</button>
                    </div>
                </div>
                <div>
                    <div style="font-size:11.5px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">Your Callback URL</div>
                    <div class="code-box">${intg?.callback_url||'—'}</div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h3 class="card-title">API Credentials</h3></div>
            <div style="padding:20px 24px;display:flex;flex-direction:column;gap:14px">
                <div>
                    <div style="font-size:11.5px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">API Key</div>
                    ${hasApiKey
                        ? `<div class="code-box" style="display:flex;align-items:center;justify-content:space-between">
                                <span style="font-family:monospace;letter-spacing:1px">${apiKeyObj.prefix}••••••••••••••••</span>
                                <span style="font-size:11px;color:#94a3b8">Partial</span>
                           </div>
                           <div style="font-size:11.5px;color:#10b981;margin-top:5px">✓ API key active since ${fmtDate(apiKeyObj.created_at)}. Full key was shown once at creation.</div>`
                        : `<div style="background:#f1f5f9;border-radius:8px;padding:12px;font-size:13px;color:#64748b;text-align:center">${intg?.status==='approved'?'API key not generated yet. Contact admin.':'Pending integration approval'}</div>`
                    }
                </div>
                <div>
                    <div style="font-size:11.5px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">Authentication Header</div>
                    <div class="code-box">X-API-Key: your_api_key_here</div>
                </div>
            </div>
        </div>
    </div>`;

    // API Reference
    html += `<div class="card">
        <div class="card-header"><h3 class="card-title">API Reference — Recharge Endpoint</h3></div>
        <div style="padding:20px 24px">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
                <div>
                    <div style="font-size:12px;font-weight:700;color:#10b981;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px">POST /api/v1/buyer/recharge</div>
                    <div style="font-size:13px;color:#64748b;margin-bottom:12px">Initiate a recharge transaction</div>
                    <div style="font-size:12px;font-weight:600;color:#374151;margin-bottom:6px">Request Body (JSON)</div>
                    <div class="code-box"><pre style="margin:0;font-size:12px;line-height:1.7">{
  "mobile":   "9876543210",
  "operator": "AIRTEL",
  "circle":   "Delhi",
  "amount":   199,
  "txn_id":   "YOUR_UNIQUE_TXN_ID"
}</pre></div>
                </div>
                <div>
                    <div style="font-size:12px;font-weight:700;color:#3b82f6;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px">Success Response</div>
                    <div class="code-box"><pre style="margin:0;font-size:12px;line-height:1.7">{
  "success": true,
  "txn_id":  "YOUR_UNIQUE_TXN_ID",
  "status":  "processing",
  "message": "Recharge initiated"
}</pre></div>
                    <div style="font-size:12px;font-weight:700;color:#f59e0b;text-transform:uppercase;letter-spacing:.5px;margin:12px 0 6px">Callback Payload (POST to your URL)</div>
                    <div class="code-box"><pre style="margin:0;font-size:12px;line-height:1.7">{
  "txn_id":    "YOUR_TXN_ID",
  "status":    "success",
  "operator_ref": "OP123456"
}</pre></div>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px">
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px">
                    <div style="font-size:11.5px;font-weight:700;color:#374151;margin-bottom:4px">GET /buyer/recharge/{txn_id}</div>
                    <div style="font-size:12px;color:#64748b">Check recharge status by transaction ID</div>
                </div>
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px">
                    <div style="font-size:11.5px;font-weight:700;color:#374151;margin-bottom:4px">GET /buyer/balance</div>
                    <div style="font-size:12px;color:#64748b">Check your wallet balance</div>
                </div>
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px">
                    <div style="font-size:11.5px;font-weight:700;color:#374151;margin-bottom:4px">GET /buyer/transactions</div>
                    <div style="font-size:12px;color:#64748b">Paginated transaction history</div>
                </div>
            </div>
        </div>
    </div>`;

    document.getElementById('config-wrap').innerHTML = html;
}

function copyText(id){
    const text = document.getElementById(id).textContent.trim();
    navigator.clipboard.writeText(text).then(()=>{
        const btn = document.querySelector(`#${id} + button`) || document.querySelector(`[onclick="copyText('${id}')"]`);
        if(btn){ const orig=btn.textContent; btn.textContent='Copied!'; setTimeout(()=>btn.textContent=orig,1500); }
    });
}

// Integration form submit
document.getElementById('intg-form').addEventListener('submit', async e=>{
    e.preventDefault();
    const body = {
        website_url:     document.getElementById('intg-website').value.trim(),
        callback_url:    document.getElementById('intg-callback').value.trim(),
        site_username:   document.getElementById('intg-username').value.trim(),
        site_password_hint: document.getElementById('intg-password-hint').value.trim(),
    };
    document.getElementById('intg-btn').disabled=true;
    document.getElementById('intg-spinner').style.display='block';
    document.getElementById('intg-btn-text').textContent='Submitting…';
    document.getElementById('intg-error').style.display='none';
    try{
        await apiFetch('/api/v1/seller/api-config/integration', {method:'POST', body:JSON.stringify(body)});
        closeIntgModal();
        loadConfig();
    }catch(err){
        document.getElementById('intg-error-msg').textContent = err.message||'Submission failed.';
        document.getElementById('intg-error').style.display='flex';
    }finally{
        document.getElementById('intg-btn').disabled=false;
        document.getElementById('intg-spinner').style.display='none';
        document.getElementById('intg-btn-text').textContent='Submit Request';
    }
});

document.getElementById('intg-modal').addEventListener('click',e=>{ if(e.target===e.currentTarget) closeIntgModal(); });

function loadConfig(){
    document.getElementById('config-wrap').innerHTML='<div style="text-align:center;padding:40px;color:#6b7280">Loading configuration…</div>';
    apiFetch('/api/v1/seller/api-config').then(renderConfig).catch(()=>{
        document.getElementById('config-wrap').innerHTML='<div style="text-align:center;padding:40px;color:#ef4444">Failed to load configuration.</div>';
    });
}

loadConfig();
</script>
@endsection
