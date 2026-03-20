@extends('layouts.admin')
@section('title','Account Report')

@push('head')
<style>
.summary-strip{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px}
@media(max-width:900px){.summary-strip{grid-template-columns:1fr 1fr}}
.stat-card{background:var(--card-bg);border-radius:var(--radius);padding:16px 20px;box-shadow:var(--shadow-sm);border-left:4px solid transparent}
.stat-card.blue{border-color:var(--accent-blue)}
.stat-card.green{border-color:var(--accent-green)}
.stat-card.orange{border-color:var(--accent-orange)}
.stat-card.purple{border-color:var(--accent-purple)}
.stat-card .val{font-size:22px;font-weight:700;margin-bottom:2px}
.stat-card .lbl{font-size:11.5px;color:var(--text-secondary)}
.tab-bar{display:flex;gap:6px;margin-bottom:18px;border-bottom:1px solid var(--border);padding-bottom:0}
.tab-btn{padding:8px 18px;border:none;background:none;font-size:13px;font-weight:500;color:var(--text-secondary);cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-1px}
.tab-btn.active{color:var(--accent-blue);border-bottom-color:var(--accent-blue);font-weight:600}
.tab-pane{display:none}.tab-pane.active{display:block}
.filter-bar{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:14px}
.filter-bar input,.filter-bar select{padding:7px 11px;border:1px solid var(--border);border-radius:8px;font-size:13px;background:#fff;color:var(--text-primary)}
.trend-up{color:var(--accent-green);font-size:12px;font-weight:600}
.trend-dn{color:var(--accent-red);font-size:12px;font-weight:600}
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Account Report</h1>
        <p class="page-sub">Admin wallet ledger, top-up history & transaction summary</p>
    </div>
    <div style="display:flex;gap:8px">
        <select id="periodSelect" onchange="loadData()" style="padding:7px 12px;border:1px solid var(--border);border-radius:8px;font-size:13px;background:#fff">
            <option value="today">Today</option>
            <option value="week" selected>This Week</option>
            <option value="month">This Month</option>
            <option value="custom">Custom Range</option>
        </select>
        <button class="btn btn-outline" onclick="exportReport()">Export</button>
    </div>
</div>

<div class="summary-strip">
    <div class="stat-card blue"><div class="val" id="sOpenBal">—</div><div class="lbl">Opening Balance</div></div>
    <div class="stat-card green"><div class="val" id="sCredits">—</div><div class="lbl">Total Credits</div></div>
    <div class="stat-card orange"><div class="val" id="sDebits">—</div><div class="lbl">Total Debits</div></div>
    <div class="stat-card purple"><div class="val" id="sCloseBal">—</div><div class="lbl">Closing Balance</div></div>
</div>

<div class="tab-bar">
    <button class="tab-btn active" onclick="switchTab('ledger',this)">Wallet Ledger</button>
    <button class="tab-btn" onclick="switchTab('sellers',this)">Seller-wise Summary</button>
    <button class="tab-btn" onclick="switchTab('daybook',this)">Day Book</button>
</div>

{{-- Ledger Tab --}}
<div class="tab-pane active" id="tab-ledger">
    <div class="card">
        <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
            <span style="font-weight:600;font-size:14px">Wallet Ledger</span>
            <div class="filter-bar" style="margin:0">
                <select id="lType">
                    <option value="">All Types</option>
                    <option value="credit">Credit</option>
                    <option value="debit">Debit</option>
                </select>
                <input type="text" id="lSearch" placeholder="Search description..." oninput="filterLedger()">
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Date & Time</th><th>Description</th><th>Ref ID</th><th>Type</th><th>Amount</th><th>Balance After</th></tr>
                </thead>
                <tbody id="ledgerBody"></tbody>
            </table>
        </div>
    </div>
</div>

{{-- Sellers Tab --}}
<div class="tab-pane" id="tab-sellers">
    <div class="card">
        <div class="card-header"><span style="font-weight:600;font-size:14px">Seller-wise Account Summary</span></div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Seller</th><th>Current Balance</th><th>Total Recharged</th><th>Total Top-up Received</th><th>Commission Earned</th><th>Last Activity</th></tr>
                </thead>
                <tbody id="sellerSummaryBody"></tbody>
            </table>
        </div>
    </div>
</div>

{{-- Day Book Tab --}}
<div class="tab-pane" id="tab-daybook">
    <div class="card">
        <div class="card-header" style="display:flex;align-items:center;justify-content:space-between">
            <span style="font-weight:600;font-size:14px">Day Book</span>
            <input type="date" id="daybookDate" value="{{ date('Y-m-d') }}" onchange="loadDaybook()" style="padding:6px 10px;border:1px solid var(--border);border-radius:8px;font-size:13px">
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Time</th><th>Particulars</th><th>Debit (₹)</th><th>Credit (₹)</th><th>Balance (₹)</th></tr>
                </thead>
                <tbody id="daybookBody"></tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
const LEDGER_DEMO = [
    {dt:'20 Mar 2026, 11:30 AM',desc:'Top-up from Superadmin',ref:'TOP20260001',type:'credit',amt:50000,bal:127500},
    {dt:'20 Mar 2026, 10:45 AM',desc:'Recharge by RajeshTelecom',ref:'TXN2600041',type:'debit',amt:199,bal:77500},
    {dt:'20 Mar 2026, 10:20 AM',desc:'Commission to PriyaRecharge',ref:'COM2600040',type:'debit',amt:12.50,bal:77699},
    {dt:'19 Mar 2026, 05:00 PM',desc:'Recharge by AmitStore',ref:'TXN2600039',type:'debit',amt:299,bal:77711.50},
    {dt:'19 Mar 2026, 03:30 PM',desc:'Top-up from Superadmin',ref:'TOP20260002',type:'credit',amt:25000,bal:78010.50},
    {dt:'19 Mar 2026, 12:00 PM',desc:'Recharge by SunilShop',ref:'TXN2600038',type:'debit',amt:149,bal:53010.50},
    {dt:'18 Mar 2026, 09:15 AM',desc:'Refund issued for TXN2600030',ref:'REF2600005',type:'credit',amt:99,bal:53159.50},
    {dt:'18 Mar 2026, 08:00 AM',desc:'Recharge by KiranMobile',ref:'TXN2600037',type:'debit',amt:399,bal:53060.50},
];

const SELLER_DEMO = [
    {seller:'RajeshTelecom',balance:'₹12,450',recharged:'₹1,85,230',topup:'₹2,00,000',commission:'₹3,704',last:'20 Mar 2026'},
    {seller:'PriyaRecharge',balance:'₹8,720',recharged:'₹1,42,100',topup:'₹1,50,000',commission:'₹2,842',last:'20 Mar 2026'},
    {seller:'SunilShop',balance:'₹5,310',recharged:'₹98,450',topup:'₹1,00,000',commission:'₹1,969',last:'19 Mar 2026'},
    {seller:'AmitStore',balance:'₹3,220',recharged:'₹75,600',topup:'₹80,000',commission:'₹1,512',last:'20 Mar 2026'},
    {seller:'KiranMobile',balance:'₹1,890',recharged:'₹45,300',topup:'₹50,000',commission:'₹906',last:'19 Mar 2026'},
];

const DAYBOOK_DEMO = [
    {time:'09:12 AM',particulars:'Opening Balance',dr:'',cr:'',bal:'77,500.00'},
    {time:'09:34 AM',particulars:'Recharge - Airtel ₹199 (9876543210)',dr:'199.00',cr:'',bal:'77,301.00'},
    {time:'10:05 AM',particulars:'Commission credit - RajeshTelecom',dr:'',cr:'12.50',bal:'77,313.50'},
    {time:'10:22 AM',particulars:'Recharge - Jio ₹299 (8765432109)',dr:'299.00',cr:'',bal:'77,014.50'},
    {time:'10:45 AM',particulars:'Top-up received from SuperAdmin',dr:'',cr:'5000.00',bal:'82,014.50'},
    {time:'11:30 AM',particulars:'Recharge - BSNL ₹99 (7654321098)',dr:'99.00',cr:'',bal:'81,915.50'},
    {time:'12:15 PM',particulars:'Refund credit - TXN2600025',dr:'',cr:'149.00',bal:'82,064.50'},
];

function switchTab(tab,btn){
    document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(p=>p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('tab-'+tab).classList.add('active');
}

function filterLedger(){
    const t=document.getElementById('lType').value;
    const q=document.getElementById('lSearch').value.toLowerCase();
    renderLedger(LEDGER_DEMO.filter(r=>(!t||r.type===t)&&(!q||r.desc.toLowerCase().includes(q)||r.ref.toLowerCase().includes(q))));
}

function renderLedger(rows){
    document.getElementById('ledgerBody').innerHTML=rows.map(r=>`<tr>
        <td style="font-size:12px;color:var(--text-secondary)">${r.dt}</td>
        <td>${r.desc}</td>
        <td style="font-family:monospace;font-size:12px">${r.ref}</td>
        <td><span class="txn-status ${r.type==='credit'?'status-success':'status-failed'}" style="font-size:11px">${r.type==='credit'?'Credit':'Debit'}</span></td>
        <td style="font-weight:700;color:${r.type==='credit'?'var(--accent-green)':'var(--accent-red)'}">
            ${r.type==='credit'?'+':'-'}₹${r.amt.toLocaleString('en-IN',{minimumFractionDigits:2})}
        </td>
        <td style="font-weight:600">₹${r.bal.toLocaleString('en-IN',{minimumFractionDigits:2})}</td>
    </tr>`).join('');
}

function loadData(){
    document.getElementById('sOpenBal').textContent='₹77,500';
    document.getElementById('sCredits').textContent='₹75,099';
    document.getElementById('sDebits').textContent='₹24,699';
    document.getElementById('sCloseBal').textContent='₹1,27,900';
    renderLedger(LEDGER_DEMO);
    document.getElementById('sellerSummaryBody').innerHTML=SELLER_DEMO.map(r=>`<tr>
        <td style="font-weight:600">${r.seller}</td>
        <td style="font-weight:700;color:var(--accent-green)">${r.balance}</td>
        <td>${r.recharged}</td>
        <td>${r.topup}</td>
        <td style="color:var(--accent-purple)">${r.commission}</td>
        <td style="font-size:12px;color:var(--text-secondary)">${r.last}</td>
    </tr>`).join('');
    loadDaybook();
}

function loadDaybook(){
    document.getElementById('daybookBody').innerHTML=DAYBOOK_DEMO.map(r=>`<tr>
        <td style="font-size:12px;color:var(--text-secondary)">${r.time}</td>
        <td>${r.particulars}</td>
        <td style="color:var(--accent-red);font-weight:${r.dr?600:400}">${r.dr||'—'}</td>
        <td style="color:var(--accent-green);font-weight:${r.cr?600:400}">${r.cr||'—'}</td>
        <td style="font-weight:700">₹${r.bal}</td>
    </tr>`).join('');
}

function exportReport(){
    alert('Exporting account report as CSV...');
}

document.addEventListener('DOMContentLoaded',loadData);
</script>
@endpush
@endsection
