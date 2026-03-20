@extends('layouts.admin')
@section('title', 'My Profile')
@section('page-title', 'My Profile')

@push('head')
<style>
/* Profile page styles */
.profile-grid { display: grid; grid-template-columns: 260px 1fr 300px; gap: 16px; align-items: start; }
.profile-avatar-wrap { position: relative; width: 90px; height: 90px; margin: 0 auto 16px; }
.profile-avatar { width: 90px; height: 90px; border-radius: 50%; background: linear-gradient(135deg, var(--accent-blue), var(--accent-purple)); display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 800; color: #fff; box-shadow: 0 8px 24px rgba(37,99,235,.35); }
.avatar-upload-btn { position: absolute; bottom: 0; right: 0; width: 28px; height: 28px; border-radius: 50%; background: var(--accent-blue); border: 2px solid #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; }
.avatar-upload-btn svg { width: 13px; height: 13px; color: #fff; }
.tab-bar { display: flex; gap: 4px; border-bottom: 1px solid var(--border); margin-bottom: 20px; }
.tab-btn { padding: 10px 18px; font-size: 13px; font-weight: 600; color: var(--text-secondary); background: none; border: none; cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -1px; transition: all .15s; }
.tab-btn.active { color: var(--accent-blue); border-bottom-color: var(--accent-blue); }
.tab-pane { display: none; }
.tab-pane.active { display: block; }
.form-group { margin-bottom: 16px; }
.form-label { display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase; letter-spacing: .5px; }
.form-control { width: 100%; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 13.5px; font-family: inherit; color: var(--text-primary); background: var(--bg-page); transition: border-color .15s, box-shadow .15s; outline: none; }
.form-control:focus { border-color: var(--accent-blue); box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
.form-control:read-only { background: var(--bg-page); color: var(--text-secondary); cursor: not-allowed; }
.form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.section-title { font-size: 13px; font-weight: 700; color: var(--text-primary); margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
.section-title::after { content: ''; flex: 1; height: 1px; background: var(--border); }
.pwd-strength { height: 4px; border-radius: 4px; background: var(--border); overflow: hidden; margin-top: 8px; }
.pwd-strength-bar { height: 100%; border-radius: 4px; width: 0; transition: width .3s, background .3s; }
.pwd-strength-label { font-size: 11px; margin-top: 4px; }
.pwd-input-wrap { position: relative; }
.pwd-toggle { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-secondary); padding: 4px; }
.pwd-toggle svg { width: 16px; height: 16px; }

/* 2FA styles */
.tfa-card { border: 1.5px solid var(--border); border-radius: var(--radius); padding: 20px; margin-bottom: 16px; }
.tfa-status { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 20px; font-size: 11.5px; font-weight: 700; }
.tfa-status.on { background: #d1fae5; color: #059669; }
.tfa-status.off { background: #fee2e2; color: #dc2626; }
.otp-inputs { display: flex; gap: 10px; margin: 16px 0; }
.otp-box { width: 46px; height: 52px; border: 2px solid var(--border); border-radius: 10px; text-align: center; font-size: 22px; font-weight: 700; color: var(--text-primary); background: var(--bg-page); outline: none; font-family: monospace; transition: border-color .15s; }
.otp-box:focus { border-color: var(--accent-blue); box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
.otp-countdown { font-size: 12px; color: var(--text-muted); }
.backup-codes { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-top: 12px; }
.backup-code { font-family: monospace; font-size: 12px; background: var(--bg-page); border: 1px solid var(--border); border-radius: 6px; padding: 6px 8px; text-align: center; color: var(--text-primary); letter-spacing: 1px; }

/* Permissions grid */
.perm-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
.perm-card { border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 14px; }
.perm-card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; }
.perm-name { font-size: 13px; font-weight: 600; color: var(--text-primary); }
.perm-desc { font-size: 11.5px; color: var(--text-muted); }
.perm-badge { font-size: 10.5px; font-weight: 700; padding: 2px 8px; border-radius: 20px; }
.perm-badge.granted { background: #d1fae5; color: #059669; }
.perm-badge.denied { background: #fee2e2; color: #dc2626; }

/* Activity panel */
.activity-item { display: flex; gap: 10px; padding: 10px 0; border-bottom: 1px solid var(--border); }
.activity-item:last-child { border-bottom: none; }
.activity-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; margin-top: 4px; }
.activity-text { font-size: 12.5px; color: var(--text-primary); line-height: 1.4; }
.activity-time { font-size: 11px; color: var(--text-muted); margin-top: 2px; }

/* Toggle */
.toggle-wrap { display: flex; align-items: center; gap: 10px; }
.toggle { position: relative; width: 40px; height: 22px; }
.toggle input { opacity: 0; width: 0; height: 0; }
.toggle-slider { position: absolute; inset: 0; background: #cbd5e1; border-radius: 22px; cursor: pointer; transition: .2s; }
.toggle-slider::before { content: ''; position: absolute; width: 16px; height: 16px; left: 3px; top: 3px; background: #fff; border-radius: 50%; transition: .2s; }
.toggle input:checked + .toggle-slider { background: var(--accent-blue); }
.toggle input:checked + .toggle-slider::before { transform: translateX(18px); }

/* Session table */
.sessions-table th { background: var(--bg-page); }

@media (max-width:1100px) { .profile-grid { grid-template-columns: 220px 1fr; } .profile-grid > :last-child { display: none; } }
@media (max-width:700px) { .profile-grid { grid-template-columns: 1fr; } .form-grid-2 { grid-template-columns: 1fr; } .perm-grid { grid-template-columns: 1fr 1fr; } }
</style>
@endpush

@section('content')
<div class="breadcrumb">
    <a href="/admin/dashboard">Dashboard</a>
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>My Profile</span>
</div>

<div class="profile-grid">

    <!-- ── LEFT: Profile Card ─────────────────────────────────────── -->
    <div style="display:flex;flex-direction:column;gap:14px">
        <div class="card">
            <div class="card-body" style="text-align:center">
                <div class="profile-avatar-wrap">
                    <div class="profile-avatar" id="pr-avatar">A</div>
                    <label class="avatar-upload-btn" for="avatar-input" title="Change photo">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </label>
                    <input type="file" id="avatar-input" accept="image/*" style="display:none" onchange="previewAvatar(this)">
                </div>
                <div style="font-size:18px;font-weight:700;color:var(--text-primary);margin-bottom:4px" id="pr-name">Loading…</div>
                <div style="font-size:11px;font-weight:700;color:var(--accent-blue);background:#dbeafe;display:inline-block;padding:3px 12px;border-radius:20px;margin-bottom:12px;text-transform:uppercase;letter-spacing:.5px" id="pr-role">—</div>
                <div style="font-size:13px;color:var(--text-secondary)" id="pr-email">—</div>
                <div style="font-size:13px;color:var(--text-muted);margin-top:4px" id="pr-mobile">—</div>

                <div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--border);display:grid;grid-template-columns:1fr 1fr;gap:8px;text-align:left">
                    <div>
                        <div style="font-size:11px;color:var(--text-muted);margin-bottom:2px">Member Since</div>
                        <div style="font-size:12.5px;font-weight:600;color:var(--text-primary)" id="pr-since">—</div>
                    </div>
                    <div>
                        <div style="font-size:11px;color:var(--text-muted);margin-bottom:2px">Status</div>
                        <span style="font-size:11.5px;font-weight:700;background:#d1fae5;color:#059669;padding:2px 8px;border-radius:20px">Active</span>
                    </div>
                    <div style="grid-column:1/-1">
                        <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px">2FA Security</div>
                        <span class="tfa-status off" id="pr-2fa-badge">Not Active</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="card">
            <div class="card-header"><span class="card-title">Quick Stats</span></div>
            <div class="card-body" style="padding:14px 16px">
                <div style="display:flex;flex-direction:column;gap:12px">
                    <div style="display:flex;justify-content:space-between;align-items:center">
                        <span style="font-size:12.5px;color:var(--text-secondary)">Recharges Today</span>
                        <span style="font-size:14px;font-weight:700;color:var(--accent-green)" id="qs-today">—</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center">
                        <span style="font-size:12.5px;color:var(--text-secondary)">This Month</span>
                        <span style="font-size:14px;font-weight:700;color:var(--accent-blue)" id="qs-month">—</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center">
                        <span style="font-size:12.5px;color:var(--text-secondary)">Success Rate</span>
                        <span style="font-size:14px;font-weight:700;color:var(--accent-purple)" id="qs-rate">—</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center">
                        <span style="font-size:12.5px;color:var(--text-secondary)">Last Login</span>
                        <span style="font-size:12px;color:var(--text-muted)" id="qs-login">—</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── MIDDLE: Tabbed Content ──────────────────────────────────── -->
    <div class="card">
        <div style="padding:16px 20px 0">
            <div class="tab-bar">
                <button class="tab-btn active" onclick="switchTab('personal',this)">Personal Info</button>
                <button class="tab-btn" onclick="switchTab('security',this)">Security & 2FA</button>
                <button class="tab-btn" onclick="switchTab('permissions',this)">Permissions</button>
                <button class="tab-btn" onclick="switchTab('prefs',this)">Preferences</button>
            </div>
        </div>

        <div class="card-body">

            <!-- TAB 1: Personal Info -->
            <div class="tab-pane active" id="tab-personal">
                <div id="personal-alert" style="display:none;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:14px"></div>
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="f-name" placeholder="Your full name">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="f-email" readonly>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mobile Number</label>
                        <input type="text" class="form-control" id="f-mobile" readonly>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" id="f-dob">
                    </div>
                    <div class="form-group">
                        <label class="form-label">City</label>
                        <input type="text" class="form-control" id="f-city" placeholder="Your city">
                    </div>
                    <div class="form-group">
                        <label class="form-label">State</label>
                        <select class="form-control" id="f-state">
                            <option value="">Select State</option>
                            <option>Andhra Pradesh</option><option>Delhi</option><option>Gujarat</option>
                            <option>Karnataka</option><option>Kerala</option><option>Maharashtra</option>
                            <option>Rajasthan</option><option>Tamil Nadu</option><option>Telangana</option>
                            <option>Uttar Pradesh</option><option>West Bengal</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">PAN Number <span style="color:var(--text-muted)">(optional)</span></label>
                        <input type="text" class="form-control" id="f-pan" placeholder="ABCDE1234F" maxlength="10" style="text-transform:uppercase">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Employee ID</label>
                        <input type="text" class="form-control" id="f-empid" readonly>
                    </div>
                </div>
                <div style="display:flex;gap:10px;margin-top:4px">
                    <button class="btn btn-primary" onclick="savePersonalInfo()">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Save Changes
                    </button>
                    <button class="btn btn-outline" onclick="loadProfile()">Reset</button>
                </div>
            </div>

            <!-- TAB 2: Security & 2FA -->
            <div class="tab-pane" id="tab-security">

                <!-- A: Change Password -->
                <div class="section-title">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:15px;height:15px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Change Password
                </div>
                <div id="pwd-alert" style="display:none;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:14px"></div>
                <div style="max-width:420px;display:flex;flex-direction:column;gap:14px">
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label">Current Password</label>
                        <div class="pwd-input-wrap">
                            <input type="password" class="form-control" id="pwd-current" placeholder="Enter current password" style="padding-right:42px">
                            <button type="button" class="pwd-toggle" onclick="togglePwdVis('pwd-current',this)"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label">New Password</label>
                        <div class="pwd-input-wrap">
                            <input type="password" class="form-control" id="pwd-new" placeholder="Min. 8 characters" style="padding-right:42px" oninput="checkPwdStrength(this.value)">
                            <button type="button" class="pwd-toggle" onclick="togglePwdVis('pwd-new',this)"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
                        </div>
                        <div class="pwd-strength"><div class="pwd-strength-bar" id="pwd-bar"></div></div>
                        <div class="pwd-strength-label" id="pwd-label" style="color:var(--text-muted)">Enter a password</div>
                    </div>
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label">Confirm New Password</label>
                        <div class="pwd-input-wrap">
                            <input type="password" class="form-control" id="pwd-confirm" placeholder="Repeat new password" style="padding-right:42px">
                            <button type="button" class="pwd-toggle" onclick="togglePwdVis('pwd-confirm',this)"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
                        </div>
                    </div>
                    <button class="btn btn-primary" onclick="changePassword()" style="width:fit-content">Update Password</button>
                </div>

                <!-- B: 2FA -->
                <div class="section-title" style="margin-top:28px">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:15px;height:15px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Two-Factor Authentication
                </div>

                <div class="tfa-card" id="tfa-status-card">
                    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
                        <div>
                            <div style="font-size:14px;font-weight:600;color:var(--text-primary);margin-bottom:4px">Secure your account with OTP</div>
                            <div style="font-size:12.5px;color:var(--text-secondary)">Every login will require a one-time password sent to your registered mobile number.</div>
                        </div>
                        <div style="display:flex;align-items:center;gap:12px">
                            <span class="tfa-status off" id="tfa-badge-main">Not Active</span>
                            <button class="btn btn-primary btn-sm" id="tfa-setup-btn" onclick="startTfaSetup()">Enable 2FA</button>
                            <button class="btn btn-outline btn-sm" id="tfa-disable-btn" onclick="disableTfa()" style="display:none;color:var(--accent-red);border-color:var(--accent-red)">Disable 2FA</button>
                        </div>
                    </div>
                </div>

                <!-- 2FA Setup Steps -->
                <div id="tfa-setup-flow" style="display:none">
                    <!-- Step 1 -->
                    <div id="tfa-step1" class="tfa-card" style="border-color:var(--accent-blue)">
                        <div style="font-size:13px;font-weight:600;color:var(--text-primary);margin-bottom:12px">Step 1 — Send OTP to your mobile</div>
                        <div style="display:flex;gap:10px;align-items:flex-end">
                            <div class="form-group" style="margin-bottom:0;flex:1">
                                <label class="form-label">Mobile Number</label>
                                <input type="text" class="form-control" id="tfa-mobile" placeholder="10-digit mobile number">
                            </div>
                            <button class="btn btn-primary" onclick="sendTfaOtp()">Send OTP</button>
                        </div>
                        <div id="tfa-otp-sending" style="display:none;font-size:12.5px;color:var(--text-muted);margin-top:8px">Sending OTP…</div>
                    </div>

                    <!-- Step 2 -->
                    <div id="tfa-step2" class="tfa-card" style="display:none;border-color:var(--accent-blue)">
                        <div style="font-size:13px;font-weight:600;color:var(--text-primary);margin-bottom:6px">Step 2 — Enter the 6-digit OTP</div>
                        <div style="font-size:12.5px;color:var(--text-secondary);margin-bottom:12px">OTP sent to <strong id="tfa-mobile-display"></strong></div>
                        <div class="otp-inputs" id="otp-boxes">
                            <input class="otp-box" maxlength="1" type="text" inputmode="numeric" pattern="[0-9]">
                            <input class="otp-box" maxlength="1" type="text" inputmode="numeric" pattern="[0-9]">
                            <input class="otp-box" maxlength="1" type="text" inputmode="numeric" pattern="[0-9]">
                            <input class="otp-box" maxlength="1" type="text" inputmode="numeric" pattern="[0-9]">
                            <input class="otp-box" maxlength="1" type="text" inputmode="numeric" pattern="[0-9]">
                            <input class="otp-box" maxlength="1" type="text" inputmode="numeric" pattern="[0-9]">
                        </div>
                        <div style="display:flex;align-items:center;gap:14px;margin-bottom:14px">
                            <div class="otp-countdown">Expires in <strong id="otp-timer">60</strong>s</div>
                            <button class="btn-link" id="resend-btn" onclick="sendTfaOtp()" style="display:none">Resend OTP</button>
                        </div>
                        <div style="display:flex;gap:10px">
                            <button class="btn btn-primary" onclick="verifyTfaOtp()">Verify & Enable 2FA</button>
                            <button class="btn btn-outline btn-sm" onclick="cancelTfaSetup()">Cancel</button>
                        </div>
                        <div id="tfa-verify-error" style="display:none;font-size:12.5px;color:var(--accent-red);margin-top:8px"></div>
                    </div>

                    <!-- Step 3: Success -->
                    <div id="tfa-step3" style="display:none;background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:var(--radius);padding:20px">
                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px">
                            <div style="width:36px;height:36px;background:#d1fae5;border-radius:50%;display:flex;align-items:center;justify-content:center">
                                <svg fill="none" viewBox="0 0 24 24" stroke="#059669" stroke-width="2.5" style="width:18px;height:18px"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div>
                                <div style="font-size:14px;font-weight:700;color:#052e16">2FA Successfully Enabled!</div>
                                <div style="font-size:12.5px;color:#166534">Your account is now protected with two-factor authentication.</div>
                            </div>
                        </div>
                        <div style="font-size:12.5px;font-weight:600;color:#052e16;margin-bottom:8px">Backup Codes — Save these safely:</div>
                        <div class="backup-codes" id="backup-codes-grid"></div>
                        <div style="font-size:11.5px;color:#166534;margin-top:10px">⚠️ These codes can be used to access your account if you lose your mobile. Each code can only be used once.</div>
                    </div>
                </div>

                <!-- C: Active Sessions -->
                <div class="section-title" style="margin-top:28px">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:15px;height:15px"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2"/></svg>
                    Active Sessions
                </div>
                <div class="table-wrap">
                    <table>
                        <thead><tr>
                            <th>Device</th><th>Browser</th><th>IP Address</th><th>Last Active</th><th>Action</th>
                        </tr></thead>
                        <tbody id="sessions-table">
                            <tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:20px">Loading sessions…</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 3: Permissions -->
            <div class="tab-pane" id="tab-permissions">
                <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:var(--radius-sm);padding:12px 16px;font-size:13px;color:#1e40af;margin-bottom:18px;display:flex;gap:8px">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;flex-shrink:0;margin-top:1px"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Permissions are assigned by your Super Admin. Contact them to update access rights.
                </div>
                <div class="perm-grid" id="perm-grid">
                    <!-- Loaded via JS -->
                    <div class="perm-card"><div style="text-align:center;color:var(--text-muted);font-size:13px;padding:20px">Loading permissions…</div></div>
                </div>
            </div>

            <!-- TAB 4: Preferences -->
            <div class="tab-pane" id="tab-prefs">
                <div id="prefs-alert" style="display:none;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:14px"></div>

                <div class="section-title">Notifications</div>
                <div style="display:flex;flex-direction:column;gap:14px;margin-bottom:24px">
                    <div style="display:flex;align-items:center;justify-content:space-between">
                        <div><div style="font-size:13.5px;font-weight:600;color:var(--text-primary)">Email Notifications</div><div style="font-size:12px;color:var(--text-muted)">Receive alerts and reports via email</div></div>
                        <label class="toggle"><input type="checkbox" id="pref-email" checked><span class="toggle-slider"></span></label>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between">
                        <div><div style="font-size:13.5px;font-weight:600;color:var(--text-primary)">SMS Alerts</div><div style="font-size:12px;color:var(--text-muted)">Get critical alerts via SMS</div></div>
                        <label class="toggle"><input type="checkbox" id="pref-sms"><span class="toggle-slider"></span></label>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between">
                        <div><div style="font-size:13.5px;font-weight:600;color:var(--text-primary)">Browser Notifications</div><div style="font-size:12px;color:var(--text-muted)">Desktop push notifications</div></div>
                        <label class="toggle"><input type="checkbox" id="pref-browser" checked><span class="toggle-slider"></span></label>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between">
                        <div><div style="font-size:13.5px;font-weight:600;color:var(--text-primary)">Recharge Alerts</div><div style="font-size:12px;color:var(--text-muted)">Notify on each transaction result</div></div>
                        <label class="toggle"><input type="checkbox" id="pref-recharge" checked><span class="toggle-slider"></span></label>
                    </div>
                </div>

                <div class="section-title">Display Settings</div>
                <div class="form-grid-2" style="max-width:480px">
                    <div class="form-group">
                        <label class="form-label">Language</label>
                        <select class="form-control" id="pref-lang">
                            <option value="en" selected>English</option>
                            <option value="hi">Hindi</option>
                            <option value="gu">Gujarati</option>
                            <option value="mr">Marathi</option>
                            <option value="ta">Tamil</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Timezone</label>
                        <select class="form-control" id="pref-tz">
                            <option value="Asia/Kolkata" selected>IST — Asia/Kolkata</option>
                            <option value="UTC">UTC</option>
                        </select>
                    </div>
                </div>
                <button class="btn btn-primary" onclick="savePrefs()">Save Preferences</button>
            </div>

        </div><!-- /card-body -->
    </div><!-- /middle card -->

    <!-- ── RIGHT: Activity Panel ───────────────────────────────────── -->
    <div style="display:flex;flex-direction:column;gap:14px">
        <div class="card">
            <div class="card-header"><span class="card-title">Recent Activity</span></div>
            <div class="card-body" style="padding:12px 16px">
                <div id="activity-feed">
                    <div style="text-align:center;color:var(--text-muted);font-size:13px;padding:20px">Loading…</div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><span class="card-title">Login History</span></div>
            <div class="card-body" style="padding:12px 16px">
                <div id="login-history">
                    <div style="text-align:center;color:var(--text-muted);font-size:13px;padding:20px">Loading…</div>
                </div>
            </div>
        </div>
    </div>

</div><!-- /profile-grid -->
@endsection

@push('scripts')
<script>
/* ─── PROFILE PAGE ─────────────────────────────────────── */
let _tfaEnabled = false;
let _otpTimer   = null;

/* Tab switching */
function switchTab(name, btn) {
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    btn.classList.add('active');
}

/* Load profile */
async function loadProfile() {
    try {
        const res  = await apiFetch('/api/v1/employee/profile');
        if (!res) return;
        const data = await res.json();
        const emp  = data.employee || data;

        const name = emp.name || '—';
        document.getElementById('pr-avatar').textContent = name.charAt(0).toUpperCase();
        document.getElementById('pr-name').textContent   = name;
        document.getElementById('pr-role').textContent   = (emp.role || '—').replace(/_/g,' ').toUpperCase();
        document.getElementById('pr-email').textContent  = emp.email  || '—';
        document.getElementById('pr-mobile').textContent = emp.mobile || '—';
        document.getElementById('pr-since').textContent  = emp.created_at ? new Date(emp.created_at).toLocaleDateString('en-IN', {day:'2-digit',month:'short',year:'numeric'}) : '—';

        document.getElementById('f-name').value  = emp.name   || '';
        document.getElementById('f-email').value = emp.email  || '';
        document.getElementById('f-mobile').value= emp.mobile || '';
        document.getElementById('f-empid').value = emp.id     || '';
        document.getElementById('f-dob').value   = emp.dob    || '';
        document.getElementById('f-city').value  = emp.city   || '';
        if (emp.state) document.getElementById('f-state').value = emp.state;
        document.getElementById('f-pan').value   = (emp.pan  || '').toUpperCase();
        document.getElementById('tfa-mobile').value = emp.mobile || '';

        _tfaEnabled = !!emp.two_factor_enabled;
        updateTfaBadge();
        loadPermissions(emp.permissions || []);
        loadActivity();
        loadSessions();

        document.getElementById('qs-login').textContent = emp.last_login ? fmtAgo(emp.last_login) : 'Never';
    } catch(e) {
        console.error('Profile load error:', e);
    }
}

function updateTfaBadge() {
    const on = _tfaEnabled;
    ['pr-2fa-badge','tfa-badge-main'].forEach(id => {
        const el = document.getElementById(id);
        el.textContent  = on ? 'Active' : 'Not Active';
        el.className    = 'tfa-status ' + (on ? 'on' : 'off');
    });
    document.getElementById('tfa-setup-btn').style.display  = on ? 'none'  : '';
    document.getElementById('tfa-disable-btn').style.display = on ? '' : 'none';
}

/* Save personal info */
async function savePersonalInfo() {
    const alertEl = document.getElementById('personal-alert');
    alertEl.style.display = 'none';
    const body = {
        name:  document.getElementById('f-name').value.trim(),
        dob:   document.getElementById('f-dob').value,
        city:  document.getElementById('f-city').value.trim(),
        state: document.getElementById('f-state').value,
        pan:   document.getElementById('f-pan').value.trim().toUpperCase(),
    };
    try {
        const res  = await apiFetch('/api/v1/employee/profile', {method:'PUT', body:JSON.stringify(body)});
        const data = await res.json();
        if (res.ok) {
            alertEl.style.cssText = 'display:flex;background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:14px';
            alertEl.textContent = '✓ Profile updated successfully.';
        } else {
            alertEl.style.cssText = 'display:flex;background:#fff1f2;border:1px solid #fecdd3;color:#be123c;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:14px';
            alertEl.textContent = data.message || 'Update failed. Please try again.';
        }
    } catch(e) { alertEl.textContent = 'Network error.'; alertEl.style.display='flex'; }
}

/* Change password */
async function changePassword() {
    const alertEl = document.getElementById('pwd-alert');
    const curr    = document.getElementById('pwd-current').value;
    const newPwd  = document.getElementById('pwd-new').value;
    const conf    = document.getElementById('pwd-confirm').value;
    alertEl.style.display = 'none';

    if (!curr || !newPwd || !conf) { showPwdAlert('error', 'All fields are required.'); return; }
    if (newPwd.length < 8)         { showPwdAlert('error', 'New password must be at least 8 characters.'); return; }
    if (newPwd !== conf)           { showPwdAlert('error', 'New passwords do not match.'); return; }

    try {
        const res  = await apiFetch('/api/v1/employee/auth/password', {method:'PUT', body:JSON.stringify({current_password:curr, password:newPwd, password_confirmation:conf})});
        const data = await res.json();
        if (res.ok) {
            showPwdAlert('success', '✓ Password changed successfully.');
            document.getElementById('pwd-current').value = '';
            document.getElementById('pwd-new').value = '';
            document.getElementById('pwd-confirm').value = '';
            document.getElementById('pwd-bar').style.width = '0';
            document.getElementById('pwd-label').textContent = 'Enter a password';
        } else {
            showPwdAlert('error', data.message || 'Password change failed.');
        }
    } catch(e) { showPwdAlert('error', 'Network error.'); }
}
function showPwdAlert(type, msg) {
    const el = document.getElementById('pwd-alert');
    el.style.cssText = 'display:flex;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:14px;' +
        (type==='success' ? 'background:#f0fdf4;border:1px solid #bbf7d0;color:#166534' : 'background:#fff1f2;border:1px solid #fecdd3;color:#be123c');
    el.textContent = msg;
}

/* Password strength */
function checkPwdStrength(val) {
    const bar = document.getElementById('pwd-bar');
    const lbl = document.getElementById('pwd-label');
    let score = 0;
    if (val.length >= 8)  score++;
    if (val.length >= 12) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^a-zA-Z0-9]/.test(val)) score++;

    const levels = [
        {pct:'0%',   color:'',          label:'Enter a password',    style:'color:var(--text-muted)'},
        {pct:'25%',  color:'#ef4444',   label:'Weak',                style:'color:#ef4444'},
        {pct:'50%',  color:'#f97316',   label:'Fair',                style:'color:#f97316'},
        {pct:'75%',  color:'#f59e0b',   label:'Good',                style:'color:#f59e0b'},
        {pct:'90%',  color:'#10b981',   label:'Strong',              style:'color:#10b981'},
        {pct:'100%', color:'#059669',   label:'Very Strong ✓',       style:'color:#059669'},
    ];
    const lvl = val.length === 0 ? levels[0] : levels[Math.min(score, 5)];
    bar.style.width      = lvl.pct;
    bar.style.background = lvl.color;
    lbl.textContent      = lvl.label;
    lbl.style.cssText    = 'font-size:11px;margin-top:4px;' + lvl.style;
}

/* Password toggle */
function togglePwdVis(id, btn) {
    const inp = document.getElementById(id);
    inp.type  = inp.type === 'password' ? 'text' : 'password';
}

/* 2FA */
function startTfaSetup() {
    document.getElementById('tfa-setup-flow').style.display = 'block';
    document.getElementById('tfa-step1').style.display = '';
    document.getElementById('tfa-step2').style.display = 'none';
    document.getElementById('tfa-step3').style.display = 'none';
}
function cancelTfaSetup() {
    document.getElementById('tfa-setup-flow').style.display = 'none';
    clearInterval(_otpTimer);
}

async function sendTfaOtp() {
    const mobile = document.getElementById('tfa-mobile').value.trim();
    if (!/^\d{10}$/.test(mobile)) { alert('Enter a valid 10-digit mobile number.'); return; }
    document.getElementById('tfa-otp-sending').style.display = '';
    try {
        await apiFetch('/api/v1/employee/2fa/send-otp', {method:'POST', body:JSON.stringify({mobile})});
        document.getElementById('tfa-mobile-display').textContent = mobile;
        document.getElementById('tfa-step1').style.display = 'none';
        document.getElementById('tfa-step2').style.display = '';
        startOtpTimer();
        document.getElementById('otp-boxes').querySelectorAll('.otp-box')[0].focus();
    } catch(e) { alert('Failed to send OTP. Try again.'); }
    document.getElementById('tfa-otp-sending').style.display = 'none';
}

function startOtpTimer() {
    let sec = 60;
    clearInterval(_otpTimer);
    document.getElementById('resend-btn').style.display = 'none';
    _otpTimer = setInterval(() => {
        sec--;
        document.getElementById('otp-timer').textContent = sec;
        if (sec <= 0) {
            clearInterval(_otpTimer);
            document.getElementById('resend-btn').style.display = '';
            document.getElementById('otp-timer').textContent = '0';
        }
    }, 1000);
}

async function verifyTfaOtp() {
    const boxes = document.querySelectorAll('#otp-boxes .otp-box');
    const otp   = Array.from(boxes).map(b => b.value).join('');
    const errEl = document.getElementById('tfa-verify-error');
    if (otp.length < 6) { errEl.textContent = 'Please enter all 6 digits.'; errEl.style.display=''; return; }
    errEl.style.display = 'none';
    try {
        const mobile = document.getElementById('tfa-mobile').value.trim();
        const res    = await apiFetch('/api/v1/employee/2fa/verify', {method:'POST', body:JSON.stringify({mobile, otp})});
        const data   = await res.json();
        if (res.ok) {
            document.getElementById('tfa-step2').style.display = 'none';
            document.getElementById('tfa-step3').style.display = '';
            clearInterval(_otpTimer);
            _tfaEnabled = true;
            updateTfaBadge();
            // Show backup codes
            const codes = data.backup_codes || ['XXXX-XXXX','YYYY-YYYY','ZZZZ-ZZZZ','AAAA-BBBB','CCCC-DDDD','EEEE-FFFF','GGGG-HHHH','IIII-JJJJ'];
            document.getElementById('backup-codes-grid').innerHTML = codes.map(c => `<div class="backup-code">${c}</div>`).join('');
        } else {
            errEl.textContent = data.message || 'Invalid OTP. Please try again.';
            errEl.style.display = '';
        }
    } catch(e) { errEl.textContent = 'Network error.'; errEl.style.display = ''; }
}

async function disableTfa() {
    if (!confirm('Are you sure you want to disable 2FA? Your account will be less secure.')) return;
    try {
        const res = await apiFetch('/api/v1/employee/2fa/disable', {method:'DELETE'});
        if (res.ok) { _tfaEnabled = false; updateTfaBadge(); document.getElementById('tfa-setup-flow').style.display='none'; }
        else { const d = await res.json(); alert(d.message || 'Failed to disable 2FA.'); }
    } catch(e) { alert('Network error.'); }
}

/* OTP box auto-advance */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.otp-box').forEach((box, i, boxes) => {
        box.addEventListener('input', (e) => {
            const val = e.target.value.replace(/\D/g,'');
            e.target.value = val;
            if (val && i < boxes.length - 1) boxes[i+1].focus();
        });
        box.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !e.target.value && i > 0) boxes[i-1].focus();
        });
    });
});

