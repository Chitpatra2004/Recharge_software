@extends('layouts.admin')
@section('title','Account Report')

@push('head')
<style>
.summary-strip{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px}
@media(max-width:900px){.summary-strip{grid-template-columns:1fr 1fr}}
.stat-card{background:var(--card-bg);border-radius:var(--radius);padding:16px 20px;box-shadow:var(--shadow-sm);border-left:4px solid transparent}
.stat-card.blue{border-color:var(--accent-blue)}.stat-card.green{border-color:var(--accent-green)}
.stat-card.orange{border-color:var(--accent-orange)}.stat-card.purple{border-color:var(--accent-purple)}
.stat-card .val{font-size:22px;font-weight:700;margin-bottom:2px}.stat-card .lbl{font-size:11.5px;color:var(--text-secondary)}
.tab-bar{display:flex;gap:6px;margin-bottom:18px;border-bottom:1px solid var(--border);padding-bottom:0}
.tab-btn{padding:8px 18px;border:none;background:none;font-size:13px;font-weight:500;color:var(--text-secondary);cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-1px;font-family:inherit}
.tab-btn.active{color:var(--accent-blue);border-bottom-color:var(--accent-blue);font-weight:600}
.tab-pane{display:none}.tab-pane.active{display:block}
.filter-bar{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:14px}
.filter-bar input,.filter-bar select{padding:7px 11px;border:1px solid var(--border);border-radius:8px;font-size:13px;background:var(--card-bg);color:var(--text-primary)}
.admin-remark-cell{max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:11.5px;color:#7c3aed;font-style:italic}
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Account Report</h1>
        <p class="page-sub">Wallet ledger, payment entries &amp; day book</p>
    </div>
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <input type="date" id="dateFrom" style="padding:7px 11px;border:1px solid var(--border);border-radius:8px;font-size:13px;background:var(--card-bg)">
        <input type="date" id="dateTo"   style="padding:7px 11px;border:1px solid var(--border);border-radius:8px;font-size:13px;background:var(--card-bg)">
        <button class="btn btn-primary btn-sm" onclick="loadData()">Apply</button>
        <button class="btn btn-outline" onclick="exportReport()">Export</button>
    </div>
</div>

<div class="summary-strip">
    <div class="stat-card blue"><div class="val" id="sTotalEntries">—</div><div class="lbl">Total Entries</div></div>
    <div class="stat-card green"><div class="val" id="sCredits">—</div><div class="lbl">Total Credits</div></div>
    <div class="stat-card orange"><div class="val" id="sDebits">—</div><div class="lbl">Total Debits</div></div>
    <div class="stat-card purple"><div class="val" id="sNet">—</div><div class="lbl">Net (Credits − Debits)</div></div>
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
                <select id="lType" onchange="loadData()">
                    <option value="">All Types</option>
                    <option value="credit">Credit</option>
                    <option value="debit">Debit</option>
                </select>
                <input type="text" id="lSearch" placeholder="Search desc / RRN / name…" oninput="loadData()">
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date &amp; Time</th>
                        <th>User</th>
                        <th>Description</th>
                        <th>Bank / RRN</th>
                        <th>Remark</th>
                        <th>Admin Remark</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Balance After</th>
                        <th>Edit</th>
                    </tr>
                </thead>
                <tbody id="ledgerBody">
                    <tr><td colspan="10" style="text-align:center;padding:30px;color:var(--text-muted)">Loading…</td></tr>
                </tbody>
            </table>
        </div>
        <div id="ledgerPagination"></div>
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
                <tbody id="sellerSummaryBody">
                    <tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text-muted)">Loading…</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Day Book Tab --}}
<div class="tab-pane" id="tab-daybook">
    <div class="card">
        <div class="card-header" style="display:flex;align-items:center;justify-content:space-between">
            <span style="font-weight:600;font-size:14px">Day Book</span>
            <input type="date" id="daybookDate" value="{{ date('Y-m-d') }}" onchange="loadDaybook()" style="padding:6px 10px;border:1px solid var(--border);border-radius:8px;font-size:13px;background:var(--card-bg)">
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Time</th><th>User</th><th>Particulars</th><th>Bank / RRN</th><th>Remark</th><th>Admin Remark</th><th>Debit (₹)</th><th>Credit (₹)</th><th>Balance After</th><th>Edit</th></tr>
                </thead>
                <tbody id="daybookBody">
                    <tr><td colspan="10" style="text-align:center;padding:30px;color:var(--text-muted)">Loading…</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ── EDIT MODAL ── --}}
