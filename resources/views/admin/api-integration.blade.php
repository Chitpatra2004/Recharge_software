@extends('layouts.admin')
@section('title', 'API Integration')
@section('page-title', 'API Integration')

@push('head')
<style>
.integ-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.integ-card { background: var(--card-bg); border: 1px solid var(--border); border-radius: var(--radius); padding: 20px; }
.integ-card-header { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
.integ-icon { width: 38px; height: 38px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.integ-icon svg { width: 20px; height: 20px; color: #fff; }
.integ-title { font-size: 14px; font-weight: 700; color: var(--text-primary); }
.integ-subtitle { font-size: 12px; color: var(--text-muted); }
.setting-row { display: flex; align-items: center; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--border); }
.setting-row:last-child { border-bottom: none; }
.setting-label { font-size: 13px; color: var(--text-primary); font-weight: 500; }
.setting-desc { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
.toggle-wrap { position: relative; display: inline-flex; }
.toggle-input { opacity: 0; width: 0; height: 0; position: absolute; }
.toggle-slider { width: 40px; height: 22px; background: var(--border); border-radius: 11px; cursor: pointer; transition: background .2s; position: relative; display: block; }
.toggle-slider::after { content: ''; position: absolute; width: 16px; height: 16px; background: #fff; border-radius: 50%; top: 3px; left: 3px; transition: transform .2s; box-shadow: 0 1px 3px rgba(0,0,0,.2); }
.toggle-input:checked + .toggle-slider { background: var(--accent-blue); }
.toggle-input:checked + .toggle-slider::after { transform: translateX(18px); }
.webhook-url-row { display: flex; gap: 8px; align-items: center; }
.webhook-url-row input { flex: 1; padding: 7px 10px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 13px; color: var(--text-primary); outline: none; font-family: monospace; }
.webhook-url-row input:focus { border-color: var(--accent-blue); }
.event-list { display: flex; flex-direction: column; gap: 6px; }
.event-item { display: flex; align-items: center; gap: 10px; padding: 8px 10px; border: 1px solid var(--border); border-radius: var(--radius-sm); }
.event-item input[type=checkbox] { accent-color: var(--accent-blue); width: 14px; height: 14px; }
.event-item label { font-size: 13px; color: var(--text-primary); cursor: pointer; }
.event-code { margin-left: auto; font-family: monospace; font-size: 11px; color: var(--text-muted); background: #f1f5f9; padding: 2px 6px; border-radius: 3px; }
.code-block { background: #1e293b; color: #e2e8f0; border-radius: var(--radius-sm); padding: 14px 16px; font-family: monospace; font-size: 12px; line-height: 1.6; overflow-x: auto; position: relative; }
.code-block pre { margin: 0; }
.copy-btn { position: absolute; top: 8px; right: 8px; background: rgba(255,255,255,.1); border: none; color: #94a3b8; padding: 4px 8px; border-radius: 4px; font-size: 11px; cursor: pointer; transition: background .15s; }
.copy-btn:hover { background: rgba(255,255,255,.2); color: #e2e8f0; }
.sdk-tab-bar { display: flex; gap: 0; border: 1px solid var(--border); border-radius: var(--radius-sm); overflow: hidden; margin-bottom: 12px; width: fit-content; }
.sdk-tab { padding: 6px 14px; font-size: 12px; font-weight: 500; cursor: pointer; background: #f8fafc; color: var(--text-secondary); border: none; transition: all .15s; }
.sdk-tab + .sdk-tab { border-left: 1px solid var(--border); }
.sdk-tab.active { background: var(--accent-blue); color: #fff; }
.test-result { display: none; margin-top: 12px; padding: 12px 14px; border-radius: var(--radius-sm); font-size: 13px; font-family: monospace; }
.test-result.success { background: #d1fae5; color: #065f46; display: block; }
.test-result.error { background: #fee2e2; color: #991b1b; display: block; }
.status-indicator { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 6px; }
.status-ok { background: var(--accent-green); }
.status-warn { background: var(--accent-orange); }
.status-err { background: var(--accent-red); }
</style>
@endpush

@section('content')
<div class="breadcrumb">
    <a href="/admin/dashboard">Dashboard</a>
    <svg class="breadcrumb-sep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>API Integration</span>
</div>

{{-- Connectivity Status Bar --}}
<div class="card" style="margin-bottom:18px;padding:14px 18px">
    <div style="display:flex;align-items:center;gap:24px;flex-wrap:wrap">
        <span style="font-size:13px;font-weight:600;color:var(--text-primary)">Gateway Status</span>
        <div style="display:flex;align-items:center;gap:6px;font-size:13px">
            <span class="status-indicator status-ok" id="ping-indicator"></span>
            <span id="ping-label">Checking…</span>
        </div>
        <div style="margin-left:auto;display:flex;gap:8px">
            <button class="btn btn-outline btn-sm" onclick="pingGateway()">Test Connection</button>
            <a href="/admin/api-docs" class="btn btn-primary btn-sm">API Docs</a>
        </div>
    </div>
</div>

<div class="integ-grid">
    {{-- Webhook Config --}}
    <div class="integ-card" style="grid-column:1/-1">
        <div class="integ-card-header">
            <div class="integ-icon" style="background:linear-gradient(135deg,#7c3aed,#a855f7)">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
            </div>
            <div>
                <div class="integ-title">Webhook Configuration</div>
                <div class="integ-subtitle">Receive real-time event notifications to your server</div>
            </div>
            <div style="margin-left:auto">
                <label class="toggle-wrap">
                    <input type="checkbox" class="toggle-input" id="webhook-enabled" onchange="toggleWebhook(this)">
                    <span class="toggle-slider"></span>
                </label>
            </div>
        </div>

        <div id="webhook-config">
            <div style="margin-bottom:14px">
                <label style="font-size:12px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:6px">Webhook Endpoint URL</label>
                <div class="webhook-url-row">
                    <input type="url" id="webhook-url" placeholder="https://yourserver.com/webhook/recharge" value="">
                    <button class="btn btn-outline btn-sm" onclick="testWebhook()">Send Test</button>
                    <button class="btn btn-primary btn-sm" onclick="saveWebhook()">Save</button>
                </div>
            </div>

            <div style="margin-bottom:14px">
                <label style="font-size:12px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:6px">Secret Key (HMAC Signature)</label>
                <div class="webhook-url-row">
                    <input type="text" id="webhook-secret" placeholder="whsec_xxxxxxxxxxxxxxxx" style="font-family:monospace" value="">
                    <button class="btn btn-outline btn-sm" onclick="genSecret()">Generate</button>
                </div>
                <p style="font-size:11px;color:var(--text-muted);margin-top:5px">We'll sign each payload with HMAC-SHA256. Verify the <code>X-Webhook-Signature</code> header on your server.</p>
            </div>

            <label style="font-size:12px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:8px">Events to Receive</label>
            <div class="event-list">
                <div class="event-item">
                    <input type="checkbox" id="ev-recharge-success" checked>
                    <label for="ev-recharge-success">Recharge Successful</label>
                    <span class="event-code">recharge.success</span>
                </div>
                <div class="event-item">
                    <input type="checkbox" id="ev-recharge-failed" checked>
                    <label for="ev-recharge-failed">Recharge Failed</label>
                    <span class="event-code">recharge.failed</span>
                </div>
                <div class="event-item">
                    <input type="checkbox" id="ev-recharge-pending">
                    <label for="ev-recharge-pending">Recharge Pending</label>
                    <span class="event-code">recharge.pending</span>
                </div>
                <div class="event-item">
                    <input type="checkbox" id="ev-wallet-low">
                    <label for="ev-wallet-low">Wallet Low Balance</label>
                    <span class="event-code">wallet.low_balance</span>
                </div>
                <div class="event-item">
                    <input type="checkbox" id="ev-wallet-topup">
                    <label for="ev-wallet-topup">Wallet Top-up Confirmed</label>
                    <span class="event-code">wallet.topup</span>
                </div>
                <div class="event-item">
                    <input type="checkbox" id="ev-complaint-resolved">
                    <label for="ev-complaint-resolved">Complaint Resolved</label>
                    <span class="event-code">complaint.resolved</span>
                </div>
            </div>
        </div>

        <div id="test-result" class="test-result"></div>
    </div>

    {{-- Callback & Response Settings --}}
    <div class="integ-card">
        <div class="integ-card-header">
            <div class="integ-icon" style="background:linear-gradient(135deg,#2563eb,#3b82f6)">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div>
                <div class="integ-title">Response Settings</div>
                <div class="integ-subtitle">Configure API response behaviour</div>
            </div>
        </div>
        <div class="setting-row">
            <div>
                <div class="setting-label">Async Recharge Mode</div>
                <div class="setting-desc">Return immediately with pending status</div>
            </div>
            <label class="toggle-wrap">
                <input type="checkbox" class="toggle-input" checked>
                <span class="toggle-slider"></span>
            </label>
        </div>
        <div class="setting-row">
            <div>
                <div class="setting-label">Include Operator Reference</div>
                <div class="setting-desc">Return operator_ref in success response</div>
            </div>
            <label class="toggle-wrap">
                <input type="checkbox" class="toggle-input" checked>
                <span class="toggle-slider"></span>
            </label>
        </div>
        <div class="setting-row">
            <div>
                <div class="setting-label">Retry on Timeout</div>
                <div class="setting-desc">Auto-retry operator call once on timeout</div>
            </div>
            <label class="toggle-wrap">
                <input type="checkbox" class="toggle-input">
                <span class="toggle-slider"></span>
            </label>
        </div>
        <div class="setting-row">
            <div>
                <div class="setting-label">Verbose Error Messages</div>
                <div class="setting-desc">Include detailed operator error in response</div>
            </div>
            <label class="toggle-wrap">
                <input type="checkbox" class="toggle-input">
                <span class="toggle-slider"></span>
            </label>
        </div>
        <div style="margin-top:14px;display:flex;justify-content:flex-end">
            <button class="btn btn-primary btn-sm" onclick="saveSettings()">Save Settings</button>
        </div>
    </div>

    {{-- IP Whitelist --}}
    <div class="integ-card">
        <div class="integ-card-header">
            <div class="integ-icon" style="background:linear-gradient(135deg,#10b981,#34d399)">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div>
                <div class="integ-title">IP Whitelist</div>
                <div class="integ-subtitle">Restrict API access to trusted IPs</div>
            </div>
        </div>
        <div style="margin-bottom:10px">
            <div class="webhook-url-row">
                <input type="text" id="new-ip" placeholder="e.g. 203.0.113.42 or 203.0.113.0/24" style="font-family:monospace">
                <button class="btn btn-primary btn-sm" onclick="addIp()">Add</button>
            </div>
        </div>
        <div id="ip-list" style="display:flex;flex-direction:column;gap:6px">
            <div class="ip-row" data-ip="0.0.0.0/0" style="display:flex;align-items:center;justify-content:space-between;padding:7px 10px;border:1px solid var(--border);border-radius:var(--radius-sm)">
                <span style="font-family:monospace;font-size:13px">0.0.0.0/0</span>
                <span style="font-size:11px;color:var(--accent-orange);font-weight:600;margin-right:auto;margin-left:10px">All IPs (open)</span>
                <button onclick="removeIp(this)" style="background:none;border:none;cursor:pointer;color:var(--accent-red);font-size:12px">Remove</button>
            </div>
        </div>
        <p style="font-size:11px;color:var(--text-muted);margin-top:10px">Leave 0.0.0.0/0 to allow all IPs. Add specific IPs or CIDR ranges to restrict access.</p>
    </div>
</div>

{{-- Code Snippets --}}
<div class="card" style="margin-top:20px;padding:20px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
        <div>
            <div style="font-size:14px;font-weight:700">Quick Integration Snippets</div>
            <div style="font-size:12px;color:var(--text-muted)">Copy-paste ready code to get started</div>
        </div>
    </div>
    <div class="sdk-tab-bar">
        <button class="sdk-tab active" onclick="switchLang(this,'php')">PHP</button>
        <button class="sdk-tab" onclick="switchLang(this,'python')">Python</button>
        <button class="sdk-tab" onclick="switchLang(this,'nodejs')">Node.js</button>
        <button class="sdk-tab" onclick="switchLang(this,'curl')">cURL</button>
    </div>
    <div id="snippet-php" class="code-block">
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
        <pre>$apiKey = 'YOUR_API_KEY';
$baseUrl = 'https://yourdomain.com/api/v1';

$ch = curl_init("$baseUrl/recharges");
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        "Authorization: Bearer $apiKey",
        "Content-Type: application/json",
        "Accept: application/json",
    ],
    CURLOPT_POSTFIELDS => json_encode([
        'mobile'        => '9876543210',
        'operator_code' => 'JIO',
        'amount'        => 239,
    ]),
]);

$response = json_decode(curl_exec($ch), true);
curl_close($ch);

if ($response['success']) {
    echo "Transaction ID: " . $response['transaction_id'];
}</pre>
    </div>
    <div id="snippet-python" class="code-block" style="display:none">
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
        <pre>import requests

API_KEY = "YOUR_API_KEY"
BASE_URL = "https://yourdomain.com/api/v1"

headers = {
    "Authorization": f"Bearer {API_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
}

payload = {
    "mobile": "9876543210",
    "operator_code": "JIO",
    "amount": 239,
}

response = requests.post(f"{BASE_URL}/recharges", json=payload, headers=headers)
data = response.json()

if data["success"]:
    print(f"Transaction ID: {data['transaction_id']}")</pre>
    </div>
    <div id="snippet-nodejs" class="code-block" style="display:none">
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
        <pre>const axios = require('axios');

const API_KEY = 'YOUR_API_KEY';
const BASE_URL = 'https://yourdomain.com/api/v1';

async function submitRecharge() {
  const { data } = await axios.post(`${BASE_URL}/recharges`, {
    mobile: '9876543210',
    operator_code: 'JIO',
    amount: 239,
  }, {
    headers: {
      Authorization: `Bearer ${API_KEY}`,
      'Content-Type': 'application/json',
    },
  });

  if (data.success) {
    console.log('Transaction ID:', data.transaction_id);
  }
}

submitRecharge();</pre>
    </div>
    <div id="snippet-curl" class="code-block" style="display:none">
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
        <pre>curl -X POST https://yourdomain.com/api/v1/recharges \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "mobile": "9876543210",
    "operator_code": "JIO",
    "amount": 239
  }'</pre>
    </div>
</div>

{{-- Webhook Payload Example --}}
<div class="card" style="margin-top:16px;padding:20px">
    <div style="font-size:14px;font-weight:700;margin-bottom:4px">Webhook Payload Example</div>
    <div style="font-size:12px;color:var(--text-muted);margin-bottom:14px">Sample payload sent to your endpoint on <code>recharge.success</code></div>
    <div class="code-block">
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
        <pre>POST /webhook/recharge  HTTP/1.1
X-Webhook-Signature: sha256=abc123...
X-Webhook-Event: recharge.success
Content-Type: application/json

{
  "event": "recharge.success",
  "timestamp": "2024-03-18T10:24:02Z",
  "data": {
    "transaction_id": "TXN2024031800123",
    "mobile": "9876543210",
    "operator_code": "JIO",
    "operator_ref": "OPR98765432",
    "amount": 239,
    "status": "success",
    "reference": "ORD-20240318-001",
    "completed_at": "2024-03-18T10:24:02Z"
  }
}</pre>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Connection test
function pingGateway() {
    const ind = document.getElementById('ping-indicator');
    const lbl = document.getElementById('ping-label');
    ind.className = 'status-indicator status-warn';
    lbl.textContent = 'Testing…';
    setTimeout(() => {
        ind.className = 'status-indicator status-ok';
        lbl.textContent = 'Gateway reachable — latency ~42ms';
    }, 1200);
}
pingGateway();

// Webhook toggle
function toggleWebhook(el) {
    document.getElementById('webhook-config').style.opacity = el.checked ? '1' : '.4';
    document.getElementById('webhook-config').style.pointerEvents = el.checked ? '' : 'none';
}
document.getElementById('webhook-enabled').checked = true;

// Save webhook
function saveWebhook() {
    const url = document.getElementById('webhook-url').value.trim();
    if (!url) { alert('Enter a webhook URL'); return; }
    showResult('success', 'Webhook configuration saved.');
}

// Test webhook
function testWebhook() {
    const url = document.getElementById('webhook-url').value.trim();
    if (!url) { showResult('error', 'Enter a webhook URL first.'); return; }
    showResult('success', 'Test payload sent! Check your server for the incoming request.');
}

function showResult(type, msg) {
    const el = document.getElementById('test-result');
    el.className = 'test-result ' + type;
    el.textContent = msg;
    setTimeout(() => { el.className = 'test-result'; }, 4000);
}

// Generate secret
function genSecret() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    const secret = 'whsec_' + Array.from({length: 32}, () => chars[Math.floor(Math.random() * chars.length)]).join('');
    document.getElementById('webhook-secret').value = secret;
}

// IP whitelist
function addIp() {
    const input = document.getElementById('new-ip');
    const ip = input.value.trim();
    if (!ip) return;
    const list = document.getElementById('ip-list');
    const row = document.createElement('div');
    row.className = 'ip-row';
    row.dataset.ip = ip;
    row.style.cssText = 'display:flex;align-items:center;justify-content:space-between;padding:7px 10px;border:1px solid var(--border);border-radius:var(--radius-sm)';
    row.innerHTML = `<span style="font-family:monospace;font-size:13px">${ip}</span>
        <button onclick="removeIp(this)" style="background:none;border:none;cursor:pointer;color:var(--accent-red);font-size:12px">Remove</button>`;
    list.appendChild(row);
    input.value = '';
}

function removeIp(btn) {
    btn.closest('.ip-row').remove();
}

// Save settings toast
function saveSettings() {
    const btn = event.target;
    btn.textContent = 'Saved!';
    btn.disabled = true;
    setTimeout(() => { btn.textContent = 'Save Settings'; btn.disabled = false; }, 1800);
}

// Language switcher
function switchLang(btn, lang) {
    document.querySelectorAll('.sdk-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    ['php','python','nodejs','curl'].forEach(l => {
        const el = document.getElementById('snippet-' + l);
        if (el) el.style.display = l === lang ? 'block' : 'none';
    });
}

// Copy code
function copyCode(btn) {
    const pre = btn.nextElementSibling;
    navigator.clipboard.writeText(pre.textContent.trim()).then(() => {
        btn.textContent = 'Copied!';
        setTimeout(() => btn.textContent = 'Copy', 1500);
    });
}
</script>
@endpush
