@extends('layouts.superadmin')
@section('title', 'Settings')
@section('page-title', 'Settings')

@push('head')
<style>
.settings-layout { display:grid; grid-template-columns:200px 1fr; gap:16px; }
@media(max-width:768px){ .settings-layout{grid-template-columns:1fr;} }
.settings-menu { display:flex; flex-direction:column; gap:2px; }
.settings-menu-item {
    display:flex; align-items:center; gap:9px;
    padding:9px 12px; border-radius:var(--rh-radius-sm);
    font-size:13px; font-weight:500; color:var(--rh-muted);
    cursor:pointer; transition:all var(--rh-transition);
    border:none; background:none; text-align:left; width:100%;
}
.settings-menu-item:hover { background:var(--rh-brand-lt); color:var(--rh-brand); }
.settings-menu-item.active { background:var(--rh-brand); color:#fff; font-weight:600; }
.settings-menu-item svg { width:16px; height:16px; flex-shrink:0; }
.settings-section { display:none; }
.settings-section.active { display:block; }
.settings-row { display:flex; align-items:center; justify-content:space-between; padding:14px 0; border-bottom:1px solid var(--rh-border); gap:16px; }
.settings-row:last-child { border-bottom:none; }
.settings-row-info { flex:1; }
.settings-row-lbl { font-size:13px; font-weight:600; color:var(--rh-text); margin-bottom:2px; }
.settings-row-sub { font-size:11.5px; color:var(--rh-muted); }
.settings-row-ctrl { flex-shrink:0; min-width:180px; }
.save-btn { display:inline-flex;align-items:center;gap:6px;padding:7px 16px;border-radius:7px;font-size:13px;font-weight:600;cursor:pointer;border:none;transition:all .15s;margin-left:auto;background:var(--rh-brand);color:#fff; }
.save-btn:disabled { opacity:.6; cursor:not-allowed; }
.toast { position:fixed;bottom:24px;right:24px;padding:12px 20px;border-radius:10px;font-size:13.5px;font-weight:600;color:#fff;z-index:9999;display:none;box-shadow:0 4px 20px rgba(0,0,0,.2); }
.toast.show { display:block; }
.toast.success { background:#10b981; }
.toast.error   { background:#ef4444; }
</style>
@endpush

@section('content')

<div class="rh-breadcrumb">
    <a href="{{ route('superadmin.dashboard') }}">Dashboard</a>
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>Settings</span>
</div>

<div id="toast" class="toast"></div>

<div class="settings-layout">

    {{-- Sidebar Menu --}}
    <div class="rh-card" style="height:fit-content;padding:10px">
        <div class="settings-menu">
            <button class="settings-menu-item active" onclick="switchTab(this,'general')">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                General
            </button>
            <button class="settings-menu-item" onclick="switchTab(this,'notifications')">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                Notifications
            </button>
            <button class="settings-menu-item" onclick="switchTab(this,'finance')">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Finance
            </button>
            <button class="settings-menu-item" onclick="switchTab(this,'api')">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                API / Integrations
            </button>
            <button class="settings-menu-item" onclick="switchTab(this,'smtp')">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                SMTP / SMS
            </button>
        </div>
    </div>

    {{-- Content Panels --}}
    <div>

        {{-- General --}}
        <div class="rh-card settings-section active" id="tab-general">
            <div class="rh-card-header">
                <span class="rh-card-title">General Settings</span>
                <button class="save-btn" onclick="saveTab('general')">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Save Changes
                </button>
            </div>
            <div class="rh-card-body">
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">Platform Name</div><div class="settings-row-sub">Displayed across the platform</div></div>
                    <div class="settings-row-ctrl"><input type="text" class="rh-input" id="platform_name"></div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">Support Email</div><div class="settings-row-sub">Users can contact support via this email</div></div>
                    <div class="settings-row-ctrl"><input type="email" class="rh-input" id="support_email"></div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">Support Phone</div><div class="settings-row-sub">Shown in user portal & seller apps</div></div>
                    <div class="settings-row-ctrl"><input type="text" class="rh-input" id="support_phone"></div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">Timezone</div><div class="settings-row-sub">All dates & times shown in this timezone</div></div>
                    <div class="settings-row-ctrl">
                        <select class="rh-input" id="timezone">
                            <option value="Asia/Kolkata">Asia/Kolkata (IST)</option>
                            <option value="UTC">UTC</option>
                        </select>
                    </div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">Currency</div><div class="settings-row-sub">Default currency for all transactions</div></div>
                    <div class="settings-row-ctrl">
                        <select class="rh-input" id="currency">
                            <option value="INR">INR (&#8377;)</option>
                            <option value="USD">USD ($)</option>
                        </select>
                    </div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">Maintenance Mode</div><div class="settings-row-sub">Put the platform in maintenance mode (sellers can't transact)</div></div>
                    <div class="settings-row-ctrl" style="display:flex;align-items:center;justify-content:flex-end">
                        <div class="rh-toggle-wrap">
                            <input type="checkbox" class="rh-toggle-input" id="maintenance_mode">
                            <label for="maintenance_mode" class="rh-toggle"></label>
                        </div>
                    </div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">Admin Multiple Login</div><div class="settings-row-sub">Allow same admin account to stay logged in on multiple devices</div></div>
                    <div class="settings-row-ctrl" style="display:flex;align-items:center;justify-content:flex-end">
                        <div class="rh-toggle-wrap">
                            <input type="checkbox" class="rh-toggle-input" id="admin_multiple_sessions">
                            <label for="admin_multiple_sessions" class="rh-toggle"></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Notifications --}}
        <div class="rh-card settings-section" id="tab-notifications">
            <div class="rh-card-header">
                <span class="rh-card-title">Notification Settings</span>
                <button class="save-btn" onclick="saveTab('notifications')">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Save Changes
                </button>
            </div>
            <div class="rh-card-body">
                @php
                $notifs = [
                    ['key'=>'notif_topup_request', 'label'=>'New Wallet Top-up Request',  'sub'=>'Alert SuperAdmin when a top-up is requested'],
                    ['key'=>'notif_api_failure',   'label'=>'API Failure Alert',           'sub'=>'Send email when any API returns errors > 5%'],
                    ['key'=>'notif_low_balance',   'label'=>'Low Wallet Balance',          'sub'=>'Notify admin when seller balance drops below ₹500'],
                    ['key'=>'notif_new_admin',     'label'=>'New Admin Registered',        'sub'=>'Alert SuperAdmin when a new admin is added'],
                    ['key'=>'notif_daily_summary', 'label'=>'Daily Revenue Summary',       'sub'=>'Send daily email report at 11:59 PM IST'],
                    ['key'=>'notif_complaint_esc', 'label'=>'Complaint Escalation',        'sub'=>'Alert when complaint is unresolved for 24+ hours'],
                ];
                @endphp
                @foreach($notifs as $n)
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">{{ $n['label'] }}</div><div class="settings-row-sub">{{ $n['sub'] }}</div></div>
                    <div class="settings-row-ctrl" style="display:flex;justify-content:flex-end">
                        <div class="rh-toggle-wrap">
                            <input type="checkbox" class="rh-toggle-input" id="{{ $n['key'] }}">
                            <label for="{{ $n['key'] }}" class="rh-toggle"></label>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Finance --}}
        <div class="rh-card settings-section" id="tab-finance">
            <div class="rh-card-header">
                <span class="rh-card-title">Finance Settings</span>
                <button class="save-btn" onclick="saveTab('finance')">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Save Changes
                </button>
            </div>
            <div class="rh-card-body">
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">Minimum Wallet Balance</div><div class="settings-row-sub">Block recharges below this amount</div></div>
                    <div class="settings-row-ctrl"><input type="number" class="rh-input" id="min_wallet_balance" min="0"></div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">Auto Top-up Approval</div><div class="settings-row-sub">Auto-approve top-ups below this amount</div></div>
                    <div class="settings-row-ctrl"><input type="number" class="rh-input" id="auto_topup_threshold" min="0"></div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">Max Single Recharge</div><div class="settings-row-sub">Maximum allowed per single recharge</div></div>
                    <div class="settings-row-ctrl"><input type="number" class="rh-input" id="max_single_recharge" min="1"></div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">GST on Commission</div><div class="settings-row-sub">Apply GST (18%) on commission amounts</div></div>
                    <div class="settings-row-ctrl" style="display:flex;justify-content:flex-end">
                        <div class="rh-toggle-wrap">
                            <input type="checkbox" class="rh-toggle-input" id="gst_on_commission">
                            <label for="gst_on_commission" class="rh-toggle"></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- API --}}
        <div class="rh-card settings-section" id="tab-api">
            <div class="rh-card-header">
                <span class="rh-card-title">API & Integration Settings</span>
                <button class="save-btn" onclick="saveTab('api')">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Save Changes
                </button>
            </div>
            <div class="rh-card-body">
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">Default API Timeout</div><div class="settings-row-sub">Seconds before a request is considered failed</div></div>
                    <div class="settings-row-ctrl"><input type="number" class="rh-input" id="api_timeout" min="5" max="300"></div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">Auto Fallback on Failure</div><div class="settings-row-sub">Switch to backup API on primary failure</div></div>
                    <div class="settings-row-ctrl" style="display:flex;justify-content:flex-end">
                        <div class="rh-toggle-wrap">
                            <input type="checkbox" class="rh-toggle-input" id="auto_fallback">
                            <label for="auto_fallback" class="rh-toggle"></label>
                        </div>
                    </div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">Rate Limit (per seller)</div><div class="settings-row-sub">Max API requests per minute per seller</div></div>
                    <div class="settings-row-ctrl"><input type="number" class="rh-input" id="rate_limit_per_seller" min="1"></div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">Webhook Retry Attempts</div><div class="settings-row-sub">Times to retry failed webhook deliveries</div></div>
                    <div class="settings-row-ctrl"><input type="number" class="rh-input" id="webhook_retry_attempts" min="0" max="10"></div>
                </div>
            </div>
        </div>

        {{-- SMTP --}}
        <div class="rh-card settings-section" id="tab-smtp">
            <div class="rh-card-header">
                <span class="rh-card-title">SMTP / SMS Gateway</span>
                <button class="save-btn" onclick="saveTab('smtp')">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Save &amp; Test
                </button>
            </div>
            <div class="rh-card-body">
                <h4 style="font-size:12.5px;font-weight:700;color:var(--rh-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:12px">Email (SMTP)</h4>
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">SMTP Host</div></div>
                    <div class="settings-row-ctrl"><input type="text" class="rh-input" id="smtp_host" placeholder="smtp.gmail.com"></div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">SMTP Port</div></div>
                    <div class="settings-row-ctrl"><input type="number" class="rh-input" id="smtp_port"></div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">SMTP Username</div></div>
                    <div class="settings-row-ctrl"><input type="email" class="rh-input" id="smtp_username"></div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">SMTP Password</div></div>
                    <div class="settings-row-ctrl"><input type="password" class="rh-input" id="smtp_password" placeholder="Leave blank to keep current"></div>
                </div>
                <h4 style="font-size:12.5px;font-weight:700;color:var(--rh-muted);text-transform:uppercase;letter-spacing:.05em;margin:18px 0 12px">SMS Gateway</h4>
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">SMS Provider</div></div>
                    <div class="settings-row-ctrl">
                        <select class="rh-input" id="sms_provider">
                            <option>Textlocal</option>
                            <option>MSG91</option>
                            <option>Fast2SMS</option>
                            <option>Twilio</option>
                        </select>
                    </div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">API Key</div></div>
                    <div class="settings-row-ctrl"><input type="password" class="rh-input" id="sms_api_key" placeholder="Leave blank to keep current"></div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">Sender ID</div></div>
                    <div class="settings-row-ctrl"><input type="text" class="rh-input" id="sms_sender_id"></div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

