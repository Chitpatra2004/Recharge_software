@extends('layouts.user')
@section('title','API Integration')
@section('page-title','API Integration')

@section('content')
<div class="breadcrumb">
    <a href="/user/dashboard">Dashboard</a>
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>API Integration</span>
</div>

{{-- Header banner --}}
<div style="background:linear-gradient(135deg,rgba(37,99,235,.2),rgba(99,102,241,.15));border:1px solid rgba(99,102,241,.25);border-radius:var(--radius);padding:22px 24px;margin-bottom:20px;display:flex;align-items:center;gap:16px">
    <div style="width:48px;height:48px;background:linear-gradient(135deg,var(--blue-dk),var(--indigo));border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:24px;height:24px;color:#fff"><path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
    </div>
    <div>
        <div style="font-size:16px;font-weight:700;color:var(--text)">RechargeHub Partner API</div>
        <div style="font-size:12px;color:var(--muted);margin-top:3px">Integrate recharge, bill payment, and status check into your own application using our REST API.</div>
    </div>
    <div style="margin-left:auto">
        <button onclick="generateKey()" class="btn btn-primary btn-sm">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
            Generate / Refresh API Key
        </button>
    </div>
</div>

{{-- API Key Card --}}
<div class="card" style="margin-bottom:18px">
    <div class="card-header"><span class="card-title">🔑 Your API Key</span></div>
    <div class="card-body">
        <div style="display:flex;align-items:center;gap:10px">
            <code id="api-key-display" style="flex:1;background:rgba(255,255,255,.05);border:1px solid var(--border2);border-radius:8px;padding:11px 14px;font-family:monospace;font-size:13px;color:var(--muted);word-break:break-all;display:block">
                Click "Generate / Refresh API Key" to get your key
            </code>
            <button onclick="copyKey()" id="copy-btn" title="Copy" style="background:var(--card2);border:1px solid var(--border2);border-radius:8px;padding:10px 14px;color:var(--muted);cursor:pointer;font-size:12px;font-weight:600;white-space:nowrap;font-family:inherit;flex-shrink:0">📋 Copy</button>
        </div>
        <div id="key-note" style="display:none;font-size:11px;color:#fbbf24;margin-top:8px;background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.2);border-radius:6px;padding:8px 12px">
            ⚠️ Copy this key now — it will not be shown again. Store it securely.
        </div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:16px">
            <div style="background:var(--card2);border:1px solid var(--border2);border-radius:8px;padding:12px">
                <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--muted2);margin-bottom:4px">Base URL</div>
                <code style="font-size:11px;color:var(--text)">http://localhost:8000/api/v1</code>
            </div>
            <div style="background:var(--card2);border:1px solid var(--border2);border-radius:8px;padding:12px">
                <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--muted2);margin-bottom:4px">Auth Header</div>
                <code style="font-size:11px;color:var(--text)">X-API-Key: &lt;your_api_key&gt;</code>
            </div>
            <div style="background:var(--card2);border:1px solid var(--border2);border-radius:8px;padding:12px">
                <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--muted2);margin-bottom:4px">Content-Type</div>
                <code style="font-size:11px;color:var(--text)">application/json</code>
            </div>
        </div>
    </div>
</div>

