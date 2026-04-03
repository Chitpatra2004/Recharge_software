@extends('layouts.user')
@section('title','Bill Payments — BBPS')
@section('page-title','Bill Payments (BBPS)')

@push('head')
<style>
.cat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:10px;margin-bottom:20px}
.cat-btn{display:flex;flex-direction:column;align-items:center;gap:8px;padding:16px 10px;border-radius:12px;background:var(--card);border:1.5px solid var(--border);cursor:pointer;transition:all .15s;font-size:12px;font-weight:600;color:var(--muted);font-family:inherit;width:100%}
.cat-btn:hover{background:var(--card2);color:var(--text);border-color:var(--border2)}
.cat-btn.active{background:rgba(59,130,246,.12);border-color:rgba(59,130,246,.4);color:#60a5fa}
.cat-btn svg{width:26px;height:26px;opacity:.75}
.cat-btn.active svg{opacity:1}
.cat-icon{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:4px}
.finp{width:100%;background:var(--card2);border:1px solid var(--border2);border-radius:8px;padding:10px 13px;font-size:13px;color:var(--text);outline:none;font-family:inherit}
.finp:focus{border-color:var(--blue)}
.flbl{font-size:11.5px;font-weight:600;color:var(--muted);display:block;margin-bottom:5px}
.bill-card{background:rgba(16,185,129,.07);border:1px solid rgba(16,185,129,.2);border-radius:12px;padding:16px 18px;margin-bottom:16px}
.bill-row{display:flex;justify-content:space-between;align-items:center;padding:5px 0;border-bottom:1px solid rgba(255,255,255,.05)}
.bill-row:last-child{border-bottom:none}
.history-badge{font-size:10px;font-weight:600;padding:2px 8px;border-radius:20px}
.ci-electricity{background:#f59e0b20}.ci-water{background:#3b82f620}.ci-gas{background:#ef444420}
.ci-dth{background:#8b5cf620}.ci-broadband{background:#10b98120}.ci-landline{background:#06b6d420}
.ci-insurance{background:#f59e0b20}.ci-loan{background:#ec489920}.ci-fastag{background:#14b8a620}
.ci-credit_card{background:#6366f120}.ci-municipal_tax{background:#0ea5e920}.ci-education{background:#a855f720}.ci-subscription{background:#f4366420}
</style>
@endpush

@section('content')
<div class="breadcrumb">
    <a href="/user/dashboard">Dashboard</a>
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>Bill Payments</span>
</div>

{{-- Tabs --}}
<div style="display:flex;gap:3px;background:var(--card);border:1px solid var(--border);border-radius:10px;padding:4px;margin-bottom:20px;width:fit-content">
    <button class="btn" id="tab-pay"  onclick="switchTab('pay')"  style="background:linear-gradient(135deg,var(--blue-dk),var(--indigo));color:#fff;border-radius:7px">Pay Bill</button>
    <button class="btn btn-outline" id="tab-hist" onclick="switchTab('hist')" style="border:none">History</button>
</div>

{{-- ══════ PAY BILL TAB ══════ --}}
<div id="pane-pay">

    {{-- Category Selection --}}
    <div class="card" style="margin-bottom:18px">
        <div class="card-header"><span class="card-title">Select Service Category</span></div>
        <div class="card-body">
            <div class="cat-grid" id="cat-grid">
                @foreach([
                    ['electricity','Electricity','#f59e0b','M13 10V3L4 14h7v7l9-11h-7z'],
                    ['water','Water','#3b82f6','M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10'],
                    ['gas','Gas','#ef4444','M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z'],
                    ['dth','DTH','#8b5cf6','M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                    ['broadband','Broadband','#10b981','M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0'],
                    ['landline','Landline','#06b6d4','M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z'],
                    ['insurance','Insurance','#f59e0b','M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                    ['loan','Loan EMI','#ec4899','M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
                    ['fastag','FASTag','#14b8a6','M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 2 2-2 2 2 2-2 2 2 2-2 2 2'],
                    ['credit_card','Credit Card','#6366f1','M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
                    ['municipal_tax','Municipal Tax','#0ea5e9','M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z'],
                    ['education','Education','#a855f7','M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222'],
                    ['subscription','Subscription','#f43664','M15 10l4.553-2.069A1 1 0 0121 8.868V15.13a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z'],
                ] as [$cat, $label, $color, $path])
                <button class="cat-btn" id="cat-{{ $cat }}" onclick="selectCat('{{ $cat }}')">
                    <div class="cat-icon ci-{{ $cat }}">
                        <svg fill="none" viewBox="0 0 24 24" stroke="{{ $color }}" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/></svg>
                    </div>
                    {{ $label }}
                </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Bill Form --}}
    <div class="card" id="bill-form-card" style="display:none">
        <div class="card-header" style="justify-content:space-between">
            <span class="card-title" id="form-title">Pay Bill</span>
            <span id="cat-badge" style="font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;background:rgba(59,130,246,.15);color:#60a5fa"></span>
        </div>
        <div class="card-body" style="display:flex;flex-direction:column;gap:14px">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div>
                    <label class="flbl">Biller / Operator *</label>
                    <select id="biller-sel" class="finp" onchange="onBillerChange()">
                        <option value="">Loading billers…</option>
                    </select>
                </div>
                <div>
                    <label class="flbl" id="consumer-label">Consumer / Account Number *</label>
                    <div style="display:flex;gap:8px">
                        <input type="text" id="consumer-num" class="finp" placeholder="Enter consumer number" style="flex:1">
                        <button onclick="fetchBill()" id="fetch-btn" class="btn btn-outline" style="white-space:nowrap;padding:10px 14px;font-size:13px">
                            Fetch Bill
                        </button>
                    </div>
                </div>
            </div>

            {{-- Fetched Bill Details --}}
            <div id="bill-details" style="display:none" class="bill-card">
                <div style="font-size:13px;font-weight:700;color:#34d399;margin-bottom:10px">Bill Details Fetched</div>
                <div class="bill-row"><span style="font-size:12px;color:var(--muted)">Consumer Name</span><span id="bd-name" style="font-size:13px;font-weight:600;color:var(--text)">—</span></div>
                <div class="bill-row"><span style="font-size:12px;color:var(--muted)">Bill Number</span><span id="bd-bill" style="font-size:12px;font-family:monospace;color:var(--muted)">—</span></div>
                <div class="bill-row"><span style="font-size:12px;color:var(--muted)">Bill Period</span><span id="bd-period" style="font-size:12px;color:var(--muted)">—</span></div>
                <div class="bill-row"><span style="font-size:12px;color:var(--muted)">Due Date</span><span id="bd-due" style="font-size:12px;color:#fbbf24;font-weight:600">—</span></div>
                <div class="bill-row"><span style="font-size:13px;font-weight:600;color:var(--text)">Amount Due</span><span id="bd-amount" style="font-size:18px;font-weight:800;color:#34d399">—</span></div>
            </div>

            <div>
                <label class="flbl">Amount (₹) *</label>
                <input type="number" id="pay-amount" class="finp" placeholder="Enter amount to pay" min="1" step="0.01">
            </div>

            <div id="pay-msg" style="display:none;font-size:13px;border-radius:8px;padding:10px 13px"></div>

            <button onclick="payBill()" id="pay-btn" class="btn btn-primary" style="justify-content:center;width:100%;padding:12px">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:15px;height:15px"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Pay Now
            </button>
        </div>
    </div>
</div>

{{-- ══════ HISTORY TAB ══════ --}}
<div id="pane-hist" style="display:none">
    {{-- Filters --}}
    <div class="card" style="margin-bottom:16px">
        <div style="padding:14px 18px;display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
            <div style="display:flex;flex-direction:column;gap:4px">
                <label class="flbl">Category</label>
                <select id="hf-cat" class="finp" style="width:140px" onchange="loadHistory()">
                    <option value="">All</option>
                    <option value="electricity">Electricity</option>
                    <option value="water">Water</option>
                    <option value="gas">Gas</option>
                    <option value="dth">DTH</option>
                    <option value="broadband">Broadband</option>
                    <option value="landline">Landline</option>
                    <option value="insurance">Insurance</option>
                    <option value="loan">Loan EMI</option>
                    <option value="fastag">FASTag</option>
                    <option value="credit_card">Credit Card</option>
                    <option value="municipal_tax">Municipal Tax</option>
                    <option value="education">Education</option>
                    <option value="subscription">Subscription</option>
                </select>
            </div>
            <div style="display:flex;flex-direction:column;gap:4px">
                <label class="flbl">Status</label>
                <select id="hf-status" class="finp" style="width:120px" onchange="loadHistory()">
                    <option value="">All</option>
                    <option value="success">Success</option>
                    <option value="pending">Pending</option>
                    <option value="failed">Failed</option>
                </select>
            </div>
            <div style="display:flex;flex-direction:column;gap:4px">
                <label class="flbl">From</label>
                <input type="date" id="hf-from" class="finp" style="width:140px" onchange="loadHistory()">
            </div>
            <div style="display:flex;flex-direction:column;gap:4px">
                <label class="flbl">To</label>
                <input type="date" id="hf-to" class="finp" style="width:140px" onchange="loadHistory()">
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header" style="justify-content:space-between">
            <span class="card-title">BBPS Transaction History</span>
            <span id="hist-count" style="font-size:12px;color:var(--muted)"></span>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Date</th><th>Category</th><th>Biller</th><th>Consumer No.</th><th>Amount</th><th>Txn ID</th><th>Status</th></tr></thead>
                <tbody id="hist-tbody"><tr><td colspan="7"><div class="loading"><div class="spinner"></div>Loading…</div></td></tr></tbody>
            </table>
        </div>
        <div class="card-footer" id="hist-pages" style="gap:8px;justify-content:flex-end"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let activeCat = '', activeBillerId = '', activeBillerName = '', fetchedBill = null, histPage = 1;

function switchTab(tab) {
    document.getElementById('pane-pay').style.display  = tab==='pay'  ? '' : 'none';
    document.getElementById('pane-hist').style.display = tab==='hist' ? '' : 'none';
    document.getElementById('tab-pay').className  = 'btn' + (tab==='pay'  ? '' : ' btn-outline');
    document.getElementById('tab-hist').className = 'btn' + (tab==='hist' ? '' : ' btn-outline');
    if(tab==='pay')  { document.getElementById('tab-pay').style.cssText='background:linear-gradient(135deg,var(--blue-dk),var(--indigo));color:#fff;border-radius:7px'; document.getElementById('tab-hist').style.cssText='border:none'; }
    else             { document.getElementById('tab-hist').style.cssText='background:linear-gradient(135deg,var(--blue-dk),var(--indigo));color:#fff;border-radius:7px'; document.getElementById('tab-pay').style.cssText='border:none'; }
    if(tab==='hist') loadHistory();
}

async function selectCat(cat) {
    document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('cat-'+cat).classList.add('active');
    activeCat = cat;
    fetchedBill = null;
    document.getElementById('bill-details').style.display = 'none';
    document.getElementById('pay-amount').value = '';
    document.getElementById('pay-msg').style.display = 'none';
    document.getElementById('bill-form-card').style.display = '';
    const catNames = { electricity:'Electricity', water:'Water', gas:'Gas', dth:'DTH', broadband:'Broadband',
                       landline:'Landline', insurance:'Insurance', loan:'Loan EMI', fastag:'FASTag',
                       credit_card:'Credit Card', municipal_tax:'Municipal Tax', education:'Education', subscription:'Subscription' };
    document.getElementById('form-title').textContent = 'Pay ' + (catNames[cat] || cat) + ' Bill';
    document.getElementById('cat-badge').textContent  = (catNames[cat] || cat).toUpperCase();
    document.getElementById('consumer-num').value = '';
    document.getElementById('biller-sel').innerHTML = '<option value="">Loading billers…</option>';

    const label = { electricity:'CA/Consumer Number', water:'Consumer Number', gas:'BP Number / Customer ID',
                    dth:'Subscriber ID / Smart Card', broadband:'Account Number', landline:'STD Code + Phone Number',
                    insurance:'Policy Number', loan:'Loan Account Number', fastag:'Vehicle / Tag Number',
                    credit_card:'Credit Card Number', municipal_tax:'Property / Assessment Number',
                    education:'Student / Roll Number', subscription:'Registered Mobile / Customer ID' };
    document.getElementById('consumer-label').textContent = (label[cat] || 'Consumer Number') + ' *';

    const res = await apiFetch('/api/v1/bbps/billers?category=' + cat);
    if(!res) return;
    const data = await res.json();
    const sel = document.getElementById('biller-sel');
    sel.innerHTML = '<option value="">Select Biller</option>';
    (data.billers || []).forEach(b => {
        const o = document.createElement('option');
        o.value = b.id; o.textContent = b.name;
        sel.appendChild(o);
    });
}

function onBillerChange() {
    const sel = document.getElementById('biller-sel');
    activeBillerId   = sel.value;
    activeBillerName = sel.options[sel.selectedIndex]?.text || '';
    fetchedBill = null;
    document.getElementById('bill-details').style.display = 'none';
    document.getElementById('pay-amount').value = '';
}

async function fetchBill() {
    const billerId = document.getElementById('biller-sel').value;
    const consumer = document.getElementById('consumer-num').value.trim();
    if(!billerId) { showMsg('Please select a biller.','error'); return; }
    if(!consumer) { showMsg('Please enter consumer number.','error'); return; }

    const btn = document.getElementById('fetch-btn');
    btn.textContent = 'Fetching…'; btn.disabled = true;

    const res  = await apiFetch('/api/v1/bbps/fetch-bill', { method:'POST', body: JSON.stringify({ biller_id: billerId, consumer_number: consumer }) });
    btn.textContent = 'Fetch Bill'; btn.disabled = false;
    if(!res) return;
    const data = await res.json();
    if(res.ok) {
        fetchedBill = data;
        document.getElementById('bd-name').textContent   = data.consumer_name || '—';
        document.getElementById('bd-bill').textContent   = data.bill_number   || '—';
        document.getElementById('bd-period').textContent = data.bill_period   || '—';
        document.getElementById('bd-due').textContent    = data.due_date      || '—';
        document.getElementById('bd-amount').textContent = '₹' + parseFloat(data.due_amount||0).toLocaleString('en-IN',{minimumFractionDigits:2});
        document.getElementById('pay-amount').value      = data.due_amount;
        document.getElementById('bill-details').style.display = '';
        showMsg('','');
    } else {
        showMsg(data.message || 'Failed to fetch bill details.', 'error');
    }
}

async function payBill() {
    const billerId = document.getElementById('biller-sel').value;
    const consumer = document.getElementById('consumer-num').value.trim();
    const amount   = parseFloat(document.getElementById('pay-amount').value);
    if(!activeCat)  { showMsg('Please select a service category.','error'); return; }
    if(!billerId)   { showMsg('Please select a biller.','error'); return; }
    if(!consumer)   { showMsg('Please enter consumer number.','error'); return; }
    if(!amount||amount<=0) { showMsg('Please enter a valid amount.','error'); return; }

    const btn = document.getElementById('pay-btn');
    btn.disabled = true; btn.innerHTML = '<div class="spinner" style="width:15px;height:15px;border-width:2px"></div> Processing…';

    const body = { biller_category: activeCat, biller_id: billerId, biller_name: activeBillerName, consumer_number: consumer, amount, bill_details: fetchedBill };
    const res  = await apiFetch('/api/v1/bbps/pay', { method:'POST', body: JSON.stringify(body) });
    btn.disabled = false; btn.innerHTML = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:15px;height:15px"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg> Pay Now';
    if(!res) return;
    const data = await res.json();
    if(res.ok) {
        showMsg('✓ Bill paid successfully! Txn ID: ' + data.txn_id + ' | Balance: ₹' + parseFloat(data.balance_after||0).toLocaleString('en-IN',{minimumFractionDigits:2}), 'success');
        document.getElementById('pay-amount').value = '';
        document.getElementById('bill-details').style.display = 'none';
        fetchedBill = null;
    } else {
        showMsg(data.message || 'Payment failed. Please try again.', 'error');
    }
}

function showMsg(txt, type) {
    const m = document.getElementById('pay-msg');
    if(!txt) { m.style.display='none'; return; }
    m.textContent = txt;
    m.style.cssText = `display:block;font-size:13px;border-radius:8px;padding:10px 13px;background:${type==='success'?'rgba(16,185,129,.1)':'rgba(239,68,68,.1)'};border:1px solid ${type==='success'?'rgba(16,185,129,.25)':'rgba(239,68,68,.25)'};color:${type==='success'?'#6ee7b7':'#fca5a5'}`;
}

async function loadHistory() {
    const params = new URLSearchParams();
    const cat    = document.getElementById('hf-cat').value;
    const status = document.getElementById('hf-status').value;
    const from   = document.getElementById('hf-from').value;
    const to     = document.getElementById('hf-to').value;
    if(cat)    params.set('category',  cat);
    if(status) params.set('status',    status);
    if(from)   params.set('date_from', from);
    if(to)     params.set('date_to',   to);
    params.set('page', histPage);

    document.getElementById('hist-tbody').innerHTML = '<tr><td colspan="7"><div class="loading"><div class="spinner"></div>Loading…</div></td></tr>';

    const res  = await apiFetch('/api/v1/bbps/history?' + params);
    if(!res) return;
    const data = await res.json();
    const rows = data.data || [];

    document.getElementById('hist-count').textContent = (data.total || rows.length) + ' records';

    const stColors = {success:'rgba(16,185,129,.15)',pending:'rgba(245,158,11,.15)',failed:'rgba(239,68,68,.15)'};
    const stText   = {success:'#34d399',pending:'#fbbf24',failed:'#f87171'};

    document.getElementById('hist-tbody').innerHTML = rows.length
        ? rows.map(r => `<tr>
            <td style="font-size:12px;color:var(--muted)">${r.created_at ? new Date(r.created_at).toLocaleString('en-IN',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:true}) : '—'}</td>
            <td><span style="font-size:11px;text-transform:capitalize;background:var(--card2);padding:2px 8px;border-radius:10px">${r.biller_category}</span></td>
            <td style="font-weight:500;font-size:13px">${r.biller_name}</td>
            <td style="font-size:12px;font-family:monospace;color:var(--muted)">${r.consumer_number}</td>
            <td style="font-weight:700;color:#60a5fa">₹${parseFloat(r.amount).toLocaleString('en-IN',{minimumFractionDigits:2})}</td>
            <td style="font-size:11px;font-family:monospace;color:var(--muted)">${r.txn_id||'—'}</td>
            <td><span class="history-badge" style="background:${stColors[r.status]||'var(--card2)'};color:${stText[r.status]||'var(--muted)'}">${(r.status||'—').toUpperCase()}</span></td>
          </tr>`).join('')
        : '<tr><td colspan="7" style="text-align:center;color:var(--muted);padding:30px">No BBPS transactions found</td></tr>';

    // Pagination
    const pages = document.getElementById('hist-pages');
    if(data.last_page > 1) {
        pages.innerHTML = `<button class="btn btn-outline btn-sm" ${histPage<=1?'disabled':''} onclick="histPage--;loadHistory()">←</button>
            <span style="font-size:12px;color:var(--muted)">Page ${histPage}/${data.last_page}</span>
            <button class="btn btn-outline btn-sm" ${histPage>=data.last_page?'disabled':''} onclick="histPage++;loadHistory()">→</button>`;
    } else { pages.innerHTML=''; }
}

document.addEventListener('DOMContentLoaded', () => { histPage=1; });
</script>
@endpush
