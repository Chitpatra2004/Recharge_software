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

/* Login As column */
th:nth-child(10),td:nth-child(10){background:#f0f9ff!important;border-left:2px solid #bae6fd;text-align:center}
thead th:nth-child(10){color:#0369a1!important}
.seller-action-list{display:flex;flex-direction:column;align-items:stretch;gap:5px;width:116px}
.seller-action-list button{width:100%;text-align:center}
.seller-login-cell{min-width:105px;text-align:center}
.seller-login-btn{display:inline-flex;align-items:center;justify-content:center;gap:5px;width:92px;padding:5px 8px;border-radius:7px;font-size:11.5px;cursor:pointer;border:1px solid #475569;background:#f8fafc;color:#1e293b;font-weight:600;white-space:nowrap}

/* ── TABS ── */
.role-tabs{display:flex;gap:0;border-bottom:2px solid var(--border);margin-bottom:20px}
.role-tab{padding:10px 22px;font-size:13.5px;font-weight:600;cursor:pointer;border:none;background:none;color:var(--text-secondary);border-bottom:3px solid transparent;margin-bottom:-2px;transition:all .15s;display:flex;align-items:center;gap:8px;font-family:inherit}
.role-tab:hover{color:var(--text-primary)}
.role-tab.active{color:var(--accent-blue);border-bottom-color:var(--accent-blue)}
.role-tab .tab-count{font-size:11px;font-weight:700;padding:2px 8px;border-radius:20px;background:#f1f5f9;color:#64748b}
.role-tab.active .tab-count{background:#dbeafe;color:#2563eb}
.legacy-edit-card{width:calc(100vw - 56px);max-width:1580px;background:#fff;border:1px solid #d7dde5;border-radius:3px;max-height:94vh;overflow:auto;color:#000}
.legacy-edit-card h3{display:none}
.legacy-section-title{height:44px;display:flex;align-items:center;padding:0 22px;border-bottom:1px solid #d7dde5;font-size:14px;font-weight:800;text-transform:uppercase}
.legacy-registration{display:grid;grid-template-columns:1fr 1fr;column-gap:150px;row-gap:28px;padding:36px 90px 42px}
.legacy-field{display:grid;grid-template-columns:170px 1fr;align-items:center;gap:12px}
.legacy-field label{text-align:right;font-size:16px;font-weight:400;color:#000}
.legacy-field input,.legacy-field select,.legacy-field textarea{height:30px;border:1px solid #8a8a8a;border-radius:0;padding:3px 5px;font-size:16px;background:#fff;color:#000;width:100%}
.legacy-field textarea{height:54px;resize:vertical}
.legacy-registration div:not([style*="display:contents"]){display:grid!important;grid-template-columns:170px 1fr!important;align-items:center!important;gap:12px!important}
.legacy-registration label{text-align:right!important;font-size:16px!important;font-weight:400!important;color:#000!important;margin:0!important;display:block!important}
.legacy-registration input,.legacy-registration select,.legacy-registration textarea{height:30px!important;border:1px solid #8a8a8a!important;border-radius:0!important;padding:3px 5px!important;font-size:16px!important;background:#fff!important;color:#000!important;width:100%!important}
.legacy-registration textarea{height:54px!important}
.legacy-commission-head{display:flex;align-items:center;justify-content:space-between;border-top:1px solid #d7dde5;border-bottom:1px solid #d7dde5;min-height:58px}
.legacy-commission-tools{display:flex;gap:4px;align-items:center;padding-right:10px}
.legacy-commission-tools select,.legacy-commission-tools input{height:32px;border:1px solid #666;border-radius:3px;padding:3px 8px;font-size:14px;background:#fff;color:#000}
.legacy-submit{height:32px;background:#00c51a;color:#fff;border:0;border-radius:4px;padding:0 12px;font-weight:700;cursor:pointer}
.legacy-service-title{font-size:28px;font-weight:800;color:#8b91a3;margin:24px 0 18px 3px}
.seller-commission-table{width:100%;border-collapse:collapse;font-size:16px;color:#000}
.seller-commission-table th{padding:8px 16px 18px;text-align:left;font-size:16px;font-weight:400;background:#fff;border:0;color:#000}
.seller-commission-table td{padding:12px 16px;border:0;vertical-align:middle}
.seller-commission-table input,.seller-commission-table select{height:30px;border:1px solid #8a8a8a;border-radius:0;padding:2px 4px;font-size:16px;background:#fff;color:#000;width:136px;max-width:100%}
.row-submit{background:#0868c7;color:#fff;border:0;border-radius:4px;padding:7px 12px;font-size:14px;cursor:pointer}
@media(max-width:980px){.legacy-registration{grid-template-columns:1fr;padding:24px}.legacy-field{grid-template-columns:130px 1fr}.legacy-field label{text-align:left}.legacy-commission-head{align-items:flex-start;flex-direction:column}.legacy-commission-tools{padding:10px;flex-wrap:wrap}}
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
        API Sellers
        <span class="tab-count" id="tab-count-api_user">—</span>
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
                    <th>Login As</th>
                </tr>
            </thead>
            <tbody id="sellerBody">
                <tr><td colspan="10" style="text-align:center;padding:30px;color:var(--text-muted)">Loading...</td></tr>
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
        <div style="display:flex;justify-content:space-between;align-items:center;background:#e3e8ee;border-bottom:1px solid #cfd6df;padding:10px 18px">
            <div style="font-size:13px;color:#4b5563">Dashboard &nbsp; / &nbsp; APIUSER &nbsp; / &nbsp; <strong style="color:#1f2937">APIUSER Edit</strong></div>
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


{{-- Edit Profile Modal --}}
<div id="editModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:600;align-items:center;justify-content:center">
    <div class="legacy-edit-card">
        <div style="display:flex;justify-content:space-between;align-items:center;background:#e3e8ee;border-bottom:1px solid #cfd6df;padding:10px 18px">
            <div style="font-size:13px;color:#4b5563">Dashboard &nbsp; / &nbsp; APIUSER &nbsp; / &nbsp; <strong style="color:#1f2937">APIUSER Edit</strong></div>
            <h3 style="font-size:16px;font-weight:700">Edit Profile — <span id="editSellerName"></span></h3>
            <button onclick="closeEditModal()" style="background:none;border:none;cursor:pointer;font-size:22px;color:var(--text-muted)">&times;</button>
        </div>
        <input type="hidden" id="editSellerId">
        <input type="hidden" id="editCommission">
        <div class="legacy-section-title">Registration Form</div>
        <div class="legacy-registration">
            <div><label>APIUSER Name :</label><input id="editName" type="text"></div>
            <div><label>APIUSER Login No. :</label><input id="editLoginNo" type="text"></div>
            <div><label>Postal Address :</label><textarea id="editAddress"></textarea></div>
            <div><label>Pin Code :</label><input id="editPincode" type="text"></div>
            <div><label>State :</label><select id="editState">
                <option value="">Select State</option>
                <option>Andaman and Nicobar Islands</option>
                <option>Andhra Pradesh</option>
                <option>Arunachal Pradesh</option>
                <option>Assam</option>
                <option>Bihar</option>
                <option>Chandigarh</option>
                <option>Chhattisgarh</option>
                <option>Dadra and Nagar Haveli and Daman and Diu</option>
                <option>Delhi</option>
                <option>Goa</option>
                <option>Gujarat</option>
                <option>Haryana</option>
                <option>Himachal Pradesh</option>
                <option>Jammu and Kashmir</option>
                <option>Jharkhand</option>
                <option>Karnataka</option>
                <option>Kerala</option>
                <option>Ladakh</option>
                <option>Lakshadweep</option>
                <option>Madhya Pradesh</option>
                <option>Maharashtra</option>
                <option>Manipur</option>
                <option>Meghalaya</option>
                <option>Mizoram</option>
                <option>Nagaland</option>
                <option>Odisha</option>
                <option>Puducherry</option>
                <option>Punjab</option>
                <option>Rajasthan</option>
                <option>Sikkim</option>
                <option>Tamil Nadu</option>
                <option>Telangana</option>
                <option>Tripura</option>
                <option>Uttar Pradesh</option>
                <option>Uttarakhand</option>
                <option>West Bengal</option>
            </select></div>
            <div><label>City/District :</label><input id="editCity" type="text" placeholder="Enter City / District"></div>
            <div><label>Mobile No :</label><input id="editMobile" type="tel"></div>
            <div><label>Email :</label><input id="editEmail" type="email"></div>
            <div><label>Pan No :</label><input id="editPan" type="text"></div>
            <div><label>Contact Person :</label><input id="editContactPerson" type="text" placeholder="Enter Contact No."></div>
            <div><label>Aadhar No :</label><input id="editAadhar" type="text"></div>
            <div><label>GST Number :</label><input id="editGstNumber" type="text" placeholder="Enter GST Number."></div>
        </div>
        <div class="legacy-commission-head">
            <div class="legacy-section-title" style="border:0;height:58px">Commission Settings</div>
            <div class="legacy-commission-tools">
                <select id="bulkService" onchange="filterCommissionRows()">
                    <option value="">Mobile</option>
                </select>
                <input id="bulkCommission" type="number" min="0" step="0.001" placeholder="0.00" style="width:185px">
                <select id="bulkType" style="width:145px">
                    <option value="percentage">Percentage (%)</option>
                    <option value="flat">Flat (Rs.)</option>
                </select>
                <button type="button" class="legacy-submit" onclick="applyBulkCommission()">Submit</button>
            </div>
        </div>
        <div id="commissionSettingsWrap" style="padding-bottom:18px;overflow:auto">
            <div style="padding:24px;text-align:center;color:#64748b">Loading commission settings...</div>
        </div>
        <div style="display:flex;gap:10px">
            <button id="editSaveBtn" onclick="submitEdit()" style="flex:1;padding:10px;background:#2563eb;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer">Save Changes</button>
            <button onclick="closeEditModal()" style="padding:10px 18px;border:1px solid var(--border);border-radius:8px;font-size:13px;cursor:pointer;background:#fff">Cancel</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
const TOKEN = () => localStorage.getItem('emp_token');
const empFetch = async (url, method = 'GET', body = null) => {
    const token = TOKEN();
    if (!token) {
        window.location.href = '/admin/login';
        throw new Error('Please login again.');
    }

    const r = await fetch(url, {
        method,
        headers: {
            'Authorization': 'Bearer ' + token,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        ...(body ? { body: JSON.stringify(body) } : {}),
    });

    const d = await r.json().catch(() => ({}));
    if (r.status === 401) {
        localStorage.removeItem('emp_token');
        localStorage.removeItem('emp_data');
        window.location.href = '/admin/login';
        throw new Error('Please login again.');
    }
    if (!r.ok) throw new Error(d.message || 'Error');
    return d;
};

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
        tbody.innerHTML='<tr><td colspan="10" style="text-align:center;padding:30px;color:var(--text-muted)">No records found</td></tr>';
        return;
    }
    tbody.innerHTML=sellers.map((r,i)=>{
        const bg=COLORS[i%COLORS.length];
        const init=(r.name||'?').charAt(0).toUpperCase();
        const isPending = r.approval_status==='pending' || r.status==='inactive';
        const isActiveApi = r.status==='active' && r.role==='api_user';
        const nm = r.name.replace(/'/g,"\\'");

        /* Login As column */
        const loginAsTool = isActiveApi ? `
            <button onclick="loginAsSeller(${r.id},'${nm}')"
                class="seller-login-btn">
                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                Login As
            </button>` : `<span style="font-size:12px;color:#94a3b8">—</span>`;

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
                <div class="seller-action-list">
                    ${isPending ? `
                        <button onclick="openApproveModal(${r.id},'approve')"
                            style="padding:4px 10px;border-radius:6px;font-size:11.5px;cursor:pointer;border:1px solid #10b981;color:#fff;background:#10b981;font-weight:600;white-space:nowrap">
                            ✓ Approve
                        </button>
                        <button onclick="openApproveModal(${r.id},'reject')"
                            style="padding:4px 10px;border-radius:6px;font-size:11.5px;cursor:pointer;border:1px solid #ef4444;color:#ef4444;background:#fff;font-weight:600;white-space:nowrap">
                            ✗ Reject
                        </button>
                    ` : ''}
                    ${r.status==='active' ? `
                        <button onclick="suspendSeller(${r.id},'${nm}')"
                            style="padding:4px 10px;border-radius:6px;font-size:11.5px;cursor:pointer;border:1px solid #f59e0b;color:#f59e0b;background:#fff;white-space:nowrap">
                            Deactivate
                        </button>
                    ` : ''}
                    ${r.status==='suspended' ? `
                        <button onclick="approveSeller(${r.id},'${nm}')"
                            style="padding:4px 10px;border-radius:6px;font-size:11.5px;cursor:pointer;border:1px solid #10b981;color:#10b981;background:#fff;white-space:nowrap">
                            Activate
                        </button>
                    ` : ''}
                    ${r.integration_status==='pending' ? `
                        <button onclick="openIntgDecision(${r.id},${r.integration_id},'${nm}')"
                            style="padding:4px 10px;border-radius:6px;font-size:11.5px;cursor:pointer;border:1px solid #7c3aed;color:#7c3aed;background:#fff;white-space:nowrap">
                            API Request
                        </button>
                    ` : ''}
                    <button onclick="openEditModal(${r.id},'${nm}')"
                        style="padding:4px 10px;border-radius:6px;font-size:11.5px;cursor:pointer;border:1px solid #2563eb;color:#2563eb;background:#fff;font-weight:600;white-space:nowrap">
                        Edit Profile
                    </button>
                    ${r.status==='active' ? `
                        <button onclick="openWalletModal(${r.id},'${nm}',${r.wallet_balance||0},'credit')"
                            style="padding:4px 10px;border-radius:6px;font-size:11.5px;cursor:pointer;border:1px solid #10b981;color:#10b981;background:#fff;font-weight:600;white-space:nowrap">
                            + Balance
                        </button>
                        <button onclick="openWalletModal(${r.id},'${nm}',${r.wallet_balance||0},'debit')"
                            style="padding:4px 10px;border-radius:6px;font-size:11.5px;cursor:pointer;border:1px solid #ef4444;color:#ef4444;background:#fff;white-space:nowrap">
                            ↩ Reverse
                        </button>
                    ` : ''}
                </div>
            </td>
            <td class="seller-login-cell">${loginAsTool}</td>
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
    document.getElementById('sellerBody').innerHTML='<tr><td colspan="10" style="text-align:center;padding:30px;color:var(--text-muted)">Loading…</td></tr>';
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

        renderTable(allSellers);
        renderPagination(pagination);
    }).catch(err=>{
        document.getElementById('sellerBody').innerHTML=`<tr><td colspan="10" style="text-align:center;padding:30px;color:#ef4444">${err.message||'Failed to load.'}</td></tr>`;
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

// ── Document viewer ───────────────────────────────────────────────────────
function openDoc(id, type){
    empFetch(`/api/v1/employee/sellers/${id}/document/${type}`)
        .then(data => window.open(data.url, '_blank'))
        .catch(e => alert('Could not load document: ' + (e.message || 'Error')));
}

// ── Approve/Reject modal ──────────────────────────────────────────────────
let approveModalSellerId=null, approveModalAction=null;
function openApproveModal(id, action){
    const rowData = allSellers.find(s => s.id === id) || {};
    approveModalSellerId=id; approveModalAction=action;
    document.getElementById('approveModalTitle').textContent = action==='approve' ? '✓ Approve Registration' : '✗ Reject Registration';
    document.getElementById('approveModalName').textContent = rowData.name || id;
    let docHtml='';
    if(rowData.has_pan)
        docHtml+=`<span class="doc-badge doc-yes">✓ PAN</span> <button onclick="openDoc(${id},'pan')" style="font-size:11px;color:#2563eb;font-weight:600;background:none;border:none;cursor:pointer;padding:0">View</button>&nbsp;`;
    else
        docHtml+='<span class="doc-badge doc-no">✗ PAN missing</span>&nbsp;';
    if(rowData.has_gst)
        docHtml+=`<span class="doc-badge doc-yes">✓ GST</span> <button onclick="openDoc(${id},'gst')" style="font-size:11px;color:#2563eb;font-weight:600;background:none;border:none;cursor:pointer;padding:0">View</button>&nbsp;`;
    else
        docHtml+='<span class="doc-badge doc-no">✗ GST missing</span>&nbsp;';
    if(rowData.has_document)
        docHtml+=`<span class="doc-badge doc-yes">✓ Doc</span> <button onclick="openDoc(${id},'doc')" style="font-size:11px;color:#2563eb;font-weight:600;background:none;border:none;cursor:pointer;padding:0">View</button>`;
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

        const payload = { type:'rh_seller_impersonate', token:data.token, user:data.user };

        try {
            localStorage.setItem('rh_seller_impersonate_token', JSON.stringify({
                token: data.token,
                user: data.user,
                exp: Date.now() + 7200000,
            }));
        } catch (_) {}

        const tab = window.open(data.seller_portal || '/seller/dashboard','_blank');

        if(tab){
            try {
                setTimeout(() => {
                    tab.postMessage(payload, window.location.origin);
                }, 700);
            } catch (_) {}
        }
    }).catch(e=>alert(e.message||'Failed.'));
}

// ── Add seller ────────────────────────────────────────────────────────────
function showAddModal(){ document.getElementById('addModal').style.display='flex'; }
function submitAdd(){ document.getElementById('addModal').style.display='none'; }
function exportSellers(){ window.open('/api/v1/employee/sellers?export=csv&role='+activeTab+'&'+new URLSearchParams({search:document.getElementById('searchSeller').value,status:document.getElementById('fStatus').value}),'_blank'); }


// ── Edit Profile Modal ─────────────────────────────────────────────────────
let editCommissionRows = [];
function openEditModal(id, name){
    document.getElementById('editSellerId').value = id;
    document.getElementById('editSellerName').textContent = name || '…';
    // Clear all fields while loading
    ['editName','editLoginNo','editAddress','editPincode','editMobile','editEmail',
     'editPan','editContactPerson','editAadhar','editGstNumber','editCity'].forEach(f => {
        const el = document.getElementById(f);
        if(el) el.value = '';
    });
    document.getElementById('editState').value = '';
    document.getElementById('editCommission').value = '';
    document.getElementById('editModal').style.display = 'flex';
    // Fetch full seller data to populate form
    empFetch(`/api/v1/employee/sellers/${id}`).then(({data}) => {
        const u = data.user || {};
        document.getElementById('editSellerName').textContent = u.name || name;
        document.getElementById('editName').value           = u.name || '';
        document.getElementById('editLoginNo').value        = u.mobile || '';
        document.getElementById('editMobile').value         = u.mobile || '';
        document.getElementById('editEmail').value          = u.email || '';
        document.getElementById('editAddress').value        = u.address || '';
        document.getElementById('editPincode').value        = u.pincode || '';
        document.getElementById('editCity').value           = u.city || '';
        document.getElementById('editPan').value            = u.pan_no || '';
        document.getElementById('editAadhar').value         = u.aadhar_no || '';
        document.getElementById('editGstNumber').value      = u.gst_number || '';
        document.getElementById('editContactPerson').value  = u.contact_person || '';
        document.getElementById('editCommission').value     = u.commission_rate || '';
        // Set state dropdown
        const stateEl = document.getElementById('editState');
        const stateVal = u.state || '';
        for(let i = 0; i < stateEl.options.length; i++){
            if(stateEl.options[i].value === stateVal || stateEl.options[i].text === stateVal){
                stateEl.selectedIndex = i; break;
            }
        }
    }).catch(e => console.warn('Could not load seller details:', e.message));
    loadCommissionSettings(id);
}
function closeEditModal(){ document.getElementById('editModal').style.display='none'; }
function loadCommissionSettings(id){
    editCommissionRows = [];
    document.getElementById('commissionSettingsWrap').innerHTML = '<div style="padding:24px;text-align:center;color:#64748b">Loading commission settings...</div>';
    empFetch(`/api/v1/employee/sellers/${id}/commissions`).then(d => {
        editCommissionRows = d.data || [];
        renderServiceOptions(editCommissionRows);
        renderCommissionSettings();
    }).catch(e => {
        document.getElementById('commissionSettingsWrap').innerHTML = `<div style="padding:24px;text-align:center;color:#ef4444">${e.message || 'Failed to load commission settings.'}</div>`;
    });
}
function renderServiceOptions(rows){
    const services = [...new Set(rows.map(r => r.category).filter(Boolean))].sort();
    document.getElementById('bulkService').innerHTML = services.map(s => `<option value="${s}">${s.charAt(0).toUpperCase()+s.slice(1)}</option>`).join('') || '<option value="">Mobile</option>';
}
function renderCommissionSettings(){
    const selectedService = document.getElementById('bulkService').value;
    const rows = selectedService ? editCommissionRows.filter(r => r.category === selectedService) : editCommissionRows;
    if(!rows.length){
        document.getElementById('commissionSettingsWrap').innerHTML = '<div style="padding:24px;text-align:center;color:#64748b">No operators found.</div>';
        return;
    }
    const title = selectedService ? selectedService.charAt(0).toUpperCase() + selectedService.slice(1) : 'Mobile';
    let html = `<div class="legacy-service-title">${title}</div><table class="seller-commission-table"><thead><tr>
        <th style="width:60px">Sr.</th><th>Operator Name</th><th style="width:160px">Commission</th><th style="width:170px">Type</th><th style="width:170px">Api1</th><th style="width:120px">Limit Txn</th><th style="width:150px">Limit Amount</th><th style="width:180px">Blocked Amounts</th><th style="width:100px"></th>
    </tr></thead><tbody>`;
    rows.forEach((r, idx) => {
        const masterIdx = editCommissionRows.findIndex(x => x.operator_code === r.operator_code);
        html += `<tr data-service="${r.category || ''}">
            <td>${idx + 1}</td>
            <td>${r.operator_name || r.operator_code}</td>
            <td><input type="number" min="0" step="0.001" value="${Number(r.commission || 0).toFixed(3)}" onchange="editCommissionRows[${masterIdx}].commission=this.value"></td>
            <td><select onchange="editCommissionRows[${masterIdx}].commission_type=this.value">
                <option value="percentage" ${r.commission_type === 'percentage' ? 'selected' : ''}>Percentage (%)</option>
                <option value="flat" ${r.commission_type === 'flat' ? 'selected' : ''}>Flat (Rs.)</option>
            </select></td>
            <td><input type="text" value="${r.api1 || ''}" onchange="editCommissionRows[${masterIdx}].api1=this.value"></td>
            <td><input type="number" min="0" step="1" value="${r.limit_txn || 0}" onchange="editCommissionRows[${masterIdx}].limit_txn=this.value" style="width:68px"></td>
            <td><input type="number" min="0" step="0.01" value="${r.limit_amount || 0}" onchange="editCommissionRows[${masterIdx}].limit_amount=this.value" style="width:90px"></td>
            <td><input type="text" value="${r.blocked_amounts || ''}" onchange="editCommissionRows[${masterIdx}].blocked_amounts=this.value"></td>
            <td><button type="button" class="row-submit" onclick="submitSingleCommission(${masterIdx})">Submit</button></td>
        </tr>`;
    });
    html += '</tbody></table>';
    document.getElementById('commissionSettingsWrap').innerHTML = html;
}
function filterCommissionRows(){ renderCommissionSettings(); }
function applyBulkCommission(){
    const service = document.getElementById('bulkService').value;
    const commission = document.getElementById('bulkCommission').value;
    const type = document.getElementById('bulkType').value;
    if(commission === ''){ alert('Enter commission value.'); return; }
    editCommissionRows = editCommissionRows.map(r => (!service || r.category === service)
        ? {...r, commission, commission_type:type, is_active:true}
        : r
    );
    renderCommissionSettings();
}
function commissionPayload(rows){
    return rows.map(r => ({
        operator_code: r.operator_code,
        commission: r.commission || 0,
        commission_type: r.commission_type || 'percentage',
        api1: r.api1 || null,
        limit_txn: r.limit_txn || 0,
        limit_amount: r.limit_amount || 0,
        blocked_amounts: r.blocked_amounts || null,
        is_active: !!r.is_active
    }));
}
function submitSingleCommission(index){
    const id = document.getElementById('editSellerId').value;
    const row = editCommissionRows[index];
    if(!id || !row) return;
    empFetch(`/api/v1/employee/sellers/${id}/commissions`,'PUT',{commissions: commissionPayload([row])})
        .then(d=>alert(d.message || 'Commission saved.'))
        .catch(e=>alert(e.message || 'Failed.'));
}
function submitEdit(){
    const id             = document.getElementById('editSellerId').value;
    const name           = document.getElementById('editName').value.trim();
    const email          = document.getElementById('editEmail').value.trim();
    const mobile         = document.getElementById('editMobile').value.trim();
    const comm           = document.getElementById('editCommission').value.trim();
    const address        = document.getElementById('editAddress').value.trim();
    const pincode        = document.getElementById('editPincode').value.trim();
    const state          = document.getElementById('editState').value.trim();
    const city           = document.getElementById('editCity').value.trim();
    const pan_no         = document.getElementById('editPan').value.trim();
    const aadhar_no      = document.getElementById('editAadhar').value.trim();
    const gst_number     = document.getElementById('editGstNumber').value.trim();
    const contact_person = document.getElementById('editContactPerson').value.trim();
    if(!name||!email||!mobile){ alert('Name, email and mobile are required.'); return; }
    const btn = document.getElementById('editSaveBtn');
    btn.disabled=true; btn.textContent='Saving…';
    const payload = {
        name, email, mobile,
        commission_rate: comm || null,
        address: address || null,
        pincode: pincode || null,
        state: state || null,
        city: city || null,
        pan_no: pan_no || null,
        aadhar_no: aadhar_no || null,
        gst_number: gst_number || null,
        contact_person: contact_person || null,
    };
    empFetch(`/api/v1/employee/sellers/${id}`,'PATCH', payload)
        .then(d=>empFetch(`/api/v1/employee/sellers/${id}/commissions`,'PUT',{commissions: commissionPayload(editCommissionRows)}).then(()=>d))
        .then(d=>{ closeEditModal(); alert(d.message||'Saved.'); loadData(currentPage); })
        .catch(e=>alert(e.message||'Failed.'))
        .finally(()=>{ btn.disabled=false; btn.textContent='Save Changes'; });
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
