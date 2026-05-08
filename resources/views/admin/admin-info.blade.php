@extends('layouts.admin')

@section('title', 'Admin Info')
@section('page-title', 'Admin Info')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;gap:12px;flex-wrap:wrap">
    <div>
        <h1 style="font-size:20px;font-weight:700;color:var(--text-primary)">Admin Info</h1>
        <div class="breadcrumb" style="margin-bottom:0;margin-top:3px">
            <a href="/admin/dashboard">Dashboard</a>
            <svg class="breadcrumb-sep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span>Callback, Timeout & Seller Notice</span>
        </div>
    </div>
    <button class="btn btn-primary btn-sm" onclick="saveSettings()">Save Settings</button>
</div>

<div class="admin-info-grid">
    <div class="card">
        <div class="card-header"><h3 class="card-title">Recharge Timeout</h3></div>
        <div class="card-body">
            <label class="ai-label">Request Timeout Seconds</label>
            <input class="ai-input" type="number" id="recharge_request_timeout" min="3" max="120">
            <div class="ai-help">Operator API response ka max wait time.</div>

            <label class="ai-label" style="margin-top:14px">Connect Timeout Seconds</label>
            <input class="ai-input" type="number" id="recharge_connect_timeout" min="1" max="60">
            <div class="ai-help">API server connect hone ka max wait time.</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Seller Callback</h3></div>
        <div class="card-body">
            <div class="ai-row">
                <div><div class="ai-title">Callback Enabled</div><div class="ai-help">Seller ko status callback bhejna hai.</div></div>
                <label class="ai-switch"><input type="checkbox" id="seller_callback_enabled"><span></span></label>
            </div>
            <div class="ai-row">
                <div><div class="ai-title">Instant Callback</div><div class="ai-help">Transaction clear hote hi callback send hoga.</div></div>
                <label class="ai-switch"><input type="checkbox" id="seller_callback_instant"><span></span></label>
            </div>
            <div class="ai-row">
                <div><div class="ai-title">Late Transaction Callback</div><div class="ai-help">Late callback bhi seller ko forward hoga.</div></div>
                <label class="ai-switch"><input type="checkbox" id="seller_callback_late"><span></span></label>
            </div>

            <label class="ai-label" style="margin-top:14px">Callback Timeout Seconds</label>
            <input class="ai-input" type="number" id="seller_callback_timeout" min="3" max="120">

            <label class="ai-label" style="margin-top:14px">Late After Minutes</label>
            <input class="ai-input" type="number" id="seller_callback_late_after_minutes" min="1" max="1440">
        </div>
    </div>

    <div class="card ai-wide">
        <div class="card-header"><h3 class="card-title">Seller Dashboard Notice</h3></div>
        <div class="card-body">
            <div class="ai-row" style="margin-bottom:14px">
                <div><div class="ai-title">Show Notice</div><div class="ai-help">ON karne par seller dashboard me message show hoga.</div></div>
                <label class="ai-switch"><input type="checkbox" id="seller_notice_enabled"><span></span></label>
            </div>

            <label class="ai-label">Notice Title</label>
            <input class="ai-input" type="text" id="seller_notice_title" maxlength="120" placeholder="Important Notice">

            <label class="ai-label" style="margin-top:14px">Notice Message</label>
            <textarea class="ai-input" id="seller_notice_message" rows="5" maxlength="1000" placeholder="Seller dashboard par jo message show karna hai..."></textarea>
        </div>
    </div>
</div>
@endsection

@push('head')
<style>
.admin-info-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}
.ai-wide{grid-column:1/-1}
.ai-label{display:block;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:6px}
.ai-input{width:100%;border:1px solid var(--border);border-radius:8px;padding:9px 12px;font-size:13px;color:var(--text-primary);background:var(--card-bg);outline:none}
.ai-help{font-size:12px;color:var(--text-muted);margin-top:4px}
.ai-title{font-size:13px;font-weight:700;color:var(--text-primary)}
.ai-row{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:10px 0;border-bottom:1px solid var(--border)}
.ai-row:last-child{border-bottom:none}
.ai-switch{position:relative;width:42px;height:24px;display:inline-block;flex:0 0 auto}
.ai-switch input{display:none}
.ai-switch span{position:absolute;inset:0;background:#cbd5e1;border-radius:999px;cursor:pointer;transition:.18s}
.ai-switch span:before{content:"";position:absolute;width:18px;height:18px;left:3px;top:3px;background:#fff;border-radius:50%;transition:.18s;box-shadow:0 1px 4px rgba(0,0,0,.2)}
.ai-switch input:checked+span{background:var(--accent-blue)}
.ai-switch input:checked+span:before{transform:translateX(18px)}
@media(max-width:800px){.admin-info-grid{grid-template-columns:1fr}}
</style>
@endpush

@push('scripts')
<script>
async function loadSettings() {
    const res = await apiFetch('/api/v1/employee/admin-info');
    const data = await res.json();
    const s = data.data || {};
    Object.keys(s).forEach(key => {
        const el = document.getElementById(key);
        if (!el) return;
        if (el.type === 'checkbox') el.checked = String(s[key]) === '1';
        else el.value = s[key] ?? '';
    });
}

async function saveSettings() {
    const body = {
        recharge_request_timeout: val('recharge_request_timeout'),
        recharge_connect_timeout: val('recharge_connect_timeout'),
        seller_callback_enabled: chk('seller_callback_enabled'),
        seller_callback_timeout: val('seller_callback_timeout'),
        seller_callback_instant: chk('seller_callback_instant'),
        seller_callback_late: chk('seller_callback_late'),
        seller_callback_late_after_minutes: val('seller_callback_late_after_minutes'),
        seller_notice_enabled: chk('seller_notice_enabled'),
        seller_notice_title: val('seller_notice_title'),
        seller_notice_message: val('seller_notice_message'),
    };

    const res = await apiFetch('/api/v1/employee/admin-info', {
        method: 'PUT',
        body: JSON.stringify(body)
    });
    const data = await res.json();
    showToast(data.message || (res.ok ? 'Saved' : 'Save failed'), res.ok ? 'success' : 'error');
}

function val(id) { return document.getElementById(id)?.value ?? ''; }
function chk(id) { return document.getElementById(id)?.checked ? '1' : '0'; }
function showToast(msg, type) {
    const t = document.createElement('div');
    t.textContent = msg;
    t.style.cssText = `position:fixed;right:22px;bottom:22px;background:${type==='success'?'#10b981':'#ef4444'};color:#fff;padding:12px 18px;border-radius:10px;font-size:13px;font-weight:700;z-index:9999`;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3000);
}

document.addEventListener('DOMContentLoaded', loadSettings);
</script>
@endpush
