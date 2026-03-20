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
                    <th>Total Recharges</th>
                    <th>Last Active</th>
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
const DEMO_SELLERS = [
    {id:1,name:'RajeshTelecom',mobile:'9876543210',apikey:'SK_AT_7f8a9b',wallet:'₹12,450',recharges:1247,last:'20 Mar 2026',status:'active'},
    {id:2,name:'PriyaRecharge',mobile:'8765432109',apikey:'SK_PR_2c3d4e',wallet:'₹8,720', recharges:983, last:'20 Mar 2026',status:'active'},
    {id:3,name:'SunilShop',    mobile:'7654321098',apikey:'SK_SS_5f6g7h',wallet:'₹5,310', recharges:756, last:'19 Mar 2026',status:'active'},
    {id:4,name:'AmitStore',    mobile:'9988776655',apikey:'SK_AS_8i9j0k',wallet:'₹3,220', recharges:512, last:'20 Mar 2026',status:'active'},
    {id:5,name:'KiranMobile',  mobile:'8877665544',apikey:'SK_KM_1l2m3n',wallet:'₹1,890', recharges:321, last:'19 Mar 2026',status:'active'},
    {id:6,name:'MohanTelecom', mobile:'9765432100',apikey:'SK_MT_4o5p6q',wallet:'₹0',     recharges:198, last:'05 Mar 2026',status:'suspended'},
];

let filtered = [...DEMO_SELLERS];

function filterTable(){
    const q=document.getElementById('searchSeller').value.toLowerCase();
    const s=document.getElementById('fStatus').value;
    filtered=DEMO_SELLERS.filter(r=>{
        const mq=!q||(r.name.toLowerCase().includes(q)||r.mobile.includes(q)||r.apikey.toLowerCase().includes(q));
        const ms=!s||r.status===s;
        return mq&&ms;
    });
    renderTable();
}

const COLORS=['#2563eb','#10b981','#f59e0b','#ef4444','#7c3aed','#0891b2'];

function renderTable(){
    const tbody=document.getElementById('sellerBody');
    if(!filtered.length){tbody.innerHTML='<tr><td colspan="8" style="text-align:center;padding:30px;color:var(--text-muted)">No sellers found</td></tr>';return;}
    tbody.innerHTML=filtered.map((r,i)=>{
        const bg=COLORS[i%COLORS.length];
        const badge=r.status==='active'
            ?'<span class="txn-status status-success">Active</span>'
            :'<span class="txn-status status-failed">Suspended</span>';
        const suspendBtn=r.status==='active'
            ?`<button onclick="toggleStatus(${r.id})" style="padding:4px 10px;border-radius:6px;font-size:11.5px;cursor:pointer;border:1px solid var(--accent-orange);color:var(--accent-orange);background:#fff">Suspend</button>`
            :`<button onclick="toggleStatus(${r.id})" style="padding:4px 10px;border-radius:6px;font-size:11.5px;cursor:pointer;border:1px solid var(--accent-green);color:var(--accent-green);background:#fff">Activate</button>`;
        return `<tr>
            <td>
                <div style="display:flex;align-items:center;gap:10px">
                    <div class="avatar" style="background:${bg}">${r.name.charAt(0)}</div>
                    <span style="font-weight:600">${r.name}</span>
                </div>
            </td>
            <td>${r.mobile}</td>
            <td><code style="background:#f1f5f9;padding:2px 7px;border-radius:4px;font-size:11.5px">${r.apikey}***</code></td>
            <td style="font-weight:600;color:var(--accent-green)">${r.wallet}</td>
            <td>${r.recharges.toLocaleString('en-IN')}</td>
            <td style="font-size:12px;color:var(--text-secondary)">${r.last}</td>
            <td>${badge}</td>
            <td style="display:flex;gap:5px">
                ${suspendBtn}
                <a href="/admin/sellers/payment-requests" style="padding:4px 10px;border-radius:6px;font-size:11.5px;cursor:pointer;border:1px solid var(--border);background:#fff;text-decoration:none;color:var(--text-primary)">Payments</a>
            </td>
        </tr>`;
    }).join('');
}

function loadData(){
    const active=DEMO_SELLERS.filter(r=>r.status==='active').length;
    const susp=DEMO_SELLERS.filter(r=>r.status==='suspended').length;
    document.getElementById('sTotal').textContent=DEMO_SELLERS.length;
    document.getElementById('sActive').textContent=active;
    document.getElementById('sSuspended').textContent=susp;
    document.getElementById('sWalletTotal').textContent='₹31,590';
    filtered=[...DEMO_SELLERS];
    renderTable();
}

function toggleStatus(id){
    const r=DEMO_SELLERS.find(s=>s.id===id);
    if(!r)return;
    if(confirm(`${r.status==='active'?'Suspend':'Activate'} seller "${r.name}"?`)){
        r.status=r.status==='active'?'suspended':'active';
        filterTable();
    }
}

function showAddModal(){document.getElementById('addModal').style.display='flex';}
function submitAdd(){document.getElementById('addModal').style.display='none';alert('Seller added successfully!');}
function exportSellers(){alert('Exporting seller list as CSV...');}

document.addEventListener('DOMContentLoaded',loadData);
</script>
@endpush
@endsection