{{-- ── OPERATOR CODES ─────────────────────────────────────────────────────── --}}
<div class="card" style="margin-bottom:18px">
    <div class="card-header"><span class="card-title">📡 Operator Codes</span></div>
    <div class="card-body" style="padding:0">
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0">
            @foreach([
                ['Mobile Prepaid / Postpaid', [
                    ['AIRTEL','Airtel Mobile','prepaid, postpaid'],
                    ['JIO','Reliance Jio','prepaid, postpaid'],
                    ['VI','Vi (Vodafone Idea)','prepaid, postpaid'],
                    ['BSNL','BSNL Mobile','prepaid, postpaid'],
                    ['MTNL','MTNL Mumbai / Delhi','prepaid, postpaid'],
                ]],
                ['DTH / Satellite TV', [
                    ['TATAPLAY','Tata Play (formerly Tata Sky)','dth'],
                    ['DISHTV','Dish TV','dth'],
                    ['SUNDIRECT','Sun Direct','dth'],
                    ['VIDEOCOND2H','Videocon D2H','dth'],
                    ['AIRTELDIGITAL','Airtel Digital TV','dth'],
                ]],
                ['Broadband / Internet', [
                    ['AIRTEL_BB','Airtel Broadband','broadband'],
                    ['JIO_FIBER','Jio Fiber','broadband'],
                    ['BSNL_BB','BSNL Broadband','broadband'],
                    ['ACT','ACT Fibernet','broadband'],
                    ['HATHWAY','Hathway Broadband','broadband'],
                ]],
            ] as [$category, $operators])
            <div style="border-right:1px solid var(--border);padding:16px 18px">
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--muted2);margin-bottom:10px">{{ $category }}</div>
                @foreach($operators as [$code, $name, $types])
                <div style="display:flex;align-items:center;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--border)">
                    <div>
                        <code style="font-size:12px;font-weight:700;color:var(--blue);background:rgba(59,130,246,.1);padding:2px 7px;border-radius:5px">{{ $code }}</code>
                        <div style="font-size:11px;color:var(--muted);margin-top:3px">{{ $name }}</div>
                    </div>
                    <div style="font-size:10px;color:var(--muted2)">{{ $types }}</div>
                </div>
                @endforeach
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ── API ENDPOINTS ──────────────────────────────────────────────────────── --}}

