@extends('layouts.admin')
@section('title', 'Exports')
@section('page-title', 'Exports')

@section('content')
<div class="breadcrumb">
    <a href="/admin/dashboard">Dashboard</a>
    <svg class="breadcrumb-sep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>Exports</span>
</div>

{{-- Date Range --}}
<div class="card" style="margin-bottom:20px">
    <div class="card-body" style="padding:16px 20px">
        <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
            <div>
                <label style="font-size:11px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">Date From</label>
                <input type="date" id="e-from" style="border:1px solid var(--border);border-radius:6px;padding:6px 10px;font-size:13px">
            </div>
            <div>
                <label style="font-size:11px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">Date To</label>
                <input type="date" id="e-to" style="border:1px solid var(--border);border-radius:6px;padding:6px 10px;font-size:13px">
            </div>
        </div>
    </div>
</div>

{{-- Export Buttons --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;margin-bottom:28px">

    <div class="card" style="padding:20px;text-align:center">
        <div style="width:44px;height:44px;background:#dbeafe;border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:22px;height:22px;color:var(--accent-blue)"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <div style="font-size:13px;font-weight:600;margin-bottom:4px">Recharge Report</div>
        <div style="font-size:11px;color:var(--text-muted);margin-bottom:14px">All recharge transactions</div>
        <button class="btn btn-primary btn-sm" style="width:100%" onclick="doExport('recharges','Recharge Report')">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Download CSV
        </button>
    </div>

    <div class="card" style="padding:20px;text-align:center">
        <div style="width:44px;height:44px;background:#d1fae5;border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:22px;height:22px;color:var(--accent-green)"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <div style="font-size:13px;font-weight:600;margin-bottom:4px">Operator Report</div>
        <div style="font-size:11px;color:var(--text-muted);margin-bottom:14px">Operator-wise performance</div>
        <button class="btn btn-primary btn-sm" style="width:100%;background:var(--accent-green)" onclick="doExport('operators','Operator Report')">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Download CSV
        </button>
    </div>

    <div class="card" style="padding:20px;text-align:center">
        <div style="width:44px;height:44px;background:#fef3c7;border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:22px;height:22px;color:var(--accent-orange)"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
        </div>
        <div style="font-size:13px;font-weight:600;margin-bottom:4px">Wallet Report</div>
        <div style="font-size:11px;color:var(--text-muted);margin-bottom:14px">Wallet balances &amp; ledger</div>
        <button class="btn btn-primary btn-sm" style="width:100%;background:var(--accent-orange)" onclick="doExport('wallets','Wallet Report')">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Download CSV
        </button>
    </div>

    <div class="card" style="padding:20px;text-align:center">
        <div style="width:44px;height:44px;background:#fee2e2;border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:22px;height:22px;color:var(--accent-red)"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        </div>
        <div style="font-size:13px;font-weight:600;margin-bottom:4px">Complaint Report</div>
        <div style="font-size:11px;color:var(--text-muted);margin-bottom:14px">All complaints summary</div>
        <button class="btn btn-primary btn-sm" style="width:100%;background:var(--accent-red)" onclick="doExport('complaints','Complaint Report')">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Download CSV
        </button>
    </div>

</div>

{{-- Export History --}}
<div class="card">
    <div class="card-header" style="justify-content:space-between">
        <span class="card-title">Export History</span>
        <button class="btn btn-outline btn-sm" onclick="clearHistory()">Clear History</button>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Report</th><th>Date Range</th><th>Exported At</th><th>Records</th><th>File</th></tr></thead>
            <tbody id="hist-tbody">
                <tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:24px">No exports yet</td></tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
function loadHistory() {
    const hist = JSON.parse(localStorage.getItem('export_history') || '[]');
    const tbody = document.getElementById('hist-tbody');
    if (!hist.length) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:24px">No exports yet</td></tr>';
        return;
    }
    tbody.innerHTML = [...hist].reverse().map(h => `<tr>
        <td><strong>${h.report}</strong></td>
        <td style="font-size:12px">${h.from||'—'} → ${h.to||'—'}</td>
        <td>${fmtAgo(h.exported_at)}</td>
        <td>${fmtNum(h.records)}</td>
        <td style="color:var(--accent-green);font-size:12px">${h.filename}</td>
    </tr>`).join('');
}

function clearHistory() {
    if (!confirm('Clear export history?')) return;
    localStorage.removeItem('export_history');
    loadHistory();
}

async function doExport(type, label) {
    const from = document.getElementById('e-from').value;
    const to   = document.getElementById('e-to').value;
    const p    = new URLSearchParams({ per_page: 500 });
    if (from) p.set('date_from', from);
    if (to)   p.set('date_to',   to);

    const res = await apiFetch(`/api/v1/admin/reports/${type}?` + p.toString());
    if (!res) return;
    const data = await res.json();

    let rows = [];
    let headers = [];
    if (type === 'recharges') {
        rows    = data.transactions?.data || data.transactions || [];
        headers = ['id','mobile','operator_code','recharge_type','amount','status','created_at'];
    } else if (type === 'operators') {
        rows    = data.operators || [];
        headers = ['operator_code','name','category','total','success','failed','success_rate','amount'];
    } else if (type === 'wallets') {
        rows    = data.wallets?.data || data.wallets || [];
        headers = ['id','user_id','balance','reserved','status','created_at'];
    } else if (type === 'complaints') {
        rows    = data.complaints?.data || data.complaints || [];
        headers = ['id','user_id','type','priority','status','created_at'];
    }

    const csv  = [headers.join(','), ...rows.map(r => headers.map(h => JSON.stringify(r[h]??'')).join(','))].join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    const filename = `${type}_report_${new Date().toISOString().slice(0,10)}.csv`;
    a.href = url; a.download = filename; a.click();
    URL.revokeObjectURL(url);

    // Save to history
    const hist = JSON.parse(localStorage.getItem('export_history') || '[]');
    hist.push({ report: label, from, to, records: rows.length, filename, exported_at: new Date().toISOString() });
    localStorage.setItem('export_history', JSON.stringify(hist));
    loadHistory();
}

document.addEventListener('DOMContentLoaded', () => {
    const today = new Date().toISOString().slice(0,10);
    const month = new Date(Date.now() - 30*86400000).toISOString().slice(0,10);
    document.getElementById('e-from').value = month;
    document.getElementById('e-to').value   = today;
    loadHistory();
});
</script>
@endpush