/* Permissions */
const ALL_PERMS = [
    {key:'recharge',    name:'Recharge Access',     desc:'Process recharge transactions'},
    {key:'reports',     name:'Report View',          desc:'View transaction reports'},
    {key:'users',       name:'User Management',      desc:'Manage customer accounts'},
    {key:'complaints',  name:'Complaint Handling',   desc:'Resolve customer complaints'},
    {key:'wallets',     name:'Wallet Management',    desc:'Manage user wallets'},
    {key:'operators',   name:'Operator Access',      desc:'View/manage operators'},
    {key:'commission',  name:'Commission View',      desc:'View commission data'},
    {key:'exports',     name:'Data Export',          desc:'Export reports and data'},
    {key:'api_access',  name:'API Access',           desc:'Use platform API'},
    {key:'settings',    name:'Settings Access',      desc:'Modify system settings'},
    {key:'activity',    name:'Activity Logs',        desc:'View audit/activity logs'},
    {key:'api_docs',    name:'API Documentation',    desc:'View API docs & integration'},
];
function loadPermissions(granted) {
    const grid = document.getElementById('perm-grid');
    grid.innerHTML = ALL_PERMS.map(p => {
        const has = granted.includes(p.key);
        return `<div class="perm-card">
            <div class="perm-card-header">
                <span class="perm-name">${p.name}</span>
                <span class="perm-badge ${has?'granted':'denied'}">${has?'Granted':'Denied'}</span>
            </div>
            <div class="perm-desc">${p.desc}</div>
        </div>`;
    }).join('');
}