{{-- 1. Recharge API --}}
<div class="card" style="margin-bottom:18px">
    <div class="card-header" style="justify-content:space-between">
        <span class="card-title">⚡ 1. Recharge / Bill Pay API</span>
        <span style="background:rgba(245,158,11,.15);color:#fbbf24;font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px">POST</span>
    </div>
    <div class="card-body">
        {{-- Endpoint --}}
        <div style="margin-bottom:16px">
            <div class="ep-label">Endpoint URL</div>
            <div class="ep-url">
                <span style="color:#fbbf24;font-weight:700">POST</span>
                <code>http://localhost:8000/api/v1/buyer/recharge</code>
                <button onclick="copyText('http://localhost:8000/api/v1/buyer/recharge')" class="copy-small">Copy</button>
            </div>
        </div>

        {{-- Parameters --}}
        <div style="margin-bottom:16px">
            <div class="ep-label">Request Parameters</div>
            <div class="table-wrap" style="border:1px solid var(--border2);border-radius:8px;overflow:hidden">
                <table>
                    <thead><tr><th>Parameter</th><th>Type</th><th>Required</th><th>Description</th><th>Example</th></tr></thead>
                    <tbody>
                        <tr><td><code class="param">mobile</code></td><td>string</td><td><span class="req">Required</span></td><td>Mobile number / subscriber ID / consumer number</td><td><code>9876543210</code></td></tr>
                        <tr><td><code class="param">operator_code</code></td><td>string</td><td><span class="req">Required</span></td><td>Operator code from the table above</td><td><code>AIRTEL</code></td></tr>
                        <tr><td><code class="param">amount</code></td><td>number</td><td><span class="req">Required</span></td><td>Recharge amount in ₹ (min: 10, max: 10000)</td><td><code>299</code></td></tr>
                        <tr><td><code class="param">recharge_type</code></td><td>string</td><td><span class="opt">Optional</span></td><td><code>prepaid</code> / <code>postpaid</code> / <code>dth</code> / <code>broadband</code> (default: prepaid)</td><td><code>prepaid</code></td></tr>
                        <tr><td><code class="param">circle</code></td><td>string</td><td><span class="opt">Optional</span></td><td>Telecom circle / state (e.g. Maharashtra, Delhi)</td><td><code>Maharashtra</code></td></tr>
                        <tr><td><code class="param">idempotency_key</code></td><td>string</td><td><span class="opt">Optional</span></td><td>Unique key to prevent duplicate transactions (max 128 chars)</td><td><code>ORDER_12345</code></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Headers --}}
        <div style="margin-bottom:16px">
            <div class="ep-label">Request Headers</div>
            <div class="table-wrap" style="border:1px solid var(--border2);border-radius:8px;overflow:hidden">
                <table>
                    <thead><tr><th>Header</th><th>Value</th></tr></thead>
                    <tbody>
                        <tr><td><code class="param">X-API-Key</code></td><td><code>your_api_key_here</code></td></tr>
                        <tr><td><code class="param">Content-Type</code></td><td><code>application/json</code></td></tr>
                        <tr><td><code class="param">Accept</code></td><td><code>application/json</code></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Sample Request --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
            <div>
                <div class="ep-label">Sample Request (JSON Body)</div>
                <div class="code-block" id="sample-recharge-req">{
  "mobile": "9876543210",
  "operator_code": "AIRTEL",
  "amount": 299,
  "recharge_type": "prepaid",
  "circle": "Maharashtra",
  "idempotency_key": "ORDER_20240101_001"
}</div>
                <button onclick="copyBlock('sample-recharge-req')" class="copy-code-btn">📋 Copy</button>
            </div>
            <div>
                <div class="ep-label">Sample cURL</div>
                <div class="code-block" id="sample-recharge-curl">curl -X POST \
  http://localhost:8000/api/v1/buyer/recharge \
  -H "X-API-Key: YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "mobile": "9876543210",
    "operator_code": "AIRTEL",
    "amount": 299,
    "recharge_type": "prepaid"
  }'</div>
                <button onclick="copyBlock('sample-recharge-curl')" class="copy-code-btn">📋 Copy</button>
            </div>
        </div>

        {{-- Success Response --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:14px">
            <div>
                <div class="ep-label">✅ Success Response (HTTP 202)</div>
                <div class="code-block">{
  "success": true,
  "data": {
    "txn_id": 1042,
    "status": "processing",
    "mobile": "9876543210",
    "operator": "AIRTEL",
    "amount": "299.00",
    "commission": "4.49",
    "net_amount": "294.51",
    "submitted_at": "2026-03-19T10:30:00+05:30"
  },
  "meta": {
    "message": "Recharge submitted and queued for processing."
  }
}</div>
            </div>
            <div>
                <div class="ep-label">❌ Error Response</div>
                <div class="code-block">{
  "success": false,
  "error": {
    "code": "INSUFFICIENT_BALANCE",
    "message": "Wallet balance is too low."
  }
}

// Other error codes:
// VALIDATION_ERROR     — Invalid parameters
// DUPLICATE_TRANSACTION — Same number within 60s
// OPERATOR_UNAVAILABLE — Operator is down
// WALLET_FROZEN        — Account restricted
// SERVER_ERROR         — Internal error</div>
            </div>
        </div>

        {{-- Transaction Statuses --}}
        <div style="margin-top:16px">
            <div class="ep-label">Transaction Status Values</div>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                @foreach([
                    ['queued','#94a3b8','Submitted, waiting to process'],
                    ['processing','#fbbf24','Sent to operator, awaiting response'],
                    ['success','#34d399','Recharge successful'],
                    ['failed','#f87171','Recharge failed'],
                    ['refunded','#60a5fa','Amount refunded to wallet'],
                    ['partial','#a78bfa','Partial recharge done'],
                ] as [$status,$color,$desc])
                <div style="background:var(--card2);border:1px solid var(--border2);border-radius:8px;padding:8px 12px;font-size:12px">
                    <code class="sc-{{ $status }}">{{ $status }}</code>
                    <div style="color:var(--muted);font-size:11px;margin-top:2px">{{ $desc }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- 2. Status Check --}}
<div class="card" style="margin-bottom:18px">
    <div class="card-header" style="justify-content:space-between">
        <span class="card-title">🔍 2. Transaction Status Check</span>
        <span style="background:rgba(16,185,129,.15);color:#34d399;font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px">GET</span>
    </div>
    <div class="card-body">
        <div style="margin-bottom:16px">
            <div class="ep-label">Endpoint URL</div>
            <div class="ep-url">
                <span style="color:#34d399;font-weight:700">GET</span>
                <code>http://localhost:8000/api/v1/buyer/recharge/{txn_id}</code>
                <button onclick="copyText('http://localhost:8000/api/v1/buyer/recharge/{txn_id}')" class="copy-small">Copy</button>
            </div>
            <div style="font-size:12px;color:var(--muted);margin-top:8px">Replace <code style="color:var(--orange)">{txn_id}</code> with the <code>txn_id</code> returned from the recharge API. Poll every 5–10 seconds until <code>is_terminal</code> is <code>true</code>.</div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
            <div>
                <div class="ep-label">Sample Request</div>
                <div class="code-block">curl -X GET \
  http://localhost:8000/api/v1/buyer/recharge/1042 \
  -H "X-API-Key: YOUR_API_KEY" \
  -H "Accept: application/json"</div>
            </div>
            <div>
                <div class="ep-label">Sample Response</div>
                <div class="code-block">{
  "success": true,
  "data": {
    "txn_id": 1042,
    "status": "success",
    "mobile": "9876543210",
    "operator": "AIRTEL",
    "type": "prepaid",
    "amount": "299.00",
    "commission": "4.49",
    "net_amount": "294.51",
    "operator_ref": "OP_REF_7842901",
    "failure_reason": null,
    "retry_count": 0,
    "submitted_at": "2026-03-19T10:30:00+05:30",
    "processed_at": "2026-03-19T10:30:08+05:30",
    "is_terminal": true
  }
}</div>
            </div>
        </div>
    </div>
</div>

{{-- 3. Balance Check --}}
<div class="card" style="margin-bottom:18px">
    <div class="card-header" style="justify-content:space-between">
        <span class="card-title">💰 3. Wallet Balance Check</span>
        <span style="background:rgba(16,185,129,.15);color:#34d399;font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px">GET</span>
    </div>
    <div class="card-body">
        <div style="margin-bottom:16px">
            <div class="ep-label">Endpoint URL</div>
            <div class="ep-url">
                <span style="color:#34d399;font-weight:700">GET</span>
                <code>http://localhost:8000/api/v1/buyer/balance</code>
                <button onclick="copyText('http://localhost:8000/api/v1/buyer/balance')" class="copy-small">Copy</button>
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
            <div>
                <div class="ep-label">Sample Request</div>
                <div class="code-block">curl -X GET \
  http://localhost:8000/api/v1/buyer/balance \
  -H "X-API-Key: YOUR_API_KEY" \
  -H "Accept: application/json"</div>
            </div>
            <div>
                <div class="ep-label">Sample Response</div>
                <div class="code-block">{
  "success": true,
  "data": {
    "balance": "5000.00",
    "available_balance": "4850.00",
    "reserved_balance": "150.00",
    "credit_limit": "0.00",
    "daily_limit": null,
    "daily_used": "750.00",
    "status": "active"
  },
  "meta": {
    "fetched_at": "2026-03-19T10:35:00+05:30"
  }
}</div>
            </div>
        </div>
    </div>
</div>

{{-- 4. Transaction History --}}
<div class="card" style="margin-bottom:18px">
    <div class="card-header" style="justify-content:space-between">
        <span class="card-title">📋 4. Transaction History</span>
        <span style="background:rgba(16,185,129,.15);color:#34d399;font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px">GET</span>
    </div>
    <div class="card-body">
        <div style="margin-bottom:14px">
            <div class="ep-label">Endpoint URL</div>
            <div class="ep-url">
                <span style="color:#34d399;font-weight:700">GET</span>
                <code>http://localhost:8000/api/v1/buyer/transactions</code>
                <button onclick="copyText('http://localhost:8000/api/v1/buyer/transactions')" class="copy-small">Copy</button>
            </div>
        </div>
        <div style="margin-bottom:14px">
            <div class="ep-label">Query Parameters (all optional)</div>
            <div class="table-wrap" style="border:1px solid var(--border2);border-radius:8px;overflow:hidden">
                <table>
                    <thead><tr><th>Parameter</th><th>Type</th><th>Description</th><th>Example</th></tr></thead>
                    <tbody>
                        <tr><td><code class="param">status</code></td><td>string</td><td>Filter by status</td><td><code>success</code></td></tr>
                        <tr><td><code class="param">mobile</code></td><td>string</td><td>Filter by mobile number</td><td><code>9876543210</code></td></tr>
                        <tr><td><code class="param">operator_code</code></td><td>string</td><td>Filter by operator</td><td><code>JIO</code></td></tr>
                        <tr><td><code class="param">date_from</code></td><td>date</td><td>Start date (YYYY-MM-DD)</td><td><code>2026-03-01</code></td></tr>
                        <tr><td><code class="param">date_to</code></td><td>date</td><td>End date (YYYY-MM-DD)</td><td><code>2026-03-31</code></td></tr>
                        <tr><td><code class="param">per_page</code></td><td>integer</td><td>Results per page (max 100, default 20)</td><td><code>50</code></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div>
            <div class="ep-label">Sample Request</div>
            <div class="code-block">curl -X GET \
  "http://localhost:8000/api/v1/buyer/transactions?status=success&date_from=2026-03-01&per_page=50" \
  -H "X-API-Key: YOUR_API_KEY" \
  -H "Accept: application/json"</div>
        </div>
    </div>
</div>

{{-- 5. Complaint --}}
<div class="card" style="margin-bottom:18px">
    <div class="card-header" style="justify-content:space-between">
        <span class="card-title">📢 5. Raise a Complaint</span>
        <span style="background:rgba(245,158,11,.15);color:#fbbf24;font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px">POST</span>
    </div>
    <div class="card-body">
        <div style="margin-bottom:14px">
            <div class="ep-label">Endpoint URL</div>
            <div class="ep-url">
                <span style="color:#fbbf24;font-weight:700">POST</span>
                <code>http://localhost:8000/api/v1/complaints</code>
                <button onclick="copyText('http://localhost:8000/api/v1/complaints')" class="copy-small">Copy</button>
            </div>
            <div style="font-size:12px;color:var(--muted);margin-top:8px">⚠️ This endpoint uses <strong>Bearer token</strong> authentication (not X-API-Key). Use the token received on login.</div>
        </div>
        <div style="margin-bottom:14px">
            <div class="ep-label">Request Parameters</div>
            <div class="table-wrap" style="border:1px solid var(--border2);border-radius:8px;overflow:hidden">
                <table>
                    <thead><tr><th>Parameter</th><th>Type</th><th>Required</th><th>Description</th><th>Values</th></tr></thead>
                    <tbody>
                        <tr><td><code class="param">type</code></td><td>string</td><td><span class="req">Required</span></td><td>Complaint category</td><td><code>recharge_failed</code> / <code>balance_deducted</code> / <code>wrong_recharge</code> / <code>refund</code> / <code>operator_delay</code> / <code>other</code></td></tr>
                        <tr><td><code class="param">description</code></td><td>string</td><td><span class="req">Required</span></td><td>Detailed description of the issue</td><td>min 10 chars</td></tr>
                        <tr><td><code class="param">recharge_transaction_id</code></td><td>integer</td><td><span class="opt">Optional</span></td><td>Transaction ID related to complaint</td><td><code>1042</code></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
            <div>
                <div class="ep-label">Sample Request</div>
                <div class="code-block">curl -X POST \
  http://localhost:8000/api/v1/complaints \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "recharge_failed",
    "description": "Recharged ₹299 to 9876543210 via Airtel but balance was deducted without recharge.",
    "recharge_transaction_id": 1042
  }'</div>
            </div>
            <div>
                <div class="ep-label">Sample Response</div>
                <div class="code-block">{
  "message": "Complaint submitted successfully.",
  "complaint": {
    "id": 85,
    "type": "recharge_failed",
    "status": "open",
    "priority": "medium",
    "created_at": "2026-03-19T10:40:00+05:30"
  }
}</div>
            </div>
        </div>
    </div>