<div id="editModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:600;align-items:center;justify-content:center">
    <div class="card" style="width:500px;max-width:95vw;max-height:92vh;overflow-y:auto;padding:0">
        <div style="padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
            <div>
                <h3 style="font-size:15px;font-weight:700;color:var(--text-primary)">Edit Payment Entry</h3>
                <div id="editModalSub" style="font-size:11.5px;color:var(--text-muted);margin-top:2px"></div>
            </div>
            <button onclick="closeEditModal()" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--text-muted);line-height:1">&times;</button>
        </div>
        <div style="padding:20px 22px;display:flex;flex-direction:column;gap:14px">

            {{-- Bank Name --}}
            <div>
                <label style="display:block;font-size:11.5px;font-weight:600;color:var(--text-secondary);margin-bottom:5px;text-transform:uppercase;letter-spacing:.4px">Bank Name</label>
                <select id="eBank" style="width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:8px;font-size:13px;background:var(--card-bg);color:var(--text-primary)">
                    <option value="">— Select Bank —</option>
                    <option>State Bank of India (SBI)</option>
                    <option>HDFC Bank</option>
                    <option>ICICI Bank</option>
                    <option>Axis Bank</option>
                    <option>Punjab National Bank (PNB)</option>
                    <option>Bank of Baroda (BOB)</option>
                    <option>Canara Bank</option>
                    <option>Union Bank of India</option>
                    <option>Kotak Mahindra Bank</option>
                    <option>IndusInd Bank</option>
                    <option>Yes Bank</option>
                    <option>IDFC First Bank</option>
                    <option>Federal Bank</option>
                    <option>Bank of India (BOI)</option>
                    <option>Central Bank of India</option>
                    <option>Indian Bank</option>
                    <option>UCO Bank</option>
                    <option>Bank of Maharashtra</option>
                    <option>Paytm Payments Bank</option>
                    <option>Airtel Payments Bank</option>
                    <option>Other / UPI</option>
                </select>
            </div>

            {{-- RRN --}}
            <div>
                <label style="display:block;font-size:11.5px;font-weight:600;color:var(--text-secondary);margin-bottom:5px;text-transform:uppercase;letter-spacing:.4px">RRN / UTR / Reference No.</label>
                <input type="text" id="eRrn" placeholder="Enter RRN / UTR number" style="width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:8px;font-size:13px">
            </div>

            {{-- Amount & Type --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:600;color:var(--text-secondary);margin-bottom:5px;text-transform:uppercase;letter-spacing:.4px">Amount (₹)</label>
                    <input type="number" id="eAmount" min="0.01" step="0.01" placeholder="0.00" style="width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:8px;font-size:13px">
                </div>
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:600;color:var(--text-secondary);margin-bottom:5px;text-transform:uppercase;letter-spacing:.4px">Type</label>
                    <select id="eType" style="width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:8px;font-size:13px;background:var(--card-bg);color:var(--text-primary)">
                        <option value="credit">Credit (+)</option>
                        <option value="debit">Debit (−)</option>
                    </select>
                </div>
            </div>

            {{-- Remark --}}
            <div>
                <label style="display:block;font-size:11.5px;font-weight:600;color:var(--text-secondary);margin-bottom:5px;text-transform:uppercase;letter-spacing:.4px">Remark</label>
                <input type="text" id="eRemark" placeholder="Short remark for this entry" style="width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:8px;font-size:13px">
            </div>

            {{-- Admin Remark (visible to employee & admin) --}}
            <div style="background:#faf5ff;border:1px solid #e9d5ff;border-radius:10px;padding:14px">
                <label style="display:flex;align-items:center;gap:6px;font-size:11.5px;font-weight:700;color:#7c3aed;margin-bottom:6px;text-transform:uppercase;letter-spacing:.4px">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Admin Remark <span style="font-size:10px;font-weight:500;color:#a855f7">(visible to employee &amp; admin)</span>
                </label>
                <textarea id="eAdminRemark" rows="3" placeholder="Internal admin remark — visible to all staff…" style="width:100%;padding:9px 12px;border:1px solid #d8b4fe;border-radius:8px;font-size:13px;resize:vertical;background:#fff;color:var(--text-primary)"></textarea>
            </div>
        </div>
        <div style="padding:14px 22px;border-top:1px solid var(--border);display:flex;gap:10px">
            <button id="editSaveBtn" onclick="saveEdit()" style="flex:1;padding:10px;background:var(--accent-blue);color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer;font-family:inherit">Save Changes</button>
            <button onclick="closeEditModal()" style="padding:10px 18px;border:1px solid var(--border);border-radius:8px;font-size:13px;cursor:pointer;background:var(--card-bg);font-family:inherit">Cancel</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
const T = ()=>localStorage.getItem('emp_token');
const eFetch=(url,m='GET',b=null)=>fetch(url,{method:m,headers:{'Authorization':'Bearer '+T(),'Content-Type':'application/json','Accept':'application/json'},...(b?{body:JSON.stringify(b)}:{})}).then(async r=>{const d=await r.json();if(!r.ok)throw new Error(d.message||'Error');return d;});

function fmtAmt(n){return '₹'+Number(n||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2});}
function fmtDT(d){return d?new Date(d).toLocaleString('en-IN',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'}):'—';}
function fmtTime(d){return d?new Date(d).toLocaleTimeString('en-IN',{hour:'2-digit',minute:'2-digit'}):'—';}

let currentPage=1;

// ── Tab switch ─────────────────────────────────────────────────────────────
function switchTab(tab,btn){
    document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(p=>p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('tab-'+tab).classList.add('active');
    if(tab==='sellers') loadSellerSummary();
    if(tab==='daybook') loadDaybook();
}

// ── Main ledger ────────────────────────────────────────────────────────────
function buildParams(page){
    const p=new URLSearchParams({page:page||currentPage});
    const df=document.getElementById('dateFrom').value;
    const dt=document.getElementById('dateTo').value;
    const tp=document.getElementById('lType').value;
    const q=document.getElementById('lSearch').value.trim();
    if(df) p.set('date_from',df);
    if(dt) p.set('date_to',dt);
    if(tp) p.set('type',tp);
    if(q)  p.set('search',q);
    return p;
}

function loadData(page){
    currentPage=page||1;
    document.getElementById('ledgerBody').innerHTML='<tr><td colspan="10" style="text-align:center;padding:30px;color:var(--text-muted)">Loading…</td></tr>';
    eFetch(`/api/v1/employee/account-ledger?${buildParams(currentPage)}`).then(d=>{
        const rows = d.data?.data || [];
        const sum  = d.summary || {};
        const cred = Number(sum.total_credits||0), deb = Number(sum.total_debits||0);
        document.getElementById('sTotalEntries').textContent = sum.total_entries || 0;
        document.getElementById('sCredits').textContent      = fmtAmt(cred);
        document.getElementById('sDebits').textContent       = fmtAmt(deb);
        document.getElementById('sNet').textContent          = fmtAmt(cred - deb);

        if(!rows.length){
            document.getElementById('ledgerBody').innerHTML='<tr><td colspan="10" style="text-align:center;padding:30px;color:var(--text-muted)">No entries found</td></tr>';
            document.getElementById('ledgerPagination').innerHTML='';
            return;
        }
        renderLedger(rows);
        renderPagination(d.data, 'ledgerPagination', loadData);
    }).catch(e=>{
        document.getElementById('ledgerBody').innerHTML=`<tr><td colspan="10" style="text-align:center;padding:20px;color:#ef4444">${e.message}</td></tr>`;
    });
}

function renderLedger(rows){
    document.getElementById('ledgerBody').innerHTML=rows.map(r=>{
        const isCredit = r.type==='credit';
        const adminRmk = r.admin_remark
            ? `<span class="admin-remark-cell" title="${r.admin_remark.replace(/"/g,'&quot;')}">${r.admin_remark}</span>`
            : '<span style="color:var(--text-muted);font-size:11px">—</span>';
        return `<tr>
            <td style="font-size:12px;color:var(--text-secondary);white-space:nowrap">${fmtDT(r.created_at)}</td>
            <td style="font-size:12.5px;font-weight:600">${r.user_name||'—'}</td>
            <td style="font-size:12.5px;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="${(r.description||'').replace(/"/g,'&quot;')}">${r.description||'—'}</td>
            <td style="font-size:11.5px">
                ${r.bank_name?`<div style="font-weight:600;color:var(--text-primary)">${r.bank_name}</div>`:''}
                ${r.rrn?`<div style="font-family:monospace;font-size:10.5px;color:var(--text-muted)">${r.rrn}</div>`:'<span style="color:var(--text-muted);font-size:11px">—</span>'}
            </td>
            <td style="font-size:12px;color:var(--text-secondary)">${r.remark||'—'}</td>
            <td>${adminRmk}</td>
            <td>
                <span style="background:${isCredit?'#d1fae5':'#fee2e2'};color:${isCredit?'#065f46':'#991b1b'};padding:2px 9px;border-radius:20px;font-size:11px;font-weight:700">
                    ${isCredit?'Credit':'Debit'}
                </span>
            </td>
            <td style="font-weight:700;color:${isCredit?'#10b981':'#ef4444'};white-space:nowrap">
                ${isCredit?'+':'−'}${fmtAmt(r.amount)}
            </td>
            <td style="font-weight:600;white-space:nowrap">${fmtAmt(r.balance_after)}</td>
            <td>
                <button onclick='openEditModal(${JSON.stringify(r)})'
                    style="padding:4px 10px;border:1px solid var(--accent-blue);color:var(--accent-blue);background:#fff;border-radius:6px;font-size:11.5px;cursor:pointer;font-weight:600">
                    Edit
                </button>
            </td>
        </tr>`;
    }).join('');
}

// ── Seller summary ─────────────────────────────────────────────────────────
function loadSellerSummary(){
    document.getElementById('sellerSummaryBody').innerHTML='<tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text-muted)">Loading…</td></tr>';
    eFetch('/api/v1/employee/sellers?per_page=50').then(d=>{
        const sellers = d.data?.data || [];
        if(!sellers.length){
            document.getElementById('sellerSummaryBody').innerHTML='<tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text-muted)">No sellers found</td></tr>';
            return;
        }
        document.getElementById('sellerSummaryBody').innerHTML=sellers.map(s=>`<tr>
            <td style="font-weight:600">${s.name}</td>
            <td style="font-weight:700;color:#10b981">${fmtAmt(s.wallet_balance||0)}</td>
            <td>${s.recharge_transactions_count||0} txns</td>
            <td>—</td><td>—</td>
            <td style="font-size:12px;color:var(--text-secondary)">${s.created_at?new Date(s.created_at).toLocaleDateString('en-IN'):'—'}</td>
        </tr>`).join('');
    }).catch(()=>{
        document.getElementById('sellerSummaryBody').innerHTML='<tr><td colspan="6" style="text-align:center;padding:20px;color:#ef4444">Failed to load</td></tr>';
    });
}

// ── Day book ───────────────────────────────────────────────────────────────
function loadDaybook(){
    const date = document.getElementById('daybookDate').value;
    document.getElementById('daybookBody').innerHTML='<tr><td colspan="10" style="text-align:center;padding:30px;color:var(--text-muted)">Loading…</td></tr>';
    const p = new URLSearchParams({date_from:date, date_to:date, per_page:100});
    eFetch(`/api/v1/employee/account-ledger?${p}`).then(d=>{
        const rows = d.data?.data || [];
        if(!rows.length){
            document.getElementById('daybookBody').innerHTML='<tr><td colspan="10" style="text-align:center;padding:30px;color:var(--text-muted)">No entries for this date</td></tr>';
            return;
        }
        document.getElementById('daybookBody').innerHTML=rows.map(r=>{
            const isCredit = r.type==='credit';
            const adminRmk = r.admin_remark
                ? `<span class="admin-remark-cell" title="${r.admin_remark.replace(/"/g,'&quot;')}">${r.admin_remark}</span>`
                : '<span style="color:var(--text-muted);font-size:11px">—</span>';
            return `<tr>
                <td style="font-size:12px;color:var(--text-secondary);white-space:nowrap">${fmtTime(r.created_at)}</td>
                <td style="font-size:12.5px;font-weight:600">${r.user_name||'—'}</td>
                <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${r.description||'—'}</td>
                <td style="font-size:11.5px">
                    ${r.bank_name?`<div style="font-weight:600">${r.bank_name}</div>`:''}
                    ${r.rrn?`<div style="font-family:monospace;font-size:10.5px;color:var(--text-muted)">${r.rrn}</div>`:'<span style="font-size:11px;color:var(--text-muted)">—</span>'}
                </td>
                <td style="font-size:12px">${r.remark||'—'}</td>
                <td>${adminRmk}</td>
                <td style="color:#ef4444;font-weight:${!isCredit?600:400}">${!isCredit?fmtAmt(r.amount).replace('₹',''):'—'}</td>
                <td style="color:#10b981;font-weight:${isCredit?600:400}">${isCredit?fmtAmt(r.amount).replace('₹',''):'—'}</td>
                <td style="font-weight:600">${fmtAmt(r.balance_after)}</td>
                <td>
                    <button onclick='openEditModal(${JSON.stringify(r)})'
                        style="padding:4px 10px;border:1px solid var(--accent-blue);color:var(--accent-blue);background:#fff;border-radius:6px;font-size:11.5px;cursor:pointer;font-weight:600">
                        Edit
                    </button>
                </td>
            </tr>`;
        }).join('');
    }).catch(()=>{
        document.getElementById('daybookBody').innerHTML='<tr><td colspan="10" style="text-align:center;padding:20px;color:#ef4444">Failed to load</td></tr>';
    });
}

// ── Pagination ─────────────────────────────────────────────────────────────
function renderPagination(meta, wrapperId, callback){
    const wrap=document.getElementById(wrapperId);
    if(!wrap||!meta) return;
    const lp=meta.last_page||1, cp=meta.current_page||1;
    let html=`<div style="display:flex;align-items:center;justify-content:space-between;padding:12px 18px;border-top:1px solid var(--border)">
        <span style="font-size:13px;color:var(--text-secondary)">Total: ${meta.total||0} records</span>
        <div style="display:flex;gap:5px">`;
    for(let i=1;i<=lp;i++)
        html+=`<button onclick="${callback.name}(${i})" style="padding:5px 10px;border-radius:6px;border:1.5px solid ${i===cp?'var(--accent-blue)':'var(--border)'};background:${i===cp?'var(--accent-blue)':'var(--card-bg)'};color:${i===cp?'#fff':'var(--text-primary)'};font-size:12.5px;cursor:pointer">${i}</button>`;
    html+=`</div></div>`;
    wrap.innerHTML=html;
}

// ── Edit modal ─────────────────────────────────────────────────────────────
let editId = null;

function openEditModal(row){
    editId = row.id;
    document.getElementById('editModalSub').textContent = `#${row.id} · ${fmtDT(row.created_at)} · ${row.user_name||''}`;
    // Bank name
    const bankSel = document.getElementById('eBank');
    const matchedOpt = [...bankSel.options].find(o=>o.value===row.bank_name||o.text===row.bank_name);
    bankSel.value = matchedOpt ? matchedOpt.value : (row.bank_name||'');
    if(!matchedOpt && row.bank_name){
        // Set value directly if not found in list
        bankSel.value = '';
    }
    document.getElementById('eRrn').value          = row.rrn||'';
    document.getElementById('eAmount').value       = row.amount||'';
    document.getElementById('eType').value         = row.type||'credit';
    document.getElementById('eRemark').value       = row.remark||'';
    document.getElementById('eAdminRemark').value  = row.admin_remark||'';
    document.getElementById('editModal').style.display='flex';
}

function closeEditModal(){
    document.getElementById('editModal').style.display='none';
    editId=null;
}

function saveEdit(){
    if(!editId) return;
    const bank   = document.getElementById('eBank').value;
    const rrn    = document.getElementById('eRrn').value.trim();
    const amount = parseFloat(document.getElementById('eAmount').value);
    const type   = document.getElementById('eType').value;
    const remark = document.getElementById('eRemark').value.trim();
    const adm    = document.getElementById('eAdminRemark').value.trim();

    if(!amount||amount<=0){ alert('Enter a valid amount.'); return; }

    const btn = document.getElementById('editSaveBtn');
    btn.disabled=true; btn.textContent='Saving…';

    eFetch(`/api/v1/employee/wallet-transactions/${editId}`,'PUT',{
        bank_name: bank||null, rrn:rrn||null, amount, type, remark:remark||null, admin_remark:adm||null
    }).then(()=>{
        closeEditModal();
        loadData(currentPage);
        // Reload daybook if visible
        const dbPane = document.getElementById('tab-daybook');
        if(dbPane.classList.contains('active')) loadDaybook();
    }).catch(e=>alert(e.message||'Failed to save.'))
    .finally(()=>{ btn.disabled=false; btn.textContent='Save Changes'; });
}

// Close modal on outside click
document.addEventListener('click', e=>{
    const m=document.getElementById('editModal');
    if(m.style.display==='flex' && e.target===m) closeEditModal();
});

function exportReport(){
    const p=buildParams(1);
    p.set('per_page',1000);
    window.open(`/api/v1/employee/account-ledger?${p}`,'_blank');
}

// ── Init ───────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded',()=>{
    // Default dates: last 30 days
    const today=new Date(), from=new Date();
    from.setDate(today.getDate()-30);
    document.getElementById('dateFrom').value=from.toISOString().slice(0,10);
    document.getElementById('dateTo').value=today.toISOString().slice(0,10);
    loadData();
});
</script>
@endpush
@endsection