function switchTab(btn, tabId) {
    document.querySelectorAll('.settings-menu-item').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.settings-section').forEach(s => s.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('tab-' + tabId).classList.add('active');
}

function showToast(msg, type = 'success') {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = 'toast show ' + type;
    setTimeout(() => t.classList.remove('show'), 3500);
}

function val(id)  { return document.getElementById(id)?.value ?? ''; }
function chk(id)  { return document.getElementById(id)?.checked ? '1' : '0'; }
function setVal(id, v) { const el = document.getElementById(id); if (el) el.value = v ?? ''; }
function setChk(id, v) { const el = document.getElementById(id); if (el) el.checked = v === '1' || v === true; }

async function post(url, data) {
    const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify(data),
    });
    return res.json();
}

const tabPayloads = {
    general: () => ({
        platform_name:    val('platform_name'),
        support_email:    val('support_email'),
        support_phone:    val('support_phone'),
        timezone:         val('timezone'),
        currency:         val('currency'),
        maintenance_mode: chk('maintenance_mode'),
        admin_multiple_sessions: chk('admin_multiple_sessions'),
    }),
    notifications: () => ({
        notif_topup_request: chk('notif_topup_request'),
        notif_api_failure:   chk('notif_api_failure'),
        notif_low_balance:   chk('notif_low_balance'),
        notif_new_admin:     chk('notif_new_admin'),
        notif_daily_summary: chk('notif_daily_summary'),
        notif_complaint_esc: chk('notif_complaint_esc'),
    }),
    finance: () => ({
        min_wallet_balance:   val('min_wallet_balance'),
        auto_topup_threshold: val('auto_topup_threshold'),
        max_single_recharge:  val('max_single_recharge'),
        gst_on_commission:    chk('gst_on_commission'),
    }),
    api: () => ({
        api_timeout:            val('api_timeout'),
        auto_fallback:          chk('auto_fallback'),
        rate_limit_per_seller:  val('rate_limit_per_seller'),
        webhook_retry_attempts: val('webhook_retry_attempts'),
    }),
    smtp: () => {
        const d = {
            smtp_host:     val('smtp_host'),
            smtp_port:     val('smtp_port'),
            smtp_username: val('smtp_username'),
            sms_provider:  val('sms_provider'),
            sms_sender_id: val('sms_sender_id'),
        };
        const pw  = val('smtp_password');
        const key = val('sms_api_key');
        if (pw)  d.smtp_password = pw;
        if (key) d.sms_api_key   = key;
        return d;
    },
};