</div>

{{-- 6. Callback Register --}}
<div class="card" style="margin-bottom:18px">
    <div class="card-header" style="justify-content:space-between">
        <span class="card-title">🔔 6. Register Callback URL</span>
        <span style="background:rgba(245,158,11,.15);color:#fbbf24;font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px">POST</span>
    </div>
    <div class="card-body">
        <div style="margin-bottom:14px">
            <div class="ep-label">Endpoint URL</div>
            <div class="ep-url">
                <span style="color:#fbbf24;font-weight:700">POST</span>
                <code>http://localhost:8000/api/v1/buyer/callback/register</code>
            </div>
            <div style="font-size:12px;color:var(--muted);margin-top:8px">Register your server URL to receive real-time recharge status updates. We will POST to your URL whenever a transaction status changes.</div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
            <div>
                <div class="ep-label">Sample Request</div>
                <div class="code-block">curl -X POST \
  http://localhost:8000/api/v1/buyer/callback/register \
  -H "X-API-Key: YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "callback_url": "https://yoursite.com/recharge/callback",
    "secret": "my_secret_key_min16chars"
  }'</div>
            </div>
            <div>
                <div class="ep-label">Callback Payload (sent to your URL)</div>
                <div class="code-block">{
  "txn_id": 1042,
  "status": "success",
  "mobile": "9876543210",
  "operator": "AIRTEL",
  "amount": "299.00",
  "operator_ref": "OP_REF_7842901",
  "processed_at": "2026-03-19T10:30:08+05:30"
}

