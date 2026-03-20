@extends('layouts.admin')
@section('title','User Bank List')

@push('head')
<style>
.filter-bar{display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;margin-bottom:18px}
.filter-bar input,.filter-bar select{padding:7px 11px;border:1px solid var(--border);border-radius:8px;font-size:13px;background:#fff;color:var(--text-primary)}
.summary-strip{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px}
@media(max-width:900px){.summary-strip{grid-template-columns:1fr 1fr}}
.stat-card{background:var(--card-bg);border-radius:var(--radius);padding:16px 20px;box-shadow:var(--shadow-sm);border-left:4px solid transparent}
.stat-card.green{border-color:var(--accent-green)}
.stat-card.blue{border-color:var(--accent-blue)}
.stat-card.orange{border-color:var(--accent-orange)}
.stat-card.red{border-color:var(--accent-red)}
.stat-card .val{font-size:22px;font-weight:700;margin-bottom:2px}
.stat-card .lbl{font-size:11.5px;color:var(--text-secondary)}
.acct-num{font-family:monospace;font-size:12px;letter-spacing:.5px}
.verified-badge{background:#d1fae5;color:#065f46;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:600}
.unverified-badge{background:#fef3c7;color:#92400e;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:600}
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">User Bank List</h1>
        <p class="page-sub">Bank accounts registered by sellers for wallet withdrawals & payments</p>
    </div>
    <div style="display:flex;gap:8px">
        <button class="btn btn-outline" onclick="exportBanks()">Export CSV</button>
        <button class="btn btn-primary" onclick="loadData()">Refresh</button>
    </div>
</div>

<div class="summary-strip">
    <div class="stat-card green"><div class="val" id="sTotalAccounts">—</div><div class="lbl">Total Accounts</div></div>
    <div class="stat-card blue"><div class="val" id="sVerified">—</div><div class="lbl">Verified</div></div>
    <div class="stat-card orange"><div class="val" id="sUnverified">—</div><div class="lbl">Pending Verification</div></div>
    <div class="stat-card red"><div class="val" id="sBanks">—</div><div class="lbl">Unique Banks</div></div>
</div>

<div class="card">
    <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
        <span style="font-weight:600;font-size:14px">Registered Bank Accounts</span>
        <div class="filter-bar" style="margin:0">
            <input type="text" id="searchSeller" placeholder="Search seller / IFSC / account..." oninput="filterTable()">
            <select id="fBank" onchange="filterTable()">
                <option value="">All Banks</option>
                <option>SBI</option><option>HDFC</option><option>ICICI</option><option>Axis</option>
                <option>PNB</option><option>BOB</option><option>Canara</option><option>Kotak</option>
            </select>
            <select id="fVerified" onchange="filterTable()">
                <option value="">All Status</option>
                <option value="verified">Verified</option>
                <option value="pending">Pending</option>
            </select>
        </div>
    </div>
    <div class="table-wrap">
        <table id="bankTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Seller Name</th>
                    <th>Account Holder</th>
                    <th>Account Number</th>
                    <th>IFSC Code</th>
                    <th>Bank</th>
                    <th>Branch</th>
                    <th>Added On</th>
                    <th>Verified</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="bankBody">
                <tr><td colspan="10" style="text-align:center;padding:30px;color:var(--text-muted)">Loading...</td></tr>
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
const DEMO_BANKS = [
    {id:1,seller:'RajeshTelecom',holder:'Rajesh Kumar Sharma',acct:'3721****8901',full_acct:'372100008901',ifsc:'HDFC0001234',bank:'HDFC',branch:'Connaught Place, Delhi',added:'10 Jan 2026',verified:true},
    {id:2,seller:'PriyaRecharge',holder:'Priya Nair',acct:'4521****2309',full_acct:'452100002309',ifsc:'SBIN0005678',bank:'SBI',branch:'MG Road, Bangalore',added:'15 Jan 2026',verified:true},
    {id:3,seller:'SunilShop',holder:'Sunil Mehta',acct:'1234****5678',full_acct:'123400005678',ifsc:'ICIC0002345',bank:'ICICI',branch:'FC Road, Pune',added:'20 Jan 2026',verified:false},
    {id:4,seller:'AmitStore',holder:'Amit Patel',acct:'9876****4321',full_acct:'987600004321',ifsc:'UTIB0003456',bank:'Axis',branch:'CG Road, Ahmedabad',added:'5 Feb 2026',verified:true},
    {id:5,seller:'KiranMobile',holder:'Kiran Reddy',acct:'5555****7777',full_acct:'555500007777',ifsc:'PUNB0004567',bank:'PNB',branch:'Banjara Hills, Hyderabad',added:'8 Feb 2026',verified:false},
    {id:6,seller:'MohanTelecom',holder:'Mohan Lal Singh',acct:'2468****1357',full_acct:'246800001357',ifsc:'BARB0005678',bank:'BOB',branch:'Hazratganj, Lucknow',added:'12 Feb 2026',verified:true},
    {id:7,seller:'AnilRecharge',holder:'Anil Kumar',acct:'1357****2468',full_acct:'135700002468',ifsc:'CNRB0006789',bank:'Canara',branch:'Anna Nagar, Chennai',added:'18 Feb 2026',verified:true},
    {id:8,seller:'RaniShop',holder:'Rani Devi',acct:'7890****1234',full_acct:'789000001234',ifsc:'KKBK0007890',bank:'Kotak',branch:'Salt Lake, Kolkata',added:'22 Feb 2026',verified:false},
];

let filtered = [...DEMO_BANKS];

function filterTable(){
    const q=document.getElementById('searchSeller').value.toLowerCase();
    const bank=document.getElementById('fBank').value;
    const ver=document.getElementById('fVerified').value;
    filtered=DEMO_BANKS.filter(r=>{
        const matchQ=!q||(r.seller.toLowerCase().includes(q)||r.ifsc.toLowerCase().includes(q)||r.full_acct.includes(q)||r.holder.toLowerCase().includes(q));
        const matchB=!bank||r.bank===bank;
        const matchV=!ver||(ver==='verified'?r.verified:!r.verified);
        return matchQ&&matchB&&matchV;
    });
    renderTable();
}

function renderTable(){
    const tbody=document.getElementById('bankBody');
    if(!filtered.length){tbody.innerHTML='<tr><td colspan="10" style="text-align:center;padding:30px;color:var(--text-muted)">No accounts found</td></tr>';return;}
    tbody.innerHTML=filtered.map((r,i)=>`<tr>
        <td style="color:var(--text-muted)">${i+1}</td>
        <td style="font-weight:600">${r.seller}</td>
        <td>${r.holder}</td>
        <td class="acct-num">${r.acct}</td>
        <td><code style="background:#f1f5f9;padding:1px 6px;border-radius:4px;font-size:12px">${r.ifsc}</code></td>
        <td style="font-weight:600">${r.bank}</td>
        <td style="font-size:12px;color:var(--text-secondary)">${r.branch}</td>
        <td style="font-size:12px;color:var(--text-secondary)">${r.added}</td>
        <td>${r.verified?'<span class="verified-badge">Verified</span>':'<span class="unverified-badge">Pending</span>'}</td>
        <td>
            ${!r.verified?`<button onclick="verifyAccount(${r.id})" style="padding:4px 10px;border-radius:6px;font-size:11.5px;cursor:pointer;border:1px solid var(--accent-green);color:var(--accent-green);background:#fff">Verify</button>`:''}
            <button onclick="viewAccount(${r.id})" style="padding:4px 10px;border-radius:6px;font-size:11.5px;cursor:pointer;border:1px solid var(--border);background:#fff;margin-left:4px">View</button>
        </td>
    </tr>`).join('');
}

function loadData(){
    const v=DEMO_BANKS.filter(r=>r.verified).length;
    const u=DEMO_BANKS.filter(r=>!r.verified).length;
    const banks=new Set(DEMO_BANKS.map(r=>r.bank)).size;
    document.getElementById('sTotalAccounts').textContent=DEMO_BANKS.length;
    document.getElementById('sVerified').textContent=v;
    document.getElementById('sUnverified').textContent=u;
    document.getElementById('sBanks').textContent=banks;
    filtered=[...DEMO_BANKS];
    renderTable();
}

function verifyAccount(id){if(confirm('Mark this account as verified?')){const r=DEMO_BANKS.find(b=>b.id===id);if(r)r.verified=true;filterTable();}}
function viewAccount(id){const r=DEMO_BANKS.find(b=>b.id===id);if(r)alert(`Seller: ${r.seller}\nHolder: ${r.holder}\nAccount: ${r.full_acct}\nIFSC: ${r.ifsc}\nBank: ${r.bank}, ${r.branch}`);}

function exportBanks(){
    const rows=['Seller,Account Holder,Account Number,IFSC,Bank,Branch,Added,Verified'];
    DEMO_BANKS.forEach(r=>rows.push(`"${r.seller}","${r.holder}","${r.full_acct}",${r.ifsc},${r.bank},"${r.branch}",${r.added},${r.verified?'Yes':'No'}`));
    const a=document.createElement('a');
    a.href='data:text/csv;charset=utf-8,'+encodeURIComponent(rows.join('\n'));
    a.download='bank_accounts.csv';a.click();
}

document.addEventListener('DOMContentLoaded',loadData);
</script>
@endpush
@endsection
