<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — RechargeHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Inter',system-ui,sans-serif;background:#040d21;color:#f1f5f9;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px 16px;position:relative;overflow-x:hidden}
        body::before{content:'';position:fixed;inset:0;background:radial-gradient(ellipse 80% 60% at 50% 0%,rgba(16,185,129,.14) 0%,transparent 70%),radial-gradient(ellipse 50% 50% at 10% 80%,rgba(99,102,241,.1) 0%,transparent 60%);pointer-events:none}
        body::after{content:'';position:fixed;inset:0;background-image:radial-gradient(circle,rgba(255,255,255,.05) 1px,transparent 1px);background-size:36px 36px;mask-image:radial-gradient(ellipse 70% 70% at 50% 50%,black 30%,transparent 80%);pointer-events:none}

        /* Card */
        .card{position:relative;z-index:1;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);border-radius:20px;padding:40px;width:100%;max-width:500px;box-shadow:0 24px 80px rgba(0,0,0,.5);backdrop-filter:blur(20px)}
        .card::before{content:'';position:absolute;top:0;left:40px;right:40px;height:1px;background:linear-gradient(90deg,transparent,rgba(16,185,129,.5),transparent)}

        /* Brand */
        .brand{display:flex;align-items:center;gap:10px;justify-content:center;margin-bottom:28px;text-decoration:none}
        .brand-icon{width:40px;height:40px;background:linear-gradient(135deg,#10b981,#2563eb);border-radius:11px;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 20px rgba(16,185,129,.35)}
        .brand-icon svg{width:22px;height:22px;color:#fff}
        .brand-name{font-size:18px;font-weight:800}
        .brand-name span{background:linear-gradient(90deg,#34d399,#60a5fa);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}

        h2{font-size:22px;font-weight:800;text-align:center;color:#fff;margin-bottom:5px;letter-spacing:-.5px}
        .sub{font-size:13.5px;color:#64748b;text-align:center;margin-bottom:26px}

        /* Alert */
        .alert{border-radius:10px;padding:11px 14px;font-size:13px;margin-bottom:16px;display:none;align-items:flex-start;gap:8px;line-height:1.5}
        .alert.show{display:flex}
        .alert svg{flex-shrink:0;width:15px;height:15px;margin-top:1px}
        .alert-error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#fca5a5}
        .alert-success{background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.25);color:#6ee7b7}

        /* Grid */
        .grid2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
        @media(max-width:520px){.grid2{grid-template-columns:1fr}}

        /* Field */
        .field{margin-bottom:16px}
        .field-label{display:flex;align-items:center;gap:4px;font-size:12px;font-weight:600;color:#94a3b8;margin-bottom:6px;letter-spacing:.3px}
        .req{color:#ef4444;font-size:13px}
        .input-wrap{position:relative}
        .input-icon{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#475569;pointer-events:none}
        .input-icon svg{width:15px;height:15px;display:block}
        input,select{width:100%;background:rgba(255,255,255,.05);border:1.5px solid rgba(255,255,255,.1);border-radius:10px;padding:11px 14px 11px 38px;font-size:14px;color:#f1f5f9;font-family:inherit;transition:border-color .2s,box-shadow .2s;outline:none;appearance:none}
        input::placeholder{color:#475569}
        input:focus,select:focus{border-color:rgba(16,185,129,.5);box-shadow:0 0 0 3px rgba(16,185,129,.1)}
        select{cursor:pointer}
        select option{background:#0f172a;color:#f1f5f9}
        .no-icon{padding-left:14px}

        /* Field states */
        input.is-invalid,select.is-invalid{border-color:rgba(239,68,68,.6)!important;box-shadow:0 0 0 3px rgba(239,68,68,.1)!important}
        input.is-valid,select.is-valid{border-color:rgba(16,185,129,.5)!important;box-shadow:0 0 0 3px rgba(16,185,129,.08)!important}

        /* Field error */
        .field-err{display:none;align-items:center;gap:5px;margin-top:5px;font-size:11.5px;color:#fca5a5}
        .field-err.show{display:flex}
        .field-err svg{width:12px;height:12px;flex-shrink:0}

        /* Password toggle */
        .pwd-toggle{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#475569;padding:4px;display:flex;align-items:center;border-radius:5px}
        .pwd-toggle:hover{color:#94a3b8}
        .pwd-toggle svg{width:16px;height:16px}

        /* Hint */
        .hint{font-size:11px;color:#334155;margin-top:4px}

        /* Divider */
        .section-divider{display:flex;align-items:center;gap:10px;margin:6px 0 18px}
        .section-divider::before,.section-divider::after{content:'';flex:1;height:1px;background:rgba(255,255,255,.07)}
        .section-divider span{font-size:11px;color:#334155;font-weight:500;text-transform:uppercase;letter-spacing:.5px;white-space:nowrap}

        /* Submit */
        .btn{width:100%;background:linear-gradient(135deg,#059669,#10b981);color:#fff;border:none;border-radius:10px;padding:13px;font-size:14px;font-weight:700;cursor:pointer;font-family:inherit;transition:all .2s;box-shadow:0 6px 20px rgba(16,185,129,.3);margin-top:4px;display:flex;align-items:center;justify-content:center;gap:8px}
        .btn:hover:not(:disabled){transform:translateY(-1px);box-shadow:0 8px 28px rgba(16,185,129,.45)}
        .btn:disabled{opacity:.6;cursor:not-allowed}
        .spinner{width:16px;height:16px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite;display:none;flex-shrink:0}
        @keyframes spin{to{transform:rotate(360deg)}}

        /* Password strength */
        .pwd-strength{margin-top:6px}
        .strength-bar{height:3px;border-radius:2px;background:rgba(255,255,255,.07);overflow:hidden}
        .strength-fill{height:100%;border-radius:2px;transition:width .3s,background .3s;width:0}
        .strength-label{font-size:11px;margin-top:4px;color:#475569}

        .link-row{text-align:center;font-size:13px;color:#64748b;margin-top:18px}
        .link-row a{color:#34d399;text-decoration:none;font-weight:600}
        .link-row a:hover{color:#6ee7b7}

        .back-link{position:fixed;top:24px;left:24px;z-index:10;display:flex;align-items:center;gap:6px;color:#475569;font-size:13px;font-weight:500;text-decoration:none;transition:color .15s}
        .back-link:hover{color:#94a3b8}
        .back-link svg{width:16px;height:16px}
    </style>
</head>
<body>

<a href="/" class="back-link">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    Back to Home
</a>

<div class="card">
    <a href="/" class="brand">
        <div class="brand-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <span class="brand-name">Recharge<span>Hub</span></span>
    </a>

    <h2>Create your account</h2>
    <p class="sub">Register to recharge mobile, DTH & more instantly</p>

    <!-- Top alerts -->
    <div class="alert alert-error" id="alert-error">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span id="alert-error-msg"></span>
    </div>
    <div class="alert alert-success" id="alert-success">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span id="alert-success-msg"></span>
    </div>

    <form id="reg-form" novalidate autocomplete="off">

        <!-- Name + Mobile -->
        <div class="grid2">
            <div class="field">
                <div class="field-label">Full Name <span class="req">*</span></div>
                <div class="input-wrap">
                    <span class="input-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></span>
                    <input type="text" id="name" placeholder="Rahul Sharma" maxlength="100" autocomplete="name">
                </div>
                <div class="field-err" id="name-err">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span id="name-err-msg">Name is required.</span>
                </div>
            </div>
            <div class="field">
                <div class="field-label">Mobile Number <span class="req">*</span></div>
                <div class="input-wrap">
                    <span class="input-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg></span>
                    <input type="tel" id="mobile" placeholder="9876543210" maxlength="10" autocomplete="tel">
                </div>
                <div class="field-err" id="mobile-err">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span id="mobile-err-msg">Enter a valid 10-digit mobile number.</span>
                </div>
            </div>
        </div>

        <!-- Email -->
        <div class="field">
            <div class="field-label">Email Address <span class="req">*</span></div>
            <div class="input-wrap">
                <span class="input-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg></span>
                <input type="email" id="email" placeholder="you@example.com" maxlength="150" autocomplete="email">
            </div>
            <div class="field-err" id="email-err">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span id="email-err-msg">Enter a valid email address.</span>
            </div>
        </div>

        <!-- Account Type -->
        <div class="field">
            <div class="field-label">Account Type <span class="req">*</span></div>
            <div class="input-wrap">
                <span class="input-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg></span>
                <select id="role">
                    <option value="retailer">Retailer — I sell recharges to customers</option>
                    <option value="distributor">Distributor — I manage a network of retailers</option>
                    <option value="api_user">API User / Developer — I integrate via API</option>
                </select>
            </div>
        </div>

        <div class="section-divider"><span>Set Password</span></div>

        <!-- Password + Confirm -->
        <div class="grid2">
            <div class="field">
                <div class="field-label">Password <span class="req">*</span></div>
                <div class="input-wrap">
                    <span class="input-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></span>
                    <input type="password" id="password" placeholder="Min. 8 characters" maxlength="72" autocomplete="new-password" style="padding-right:40px">
                    <button type="button" class="pwd-toggle" onclick="togglePwd('password','eye1')">
                        <svg id="eye1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
                <div class="pwd-strength">
                    <div class="strength-bar"><div class="strength-fill" id="strength-fill"></div></div>
                    <div class="strength-label" id="strength-label"></div>
                </div>
                <div class="field-err" id="password-err">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span id="password-err-msg">Password must be at least 8 characters.</span>
                </div>
            </div>
            <div class="field">
                <div class="field-label">Confirm Password <span class="req">*</span></div>
                <div class="input-wrap">
                    <span class="input-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg></span>
                    <input type="password" id="password_confirmation" placeholder="Repeat password" maxlength="72" autocomplete="new-password" style="padding-right:40px">
                    <button type="button" class="pwd-toggle" onclick="togglePwd('password_confirmation','eye2')">
                        <svg id="eye2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
                <div class="field-err" id="confirm-err">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span id="confirm-err-msg">Passwords do not match.</span>
                </div>
            </div>
        </div>

        <!-- Referral Code (optional) -->
        <div class="field">
            <div class="field-label">Referral Code <span style="color:#334155;font-weight:400">(optional)</span></div>
            <div class="input-wrap">
                <span class="input-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg></span>
                <input type="text" id="referral_code" placeholder="Enter referral code" maxlength="20" autocomplete="off">
            </div>
        </div>

        <!-- Submit -->
        <button type="submit" class="btn" id="reg-btn">
            <div class="spinner" id="spinner"></div>
            <span id="btn-text">Create Account &amp; Start Recharging</span>
        </button>

    </form>

    <div class="link-row">Already have an account? <a href="/user/login">Sign in</a></div>
</div>

<script>
/* ── Redirect if already logged in ────────────────────────── */
if (localStorage.getItem('user_token')) window.location.href = '/user/dashboard';

/* ── Helpers ───────────────────────────────────────────────── */
const $  = id => document.getElementById(id);
const showFieldErr = (errId, msgId, msg) => { if(msgId) $(msgId).textContent = msg; $(errId).classList.add('show'); };
const hideFieldErr = id => $(id).classList.remove('show');
const markInvalid  = id => { $(id).classList.add('is-invalid'); $(id).classList.remove('is-valid'); };
const markValid    = id => { $(id).classList.remove('is-invalid'); $(id).classList.add('is-valid'); };
const markReset    = id => { $(id).classList.remove('is-invalid','is-valid'); };

/* ── Top alerts ─────────────────────────────────────────────── */
function showError(msg)   { $('alert-error-msg').textContent = msg;   $('alert-error').classList.add('show');   $('alert-success').classList.remove('show'); }
function showSuccess(msg) { $('alert-success-msg').textContent = msg; $('alert-success').classList.add('show'); $('alert-error').classList.remove('show'); }
function clearAlerts()    { $('alert-error').classList.remove('show'); $('alert-success').classList.remove('show'); }

/* ── Password visibility toggle ─────────────────────────────── */
const EYE_SHOW = `<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>`;
const EYE_HIDE = `<path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>`;
function togglePwd(inputId, iconId) {
    const inp = $(inputId);
    const isText = inp.type === 'text';
    inp.type = isText ? 'password' : 'text';
    $(iconId).innerHTML = isText ? EYE_SHOW : EYE_HIDE;
}

/* ── Password strength ──────────────────────────────────────── */
function checkStrength(pwd) {
    let score = 0;
    if (pwd.length >= 8)  score++;
    if (pwd.length >= 12) score++;
    if (/[A-Z]/.test(pwd)) score++;
    if (/[0-9]/.test(pwd)) score++;
    if (/[^A-Za-z0-9]/.test(pwd)) score++;
    const levels = [
        { w:'0%',   bg:'transparent', label:'' },
        { w:'25%',  bg:'#ef4444',     label:'Weak' },
        { w:'50%',  bg:'#f59e0b',     label:'Fair' },
        { w:'75%',  bg:'#3b82f6',     label:'Good' },
        { w:'100%', bg:'#10b981',     label:'Strong' },
    ];
    const lvl = levels[Math.min(score, 4)];
    $('strength-fill').style.width      = lvl.w;
    $('strength-fill').style.background = lvl.bg;
    $('strength-label').textContent     = lvl.label;
    $('strength-label').style.color     = lvl.bg;
}

/* ── Client-side validators ─────────────────────────────────── */
function validateName(v)    { if(!v) return 'Full name is required.'; if(v.length < 2) return 'Name must be at least 2 characters.'; if(v.length > 100) return 'Name must not exceed 100 characters.'; return null; }
function validateMobile(v)  { if(!v) return 'Mobile number is required.'; if(!/^\d{10}$/.test(v)) return 'Must be exactly 10 digits.'; return null; }
function validateEmail(v)   { if(!v) return 'Email address is required.'; if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) return 'Enter a valid email address.'; if(v.length > 150) return 'Email must not exceed 150 characters.'; return null; }
function validatePassword(v){ if(!v) return 'Password is required.'; if(v.length < 8) return 'Password must be at least 8 characters.'; if(v.length > 72) return 'Password must not exceed 72 characters.'; return null; }
function validateConfirm(v) { if(!v) return 'Please confirm your password.'; if(v !== $('password').value) return 'Passwords do not match.'; return null; }

/* ── Real-time blur validation ──────────────────────────────── */
[
    ['name',                 'name-err',     'name-err-msg',     validateName],
    ['mobile',               'mobile-err',   'mobile-err-msg',   validateMobile],
    ['email',                'email-err',    'email-err-msg',    validateEmail],
    ['password',             'password-err', 'password-err-msg', validatePassword],
    ['password_confirmation','confirm-err',  'confirm-err-msg',  validateConfirm],
].forEach(([inputId, errId, msgId, fn]) => {
    $(inputId).addEventListener('blur', function() {
        const err = fn(this.value.trim ? this.value.trim() : this.value);
        if (err) { markInvalid(inputId); showFieldErr(errId, msgId, err); }
        else if (this.value) { markValid(inputId); hideFieldErr(errId); }
        else { markReset(inputId); hideFieldErr(errId); }
    });
    $(inputId).addEventListener('input', function() {
        if (this.classList.contains('is-invalid')) {
            const err = fn(this.value.trim ? this.value.trim() : this.value);
            if (!err) { markValid(inputId); hideFieldErr(errId); }
        }
        if (inputId === 'password') checkStrength(this.value);
    });
});

/* ── Loading state ──────────────────────────────────────────── */
function setLoading(on) {
    $('reg-btn').disabled          = on;
    $('spinner').style.display     = on ? 'block' : 'none';
    $('btn-text').textContent      = on ? 'Creating account…' : 'Create Account & Start Recharging';
}

/* ── Mobile — digits only ────────────────────────────────────── */
$('mobile').addEventListener('input', function() {
    this.value = this.value.replace(/\D/g, '').slice(0, 10);
});

/* ── Form submit ─────────────────────────────────────────────── */
$('reg-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    clearAlerts();

    const name     = $('name').value.trim();
    const mobile   = $('mobile').value.trim();
    const email    = $('email').value.trim();
    const role     = $('role').value;
    const password = $('password').value;
    const confirm  = $('password_confirmation').value;
    const referral = $('referral_code').value.trim();

    /* Run all validators */
    const checks = [
        ['name',                 'name-err',     'name-err-msg',     validateName(name)],
        ['mobile',               'mobile-err',   'mobile-err-msg',   validateMobile(mobile)],
        ['email',                'email-err',    'email-err-msg',    validateEmail(email)],
        ['password',             'password-err', 'password-err-msg', validatePassword(password)],
        ['password_confirmation','confirm-err',  'confirm-err-msg',  validateConfirm(confirm)],
    ];

    let hasError = false;
    checks.forEach(([inputId, errId, msgId, err]) => {
        if (err) { markInvalid(inputId); showFieldErr(errId, msgId, err); hasError = true; }
        else      { markValid(inputId);   hideFieldErr(errId); }
    });
    if (hasError) return;

    setLoading(true);

    try {
        const body = { name, mobile, email, role, password, password_confirmation: confirm, device_name: 'web' };
        if (referral) body.referral_code = referral;

        const res  = await fetch('/api/v1/auth/register', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body:    JSON.stringify(body),
        });
        const data = await res.json();

        if (res.ok) {
            localStorage.setItem('user_token', data.token);
            localStorage.setItem('user_data',  JSON.stringify(data.user));
            showSuccess('Account created! Redirecting to your dashboard…');
            setTimeout(() => window.location.href = '/user/dashboard', 1200);
            return;
        }

        /* Map server field errors back to inline fields */
        if (data.errors) {
            const fieldMap = { name:'name', email:'email', mobile:'mobile', password:'password', password_confirmation:'confirm' };
            let firstMsg = null;
            Object.entries(data.errors).forEach(([field, msgs]) => {
                const msg = Array.isArray(msgs) ? msgs[0] : msgs;
                if (!firstMsg) firstMsg = msg;
                const key = fieldMap[field];
                if (key) {
                    const inputId = field === 'password_confirmation' ? 'password_confirmation' : field;
                    const errId   = key + '-err';
                    const msgId   = key + '-err-msg';
                    markInvalid(inputId);
                    showFieldErr(errId, msgId, msg);
                }
            });
            showError(firstMsg || data.message || 'Please fix the errors below.');
        } else {
            showError(data.message || 'Registration failed. Please try again.');
        }
    } catch {
        showError('Network error. Please check your connection and try again.');
    } finally {
        setLoading(false);
    }
});
</script>
</body>
</html>
