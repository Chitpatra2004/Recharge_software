@extends('layouts.admin')
@section('title', 'API Integration Portal')
@section('page-title', 'API Integration Portal')

@section('content')
<style>
/* ── Layout ── */
.portal-wrap{display:flex;gap:16px;height:calc(100vh - 140px);min-height:600px}
.portal-sidebar{width:240px;flex-shrink:0;background:var(--card-bg);border:1px solid var(--border);border-radius:var(--radius);display:flex;flex-direction:column;overflow:hidden}
.portal-sidebar-head{padding:14px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.portal-sidebar-head h3{font-size:13px;font-weight:700;color:var(--text-primary)}
.sidebar-list{flex:1;overflow-y:auto}
.sidebar-api-item{padding:11px 16px;cursor:pointer;border-bottom:1px solid var(--border);transition:background .15s}
.sidebar-api-item:hover{background:var(--bg-page)}
.sidebar-api-item.active{background:var(--primary);color:#fff}
.sidebar-api-item.active .sai-id,.sidebar-api-item.active .sai-name,.sidebar-api-item.active .sai-prov{color:#fff!important}
.sai-id{font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px}
.sai-name{font-size:13px;font-weight:700;color:var(--text-primary);margin:2px 0}
.sai-prov{font-size:11px;color:var(--text-secondary)}
.sai-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
.sai-dot.on{background:#2e7d32}.sai-dot.off{background:#9e9e9e}

/* ── Main panel ── */
.portal-main{flex:1;min-width:0;background:var(--card-bg);border:1px solid var(--border);border-radius:var(--radius);display:flex;flex-direction:column;overflow:hidden}
.portal-top-bar{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;background:var(--card-bg)}
.portal-api-name{font-size:16px;font-weight:700;color:var(--text-primary)}
.portal-api-sub{font-size:12px;color:var(--text-secondary);margin-top:2px}
.callback-url-box{display:flex;align-items:center;gap:8px;background:var(--bg-page);border:1px solid var(--border);border-radius:8px;padding:8px 12px;font-size:12px}
.callback-url-box input{border:none;background:transparent;color:var(--primary);font-size:12px;font-weight:600;outline:none;flex:1;min-width:0}

/* ── Tabs ── */
.portal-tabs{display:flex;border-bottom:1px solid var(--border);overflow-x:auto;background:var(--card-bg)}
.portal-tab{padding:11px 18px;font-size:12px;font-weight:600;color:var(--text-secondary);cursor:pointer;border-bottom:2px solid transparent;white-space:nowrap;transition:color .15s}
.portal-tab:hover{color:var(--text-primary)}
.portal-tab.active{color:var(--primary);border-bottom-color:var(--primary)}
.portal-tab-icon{font-size:14px;margin-right:6px}

/* ── Tab content ── */
.portal-tab-body{flex:1;overflow-y:auto;padding:24px}
.portal-section{display:none}.portal-section.active{display:block}
.portal-section h4{font-size:13px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:16px;padding-bottom:8px;border-bottom:1px solid var(--border)}

/* ── Form fields ── */
.pf-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px 20px}
.pf-full{grid-column:1/-1}
.pf-label{display:block;font-size:11px;font-weight:700;color:var(--text-secondary);margin-bottom:5px;text-transform:uppercase;letter-spacing:.3px}
.pf-input,.pf-select,.pf-textarea{width:100%;border:1px solid var(--border);border-radius:8px;padding:9px 12px;font-size:13px;color:var(--text-primary);background:var(--bg-page);outline:none;box-sizing:border-box;transition:border-color .15s}
.pf-input:focus,.pf-select:focus,.pf-textarea:focus{border-color:var(--primary)}
.pf-textarea{resize:vertical;min-height:72px;font-family:monospace;font-size:12px}
.pf-hint{font-size:11px;color:var(--text-muted);margin-top:4px;line-height:1.4}
.pf-hint code{background:var(--bg-page);border:1px solid var(--border);border-radius:3px;padding:1px 4px;font-size:10px}

/* ── Save row ── */
.pf-save-row{display:flex;align-items:center;gap:10px;margin-top:20px;padding-top:16px;border-top:1px solid var(--border)}
.pf-save-msg{font-size:12px;font-weight:600}
.pf-save-msg.ok{color:#2e7d32}.pf-save-msg.err{color:#c62828}

/* ── Test panel ── */
.test-panel{background:var(--bg-page);border:1px solid var(--border);border-radius:10px;padding:16px;margin-top:20px}
.test-panel h5{font-size:12px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.4px;margin-bottom:12px}
.test-result{font-family:monospace;font-size:12px;background:var(--card-bg);border:1px solid var(--border);border-radius:6px;padding:10px;margin-top:10px;white-space:pre-wrap;max-height:160px;overflow-y:auto;color:var(--text-primary)}
.test-result.ok{border-color:#2e7d32;color:#2e7d32}
.test-result.err{border-color:#c62828;color:#c62828}

/* ── Op codes table ── */
.opcode-table{width:100%;border-collapse:collapse;font-size:13px}
.opcode-table th{padding:8px 12px;background:var(--bg-page);text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--text-secondary);border-bottom:1px solid var(--border)}
.opcode-table td{padding:6px 8px;border-bottom:1px solid var(--border)}
.opcode-table td input{border:1px solid var(--border);border-radius:6px;padding:5px 8px;font-size:12px;color:var(--text-primary);background:var(--bg-page);width:100%;outline:none;box-sizing:border-box}
.opcode-table td input:focus{border-color:var(--primary)}
.opcode-del{background:none;border:none;cursor:pointer;color:#c62828;font-size:16px;padding:4px}

/* ── Empty state ── */
.portal-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;color:var(--text-muted);gap:10px}
.portal-empty svg{opacity:.3}

/* ── ADD modal ── */
.portal-modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:300;align-items:center;justify-content:center}
.portal-modal-overlay.open{display:flex}
.portal-modal{background:var(--card-bg);border-radius:var(--radius);width:480px;max-width:95vw;box-shadow:0 20px 60px rgba(0,0,0,.3)}
.portal-modal-head{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border)}
.portal-modal-head h3{font-size:15px;font-weight:700;color:var(--text-primary)}
.portal-modal-body{padding:20px;display:flex;flex-direction:column;gap:14px}
.portal-modal-foot{padding:14px 20px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:10px}

html[data-dark="1"] .pf-input,
html[data-dark="1"] .pf-select,
html[data-dark="1"] .pf-textarea{background:var(--card-bg)}
html[data-dark="1"] .test-result{background:var(--bg-page)}
</style>

{{-- Breadcrumb --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
    <div>
        <h1 style="font-size:20px;font-weight:700;color:var(--text-primary)">API Integration Portal</h1>
        <div class="breadcrumb" style="margin-bottom:0;margin-top:3px">
            <a href="/admin/dashboard">Dashboard</a>
            <svg class="breadcrumb-sep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span>Manage</span>
            <svg class="breadcrumb-sep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span>API Integration Portal</span>
        </div>
    </div>
    <button class="btn btn-primary btn-sm" onclick="openAddModal()">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Add API Provider
    </button>
</div>

<div class="portal-wrap">
    {{-- ── Sidebar ── --}}
    <div class="portal-sidebar">
        <div class="portal-sidebar-head">
            <h3>API Providers</h3>
            <span id="provider-count" style="font-size:11px;color:var(--text-muted)"></span>
        </div>
        <div class="sidebar-list" id="sidebar-list">
            <div style="padding:32px 16px;text-align:center;color:var(--text-muted);font-size:12px">Loading…</div>
        </div>
    </div>

    {{-- ── Main panel ── --}}
    <div class="portal-main" id="portal-main">
        <div class="portal-empty" id="empty-state">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2" style="width:64px;height:64px">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
            </svg>
            <div style="font-size:14px;font-weight:600">Select an API Provider</div>
            <div style="font-size:12px">Choose from the left panel to configure</div>
        </div>

        <div id="config-panel" style="display:none;flex-direction:column;height:100%">
            {{-- Top bar --}}
            <div class="portal-top-bar">
                <div>
                    <div class="portal-api-name" id="panel-name">—</div>
                    <div class="portal-api-sub" id="panel-sub">—</div>
                </div>
                <div>
                    <div style="font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px">Your Callback URL</div>
                    <div class="callback-url-box">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;flex-shrink:0;color:var(--text-muted)"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        <input type="text" id="callback-url-display" readonly>
                        <button onclick="copyCallback()" style="background:none;border:none;cursor:pointer;color:var(--primary);font-size:11px;font-weight:700;white-space:nowrap">COPY</button>
                    </div>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="portal-tabs" id="portal-tabs">
                <div class="portal-tab active" onclick="switchTab('creds',this)">🔑 Credentials</div>
                <div class="portal-tab" onclick="switchTab('recharge',this)">💳 Recharge API</div>
                <div class="portal-tab" onclick="switchTab('balance',this)">💰 Balance API</div>
                <div class="portal-tab" onclick="switchTab('status',this)">🔍 Status Check</div>
                <div class="portal-tab" onclick="switchTab('complaint',this)">📋 Complaint API</div>
                <div class="portal-tab" onclick="switchTab('callback',this)">📡 Callback</div>
                <div class="portal-tab" onclick="switchTab('opcodes',this)">🗂️ Op Codes</div>
            </div>

            {{-- Tab body --}}
            <div class="portal-tab-body">

                {{-- ① Credentials ── --}}
                <div class="portal-section active" id="sec-creds">
                    <h4>API Credentials</h4>
                    <div class="pf-grid">
                        <div>
                            <label class="pf-label">Username / Mobile *</label>
                            <input class="pf-input" id="cr-username" placeholder="Your registered username or mobile">
                            <div class="pf-hint">Used as <code>[username]</code> in all API params</div>
                        </div>
                        <div>
                            <label class="pf-label">API Token / Key *</label>
                            <input class="pf-input" type="password" id="cr-token" placeholder="Leave blank to keep existing token" autocomplete="new-password">
                            <div class="pf-hint">Used as <code>[apitoken]</code> / <code>[password]</code> / <code>[token]</code></div>
                        </div>
                    </div>
                    <div class="pf-save-row">
                        <button class="btn btn-primary btn-sm" onclick="saveSection('creds')">Save Credentials</button>
                        <span class="pf-save-msg" id="msg-creds"></span>
                    </div>
                </div>

                {{-- ② Recharge API ── --}}
                <div class="portal-section" id="sec-recharge">
                    <h4>Recharge API</h4>
                    <div class="pf-grid">
                        <div>
                            <label class="pf-label">Method *</label>
                            <select class="pf-select" id="ra-method">
                                <option value="GET">GET</option>
                                <option value="POST">POST</option>
                            </select>
                        </div>
                        <div>
                            <label class="pf-label">Status Key *</label>
                            <input class="pf-input" id="ra-status-key" placeholder="status">
                        </div>
                        <div class="pf-full">
                            <label class="pf-label">Recharge API URL *</label>
                            <input class="pf-input" id="ra-url" placeholder="https://pdrs.online/API2/RechargeNew">
                        </div>
                        <div class="pf-full">
                            <label class="pf-label">Request Parameters</label>
                            <textarea class="pf-textarea" id="ra-params" rows="3" placeholder="username=[username]&token=[apitoken]&number=[number]&opcode=[opcode]&amount=[amount]&transid=[transid]&circlecode=*"></textarea>
                            <div class="pf-hint">Placeholders: <code>[username]</code> <code>[apitoken]</code> <code>[number]</code> <code>[amount]</code> <code>[opcode]</code> <code>[transid]</code> <code>[circlecode]</code></div>
                        </div>
                        <div>
                            <label class="pf-label">API TxnId Key *</label>
                            <input class="pf-input" id="ra-txnid-key" placeholder="tid">
                        </div>
                        <div>
                            <label class="pf-label">Live Operator Id Key</label>
                            <input class="pf-input" id="ra-liveid-key" placeholder="operator_id">
                        </div>
                        <div>
                            <label class="pf-label">Success Value *</label>
                            <input class="pf-input" id="ra-success" placeholder="Success">
                        </div>
                        <div>
                            <label class="pf-label">Pending Value</label>
                            <input class="pf-input" id="ra-pending" placeholder="Pending">
                        </div>
                        <div>
                            <label class="pf-label">Failure Value *</label>
                            <input class="pf-input" id="ra-failure" placeholder="Failure">
                        </div>
                    </div>
                    <div class="pf-save-row">
                        <button class="btn btn-primary btn-sm" onclick="saveSection('recharge')">Save Recharge API</button>
                        <span class="pf-save-msg" id="msg-recharge"></span>
                    </div>
                </div>

                {{-- ③ Balance API ── --}}
                <div class="portal-section" id="sec-balance">
                    <h4>Balance Check API</h4>
                    <div class="pf-grid">
                        <div>
                            <label class="pf-label">Method *</label>
                            <select class="pf-select" id="ba-method">
                                <option value="GET">GET</option>
                                <option value="POST">POST</option>
                            </select>
                        </div>
                        <div>
                            <label class="pf-label">Balance Response Key *</label>
                            <input class="pf-input" id="ba-balance-key" placeholder="balance">
                        </div>
                        <div class="pf-full">
                            <label class="pf-label">Balance API URL *</label>
                            <input class="pf-input" id="ba-url" placeholder="https://pdrs.online/API2/Balance">
                        </div>
                        <div class="pf-full">
                            <label class="pf-label">Request Parameters</label>
                            <textarea class="pf-textarea" id="ba-params" placeholder="username=[username]&token=[apitoken]"></textarea>
                            <div class="pf-hint">Placeholders: <code>[username]</code> <code>[apitoken]</code></div>
                        </div>
                    </div>
                    <div class="pf-save-row">
                        <button class="btn btn-primary btn-sm" onclick="saveSection('balance')">Save Balance API</button>
                        <span class="pf-save-msg" id="msg-balance"></span>
                    </div>
                    <div class="test-panel">
                        <h5>Live Test — Check Balance</h5>
                        <button class="btn btn-outline btn-sm" onclick="testBalance()">Check Balance Now</button>
                        <div class="test-result" id="test-balance-result" style="display:none"></div>
                    </div>
                </div>

                {{-- ④ Status Check API ── --}}
                <div class="portal-section" id="sec-status">
                    <h4>Transaction Status Check API</h4>
                    <div class="pf-grid">
                        <div>
                            <label class="pf-label">Method *</label>
                            <select class="pf-select" id="sa-method">
                                <option value="GET">GET</option>
                                <option value="POST">POST</option>
                            </select>
                        </div>
                        <div>
                            <label class="pf-label">Status Response Key *</label>
                            <input class="pf-input" id="sa-status-key" placeholder="status">
                        </div>
                        <div class="pf-full">
                            <label class="pf-label">Status Check URL *</label>
                            <input class="pf-input" id="sa-url" placeholder="https://pdrs.online/API2/status">
                        </div>
                        <div class="pf-full">
                            <label class="pf-label">Request Parameters</label>
                            <textarea class="pf-textarea" id="sa-params" placeholder="userid=[username]&token=[apitoken]&order_id=[order_id]"></textarea>
                            <div class="pf-hint">Placeholders: <code>[username]</code> <code>[apitoken]</code> <code>[order_id]</code></div>
                        </div>
                        <div>
                            <label class="pf-label">TxnId Key</label>
                            <input class="pf-input" id="sa-txnid-key" placeholder="tid">
                        </div>
                    </div>
                    <div class="pf-save-row">
                        <button class="btn btn-primary btn-sm" onclick="saveSection('status')">Save Status API</button>
                        <span class="pf-save-msg" id="msg-status"></span>
                    </div>
                    <div class="test-panel">
                        <h5>Live Test — Check Transaction Status</h5>
                        <div style="display:flex;gap:8px;align-items:center">
                            <input class="pf-input" id="test-order-id" placeholder="Enter Order ID" style="max-width:200px">
                            <button class="btn btn-outline btn-sm" onclick="testStatus()">Check Status</button>
                        </div>
                        <div class="test-result" id="test-status-result" style="display:none"></div>
                    </div>
                </div>

                {{-- ⑤ Complaint API ── --}}
                <div class="portal-section" id="sec-complaint">
                    <h4>Complaint / Dispute API</h4>
                    <div class="pf-grid">
                        <div>
                            <label class="pf-label">Method *</label>
                            <select class="pf-select" id="ca-method">
                                <option value="GET">GET</option>
                                <option value="POST">POST</option>
                            </select>
                        </div>
                        <div class="pf-full">
                            <label class="pf-label">Complaint API URL *</label>
                            <input class="pf-input" id="ca-url" placeholder="https://pdrs.online/API2/complain_api">
                        </div>
                        <div class="pf-full">
                            <label class="pf-label">Request Parameters</label>
                            <textarea class="pf-textarea" id="ca-params" placeholder="username=[username]&token=[apitoken]&order_id=[order_id]&Message=[message]"></textarea>
                            <div class="pf-hint">Placeholders: <code>[username]</code> <code>[apitoken]</code> <code>[order_id]</code> <code>[message]</code></div>
                        </div>
                    </div>
                    <div class="pf-save-row">
                        <button class="btn btn-primary btn-sm" onclick="saveSection('complaint')">Save Complaint API</button>
                        <span class="pf-save-msg" id="msg-complaint"></span>
                    </div>
                    <div class="test-panel">
                        <h5>Live Test — Raise Complaint</h5>
                        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
                            <input class="pf-input" id="test-complaint-order" placeholder="Order ID" style="max-width:160px">
                            <input class="pf-input" id="test-complaint-msg" placeholder="Message" style="max-width:200px">
                            <button class="btn btn-outline btn-sm" onclick="testComplaint()">Send Complaint</button>
                        </div>
                        <div class="test-result" id="test-complaint-result" style="display:none"></div>
                    </div>
                </div>

                {{-- ⑥ Callback Settings ── --}}
                <div class="portal-section" id="sec-callback">
                    <h4>Callback / Webhook Settings</h4>
                    <div style="background:var(--bg-page);border:1.5px solid var(--primary);border-radius:10px;padding:14px 16px;margin-bottom:20px">
                        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--primary);margin-bottom:8px">Your Callback URL (set this in provider dashboard)</div>
                        <div style="display:flex;align-items:center;gap:8px">
                            <input class="pf-input" id="cb-url-big" readonly style="font-family:monospace;font-size:12px;font-weight:600;color:var(--primary)">
                            <button onclick="copyCallback()" class="btn btn-outline btn-sm" style="white-space:nowrap">Copy URL</button>
                        </div>
                    </div>
                    <div style="font-size:12px;color:var(--text-secondary);margin-bottom:16px">Map how the provider sends callback parameters to your system:</div>
                    <div class="pf-grid">
                        <div>
                            <label class="pf-label">Order ID Param Name *</label>
                            <input class="pf-input" id="cb-orderid" placeholder="uniqueid">
                            <div class="pf-hint">The param name provider uses for your order/txn ID (e.g. <code>uniqueid</code>)</div>
                        </div>
                        <div>
                            <label class="pf-label">Status Param Name *</label>
                            <input class="pf-input" id="cb-status" placeholder="status">
                        </div>
                        <div>
                            <label class="pf-label">Provider TxnId Param</label>
                            <input class="pf-input" id="cb-txnid" placeholder="transaction_id">
                        </div>
                        <div>
                            <label class="pf-label">Operator ID Param</label>
                            <input class="pf-input" id="cb-opid" placeholder="operator_id">
                        </div>
                        <div>
                            <label class="pf-label">Success Value *</label>
                            <input class="pf-input" id="cb-success" placeholder="Success">
                        </div>
                        <div>
                            <label class="pf-label">Failure Value *</label>
                            <input class="pf-input" id="cb-failure" placeholder="Failure">
                        </div>
                        <div>
                            <label class="pf-label">Pending Value</label>
                            <input class="pf-input" id="cb-pending" placeholder="Pending">
                        </div>
                    </div>
                    <div class="pf-save-row">
                        <button class="btn btn-primary btn-sm" onclick="saveSection('callback')">Save Callback Settings</button>
                        <span class="pf-save-msg" id="msg-callback"></span>
                    </div>
                </div>

                {{-- ⑦ Operator Codes ── --}}
                <div class="portal-section" id="sec-opcodes">
                    <h4>Operator Code Mapping</h4>
                    <div style="font-size:12px;color:var(--text-secondary);margin-bottom:14px">Map your system's operator codes to this API provider's codes. Used as <code>[opcode]</code> in request params.</div>
                    <div style="overflow-x:auto">
                        <table class="opcode-table" id="opcode-table">
                            <thead>
                                <tr>
                                    <th>Operator Name / Your Code</th>
                                    <th>API Provider Code</th>
                                    <th style="width:40px"></th>
                                </tr>
                            </thead>
                            <tbody id="opcode-tbody"></tbody>
                        </table>
                    </div>
                    <button class="btn btn-outline btn-sm" onclick="addOpCodeRow()" style="margin-top:12px">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Add Row
                    </button>
                    <div class="pf-save-row">
                        <button class="btn btn-primary btn-sm" onclick="saveSection('opcodes')">Save Operator Codes</button>
                        <span class="pf-save-msg" id="msg-opcodes"></span>
                    </div>

                    {{-- Quick-fill PDRS codes --}}
                    <div class="test-panel">
                        <h5>Quick Fill — PDRS Standard Codes</h5>
                        <div style="font-size:12px;color:var(--text-secondary);margin-bottom:10px">Click to pre-populate PDRS operator codes. You can edit them after.</div>
                        <button class="btn btn-outline btn-sm" onclick="fillPdrsCodes()">Load PDRS Codes</button>
                    </div>
                </div>

            </div>{{-- end tab-body --}}
        </div>{{-- end config-panel --}}
    </div>{{-- end portal-main --}}
</div>{{-- end portal-wrap --}}

{{-- ADD modal --}}
<div id="add-modal" class="portal-modal-overlay">
    <div class="portal-modal">
        <div class="portal-modal-head">
            <h3>Add API Provider</h3>
            <button onclick="closeAddModal()" style="background:none;border:none;cursor:pointer;color:var(--text-muted)">✕</button>
        </div>
        <div class="portal-modal-body">
            <div>
                <label class="pf-label">API Name *</label>
                <input class="pf-input" id="add-name" placeholder="e.g. PDRS, SWORLD">
            </div>
            <div>
                <label class="pf-label">API Partner / Company</label>
                <input class="pf-input" id="add-provider" placeholder="e.g. Pdrs For Tradgo">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div>
                    <label class="pf-label">Operator Code *</label>
                    <input class="pf-input" id="add-opcode" placeholder="e.g. ALL">
                </div>
                <div>
                    <label class="pf-label">Recharge Type *</label>
                    <select class="pf-select" id="add-rtype">
                        <option value="prepaid">Prepaid</option>
                        <option value="postpaid">Postpaid</option>
                        <option value="dth">DTH</option>
                        <option value="bbps">BBPS</option>
                        <option value="fastag">Fastag</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="portal-modal-foot">
            <button class="btn btn-outline btn-sm" onclick="closeAddModal()">Cancel</button>
            <button class="btn btn-primary btn-sm" id="add-btn" onclick="addProvider()">Add Provider</button>
        </div>
    </div>
</div>

<script>
const API  = '/api/v1/employee';
const tok  = () => localStorage.getItem('emp_token') || '';
const head = () => ({'Authorization':'Bearer '+tok(),'Content-Type':'application/json','Accept':'application/json'});
const APP_URL = '{{ rtrim(config("app.url"), "/") }}';

let _currentId  = null;
let _providers  = [];

// ── PDRS standard codes reference ────────────────────────────────────────────
const PDRS_CODES = [
    {our:'JIO',api:'JIO'},{our:'JIOL',api:'Jiol'},{our:'AIRTEL',api:'RA'},
    {our:'AIRTEL_LIVE',api:'ATL'},{our:'IDEA',api:'RI'},{our:'VI',api:'RI'},
    {our:'VODAFONE',api:'RV'},{our:'BSNL_STV',api:'TB'},{our:'BSNL',api:'RB'},
    {our:'MTNL',api:'MTNL'},{our:'DA',api:'DA'},{our:'DD',api:'DD'},
    {our:'DS',api:'DS'},{our:'DT',api:'DT'},{our:'DV',api:'DV'},
    {our:'PA',api:'PA'},{our:'PB',api:'PB'},{our:'PJIO',api:'PJIO'},
    {our:'PI',api:'PI'},{our:'PV',api:'PV'},
];

// ── Load providers ────────────────────────────────────────────────────────────
async function loadProviders() {
    const r = await fetch(`${API}/api-providers`, {headers:{...head()}});
    const d = await r.json();
    _providers = d.routes || [];
    document.getElementById('provider-count').textContent = _providers.length + ' APIs';
    renderSidebar();
}

function renderSidebar() {
    const el = document.getElementById('sidebar-list');
    if (!_providers.length) {
        el.innerHTML = '<div style="padding:32px 16px;text-align:center;color:var(--text-muted);font-size:12px">No API providers yet.</div>';
        return;
    }
    el.innerHTML = _providers.map(p => `
        <div class="sidebar-api-item ${_currentId === p.id ? 'active' : ''}" onclick="selectProvider(${p.id})">
            <div style="display:flex;align-items:center;gap:8px;justify-content:space-between">
                <span class="sai-id">${p.api_id}</span>
                <span class="sai-dot ${p.is_active ? 'on' : 'off'}"></span>
            </div>
            <div class="sai-name">${esc(p.name)}</div>
            <div class="sai-prov">${esc(p.api_provider)}</div>
        </div>
    `).join('');
}

// ── Select provider & load full config ───────────────────────────────────────
async function selectProvider(id) {
    _currentId = id;
    renderSidebar();

    document.getElementById('empty-state').style.display   = 'none';
    document.getElementById('config-panel').style.display  = 'flex';

    const p = _providers.find(x => x.id === id);
    document.getElementById('panel-name').textContent = p?.name     || '—';
    document.getElementById('panel-sub').textContent  = p?.api_provider || '—';

    const cbUrl = `${APP_URL}/api/v1/recharge/pdrs-callback`;
    document.getElementById('callback-url-display').value = cbUrl;
    document.getElementById('cb-url-big').value           = cbUrl;

    // reset tabs
    switchTab('creds', document.querySelector('.portal-tab'));

    // fetch full config
    try {
        const r = await fetch(`${API}/api-providers/${id}/full-config`, {headers:{...head()}});
        const d = await r.json();
        fillForms(d);
    } catch(e) { showMsg('msg-creds', 'Failed to load config.', false); }
}

function fillForms(d) {
    const c  = d.credentials    || {};
    const ra = d.recharge_api   || {};
    const ba = d.balance_api    || {};
    const sa = d.status_api     || {};
    const ca = d.complaint_api  || {};
    const cb = d.callback       || {};
    const oc = d.op_codes       || {};

    // Credentials
    set('cr-username', c.username || '');
    set('cr-token', ''); // never pre-fill token

    // Recharge API
    set('ra-method', ra.method || 'GET', true);
    set('ra-url', ra.url || '');
    set('ra-params', ra.params || '');
    set('ra-status-key', ra.status_key || 'status');
    set('ra-txnid-key', ra.txnid_key || 'tid');
    set('ra-liveid-key', ra.live_id_key || 'operator_id');
    set('ra-success', ra.success_val || 'Success');
    set('ra-pending', ra.pending_val || 'Pending');
    set('ra-failure', ra.failure_val || 'Failure');

    // Balance API
    set('ba-method', ba.method || 'GET', true);
    set('ba-url', ba.url || '');
    set('ba-params', ba.params || '');
    set('ba-balance-key', ba.balance_key || 'balance');

    // Status API
    set('sa-method', sa.method || 'GET', true);
    set('sa-url', sa.url || '');
    set('sa-params', sa.params || '');
    set('sa-status-key', sa.status_key || 'status');
    set('sa-txnid-key', sa.txnid_key || 'tid');

    // Complaint API
    set('ca-method', ca.method || 'GET', true);
    set('ca-url', ca.url || '');
    set('ca-params', ca.params || '');

    // Callback
    set('cb-orderid',  cb.order_id_param || 'uniqueid');
    set('cb-status',   cb.status_param   || 'status');
    set('cb-txnid',    cb.txnid_param    || 'transaction_id');
    set('cb-opid',     cb.op_id_param    || 'operator_id');
    set('cb-success',  cb.success_val    || 'Success');
    set('cb-failure',  cb.failure_val    || 'Failure');
    set('cb-pending',  cb.pending_val    || 'Pending');

    // Op codes
    renderOpCodes(oc);
}

// ── Save sections ─────────────────────────────────────────────────────────────
async function saveSection(section) {
    if (!_currentId) return;
    const id = _currentId;
    let url, body;

    if (section === 'creds') {
        const token = get('cr-token');
        body = {username: get('cr-username')};
        if (token) body.api_token = token;
        url = `${API}/api-providers/${id}/credentials`;
    } else if (section === 'recharge') {
        body = {method:get('ra-method'),url:get('ra-url'),params:get('ra-params'),
                status_key:get('ra-status-key'),txnid_key:get('ra-txnid-key'),
                live_id_key:get('ra-liveid-key'),success_val:get('ra-success'),
                pending_val:get('ra-pending'),failure_val:get('ra-failure')};
        url = `${API}/api-providers/${id}/recharge-api`;
    } else if (section === 'balance') {
        body = {method:get('ba-method'),url:get('ba-url'),params:get('ba-params'),balance_key:get('ba-balance-key')};
        url = `${API}/api-providers/${id}/balance-api`;
    } else if (section === 'status') {
        body = {method:get('sa-method'),url:get('sa-url'),params:get('sa-params'),
                status_key:get('sa-status-key'),txnid_key:get('sa-txnid-key')};
        url = `${API}/api-providers/${id}/status-api`;
    } else if (section === 'complaint') {
        body = {method:get('ca-method'),url:get('ca-url'),params:get('ca-params')};
        url = `${API}/api-providers/${id}/complaint-api`;
    } else if (section === 'callback') {
        body = {order_id_param:get('cb-orderid'),status_param:get('cb-status'),
                txnid_param:get('cb-txnid'),op_id_param:get('cb-opid'),
                success_val:get('cb-success'),failure_val:get('cb-failure'),pending_val:get('cb-pending')};
        url = `${API}/api-providers/${id}/callback`;
    } else if (section === 'opcodes') {
        const rows = document.querySelectorAll('#opcode-tbody tr');
        const codes = [];
        rows.forEach(row => {
            const our = row.querySelector('.oc-our')?.value?.trim();
            const api = row.querySelector('.oc-api')?.value?.trim();
            if (our && api) codes.push({our_code: our, api_code: api});
        });
        body = {codes};
        url = `${API}/api-providers/${id}/op-codes`;
    }

    const msgId = `msg-${section === 'opcodes' ? 'opcodes' : section}`;
    try {
        const r = await fetch(url, {method:'PUT', headers:head(), body:JSON.stringify(body)});
        const d = await r.json();
        showMsg(msgId, r.ok ? '✓ ' + d.message : '✗ ' + (d.message || 'Error'), r.ok);
    } catch(e) { showMsg(msgId, '✗ Request failed', false); }
}

// ── Live tests ────────────────────────────────────────────────────────────────
async function testBalance() {
    if (!_currentId) return;
    const el = document.getElementById('test-balance-result');
    el.style.display = 'block'; el.className = 'test-result'; el.textContent = 'Checking balance…';
    try {
        const r = await fetch(`${API}/api-providers/${_currentId}/test-balance`, {headers:{...head()}});
        const d = await r.json();
        el.className = 'test-result ' + (r.ok ? 'ok' : 'err');
        el.textContent = JSON.stringify(d, null, 2);
    } catch(e) { el.className = 'test-result err'; el.textContent = 'Request failed: ' + e.message; }
}

async function testStatus() {
    if (!_currentId) return;
    const oid = document.getElementById('test-order-id').value.trim();
    if (!oid) { alert('Enter an Order ID'); return; }
    const el = document.getElementById('test-status-result');
    el.style.display = 'block'; el.className = 'test-result'; el.textContent = 'Checking…';
    try {
        const r = await fetch(`${API}/api-providers/${_currentId}/test-status?order_id=${encodeURIComponent(oid)}`, {headers:{...head()}});
        const d = await r.json();
        el.className = 'test-result ' + (r.ok ? 'ok' : 'err');
        el.textContent = JSON.stringify(d, null, 2);
    } catch(e) { el.className = 'test-result err'; el.textContent = 'Request failed: ' + e.message; }
}

async function testComplaint() {
    if (!_currentId) return;
    const oid = document.getElementById('test-complaint-order').value.trim();
    const msg = document.getElementById('test-complaint-msg').value.trim() || 'complain';
    if (!oid) { alert('Enter an Order ID'); return; }
    const el = document.getElementById('test-complaint-result');
    el.style.display = 'block'; el.className = 'test-result'; el.textContent = 'Sending…';
    try {
        const r = await fetch(`${API}/api-providers/${_currentId}/test-complaint`, {
            method:'POST', headers:head(), body:JSON.stringify({order_id:oid, message:msg})
        });
        const d = await r.json();
        el.className = 'test-result ' + (d.success ? 'ok' : 'err');
        el.textContent = JSON.stringify(d, null, 2);
    } catch(e) { el.className = 'test-result err'; el.textContent = 'Request failed: ' + e.message; }
}

// ── Op codes table ────────────────────────────────────────────────────────────
function renderOpCodes(codes) {
    const tbody = document.getElementById('opcode-tbody');
    const entries = Object.entries(codes);
    if (!entries.length) { tbody.innerHTML = ''; addOpCodeRow(); return; }
    tbody.innerHTML = entries.map(([our, api]) => opCodeRow(our, api)).join('');
}

function opCodeRow(our = '', api = '') {
    return `<tr>
        <td><input class="oc-our" value="${esc(our)}" placeholder="Our code (e.g. JIO)"></td>
        <td><input class="oc-api" value="${esc(api)}" placeholder="API code (e.g. JIO)"></td>
        <td><button class="opcode-del" onclick="this.closest('tr').remove()">✕</button></td>
    </tr>`;
}

function addOpCodeRow() {
    document.getElementById('opcode-tbody').insertAdjacentHTML('beforeend', opCodeRow());
}

function fillPdrsCodes() {
    document.getElementById('opcode-tbody').innerHTML = PDRS_CODES.map(c => opCodeRow(c.our, c.api)).join('');
}

// ── Tabs ──────────────────────────────────────────────────────────────────────
function switchTab(name, el) {
    document.querySelectorAll('.portal-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.portal-tab').forEach(t => t.classList.remove('active'));
    document.getElementById('sec-' + name).classList.add('active');
    if (el) el.classList.add('active');
    else document.querySelectorAll('.portal-tab').forEach((t,i) => {
        if (['creds','recharge','balance','status','complaint','callback','opcodes'][i] === name) t.classList.add('active');
    });
}

// ── Add provider modal ────────────────────────────────────────────────────────
function openAddModal() { document.getElementById('add-modal').classList.add('open'); }
function closeAddModal() { document.getElementById('add-modal').classList.remove('open'); }

async function addProvider() {
    const body = {
        name:          document.getElementById('add-name').value.trim(),
        api_provider:  document.getElementById('add-provider').value.trim(),
        operator_code: document.getElementById('add-opcode').value.trim(),
        recharge_type: document.getElementById('add-rtype').value,
    };
    if (!body.name || !body.operator_code) { alert('Name and Operator Code are required.'); return; }
    const btn = document.getElementById('add-btn');
    btn.disabled = true; btn.textContent = 'Adding…';
    try {
        const r = await fetch(`${API}/api-providers`, {method:'POST', headers:head(), body:JSON.stringify(body)});
        const d = await r.json();
        if (!r.ok) { alert(d.message || 'Failed.'); return; }
        closeAddModal();
        await loadProviders();
        selectProvider(d.id);
    } catch(e) { alert('Request failed.'); }
    finally { btn.disabled = false; btn.textContent = 'Add Provider'; }
}

// ── Copy callback URL ─────────────────────────────────────────────────────────
function copyCallback() {
    const url = document.getElementById('callback-url-display').value;
    navigator.clipboard.writeText(url).then(() => showToast('Callback URL copied!'));
}

// ── Helpers ───────────────────────────────────────────────────────────────────
const get = id => document.getElementById(id)?.value ?? '';
function set(id, val, isSelect = false) {
    const el = document.getElementById(id);
    if (!el) return;
    el.value = val;
}
function showMsg(id, msg, ok) {
    const el = document.getElementById(id);
    if (!el) return;
    el.textContent = msg; el.className = 'pf-save-msg ' + (ok ? 'ok' : 'err');
    setTimeout(() => { if (el) el.textContent = ''; }, 4000);
}
function showToast(msg) {
    const t = document.createElement('div');
    t.textContent = msg;
    Object.assign(t.style, {position:'fixed',bottom:'24px',right:'24px',background:'#1b5e20',color:'#fff',padding:'10px 20px',borderRadius:'8px',fontWeight:'600',fontSize:'13px',zIndex:9999,boxShadow:'0 4px 16px rgba(0,0,0,.2)'});
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3000);
}
function esc(s) { return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

// Close modal on backdrop click
document.getElementById('add-modal').addEventListener('click', function(e) {
    if (e.target === this) closeAddModal();
});

// Boot
loadProviders();
</script>
@endsection