/* Activity feed */
async function loadActivity() {
    try {
        const res  = await apiFetch('/api/v1/employee/activity?limit=10');
        if (!res) return;
        const data = await res.json();
        const feed = document.getElementById('activity-feed');
        const items = data.data || data.activities || [];
        const colors = {login:'#10b981', recharge:'#2563eb', logout:'#f59e0b', error:'#ef4444'};
        feed.innerHTML = items.length ? items.map(a => `
            <div class="activity-item">
                <div class="activity-dot" style="background:${colors[a.type]||'#94a3b8'}"></div>
                <div>
                    <div class="activity-text">${a.description||a.action||'Activity'}</div>
                    <div class="activity-time">${fmtAgo(a.created_at)}</div>
                </div>
            </div>`).join('') : '<div style="text-align:center;color:var(--text-muted);font-size:13px;padding:16px">No recent activity</div>';
    } catch(e) {
        document.getElementById('activity-feed').innerHTML = '<div style="text-align:center;color:var(--text-muted);font-size:13px;padding:16px">Could not load activity</div>';
    }
}

/* Sessions */
async function loadSessions() {
    try {
        const res  = await apiFetch('/api/v1/employee/sessions');
        if (!res) return;
        const data = await res.json();
        const sessions = data.data || data.sessions || [];
        const tbody = document.getElementById('sessions-table');
        tbody.innerHTML = sessions.length ? sessions.map(s => `
            <tr>
                <td>${s.device||'Unknown'}</td>
                <td>${s.browser||'—'}</td>
                <td style="font-family:monospace;font-size:12px">${s.ip||'—'}</td>
                <td>${fmtAgo(s.last_active)}</td>
                <td><button class="btn btn-outline btn-sm" onclick="revokeSession('${s.id}')" style="color:var(--accent-red);border-color:var(--accent-red)">Revoke</button></td>
            </tr>`).join('') : '<tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:16px">No active sessions found</td></tr>';
    } catch(e) {
        document.getElementById('sessions-table').innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:16px">Could not load sessions</td></tr>';
    }
}

