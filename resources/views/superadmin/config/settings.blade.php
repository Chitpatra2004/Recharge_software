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
</style>
@endpush

@section('content')

<div class="rh-breadcrumb">
    <a href="{{ route('superadmin.dashboard') }}">Dashboard</a>
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>Settings</span>
</div>

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
                <button class="btn btn-sm btn-primary" style="margin-left:auto">Save Changes</button>
            </div>
            <div class="rh-card-body">
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">Platform Name</div><div class="settings-row-sub">Displayed across the platform</div></div>
                    <div class="settings-row-ctrl"><input type="text" class="rh-input" value="RechargeHub"></div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">Support Email</div><div class="settings-row-sub">Users can contact support via this email</div></div>
                    <div class="settings-row-ctrl"><input type="email" class="rh-input" value="support@rechargerhub.in"></div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">Support Phone</div><div class="settings-row-sub">Shown in user portal & seller apps</div></div>
                    <div class="settings-row-ctrl"><input type="text" class="rh-input" value="+91 98765 43210"></div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">Timezone</div><div class="settings-row-sub">All dates & times shown in this timezone</div></div>
                    <div class="settings-row-ctrl">
                        <select class="rh-input">
                            <option selected>Asia/Kolkata (IST)</option>
                            <option>UTC</option>
                        </select>
                    </div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">Currency</div><div class="settings-row-sub">Default currency for all transactions</div></div>
                    <div class="settings-row-ctrl">
                        <select class="rh-input">
                            <option selected>INR (₹)</option>
                            <option>USD ($)</option>
                        </select>
                    </div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">Maintenance Mode</div><div class="settings-row-sub">Put the platform in maintenance mode (sellers can't transact)</div></div>
                    <div class="settings-row-ctrl" style="display:flex;align-items:center;justify-content:flex-end">
                        <div class="rh-toggle-wrap">
                            <input type="checkbox" class="rh-toggle-input" id="maintenanceMode">
                            <label for="maintenanceMode" class="rh-toggle"></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Notifications --}}
        <div class="rh-card settings-section" id="tab-notifications">
            <div class="rh-card-header">
                <span class="rh-card-title">Notification Settings</span>
                <button class="btn btn-sm btn-primary" style="margin-left:auto">Save Changes</button>
            </div>
            <div class="rh-card-body">
                @php
                $notifs = [
                    ['label'=>'New Wallet Top-up Request','sub'=>'Alert SuperAdmin when a top-up is requested','enabled'=>true],
                    ['label'=>'API Failure Alert','sub'=>'Send email when any API returns errors > 5%','enabled'=>true],
                    ['label'=>'Low Wallet Balance','sub'=>'Notify admin when seller balance drops below ₹500','enabled'=>true],
                    ['label'=>'New Admin Registered','sub'=>'Alert SuperAdmin when a new admin is added','enabled'=>true],
                    ['label'=>'Daily Revenue Summary','sub'=>'Send daily email report at 11:59 PM IST','enabled'=>false],
                    ['label'=>'Complaint Escalation','sub'=>'Alert when complaint is unresolved for 24+ hours','enabled'=>true],
                ];
                @endphp
                @foreach($notifs as $i=>$n)
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">{{ $n['label'] }}</div><div class="settings-row-sub">{{ $n['sub'] }}</div></div>
                    <div class="settings-row-ctrl" style="display:flex;justify-content:flex-end">
                        <div class="rh-toggle-wrap">
                            <input type="checkbox" class="rh-toggle-input" id="notif_{{ $i }}" {{ $n['enabled']?'checked':'' }}>
                            <label for="notif_{{ $i }}" class="rh-toggle"></label>
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
                <button class="btn btn-sm btn-primary" style="margin-left:auto">Save Changes</button>
            </div>
            <div class="rh-card-body">
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">Minimum Wallet Balance</div><div class="settings-row-sub">Block recharges below this amount</div></div>
                    <div class="settings-row-ctrl"><input type="number" class="rh-input" value="100"></div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">Auto Top-up Approval</div><div class="settings-row-sub">Auto-approve top-ups below this amount</div></div>
                    <div class="settings-row-ctrl"><input type="number" class="rh-input" value="5000"></div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">Max Single Recharge</div><div class="settings-row-sub">Maximum allowed per single recharge</div></div>
                    <div class="settings-row-ctrl"><input type="number" class="rh-input" value="10000"></div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">GST on Commission</div><div class="settings-row-sub">Apply GST (18%) on commission amounts</div></div>
                    <div class="settings-row-ctrl" style="display:flex;justify-content:flex-end">
                        <div class="rh-toggle-wrap">
                            <input type="checkbox" class="rh-toggle-input" id="gstToggle" checked>
                            <label for="gstToggle" class="rh-toggle"></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- API --}}
        <div class="rh-card settings-section" id="tab-api">
            <div class="rh-card-header">
                <span class="rh-card-title">API & Integration Settings</span>
                <button class="btn btn-sm btn-primary" style="margin-left:auto">Save Changes</button>
            </div>
            <div class="rh-card-body">
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">Default API Timeout</div><div class="settings-row-sub">Seconds before a request is considered failed</div></div>
                    <div class="settings-row-ctrl"><input type="number" class="rh-input" value="30"></div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">Auto Fallback on Failure</div><div class="settings-row-sub">Switch to backup API on primary failure</div></div>
                    <div class="settings-row-ctrl" style="display:flex;justify-content:flex-end">
                        <div class="rh-toggle-wrap">
                            <input type="checkbox" class="rh-toggle-input" id="autoFallback" checked>
                            <label for="autoFallback" class="rh-toggle"></label>
                        </div>
                    </div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">Rate Limit (per seller)</div><div class="settings-row-sub">Max API requests per minute per seller</div></div>
                    <div class="settings-row-ctrl"><input type="number" class="rh-input" value="100"></div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">Webhook Retry Attempts</div><div class="settings-row-sub">Times to retry failed webhook deliveries</div></div>
                    <div class="settings-row-ctrl"><input type="number" class="rh-input" value="3"></div>
                </div>
            </div>
        </div>

        {{-- SMTP --}}
        <div class="rh-card settings-section" id="tab-smtp">
            <div class="rh-card-header">
                <span class="rh-card-title">SMTP / SMS Gateway</span>
                <button class="btn btn-sm btn-primary" style="margin-left:auto">Save & Test</button>
            </div>
            <div class="rh-card-body">
                <h4 style="font-size:12.5px;font-weight:700;color:var(--rh-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:12px">Email (SMTP)</h4>
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">SMTP Host</div></div>
                    <div class="settings-row-ctrl"><input type="text" class="rh-input" value="smtp.gmail.com"></div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">SMTP Port</div></div>
                    <div class="settings-row-ctrl"><input type="number" class="rh-input" value="587"></div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">SMTP Username</div></div>
                    <div class="settings-row-ctrl"><input type="email" class="rh-input" value="noreply@rechargerhub.in"></div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">SMTP Password</div></div>
                    <div class="settings-row-ctrl"><input type="password" class="rh-input" value="••••••••••••"></div>
                </div>
                <h4 style="font-size:12.5px;font-weight:700;color:var(--rh-muted);text-transform:uppercase;letter-spacing:.05em;margin:18px 0 12px">SMS Gateway</h4>
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">SMS Provider</div></div>
                    <div class="settings-row-ctrl">
                        <select class="rh-input">
                            <option>Textlocal</option>
                            <option>MSG91</option>
                            <option>Fast2SMS</option>
                            <option>Twilio</option>
                        </select>
                    </div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">API Key</div></div>
                    <div class="settings-row-ctrl"><input type="password" class="rh-input" value="••••••••••••••••••••"></div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-info"><div class="settings-row-lbl">Sender ID</div></div>
                    <div class="settings-row-ctrl"><input type="text" class="rh-input" value="RCHGHB"></div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
function switchTab(btn, tabId) {
    document.querySelectorAll('.settings-menu-item').forEach(b=>b.classList.remove('active'));
    document.querySelectorAll('.settings-section').forEach(s=>s.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('tab-'+tabId).classList.add('active');
}
</script>
@endpush