// Verification (if secret set):
// X-Signature: HMAC-SHA256(body, secret)</div>
            </div>
        </div>
    </div>
</div>

{{-- Rate Limits --}}
<div class="card" style="margin-bottom:18px">
    <div class="card-header"><span class="card-title">⚠️ Rate Limits & Error Codes</span></div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
            <div>
                <div class="ep-label" style="margin-bottom:10px">Rate Limits</div>
                <div class="table-wrap" style="border:1px solid var(--border2);border-radius:8px;overflow:hidden">
                    <table>
                        <thead><tr><th>Endpoint</th><th>Limit</th></tr></thead>
                        <tbody>
                            <tr><td>All API calls</td><td>60 requests / minute</td></tr>
                            <tr><td>POST /buyer/recharge</td><td>10 requests / minute</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div>
                <div class="ep-label" style="margin-bottom:10px">HTTP Status Codes</div>
                <div class="table-wrap" style="border:1px solid var(--border2);border-radius:8px;overflow:hidden">
                    <table>
                        <thead><tr><th>Code</th><th>Meaning</th></tr></thead>
                        <tbody>
                            <tr><td><code>200</code></td><td>Success (GET requests)</td></tr>
                            <tr><td><code>202</code></td><td>Recharge accepted & queued</td></tr>
                            <tr><td><code>401</code></td><td>Invalid / missing API key</td></tr>
                            <tr><td><code>403</code></td><td>Insufficient scope / frozen</td></tr>
                            <tr><td><code>409</code></td><td>Duplicate transaction</td></tr>
                            <tr><td><code>422</code></td><td>Validation error</td></tr>
                            <tr><td><code>429</code></td><td>Rate limit exceeded</td></tr>
                            <tr><td><code>503</code></td><td>Operator unavailable</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- PHP / Python code samples --}}
