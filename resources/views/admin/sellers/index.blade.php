@extends('layouts.admin')
@section('title','Sellers')

@push('head')
<style>
.filter-bar{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:14px}
.filter-bar input,.filter-bar select{padding:7px 11px;border:1px solid var(--border);border-radius:8px;font-size:13px;background:#fff;color:#1e293b}
.summary-strip{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px}
@media(max-width:1100px){.summary-strip{grid-template-columns:1fr 1fr}}
@media(max-width:700px){.summary-strip{grid-template-columns:1fr 1fr}}
.stat-card{background:var(--card-bg);border-radius:var(--radius);padding:16px 20px;box-shadow:var(--shadow-sm);border-left:4px solid transparent}
.stat-card.blue{border-color:var(--accent-blue)}
.stat-card.green{border-color:var(--accent-green)}
.stat-card.orange{border-color:var(--accent-orange)}
.stat-card.purple{border-color:var(--accent-purple)}
.stat-card.red{border-color:#ef4444}
.stat-card .val{font-size:22px;font-weight:700;margin-bottom:2px;color:#1e293b}
.stat-card .lbl{font-size:11.5px;color:#64748b}
.avatar{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff;flex-shrink:0}
.doc-badge{display:inline-flex;align-items:center;gap:3px;padding:2px 7px;border-radius:12px;font-size:10.5px;font-weight:600;margin-right:2px}
.doc-yes{background:#d1fae5;color:#065f46}
.doc-no{background:#fee2e2;color:#991b1b}
tr.pending-row td{background:#fffbeb!important}
tr.pending-row:hover td{background:#fef3c7!important}
.seller-name{color:#1e293b;font-weight:700;font-size:13px;cursor:default;line-height:1.3}
.seller-email{font-size:11.5px;color:#64748b;margin-top:1px}
.seller-joined{font-size:11px;color:#94a3b8;margin-top:1px}

/* ── TABS ── */
.role-tabs{display:flex;gap:0;border-bottom:2px solid var(--border);margin-bottom:20px}
.role-tab{padding:10px 22px;font-size:13.5px;font-weight:600;cursor:pointer;border:none;background:none;color:var(--text-secondary);border-bottom:3px solid transparent;margin-bottom:-2px;transition:all .15s;display:flex;align-items:center;gap:8px;font-family:inherit}
.role-tab:hover{color:var(--text-primary)}
.role-tab.active{color:var(--accent-blue);border-bottom-color:var(--accent-blue)}
.role-tab .tab-count{font-size:11px;font-weight:700;padding:2px 8px;border-radius:20px;background:#f1f5f9;color:#64748b}
.role-tab.active .tab-count{background:#dbeafe;color:#2563eb}
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Sellers &amp; API Users</h1>
        <p class="page-sub">Manage registration approvals, wallets, and API access</p>
    </div>
    <div style="display:flex;gap:8px">
        <button class="btn btn-outline" onclick="exportSellers()">Export</button>
        <button class="btn btn-primary" onclick="showAddModal()">+ Add Seller</button>
    </div>
</div>

{{-- Role Tabs --}}
<div class="role-tabs">
    <button class="role-tab active" id="tab-api_user" onclick="switchTab('api_user')">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:15px;height:15px">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
        </svg>
        API Users
        <span class="tab-count" id="tab-count-api_user">—</span>
    </button>
    <button class="role-tab" id="tab-retailer" onclick="switchTab('retailer')">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:15px;height:15px">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
        </svg>
        Retailers
        <span class="tab-count" id="tab-count-retailer">—</span>
    </button>
</div>

{{-- Summary Strip --}}
<div class="summary-strip">
    <div class="stat-card blue"><div class="val" id="sTotal">—</div><div class="lbl">Total</div></div>
    <div class="stat-card orange" style="cursor:pointer" onclick="filterByStatus('inactive')" title="Click to filter pending">
        <div class="val" id="sPending">—</div><div class="lbl">⏳ Pending Approval</div>
    </div>
    <div class="stat-card green"><div class="val" id="sActive">—</div><div class="lbl">Active</div></div>
    <div class="stat-card red"><div class="val" id="sSuspended">—</div><div class="lbl">Suspended / Rejected</div></div>
</div>

<div class="card">
    <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
        <span id="tableTitle" style="font-weight:600;font-size:14px">API User Accounts</span>
        <div class="filter-bar" style="margin:0">
            <input type="text" id="searchSeller" placeholder="Search name / mobile / email…" oninput="filterTable()">
            <select id="fStatus" onchange="filterTable()">
                <option value="">All Status</option>
                <option value="inactive">Pending Approval</option>
                <option value="active">Active</option>
                <option value="suspended">Suspended</option>
            </select>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th id="thName">API User</th>
                    <th>Mobile</th>
                    <th>Documents</th>
                    <th id="thApiKey">API Key</th>
                    <th>Wallet</th>
                    <th>Recharges</th>
                    <th>API Setting</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="sellerBody">
                <tr><td colspan="9" style="text-align:center;padding:30px;color:var(--text-muted)">Loading...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<div id="sellerPagination"></div>

{{-- Integration Decision Modal --}}
<div id="intgModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:500;align-items:center;justify-content:center">
    <div class="card" style="width:420px;max-width:95vw;padding:24px">
        <h3 style="font-size:16px;font-weight:700;margin-bottom:6px">API Integration Request</h3>
        <p style="font-size:13px;color:var(--text-secondary);margin-bottom:16px">Seller: <strong id="intg-seller-name"></strong></p>
        <div id="intg-summary" style="background:#f8fafc;border-radius:10px;padding:12px 14px;font-size:12px;color:#334155;margin-bottom:14px;display:grid;gap:8px">
            <div>Loading integration details...</div>
        </div>
        <div style="margin-bottom:14px">
            <label style="display:block;font-size:12px;font-weight:600;margin-bottom:5px">Notes / Reason (required for rejection)</label>
            <textarea id="intg-notes" rows="3" placeholder="Enter notes for seller…" style="width:100%;padding:8px 11px;border:1px solid var(--border);border-radius:8px;font-size:13px;resize:vertical"></textarea>
        </div>
        <div style="display:flex;gap:10px">
            <button onclick="decideIntg('approve')" style="flex:1;padding:9px;background:#10b981;color:#fff;border:none;border-radius:8px;font-size:13.5px;font-weight:700;cursor:pointer">Approve</button>
            <button onclick="decideIntg('reject')" style="flex:1;padding:9px;background:#ef4444;color:#fff;border:none;border-radius:8px;font-size:13.5px;font-weight:700;cursor:pointer">Reject</button>
            <button onclick="closeIntgModal()" style="padding:9px 16px;border:1px solid var(--border);border-radius:8px;font-size:13px;cursor:pointer;background:#fff">Cancel</button>
        </div>
    </div>
</div>

{{-- Add Seller Modal --}}
<div id="addModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:500;align-items:center;justify-content:center">
    <div class="card" style="width:480px;max-width:95vw;padding:24px;max-height:90vh;overflow-y:auto">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h3 style="font-size:16px;font-weight:700">Add New Seller</h3>
            <button onclick="document.getElementById('addModal').style.display='none'" style="background:none;border:none;cursor:pointer;font-size:20px;color:var(--text-muted)">&times;</button>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
            <div>
                <label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px">Full Name *</label>
                <input type="text" placeholder="Seller name" style="width:100%;padding:8px 11px;border:1px solid var(--border);border-radius:8px;font-size:13px">
            </div>
            <div>
                <label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px">Mobile *</label>
                <input type="tel" placeholder="10-digit mobile" style="width:100%;padding:8px 11px;border:1px solid var(--border);border-radius:8px;font-size:13px">
            </div>
            <div>
                <label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px">Email</label>
                <input type="email" placeholder="email@example.com" style="width:100%;padding:8px 11px;border:1px solid var(--border);border-radius:8px;font-size:13px">
            </div>
            <div>
                <label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px">Commission % *</label>
                <input type="number" placeholder="e.g. 2.5" step="0.1" style="width:100%;padding:8px 11px;border:1px solid var(--border);border-radius:8px;font-size:13px">
            </div>
            <div style="grid-column:1/-1">
                <label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px">Business Name</label>
                <input type="text" placeholder="Shop / company name" style="width:100%;padding:8px 11px;border:1px solid var(--border);border-radius:8px;font-size:13px">
            </div>
        </div>
        <div style="display:flex;gap:10px;margin-top:20px">
            <button class="btn btn-primary" onclick="submitAdd()">Create Seller</button>
            <button class="btn btn-outline" onclick="document.getElementById('addModal').style.display='none'">Cancel</button>
        </div>
    </div>
</div>

{{-- Wallet Adjust Modal --}}
<div id="walletModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:600;align-items:center;justify-content:center">
    <div class="card" style="width:440px;max-width:95vw;padding:26px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px">
            <h3 id="walletModalTitle" style="font-size:16px;font-weight:700">Add Balance</h3>
            <button onclick="closeWalletModal()" style="background:none;border:none;cursor:pointer;font-size:22px;color:var(--text-muted)">&times;</button>
        </div>
        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;margin-bottom:18px;font-size:13px">
            <div style="color:var(--text-secondary);margin-bottom:3px">Seller</div>
            <div id="walletSellerName" style="font-weight:700;font-size:15px"></div>
            <div style="margin-top:8px;color:var(--text-secondary)">Current Balance</div>
            <div id="walletCurrentBal" style="font-weight:700;font-size:18px;color:#10b981"></div>
        </div>
        <div style="margin-bottom:14px">
            <label style="display:block;font-size:12px;font-weight:600;margin-bottom:5px">Amount (₹) *</label>
            <input id="walletAmount" type="number" min="1" step="0.01" placeholder="Enter amount" style="width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px">
        </div>
        <div style="margin-bottom:20px">
            <label style="display:block;font-size:12px;font-weight:600;margin-bottom:5px">Reason / Description *</label>
            <input id="walletDesc" type="text" placeholder="e.g. Manual topup by admin" style="width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:8px;font-size:13px">
        </div>
        <div style="display:flex;gap:10px">
            <button id="walletSubmitBtn" onclick="submitWalletAdjust()" style="flex:1;padding:10px;border:none;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer;color:#fff">Confirm</button>
            <button onclick="closeWalletModal()" style="padding:10px 18px;border:1px solid var(--border);border-radius:8px;font-size:13px;cursor:pointer;background:#fff">Cancel</button>
        </div>
    </div>
</div>

{{-- Approve/Reject Modal --}}
<div id="approveModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:600;align-items:center;justify-content:center">
    <div class="card" style="width:440px;max-width:95vw;padding:26px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
            <h3 id="approveModalTitle" style="font-size:16px;font-weight:700">Approve Registration</h3>
            <button onclick="closeApproveModal()" style="background:none;border:none;cursor:pointer;font-size:22px;color:var(--text-muted)">&times;</button>
        </div>
        <div style="background:#f8fafc;border-radius:10px;padding:14px;margin-bottom:16px;font-size:13px">
            <div style="color:var(--text-secondary)">Applicant</div>
            <div id="approveModalName" style="font-weight:700;font-size:15px;margin-top:2px"></div>
            <div id="approveModalDocs" style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap"></div>
        </div>
        <div id="approveNotesWrap" style="margin-bottom:16px;display:none">
            <label style="display:block;font-size:12px;font-weight:600;margin-bottom:5px">Rejection Reason *</label>
            <textarea id="approveNotes" rows="3" placeholder="Enter reason for rejection…" style="width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:8px;font-size:13px;resize:vertical"></textarea>
        </div>
        <div style="display:flex;gap:10px">
            <button id="approveConfirmBtn" onclick="submitApproveDecision()" style="flex:1;padding:10px;border:none;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer;color:#fff;background:#10b981">Confirm Approval</button>
            <button onclick="closeApproveModal()" style="padding:10px 18px;border:1px solid var(--border);border-radius:8px;font-size:13px;cursor:pointer;background:#fff">Cancel</button>
        </div>
    </div>
</div>

{{-- Generate API Key Modal --}}
<div id="genKeyModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:600;align-items:center;justify-content:center">
    <div class="card" style="width:480px;max-width:95vw;padding:26px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px">
            <h3 style="font-size:16px;font-weight:700">Generate API Key</h3>
            <button onclick="closeGenKeyModal()" style="background:none;border:none;cursor:pointer;font-size:22px;color:var(--text-muted)">&times;</button>
        </div>
        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;margin-bottom:18px;font-size:13px">
            <div style="color:var(--text-secondary);margin-bottom:3px">Seller</div>
            <div id="genKeySellerName" style="font-weight:700;font-size:15px"></div>
        </div>
        <div id="genKeyResult" style="display:none;margin-bottom:18px">
            <div style="font-size:12px;font-weight:600;color:#065f46;margin-bottom:8px">Generated API Key (copy now — shown only once):</div>
            <div style="background:#1e293b;border-radius:8px;padding:12px 14px;display:flex;align-items:center;gap:10px">
                <span id="genKeyValue" style="font-family:monospace;font-size:12.5px;color:#e2e8f0;flex:1;word-break:break-all"></span>
                <button onclick="copyGenKey()" style="background:rgba(255,255,255,.15);border:none;color:#94a3b8;border-radius:5px;padding:4px 10px;font-size:11px;cursor:pointer">Copy</button>
            </div>
        </div>
        <div style="display:flex;gap:10px">
            <button id="genKeySubmitBtn" onclick="submitGenKey()" style="flex:1;padding:10px;background:#7c3aed;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer">Generate Key</button>
            <button onclick="closeGenKeyModal()" style="padding:10px 18px;border:1px solid var(--border);border-radius:8px;font-size:13px;cursor:pointer;background:#fff">Close</button>
        </div>
    </div>
</div>

{{-- API Config Modal (admin view/update seller's integration) --}}
<div id="apiCfgModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:600;align-items:center;justify-content:center">
    <div class="card" style="width:580px;max-width:95vw;padding:26px;max-height:90vh;overflow-y:auto">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px">
            <h3 style="font-size:16px;font-weight:700">API Config — <span id="apiCfgSellerName"></span></h3>
            <button onclick="closeApiCfgModal()" style="background:none;border:none;cursor:pointer;font-size:22px;color:var(--text-muted)">&times;</button>
        </div>

        {{-- Server Info Box --}}
        <div id="apiCfgServerInfo" style="background:linear-gradient(135deg,#1e3a5f,#2563eb);border-radius:10px;padding:14px 16px;margin-bottom:18px;color:#fff;font-size:13px">
            <div style="font-size:10.5px;font-weight:700;opacity:.7;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px">Platform Credentials for This Seller</div>
            <div style="margin-bottom:6px">
                <span style="opacity:.7;font-size:11px">Server IP:</span>
                <span id="apiCfgServerIp" style="font-family:monospace;font-weight:600;margin-left:8px"></span>
            </div>
            <div>
                <span style="opacity:.7;font-size:11px">Unique Callback URL:</span>
                <span id="apiCfgCallbackUrl" style="font-family:monospace;font-weight:600;margin-left:8px;font-size:11.5px;word-break:break-all"></span>
            </div>
        </div>

        <div id="apiCfgLoading" style="text-align:center;padding:20px;color:#64748b">Loading integration details…</div>
        <div id="apiCfgForm" style="display:none">
            <div style="display:grid;grid-template-columns:1fr;gap:12px;margin-bottom:16px">
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px">Website URL *</label>
                    <input id="cfg-website" type="url" placeholder="https://yourdomain.com" style="width:100%;padding:8px 11px;border:1px solid var(--border);border-radius:8px;font-size:13px">
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px">Seller Callback URL * <span style="font-size:11px;color:#94a3b8">(we call this to notify seller)</span></label>
                    <input id="cfg-callback" type="url" placeholder="https://yourdomain.com/recharge/callback" style="width:100%;padding:8px 11px;border:1px solid var(--border);border-radius:8px;font-size:13px">
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px">Status Check URL *</label>
                    <input id="cfg-status-check" type="url" placeholder="https://yourdomain.com/recharge/status" style="width:100%;padding:8px 11px;border:1px solid var(--border);border-radius:8px;font-size:13px">
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px">Dispute URL *</label>
                    <input id="cfg-dispute" type="url" placeholder="https://yourdomain.com/recharge/dispute" style="width:100%;padding:8px 11px;border:1px solid var(--border);border-radius:8px;font-size:13px">
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px">Allowed IPs *</label>
                    <textarea id="cfg-ips" rows="3" placeholder="203.0.113.42&#10;198.51.100.0/24" style="width:100%;padding:8px 11px;border:1px solid var(--border);border-radius:8px;font-size:13px;resize:vertical"></textarea>
                </div>
            </div>
            <div style="display:flex;gap:10px">
                <button id="apiCfgSaveBtn" onclick="saveApiCfg()" style="flex:1;padding:10px;background:#0891b2;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer">Save Config</button>
                <button onclick="closeApiCfgModal()" style="padding:10px 18px;border:1px solid var(--border);border-radius:8px;font-size:13px;cursor:pointer;background:#fff">Cancel</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const TOKEN = ()=>localStorage.getItem('emp_token');
const empFetch = (url,method='GET',body=null)=>fetch(url,{method,headers:{'Authorization':'Bearer '+TOKEN(),'Content-Type':'application/json','Accept':'application/json'},...(body?{body:JSON.stringify(body)}:{})}).then(async r=>{const d=await r.json();if(!r.ok)throw new Error(d.message||'Error');return d;});

const COLORS=['#2563eb','#10b981','#f59e0b','#ef4444','#7c3aed','#0891b2'];
let allSellers=[], currentPage=1, activeTab='api_user';

function fmtMoney(n){return Number(n||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2});}
function fmtDate(d){if(!d)return'—';return new Date(d).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'});}

function statusBadge(r){
    const s=r.status, a=r.approval_status;
    if(a==='pending'||s==='inactive')
        return '<span style="background:#fef3c7;color:#92400e;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:600">⏳ Pending</span>';
    if(a==='rejected'||s==='suspended')
        return '<span style="background:#fee2e2;color:#991b1b;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:600">Rejected</span>';
    if(s==='active')
        return '<span style="background:#d1fae5;color:#065f46;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:600">✓ Active</span>';
    return `<span style="background:#f1f5f9;color:#64748b;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:600">${s}</span>`;
}

function docBadges(r){
    let h='';
    h+=r.has_pan?'<span class="doc-badge doc-yes">✓ PAN</span>':'<span class="doc-badge doc-no">✗ PAN</span>';
    h+=r.has_gst?'<span class="doc-badge doc-yes">✓ GST</span>':'<span class="doc-badge doc-no">✗ GST</span>';
    if(r.has_document) h+='<span class="doc-badge doc-yes">✓ Doc</span>';
    return h;
}

function intgBadge(s){
    if(!s||s==='none')   return '<span style="font-size:11.5px;color:#94a3b8">—</span>';
    if(s==='pending')    return '<span style="background:#fef3c7;color:#92400e;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600">Pending</span>';
    if(s==='approved')   return '<span style="background:#d1fae5;color:#065f46;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600">Approved</span>';
    if(s==='rejected')   return '<span style="background:#fee2e2;color:#991b1b;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600">Rejected</span>';
    return s;
}

function switchTab(role){
    activeTab = role;
    // Update tab UI
    document.querySelectorAll('.role-tab').forEach(t => t.classList.remove('active'));
    document.getElementById('tab-' + role).classList.add('active');
    // Update table heading & column header
    const isApi = role === 'api_user';
    document.getElementById('tableTitle').textContent = isApi ? 'API User Accounts' : 'Retailer Accounts';
    document.getElementById('thName').textContent     = isApi ? 'API User' : 'Retailer';
    document.getElementById('thApiKey').textContent   = isApi ? 'API Key' : 'API Key';
    // Reset filters and reload
    document.getElementById('searchSeller').value = '';
    document.getElementById('fStatus').value = '';
    loadData(1);
}

function settingToggleBadge(field, value, row){
    const enabled = value === 'enabled';
    const canToggle = row.integration_status === 'approved';
    const color = enabled ? '#10b981' : '#ef4444';
    const label = field === 'api_status' ? 'API Status' : 'Admin Status';
    if(!canToggle){
        return `<div style="font-size:11px;color:#94a3b8">${label}: ${(value || 'disabled').toUpperCase()}</div>`;
    }
    return `<button onclick="toggleApiSetting(${row.id},'${field}','${enabled ? 'disabled' : 'enabled'}')" style="padding:4px 8px;border-radius:999px;border:1px solid ${color};background:${enabled ? color : '#fff'};color:${enabled ? '#fff' : color};font-size:11px;font-weight:700;cursor:pointer">${label}: ${enabled ? 'ON' : 'OFF'}</button>`;
}

function renderTable(sellers){
    const tbody=document.getElementById('sellerBody');
    if(!sellers.length){
        tbody.innerHTML='<tr><td colspan="9" style="text-align:center;padding:30px;color:var(--text-muted)">No records found</td></tr>';
        return;
    }
    tbody.innerHTML=sellers.map((r,i)=>{
        const bg=COLORS[i%COLORS.length];
        const init=(r.name||'?').charAt(0).toUpperCase();
        const isPending = r.approval_status==='pending' || r.status==='inactive';
        return `<tr class="${isPending?'pending-row':''}">
            <td>
                <div style="display:flex;align-items:center;gap:10px">
                    <div class="avatar" style="background:${bg}">${init}</div>
                    <div>
                        <div class="seller-name">${r.name}</div>
                        <div class="seller-email">${r.email||''}</div>
                        <div class="seller-joined">Joined: ${fmtDate(r.created_at)}</div>
                    </div>
                </div>
            </td>
            <td>${r.mobile||'—'}</td>
            <td>${docBadges(r)}</td>
            <td>${r.api_key_hint?`<code style="background:#f1f5f9;padding:2px 7px;border-radius:4px;font-size:11px">${r.api_key_hint}</code>`:'<span style="font-size:12px;color:#94a3b8">No key</span>'}</td>
            <td style="font-weight:600;color:#10b981">₹${fmtMoney(r.wallet_balance||0)}</td>
            <td>${r.recharge_transactions_count||0}</td>
            <td>
                <div style="display:flex;flex-direction:column;gap:6px">
                    ${settingToggleBadge('api_status', r.api_status, r)}
                    ${settingToggleBadge('admin_status', r.admin_status, r)}
                </div>
            </td>
            <td>${statusBadge(r)}</td>
            <td>
                <div style="display:flex;gap:4px;flex-wrap:wrap">
                    ${isPending ? `
                        <button onclick="openApproveModal(${r.id},'${r.name.replace(/'/g,"\\'")}','approve',${JSON.stringify(r)})"
                            style="padding:4px 9px;border-radius:6px;font-size:11.5px;cursor:pointer;border:1px solid #10b981;color:#fff;background:#10b981;font-weight:600">
                            ✓ Approve
                        </button>
                        <button onclick="openApproveModal(${r.id},'${r.name.replace(/'/g,"\\'")}','reject',${JSON.stringify(r)})"
                            style="padding:4px 9px;border-radius:6px;font-size:11.5px;cursor:pointer;border:1px solid #ef4444;color:#ef4444;background:#fff;font-weight:600">
                            ✗ Reject
                        </button>
                    ` : ''}
                    ${r.status==='active' ? `
                        <button onclick="suspendSeller(${r.id},'${r.name.replace(/'/g,"\\'")}')"
                            style="padding:4px 9px;border-radius:6px;font-size:11.5px;cursor:pointer;border:1px solid #f59e0b;color:#f59e0b;background:#fff">
                            Suspend
                        </button>
                    ` : ''}
                    ${r.status==='suspended' && r.approval_status==='rejected' ? `
                        <button onclick="approveSeller(${r.id},'${r.name.replace(/'/g,"\\'")}')"
                            style="padding:4px 9px;border-radius:6px;font-size:11.5px;cursor:pointer;border:1px solid #10b981;color:#10b981;background:#fff">
                            Re-Activate
                        </button>
                    ` : ''}
                    ${r.integration_status==='pending' ? `
                        <button onclick="openIntgDecision(${r.id},${r.integration_id},'${r.name.replace(/'/g,"\\'")}')"
                            style="padding:4px 9px;border-radius:6px;font-size:11.5px;cursor:pointer;border:1px solid #7c3aed;color:#7c3aed;background:#fff">
                            API Req
                        </button>
                    ` : ''}
                    ${r.status==='active' ? `
                        <button onclick="openWalletModal(${r.id},'${r.name.replace(/'/g,"\\'")}',${r.wallet_balance||0},'credit')"
                            style="padding:4px 9px;border-radius:6px;font-size:11.5px;cursor:pointer;border:1px solid #10b981;color:#10b981;background:#fff;font-weight:600">
                            + Balance
                        </button>
                        <button onclick="openWalletModal(${r.id},'${r.name.replace(/'/g,"\\'")}',${r.wallet_balance||0},'debit')"
                            style="padding:4px 9px;border-radius:6px;font-size:11.5px;cursor:pointer;border:1px solid #ef4444;color:#ef4444;background:#fff">
                            ↩ Reverse
                        </button>
                    ` : ''}
                    ${r.status==='active' && r.role==='api_user' ? `
                        <button onclick="loginAsSeller(${r.id},'${r.name.replace(/'/g,"\\'")}')"
                            style="padding:4px 9px;border-radius:6px;font-size:11.5px;cursor:pointer;border:1px solid var(--border);background:#fff;color:var(--text-primary)">
                            Login As
                        </button>
                        <button onclick="openGenKeyModal(${r.id},'${r.name.replace(/'/g,"\\'")}')"
                            style="padding:4px 9px;border-radius:6px;font-size:11.5px;cursor:pointer;border:1px solid #7c3aed;color:#7c3aed;background:#fff;font-weight:600">
                            Gen Key
                        </button>
                        <button onclick="openApiCfgModal(${r.id},'${r.name.replace(/'/g,"\\'")}')"
                            style="padding:4px 9px;border-radius:6px;font-size:11.5px;cursor:pointer;border:1px solid #0891b2;color:#0891b2;background:#fff;font-weight:600">
                            API Cfg
                        </button>
                    ` : ''}
                </div>
            </td>
        </tr>`;
    }).join('');
}

function loadData(page){
    currentPage = page||1;
    const params = new URLSearchParams({page:currentPage, role:activeTab});
    const q = document.getElementById('searchSeller').value.trim();
    const s = document.getElementById('fStatus').value;
    if(q) params.set('search',q);
    if(s) params.set('status',s);
    document.getElementById('sellerBody').innerHTML='<tr><td colspan="8" style="text-align:center;padding:30px;color:var(--text-muted)">Loading…</td></tr>';
    empFetch(`/api/v1/employee/sellers?${params}`).then(data=>{
        const pagination = data.data || {};
        allSellers = pagination.data || [];

        // Update stats for current tab only
        const stats = (data.stats || {})[activeTab] || {};
        document.getElementById('sTotal').textContent     = stats.total     || 0;
        document.getElementById('sPending').textContent   = stats.pending   || 0;
        document.getElementById('sActive').textContent    = stats.active    || 0;
        document.getElementById('sSuspended').textContent = stats.suspended || 0;

        // Update tab counts from full stats
        const allStats = data.stats || {};
        document.getElementById('tab-count-api_user').textContent  = (allStats.api_user||{}).total || 0;
        document.getElementById('tab-count-retailer').textContent  = (allStats.retailer||{}).total || 0;

        renderTable(allSellers);
        renderPagination(pagination);
    }).catch(err=>{
        document.getElementById('sellerBody').innerHTML=`<tr><td colspan="9" style="text-align:center;padding:30px;color:#ef4444">${err.message||'Failed to load.'}</td></tr>`;
    });
}

function renderPagination(meta){
    const wrap=document.getElementById('sellerPagination');
    if(!wrap) return;
    const lp=meta.last_page||1;
    let html=`<div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-top:1px solid #f1f5f9">
        <span style="font-size:13px;color:var(--text-secondary)">Total: ${meta.total||0} records</span>
        <div style="display:flex;gap:6px">`;
    for(let i=1;i<=lp;i++)
        html+=`<button onclick="loadData(${i})" style="padding:5px 10px;border-radius:6px;border:1.5px solid ${i===currentPage?'#2563eb':'#e2e8f0'};background:${i===currentPage?'#2563eb':'#fff'};color:${i===currentPage?'#fff':'#374151'};font-size:12.5px;cursor:pointer">${i}</button>`;
    html+=`</div></div>`;
    wrap.innerHTML=html;
}

function filterTable(){ loadData(1); }
function filterByStatus(s){ document.getElementById('fStatus').value=s; loadData(1); }

// ── Quick approve/suspend ─────────────────────────────────────────────────
function approveSeller(id,name){
    if(!confirm(`Re-activate "${name}"?`)) return;
    empFetch(`/api/v1/employee/sellers/${id}/approve`,'POST')
        .then(d=>{ alert(d.message); loadData(currentPage); })
        .catch(e=>alert(e.message||'Failed.'));
}
function suspendSeller(id,name){
    if(!confirm(`Suspend "${name}"?`)) return;
    empFetch(`/api/v1/employee/sellers/${id}/reject`,'POST',{notes:'Suspended by admin'})
        .then(d=>{ alert(d.message); loadData(currentPage); })
        .catch(e=>alert(e.message||'Failed.'));
}

// ── Approve/Reject modal ──────────────────────────────────────────────────
let approveModalSellerId=null, approveModalAction=null;
function openApproveModal(id,name,action,rowData){
    approveModalSellerId=id; approveModalAction=action;
    document.getElementById('approveModalTitle').textContent = action==='approve' ? '✓ Approve Registration' : '✗ Reject Registration';
    document.getElementById('approveModalName').textContent = name;
    let docHtml='';
    if(rowData.has_pan)  docHtml+='<span class="doc-badge doc-yes">✓ PAN uploaded</span>';
    else                  docHtml+='<span class="doc-badge doc-no">✗ PAN missing</span>';
    if(rowData.has_gst)  docHtml+='<span class="doc-badge doc-yes">✓ GST uploaded</span>';
    else                  docHtml+='<span class="doc-badge doc-no">✗ GST missing</span>';
    if(rowData.has_document) docHtml+='<span class="doc-badge doc-yes">✓ Document uploaded</span>';
    document.getElementById('approveModalDocs').innerHTML=docHtml;
    const notesWrap=document.getElementById('approveNotesWrap');
    notesWrap.style.display = action==='reject' ? 'block' : 'none';
    document.getElementById('approveNotes').value='';
    const btn=document.getElementById('approveConfirmBtn');
    btn.textContent = action==='approve' ? 'Confirm Approval' : 'Confirm Rejection';
    btn.style.background = action==='approve' ? '#10b981' : '#ef4444';
    document.getElementById('approveModal').style.display='flex';
}
function closeApproveModal(){ document.getElementById('approveModal').style.display='none'; }
function submitApproveDecision(){
    const btn=document.getElementById('approveConfirmBtn');
    if(approveModalAction==='reject'){
        const notes=document.getElementById('approveNotes').value.trim();
        if(!notes){ alert('Please enter a rejection reason.'); return; }
        btn.disabled=true; btn.textContent='Processing…';
        empFetch(`/api/v1/employee/sellers/${approveModalSellerId}/reject`,'POST',{notes})
            .then(d=>{ closeApproveModal(); alert(d.message); loadData(currentPage); })
            .catch(e=>alert(e.message||'Failed.'))
            .finally(()=>{ btn.disabled=false; btn.textContent='Confirm Rejection'; });
    } else {
        btn.disabled=true; btn.textContent='Approving…';
        empFetch(`/api/v1/employee/sellers/${approveModalSellerId}/approve`,'POST')
            .then(d=>{ closeApproveModal(); alert(d.message); loadData(currentPage); })
            .catch(e=>alert(e.message||'Failed.'))
            .finally(()=>{ btn.disabled=false; btn.textContent='Confirm Approval'; });
    }
}

// ── Integration modal ─────────────────────────────────────────────────────
let intgSellerId=null, intgRequestId=null;
function openIntgDecision(id,integrationId,name){
    intgSellerId=id;
    intgRequestId=integrationId;
    document.getElementById('intg-seller-name').textContent=name;
    document.getElementById('intg-notes').value='';
    document.getElementById('intg-summary').innerHTML='Loading integration details...';
    document.getElementById('intgModal').style.display='flex';
    empFetch(`/api/v1/employee/sellers/${id}`).then(({data})=>{
        const intg=data.integration||{};
        document.getElementById('intg-summary').innerHTML = `
            <div><strong>Website:</strong> ${intg.website_url || '-'}</div>
            <div><strong>Callback URL:</strong> ${intg.callback_url || '-'}</div>
            <div><strong>Status Check URL:</strong> ${intg.status_check_url || '-'}</div>
            <div><strong>Dispute URL:</strong> ${intg.dispute_url || '-'}</div>
            <div style="white-space:pre-line"><strong>Allowed IPs:</strong> ${intg.allowed_ips || '-'}</div>`;
    }).catch(()=>{
        document.getElementById('intg-summary').innerHTML = '<div>Failed to load integration details.</div>';
    });
}
function closeIntgModal(){ document.getElementById('intgModal').style.display='none'; }
function decideIntg(action){
    const notes=document.getElementById('intg-notes').value.trim();
    if(action==='reject'&&!notes){ alert('Please enter rejection notes.'); return; }
    empFetch(`/api/v1/employee/sellers/integrations/${intgRequestId}/decision`,'POST',{action,notes})
        .then(()=>{ closeIntgModal(); loadData(currentPage); })
        .catch(e=>alert(e.message||'Failed.'));
}

function toggleApiSetting(id, field, value){
    const label = field === 'api_status' ? 'API status' : 'Admin status';
    if(!confirm(`Change ${label} to ${value.toUpperCase()}?`)) return;
    empFetch(`/api/v1/employee/sellers/${id}/api-setting`,'POST',{field,value})
        .then(d=>{ alert(d.message); loadData(currentPage); })
        .catch(e=>alert(e.message||'Failed.'));
}

// ── Login As ──────────────────────────────────────────────────────────────
function loginAsSeller(id,name){
    if(!confirm(`Open seller portal as "${name}" in a new tab?`)) return;
    empFetch(`/api/v1/employee/sellers/${id}/login-as`,'POST').then(data=>{
        if(!data.token){
            alert('Failed to generate impersonation token.');
            return;
        }

        const tab = window.open(data.seller_portal || '/seller/dashboard','_blank');
        const payload = { type:'rh_seller_impersonate', token:data.token, user:data.user };

        if(tab){
            try {
                setTimeout(() => {
                    tab.postMessage(payload, window.location.origin);
                }, 700);
            } catch (_) {}
        }

        try {
            localStorage.setItem('rh_seller_impersonate_token', JSON.stringify({
                token: data.token,
                user: data.user,
                exp: Date.now() + 7200000,
            }));
        } catch (_) {}
    }).catch(e=>alert(e.message||'Failed.'));
}

// ── Add seller ────────────────────────────────────────────────────────────
function showAddModal(){ document.getElementById('addModal').style.display='flex'; }
function submitAdd(){ document.getElementById('addModal').style.display='none'; }
function exportSellers(){ window.open('/api/v1/employee/sellers?export=csv&role='+activeTab+'&'+new URLSearchParams({search:document.getElementById('searchSeller').value,status:document.getElementById('fStatus').value}),'_blank'); }

// ── Generate API Key Modal ─────────────────────────────────────────────────
let genKeySellerId = null;
function openGenKeyModal(id, name){
    genKeySellerId = id;
    document.getElementById('genKeySellerName').textContent = name;
    document.getElementById('genKeyResult').style.display   = 'none';
    document.getElementById('genKeyValue').textContent      = '';
    document.getElementById('genKeySubmitBtn').disabled     = false;
    document.getElementById('genKeySubmitBtn').textContent  = 'Generate Key';
    document.getElementById('genKeyModal').style.display    = 'flex';
}
function closeGenKeyModal(){ document.getElementById('genKeyModal').style.display='none'; }
function copyGenKey(){
    navigator.clipboard.writeText(document.getElementById('genKeyValue').textContent.trim());
}
function submitGenKey(){
    const btn = document.getElementById('genKeySubmitBtn');
    btn.disabled = true; btn.textContent = 'Generating…';
    empFetch(`/api/v1/employee/sellers/${genKeySellerId}/api-config/generate-key`, 'POST')
        .then(data=>{
            document.getElementById('genKeyValue').textContent    = data.api_key || '';
            document.getElementById('genKeyResult').style.display = 'block';
            btn.textContent = 'Regenerate';
            btn.disabled    = false;
            loadData(currentPage);
        })
        .catch(e=>{ alert(e.message||'Failed to generate key.'); btn.disabled=false; btn.textContent='Generate Key'; });
}

// ── API Config Modal ───────────────────────────────────────────────────────
let apiCfgSellerId = null;
function openApiCfgModal(id, name){
    apiCfgSellerId = id;
    document.getElementById('apiCfgSellerName').textContent = name;
    document.getElementById('apiCfgLoading').style.display  = 'block';
    document.getElementById('apiCfgForm').style.display     = 'none';
    document.getElementById('apiCfgServerIp').textContent   = '';
    document.getElementById('apiCfgCallbackUrl').textContent = window.location.origin + '/api/v1/recharge/callback/' + id;
    document.getElementById('apiCfgModal').style.display    = 'flex';

    empFetch(`/api/v1/employee/sellers/${id}`).then(({data})=>{
        const intg = data.integration || {};
        document.getElementById('cfg-website').value     = intg.website_url     || '';
        document.getElementById('cfg-callback').value    = intg.callback_url    || '';
        document.getElementById('cfg-status-check').value = intg.status_check_url || '';
        document.getElementById('cfg-dispute').value     = intg.dispute_url     || '';
        document.getElementById('cfg-ips').value         = intg.allowed_ips     || '';
        document.getElementById('apiCfgLoading').style.display = 'none';
        document.getElementById('apiCfgForm').style.display    = 'block';
    }).catch(()=>{
        document.getElementById('apiCfgLoading').textContent = 'Failed to load integration details.';
    });
}
function closeApiCfgModal(){ document.getElementById('apiCfgModal').style.display='none'; }
function saveApiCfg(){
    const btn = document.getElementById('apiCfgSaveBtn');
    const body = {
        website_url:      document.getElementById('cfg-website').value.trim(),
        callback_url:     document.getElementById('cfg-callback').value.trim(),
        status_check_url: document.getElementById('cfg-status-check').value.trim(),
        dispute_url:      document.getElementById('cfg-dispute').value.trim(),
        allowed_ips:      document.getElementById('cfg-ips').value.trim(),
    };
    if(!body.website_url||!body.callback_url||!body.status_check_url||!body.dispute_url||!body.allowed_ips){
        alert('All fields are required.'); return;
    }
    btn.disabled=true; btn.textContent='Saving…';
    empFetch(`/api/v1/employee/sellers/${apiCfgSellerId}/api-config/integration`,'PUT',body)
        .then(d=>{ alert(d.message||'Saved.'); closeApiCfgModal(); })
        .catch(e=>alert(e.message||'Failed.'))
        .finally(()=>{ btn.disabled=false; btn.textContent='Save Config'; });
}

// ── Wallet Adjust ─────────────────────────────────────────────────────────
let walletSellerId=null, walletType=null;
function openWalletModal(id,name,balance,type){
    walletSellerId=id; walletType=type;
    document.getElementById('walletSellerName').textContent=name;
    document.getElementById('walletCurrentBal').textContent='₹'+fmtMoney(balance);
    document.getElementById('walletAmount').value='';
    document.getElementById('walletDesc').value='';
    const isCredit=type==='credit';
    document.getElementById('walletModalTitle').textContent=isCredit?'➕ Add Balance':'↩ Reverse Balance';
    const btn=document.getElementById('walletSubmitBtn');
    btn.textContent=isCredit?'Add Balance':'Reverse';
    btn.style.background=isCredit?'#10b981':'#ef4444';
    document.getElementById('walletModal').style.display='flex';
}
function closeWalletModal(){ document.getElementById('walletModal').style.display='none'; }
function submitWalletAdjust(){
    const amount=parseFloat(document.getElementById('walletAmount').value);
    const description=document.getElementById('walletDesc').value.trim();
    if(!amount||amount<=0){ alert('Enter a valid amount.'); return; }
    if(!description){ alert('Please enter a reason/description.'); return; }
    const btn=document.getElementById('walletSubmitBtn');
    btn.disabled=true; btn.textContent='Processing…';
    empFetch(`/api/v1/employee/sellers/${walletSellerId}/wallet/adjust`,'POST',{type:walletType,amount,description})
        .then(data=>{ closeWalletModal(); alert(data.message+'\nNew Balance: ₹'+fmtMoney(data.new_balance)); loadData(currentPage); })
        .catch(e=>{ alert(e.message||'Failed.'); })
        .finally(()=>{ btn.disabled=false; btn.textContent=walletType==='credit'?'Add Balance':'Reverse'; });
}

document.addEventListener('DOMContentLoaded',()=>loadData(1));
</script>
@endpush
@endsection
