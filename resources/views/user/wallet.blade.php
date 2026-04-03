@extends('layouts.user')
@section('title','My Wallet')
@section('page-title','My Wallet')

@section('content')
<div class="breadcrumb">
    <a href="/user/dashboard">Dashboard</a>
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>Wallet</span>
</div>

{{-- Balance + Topup --}}
<div style="display:grid;grid-template-columns:300px 1fr;gap:16px;margin-bottom:20px">

    {{-- Balance Card --}}
    <div style="background:linear-gradient(135deg,#1e3a8a,#312e81,#1e1b4b);border:1px solid rgba(99,102,241,.3);border-radius:18px;padding:28px;position:relative;overflow:hidden">
        <div style="position:absolute;top:-30px;right:-30px;width:120px;height:120px;border-radius:50%;background:rgba(99,102,241,.15);pointer-events:none"></div>
        <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:1px;margin-bottom:8px">Available Balance</div>
        <div style="font-size:40px;font-weight:900;color:#fff;letter-spacing:-2px" id="wallet-balance">—</div>
        <div style="font-size:12px;color:#64748b;margin-top:6px">Reserved: <span id="wallet-reserved" style="color:#94a3b8">—</span></div>
        <div style="margin-top:20px;display:flex;gap:8px;align-items:center">
            <span style="background:rgba(16,185,129,.15);border:1px solid rgba(16,185,129,.25);color:#34d399;font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px" id="wallet-status">—</span>
            <button onclick="openTopup()" style="background:linear-gradient(135deg,#059669,#10b981);color:#fff;border:none;border-radius:8px;padding:7px 16px;font-size:12px;font-weight:700;cursor:pointer;margin-left:auto">+ Add Money</button>
        </div>
    </div>

    {{-- Quick Stats --}}
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;align-content:start">
        <div class="stat-card blue"><div class="stat-label">Total Credited</div><div class="stat-value" id="w-credit" style="font-size:20px">—</div><div class="stat-sub">All time</div></div>
        <div class="stat-card orange"><div class="stat-label">Total Debited</div><div class="stat-value" id="w-debit" style="font-size:20px">—</div><div class="stat-sub">All time</div></div>
        <div class="stat-card green"><div class="stat-label">Transactions</div><div class="stat-value" id="w-count">—</div><div class="stat-sub">Total entries</div></div>
    </div>
</div>

{{-- Ledger --}}
<div class="card">
    <div class="card-header" style="justify-content:space-between">
        <span class="card-title">Wallet Ledger</span>
        <span id="ledger-count" style="font-size:12px;color:var(--muted)"></span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Description</th>
                    <th>Balance After</th>
                    <th>Date</th>
                    <th>Receipt</th>
                </tr>
            </thead>
            <tbody id="ledger-tbody">
                <tr><td colspan="6"><div class="loading"><div class="spinner"></div> Loading…</div></td></tr>
            </tbody>
        </table>
    </div>
    <div class="card-footer" id="pagination" style="gap:8px;justify-content:flex-end"></div>
</div>