async function revokeSession(id) {
    if (!confirm('Revoke this session?')) return;
    await apiFetch('/api/v1/employee/sessions/' + id, {method:'DELETE'});
    loadSessions();
}

/* Login history */
async function loadLoginHistory() {
    const el = document.getElementById('login-history');
    try {
        const res  = await apiFetch('/api/v1/employee/login-history?limit=5');
        if (!res) return;
        const data = await res.json();
        const list = data.data || data.logins || [];
        el.innerHTML = list.length ? list.map(l => `
            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--border)">
                <div>
                    <div style="font-size:12.5px;color:var(--text-primary)">${l.ip||'—'}</div>
                    <div style="font-size:11px;color:var(--text-muted)">${fmtAgo(l.created_at)}</div>
                </div>
                <span style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:20px;${l.success?'background:#d1fae5;color:#059669':'background:#fee2e2;color:#dc2626'}">${l.success?'Success':'Failed'}</span>
            </div>`).join('') : '<div style="text-align:center;color:var(--text-muted);font-size:13px;padding:16px">No login history</div>';
    } catch(e) { el.innerHTML = '<div style="text-align:center;color:var(--text-muted);font-size:13px;padding:16px">Could not load history</div>'; }
}

/* Preferences */
async function savePrefs() {
    const alertEl = document.getElementById('prefs-alert');
    const body = {
        notifications: {
            email:   document.getElementById('pref-email').checked,
            sms:     document.getElementById('pref-sms').checked,
            browser: document.getElementById('pref-browser').checked,
            recharge:document.getElementById('pref-recharge').checked,
        },
        language: document.getElementById('pref-lang').value,
        timezone: document.getElementById('pref-tz').value,
    };
    try {
        const res  = await apiFetch('/api/v1/employee/preferences', {method:'PUT', body:JSON.stringify(body)});
        const data = await res.json();
        alertEl.style.cssText = res.ok
            ? 'display:flex;background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:14px'
            : 'display:flex;background:#fff1f2;border:1px solid #fecdd3;color:#be123c;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:14px';
        alertEl.textContent = res.ok ? '✓ Preferences saved.' : (data.message||'Save failed.');
        setTimeout(() => alertEl.style.display='none', 3000);
    } catch(e) { alertEl.textContent='Network error.'; alertEl.style.display='flex'; }
}

/* Avatar preview */
function previewAvatar(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        const av = document.getElementById('pr-avatar');
        av.style.backgroundImage = `url(${e.target.result})`;
        av.style.backgroundSize  = 'cover';
        av.style.fontSize = '0';
    };
    reader.readAsDataURL(input.files[0]);
}

/* Boot */
document.addEventListener('DOMContentLoaded', () => {
    requireAuth();
    bootSidebarUser();
    loadProfile();
    loadLoginHistory();
});
</script>
@endpush
