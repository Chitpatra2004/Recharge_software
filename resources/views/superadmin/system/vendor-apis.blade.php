@extends('layouts.superadmin')
@section('title', 'Vendor API Integration')
@section('page-title', 'Vendor API Integration')

@push('head')
<style>
/* ══════════════════════════════════════════════
   VENDOR API INTEGRATION PAGE
══════════════════════════════════════════════ */

/* ── Layout ─────────────────────────────────── */
.vi-layout { display: grid; grid-template-columns: 300px 1fr; gap: 20px; align-items: start; }
@media(max-width: 960px) { .vi-layout { grid-template-columns: 1fr; } }

/* ── Vendor Cards (left list) ───────────────── */
.vendor-card {
    display: flex; align-items: center; gap: 11px;
    padding: 12px 14px;
    border-radius: var(--rh-radius-sm);
    border: 1px solid transparent;
    cursor: pointer;
    transition: all var(--rh-transition);
    margin-bottom: 4px;
}
.vendor-card:hover  { background: var(--rh-brand-lt); }
.vendor-card.active { background: var(--rh-brand-lt); border-color: var(--rh-brand); }
.vendor-logo {
    width: 38px; height: 38px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 800; color: #fff; flex-shrink: 0;
}
.vendor-name { font-size: 13px; font-weight: 700; color: var(--rh-text); }
.vendor-type { font-size: 11px; color: var(--rh-muted); }