<div class="card">
    <div class="card-header">
        <span class="card-title">🧑‍💻 Code Samples</span>
        <div style="display:flex;gap:6px;margin-left:auto">
            <button class="lang-btn active" onclick="showLang('php',this)">PHP</button>
            <button class="lang-btn" onclick="showLang('python',this)">Python</button>
            <button class="lang-btn" onclick="showLang('nodejs',this)">Node.js</button>
        </div>
    </div>
    <div class="card-body">
        <div id="lang-php">
<div class="code-block">&lt;?php
$apiKey = 'YOUR_API_KEY';
$baseUrl = 'http://localhost:8000/api/v1';

// ── Initiate Recharge ─────────────────────────────────────
$ch = curl_init($baseUrl . '/buyer/recharge');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        'X-API-Key: ' . $apiKey,
        'Content-Type: application/json',
        'Accept: application/json',
    ],
    CURLOPT_POSTFIELDS => json_encode([
        'mobile'        => '9876543210',
        'operator_code' => 'AIRTEL',
        'amount'        => 299,
        'recharge_type' => 'prepaid',
    ]),
]);
$response = json_decode(curl_exec($ch), true);
curl_close($ch);

if ($response['success']) {
    $txnId = $response['data']['txn_id'];
    echo "Recharge queued. Txn ID: " . $txnId;

    // ── Poll Status ───────────────────────────────────────
    do {
        sleep(5);
        $ch2 = curl_init($baseUrl . '/buyer/recharge/' . $txnId);
        curl_setopt_array($ch2, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'X-API-Key: ' . $apiKey,
                'Accept: application/json',
            ],
        ]);
        $status = json_decode(curl_exec($ch2), true);
        curl_close($ch2);
        echo "Status: " . $status['data']['status'];
    } while (!$status['data']['is_terminal']);

    echo "Final status: " . $status['data']['status'];
} else {
    echo "Error: " . $response['error']['message'];
}
?&gt;</div>
        </div>
        <div id="lang-python" style="display:none">
