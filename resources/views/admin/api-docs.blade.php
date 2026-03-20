@extends('layouts.admin')
@section('title', 'API Documentation')
@section('page-title', 'API Documentation')

@push('head')
<style>
.api-layout { display: grid; grid-template-columns: 220px 1fr; gap: 20px; align-items: start; }
.api-nav { position: sticky; top: 16px; background: var(--card-bg); border-radius: var(--radius); border: 1px solid var(--border); overflow: hidden; }
.api-nav-section { padding: 10px 14px 4px; font-size: 10px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: .08em; }
.api-nav a { display: block; padding: 7px 14px; font-size: 13px; color: var(--text-secondary); text-decoration: none; border-left: 2px solid transparent; transition: all .15s; }
.api-nav a:hover, .api-nav a.active { color: var(--accent-blue); background: rgba(37,99,235,.06); border-left-color: var(--accent-blue); }
.doc-section { scroll-margin-top: 20px; }
.doc-section + .doc-section { margin-top: 28px; }
.endpoint-card { border: 1px solid var(--border); border-radius: var(--radius-sm); overflow: hidden; margin-bottom: 12px; }
.endpoint-header { display: flex; align-items: center; gap: 10px; padding: 12px 16px; background: #f8fafc; cursor: pointer; user-select: none; }
.endpoint-header:hover { background: #f1f5f9; }
.endpoint-body { padding: 16px; border-top: 1px solid var(--border); display: none; }
.endpoint-body.open { display: block; }
.method-badge { font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 4px; min-width: 52px; text-align: center; }
.method-get { background: #d1fae5; color: #065f46; }
.method-post { background: #dbeafe; color: #1e40af; }
.method-put { background: #fef3c7; color: #92400e; }
.method-delete { background: #fee2e2; color: #991b1b; }
.method-patch { background: #ede9fe; color: #5b21b6; }
.endpoint-path { font-family: monospace; font-size: 13px; font-weight: 600; color: var(--text-primary); }
.endpoint-desc { font-size: 12px; color: var(--text-muted); margin-left: auto; }
.param-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.param-table th { text-align: left; padding: 6px 10px; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; background: #f8fafc; border-bottom: 1px solid var(--border); }
.param-table td { padding: 8px 10px; border-bottom: 1px solid var(--border); }
.param-table tr:last-child td { border-bottom: none; }
.param-required { font-size: 10px; font-weight: 700; background: #fee2e2; color: #991b1b; padding: 1px 6px; border-radius: 3px; }
.param-optional { font-size: 10px; font-weight: 700; background: #f1f5f9; color: var(--text-muted); padding: 1px 6px; border-radius: 3px; }
.code-block { background: #1e293b; color: #e2e8f0; border-radius: var(--radius-sm); padding: 14px 16px; font-family: monospace; font-size: 12px; line-height: 1.6; overflow-x: auto; position: relative; }
.code-block pre { margin: 0; }
.copy-btn { position: absolute; top: 8px; right: 8px; background: rgba(255,255,255,.1); border: none; color: #94a3b8; padding: 4px 8px; border-radius: 4px; font-size: 11px; cursor: pointer; transition: background .15s; }
.copy-btn:hover { background: rgba(255,255,255,.2); color: #e2e8f0; }
.response-tab-bar { display: flex; gap: 6px; margin-bottom: 10px; }
.response-tab { padding: 4px 10px; border-radius: 4px; border: 1px solid var(--border); font-size: 12px; cursor: pointer; background: #f8fafc; color: var(--text-secondary); }
.response-tab.active { background: var(--accent-blue); color: #fff; border-color: var(--accent-blue); }
.section-title { font-size: 16px; font-weight: 700; color: var(--text-primary); margin-bottom: 6px; display: flex; align-items: center; gap: 8px; }
.section-desc { font-size: 13px; color: var(--text-secondary); margin-bottom: 16px; }
.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px; }
.info-item { background: #f8fafc; border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 12px 14px; }
.info-item label { font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; display: block; margin-bottom: 4px; }
.info-item span { font-size: 13px; color: var(--text-primary); font-weight: 500; font-family: monospace; }
</style>
@endpush

@section('content')
<div class="breadcrumb">
    <a href="/admin/dashboard">Dashboard</a>
    <svg class="breadcrumb-sep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>API Documentation</span>
</div>

<div class="api-layout">
    {{-- Navigation --}}
    <nav class="api-nav">
        <div class="api-nav-section">Overview</div>
        <a href="#overview" class="active" onclick="setActive(this)">Introduction</a>
        <a href="#auth" onclick="setActive(this)">Authentication</a>
        <a href="#errors" onclick="setActive(this)">Error Codes</a>
        <a href="#rate-limits" onclick="setActive(this)">Rate Limits</a>
        <div class="api-nav-section">Recharges</div>
        <a href="#recharge-submit" onclick="setActive(this)">Submit Recharge</a>
        <a href="#recharge-status" onclick="setActive(this)">Check Status</a>
        <a href="#recharge-history" onclick="setActive(this)">History</a>
        <div class="api-nav-section">Operators</div>
        <a href="#operators-list" onclick="setActive(this)">List Operators</a>
        <a href="#operators-plans" onclick="setActive(this)">Browse Plans</a>
        <div class="api-nav-section">Wallet</div>
        <a href="#wallet-balance" onclick="setActive(this)">Get Balance</a>
        <a href="#wallet-topup" onclick="setActive(this)">Request Top-up</a>
        <div class="api-nav-section">Complaints</div>
        <a href="#complaints-create" onclick="setActive(this)">Raise Complaint</a>
        <a href="#complaints-status" onclick="setActive(this)">Complaint Status</a>
    </nav>

    {{-- Content --}}
    <div>
        {{-- Overview --}}
        <div id="overview" class="doc-section card" style="padding:20px">
            <div class="section-title">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;color:var(--accent-blue)"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                API Reference
            </div>
            <p class="section-desc">The Recharge Panel API allows you to programmatically submit recharges, check statuses, manage wallets, and more. All endpoints return JSON responses.</p>
            <div class="info-grid">
                <div class="info-item"><label>Base URL</label><span>https://yourdomain.com/api/v1</span></div>
                <div class="info-item"><label>Version</label><span>v1.0</span></div>
                <div class="info-item"><label>Format</label><span>application/json</span></div>
                <div class="info-item"><label>Protocol</label><span>HTTPS only</span></div>
            </div>
        </div>

        {{-- Authentication --}}
        <div id="auth" class="doc-section card" style="padding:20px">
            <div class="section-title">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;color:var(--accent-purple)"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                Authentication
            </div>
            <p class="section-desc">All API requests require Bearer token authentication. Include your API key in the <code style="background:#f1f5f9;padding:1px 5px;border-radius:3px;font-size:12px">Authorization</code> header.</p>
            <div class="code-block" style="margin-bottom:12px">
                <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                <pre>Authorization: Bearer YOUR_API_KEY
Content-Type: application/json
Accept: application/json</pre>
            </div>
            <div class="note-bar">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                API keys can be generated from <a href="/admin/api-keys" style="color:var(--accent-blue)">Manage → API Keys</a>. Keys are shown only once at generation time.
            </div>
        </div>

        {{-- Error Codes --}}
        <div id="errors" class="doc-section card" style="padding:20px">
            <div class="section-title">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;color:var(--accent-red)"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Error Codes
            </div>
            <p class="section-desc">All errors follow a standard JSON format with a <code style="background:#f1f5f9;padding:1px 5px;border-radius:3px;font-size:12px">code</code> and <code style="background:#f1f5f9;padding:1px 5px;border-radius:3px;font-size:12px">message</code> field.</p>
            <table class="param-table">
                <thead><tr><th>HTTP Status</th><th>Code</th><th>Description</th></tr></thead>
                <tbody>
                    <tr><td>400</td><td><code>VALIDATION_ERROR</code></td><td>Request parameters failed validation</td></tr>
                    <tr><td>401</td><td><code>UNAUTHORIZED</code></td><td>Missing or invalid API key</td></tr>
                    <tr><td>403</td><td><code>FORBIDDEN</code></td><td>Key lacks required scope for this endpoint</td></tr>
                    <tr><td>404</td><td><code>NOT_FOUND</code></td><td>Resource does not exist</td></tr>
                    <tr><td>422</td><td><code>RECHARGE_FAILED</code></td><td>Operator returned failure</td></tr>
                    <tr><td>429</td><td><code>RATE_LIMITED</code></td><td>Too many requests — slow down</td></tr>
                    <tr><td>500</td><td><code>SERVER_ERROR</code></td><td>Internal error — contact support</td></tr>
                </tbody>
            </table>
        </div>

        {{-- Rate Limits --}}
        <div id="rate-limits" class="doc-section card" style="padding:20px">
            <div class="section-title">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;color:var(--accent-orange)"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Rate Limits
            </div>
            <table class="param-table">
                <thead><tr><th>Endpoint Group</th><th>Limit</th><th>Window</th></tr></thead>
                <tbody>
                    <tr><td>Recharge Submit</td><td>60 requests</td><td>Per minute</td></tr>
                    <tr><td>Status Check</td><td>120 requests</td><td>Per minute</td></tr>
                    <tr><td>Operators / Plans</td><td>30 requests</td><td>Per minute</td></tr>
                    <tr><td>All other endpoints</td><td>100 requests</td><td>Per minute</td></tr>
                </tbody>
            </table>
        </div>

        {{-- Recharge Submit --}}
        <div id="recharge-submit" class="doc-section">
            <div class="endpoint-card">
                <div class="endpoint-header" onclick="toggleEndpoint(this)">
                    <span class="method-badge method-post">POST</span>
                    <span class="endpoint-path">/recharges</span>
                    <span class="endpoint-desc">Submit a recharge</span>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;color:var(--text-muted);margin-left:8px;transition:transform .2s"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </div>
                <div class="endpoint-body open">
                    <p style="font-size:13px;color:var(--text-secondary);margin-bottom:14px">Submit a new mobile/DTH/utility recharge request. Returns immediately with a transaction ID; use the status endpoint to poll.</p>
                    <p style="font-size:12px;font-weight:700;color:var(--text-muted);margin-bottom:8px">REQUEST BODY</p>
                    <table class="param-table" style="margin-bottom:16px">
                        <thead><tr><th>Field</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
                        <tbody>
                            <tr><td><code>mobile</code></td><td>string</td><td><span class="param-required">required</span></td><td>10-digit mobile number</td></tr>
                            <tr><td><code>operator_code</code></td><td>string</td><td><span class="param-required">required</span></td><td>Operator code (see Operators API)</td></tr>
                            <tr><td><code>amount</code></td><td>number</td><td><span class="param-required">required</span></td><td>Recharge amount in INR (min 10)</td></tr>
                            <tr><td><code>circle</code></td><td>string</td><td><span class="param-optional">optional</span></td><td>Telecom circle code</td></tr>
                            <tr><td><code>reference</code></td><td>string</td><td><span class="param-optional">optional</span></td><td>Your internal order reference</td></tr>
                        </tbody>
                    </table>
                    <p style="font-size:12px;font-weight:700;color:var(--text-muted);margin-bottom:8px">EXAMPLE REQUEST</p>
                    <div class="code-block" style="margin-bottom:14px">
                        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                        <pre>POST /api/v1/recharges
Authorization: Bearer YOUR_API_KEY
Content-Type: application/json

{
  "mobile": "9876543210",
  "operator_code": "JIO",
  "amount": 239,
  "circle": "MH",
  "reference": "ORD-20240318-001"
}</pre>
                    </div>
                    <div class="response-tab-bar">
                        <button class="response-tab active" onclick="switchTab(this,'resp-sub-200')">200 Success</button>
                        <button class="response-tab" onclick="switchTab(this,'resp-sub-422')">422 Failed</button>
                    </div>
                    <div id="resp-sub-200" class="code-block">
                        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                        <pre>{
  "success": true,
  "transaction_id": "TXN2024031800123",
  "status": "pending",
  "amount": 239,
  "operator": "Jio",
  "mobile": "9876543210",
  "created_at": "2024-03-18T10:23:45Z"
}</pre>
                    </div>
                    <div id="resp-sub-422" class="code-block" style="display:none">
                        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                        <pre>{
  "success": false,
  "code": "RECHARGE_FAILED",
  "message": "Operator returned failure: Invalid number",
  "transaction_id": "TXN2024031800124"
}</pre>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recharge Status --}}
        <div id="recharge-status" class="doc-section">
            <div class="endpoint-card">
                <div class="endpoint-header" onclick="toggleEndpoint(this)">
                    <span class="method-badge method-get">GET</span>
                    <span class="endpoint-path">/recharges/{transaction_id}</span>
                    <span class="endpoint-desc">Check recharge status</span>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;color:var(--text-muted);margin-left:8px;transition:transform .2s"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </div>
                <div class="endpoint-body">
                    <p style="font-size:13px;color:var(--text-secondary);margin-bottom:14px">Retrieve the current status of a submitted recharge transaction.</p>
                    <p style="font-size:12px;font-weight:700;color:var(--text-muted);margin-bottom:8px">PATH PARAMETERS</p>
                    <table class="param-table" style="margin-bottom:16px">
                        <thead><tr><th>Parameter</th><th>Type</th><th>Description</th></tr></thead>
                        <tbody>
                            <tr><td><code>transaction_id</code></td><td>string</td><td>Transaction ID returned from submit</td></tr>
                        </tbody>
                    </table>
                    <div class="code-block">
                        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                        <pre>{
  "success": true,
  "transaction_id": "TXN2024031800123",
  "status": "success",
  "operator_ref": "OPR98765432",
  "amount": 239,
  "mobile": "9876543210",
  "completed_at": "2024-03-18T10:24:02Z"
}</pre>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recharge History --}}
        <div id="recharge-history" class="doc-section">
            <div class="endpoint-card">
                <div class="endpoint-header" onclick="toggleEndpoint(this)">
                    <span class="method-badge method-get">GET</span>
                    <span class="endpoint-path">/recharges</span>
                    <span class="endpoint-desc">List recharge history</span>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;color:var(--text-muted);margin-left:8px;transition:transform .2s"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </div>
                <div class="endpoint-body">
                    <p style="font-size:13px;color:var(--text-secondary);margin-bottom:14px">Paginated list of your recharge transactions with optional filters.</p>
                    <p style="font-size:12px;font-weight:700;color:var(--text-muted);margin-bottom:8px">QUERY PARAMETERS</p>
                    <table class="param-table" style="margin-bottom:16px">
                        <thead><tr><th>Parameter</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
                        <tbody>
                            <tr><td><code>page</code></td><td>integer</td><td><span class="param-optional">optional</span></td><td>Page number (default 1)</td></tr>
                            <tr><td><code>per_page</code></td><td>integer</td><td><span class="param-optional">optional</span></td><td>Results per page (max 100, default 20)</td></tr>
                            <tr><td><code>status</code></td><td>string</td><td><span class="param-optional">optional</span></td><td>Filter: pending, success, failed</td></tr>
                            <tr><td><code>from_date</code></td><td>date</td><td><span class="param-optional">optional</span></td><td>Start date (YYYY-MM-DD)</td></tr>
                            <tr><td><code>to_date</code></td><td>date</td><td><span class="param-optional">optional</span></td><td>End date (YYYY-MM-DD)</td></tr>
                        </tbody>
                    </table>
                    <div class="code-block">
                        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                        <pre>GET /api/v1/recharges?status=success&from_date=2024-03-01&per_page=50</pre>
                    </div>
                </div>
            </div>
        </div>

        {{-- Operators List --}}
        <div id="operators-list" class="doc-section">
            <div class="endpoint-card">
                <div class="endpoint-header" onclick="toggleEndpoint(this)">
                    <span class="method-badge method-get">GET</span>
                    <span class="endpoint-path">/operators</span>
                    <span class="endpoint-desc">List all operators</span>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;color:var(--text-muted);margin-left:8px;transition:transform .2s"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </div>
                <div class="endpoint-body">
                    <p style="font-size:13px;color:var(--text-secondary);margin-bottom:14px">Returns all active operators grouped by type (prepaid, postpaid, DTH, utility).</p>
                    <div class="code-block">
                        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                        <pre>{
  "success": true,
  "operators": [
    { "code": "JIO", "name": "Reliance Jio", "type": "prepaid", "active": true },
    { "code": "AIRTEL", "name": "Airtel", "type": "prepaid", "active": true },
    { "code": "VI", "name": "Vodafone Idea", "type": "prepaid", "active": true },
    { "code": "BSNL", "name": "BSNL", "type": "prepaid", "active": true }
  ]
}</pre>
                    </div>
                </div>
            </div>
        </div>

        {{-- Operators Plans --}}
        <div id="operators-plans" class="doc-section">
            <div class="endpoint-card">
                <div class="endpoint-header" onclick="toggleEndpoint(this)">
                    <span class="method-badge method-get">GET</span>
                    <span class="endpoint-path">/operators/{code}/plans</span>
                    <span class="endpoint-desc">Browse operator plans</span>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;color:var(--text-muted);margin-left:8px;transition:transform .2s"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </div>
                <div class="endpoint-body">
                    <p style="font-size:13px;color:var(--text-secondary);margin-bottom:14px">Returns available recharge plans for the given operator, optionally filtered by circle.</p>
                    <p style="font-size:12px;font-weight:700;color:var(--text-muted);margin-bottom:8px">QUERY PARAMETERS</p>
                    <table class="param-table" style="margin-bottom:16px">
                        <thead><tr><th>Parameter</th><th>Type</th><th>Description</th></tr></thead>
                        <tbody>
                            <tr><td><code>circle</code></td><td>string</td><td>Telecom circle code (e.g. MH, DL)</td></tr>
                        </tbody>
                    </table>
                    <div class="code-block">
                        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                        <pre>GET /api/v1/operators/JIO/plans?circle=MH</pre>
                    </div>
                </div>
            </div>
        </div>

        {{-- Wallet Balance --}}
        <div id="wallet-balance" class="doc-section">
            <div class="endpoint-card">
                <div class="endpoint-header" onclick="toggleEndpoint(this)">
                    <span class="method-badge method-get">GET</span>
                    <span class="endpoint-path">/wallet/balance</span>
                    <span class="endpoint-desc">Get wallet balance</span>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;color:var(--text-muted);margin-left:8px;transition:transform .2s"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </div>
                <div class="endpoint-body">
                    <div class="code-block">
                        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                        <pre>{
  "success": true,
  "balance": 5420.50,
  "currency": "INR",
  "last_updated": "2024-03-18T10:20:00Z"
}</pre>
                    </div>
                </div>
            </div>
        </div>

        {{-- Wallet Top-up --}}
        <div id="wallet-topup" class="doc-section">
            <div class="endpoint-card">
                <div class="endpoint-header" onclick="toggleEndpoint(this)">
                    <span class="method-badge method-post">POST</span>
                    <span class="endpoint-path">/wallet/topup</span>
                    <span class="endpoint-desc">Request wallet top-up</span>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;color:var(--text-muted);margin-left:8px;transition:transform .2s"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </div>
                <div class="endpoint-body">
                    <p style="font-size:13px;color:var(--text-secondary);margin-bottom:14px">Submit a top-up request. The amount will be added after payment gateway confirmation.</p>
                    <table class="param-table" style="margin-bottom:16px">
                        <thead><tr><th>Field</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
                        <tbody>
                            <tr><td><code>amount</code></td><td>number</td><td><span class="param-required">required</span></td><td>Amount to add (min 100 INR)</td></tr>
                            <tr><td><code>payment_method</code></td><td>string</td><td><span class="param-required">required</span></td><td>upi, neft, imps, rtgs</td></tr>
                            <tr><td><code>utr</code></td><td>string</td><td><span class="param-optional">optional</span></td><td>UTR / transaction reference</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Complaints Create --}}
        <div id="complaints-create" class="doc-section">
            <div class="endpoint-card">
                <div class="endpoint-header" onclick="toggleEndpoint(this)">
                    <span class="method-badge method-post">POST</span>
                    <span class="endpoint-path">/complaints</span>
                    <span class="endpoint-desc">Raise a complaint</span>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;color:var(--text-muted);margin-left:8px;transition:transform .2s"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </div>
                <div class="endpoint-body">
                    <table class="param-table" style="margin-bottom:16px">
                        <thead><tr><th>Field</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
                        <tbody>
                            <tr><td><code>transaction_id</code></td><td>string</td><td><span class="param-required">required</span></td><td>Related transaction ID</td></tr>
                            <tr><td><code>type</code></td><td>string</td><td><span class="param-required">required</span></td><td>pending_recharge, wrong_recharge, refund</td></tr>
                            <tr><td><code>description</code></td><td>string</td><td><span class="param-optional">optional</span></td><td>Additional details</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Complaints Status --}}
        <div id="complaints-status" class="doc-section">
            <div class="endpoint-card">
                <div class="endpoint-header" onclick="toggleEndpoint(this)">
                    <span class="method-badge method-get">GET</span>
                    <span class="endpoint-path">/complaints/{id}</span>
                    <span class="endpoint-desc">Get complaint status</span>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;color:var(--text-muted);margin-left:8px;transition:transform .2s"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </div>
                <div class="endpoint-body">
                    <div class="code-block">
                        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                        <pre>{
  "success": true,
  "complaint": {
    "id": "CMP-20240318-001",
    "transaction_id": "TXN2024031800123",
    "type": "pending_recharge",
    "status": "under_review",
    "created_at": "2024-03-18T11:00:00Z",
    "resolved_at": null
  }
}</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function setActive(el) {
    document.querySelectorAll('.api-nav a').forEach(a => a.classList.remove('active'));
    el.classList.add('active');
}

function toggleEndpoint(header) {
    const body = header.nextElementSibling;
    const icon = header.querySelector('svg:last-child');
    const isOpen = body.classList.contains('open');
    body.classList.toggle('open');
    icon.style.transform = isOpen ? '' : 'rotate(180deg)';
}

function switchTab(btn, targetId) {
    const bar = btn.closest('.response-tab-bar');
    const card = btn.closest('.endpoint-body');
    bar.querySelectorAll('.response-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    const allPanels = bar.nextElementSibling;
    // hide all sibling code blocks after the tab bar
    let el = bar.nextElementSibling;
    while (el && el.classList.contains('code-block')) {
        el.style.display = 'none';
        el = el.nextElementSibling;
    }
    const target = document.getElementById(targetId);
    if (target) target.style.display = 'block';
}

function copyCode(btn) {
    const pre = btn.nextElementSibling;
    navigator.clipboard.writeText(pre.textContent.trim()).then(() => {
        btn.textContent = 'Copied!';
        setTimeout(() => btn.textContent = 'Copy', 1500);
    });
}

// Highlight active nav item on scroll
const sections = document.querySelectorAll('.doc-section[id]');
const navLinks = document.querySelectorAll('.api-nav a[href^="#"]');
const mainContent = document.querySelector('.main-content') || document.documentElement;

function onScroll() {
    let current = '';
    sections.forEach(sec => {
        if (sec.getBoundingClientRect().top <= 60) current = sec.id;
    });
    navLinks.forEach(a => {
        a.classList.toggle('active', a.getAttribute('href') === '#' + current);
    });
}
document.addEventListener('scroll', onScroll, true);
</script>
@endpush
