@extends('layouts.seller')
@section('title','Account Ledger')
@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Account Ledger</h1>
        <p class="page-sub">Complete account statement — opening balance, recharges, discounts &amp; closing balance</p>
    </div>
</div>

<!-- Balance + Summary Banner -->
<div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:14px;margin-bottom:20px" id="summary-cards">
    <div style="background:linear-gradient(135deg,#2563eb,#1d4ed8);border-radius:14px;padding:18px 20px;color:#fff">
        <div style="font-size:11px;opacity:.7;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">Current Balance</div>
        <div id="card-balance" style="font-size:24px;font-weight:800">—</div>
    </div>
    <div style="background:linear-gradient(135deg,#f59e0b,#d97706);border-radius:14px;padding:18px 20px;color:#fff">
        <div style="font-size:11px;opacity:.7;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">Total Recharge (period)</div>
        <div id="card-recharge" style="font-size:24px;font-weight:800">—</div>
    </div>
    <div style="background:linear-gradient(135deg,#10b981,#0d9488);border-radius:14px;padding:18px 20px;color:#fff">
        <div style="font-size:11px;opacity:.7;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">Total Discount Earned</div>
        <div id="card-discount" style="font-size:24px;font-weight:800">—</div>
    </div>
    <div style="background:linear-gradient(135deg,#7c3aed,#6d28d9);border-radius:14px;padding:18px 20px;color:#fff">
        <div style="font-size:11px;opacity:.7;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">Net Debit (after discount)</div>
        <div id="card-net" style="font-size:24px;font-weight:800">—</div>
    </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom:20px">
    <div style="padding:16px 20px;display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
        <div style="display:flex;flex-direction:column;gap:4px">
            <label style="font-size:11.5px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.4px">From</label>
            <input type="date" id="f-from" class="form-control" style="width:150px">
        </div>
        <div style="display:flex;flex-direction:column;gap:4px">
            <label style="font-size:11.5px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.4px">To</label>
            <input type="date" id="f-to" class="form-control" style="width:150px">
        </div>
        <div style="display:flex;flex-direction:column;gap:4px">
            <label style="font-size:11.5px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.4px">Type</label>
            <select id="f-type" class="form-control" style="width:130px">
                <option value="">All</option>
                <option value="credit">Credit / Topup</option>
                <option value="debit">Debit / Recharge</option>
            </select>
        </div>
        <button onclick="loadLedger(1)" style="background:#2563eb;color:#fff;border:none;padding:9px 20px;border-radius:9px;font-size:13.5px;font-weight:600;cursor:pointer;height:38px">Search</button>
        <button onclick="resetFilters()" style="background:#f1f5f9;color:#374151;border:1px solid #e2e8f0;padding:9px 16px;border-radius:9px;font-size:13px;font-weight:500;cursor:pointer;height:38px">Reset</button>
        <a href="/seller/payments" style="margin-left:auto;background:#10b981;color:#fff;padding:9px 20px;border-radius:9px;font-size:13.5px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:6px;height:38px">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>Add Funds
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header" style="justify-content:space-between">
        <h3 class="card-title">Account Statement</h3>
        <span id="entry-count" style="font-size:12px;color:#64748b"></span>
    </div>
    <div id="table-wrap" style="overflow-x:auto"><div style="text-align:center;padding:40px;color:#64748b">Loading…</div></div>
    <div id="pagination" style="padding:16px 20px;border-top:1px solid #f1f5f9"></div>
</div>