{{-- UPI Topup Modal --}}
<div id="topup-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:999;align-items:center;justify-content:center;padding:20px">
    <div style="background:#0f172a;border:1px solid rgba(255,255,255,.1);border-radius:20px;width:100%;max-width:420px;overflow:hidden">
        <div style="padding:20px 24px;border-bottom:1px solid rgba(255,255,255,.08);display:flex;align-items:center;justify-content:space-between">
            <div style="font-size:16px;font-weight:700;color:#fff">Add Money to Wallet</div>
            <button onclick="closeTopup()" style="background:rgba(255,255,255,.07);border:none;color:#94a3b8;width:30px;height:30px;border-radius:8px;cursor:pointer;font-size:16px">×</button>
        </div>
        <div style="padding:24px">

            {{-- Step 1: Amount --}}
            <div id="step-amount">
                <div style="font-size:13px;color:#94a3b8;margin-bottom:16px">Select or enter amount to add</div>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:16px">
                    <button class="amt-btn" onclick="setAmt(100)">₹100</button>
                    <button class="amt-btn" onclick="setAmt(200)">₹200</button>
                    <button class="amt-btn" onclick="setAmt(500)">₹500</button>
                    <button class="amt-btn" onclick="setAmt(1000)">₹1,000</button>
                    <button class="amt-btn" onclick="setAmt(2000)">₹2,000</button>
                    <button class="amt-btn" onclick="setAmt(5000)">₹5,000</button>
                </div>
                <div style="margin-bottom:16px">
                    <label style="font-size:11px;font-weight:600;color:#94a3b8;display:block;margin-bottom:6px">Custom Amount (₹10 – ₹50,000)</label>
                    <input type="number" id="topup-amount" placeholder="Enter amount" min="10" max="50000"
                        style="width:100%;background:rgba(255,255,255,.05);border:1.5px solid rgba(255,255,255,.1);border-radius:10px;padding:11px 14px;font-size:15px;font-weight:700;color:#fff;outline:none;font-family:inherit">
                    <div id="amt-err" style="font-size:12px;color:#f87171;margin-top:5px;display:none"></div>
                </div>
                <button onclick="goToUpi()" style="width:100%;background:linear-gradient(135deg,#059669,#10b981);color:#fff;border:none;border-radius:10px;padding:13px;font-size:14px;font-weight:700;cursor:pointer">
                    Continue to UPI Payment →
                </button>
            </div>

            {{-- Step 2: UPI --}}
            <div id="step-upi" style="display:none">
                <div style="text-align:center;margin-bottom:20px">
                    <div style="font-size:13px;color:#94a3b8;margin-bottom:8px">Pay via UPI to add</div>
                    <div style="font-size:32px;font-weight:900;color:#10b981" id="upi-amt-display">—</div>
                    <div style="font-size:12px;color:#64748b;margin-top:4px">to your wallet</div>
                </div>

                {{-- UPI QR placeholder --}}
                <div style="background:rgba(255,255,255,.97);border-radius:12px;padding:16px;text-align:center;margin-bottom:16px">
                    <div style="width:150px;height:150px;margin:0 auto;background:linear-gradient(135deg,#e0e7ff,#ddd6fe);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:6px">
                        <svg fill="none" viewBox="0 0 24 24" stroke="#6366f1" stroke-width="1.5" style="width:40px;height:40px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        <div style="font-size:10px;color:#6366f1;font-weight:600">UPI QR Code</div>
                    </div>
                    <div style="margin-top:10px;font-size:12px;color:#374151;font-weight:600">rechargchub@upi</div>
                    <div style="font-size:11px;color:#6b7280;margin-top:2px">Scan & pay using any UPI app</div>
                </div>

                <div style="margin-bottom:16px">
                    <label style="font-size:11px;font-weight:600;color:#94a3b8;display:block;margin-bottom:6px">Enter UPI Transaction / Reference ID <span style="color:#ef4444">*</span></label>
                    <input type="text" id="upi-ref" placeholder="e.g. 123456789012" maxlength="100"
                        style="width:100%;background:rgba(255,255,255,.05);border:1.5px solid rgba(255,255,255,.1);border-radius:10px;padding:10px 14px;font-size:13px;color:#fff;outline:none;font-family:inherit">
                    <div id="upi-err" style="font-size:12px;color:#f87171;margin-top:5px;display:none"></div>
                    <div style="font-size:11px;color:#475569;margin-top:4px">Find this in your UPI app's payment history</div>
                </div>

                <div id="topup-alert" style="display:none;border-radius:8px;padding:10px 14px;font-size:13px;margin-bottom:12px"></div>

                <div style="display:flex;gap:10px">
                    <button onclick="backToAmount()" style="flex:1;background:rgba(255,255,255,.07);color:#94a3b8;border:none;border-radius:10px;padding:12px;font-size:13px;font-weight:600;cursor:pointer">← Back</button>
                    <button onclick="submitTopup()" id="topup-btn" style="flex:2;background:linear-gradient(135deg,#059669,#10b981);color:#fff;border:none;border-radius:10px;padding:12px;font-size:14px;font-weight:700;cursor:pointer">
                        <span id="topup-btn-text">Confirm Payment</span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('head')
