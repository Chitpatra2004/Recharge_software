@extends('layouts.admin')
@section('title','Sellers')

@push('head')
<style>
.filter-bar{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:14px}
.filter-bar input,.filter-bar select{padding:7px 11px;border:1px solid var(--border);border-radius:8px;font-size:13px;background:#fff;color:#1e293b}
.summary-strip{display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:18px}
@media(max-width:1100px){.summary-strip{grid-template-columns:1fr 1fr 1fr}}
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
/* Pending highlight row */
tr.pending-row td{background:#fffbeb!important}
tr.pending-row:hover td{background:#fef3c7!important}
/* Seller name — explicitly dark + hover highlight */
.seller-name{
    color:#1e293b;
    font-weight:700;
    font-size:13px;
    cursor:default;
    line-height:1.3;
}
.seller-email{font-size:11.5px;color:#64748b;margin-top:1px}
.seller-joined{font-size:11px;color:#94a3b8;margin-top:1px}
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

<div class="summary-strip">
    <div class="stat-card blue"><div class="val" id="sTotal">—</div><div class="lbl">Total</div></div>
    <div class="stat-card orange" style="cursor:pointer" onclick="filterByStatus('inactive')" title="Click to filter pending">
        <div class="val" id="sPending">—</div><div class="lbl">⏳ Pending Approval</div>
    </div>
    <div class="stat-card green"><div class="val" id="sActive">—</div><div class="lbl">Active</div></div>
    <div class="stat-card red"><div class="val" id="sSuspended">—</div><div class="lbl">Suspended / Rejected</div></div>
    <div class="stat-card purple"><div class="val" id="sWalletTotal">—</div><div class="lbl">Total Wallet Balance</div></div>
</div>

<div class="card">
    <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
        <span style="font-weight:600;font-size:14px">Seller / API User Accounts</span>
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
                    <th>Seller / API User</th>
                    <th>Mobile</th>
                    <th>Role</th>
                    <th>Documents</th>
                    <th>API Key</th>
                    <th>Wallet</th>
                    <th>Recharges</th>
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

{{-- Approve/Reject with Notes Modal --}}
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

@push('scripts')
<script>
const TOKEN = ()=>localStorage.getItem('emp_token');
const empFetch = (url,method='GET',body=null)=>fetch(url,{method,headers:{'Authorization':'Bearer '+TOKEN(),'Content-Type':'application/json','Accept':'application/json'},...(body?{body:JSON.stringify(body)}:{})}).then(async r=>{const d=await r.json();if(!r.ok)throw new Error(d.message||'Error');return d;});

const COLORS=['#2563eb','#10b981','#f59e0b','#ef4444','#7c3aed','#0891b2'];
let allSellers=[], currentPage=1;

function fmtMoney(n){return Number(n||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2});}
function fmtDate(d){if(!d)return'—';return new Date(d).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'});}

function statusBadge(r){
    const s = r.status, a = r.approval_status;
    if(a==='pending' || s==='inactive')
        return '<span style="background:#fef3c7;color:#92400e;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:600">⏳ Pending</span>';
    if(a==='rejected' || s==='suspended')
        return '<span style="background:#fee2e2;color:#991b1b;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:600">Rejected</span>';
    if(s==='active')
        return '<span style="background:#d1fae5;color:#065f46;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:600">✓ Active</span>';
    return `<span style="background:#f1f5f9;color:#64748b;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:600">${s}</span>`;
}

function roleBadge(r){
    if(r==='api_user')   return '<span style="background:#ede9fe;color:#5b21b6;padding:2px 8px;border-radius:12px;font-size:11px;font-weight:600">API User</span>';
    if(r==='retailer')   return '<span style="background:#dbeafe;color:#1e40af;padding:2px 8px;border-radius:12px;font-size:11px;font-weight:600">Retailer</span>';
    if(r==='distributor')return '<span style="background:#fef3c7;color:#92400e;padding:2px 8px;border-radius:12px;font-size:11px;font-weight:600">Distributor</span>';
    return r;
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

function renderTable(sellers){
    const tbody=document.getElementById('sellerBody');
    if(!sellers.length){
        tbody.innerHTML='<tr><td colspan="9" style="text-align:center;padding:30px;color:var(--text-muted)">No sellers found</td></tr>';
        return;
    }
    tbody.innerHTML=sellers.map((r,i)=>{
        const bg=COLORS[i%COLORS.length];
        const init=(r.name||'?').charAt(0).toUpperCase();
        const isPending = r.approval_status==='pending' || r.status==='inactive';
        return `<tr class="${isPending?'pending-row':''}">
            <td>
                <div style="display:flex;align-items:center;gap:10px">
                    <div class="avatar" style="background:${bg};flex-shrink:0">${init}</div>
                    <div>
                        <div class="seller-name">${r.name}</div>
                        <div class="seller-email">${r.email||''}</div>
                        <div class="seller-joined">Joined: ${fmtDate(r.created_at)}</div>
                    </div>
                </div>
            </td>
            <td>${r.mobile||'—'}</td>
            <td>${roleBadge(r.role)}</td>
            <td>${docBadges(r)}</td>
            <td>${r.api_key_hint?`<code style="background:#f1f5f9;padding:2px 7px;border-radius:4px;font-size:11px">${r.api_key_hint}</code>`:'<span style="font-size:12px;color:#94a3b8">No key</span>'}</td>
            <td style="font-weight:600;color:#10b981">₹${fmtMoney(r.wallet_balance||0)}</td>
            <td>${r.recharge_transactions_count||0}</td>
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
                        <button onclick="suspendSeller(${r.id},'${r.name.replace(/'/g,"\\'")}'"
                            style="padding:4px 9px;border-radius:6px;font-size:11.5px;cursor:pointer;border:1px solid #f59e0b;color:#f59e0b;background:#fff">
                            Suspend
                        </button>
                    ` : ''}
                    ${r.status==='suspended' && r.approval_status==='rejected' ? `
                        <button onclick="approveSeller(${r.id},'${r.name.replace(/'/g,"\\'")}'"
                            style="padding:4px 9px;border-radius:6px;font-size:11.5px;cursor:pointer;border:1px solid #10b981;color:#10b981;background:#fff">
                            Re-Activate
                        </button>
                    ` : ''}
                    ${r.integration_status==='pending' ? `
                        <button onclick="openIntgDecision(${r.id},'${r.name.replace(/'/g,"\\'")}'"
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
                        <button onclick="loginAsSeller(${r.id},'${r.name.replace(/'/g,"\\'")}'"
                            style="padding:4px 9px;border-radius:6px;font-size:11.5px;cursor:pointer;border:1px solid var(--border);background:#fff;color:var(--text-primary)">
                            Login As
                        </button>
                    ` : ''}
                </div>
            </td>
        </tr>`;
    }).join('');
}

function loadData(page){
    currentPage=page||1;
    const params=new URLSearchParams({page:currentPage});
    const q=document.getElementById('searchSeller').value.trim();
    const s=document.getElementById('fStatus').value;
    if(q) params.set('search',q);
    if(s) params.set('status',s);
    document.getElementById('sellerBody').innerHTML='<tr><td colspan="9" style="text-align:center;padding:30px;color:var(--text-muted)">Loading…</td></tr>';
    empFetch(`/api/v1/employee/sellers?${params}`).then(data=>{
        const pagination = data.data || {};
        allSellers = pagination.data || [];
        const stats = data.stats || {};
        document.getElementById('sTotal').textContent   = stats.total    || 0;
        document.getElementById('sPending').textContent = stats.pending  || 0;
        document.getElementById('sActive').textContent  = stats.active   || 0;
        document.getElementById('sSuspended').textContent = stats.suspended || 0;
        document.getElementById('sWalletTotal').textContent = '₹'+fmtMoney(allSellers.reduce((acc,r)=>acc+(r.wallet_balance||0),0));
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

// ── Quick approve/suspend (no modal) ───────────────────────────────────────
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

// ── Approve/Reject modal (with doc preview) ────────────────────────────────
let approveModalSellerId=null, approveModalAction=null;

function openApproveModal(id, name, action, rowData){
    approveModalSellerId=id;
    approveModalAction=action;
    document.getElementById('approveModalTitle').textContent = action==='approve' ? '✓ Approve Registration' : '✗ Reject Registration';
    document.getElementById('approveModalName').textContent = name;
    // Show doc badges
    let docHtml='';
    if(rowData.has_pan)  docHtml+='<span class="doc-badge doc-yes">✓ PAN uploaded</span>';
    else                  docHtml+='<span class="doc-badge doc-no">✗ PAN missing</span>';
    if(rowData.has_gst)  docHtml+='<span class="doc-badge doc-yes">✓ GST uploaded</span>';
    else                  docHtml+='<span class="doc-badge doc-no">✗ GST missing</span>';
    if(rowData.has_document) docHtml+='<span class="doc-badge doc-yes">✓ Document uploaded</span>';
    document.getElementById('approveModalDocs').innerHTML=docHtml;
    // Show notes only for reject
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
            .then(d=>{ closeApproveModal(); alert(d.message + '\n\nApproval email has been sent to the user.'); loadData(currentPage); })
            .catch(e=>alert(e.message||'Failed.'))
            .finally(()=>{ btn.disabled=false; btn.textContent='Confirm Approval'; });
    }
}

// ── Integration modal ──────────────────────────────────────────────────────
let intgSellerId=null;
function openIntgDecision(id,name){ intgSellerId=id; document.getElementById('intg-seller-name').textContent=name; document.getElementById('intg-notes').value=''; document.getElementById('intgModal').style.display='flex'; }
function closeIntgModal(){ document.getElementById('intgModal').style.display='none'; }
function decideIntg(action){
    const notes=document.getElementById('intg-notes').value.trim();
    if(action==='reject'&&!notes){ alert('Please enter rejection notes.'); return; }
    empFetch(`/api/v1/employee/sellers/integrations/${intgSellerId}/decision`,'POST',{action,notes})
        .then(()=>{ closeIntgModal(); loadData(currentPage); })
        .catch(e=>alert(e.message||'Failed.'));
}

// ── Login As ───────────────────────────────────────────────────────────────
function loginAsSeller(id,name){
    if(!confirm(`Open seller portal as "${name}" in a new tab?`)) return;
    empFetch(`/api/v1/employee/sellers/${id}/login-as`,'POST').then(data=>{
        if(data.token){ localStorage.setItem('seller_token',data.token); window.open('/seller/dashboard','_blank'); }
        else alert('Failed to generate impersonation token.');
    }).catch(e=>alert(e.message||'Failed.'));
}

// ── Add seller (stub) ──────────────────────────────────────────────────────
function showAddModal(){ document.getElementById('addModal').style.display='flex'; }
function submitAdd(){ document.getElementById('addModal').style.display='none'; }
function exportSellers(){ window.open('/api/v1/employee/sellers?export=csv&'+new URLSearchParams({search:document.getElementById('searchSeller').value,status:document.getElementById('fStatus').value}),'_blank'); }

// ── Wallet Adjust ──────────────────────────────────────────────────────────
let walletSellerId=null, walletType=null;

function openWalletModal(id, name, balance, type){
    walletSellerId=id; walletType=type;
    document.getElementById('walletSellerName').textContent=name;
    document.getElementById('walletCurrentBal').textContent='₹'+fmtMoney(balance);
    document.getElementById('walletAmount').value='';
    document.getElementById('walletDesc').value='';
    const isCredit = type==='credit';
    document.getElementById('walletModalTitle').textContent = isCredit ? '➕ Add Balance' : '↩ Reverse Balance';
    const btn = document.getElementById('walletSubmitBtn');
    btn.textContent = isCredit ? 'Add Balance' : 'Reverse';
    btn.style.background = isCredit ? '#10b981' : '#ef4444';
    document.getElementById('walletModal').style.display='flex';
}

function closeWalletModal(){ document.getElementById('walletModal').style.display='none'; }

function submitWalletAdjust(){
    const amount = parseFloat(document.getElementById('walletAmount').value);
    const description = document.getElementById('walletDesc').value.trim();
    if(!amount || amount<=0){ alert('Enter a valid amount.'); return; }
    if(!description){ alert('Please enter a reason/description.'); return; }
    const btn = document.getElementById('walletSubmitBtn');
    btn.disabled=true; btn.textContent='Processing…';
    empFetch(`/api/v1/employee/sellers/${walletSellerId}/wallet/adjust`,'POST',{
        type: walletType, amount, description
    }).then(data=>{
        closeWalletModal();
        alert(data.message + '\nNew Balance: ₹' + fmtMoney(data.new_balance));
        loadData(currentPage);
    }).catch(e=>{
        alert(e.message||'Failed.');
    }).finally(()=>{
        btn.disabled=false;
        btn.textContent = walletType==='credit' ? 'Add Balance' : 'Reverse';
    });
}

document.addEventListener('DOMContentLoaded',()=>loadData(1));
</script>
@endpush
@endsection