<div class="code-block">import requests, time

API_KEY  = 'YOUR_API_KEY'
BASE_URL = 'http://localhost:8000/api/v1'
HEADERS  = {'X-API-Key': API_KEY, 'Content-Type': 'application/json', 'Accept': 'application/json'}

# ── Initiate Recharge ─────────────────────────────────────────────
payload = {
    'mobile':        '9876543210',
    'operator_code': 'AIRTEL',
    'amount':        299,
    'recharge_type': 'prepaid',
}
res = requests.post(f'{BASE_URL}/buyer/recharge', json=payload, headers=HEADERS)
data = res.json()

if data['success']:
    txn_id = data['data']['txn_id']
    print(f'Recharge queued. Txn ID: {txn_id}')

    # ── Poll Status ───────────────────────────────────────────────
    while True:
        time.sleep(5)
        r = requests.get(f'{BASE_URL}/buyer/recharge/{txn_id}', headers=HEADERS)
        s = r.json()['data']
        print(f"Status: {s['status']}")
        if s['is_terminal']:
            break

    print(f"Final: {s['status']}")
else:
    print(f"Error: {data['error']['message']}")

# ── Check Balance ─────────────────────────────────────────────────
bal = requests.get(f'{BASE_URL}/buyer/balance', headers=HEADERS).json()
print(f"Available balance: ₹{bal['data']['available_balance']}")</div>
        </div>
        <div id="lang-nodejs" style="display:none">
<div class="code-block">const axios = require('axios');

const API_KEY  = 'YOUR_API_KEY';
const BASE_URL = 'http://localhost:8000/api/v1';
const headers  = { 'X-API-Key': API_KEY, 'Content-Type': 'application/json', Accept: 'application/json' };

async function recharge() {
    // ── Initiate Recharge ─────────────────────────────────
    const { data } = await axios.post(`${BASE_URL}/buyer/recharge`, {
        mobile:        '9876543210',
        operator_code: 'AIRTEL',
        amount:        299,
        recharge_type: 'prepaid',
    }, { headers });

    if (!data.success) { console.error(data.error.message); return; }

    const txnId = data.data.txn_id;
    console.log(`Queued. Txn ID: ${txnId}`);

    // ── Poll Status ───────────────────────────────────────
    let status;
    do {
        await new Promise(r => setTimeout(r, 5000));
        const r = await axios.get(`${BASE_URL}/buyer/recharge/${txnId}`, { headers });
        status = r.data.data;
        console.log(`Status: ${status.status}`);
    } while (!status.is_terminal);

    console.log(`Final: ${status.status}`);
}