<style>
.amt-btn{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);color:#f1f5f9;border-radius:8px;padding:10px;font-size:13px;font-weight:600;cursor:pointer;transition:all .15s;font-family:inherit}
.amt-btn:hover,.amt-btn.selected{background:rgba(16,185,129,.15);border-color:rgba(16,185,129,.4);color:#34d399}
</style>
@endpush

@push('scripts')
<script>
let selectedAmt = null;

function openTopup() {
    document.getElementById('topup-overlay').style.display = 'flex';
    document.getElementById('step-amount').style.display = 'block';
    document.getElementById('step-upi').style.display    = 'none';
    document.getElementById('topup-amount').value = '';
    document.getElementById('upi-ref').value = '';
    document.getElementById('amt-err').style.display = 'none';
    document.getElementById('upi-err').style.display = 'none';
    document.getElementById('topup-alert').style.display = 'none';
}
function closeTopup() { document.getElementById('topup-overlay').style.display = 'none'; }

function setAmt(v) {
    selectedAmt = v;
    document.getElementById('topup-amount').value = v;
    document.querySelectorAll('.amt-btn').forEach(b => b.classList.remove('selected'));
    event.target.classList.add('selected');
}

function goToUpi() {
    const amt = parseFloat(document.getElementById('topup-amount').value);
    const err = document.getElementById('amt-err');
    if (!amt || amt < 10 || amt > 50000) {
        err.textContent = 'Enter an amount between ₹10 and ₹50,000.';
        err.style.display = 'block';
        return;
    }
    err.style.display = 'none';
    document.getElementById('upi-amt-display').textContent = '₹' + amt.toLocaleString('en-IN', { minimumFractionDigits: 2 });
    document.getElementById('step-amount').style.display = 'none';
    document.getElementById('step-upi').style.display    = 'block';
}
function backToAmount() {
    document.getElementById('step-upi').style.display    = 'none';
    document.getElementById('step-amount').style.display = 'block';
}

async function submitTopup() {
    const amount  = parseFloat(document.getElementById('topup-amount').value);
    const upiRef  = document.getElementById('upi-ref').value.trim();
    const upiErr  = document.getElementById('upi-err');
    const alertEl = document.getElementById('topup-alert');
    upiErr.style.display = 'none';
    alertEl.style.display = 'none';

    if (!upiRef) { upiErr.textContent = 'Please enter UPI transaction reference ID.'; upiErr.style.display = 'block'; return; }

    const btn = document.getElementById('topup-btn');
    document.getElementById('topup-btn-text').textContent = 'Processing…';
    btn.disabled = true;

    try {
        const res  = await apiFetch('/api/v1/wallet/self-topup', {
            method: 'POST',
            body: JSON.stringify({ amount, upi_ref: upiRef }),
        });
        const data = await res.json();
        if (res.ok) {
            alertEl.style.cssText = 'display:block;background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.25);color:#34d399;border-radius:8px;padding:10px 14px;font-size:13px;margin-bottom:12px';
            alertEl.textContent = '✓ Wallet topped up! New balance: ₹' + parseFloat(data.new_balance).toFixed(2);
            setTimeout(() => { closeTopup(); loadWallet(); }, 1800);
        } else {
            alertEl.style.cssText = 'display:block;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#fca5a5;border-radius:8px;padding:10px 14px;font-size:13px;margin-bottom:12px';
            alertEl.textContent = data.message || 'Topup failed. Please try again.';
        }
    } catch {
        alertEl.style.cssText = 'display:block;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#fca5a5;border-radius:8px;padding:10px 14px;font-size:13px;margin-bottom:12px';
        alertEl.textContent = 'Network error. Please try again.';
    } finally {
        document.getElementById('topup-btn-text').textContent = 'Confirm Payment';
        btn.disabled = false;
    }
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeTopup(); });
document.getElementById('topup-overlay').addEventListener('click', e => { if (e.target.id === 'topup-overlay') closeTopup(); });