async function saveTab(tab) {
    const btn = document.querySelector(`#tab-${tab} .save-btn`);
    btn.disabled = true;
    btn.textContent = 'Saving…';
    try {
        const j = await post(`/superadmin/api/settings/${tab}`, tabPayloads[tab]());
        showToast(j.message || 'Saved!', j.success ? 'success' : 'error');
    } catch {
        showToast('Network error. Please try again.', 'error');
    }
    btn.disabled = false;
    btn.innerHTML = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Save Changes';
}

async function loadSettings() {
    try {
        const res = await fetch('/superadmin/api/settings');
        const j   = await res.json();
        const s   = j.data || {};

        setVal('platform_name',       s.platform_name);
        setVal('support_email',       s.support_email);
        setVal('support_phone',       s.support_phone);
        setVal('timezone',            s.timezone);
        setVal('currency',            s.currency);
        setChk('maintenance_mode',    s.maintenance_mode);
        setChk('admin_multiple_sessions', s.admin_multiple_sessions ?? '1');

        setChk('notif_topup_request', s.notif_topup_request);
        setChk('notif_api_failure',   s.notif_api_failure);
        setChk('notif_low_balance',   s.notif_low_balance);
        setChk('notif_new_admin',     s.notif_new_admin);
        setChk('notif_daily_summary', s.notif_daily_summary);
        setChk('notif_complaint_esc', s.notif_complaint_esc);

        setVal('min_wallet_balance',   s.min_wallet_balance);
        setVal('auto_topup_threshold', s.auto_topup_threshold);
        setVal('max_single_recharge',  s.max_single_recharge);
        setChk('gst_on_commission',    s.gst_on_commission);

        setVal('api_timeout',            s.api_timeout);
        setChk('auto_fallback',          s.auto_fallback);
        setVal('rate_limit_per_seller',  s.rate_limit_per_seller);
        setVal('webhook_retry_attempts', s.webhook_retry_attempts);

        setVal('smtp_host',    s.smtp_host);
        setVal('smtp_port',    s.smtp_port);
        setVal('smtp_username',s.smtp_username);
        setVal('sms_provider', s.sms_provider);
        setVal('sms_sender_id',s.sms_sender_id);
    } catch {
        showToast('Could not load settings.', 'error');
    }
}

document.addEventListener('DOMContentLoaded', loadSettings);
</script>
@endpush