recharge().catch(console.error);</div>
        </div>
    </div>
</div>

<style>
.ep-label { font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--muted2);margin-bottom:8px }
.ep-url   { display:flex;align-items:center;gap:10px;background:var(--card2);border:1px solid var(--border2);border-radius:8px;padding:10px 14px;font-size:13px }
.ep-url code { flex:1;color:var(--text) }
.code-block { background:rgba(0,0,0,.4);border:1px solid var(--border2);border-radius:8px;padding:14px;font-family:monospace;font-size:12px;color:#a5f3fc;line-height:1.6;white-space:pre;overflow-x:auto;margin-bottom:6px }
.copy-small { background:var(--card2);border:1px solid var(--border2);border-radius:6px;padding:4px 10px;font-size:11px;font-weight:600;color:var(--muted);cursor:pointer;font-family:inherit;white-space:nowrap;flex-shrink:0 }
.copy-small:hover { color:var(--text) }
.copy-code-btn { background:none;border:none;color:var(--muted);font-size:11px;cursor:pointer;font-family:inherit;padding:0 }
.copy-code-btn:hover { color:var(--blue) }
.param { color:var(--orange);background:rgba(245,158,11,.1);padding:1px 5px;border-radius:4px;font-size:12px }
.req { background:rgba(239,68,68,.15);color:#f87171;font-size:10px;font-weight:700;padding:2px 7px;border-radius:10px }
.opt { background:rgba(16,185,129,.1);color:#34d399;font-size:10px;font-weight:700;padding:2px 7px;border-radius:10px }
.lang-btn { background:var(--card2);border:1px solid var(--border2);border-radius:6px;padding:5px 12px;font-size:12px;font-weight:600;color:var(--muted);cursor:pointer;font-family:inherit;transition:all .15s }
.lang-btn.active { background:var(--blue-dk);border-color:var(--blue-dk);color:#fff }
.sc-queued { color:#94a3b8;font-weight:700 }
.sc-processing { color:#fbbf24;font-weight:700 }
.sc-success { color:#34d399;font-weight:700 }
.sc-failed { color:#f87171;font-weight:700 }
.sc-refunded { color:#60a5fa;font-weight:700 }
.sc-partial { color:#a78bfa;font-weight:700 }
</style>
@endsection

@push('scripts')
<script>
let _apiKey = '';

async function generateKey() {
    const res = await apiFetch('/api/v1/auth/api-key', { method: 'POST' });
    if (!res) return;
    const data = await res.json();
    _apiKey = data.api_key || data.key || '';
    const display = document.getElementById('api-key-display');
    display.textContent = _apiKey;
    display.style.color = '#34d399';
    document.getElementById('key-note').style.display = 'block';
}

function copyKey() {
    if (!_apiKey) { alert('Generate your API key first.'); return; }
    navigator.clipboard.writeText(_apiKey).then(() => {
        const btn = document.getElementById('copy-btn');
        btn.textContent = '✅ Copied!';
        setTimeout(() => btn.textContent = '📋 Copy', 2000);
    });
}

function copyText(text) {
    navigator.clipboard.writeText(text).then(() => {
        const el = event.target;
        const orig = el.textContent;
        el.textContent = '✅';
        setTimeout(() => el.textContent = orig, 1500);
    });
}

function copyBlock(id) {
    const text = document.getElementById(id).textContent;
    navigator.clipboard.writeText(text).then(() => {
        const btn = event.target;
        btn.textContent = '✅ Copied!';
        setTimeout(() => btn.textContent = '📋 Copy', 2000);
    });
}

function showLang(lang, btn) {
    ['php','python','nodejs'].forEach(l => {
        document.getElementById('lang-' + l).style.display = l === lang ? 'block' : 'none';
    });
    document.querySelectorAll('.lang-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
}
</script>
@endpush