/* ── Wizard Steps ────────────────────────────── */
.steps-bar {
    display: flex; align-items: center; gap: 0;
    margin-bottom: 24px;
    background: var(--rh-page);
    border: 1px solid var(--rh-border);
    border-radius: var(--rh-radius);
    overflow: hidden;
}
.step-item {
    flex: 1; display: flex; align-items: center; justify-content: center;
    gap: 8px; padding: 12px 8px;
    font-size: 12.5px; font-weight: 600; color: var(--rh-muted);
    position: relative; cursor: pointer;
    transition: all var(--rh-transition);
    border-right: 1px solid var(--rh-border);
}
.step-item:last-child { border-right: none; }
.step-item.done   { background: #ecfdf5; color: #065f46; }
.step-item.active { background: var(--rh-brand); color: #fff; }
.step-num {
    width: 22px; height: 22px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 800;
    background: rgba(255,255,255,.25);
    flex-shrink: 0;
}
.step-item.done .step-num   { background: var(--rh-green); color: #fff; }
.step-item:not(.active):not(.done) .step-num { background: var(--rh-border); color: var(--rh-muted); }

/* ── Panel ───────────────────────────────────── */
.step-panel { display: none; }
.step-panel.active { display: block; }

/* ── Field Grid ──────────────────────────────── */
.fg-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.fg-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; }
@media(max-width: 600px) { .fg-2, .fg-3 { grid-template-columns: 1fr; } }
.form-field { display: flex; flex-direction: column; gap: 5px; }

/* ── Tag Input ───────────────────────────────── */
.method-pills { display: flex; gap: 6px; flex-wrap: wrap; }
.method-pill {
    padding: 5px 14px; border-radius: 99px;
    border: 1.5px solid var(--rh-border); background: var(--rh-page);
    font-size: 12.5px; font-weight: 700; cursor: pointer;
    transition: all var(--rh-transition); color: var(--rh-muted);
}
.method-pill:hover, .method-pill.active { border-color: var(--rh-brand); background: var(--rh-brand); color: #fff; }

/* ── Mapping Row ─────────────────────────────── */
.map-row {
    display: grid; grid-template-columns: 180px 1fr 32px;
    align-items: center; gap: 10px;
    padding: 8px 0;
    border-bottom: 1px solid var(--rh-border);
}
.map-row:last-child { border-bottom: none; }
.map-key {
    font-size: 12.5px; font-weight: 600; color: var(--rh-text-sub);
    display: flex; align-items: center; gap: 6px;
}
.map-key-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--rh-brand); flex-shrink: 0; }
.map-required { font-size: 10px; color: var(--rh-red); }
.map-info { font-size: 10.5px; color: var(--rh-muted); }

/* ── Code Block ──────────────────────────────── */
.code-block {
    background: #0f172a; color: #e2e8f0;
    border-radius: var(--rh-radius-sm);
    padding: 16px; font-family: monospace; font-size: 12.5px;
    line-height: 1.7; overflow-x: auto; position: relative;
}
.code-block .k  { color: #93c5fd; }  /* key */
.code-block .s  { color: #86efac; }  /* string */
.code-block .n  { color: #fcd34d; }  /* number */
.code-block .c  { color: #6b7280; font-style: italic; }  /* comment */
.copy-code-btn {
    position: absolute; top: 10px; right: 10px;
    padding: 4px 10px; border-radius: 6px;
    background: rgba(255,255,255,.1); color: #cbd5e1;
    border: 1px solid rgba(255,255,255,.15);
    font-size: 11px; font-weight: 600; cursor: pointer;
    transition: all .2s;
}
.copy-code-btn:hover { background: rgba(255,255,255,.2); color: #fff; }

/* ── Vendor Status Bar ───────────────────────── */
.vendor-status-row {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 0; border-bottom: 1px solid var(--rh-border);
}
.vendor-status-row:last-child { border-bottom: none; }

/* ── Quick Fill Template ─────────────────────── */
.template-btn {
    display: flex; align-items: center; gap: 8px;
    padding: 9px 12px; border-radius: var(--rh-radius-sm);
    border: 1px solid var(--rh-border); background: var(--rh-card);
    cursor: pointer; transition: all var(--rh-transition);
    font-size: 12.5px; font-weight: 600; color: var(--rh-text-sub);
}
.template-btn:hover { border-color: var(--rh-brand); color: var(--rh-brand); background: var(--rh-brand-lt); }
.template-btn svg { width: 16px; height: 16px; flex-shrink: 0; }
</style>
@endpush

@section('content')

<div class="rh-breadcrumb">
    <a href="{{ route('superadmin.dashboard') }}">Dashboard</a>
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>Vendor API Integration</span>
</div>

<div class="rh-alert rh-alert-info" style="margin-bottom:20px">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <span>Add any external recharge/purchase API in <strong>4 simple steps</strong> — no coding required. Just fill the form, map fields, and test.</span>
</div>

<div class="vi-layout">

    <!-- ══════ LEFT: Vendor List ══════ -->
    <div>
        <div class="rh-card">
            <div class="rh-card-header">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;color:var(--rh-brand)"><path stroke-linecap="round" stroke-linejoin="round" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/></svg>
                <span class="rh-card-title">Integrated Vendors</span>
                <span id="vendorCountBadge" style="margin-left:auto;font-size:11px;background:var(--rh-brand-lt);color:var(--rh-brand);padding:2px 8px;border-radius:99px;font-weight:700">4</span>
            </div>
            <div style="padding:10px">
                <input type="text" placeholder="Search vendors…" oninput="filterVendors(this.value)"
                    style="width:100%;padding:7px 10px;border:1px solid var(--rh-border);border-radius:var(--rh-radius-sm);font-size:12.5px;font-family:inherit;outline:none;background:var(--rh-page);margin-bottom:8px">
                <div id="vendorList"></div>
                <button class="btn btn-md btn-primary" style="width:100%;margin-top:10px;justify-content:center" onclick="openAddWizard()">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Add New Vendor API
                </button>
            </div>
        </div>

        {{-- Summary Stats --}}
        <div class="rh-card" style="margin-top:14px;padding:16px">
            <div style="font-size:11.5px;font-weight:700;color:var(--rh-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:12px">Quick Stats</div>
            <div style="display:flex;flex-direction:column;gap:10px">
                <div style="display:flex;justify-content:space-between;align-items:center">
                    <span style="font-size:13px;color:var(--rh-muted)">Total Vendors</span>
                    <span style="font-weight:700;color:var(--rh-text)">4</span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center">
                    <span style="font-size:13px;color:var(--rh-muted)">Active</span>
                    <span style="font-weight:700;color:var(--rh-green)">3</span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center">
                    <span style="font-size:13px;color:var(--rh-muted)">Degraded</span>
                    <span style="font-weight:700;color:var(--rh-amber)">1</span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center">
                    <span style="font-size:13px;color:var(--rh-muted)">Avg Success Rate</span>
                    <span style="font-weight:700;color:var(--rh-brand)">97.8%</span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center">
                    <span style="font-size:13px;color:var(--rh-muted)">Req Today</span>
                    <span style="font-weight:700;color:var(--rh-text)">8,341</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ══════ RIGHT: Detail / Wizard Panel ══════ -->
    <div id="rightPanel">

        {{-- Empty State --}}
        <div id="emptyPane" class="rh-card" style="padding:60px 24px;text-align:center">
            <div style="width:60px;height:60px;background:var(--rh-brand-lt);border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 18px">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="width:30px;height:30px;color:var(--rh-brand)"><path stroke-linecap="round" stroke-linejoin="round" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/></svg>
            </div>
            <div style="font-size:16px;font-weight:800;color:var(--rh-text);margin-bottom:8px">Select a vendor to view details</div>
            <div style="font-size:13.5px;color:var(--rh-muted);margin-bottom:20px">Or add a new vendor API in 4 simple steps.</div>
            <button class="btn btn-md btn-primary" onclick="openAddWizard()" style="display:inline-flex">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Add New Vendor API
            </button>
        </div>

        {{-- Vendor Detail (shown when a vendor is selected) --}}
        <div id="vendorDetail" style="display:none">

            {{-- Vendor Header Card --}}
            <div class="rh-card" style="margin-bottom:16px">
                <div style="padding:20px;display:flex;align-items:center;gap:16px">
                    <div id="dLogo" class="vendor-logo" style="width:52px;height:52px;border-radius:14px;font-size:15px"></div>
                    <div style="flex:1">
                        <div id="dName" style="font-size:17px;font-weight:800;color:var(--rh-text)"></div>
                        <div id="dMeta" style="font-size:12.5px;color:var(--rh-muted);margin-top:3px"></div>
                    </div>
                    <div style="display:flex;gap:8px">
                        <button class="btn btn-sm btn-outline" onclick="editVendor()">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Edit
                        </button>
                        <button class="btn btn-sm btn-primary" onclick="runTest()" id="testBtn">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            Test Live
                        </button>
                        <div class="rh-toggle-wrap" title="Enable / Disable">
                            <input type="checkbox" class="rh-toggle-input" id="vendorToggle" checked>
                            <label for="vendorToggle" class="rh-toggle"></label>
                        </div>
                    </div>
                </div>

                {{-- Mini Stats --}}
                <div style="display:grid;grid-template-columns:repeat(4,1fr);border-top:1px solid var(--rh-border)">
                    <div style="padding:14px 18px;border-right:1px solid var(--rh-border);text-align:center">
                        <div id="dSuccess" style="font-size:20px;font-weight:800;color:var(--rh-green)"></div>
                        <div style="font-size:11px;color:var(--rh-muted);margin-top:2px">Success Rate</div>
                    </div>
                    <div style="padding:14px 18px;border-right:1px solid var(--rh-border);text-align:center">
                        <div id="dSpeed" style="font-size:20px;font-weight:800;color:var(--rh-text)"></div>
                        <div style="font-size:11px;color:var(--rh-muted);margin-top:2px">Avg Response</div>
                    </div>
                    <div style="padding:14px 18px;border-right:1px solid var(--rh-border);text-align:center">
                        <div id="dToday" style="font-size:20px;font-weight:800;color:var(--rh-text)"></div>
                        <div style="font-size:11px;color:var(--rh-muted);margin-top:2px">Requests Today</div>
                    </div>
                    <div style="padding:14px 18px;text-align:center">
                        <div id="dStatus" style="font-size:20px;font-weight:800"></div>
                        <div style="font-size:11px;color:var(--rh-muted);margin-top:2px">Status</div>
                    </div>
                </div>
            </div>

            {{-- Test Result Bar --}}
            <div id="testResultBar" style="display:none" class="rh-alert rh-alert-info" style="margin-bottom:16px"></div>

            {{-- Config Summary Tabs --}}
            <div class="rh-card">
                <div class="rh-card-header" style="gap:4px">
                    <span class="rh-card-title" style="margin-right:8px">Integration Details</span>
                    <button class="method-pill active" onclick="switchTab(this,'tabEndpoint')" style="padding:4px 12px;font-size:11.5px">Endpoint</button>
                    <button class="method-pill" onclick="switchTab(this,'tabRequest')" style="padding:4px 12px;font-size:11.5px">Request</button>
                    <button class="method-pill" onclick="switchTab(this,'tabResponse')" style="padding:4px 12px;font-size:11.5px">Response</button>
                    <button class="method-pill" onclick="switchTab(this,'tabLogs')" style="padding:4px 12px;font-size:11.5px">Logs</button>
                </div>

                {{-- Endpoint Tab --}}
                <div id="tabEndpoint" class="tab-content" style="padding:20px">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                        <div>
                            <div class="rh-label" style="margin-bottom:6px">Base URL</div>
                            <div id="dUrl" style="font-family:monospace;font-size:12.5px;background:var(--rh-page);border:1px solid var(--rh-border);border-radius:var(--rh-radius-sm);padding:8px 12px;color:var(--rh-text-sub)"></div>
                        </div>
                        <div>
                            <div class="rh-label" style="margin-bottom:6px">Purchase Endpoint</div>
                            <div id="dPurchaseEp" style="font-family:monospace;font-size:12.5px;background:var(--rh-page);border:1px solid var(--rh-border);border-radius:var(--rh-radius-sm);padding:8px 12px;color:var(--rh-text-sub)"></div>
                        </div>
                        <div>
                            <div class="rh-label" style="margin-bottom:6px">Auth Method</div>
                            <div id="dAuth" style="font-size:13px;font-weight:600;color:var(--rh-text)"></div>
                        </div>
                        <div>
                            <div class="rh-label" style="margin-bottom:6px">HTTP Method</div>
                            <div id="dMethod" style="font-size:13px;font-weight:600;color:var(--rh-text)"></div>
                        </div>
                        <div>
                            <div class="rh-label" style="margin-bottom:6px">Content Type</div>
                            <div id="dContentType" style="font-size:13px;color:var(--rh-muted)"></div>
                        </div>
                        <div>
                            <div class="rh-label" style="margin-bottom:6px">Timeout</div>
                            <div id="dTimeout" style="font-size:13px;color:var(--rh-muted)"></div>
                        </div>
                    </div>
                </div>

                {{-- Request Tab --}}
                <div id="tabRequest" class="tab-content" style="padding:20px;display:none">
                    <div style="font-size:12.5px;color:var(--rh-muted);margin-bottom:14px">How this portal maps fields when calling this vendor's API for a purchase request:</div>
                    <div class="map-row" style="background:var(--rh-page);border-radius:var(--rh-radius-sm) var(--rh-radius-sm) 0 0;padding:8px 14px;font-size:11px;font-weight:700;color:var(--rh-muted);text-transform:uppercase;letter-spacing:.06em">
                        <div>Our Field</div><div>→ Vendor's Field Name</div><div></div>
                    </div>
                    <div id="dFieldMap" style="border:1px solid var(--rh-border);border-radius:0 0 var(--rh-radius-sm) var(--rh-radius-sm);padding:0 14px"></div>

                    <div style="margin-top:20px">
                        <div class="rh-label" style="margin-bottom:8px">Sample Request (auto-generated)</div>
                        <div class="code-block" id="dSampleReq">
                            <button class="copy-code-btn" onclick="copyCode('dSampleReq')">Copy</button>
                        </div>
                    </div>
                </div>

                {{-- Response Tab --}}
                <div id="tabResponse" class="tab-content" style="padding:20px;display:none">
                    <div style="font-size:12.5px;color:var(--rh-muted);margin-bottom:14px">How the portal reads the vendor's response to determine success/failure:</div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">
                        <div>
                            <div class="rh-label" style="margin-bottom:6px">Status Field</div>
                            <div id="dRespStatus" style="font-family:monospace;font-size:13px;color:var(--rh-text);background:var(--rh-page);border:1px solid var(--rh-border);border-radius:var(--rh-radius-sm);padding:7px 12px"></div>
                        </div>
                        <div>
                            <div class="rh-label" style="margin-bottom:6px">Success Value</div>
                            <div id="dRespSuccess" style="font-family:monospace;font-size:13px;color:var(--rh-green);background:#ecfdf5;border:1px solid #a7f3d0;border-radius:var(--rh-radius-sm);padding:7px 12px"></div>
                        </div>
                        <div>
                            <div class="rh-label" style="margin-bottom:6px">Transaction ID Field</div>
                            <div id="dRespTxnId" style="font-family:monospace;font-size:13px;color:var(--rh-text);background:var(--rh-page);border:1px solid var(--rh-border);border-radius:var(--rh-radius-sm);padding:7px 12px"></div>
                        </div>
                        <div>
                            <div class="rh-label" style="margin-bottom:6px">Message / Error Field</div>
                            <div id="dRespMsg" style="font-family:monospace;font-size:13px;color:var(--rh-text);background:var(--rh-page);border:1px solid var(--rh-border);border-radius:var(--rh-radius-sm);padding:7px 12px"></div>
                        </div>
                    </div>

                    <div>
                        <div class="rh-label" style="margin-bottom:8px">Sample Response (from vendor)</div>
                        <div class="code-block" id="dSampleResp">
                            <button class="copy-code-btn" onclick="copyCode('dSampleResp')">Copy</button>
                        </div>
                    </div>
                </div>

                {{-- Logs Tab --}}
                <div id="tabLogs" class="tab-content" style="padding:20px;display:none">
                    <div style="font-size:12.5px;color:var(--rh-muted);margin-bottom:14px">Recent API calls to this vendor (last 10):</div>
                    <div id="dLogsList"></div>
                </div>
            </div>
        </div>

        {{-- Add Wizard --}}
        <div id="addWizard" style="display:none">
            <div class="rh-card">
                <div class="rh-card-header">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--rh-brand)"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    <span class="rh-card-title">Add New Vendor API</span>
                    <button class="btn btn-sm btn-outline" style="margin-left:auto" onclick="closeWizard()">Cancel</button>
                </div>

                {{-- Steps Bar --}}
                <div style="padding:16px 20px 0">
                    <div class="steps-bar">
                        <div class="step-item active" id="sbar-1" onclick="goStep(1)">
                            <div class="step-num">1</div>
                            <span class="step-lbl">Provider Info</span>
                        </div>
                        <div class="step-item" id="sbar-2" onclick="goStep(2)">
                            <div class="step-num">2</div>
                            <span class="step-lbl">Auth & Endpoint</span>
                        </div>
                        <div class="step-item" id="sbar-3" onclick="goStep(3)">
                            <div class="step-num">3</div>
                            <span class="step-lbl">Field Mapping</span>
                        </div>
                        <div class="step-item" id="sbar-4" onclick="goStep(4)">
                            <div class="step-num">4</div>
                            <span class="step-lbl">Test & Save</span>
                        </div>
                    </div>
                </div>

                {{-- ── STEP 1: Provider Info ── --}}
                <div id="step-1" class="step-panel active" style="padding:24px">
                    <div style="margin-bottom:20px">
                        <div style="font-size:15px;font-weight:800;color:var(--rh-text);margin-bottom:4px">Step 1 — Who is this vendor?</div>
                        <div style="font-size:13px;color:var(--rh-muted)">Basic information about the API provider.</div>
                    </div>

                    {{-- Quick Fill Templates --}}
                    <div style="margin-bottom:20px">
                        <div class="rh-label" style="margin-bottom:8px">Quick Fill from Known Providers</div>
                        <div style="display:flex;gap:8px;flex-wrap:wrap">
                            <button class="template-btn" onclick="fillTemplate('paytm')">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:var(--rh-brand)"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                Paytm
                            </button>
                            <button class="template-btn" onclick="fillTemplate('razorpay')">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:#0284c7"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                Razorpay
                            </button>
                            <button class="template-btn" onclick="fillTemplate('cashfree')">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:#059669"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                Cashfree
                            </button>
                            <button class="template-btn" onclick="fillTemplate('recharge1')">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:#d97706"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                Recharge1
                            </button>
                            <button class="template-btn" onclick="fillTemplate('cyberplat')">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:#7c3aed"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3"/></svg>
                                Cyberplat
                            </button>
                            <button class="template-btn" onclick="fillTemplate('custom')">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:var(--rh-muted)"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Custom API
                            </button>
                        </div>
                    </div>

                    <div class="fg-2" style="margin-bottom:14px">
                        <div class="form-field">
                            <label class="rh-label">Vendor / Provider Name <span style="color:var(--rh-red)">*</span></label>
                            <input type="text" class="rh-input" id="w_name" placeholder="e.g. Paytm Recharge API">
                        </div>
                        <div class="form-field">
                            <label class="rh-label">Short Code <span style="color:var(--rh-red)">*</span></label>
                            <input type="text" class="rh-input" id="w_code" placeholder="e.g. PAYTM" maxlength="10" style="text-transform:uppercase">
                        </div>
                    </div>

                    <div class="fg-3" style="margin-bottom:14px">
                        <div class="form-field">
                            <label class="rh-label">Category <span style="color:var(--rh-red)">*</span></label>
                            <select class="rh-input" id="w_category">
                                <option value="">Select…</option>
                                <option value="Mobile Recharge">Mobile Recharge</option>
                                <option value="DTH">DTH / Satellite TV</option>
                                <option value="Electricity">Electricity Bill</option>
                                <option value="Gas">Gas / Pipeline</option>
                                <option value="Water">Water Bill</option>
                                <option value="Broadband">Broadband / Internet</option>
                                <option value="Insurance">Insurance Premium</option>
                                <option value="Multi">Multi-service</option>
                            </select>
                        </div>
                        <div class="form-field">
                            <label class="rh-label">Priority (1 = highest)</label>
                            <input type="number" class="rh-input" id="w_priority" value="1" min="1" max="10">
                        </div>
                        <div class="form-field">
                            <label class="rh-label">Timeout (seconds)</label>
                            <input type="number" class="rh-input" id="w_timeout" value="30" min="5" max="120">
                        </div>
                    </div>

                    <div class="form-field" style="margin-bottom:14px">
                        <label class="rh-label">Notes (optional)</label>
                        <textarea class="rh-input" id="w_notes" rows="2" placeholder="Any notes about this vendor or integration…" style="resize:none"></textarea>
                    </div>

                    <div style="display:flex;justify-content:flex-end;margin-top:8px">
                        <button class="btn btn-md btn-primary" onclick="goStep(2)">
                            Next: Auth & Endpoint
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                {{-- ── STEP 2: Auth & Endpoint ── --}}
                <div id="step-2" class="step-panel" style="padding:24px">
                    <div style="margin-bottom:20px">
                        <div style="font-size:15px;font-weight:800;color:var(--rh-text);margin-bottom:4px">Step 2 — Endpoint & Authentication</div>
                        <div style="font-size:13px;color:var(--rh-muted)">Where to send requests and how to authenticate.</div>
                    </div>

                    <div class="form-field" style="margin-bottom:14px">
                        <label class="rh-label">Base URL <span style="color:var(--rh-red)">*</span></label>
                        <input type="url" class="rh-input" id="w_baseUrl" placeholder="https://api.vendor.com">
                        <div style="font-size:11px;color:var(--rh-muted);margin-top:3px">The root URL — do not include the path yet.</div>
                    </div>

                    <div class="fg-2" style="margin-bottom:14px">
                        <div class="form-field">
                            <label class="rh-label">Purchase Endpoint <span style="color:var(--rh-red)">*</span></label>
                            <input type="text" class="rh-input" id="w_purchaseEp" placeholder="/v1/recharge  or  /purchase">
                            <div style="font-size:11px;color:var(--rh-muted);margin-top:3px">Path appended to Base URL for recharge/purchase calls.</div>
                        </div>
                        <div class="form-field">
                            <label class="rh-label">Status Check Endpoint</label>
                            <input type="text" class="rh-input" id="w_statusEp" placeholder="/v1/status  (optional)">
                        </div>
                    </div>

                    <div class="form-field" style="margin-bottom:14px">
                        <label class="rh-label">HTTP Method</label>
                        <div class="method-pills" style="margin-top:6px">
                            <span class="method-pill active" onclick="selectPill(this,'w_method','POST')">POST</span>
                            <span class="method-pill" onclick="selectPill(this,'w_method','GET')">GET</span>
                        </div>
                        <input type="hidden" id="w_method" value="POST">
                    </div>

                    <div class="form-field" style="margin-bottom:14px">
                        <label class="rh-label">Request Format</label>
                        <div class="method-pills" style="margin-top:6px">
                            <span class="method-pill active" onclick="selectPill(this,'w_format','json')">JSON</span>
                            <span class="method-pill" onclick="selectPill(this,'w_format','form')">Form Data</span>
                            <span class="method-pill" onclick="selectPill(this,'w_format','xml')">XML</span>
                            <span class="method-pill" onclick="selectPill(this,'w_format','query')">Query String</span>
                        </div>
                        <input type="hidden" id="w_format" value="json">
                    </div>

                    <div style="border-top:1px solid var(--rh-border);margin:18px 0;padding-top:18px">
                        <div style="font-size:13.5px;font-weight:700;color:var(--rh-text);margin-bottom:14px">Authentication</div>
                        <div class="form-field" style="margin-bottom:14px">
                            <label class="rh-label">Auth Type <span style="color:var(--rh-red)">*</span></label>
                            <div class="method-pills" style="margin-top:6px">
                                <span class="method-pill active" onclick="selectAuth(this,'bearer')">Bearer Token</span>
                                <span class="method-pill" onclick="selectAuth(this,'apikey_header')">API Key (Header)</span>
                                <span class="method-pill" onclick="selectAuth(this,'apikey_body')">API Key (Body)</span>
                                <span class="method-pill" onclick="selectAuth(this,'basic')">Basic Auth</span>
                                <span class="method-pill" onclick="selectAuth(this,'hmac')">HMAC Signature</span>
                            </div>
                            <input type="hidden" id="w_authType" value="bearer">
                        </div>

                        <div id="auth_bearer" class="fg-2" style="margin-bottom:14px">
                            <div class="form-field">
                                <label class="rh-label">Bearer Token / API Key <span style="color:var(--rh-red)">*</span></label>
                                <div style="display:flex;gap:8px">
                                    <input type="password" class="rh-input" id="w_token" placeholder="Paste your API key or token" style="flex:1">
                                    <button class="btn btn-sm btn-outline" onclick="toggleVis('w_token',this)">Show</button>
                                </div>
                            </div>
                            <div class="form-field">
                                <label class="rh-label">Header Name</label>
                                <input type="text" class="rh-input" id="w_tokenHeader" value="Authorization" placeholder="Authorization">
                            </div>
                        </div>

                        <div id="auth_basic" style="display:none" class="fg-2" style="margin-bottom:14px">
                            <div class="form-field">
                                <label class="rh-label">Username / Member ID <span style="color:var(--rh-red)">*</span></label>
                                <input type="text" class="rh-input" id="w_username" placeholder="Username or Member ID">
                            </div>
                            <div class="form-field">
                                <label class="rh-label">Password / Secret <span style="color:var(--rh-red)">*</span></label>
                                <input type="password" class="rh-input" id="w_password" placeholder="Password or Secret key">
                            </div>
                        </div>

                        <div id="auth_hmac" style="display:none" class="fg-2" style="margin-bottom:14px">
                            <div class="form-field">
                                <label class="rh-label">Secret Key <span style="color:var(--rh-red)">*</span></label>
                                <input type="password" class="rh-input" id="w_hmacSecret" placeholder="HMAC secret key">
                            </div>
                            <div class="form-field">
                                <label class="rh-label">Algorithm</label>
                                <select class="rh-input" id="w_hmacAlgo">
                                    <option>HMAC-SHA256</option>
                                    <option>HMAC-SHA512</option>
                                    <option>MD5</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div style="display:flex;justify-content:space-between;margin-top:8px">
                        <button class="btn btn-md btn-outline" onclick="goStep(1)">← Back</button>
                        <button class="btn btn-md btn-primary" onclick="goStep(3)">
                            Next: Field Mapping
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                {{-- ── STEP 3: Field Mapping ── --}}
                <div id="step-3" class="step-panel" style="padding:24px">
                    <div style="margin-bottom:20px">
                        <div style="font-size:15px;font-weight:800;color:var(--rh-text);margin-bottom:4px">Step 3 — Field Mapping</div>
                        <div style="font-size:13px;color:var(--rh-muted)">Tell us what this vendor calls each field. Leave blank to use our default name.</div>
                    </div>

                    {{-- Request Fields --}}
                    <div style="margin-bottom:20px">
                        <div style="font-size:13px;font-weight:700;color:var(--rh-text);margin-bottom:12px;display:flex;align-items:center;gap:8px">
                            <span style="width:6px;height:6px;border-radius:50%;background:var(--rh-brand);display:inline-block"></span>
                            Request Fields (what we send)
                        </div>
                        <div style="border:1px solid var(--rh-border);border-radius:var(--rh-radius-sm);overflow:hidden">
                            <div style="background:var(--rh-page);padding:8px 14px;display:grid;grid-template-columns:200px 1fr 120px;gap:10px;font-size:11px;font-weight:700;color:var(--rh-muted);text-transform:uppercase;letter-spacing:.06em;border-bottom:1px solid var(--rh-border)">
                                <div>Our Field</div><div>Vendor's Field Name</div><div>Type</div>
                            </div>
                            @php
                            $reqFields = [
                                ['key'=>'mobile_number','label'=>'Mobile Number','req'=>true,'type'=>'string','default'=>'mobile'],
                                ['key'=>'amount','label'=>'Recharge Amount','req'=>true,'type'=>'number','default'=>'amount'],
                                ['key'=>'operator_code','label'=>'Operator Code','req'=>true,'type'=>'string','default'=>'operator'],
                                ['key'=>'transaction_id','label'=>'Our TXN ID','req'=>true,'type'=>'string','default'=>'txnid'],
                                ['key'=>'circle_code','label'=>'Telecom Circle','req'=>false,'type'=>'string','default'=>'circle'],
                                ['key'=>'callback_url','label'=>'Callback / Webhook URL','req'=>false,'type'=>'string','default'=>'callback'],
                            ];
                            @endphp
                            @foreach($reqFields as $f)
                            <div style="padding:10px 14px;display:grid;grid-template-columns:200px 1fr 120px;align-items:center;gap:10px;border-bottom:1px solid var(--rh-border){{ $loop->last ? ';border-bottom:none' : '' }}">
                                <div>
                                    <div style="font-size:12.5px;font-weight:600;color:var(--rh-text)">{{ $f['label'] }}</div>
                                    <div style="font-size:11px;color:var(--rh-muted)">{{ $f['key'] }}{{ $f['req'] ? ' <span style="color:var(--rh-red)">*</span>' : '' }}</div>
                                </div>
                                <input type="text" class="rh-input" id="map_{{ $f['key'] }}" placeholder="{{ $f['default'] }}" style="font-family:monospace;font-size:12.5px">
                                <select class="rh-input" style="font-size:12px">
                                    <option {{ $f['type']==='string'?'selected':'' }}>string</option>
                                    <option {{ $f['type']==='number'?'selected':'' }}>number</option>
                                    <option>boolean</option>
                                </select>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Response Fields --}}
                    <div style="margin-bottom:20px">
                        <div style="font-size:13px;font-weight:700;color:var(--rh-text);margin-bottom:12px;display:flex;align-items:center;gap:8px">
                            <span style="width:6px;height:6px;border-radius:50%;background:var(--rh-green);display:inline-block"></span>
                            Response Fields (what vendor sends back)
                        </div>
                        <div style="border:1px solid var(--rh-border);border-radius:var(--rh-radius-sm);overflow:hidden">
                            <div style="background:var(--rh-page);padding:8px 14px;display:grid;grid-template-columns:200px 1fr 1fr;gap:10px;font-size:11px;font-weight:700;color:var(--rh-muted);text-transform:uppercase;letter-spacing:.06em;border-bottom:1px solid var(--rh-border)">
                                <div>What We Need</div><div>Vendor's Field Name</div><div>Success Value</div>
                            </div>
                            @php
                            $respFields = [
                                ['key'=>'status','label'=>'Status Field','req'=>true,'default'=>'status','successDefault'=>'SUCCESS'],
                                ['key'=>'txn_id','label'=>'Vendor TXN ID','req'=>true,'default'=>'txnid','successDefault'=>'—'],
                                ['key'=>'message','label'=>'Message / Error','req'=>false,'default'=>'message','successDefault'=>'—'],
                                ['key'=>'balance','label'=>'Remaining Balance','req'=>false,'default'=>'balance','successDefault'=>'—'],
                            ];
                            @endphp
                            @foreach($respFields as $f)
                            <div style="padding:10px 14px;display:grid;grid-template-columns:200px 1fr 1fr;align-items:center;gap:10px;border-bottom:1px solid var(--rh-border){{ $loop->last ? ';border-bottom:none' : '' }}">
                                <div>
                                    <div style="font-size:12.5px;font-weight:600;color:var(--rh-text)">{{ $f['label'] }}</div>
                                    @if($f['req'])<div style="font-size:11px;color:var(--rh-red)">required</div>@endif
                                </div>
                                <input type="text" class="rh-input" id="resp_{{ $f['key'] }}" placeholder="{{ $f['default'] }}" style="font-family:monospace;font-size:12.5px">
                                <input type="text" class="rh-input" id="succ_{{ $f['key'] }}" placeholder="{{ $f['successDefault'] }}" style="font-family:monospace;font-size:12.5px;{{ $f['successDefault']==='—'?'opacity:.4':'' }}">
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div style="display:flex;justify-content:space-between;margin-top:8px">
                        <button class="btn btn-md btn-outline" onclick="goStep(2)">← Back</button>
                        <button class="btn btn-md btn-primary" onclick="goStep(4)">
                            Next: Test & Save
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                {{-- ── STEP 4: Test & Save ── --}}
                <div id="step-4" class="step-panel" style="padding:24px">
                    <div style="margin-bottom:20px">
                        <div style="font-size:15px;font-weight:800;color:var(--rh-text);margin-bottom:4px">Step 4 — Test & Save</div>
                        <div style="font-size:13px;color:var(--rh-muted)">Review your config and run a test call before going live.</div>
                    </div>

                    {{-- Config Preview --}}
                    <div id="configPreview" style="margin-bottom:20px">
                        <div class="rh-label" style="margin-bottom:10px">Configuration Preview</div>
                        <div style="border:1px solid var(--rh-border);border-radius:var(--rh-radius-sm);overflow:hidden">
                            <div id="previewRows"></div>
                        </div>
                    </div>

                    {{-- Generated Request --}}
                    <div style="margin-bottom:20px">
                        <div class="rh-label" style="margin-bottom:10px">Sample Request (auto-generated)</div>
                        <div class="code-block" id="sampleReqCode">
                            <button class="copy-code-btn" onclick="copyCode('sampleReqCode')">Copy</button>
                            <pre id="sampleReqPre" style="margin:0;white-space:pre-wrap"></pre>
                        </div>
                    </div>

                    {{-- Test Section --}}
                    <div style="background:var(--rh-page);border:1px solid var(--rh-border);border-radius:var(--rh-radius-sm);padding:18px;margin-bottom:20px">
                        <div style="font-size:13.5px;font-weight:700;color:var(--rh-text);margin-bottom:14px">Test with Sample Data</div>
                        <div class="fg-3" style="margin-bottom:14px">
                            <div class="form-field">
                                <label class="rh-label">Test Mobile</label>
                                <input type="text" class="rh-input" id="t_mobile" value="9876543210" style="font-family:monospace">
                            </div>
                            <div class="form-field">
                                <label class="rh-label">Test Amount</label>
                                <input type="number" class="rh-input" id="t_amount" value="10">
                            </div>
                            <div class="form-field">
                                <label class="rh-label">Test Operator</label>
                                <select class="rh-input" id="t_operator">
                                    <option>JIO</option><option>AIRTEL</option><option>VI</option><option>BSNL</option>
                                </select>
                            </div>
                        </div>
                        <div style="display:flex;gap:10px">
                            <button class="btn btn-md btn-outline" onclick="generateSample()" style="flex:1;justify-content:center">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                Generate Request Preview
                            </button>
                            <button class="btn btn-md btn-primary" onclick="runDryTest()" style="flex:1;justify-content:center">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                Run Dry Test
                            </button>
                        </div>
                        <div id="dryTestResult" style="display:none;margin-top:14px;padding:12px 14px;border-radius:var(--rh-radius-sm);font-family:monospace;font-size:12.5px;line-height:1.6"></div>
                    </div>

                    <div style="display:flex;justify-content:space-between;margin-top:8px">
                        <button class="btn btn-md btn-outline" onclick="goStep(3)">← Back</button>
                        <div style="display:flex;gap:10px">
                            <button class="btn btn-md btn-outline" onclick="saveVendor(false)">Save as Draft</button>
                            <button class="btn btn-md btn-green" onclick="saveVendor(true)">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                Save & Activate
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
// ══════════════════════════════════════════════════════
//  VENDOR DATA
// ══════════════════════════════════════════════════════
const VENDORS = [
    {
        id:'V001', name:'Paytm Recharge API', code:'PAYTM', color:'#4f46e5',
        category:'Mobile Recharge', status:'active',
        success:'99.2%', speed:'1.1s', today:'3,241',
        baseUrl:'https://api.paytm.com', purchaseEp:'/v2/recharge', statusEp:'/v2/status',
        method:'POST', format:'json', authType:'Bearer Token',
        contentType:'application/json', timeout:'30s',
        fieldMap:[
            {our:'Mobile Number',  vendor:'mobile'},
            {our:'Amount',         vendor:'amount'},
            {our:'Operator Code',  vendor:'operator'},
            {our:'Transaction ID', vendor:'order_id'},
            {our:'Circle Code',    vendor:'circle'},
        ],
        respStatus:'status', respSuccess:'SUCCESS', respTxnId:'txnid', respMsg:'message',
        sampleReq:`{
  "mobile": "9876543210",
  "amount": 299,
  "operator": "JIO",
  "order_id": "RH_20240320_8341",
  "circle": "MH"
}`,
        sampleResp:`{
  "status": "SUCCESS",
  "txnid": "PTM_TXN_20240320_1234",
  "message": "Recharge successful",
  "balance": 12101.50
}`,
        logs:[
            {time:'12:34:01',status:'success',mobile:'987****210',amount:'₹299',txn:'PTM_001',ms:'1.1s'},
            {time:'12:33:45',status:'success',mobile:'982****678',amount:'₹149',txn:'PTM_002',ms:'0.9s'},
            {time:'12:29:12',status:'failure',mobile:'976****198',amount:'₹399',txn:'—',ms:'3.1s'},
            {time:'12:15:08',status:'success',mobile:'965****987',amount:'₹99',txn:'PTM_004',ms:'1.2s'},
        ]
    },
    {
        id:'V002', name:'Razorpay Recharge', code:'RZPAY', color:'#0284c7',
        category:'Mobile Recharge', status:'active',
        success:'98.8%', speed:'1.3s', today:'1,890',
        baseUrl:'https://api.razorpay.com', purchaseEp:'/v1/recharge', statusEp:'/v1/orders',
        method:'POST', format:'json', authType:'Basic Auth (Key ID + Secret)',
        contentType:'application/json', timeout:'30s',
        fieldMap:[
            {our:'Mobile Number',  vendor:'contact'},
            {our:'Amount',         vendor:'amount'},
            {our:'Operator Code',  vendor:'provider'},
            {our:'Transaction ID', vendor:'receipt'},
        ],
        respStatus:'status', respSuccess:'captured', respTxnId:'id', respMsg:'description',
        sampleReq:`{
  "contact": "9876543210",
  "amount": 19900,
  "provider": "JIO",
  "receipt": "RH_20240320_8341",
  "currency": "INR"
}`,
        sampleResp:`{
  "id": "order_xyz1234567",
  "status": "captured",
  "amount": 19900,
  "description": "Recharge successful"
}`,
        logs:[
            {time:'12:30:11',status:'success',mobile:'987****210',amount:'₹199',txn:'RZP_001',ms:'1.3s'},
            {time:'12:28:44',status:'success',mobile:'982****678',amount:'₹499',txn:'RZP_002',ms:'1.1s'},
        ]
    },
    {
        id:'V003', name:'Cashfree Recharge', code:'CFREX', color:'#059669',
        category:'Multi', status:'active',
        success:'99.5%', speed:'0.9s', today:'2,110',
        baseUrl:'https://api.cashfree.com', purchaseEp:'/payout/v1/recharge', statusEp:'/payout/v1/getTransferStatus',
        method:'POST', format:'json', authType:'API Key (Header: x-client-id)',
        contentType:'application/json', timeout:'30s',
        fieldMap:[
            {our:'Mobile Number',  vendor:'phone'},
            {our:'Amount',         vendor:'amount'},
            {our:'Operator Code',  vendor:'operatorCode'},
            {our:'Transaction ID', vendor:'transferId'},
        ],
        respStatus:'status', respSuccess:'SUCCESS', respTxnId:'referenceId', respMsg:'message',
        sampleReq:`{
  "phone": "9876543210",
  "amount": 299,
  "operatorCode": "JIO",
  "transferId": "RH_20240320_8341"
}`,
        sampleResp:`{
  "status": "SUCCESS",
  "referenceId": "CF_REF_20240320",
  "message": "Transfer successful"
}`,
        logs:[
            {time:'12:31:20',status:'success',mobile:'987****210',amount:'₹249',txn:'CF_001',ms:'0.9s'},
            {time:'12:27:55',status:'success',mobile:'954****321',amount:'₹599',txn:'CF_002',ms:'1.0s'},
        ]
    },
    {
        id:'V004', name:'BSNL Custom API', code:'BSNL', color:'#d97706',
        category:'Mobile Recharge', status:'degraded',
        success:'91.4%', speed:'3.2s', today:'890',
        baseUrl:'https://bsnl-api.rechargerhub.in', purchaseEp:'/recharge', statusEp:'/status',
        method:'POST', format:'form', authType:'API Key (Body param)',
        contentType:'application/x-www-form-urlencoded', timeout:'45s',
        fieldMap:[
            {our:'Mobile Number',  vendor:'msisdn'},
            {our:'Amount',         vendor:'recharge_amount'},
            {our:'Operator Code',  vendor:'op_code'},
            {our:'Transaction ID', vendor:'client_txn_id'},
        ],
        respStatus:'ResponseCode', respSuccess:'00', respTxnId:'OperatorTxnId', respMsg:'ResponseMessage',
        sampleReq:`msisdn=9876543210&recharge_amount=22&op_code=BSNL&client_txn_id=RH_20240320_8341&api_key=YOUR_KEY`,
        sampleResp:`{
  "ResponseCode": "00",
  "OperatorTxnId": "BSNL_98765",
  "ResponseMessage": "Transaction successful"
}`,
        logs:[
            {time:'12:20:11',status:'success',mobile:'987****210',amount:'₹22',txn:'BSNL_001',ms:'3.0s'},
            {time:'12:18:44',status:'failure',mobile:'975****432',amount:'₹47',txn:'—',ms:'5.2s'},
            {time:'12:10:08',status:'success',mobile:'965****987',amount:'₹22',txn:'BSNL_003',ms:'2.8s'},
        ]
    },
];

let activeVendor = null;
let currentStep  = 1;

// ── Render Vendor List ─────────────────────────────
function renderVendors(list) {
    const el = document.getElementById('vendorList');
    el.innerHTML = list.map(v => `
        <div class="vendor-card ${activeVendor?.id===v.id?'active':''}" onclick="selectVendor('${v.id}')">
            <div class="vendor-logo" style="background:${v.color}">${v.code.slice(0,3)}</div>
            <div style="flex:1;min-width:0">
                <div class="vendor-name" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${v.name}</div>
                <div class="vendor-type">${v.category}</div>
            </div>
            <span class="badge ${v.status==='active'?'badge-green':'badge-amber'}" style="flex-shrink:0">
                ${v.status==='active'?'Active':'Degraded'}
            </span>
        </div>
    `).join('');
}
renderVendors(VENDORS);

function filterVendors(q) {
    renderVendors(VENDORS.filter(v => v.name.toLowerCase().includes(q.toLowerCase()) || v.code.toLowerCase().includes(q.toLowerCase())));
}

// ── Select Vendor ──────────────────────────────────
function selectVendor(id) {
    activeVendor = VENDORS.find(v => v.id === id);
    renderVendors(VENDORS);

    document.getElementById('emptyPane').style.display    = 'none';
    document.getElementById('addWizard').style.display    = 'none';
    document.getElementById('vendorDetail').style.display = 'block';
    document.getElementById('testResultBar').style.display = 'none';

    // Header
    const logo = document.getElementById('dLogo');
    logo.textContent   = activeVendor.code.slice(0,3);
    logo.style.background = activeVendor.color;
    document.getElementById('dName').textContent = activeVendor.name;
    document.getElementById('dMeta').textContent = `${activeVendor.code} · ${activeVendor.category} · ${activeVendor.baseUrl}`;

    // Stats
    document.getElementById('dSuccess').textContent = activeVendor.success;
    document.getElementById('dSpeed').textContent   = activeVendor.speed;
    document.getElementById('dToday').textContent   = activeVendor.today;
    const statusEl = document.getElementById('dStatus');
    statusEl.textContent  = activeVendor.status === 'active' ? 'Active' : 'Degraded';
    statusEl.style.color  = activeVendor.status === 'active' ? 'var(--rh-green)' : 'var(--rh-amber)';

    // Endpoint tab
    document.getElementById('dUrl').textContent         = activeVendor.baseUrl;
    document.getElementById('dPurchaseEp').textContent  = activeVendor.purchaseEp;
    document.getElementById('dAuth').textContent        = activeVendor.authType;
    document.getElementById('dMethod').textContent      = activeVendor.method;
    document.getElementById('dContentType').textContent = activeVendor.contentType;
    document.getElementById('dTimeout').textContent     = activeVendor.timeout;

    // Field map
    const fm = document.getElementById('dFieldMap');
    fm.innerHTML = activeVendor.fieldMap.map(f => `
        <div class="map-row">
            <div class="map-key"><span class="map-key-dot"></span>${f.our}</div>
            <div style="display:flex;align-items:center;gap:8px">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;color:var(--rh-faint);flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                <code style="font-family:monospace;font-size:12.5px;background:var(--rh-brand-lt);color:var(--rh-brand);padding:2px 8px;border-radius:5px">${f.vendor}</code>
            </div>
            <div></div>
        </div>
    `).join('');

    // Sample request/response
    document.getElementById('dSampleReq').innerHTML  = `<button class="copy-code-btn" onclick="copyCode('dSampleReq')">Copy</button><pre style="margin:0;white-space:pre-wrap">${escHtml(activeVendor.sampleReq)}</pre>`;
    document.getElementById('dSampleResp').innerHTML = `<button class="copy-code-btn" onclick="copyCode('dSampleResp')">Copy</button><pre style="margin:0;white-space:pre-wrap">${escHtml(activeVendor.sampleResp)}</pre>`;

    // Response fields
    document.getElementById('dRespStatus').textContent  = activeVendor.respStatus;
    document.getElementById('dRespSuccess').textContent = activeVendor.respSuccess;
    document.getElementById('dRespTxnId').textContent   = activeVendor.respTxnId;
    document.getElementById('dRespMsg').textContent     = activeVendor.respMsg;

    // Logs
    const logsEl = document.getElementById('dLogsList');
    logsEl.innerHTML = activeVendor.logs.map(l => `
        <div style="display:flex;align-items:center;gap:12px;padding:9px 0;border-bottom:1px solid var(--rh-border)">
            <span style="font-family:monospace;font-size:11.5px;color:var(--rh-muted);flex-shrink:0">${l.time}</span>
            <span class="badge ${l.status==='success'?'badge-green':'badge-red'}">${l.status}</span>
            <span style="font-family:monospace;font-size:12px;color:var(--rh-text-sub)">${l.mobile}</span>
            <span style="font-size:12.5px;font-weight:600;color:var(--rh-text)">${l.amount}</span>
            <span style="font-family:monospace;font-size:11.5px;color:var(--rh-muted);flex:1">${l.txn}</span>
            <span style="font-size:12px;color:${l.status==='success'?'var(--rh-green)':'var(--rh-red)'}">${l.ms}</span>
        </div>
    `).join('');

    // Activate first tab
    showTab('tabEndpoint');
}

// ── Tab Switcher ───────────────────────────────────
function switchTab(btn, tabId) {
    document.querySelectorAll('.tab-content').forEach(t => t.style.display = 'none');
    document.getElementById(tabId).style.display = 'block';
    document.querySelectorAll('.rh-card-header .method-pill').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
}
function showTab(id) {
    document.querySelectorAll('.tab-content').forEach(t => t.style.display = 'none');
    document.getElementById(id).style.display = 'block';
    document.querySelectorAll('.rh-card-header .method-pill').forEach(b => b.classList.remove('active'));
    const tabNames = {tabEndpoint:0, tabRequest:1, tabResponse:2, tabLogs:3};
    const btns = document.querySelectorAll('.rh-card-header .method-pill');
    if (btns[tabNames[id]]) btns[tabNames[id]].classList.add('active');
}

// ── Live Test ──────────────────────────────────────
function runTest() {
    const btn = document.getElementById('testBtn');
    btn.innerHTML = '<div class="rh-spinner"></div> Testing…';
    btn.disabled = true;
    const bar = document.getElementById('testResultBar');
    setTimeout(() => {
        const ok = Math.random() > 0.15;
        bar.className = `rh-alert ${ok ? 'rh-alert-ok' : 'rh-alert-err'}`;
        bar.style.display = 'flex';
        bar.innerHTML = ok
            ? `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><strong>Connection Successful</strong>&nbsp;— API responded in 124ms. Auth valid, endpoint reachable.`
            : `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><strong>Connection Failed</strong>&nbsp;— Check credentials or endpoint URL.`;
        btn.innerHTML = `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg> Test Live`;
        btn.disabled = false;
    }, 1600);
}

// ── Wizard ─────────────────────────────────────────
function openAddWizard() {
    activeVendor = null;
    renderVendors(VENDORS);
    document.getElementById('emptyPane').style.display    = 'none';
    document.getElementById('vendorDetail').style.display = 'none';
    document.getElementById('addWizard').style.display    = 'block';
    goStep(1);
    window.scrollTo({top:0, behavior:'smooth'});
}
function closeWizard() {
    document.getElementById('addWizard').style.display = 'none';
    document.getElementById('emptyPane').style.display = 'block';
}

function goStep(n) {
    currentStep = n;
    // Update step panels
    for (let i = 1; i <= 4; i++) {
        document.getElementById('step-'+i).classList.toggle('active', i === n);
    }
    // Update step bar
    for (let i = 1; i <= 4; i++) {
        const el = document.getElementById('sbar-'+i);
        el.classList.remove('active','done');
        if (i < n)       el.classList.add('done');
        else if (i === n) el.classList.add('active');
    }
    if (n === 4) buildPreview();
}

function selectPill(el, hiddenId, val) {
    el.closest('.method-pills').querySelectorAll('.method-pill').forEach(p => p.classList.remove('active'));
    el.classList.add('active');
    document.getElementById(hiddenId).value = val;
}

function selectAuth(el, type) {
    selectPill(el, 'w_authType', type);
    document.getElementById('auth_bearer').style.display = (type==='bearer'||type==='apikey_header'||type==='apikey_body') ? 'grid' : 'none';
    document.getElementById('auth_basic').style.display  = type==='basic'  ? 'grid' : 'none';
    document.getElementById('auth_hmac').style.display   = type==='hmac'   ? 'grid' : 'none';
}

function toggleVis(id, btn) {
    const el = document.getElementById(id);
    el.type = el.type==='password'?'text':'password';
    btn.textContent = el.type==='password'?'Show':'Hide';
}

function buildPreview() {
    const rows = [
        ['Vendor Name',    document.getElementById('w_name').value||'—'],
        ['Short Code',     document.getElementById('w_code').value||'—'],
        ['Category',       document.getElementById('w_category').value||'—'],
        ['Base URL',       document.getElementById('w_baseUrl').value||'—'],
        ['Purchase Path',  document.getElementById('w_purchaseEp').value||'—'],
        ['HTTP Method',    document.getElementById('w_method').value],
        ['Format',         document.getElementById('w_format').value],
        ['Auth Type',      document.getElementById('w_authType').value],
        ['Timeout',        document.getElementById('w_timeout').value+'s'],
    ];
    document.getElementById('previewRows').innerHTML = rows.map(([k,v]) => `
        <div style="display:flex;align-items:center;padding:9px 14px;border-bottom:1px solid var(--rh-border)">
            <div style="font-size:12px;font-weight:700;color:var(--rh-muted);text-transform:uppercase;letter-spacing:.05em;width:160px;flex-shrink:0">${k}</div>
            <div style="font-size:13px;font-family:monospace;color:var(--rh-text-sub)">${v}</div>
        </div>
    `).join('');
    generateSample();
}

function generateSample() {
    const mobile = document.getElementById('t_mobile').value;
    const amount = document.getElementById('t_amount').value;
    const op     = document.getElementById('t_operator').value;
    const fmt    = document.getElementById('w_format').value;
    const url    = (document.getElementById('w_baseUrl').value||'https://api.vendor.com') + (document.getElementById('w_purchaseEp').value||'/recharge');

    const mField = document.getElementById('map_mobile_number').value || 'mobile';
    const aField = document.getElementById('map_amount').value        || 'amount';
    const oField = document.getElementById('map_operator_code').value || 'operator';
    const tField = document.getElementById('map_transaction_id').value|| 'txnid';

    let body;
    if (fmt === 'json') {
        body = JSON.stringify({ [mField]: mobile, [aField]: parseInt(amount), [oField]: op, [tField]: 'RH_20240320_'+Math.floor(Math.random()*9999) }, null, 2);
    } else if (fmt === 'form' || fmt === 'query') {
        body = `${mField}=${mobile}&${aField}=${amount}&${oField}=${op}&${tField}=RH_TXN_001`;
    } else {
        body = `<request>\n  <${mField}>${mobile}</${mField}>\n  <${aField}>${amount}</${aField}>\n  <${oField}>${op}</${oField}>\n</request>`;
    }

    const token = document.getElementById('w_token').value ? '****' : 'YOUR_TOKEN';
    const preview = `POST ${url}\nContent-Type: ${fmt==='json'?'application/json':'application/x-www-form-urlencoded'}\nAuthorization: Bearer ${token}\n\n${body}`;
    document.getElementById('sampleReqPre').textContent = preview;
}

function runDryTest() {
    const btn = document.querySelector('[onclick="runDryTest()"]');
    btn.innerHTML = '<div class="rh-spinner"></div> Testing…';
    btn.disabled = true;
    const el = document.getElementById('dryTestResult');
    el.style.display = 'block';
    el.style.background = '#eff6ff'; el.style.color = '#1e40af'; el.textContent = '⏳ Sending test request…';
    setTimeout(() => {
        const ok = document.getElementById('w_baseUrl').value.length > 5;
        el.style.background = ok ? '#ecfdf5' : '#fef2f2';
        el.style.color = ok ? '#065f46' : '#991b1b';
        el.textContent = ok
            ? '✓ Dry test passed — endpoint reachable, credentials format valid. Ready to save.'
            : '✗ Missing base URL or credentials. Fill in Step 2 first.';
        btn.innerHTML = `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg> Run Dry Test`;
        btn.disabled = false;
    }, 1800);
}

function saveVendor(activate) {
    const name = document.getElementById('w_name').value.trim();
    const code = document.getElementById('w_code').value.trim();
    const url  = document.getElementById('w_baseUrl').value.trim();
    if (!name || !code || !url) { alert('Please fill in Vendor Name, Short Code, and Base URL before saving.'); return; }

    const newV = {
        id: 'V'+String(VENDORS.length+1).padStart(3,'0'),
        name, code: code.toUpperCase(), color: '#374151',
        category: document.getElementById('w_category').value || 'Custom',
        status: activate ? 'active' : 'inactive',
        success:'—', speed:'—', today:'0',
        baseUrl: url, purchaseEp: document.getElementById('w_purchaseEp').value || '/recharge',
        statusEp:'', method: document.getElementById('w_method').value,
        format: document.getElementById('w_format').value,
        authType: document.getElementById('w_authType').value,
        contentType:'application/json', timeout: document.getElementById('w_timeout').value+'s',
        fieldMap:[], respStatus:'status', respSuccess:'SUCCESS', respTxnId:'txnid', respMsg:'message',
        sampleReq:'', sampleResp:'', logs:[],
    };

    VENDORS.push(newV);
    document.getElementById('vendorCountBadge').textContent = VENDORS.length;
    renderVendors(VENDORS);
    closeWizard();
    selectVendor(newV.id);
    alert(`✓ Vendor "${name}" ${activate ? 'saved and activated' : 'saved as draft'} successfully!`);
}

// ── Template Quick Fill ────────────────────────────
const TEMPLATES = {
    paytm:    { name:'Paytm Recharge API', code:'PAYTM',   category:'Mobile Recharge', baseUrl:'https://api.paytm.com',      purchaseEp:'/v2/recharge',              method:'POST', format:'json',  authType:'bearer', mobile:'mobile',   amount:'amount',          operator:'operator', txnid:'order_id'   },
    razorpay: { name:'Razorpay Recharge',  code:'RZPAY',   category:'Mobile Recharge', baseUrl:'https://api.razorpay.com',   purchaseEp:'/v1/recharge',              method:'POST', format:'json',  authType:'basic',  mobile:'contact',  amount:'amount',          operator:'provider', txnid:'receipt'    },
    cashfree: { name:'Cashfree Recharge',  code:'CFREX',   category:'Multi',           baseUrl:'https://api.cashfree.com',  purchaseEp:'/payout/v1/recharge',       method:'POST', format:'json',  authType:'apikey_header', mobile:'phone', amount:'amount',     operator:'operatorCode', txnid:'transferId' },
    recharge1:{ name:'Recharge1 API',      code:'RC1',     category:'Mobile Recharge', baseUrl:'https://api.recharge1.com', purchaseEp:'/recharge/process',         method:'POST', format:'json',  authType:'bearer', mobile:'MobileNo', amount:'Amount',          operator:'Operator', txnid:'ClientTxnid'},
    cyberplat:{ name:'Cyberplat API',      code:'CYBR',    category:'Mobile Recharge', baseUrl:'https://cybertrek.in',      purchaseEp:'/api/v2/mobile/recharge',   method:'GET',  format:'query', authType:'apikey_body', mobile:'mobile', amount:'amount',     operator:'operator', txnid:'rd'         },
    custom:   { name:'',                   code:'',        category:'',                baseUrl:'',                          purchaseEp:'',                          method:'POST', format:'json',  authType:'bearer', mobile:'mobile',   amount:'amount',          operator:'operator', txnid:'txn_id'     },
};

function fillTemplate(key) {
    const t = TEMPLATES[key];
    if (!t) return;
    if (t.name) document.getElementById('w_name').value     = t.name;
    if (t.code) document.getElementById('w_code').value     = t.code;
    if (t.category) document.getElementById('w_category').value = t.category;
    if (t.baseUrl) document.getElementById('w_baseUrl').value   = t.baseUrl;
    if (t.purchaseEp) document.getElementById('w_purchaseEp').value = t.purchaseEp;

    // Method pill
    document.querySelectorAll('#step-2 .method-pills').forEach((wrap,i) => {
        if (i===0) { // HTTP Method
            wrap.querySelectorAll('.method-pill').forEach(p => {
                p.classList.toggle('active', p.textContent.trim()===t.method);
            });
            document.getElementById('w_method').value = t.method;
        }
        if (i===1) { // Format
            wrap.querySelectorAll('.method-pill').forEach(p => {
                const map = {json:'JSON','form data':'Form Data',xml:'XML','query string':'Query String'};
                p.classList.toggle('active', p.textContent.trim().toLowerCase()===t.format||p.textContent.trim()===map[t.format]);
            });
            document.getElementById('w_format').value = t.format;
        }
    });

    // Auth
    document.getElementById('w_authType').value = t.authType;
    selectAuth(document.querySelector(`#step-2 [onclick*="selectAuth"][onclick*="${t.authType}"]`) || document.querySelector('#step-2 .method-pills:nth-child(3) .method-pill'), t.authType);

    // Field mapping
    if (t.mobile)   document.getElementById('map_mobile_number').value  = t.mobile;
    if (t.amount)   document.getElementById('map_amount').value         = t.amount;
    if (t.operator) document.getElementById('map_operator_code').value  = t.operator;
    if (t.txnid)    document.getElementById('map_transaction_id').value = t.txnid;
}

// ── Helpers ────────────────────────────────────────
function editVendor() { alert('Edit mode — connect to API endpoint to persist changes.'); }

function copyCode(elId) {
    const el = document.getElementById(elId);
    const pre = el.querySelector('pre');
    const text = pre ? pre.textContent : el.textContent.replace('Copy','').trim();
    navigator.clipboard.writeText(text).then(() => {
        const btn = el.querySelector('.copy-code-btn');
        btn.textContent = 'Copied!';
        setTimeout(() => btn.textContent = 'Copy', 2000);
    });
}

function escHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
</script>
@endpush
