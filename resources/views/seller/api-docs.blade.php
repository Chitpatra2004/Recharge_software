@extends('layouts.seller')
@section('title','Seller API Docs')
@section('content')
@php($platformBaseUrl = 'https://tourmybharat.com')

<div class="page-header">
    <div>
        <h1 class="page-title">Seller API Documentation</h1>
        <p class="page-sub">Standard integration format with sample request and response structures</p>
    </div>
</div>

<style>
.seller-docs-grid{display:grid;grid-template-columns:280px 1fr;gap:20px;align-items:start}
.seller-docs-nav{position:sticky;top:20px}
.seller-docs-nav a{display:block;padding:10px 12px;border-radius:10px;color:#475569;text-decoration:none;font-size:13px;font-weight:600}
.seller-docs-nav a:hover{background:#f8fafc;color:#0f172a}
.seller-docs-card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:22px;margin-bottom:18px}
.seller-docs-title{font-size:18px;font-weight:800;color:#0f172a;margin-bottom:8px}
.seller-docs-desc{font-size:13px;color:#64748b;margin-bottom:16px}
.doc-kv{display:grid;grid-template-columns:160px 1fr;gap:12px;font-size:13px;margin-bottom:8px}
.doc-kv strong{color:#334155}
.doc-table{width:100%;border-collapse:collapse;font-size:13px}
.doc-table th,.doc-table td{border:1px solid #e2e8f0;padding:10px 12px;text-align:left;vertical-align:top}
.doc-table th{background:#f8fafc;color:#334155;font-size:12px;text-transform:uppercase;letter-spacing:.03em}
.doc-code{background:#0f172a;color:#e2e8f0;border-radius:12px;padding:16px;overflow:auto;font-size:12px;line-height:1.7}
.doc-note{background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;border-radius:12px;padding:12px 14px;font-size:13px}
.doc-badges{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px}
.doc-badge{padding:4px 10px;border-radius:999px;font-size:11px;font-weight:700}
.doc-badge.get{background:#dcfce7;color:#166534}
.doc-badge.post{background:#dbeafe;color:#1d4ed8}
.doc-badge.secure{background:#fef3c7;color:#92400e}
@media(max-width:980px){.seller-docs-grid{grid-template-columns:1fr}.seller-docs-nav{position:static}.doc-kv{grid-template-columns:1fr}}
</style>

<div class="seller-docs-grid">
    <div class="seller-docs-nav">
        <div class="seller-docs-card" style="padding:14px">
            <a href="#overview">Overview</a>
            <a href="#auth">Authentication</a>
            <a href="#ip-validation">IP Validation</a>
            <a href="#recharge-api">Recharge API</a>
            <a href="#callback-url">Callback URL</a>
            <a href="#status-check-url">Status Check URL</a>
            <a href="#dispute-url">Dispute URL</a>
            <a href="#responses">Sample Responses</a>
        </div>
    </div>

    <div>
        <div id="overview" class="seller-docs-card">
            <div class="seller-docs-title">Overview</div>
            <div class="seller-docs-desc">This document defines the standard seller integration format for recharge processing, callback acknowledgement, status checks, dispute checks, and IP validation.</div>
            <div class="doc-note">Seller-side integration URLs are documented in `GET` format as requested. The platform recharge submission endpoint now supports both <code>GET</code> and <code>POST</code> on the same path.</div>
        </div>

        <div id="auth" class="seller-docs-card">
            <div class="seller-docs-title">Authentication</div>
            <div class="doc-kv"><strong>Header</strong><span><code>X-API-Key: YOUR_API_KEY</code></span></div>
            <div class="doc-kv"><strong>Base URL</strong><span><code>{{ $platformBaseUrl }}/api/v1/buyer</code></span></div>
            <div class="doc-kv"><strong>Content Type</strong><span><code>application/json</code></span></div>
        </div>

        <div id="ip-validation" class="seller-docs-card">
            <div class="seller-docs-title">IP Validation</div>
            <div class="seller-docs-desc">Register the seller server IPs or CIDR ranges in the seller portal. Generated API keys will enforce those IP rules automatically.</div>
            <table class="doc-table">
                <thead>
                    <tr><th>Field</th><th>Format</th><th>Example</th></tr>
                </thead>
                <tbody>
                    <tr><td>Allowed IP</td><td>Single IP</td><td><code>203.0.113.42</code></td></tr>
                    <tr><td>Allowed Range</td><td>CIDR</td><td><code>203.0.113.0/24</code></td></tr>
                    <tr><td>Multiple Entries</td><td>Comma or new line separated</td><td><code>203.0.113.42, 198.51.100.0/24</code></td></tr>
                </tbody>
            </table>
        </div>

        <div id="recharge-api" class="seller-docs-card">
            <div class="seller-docs-title">Recharge API</div>
            <div class="doc-badges">
                <span class="doc-badge get">GET</span>
                <span class="doc-badge post">POST</span>
                <span class="doc-badge secure">X-API-Key Required</span>
            </div>
            <div class="doc-kv"><strong>Endpoint</strong><span><code>{{ $platformBaseUrl }}/api/v1/buyer/recharge</code></span></div>
            <div class="doc-kv"><strong>Purpose</strong><span>Submit a recharge request to the platform.</span></div>
            <div class="doc-kv"><strong>GET Example</strong><span><code>{{ $platformBaseUrl }}/api/v1/buyer/recharge?mobile=9876543210&amp;operator_code=AIRTEL&amp;amount=199&amp;recharge_type=prepaid&amp;circle=Delhi&amp;idempotency_key=ORD-10001</code></span></div>
            <table class="doc-table" style="margin:14px 0">
                <thead>
                    <tr><th>Parameter</th><th>Type</th><th>Required</th><th>Description</th></tr>
                </thead>
                <tbody>
                    <tr><td><code>mobile</code></td><td>string</td><td>Yes</td><td>Customer mobile number</td></tr>
                    <tr><td><code>operator_code</code></td><td>string</td><td>Yes</td><td>Operator code such as <code>AIRTEL</code> or <code>JIO</code></td></tr>
                    <tr><td><code>amount</code></td><td>number</td><td>Yes</td><td>Recharge amount</td></tr>
                    <tr><td><code>recharge_type</code></td><td>string</td><td>No</td><td><code>prepaid</code>, <code>postpaid</code>, <code>dth</code>, <code>broadband</code></td></tr>
                    <tr><td><code>circle</code></td><td>string</td><td>No</td><td>Telecom circle</td></tr>
                    <tr><td><code>idempotency_key</code></td><td>string</td><td>No</td><td>Unique client request key</td></tr>
                </tbody>
            </table>
            <div class="doc-code"><pre>{
  "mobile": "9876543210",
  "operator_code": "AIRTEL",
  "amount": 199,
  "recharge_type": "prepaid",
  "circle": "Delhi",
  "idempotency_key": "ORD-10001"
}</pre></div>
        </div>

        <div id="callback-url" class="seller-docs-card">
            <div class="seller-docs-title">Callback URL</div>
            <div class="doc-badges">
                <span class="doc-badge get">GET</span>
                <span class="doc-badge secure">Seller Endpoint</span>
            </div>
            <div class="doc-kv"><strong>Purpose</strong><span>Platform hits the seller callback URL after recharge status changes.</span></div>
            <div class="doc-code"><pre>GET https://seller-domain.com/recharge/callback?txn_id=1042&status=success&operator_ref=OP123456&amount=199.00&message=Recharge+successful</pre></div>
        </div>

        <div id="status-check-url" class="seller-docs-card">
            <div class="seller-docs-title">Status Check URL</div>
            <div class="doc-badges">
                <span class="doc-badge get">GET</span>
                <span class="doc-badge secure">Seller Endpoint</span>
            </div>
            <div class="doc-kv"><strong>Purpose</strong><span>Used when the platform or admin needs to verify the final status from the seller side.</span></div>
            <div class="doc-code"><pre>GET https://seller-domain.com/recharge/status?txn_id=1042</pre></div>
        </div>

        <div id="dispute-url" class="seller-docs-card">
            <div class="seller-docs-title">Dispute URL</div>
            <div class="doc-badges">
                <span class="doc-badge get">GET</span>
                <span class="doc-badge secure">Seller Endpoint</span>
            </div>
            <div class="doc-kv"><strong>Purpose</strong><span>Used to open or check recharge dispute details against a transaction.</span></div>
            <div class="doc-code"><pre>GET https://seller-domain.com/recharge/dispute?txn_id=1042&reason=network_issue</pre></div>
        </div>

        <div id="responses" class="seller-docs-card">
            <div class="seller-docs-title">Sample Response Types</div>
            <div class="seller-docs-desc">Use a consistent JSON envelope for all seller-side `GET` URLs.</div>

            <div style="font-size:13px;font-weight:700;color:#334155;margin-bottom:8px">Recharge Accepted</div>
            <div class="doc-code" style="margin-bottom:14px"><pre>{
  "success": true,
  "code": "ACCEPTED",
  "message": "Recharge request accepted.",
  "data": {
    "txn_id": "1042",
    "status": "queued",
    "operator_ref": null
  }
}</pre></div>

            <div style="font-size:13px;font-weight:700;color:#334155;margin-bottom:8px">Status Success</div>
            <div class="doc-code" style="margin-bottom:14px"><pre>{
  "success": true,
  "code": "STATUS_OK",
  "message": "Transaction found.",
  "data": {
    "txn_id": "1042",
    "status": "success",
    "operator_ref": "OP123456",
    "amount": "199.00"
  }
}</pre></div>

            <div style="font-size:13px;font-weight:700;color:#334155;margin-bottom:8px">Dispute Opened</div>
            <div class="doc-code" style="margin-bottom:14px"><pre>{
  "success": true,
  "code": "DISPUTE_REGISTERED",
  "message": "Dispute recorded successfully.",
  "data": {
    "txn_id": "1042",
    "dispute_id": "DSP-9001",
    "status": "open"
  }
}</pre></div>

            <div style="font-size:13px;font-weight:700;color:#334155;margin-bottom:8px">Error Response</div>
            <div class="doc-code"><pre>{
  "success": false,
  "code": "NOT_FOUND",
  "message": "Transaction not found.",
  "data": null
}</pre></div>
        </div>
    </div>
</div>

@endsection
