@extends('layouts.admin')
@section('title','Sellers')

@push('head')
<style>
.filter-bar{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:14px}
.filter-bar input,.filter-bar select{padding:7px 11px;border:1px solid var(--border);border-radius:8px;font-size:13px;background:#fff;color:var(--text-primary)}
.summary-strip{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px}
@media(max-width:900px){.summary-strip{grid-template-columns:1fr 1fr}}
.stat-card{background:var(--card-bg);border-radius:var(--radius);padding:16px 20px;box-shadow:var(--shadow-sm);border-left:4px solid transparent}
.stat-card.blue{border-color:var(--accent-blue)}
.stat-card.green{border-color:var(--accent-green)}
.stat-card.orange{border-color:var(--accent-orange)}
.stat-card.purple{border-color:var(--accent-purple)}
.stat-card .val{font-size:22px;font-weight:700;margin-bottom:2px}
.stat-card .lbl{font-size:11.5px;color:var(--text-secondary)}
.avatar{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff;flex-shrink:0}
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Sellers</h1>
        <p class="page-sub">Manage API sellers and their wallet & recharge access</p>
    </div>
    <div style="display:flex;gap:8px">
        <button class="btn btn-outline" onclick="exportSellers()">Export</button>
        <button class="btn btn-primary" onclick="showAddModal()">+ Add Seller</button>
    </div>
</div>

<div class="summary-strip">
    <div class="stat-card blue"><div class="val" id="sTotal">—</div><div class="lbl">Total Sellers</div></div>
    <div class="stat-card green"><div class="val" id="sActive">—</div><div class="lbl">Active</div></div>
    <div class="stat-card orange"><div class="val" id="sSuspended">—</div><div class="lbl">Suspended</div></div>
    <div class="stat-card purple"><div class="val" id="sWalletTotal">—</div><div class="lbl">Total Wallet Balance</div></div>
</div>

<div class="card">
    <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
        <span style="font-weight:600;font-size:14px">Seller Accounts</span>
        <div class="filter-bar" style="margin:0">
            <input type="text" id="searchSeller" placeholder="Search name / mobile / API key..." oninput="filterTable()">
            <select id="fStatus" onchange="filterTable()">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="suspended">Suspended</option>
            </select>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Seller</th>
                    <th>Mobile</th>
                    <th>API Key</th>
                    <th>Wallet Balance</th>
                    <th>Recharges</th>
                    <th>Integration</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="sellerBody">
                <tr><td colspan="8" style="text-align:center;padding:30px;color:var(--text-muted)">Loading...</td></tr>
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

@push('scripts')
<script>
const TOKEN = ()=>localStorage.getItem('emp_token');
const empFetch = (url,method='GET',body=null)=>fetch(url,{method,headers:{'Authorization':'Bearer '+TOKEN(),'Content-Type':'application/json','Accept':'application/json'},...(body?{body:JSON.stringify(body)}:{})}).then(async r=>{const d=await r.json();if(!r.ok)throw new Error(d.message||'Error');return d;});

const COLORS=['#2563eb','#10b981','#f59e0b','#ef4444','#7c3aed','#0891b2'];
let allSellers=[], currentPage=1;

function fmtMoney(n){return Number(n||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2});}
function fmtDate(d){if(!d)return'—';const dt=new Date(d);return dt.toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'});}

function statusBadge(s){
    if(s==='active')   return '<span style="background:#d1fae5;color:#065f46;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:600">Active</span>';
    if(s==='inactive') return '<span style="background:#fef3c7;color:#92400e;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:600">Pending</span>';
    if(s==='suspended')return '<span style="background:#fee2e2;color:#991b1b;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:600">Suspended</span>';
    return `<span style="background:#f1f5f9;color:#64748b;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:600">${s}</span>`;
}

function intgBadge(s){
    if(!s||s==='none')   return '<span style="font-size:11.5px;color:#94a3b8">No request</span>';
    if(s==='pending')    return '<span style="background:#fef3c7;color:#92400e;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600">Pending</span>';
    if(s==='approved')   return '<span style="background:#d1fae5;color:#065f46;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600">Approved</span>';
    if(s==='rejected')   return '<span style="background:#fee2e2;color:#991b1b;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600">Rejected</span>';
    return s;
}

function renderTable(sellers){
    const tbody=document.getElementById('sellerBody');
    if(!sellers.length){tbody.innerHTML='<tr><td colspan="9" style="text-align:center;padding:30px;color:var(--text-muted)">No sellers found</td></tr>';return;}
    tbody.innerHTML=sellers.map((r,i)=>{
        const bg=COLORS[i%COLORS.length];
        const initials=(r.name||'?').charAt(0).toUpperCase();
        return `<tr>
            <td><div style="display:flex;align-items:center;gap:10px"><div class="avatar" style="background:${bg}">${initials}</div><div><div style="font-weight:600;font-size:13px">${r.name}</div><div style="font-size:11.5px;color:var(--text-secondary)">${r.email||''}</div></div></div></td>
            <td>${r.mobile||'—'}</td>
            <td>${r.api_key_hint?`<code style="background:#f1f5f9;padding:2px 7px;border-radius:4px;font-size:11px">${r.api_key_hint}</code>`:'<span style="font-size:12px;color:#94a3b8">No key</span>'}</td>
            <td style="font-weight:600;color:#10b981">₹${fmtMoney(r.wallet_balance||0)}</td>
            <td>${r.total_recharges||0}</td>
            <td>${intgBadge(r.integration_status)}</td>
            <td>${statusBadge(r.status)}</td>
            <td style="display:flex;gap:5px;flex-wrap:wrap">
                ${r.status==='inactive'?`<button onclick="approveSeller(${r.id},'${r.name}')" style="padding:4px 9px;border-radius:6px;font-size:11.5px;cursor:pointer;border:1px solid #10b981;color:#10b981;background:#fff;font-weight:600">Approve</button>`:''}
                ${r.status==='active'?`<button onclick="suspendSeller(${r.id},'${r.name}')" style="padding:4px 9px;border-radius:6px;font-size:11.5px;cursor:pointer;border:1px solid #f59e0b;color:#f59e0b;background:#fff">Suspend</button>`:''}
                ${r.status==='suspended'?`<button onclick="approveSeller(${r.id},'${r.name}')" style="padding:4px 9px;border-radius:6px;font-size:11.5px;cursor:pointer;border:1px solid #10b981;color:#10b981;background:#fff">Activate</button>`:''}
                ${r.integration_status==='pending'?`<button onclick="openIntgDecision(${r.id},'${r.name}')" style="padding:4px 9px;border-radius:6px;font-size:11.5px;cursor:pointer;border:1px solid #7c3aed;color:#7c3aed;background:#fff">API Request</button>`:''}
                <button onclick="loginAsSeller(${r.id},'${r.name}')" style="padding:4px 9px;border-radius:6px;font-size:11.5px;cursor:pointer;border:1px solid var(--border);background:#fff;color:var(--text-primary)">Login As</button>
            </td>
        </tr>`;
    }).join('');
}

function loadData(page){
    currentPage=page||1;
    const params=new URLSearchParams({page:currentPage});
    const q=document.getElementById('searchSeller').value.trim(), s=document.getElementById('fStatus').value;
    if(q) params.set('search',q); if(s) params.set('status',s);
    document.getElementById('sellerBody').innerHTML='<tr><td colspan="9" style="text-align:center;padding:30px;color:var(--text-muted)">Loading…</td></tr>';
    empFetch(`/api/v1/employee/sellers?${params}`).then(data=>{
        allSellers=data.data||[];
        document.getElementById('sTotal').textContent=data.meta?.total||allSellers.length;
        document.getElementById('sActive').textContent=allSellers.filter(r=>r.status==='active').length;
        document.getElementById('sSuspended').textContent=allSellers.filter(r=>r.status==='suspended').length;
        document.getElementById('sWalletTotal').textContent='₹'+fmtMoney(allSellers.reduce((s,r)=>s+(r.wallet_balance||0),0));
        renderTable(allSellers);
        renderPagination(data.meta||{});
    }).catch(err=>{ document.getElementById('sellerBody').innerHTML=`<tr><td colspan="9" style="text-align:center;padding:30px;color:#ef4444">${err.message||'Failed to load sellers.'}</td></tr>`; });
}

function renderPagination(meta){
    const wrap=document.getElementById('sellerPagination');
    if(!wrap) return;
    const lp=meta.last_page||1;
    let html=`<div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-top:1px solid #f1f5f9">
        <span style="font-size:13px;color:var(--text-secondary)">Total: ${meta.total||0} sellers</span>
        <div style="display:flex;gap:6px">`;
    for(let i=1;i<=lp;i++) html+=`<button onclick="loadData(${i})" style="padding:5px 10px;border-radius:6px;border:1.5px solid ${i===currentPage?'#2563eb':'#e2e8f0'};background:${i===currentPage?'#2563eb':'#fff'};color:${i===currentPage?'#fff':'#374151'};font-size:12.5px;cursor:pointer">${i}</button>`;
    html+=`</div></div>`;
    wrap.innerHTML=html;
}

function filterTable(){loadData(1);}

function approveSeller(id,name){
    if(!confirm(`Approve/activate seller "${name}"?`)) return;
    empFetch(`/api/v1/employee/sellers/${id}/approve`,'POST').then(()=>loadData(currentPage)).catch(e=>alert(e.message||'Failed.'));
}
function suspendSeller(id,name){
    if(!confirm(`Suspend seller "${name}"?`)) return;
    empFetch(`/api/v1/employee/sellers/${id}/reject`,'POST',{reason:'Suspended by admin'}).then(()=>loadData(currentPage)).catch(e=>alert(e.message||'Failed.'));
}

let intgSellerId=null;
function openIntgDecision(id,name){ intgSellerId=id; document.getElementById('intg-seller-name').textContent=name; document.getElementById('intg-notes').value=''; document.getElementById('intgModal').style.display='flex'; }
function closeIntgModal(){ document.getElementById('intgModal').style.display='none'; }
function decideIntg(action){
    const notes=document.getElementById('intg-notes').value.trim();
    if(action==='reject'&&!notes){ alert('Please enter rejection notes.'); return; }
    empFetch(`/api/v1/employee/sellers/integrations/${intgSellerId}/decision`,'POST',{action,notes}).then(()=>{ closeIntgModal(); loadData(currentPage); }).catch(e=>alert(e.message||'Failed.'));
}

function loginAsSeller(id,name){
    if(!confirm(`Open seller portal as "${name}" in a new tab?`)) return;
    empFetch(`/api/v1/employee/sellers/${id}/login-as`,'POST').then(data=>{
        if(data.token){ localStorage.setItem('seller_token',data.token); window.open('/seller/dashboard','_blank'); }
        else alert('Failed to generate impersonation token.');
    }).catch(e=>alert(e.message||'Failed.'));
}

function showAddModal(){document.getElementById('addModal').style.display='flex';}
function submitAdd(){document.getElementById('addModal').style.display='none';}
function exportSellers(){ window.open('/api/v1/employee/sellers?export=csv&'+new URLSearchParams({search:document.getElementById('searchSeller').value,status:document.getElementById('fStatus').value}),'_blank'); }

document.addEventListener('DOMContentLoaded',()=>loadData(1));
</script>
@endpush
@endsection
