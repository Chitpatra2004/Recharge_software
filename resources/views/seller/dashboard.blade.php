@extends('layouts.seller')
@section('title','Dashboard')
@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-sub">Welcome back! Here's your business overview.</p>
    </div>
    <div id="integration-status-bar"></div>
</div>

<!-- Stats Grid -->
<div class="stats-grid" id="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(16,185,129,.15)">
            <svg fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <div class="stat-body">
            <div class="stat-label">Total Sales</div>
            <div class="stat-value" id="s-total">—</div>
            <div class="stat-sub" id="s-total-amt"></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(59,130,246,.15)">
            <svg fill="none" viewBox="0 0 24 24" stroke="#3b82f6" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <div class="stat-body">
            <div class="stat-label">Today's Sales</div>
            <div class="stat-value" id="s-today">—</div>
            <div class="stat-sub" id="s-today-amt"></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(245,158,11,.15)">
            <svg fill="none" viewBox="0 0 24 24" stroke="#f59e0b" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>
        </div>
        <div class="stat-body">
            <div class="stat-label">Wallet Balance</div>
            <div class="stat-value" id="s-wallet">—</div>
            <div class="stat-sub">Available balance</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(139,92,246,.15)">
            <svg fill="none" viewBox="0 0 24 24" stroke="#8b5cf6" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <div class="stat-body">
            <div class="stat-label">Pending Payments</div>
            <div class="stat-value" id="s-payments">—</div>
            <div class="stat-sub">Awaiting approval</div>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:20px">

    <!-- Integration Status Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">API Integration Status</h3>
        </div>
        <div id="integration-card-body" style="padding:20px 24px">
            <div style="text-align:center;color:#6b7280;padding:20px 0">Loading…</div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Transactions</h3>
            <a href="/seller/sales" style="font-size:12px;color:#10b981;text-decoration:none;font-weight:600">View All →</a>
        </div>
        <div id="recent-txns" style="padding:0">
            <div style="text-align:center;color:#6b7280;padding:20px">Loading…</div>
        </div>
    </div>

</div>

<script>
(function(){
    apiFetch('/api/v1/seller/dashboard').then(d=>{
        const data = d.data?.stats || {};

        // Stats
        el('s-total').textContent    = data.total_sales ?? '0';
        el('s-total-amt').textContent= '₹' + fmtMoney(data.total_amount ?? 0);
        el('s-today').textContent    = data.today_sales ?? '0';
        el('s-today-amt').textContent= '₹' + fmtMoney(data.today_amount ?? 0);
        el('s-wallet').textContent   = '₹' + fmtMoney(data.wallet_balance ?? 0);
        el('s-payments').textContent = data.pending_payments ?? '0';

        // Integration status — API returns d.data.integration, integration has 'website' not 'website_url'
        const intg = d.data?.integration;
        let ibody = '';
        if (!intg || intg.status === 'none') {
            ibody = `<div style="text-align:center;padding:16px 0">
                <div style="width:48px;height:48px;background:#fef3c7;border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
                    <svg fill="none" viewBox="0 0 24 24" stroke="#f59e0b" stroke-width="2" style="width:24px;height:24px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <p style="font-size:13.5px;font-weight:600;color:#1e293b;margin-bottom:6px">No Integration Request</p>
                <p style="font-size:12.5px;color:#64748b;margin-bottom:16px">Submit your website details to get API access</p>
                <a href="/seller/api-config" style="display:inline-flex;align-items:center;gap:6px;background:#10b981;color:#fff;padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Submit Integration Request
                </a>
            </div>`;
        } else {
            const badges = {pending:'#f59e0b',approved:'#10b981',rejected:'#ef4444'};
            const color  = badges[intg.status] || '#64748b';
            ibody = `<div style="display:flex;flex-direction:column;gap:12px">
                <div style="display:flex;align-items:center;justify-content:space-between">
                    <span style="font-size:13px;color:#64748b">Status</span>
                    <span class="badge-${intg.status==='approved'?'success':intg.status==='rejected'?'danger':'warning'}">${intg.status.toUpperCase()}</span>
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between">
                    <span style="font-size:13px;color:#64748b">Website URL</span>
                    <span style="font-size:13px;font-weight:500;color:#1e293b;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${intg.website||intg.website_url||'—'}</span>
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between">
                    <span style="font-size:13px;color:#64748b">Submitted</span>
                    <span style="font-size:13px;color:#1e293b">${fmtDate(intg.created_at)}</span>
                </div>
                ${intg.status==='approved'?`<a href="/seller/api-config" style="display:block;text-align:center;background:#10b981;color:#fff;padding:8px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;margin-top:4px">View API Configuration →</a>`:''}
                ${intg.status==='rejected'?`<div style="background:#fff1f2;border:1px solid #fecdd3;border-radius:8px;padding:10px 12px;font-size:12.5px;color:#be123c">${intg.admin_notes||'Request rejected by admin.'}</div><a href="/seller/api-config" style="display:block;text-align:center;border:1.5px solid #10b981;color:#10b981;padding:8px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;margin-top:4px">Re-submit Request</a>`:''}
            </div>`;
        }
        document.getElementById('integration-card-body').innerHTML = ibody;

        // Recent transactions
        const txns = d.data?.recent_sales || [];
        if (!txns.length) {
            document.getElementById('recent-txns').innerHTML = '<div style="text-align:center;color:#6b7280;padding:24px;font-size:13.5px">No transactions yet</div>';
        } else {
            let html = '<table class="table"><thead><tr><th>Mobile</th><th>Operator</th><th>Amount</th><th>Status</th></tr></thead><tbody>';
            txns.forEach(t => {
                html += `<tr>
                    <td style="font-weight:500">${t.mobile||t.mobile_number||'—'}</td>
                    <td><span style="font-size:12px">${t.operator_code||t.operator||'—'}</span></td>
                    <td>₹${fmtMoney(t.amount)}</td>
                    <td>${statusBadge(t.status)}</td>
                </tr>`;
            });
            html += '</tbody></table>';
            document.getElementById('recent-txns').innerHTML = html;
        }
    }).catch(()=>{
        document.getElementById('integration-card-body').innerHTML = '<div style="text-align:center;color:#ef4444;padding:20px;font-size:13px">Failed to load data</div>';
        document.getElementById('recent-txns').innerHTML = '<div style="text-align:center;color:#ef4444;padding:20px;font-size:13px">Failed to load</div>';
    });
})();
</script>
@endsection
