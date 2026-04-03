@extends('layouts.user')
@section('title','Add Money — RechargeHub')
@section('page-title','Add Money')

@push('head')
<style>
.mode-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:10px;margin-bottom:20px}
.mode-btn{display:flex;flex-direction:column;align-items:center;gap:8px;padding:18px 10px;border-radius:12px;background:var(--card);border:1.5px solid var(--border);cursor:pointer;transition:all .15s;font-size:13px;font-weight:600;color:var(--muted);font-family:inherit;width:100%}
.mode-btn:hover{background:var(--card2);color:var(--text);border-color:var(--border2)}
.mode-btn.active{background:rgba(59,130,246,.1);border-color:rgba(59,130,246,.5);color:#60a5fa}
.mode-btn svg{width:24px;height:24px;opacity:.7}
.mode-btn.active svg{opacity:1}
.finp{width:100%;background:var(--card2);border:1px solid var(--border2);border-radius:8px;padding:10px 13px;font-size:13px;color:var(--text);outline:none;font-family:inherit;transition:border-color .15s}
.finp:focus{border-color:var(--blue)}
.finp::placeholder{color:var(--muted2)}
.flbl{font-size:11.5px;font-weight:600;color:var(--muted);display:block;margin-bottom:5px}
.fgrp{margin-bottom:16px}
.info-box{background:rgba(59,130,246,.07);border:1px solid rgba(59,130,246,.2);border-radius:10px;padding:14px 16px;margin-bottom:20px}
.info-row{display:flex;justify-content:space-between;align-items:center;padding:4px 0;font-size:13px}
.info-lbl{color:var(--muted)}
.info-val{font-weight:600;color:var(--text)}
.upload-zone{border:2px dashed var(--border2);border-radius:10px;padding:24px;text-align:center;cursor:pointer;transition:all .15s;background:var(--card)}
.upload-zone:hover,.upload-zone.drag{border-color:var(--blue);background:rgba(59,130,246,.05)}
.upload-zone svg{width:32px;height:32px;color:var(--muted2);margin:0 auto 8px;display:block}
.upload-zone p{font-size:13px;color:var(--muted);margin:0}
.upload-zone small{font-size:11px;color:var(--muted2)}
.preview-img{width:100%;max-height:160px;object-fit:contain;border-radius:8px;border:1px solid var(--border2);margin-top:10px;display:none}
.req-badge{display:inline-block;font-size:9px;font-weight:700;background:rgba(239,68,68,.15);color:#f87171;border-radius:4px;padding:1px 5px;margin-left:4px;vertical-align:middle}
.history-badge{font-size:10px;font-weight:600;padding:2px 8px;border-radius:20px}
.badge-pending{background:rgba(245,158,11,.12);color:#fbbf24}
.badge-approved{background:rgba(16,185,129,.12);color:#34d399}
.badge-rejected{background:rgba(239,68,68,.12);color:#f87171}
.tab-bar{display:flex;gap:3px;background:var(--card);border:1px solid var(--border);border-radius:10px;padding:4px;margin-bottom:20px;width:fit-content}
.tab-btn{padding:7px 18px;border-radius:7px;border:none;cursor:pointer;font-family:inherit;font-size:13px;font-weight:500;color:var(--muted);background:none;transition:all .15s}
.tab-btn.active{background:linear-gradient(135deg,var(--blue-dk),var(--indigo));color:#fff}
.ftable{width:100%;border-collapse:collapse;font-size:13px}
.ftable th{padding:10px 14px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--muted2);border-bottom:1px solid var(--border)}
.ftable td{padding:11px 14px;border-bottom:1px solid rgba(255,255,255,.04)}
.ftable tr:hover td{background:var(--card)}
.pager{display:flex;gap:6px;justify-content:center;margin-top:16px;flex-wrap:wrap}
.pager button{padding:5px 12px;border-radius:6px;border:1px solid var(--border2);background:var(--card);color:var(--muted);cursor:pointer;font-size:12px;font-family:inherit;transition:all .15s}
.pager button:hover,.pager button.active{background:var(--blue);border-color:var(--blue);color:#fff}
.pager button:disabled{opacity:.35;cursor:default}
</style>
@endpush

@section('content')
<div class="breadcrumb">
    <a href="/user/dashboard">Dashboard</a>
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>Add Money</span>
</div>

<div class="tab-bar">
    <button class="tab-btn active" id="tab-add"  onclick="switchTab('add')">Add Money</button>
    <button class="tab-btn"        id="tab-hist" onclick="switchTab('hist')">Request History</button>
</div>

{{-- ═══ ADD MONEY TAB ═══ --}}
<div id="pane-add">
<div style="display:grid;grid-template-columns:1fr 380px;gap:20px;align-items:start">

    {{-- LEFT: Form --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">Submit Payment Request</div>
            <div class="card-sub">Upload payment proof and admin will credit your wallet</div>
        </div>

        {{-- Alert --}}
        <div id="am-alert" style="display:none;margin-bottom:14px"></div>

        {{-- Payment Mode --}}
        <div style="margin-bottom:16px">
            <label class="flbl">Payment Mode</label>
            <div class="mode-grid">
                <button class="mode-btn active" id="mode-upi" onclick="selectMode('upi')">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    UPI
                </button>
                <button class="mode-btn" id="mode-bank_transfer" onclick="selectMode('bank_transfer')">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    Bank Transfer
                </button>
                <button class="mode-btn" id="mode-neft" onclick="selectMode('neft')">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    NEFT
                </button>
                <button class="mode-btn" id="mode-rtgs" onclick="selectMode('rtgs')">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    RTGS
                </button>
                <button class="mode-btn" id="mode-cheque" onclick="selectMode('cheque')">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Cheque
                </button>
            </div>
        </div>

        {{-- Amount --}}
        <div class="fgrp">
            <label class="flbl">Amount (₹) <span class="req-badge">REQUIRED</span></label>
            <input type="number" class="finp" id="am-amount" placeholder="Minimum ₹10" min="10" max="500000" step="1">
        </div>

        {{-- Quick Amounts --}}
        <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap">
            @foreach([500,1000,2000,5000,10000] as $a)
            <button class="btn btn-outline" style="font-size:12px;padding:5px 12px" onclick="document.getElementById('am-amount').value='{{ $a }}'">₹{{ number_format($a) }}</button>
            @endforeach
        </div>

        {{-- Reference Number --}}
        <div class="fgrp">
            <label class="flbl">Transaction / Reference Number <span class="req-badge">REQUIRED</span></label>
            <input type="text" class="finp" id="am-ref" placeholder="UTR / Cheque no. / Transaction ID">
        </div>

        {{-- UPI ID (conditional) --}}
        <div class="fgrp" id="upi-field">
            <label class="flbl">Your UPI ID</label>
            <input type="text" class="finp" id="am-upi" placeholder="yourname@upi">
        </div>

        {{-- Payment Date --}}
        <div class="fgrp">
            <label class="flbl">Payment Date</label>
            <input type="date" class="finp" id="am-date" max="{{ date('Y-m-d') }}">
        </div>

        {{-- Notes --}}
        <div class="fgrp">
            <label class="flbl">Notes (optional)</label>
            <textarea class="finp" id="am-notes" rows="2" placeholder="Any additional info for admin…" style="resize:vertical"></textarea>
        </div>

        {{-- Proof Upload --}}
        <div class="fgrp">
            <label class="flbl">Payment Proof</label>
            <div class="upload-zone" id="upload-zone" onclick="document.getElementById('am-proof').click()"
                 ondragover="e=>{e.preventDefault();this.classList.add('drag')}" ondragleave="this.classList.remove('drag')"
                 ondrop="handleDrop(event)">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <p>Click to upload or drag & drop</p>
                <small>JPG, PNG or PDF — max 5 MB</small>
            </div>
            <input type="file" id="am-proof" accept=".jpg,.jpeg,.png,.pdf" style="display:none" onchange="previewFile(this)">
            <img id="proof-preview" class="preview-img" alt="Preview">
            <div id="proof-name" style="font-size:12px;color:var(--muted2);margin-top:6px"></div>
        </div>

        <button class="btn" id="am-submit" onclick="submitRequest()" style="width:100%;background:linear-gradient(135deg,var(--blue-dk),var(--indigo));color:#fff;font-size:14px;padding:12px">
            Submit Payment Request
        </button>
    </div>

    {{-- RIGHT: Bank Details & Info --}}
    <div style="display:flex;flex-direction:column;gap:16px">
        <div class="card">
            <div class="card-header">
                <div class="card-title">Payment Details</div>
                <div class="card-sub">Transfer money to any of these accounts</div>
            </div>
            <div class="info-box" style="margin-bottom:14px">
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--muted2);margin-bottom:8px">UPI</div>
                <div class="info-row">
                    <span class="info-lbl">UPI ID</span>
                    <span class="info-val" style="font-family:monospace">pay@rechargepay</span>
                </div>
            </div>
            <div class="info-box">
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--muted2);margin-bottom:8px">Bank Account</div>
                <div class="info-row"><span class="info-lbl">Account Name</span><span class="info-val">RechargeHub Pvt Ltd</span></div>
                <div class="info-row"><span class="info-lbl">Account No.</span><span class="info-val" style="font-family:monospace">1234567890123</span></div>
                <div class="info-row"><span class="info-lbl">IFSC Code</span><span class="info-val" style="font-family:monospace">HDFC0001234</span></div>
                <div class="info-row"><span class="info-lbl">Bank</span><span class="info-val">HDFC Bank</span></div>
                <div class="info-row"><span class="info-lbl">Branch</span><span class="info-val">Mumbai Main</span></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title">How it works</div>
            </div>
            <div style="display:flex;flex-direction:column;gap:12px;font-size:13px">
                <div style="display:flex;gap:10px;align-items:flex-start">
                    <div style="width:22px;height:22px;border-radius:50%;background:linear-gradient(135deg,var(--blue-dk),var(--indigo));display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;flex-shrink:0">1</div>
                    <div><span style="font-weight:500">Transfer money</span> to the account or UPI above</div>
                </div>
                <div style="display:flex;gap:10px;align-items:flex-start">
                    <div style="width:22px;height:22px;border-radius:50%;background:linear-gradient(135deg,var(--blue-dk),var(--indigo));display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;flex-shrink:0">2</div>
                    <div>Fill this form and <span style="font-weight:500">upload the screenshot / receipt</span></div>
                </div>
                <div style="display:flex;gap:10px;align-items:flex-start">
                    <div style="width:22px;height:22px;border-radius:50%;background:linear-gradient(135deg,var(--blue-dk),var(--indigo));display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;flex-shrink:0">3</div>
                    <div>Admin verifies and <span style="font-weight:500">credits your wallet within 24 hours</span></div>
                </div>
            </div>
        </div>

        <div class="card" style="border-color:rgba(245,158,11,.2);background:rgba(245,158,11,.04)">
            <div style="display:flex;gap:10px;align-items:flex-start;font-size:13px">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:18px;height:18px;color:var(--orange);flex-shrink:0;margin-top:1px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div style="color:var(--muted)">Always transfer from your <strong style="color:var(--text)">registered bank account</strong>. Third-party transfers may be rejected. Min: <strong style="color:var(--text)">₹10</strong> · Max: <strong style="color:var(--text)">₹5,00,000</strong> per request.</div>
            </div>
        </div>
    </div>

</div>
</div>

{{-- ═══ HISTORY TAB ═══ --}}
<div id="pane-hist" style="display:none">
    <div class="card" style="overflow:hidden">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
            <div>
                <div class="card-title">Payment Requests</div>
                <div class="card-sub">Track your add-money requests</div>
            </div>
            <div style="display:flex;gap:8px;align-items:center">
                <select class="finp" id="h-status" onchange="loadHistory(1)" style="min-width:130px">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
        </div>
        <table class="ftable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Amount</th>
                    <th>Mode</th>
                    <th>Reference</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Admin Notes</th>
                </tr>
            </thead>
            <tbody id="h-tbody"><tr><td colspan="7" style="text-align:center;padding:30px;color:var(--muted)">Loading…</td></tr></tbody>
        </table>
        <div id="h-pager" class="pager"></div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let selMode = 'upi';

function switchTab(t) {
    ['add','hist'].forEach(x => {
        document.getElementById('tab-'+x).classList.toggle('active', x===t);
        document.getElementById('pane-'+x).style.display = x===t ? '' : 'none';
    });
    if (t==='hist' && !window._hLoaded) { loadHistory(1); window._hLoaded = true; }
}

function selectMode(m) {
    selMode = m;
    document.querySelectorAll('.mode-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('mode-'+m).classList.add('active');
    document.getElementById('upi-field').style.display = m==='upi' ? '' : 'none';
}

function previewFile(input) {
    const file = input.files[0];
    if (!file) return;
    document.getElementById('proof-name').textContent = file.name + ' (' + (file.size/1024).toFixed(1) + ' KB)';
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = e => { const img = document.getElementById('proof-preview'); img.src=e.target.result; img.style.display='block'; };
        reader.readAsDataURL(file);
    } else {
        document.getElementById('proof-preview').style.display = 'none';
    }
}

function handleDrop(e) {
    e.preventDefault();
    document.getElementById('upload-zone').classList.remove('drag');
    const dt = e.dataTransfer;
    if (dt && dt.files.length) {
        document.getElementById('am-proof').files = dt.files;
        previewFile(document.getElementById('am-proof'));
    }
}

function showAlert(msg, type='error') {
    const el = document.getElementById('am-alert');
    const colors = {error:'rgba(239,68,68,.1)',success:'rgba(16,185,129,.1)',info:'rgba(59,130,246,.1)'};
    const borders = {error:'rgba(239,68,68,.3)',success:'rgba(16,185,129,.3)',info:'rgba(59,130,246,.3)'};
    const textc = {error:'#f87171',success:'#34d399',info:'#60a5fa'};
    el.style.display = 'block';
    el.style.background = colors[type];
    el.style.border = '1px solid ' + borders[type];
    el.style.borderRadius = '8px';
    el.style.padding = '10px 14px';
    el.style.color = textc[type];
    el.style.fontSize = '13px';
    el.textContent = msg;
}

async function submitRequest() {
    const amount = document.getElementById('am-amount').value;
    const ref    = document.getElementById('am-ref').value.trim();

    if (!amount || Number(amount) < 10) { showAlert('Please enter a valid amount (min ₹10).'); return; }
    if (!ref) { showAlert('Transaction / Reference Number is required.'); return; }

    const btn = document.getElementById('am-submit');
    btn.disabled = true;
    btn.textContent = 'Submitting…';

    const fd = new FormData();
    fd.append('amount',           amount);
    fd.append('payment_mode',     selMode);
    fd.append('reference_number', ref);

    const upiId  = document.getElementById('am-upi').value.trim();
    const date   = document.getElementById('am-date').value;
    const notes  = document.getElementById('am-notes').value.trim();
    const proof  = document.getElementById('am-proof').files[0];

    if (upiId)  fd.append('upi_id', upiId);
    if (date)   fd.append('payment_date', date);
    if (notes)  fd.append('notes', notes);
    if (proof)  fd.append('proof_image', proof);

    try {
        const res = await apiFetch('/api/v1/payment-requests', {method:'POST', body:fd, headers:{}});
        const d   = await res.json();
        if (res.ok) {
            showAlert(d.message || 'Request submitted successfully!', 'success');
            // reset form
            document.getElementById('am-amount').value = '';
            document.getElementById('am-ref').value    = '';
            document.getElementById('am-upi').value    = '';
            document.getElementById('am-date').value   = '';
            document.getElementById('am-notes').value  = '';
            document.getElementById('am-proof').value  = '';
            document.getElementById('proof-preview').style.display = 'none';
            document.getElementById('proof-name').textContent = '';
            window._hLoaded = false;
        } else {
            const msg = d.message || (d.errors ? Object.values(d.errors).flat().join(' ') : 'Submission failed.');
            showAlert(msg);
        }
    } catch(e) {
        showAlert('Network error. Please try again.');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Submit Payment Request';
    }
}

/* ── History ── */
function fmtDate(s) {
    if (!s) return '—';
    const d = new Date(s);
    return d.toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'});
}
function fmtAmt(n) { return '₹'+Number(n||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2}); }
function buildPager(pager, fn) {
    const last=pager.last_page||1,cur=pager.current_page||1;
    let h='';
    if(last<=1)return h;
    h+=`<button ${cur<=1?'disabled':''} onclick="${fn}(${cur-1})">‹</button>`;
    for(let p=Math.max(1,cur-2);p<=Math.min(last,cur+2);p++)
        h+=`<button class="${p===cur?'active':''}" onclick="${fn}(${p})">${p}</button>`;
    h+=`<button ${cur>=last?'disabled':''} onclick="${fn}(${cur+1})">›</button>`;
    return h;
}

async function loadHistory(page) {
    const status = document.getElementById('h-status').value;
    const params = new URLSearchParams({page, per_page:15});
    if (status) params.set('status', status);
    document.getElementById('h-tbody').innerHTML = '<tr><td colspan="7" style="text-align:center;padding:30px;color:var(--muted)">Loading…</td></tr>';
    try {
        const res = await apiFetch('/api/v1/payment-requests?' + params);
        const d   = await res.json();
        const rows = d.data || [];
        if (!rows.length) {
            document.getElementById('h-tbody').innerHTML = '<tr><td colspan="7" style="text-align:center;padding:30px;color:var(--muted)">No requests found.</td></tr>';
        } else {
            const map = {pending:'badge-pending',approved:'badge-approved',rejected:'badge-rejected'};
            document.getElementById('h-tbody').innerHTML = rows.map(r=>`
                <tr>
                    <td style="color:var(--muted2)">#${r.id}</td>
                    <td style="font-weight:600">${fmtAmt(r.amount)}</td>
                    <td style="text-transform:uppercase;font-size:11px">${(r.payment_mode||'').replace('_',' ')}</td>
                    <td style="font-family:monospace;font-size:12px">${r.reference_number||'—'}</td>
                    <td style="color:var(--muted2);font-size:12px">${fmtDate(r.created_at)}</td>
                    <td><span class="history-badge ${map[r.status]||'badge-pending'}">${(r.status||'').toUpperCase()}</span></td>
                    <td style="color:var(--muted2);font-size:12px">${r.admin_notes||'—'}</td>
                </tr>
            `).join('');
        }
        document.getElementById('h-pager').innerHTML = buildPager(d, 'loadHistory');
    } catch(e) {
        document.getElementById('h-tbody').innerHTML = '<tr><td colspan="7" style="text-align:center;padding:30px;color:var(--red)">Failed to load.</td></tr>';
    }
}

/* ── Init ── */
document.getElementById('am-date').max = new Date().toISOString().split('T')[0];
selectMode('upi');
</script>
@endpush