async function loadWallet(page = 1) {
    const [balRes, ledRes] = await Promise.all([
        apiFetch('/api/v1/wallet/balance'),
        apiFetch('/api/v1/wallet/transactions?per_page=20&page=' + page),
    ]);

    if (balRes?.ok) {
        const d = await balRes.json();
        document.getElementById('wallet-balance').textContent  = fmtAmt(d.balance);
        document.getElementById('wallet-reserved').textContent = fmtAmt(d.reserved_balance || 0);
        document.getElementById('wallet-status').textContent   = d.status || 'active';
    }

    if (ledRes?.ok) {
        const d    = await ledRes.json();
        const txns = d.data?.data || d.data || [];
        const total = d.data?.total ?? d.total ?? txns.length;
        document.getElementById('ledger-count').textContent = fmtNum(total) + ' entries';

        let credit = 0, debit = 0;
        txns.forEach(t => {
            if (t.type === 'credit' || t.type === 'release') credit += parseFloat(t.amount || 0);
            else debit += parseFloat(t.amount || 0);
        });
        document.getElementById('w-credit').textContent = fmtAmt(credit);
        document.getElementById('w-debit').textContent  = fmtAmt(debit);
        document.getElementById('w-count').textContent  = fmtNum(total);

        const typeColor = { credit:'var(--green)', debit:'var(--red)', reserve:'var(--orange)', release:'var(--blue)', reversal:'#a78bfa' };

        document.getElementById('ledger-tbody').innerHTML = txns.length
            ? txns.map(t => {
                const isCredit = t.type === 'credit' || t.type === 'release';
                const date = t.created_at ? new Date(t.created_at).toLocaleString('en-IN',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:true}) : '—';
                return `<tr>
                    <td><span style="font-size:11px;font-weight:700;color:${typeColor[t.type]||'#fff'}">${(t.type||'').toUpperCase()}</span></td>
                    <td style="font-weight:700;color:${isCredit?'var(--green)':'var(--red)'}">${isCredit?'+':'−'}${fmtAmt(t.amount)}</td>
                    <td style="color:var(--muted);font-size:12px">${t.description || '—'}</td>
                    <td style="font-weight:600">${fmtAmt(t.balance_after)}</td>
                    <td style="font-size:12px;color:var(--muted)">${date}</td>
                    <td><button onclick="printWalletReceipt(${JSON.stringify(t).replace(/"/g,'&quot;')})" style="background:rgba(99,102,241,.15);border:1px solid rgba(99,102,241,.25);color:#a5b4fc;font-size:11px;font-weight:600;padding:4px 10px;border-radius:6px;cursor:pointer;border:none">Receipt</button></td>
                </tr>`;
            }).join('')
            : '<tr><td colspan="6" style="text-align:center;color:var(--muted);padding:30px">No transactions yet</td></tr>';

        // Pagination
        const meta = d.data || d;
        const lastPage = meta.last_page || 1;
        const currPage = meta.current_page || page;
        const pag = document.getElementById('pagination');
        if (lastPage > 1) {
            let h = '';
            if (currPage > 1) h += `<button class="btn btn-outline btn-sm" onclick="loadWallet(${currPage-1})">← Prev</button>`;
            h += `<span style="font-size:12px;color:var(--muted)">Page ${currPage} of ${lastPage}</span>`;
            if (currPage < lastPage) h += `<button class="btn btn-outline btn-sm" onclick="loadWallet(${currPage+1})">Next →</button>`;
            pag.innerHTML = h;
        } else pag.innerHTML = '';
    }
}

function printWalletReceipt(txn) {
    const user = getUserData();
    const date = txn.created_at ? new Date(txn.created_at).toLocaleString('en-IN',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:true}) : '—';
    const isCredit = txn.type === 'credit' || txn.type === 'release';
    const w = window.open('', '_blank', 'width=480,height=600');
    w.document.write(`<!DOCTYPE html><html><head><title>Wallet Receipt</title>
    <style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:Arial,sans-serif;padding:30px;color:#111}
    .header{text-align:center;margin-bottom:24px}.brand{font-size:22px;font-weight:800;color:#2563eb}
    .amount{font-size:36px;font-weight:900;margin:12px 0;color:${isCredit?'#059669':'#dc2626'}}
    table{width:100%;border-collapse:collapse;margin-top:20px;font-size:13px}
    td{padding:10px 0;border-bottom:1px solid #f1f5f9}td:last-child{text-align:right;font-weight:600}
    .footer{text-align:center;margin-top:24px;font-size:11px;color:#9ca3af}
    @media print{body{padding:0}}</style></head><body>
    <div class="header"><div class="brand">RechargeHub</div>
    <div style="font-size:12px;color:#6b7280;margin-top:4px">Wallet Transaction Receipt</div></div>
    <div style="text-align:center">
    <div class="amount">${isCredit?'+':'−'}₹${parseFloat(txn.amount||0).toFixed(2)}</div>
    <div style="font-size:13px;font-weight:600;color:#374151">${(txn.type||'').toUpperCase()}</div></div>
    <table>
    <tr><td style="color:#6b7280">Transaction ID</td><td>${txn.txn_id||txn.id||'—'}</td></tr>
    <tr><td style="color:#6b7280">Type</td><td>${(txn.type||'').toUpperCase()}</td></tr>
    <tr><td style="color:#6b7280">Amount</td><td>${isCredit?'+':'−'}₹${parseFloat(txn.amount||0).toFixed(2)}</td></tr>
    <tr><td style="color:#6b7280">Balance After</td><td>₹${parseFloat(txn.balance_after||0).toFixed(2)}</td></tr>
    <tr><td style="color:#6b7280">Description</td><td>${txn.description||'—'}</td></tr>
    <tr><td style="color:#6b7280">Date & Time</td><td>${date}</td></tr>
    <tr><td style="color:#6b7280">Account</td><td>${user.name||'—'}</td></tr>
    </table>
    <div class="footer">Thank you for using RechargeHub</div>
    <script>window.onload=()=>{window.print()}<\/script></body></html>`);
    w.document.close();
}

document.addEventListener('DOMContentLoaded', () => loadWallet());
</script>
@endpush