<style>
.ledger-table { width:100%; border-collapse:collapse; font-size:13px; }
.ledger-table th { background:#f8fafc; color:#475569; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; padding:10px 14px; border-bottom:2px solid #e2e8f0; white-space:nowrap; }
.ledger-table td { padding:10px 14px; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
.ledger-table tr:last-child td { border-bottom:none; }
.ledger-table tr:hover td { background:#f8fafc; }
.badge-type-credit { background:#d1fae5; color:#065f46; padding:2px 8px; border-radius:20px; font-size:11px; font-weight:700; }
.badge-type-debit  { background:#fee2e2; color:#991b1b; padding:2px 8px; border-radius:20px; font-size:11px; font-weight:700; }
.amt-credit { color:#10b981; font-weight:700; }
.amt-debit  { color:#ef4444; font-weight:700; }
.mono { font-family:monospace; font-size:11.5px; color:#94a3b8; }
</style>

<script>
let cp = 1;

function resetFilters(){
    document.getElementById('f-from').value = '';
    document.getElementById('f-to').value = '';
    document.getElementById('f-type').value = '';
    loadLedger(1);
}

function loadLedger(page) {
    cp = page || 1;
    const params = new URLSearchParams({ page: cp, per_page: 25 });
    const from = document.getElementById('f-from').value;
    const to   = document.getElementById('f-to').value;
    const tp   = document.getElementById('f-type').value;
    if (from) params.set('date_from', from);
    if (to)   params.set('date_to', to);
    if (tp)   params.set('type', tp);

    document.getElementById('table-wrap').innerHTML = '<div style="text-align:center;padding:40px;color:#64748b">Loading…</div>';

    apiFetch(`/api/v1/seller/reports/ledger?${params}`).then(data => {
        // Update balance card
        if (data.balance !== undefined) {
            document.getElementById('card-balance').textContent = '₹' + fmtMoney(data.balance);
        }

        const rows = data.data || [];

        // Compute summary from page rows
        let totalRecharge = 0, totalDiscount = 0, totalNet = 0;
        rows.forEach(r => {
            if (r.type === 'debit' && r.recharge_amount) {
                totalRecharge += parseFloat(r.recharge_amount || 0);
                totalDiscount += parseFloat(r.discount || 0);
                totalNet      += parseFloat(r.net_debit || 0);
            }
        });
        document.getElementById('card-recharge').textContent = '₹' + fmtMoney(totalRecharge);
        document.getElementById('card-discount').textContent = '₹' + fmtMoney(totalDiscount);
        document.getElementById('card-net').textContent      = '₹' + fmtMoney(totalNet);

        if (!rows.length) {
            document.getElementById('table-wrap').innerHTML = '<div style="text-align:center;padding:40px;color:#64748b;font-size:14px">No ledger entries found for selected filters.</div>';
            document.getElementById('pagination').innerHTML = '';
            document.getElementById('entry-count').textContent = '';
            return;
        }

        const meta = data.meta || data;
        document.getElementById('entry-count').textContent = `${meta.total || rows.length} entries`;

        let html = `<table class="ledger-table">
            <thead><tr>
                <th>Date &amp; Time</th>
                <th>Mobile / Description</th>
                <th>Operator</th>
                <th>Type</th>
                <th>Opening Bal</th>
                <th>Recharge Amt</th>
                <th>Discount</th>
                <th>Net Debit</th>
                <th>Topup Credit</th>
                <th>Closing Bal</th>
            </tr></thead><tbody>`;

        rows.forEach(r => {
            const isCredit   = r.type === 'credit';
            const isRecharge = r.type === 'debit' && r.recharge_amount;
            const openBal    = parseFloat(r.opening_balance || 0);
            const closeBal   = parseFloat(r.closing_balance || 0);

            const rechargeAmt = isRecharge ? '₹' + fmtMoney(r.recharge_amount) : '—';
            const discountAmt = isRecharge ? '<span class="amt-credit">+₹' + fmtMoney(r.discount || 0) + '</span>' : '—';
            const netDebit    = isRecharge ? '<span class="amt-debit">−₹' + fmtMoney(r.net_debit || 0) + '</span>' : '—';
            const topupCredit = isCredit   ? '<span class="amt-credit">+₹' + fmtMoney(r.txn_amount || 0) + '</span>' : '—';

            const mobileOrDesc = isRecharge
                ? `<div style="font-weight:600">${r.mobile || '—'}</div>`
                : `<div style="color:#475569;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${r.description || '—'}</div>`;

            html += `<tr>
                <td style="white-space:nowrap;font-size:12px">
                    <div>${fmtDate(r.created_at)}</div>
                    <div class="mono">${r.txn_id || ''}</div>
                </td>
                <td>${mobileOrDesc}</td>
                <td style="font-size:12.5px;font-weight:600;color:#2563eb">${r.operator_code || (isCredit ? '—' : '—')}</td>
                <td><span class="${isCredit ? 'badge-type-credit' : 'badge-type-debit'}">${isCredit ? 'Credit' : 'Debit'}</span></td>
                <td style="font-weight:600;color:#374151">₹${fmtMoney(openBal)}</td>
                <td style="font-weight:600">${rechargeAmt}</td>
                <td>${discountAmt}</td>
                <td>${netDebit}</td>
                <td>${topupCredit}</td>
                <td style="font-weight:700;color:${closeBal >= openBal ? '#10b981' : '#ef4444'}">₹${fmtMoney(closeBal)}</td>
            </tr>`;
        });

        html += '</tbody></table>';
        document.getElementById('table-wrap').innerHTML = html;

        // Pagination
        const lp = meta.last_page || 1;
        if (lp <= 1) { document.getElementById('pagination').innerHTML = ''; return; }

        let pag = `<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
            <span style="font-size:13px;color:#64748b">Page ${cp} of ${lp} &nbsp;·&nbsp; ${meta.total || 0} total entries</span>
            <div style="display:flex;gap:5px;flex-wrap:wrap">`;

        const start = Math.max(1, cp - 3), end = Math.min(lp, cp + 3);
        if (start > 1) pag += `<button onclick="loadLedger(1)" class="pg-btn">1</button>${start > 2 ? '<span style="padding:0 4px;color:#94a3b8">…</span>' : ''}`;
        for (let i = start; i <= end; i++) {
            pag += `<button onclick="loadLedger(${i})" class="pg-btn${i === cp ? ' pg-active' : ''}">${i}</button>`;
        }
        if (end < lp) pag += `${end < lp - 1 ? '<span style="padding:0 4px;color:#94a3b8">…</span>' : ''}<button onclick="loadLedger(${lp})" class="pg-btn">${lp}</button>`;
        pag += `</div></div>`;
        document.getElementById('pagination').innerHTML = pag;

    }).catch(() => {
        document.getElementById('table-wrap').innerHTML = '<div style="text-align:center;padding:40px;color:#ef4444">Failed to load ledger. Please try again.</div>';
    });
}

// Pagination button styles
const style = document.createElement('style');
style.textContent = `.pg-btn{padding:5px 11px;border-radius:7px;border:1.5px solid #e2e8f0;background:#fff;color:#374151;font-size:13px;cursor:pointer}.pg-btn:hover{background:#f1f5f9}.pg-active{border-color:#2563eb!important;background:#2563eb!important;color:#fff!important}`;
document.head.appendChild(style);

loadLedger(1);
</script>
@endsection
