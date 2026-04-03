<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>API User Registration — RechargeHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Inter',system-ui,sans-serif;background:#040d21;color:#f1f5f9;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px 16px;position:relative}
        body::before{content:'';position:fixed;inset:0;background:radial-gradient(ellipse 80% 60% at 50% 0%,rgba(99,102,241,.16) 0%,transparent 70%),radial-gradient(ellipse 50% 50% at 90% 90%,rgba(139,92,246,.1) 0%,transparent 60%);pointer-events:none}
        body::after{content:'';position:fixed;inset:0;background-image:radial-gradient(circle,rgba(255,255,255,.04) 1px,transparent 1px);background-size:36px 36px;pointer-events:none}

        .card{position:relative;z-index:1;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);border-radius:20px;padding:38px 40px;width:100%;max-width:540px;box-shadow:0 24px 80px rgba(0,0,0,.5);backdrop-filter:blur(20px)}
        .card::before{content:'';position:absolute;top:0;left:40px;right:40px;height:1px;background:linear-gradient(90deg,transparent,rgba(99,102,241,.5),transparent)}

        /* Brand */
        .brand{display:flex;align-items:center;gap:10px;justify-content:center;margin-bottom:10px;text-decoration:none}
        .brand-icon{width:38px;height:38px;background:linear-gradient(135deg,#6366f1,#7c3aed);border-radius:10px;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 20px rgba(99,102,241,.4)}
        .brand-icon svg{width:20px;height:20px;color:#fff}
        .brand-name{font-size:17px;font-weight:800}
        .brand-name span{background:linear-gradient(90deg,#818cf8,#a78bfa);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}

        /* Badge */
        .api-badge{display:inline-flex;align-items:center;gap:6px;background:rgba(99,102,241,.12);border:1px solid rgba(99,102,241,.25);border-radius:20px;padding:4px 12px;font-size:11.5px;font-weight:600;color:#818cf8;margin:0 auto 16px;display:flex;justify-content:center;width:fit-content;margin:0 auto 16px}
        .api-badge svg{width:13px;height:13px}

        h2{font-size:20px;font-weight:800;text-align:center;color:#fff;margin-bottom:4px}
        .sub{font-size:13px;color:#64748b;text-align:center;margin-bottom:22px}

        /* Feature highlights */
        .features{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:22px}
        .feat{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:10px;padding:10px 12px;display:flex;align-items:center;gap:8px;font-size:12px;color:#94a3b8}
        .feat svg{width:14px;height:14px;color:#818cf8;flex-shrink:0}

        /* Alert */
        .alert{border-radius:10px;padding:11px 14px;font-size:13px;margin-bottom:14px;display:none;gap:8px;line-height:1.5;align-items:flex-start}
        .alert.show{display:flex}
        .alert-error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#fca5a5}
        .alert-success{background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.25);color:#6ee7b7}
        .alert svg{flex-shrink:0;width:15px;height:15px;margin-top:1px}

        /* Grid */
        .grid2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
        @media(max-width:520px){.grid2{grid-template-columns:1fr}}

        /* Form */
        .field{margin-bottom:14px}
        .field-label{font-size:12px;font-weight:600;color:#94a3b8;margin-bottom:5px;display:flex;align-items:center;gap:4px}
        .req{color:#ef4444}
        .opt{color:#334155;font-weight:400}
        .input-wrap{position:relative}
        .f-icon{position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#475569;pointer-events:none;display:flex}
        .f-icon svg{width:14px;height:14px}
        input,select,textarea{width:100%;background:rgba(255,255,255,.05);border:1.5px solid rgba(255,255,255,.1);border-radius:9px;padding:10px 12px 10px 34px;font-size:13.5px;color:#f1f5f9;font-family:inherit;transition:border-color .2s;outline:none}
        input::placeholder,textarea::placeholder{color:#475569}
        input:focus,select:focus,textarea:focus{border-color:rgba(99,102,241,.5);box-shadow:0 0 0 3px rgba(99,102,241,.1)}
        textarea{resize:vertical;min-height:70px;padding-left:12px}
        select option{background:#0f172a}
        .no-icon{padding-left:12px!important}
        input.err{border-color:rgba(239,68,68,.6)!important;box-shadow:0 0 0 3px rgba(239,68,68,.08)!important}
        input.ok{border-color:rgba(99,102,241,.5)!important}
        .err-msg{display:none;font-size:11.5px;color:#fca5a5;margin-top:4px;align-items:center;gap:4px}
        .err-msg.show{display:flex}
        .err-msg svg{width:11px;height:11px;flex-shrink:0}

        /* Password */
        .pwd-toggle{position:absolute;right:11px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#475569;padding:3px}
        .pwd-toggle svg{width:15px;height:15px}
        .strength-bar{height:3px;background:rgba(255,255,255,.06);border-radius:2px;margin-top:5px;overflow:hidden}
        .strength-fill{height:100%;border-radius:2px;transition:width .3s,background .3s;width:0}
        .strength-lbl{font-size:11px;margin-top:3px;color:#475569}
        .pwd-rules{display:grid;grid-template-columns:1fr 1fr;gap:3px 10px;margin-top:6px}
        .rule{font-size:11px;color:#475569;display:flex;align-items:center;gap:4px;transition:color .2s}
        .rule.pass{color:#818cf8}
        .rule svg{width:11px;height:11px;flex-shrink:0}

        /* Section divider */
        .section-div{display:flex;align-items:center;gap:8px;margin:4px 0 16px}
        .section-div::before,.section-div::after{content:'';flex:1;height:1px;background:rgba(255,255,255,.07)}
        .section-div span{font-size:11px;color:#334155;text-transform:uppercase;letter-spacing:.5px;white-space:nowrap}

        /* File upload */
        .file-zone{border:1.5px dashed rgba(99,102,241,.2);border-radius:9px;padding:18px 16px;text-align:center;cursor:pointer;transition:border-color .2s,background .2s;position:relative}
        .file-zone:hover,.file-zone.dragover{border-color:rgba(99,102,241,.5);background:rgba(99,102,241,.05)}
        .file-zone input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
        .file-zone svg{width:26px;height:26px;color:#334155;margin:0 auto 6px;display:block}
        .file-zone-text{font-size:12px;color:#64748b}
        .file-zone-text b{color:#818cf8}
        .file-name{font-size:11.5px;color:#818cf8;margin-top:5px;font-weight:600}
        .file-hint{font-size:11px;color:#334155;margin-top:2px}

        /* Use case checkbox group */
        .use-cases{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:4px}
        .uc-item{background:rgba(255,255,255,.03);border:1.5px solid rgba(255,255,255,.08);border-radius:8px;padding:8px 10px;cursor:pointer;transition:all .15s;display:flex;align-items:center;gap:8px;font-size:12.5px;color:#94a3b8}
        .uc-item input[type=checkbox]{display:none}
        .uc-item.selected{border-color:rgba(99,102,241,.5);background:rgba(99,102,241,.08);color:#c7d2fe}
        .uc-item .uc-check{width:14px;height:14px;border:1.5px solid #334155;border-radius:4px;flex-shrink:0;display:flex;align-items:center;justify-content:center;transition:all .15s}
        .uc-item.selected .uc-check{background:#6366f1;border-color:#6366f1}
        .uc-item.selected .uc-check svg{display:block}
        .uc-item .uc-check svg{display:none;width:9px;height:9px;color:#fff}

        /* Submit */
        .btn{width:100%;background:linear-gradient(135deg,#4f46e5,#6366f1);color:#fff;border:none;border-radius:9px;padding:12px;font-size:14px;font-weight:700;cursor:pointer;font-family:inherit;transition:all .2s;box-shadow:0 6px 20px rgba(99,102,241,.35);margin-top:4px;display:flex;align-items:center;justify-content:center;gap:8px}
        .btn:hover:not(:disabled){transform:translateY(-1px);box-shadow:0 8px 28px rgba(99,102,241,.5)}
        .btn:disabled{opacity:.6;cursor:not-allowed}
        .spinner{width:15px;height:15px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite;display:none}
        @keyframes spin{to{transform:rotate(360deg)}}

        /* Terms */
        .terms-row{display:flex;align-items:flex-start;gap:10px;margin-bottom:14px;font-size:12.5px;color:#64748b}
        .terms-row input[type=checkbox]{width:15px;height:15px;flex-shrink:0;margin-top:1px;accent-color:#6366f1}
        .terms-row a{color:#818cf8;text-decoration:none}
        .err-msg.terms-err{margin-top:4px}

        .link-row{text-align:center;font-size:13px;color:#64748b;margin-top:16px}
        .link-row a{color:#818cf8;font-weight:600;text-decoration:none}

        .back-btn{display:flex;align-items:center;gap:5px;font-size:12.5px;color:#475569;text-decoration:none;margin-bottom:14px;transition:color .15s}
        .back-btn:hover{color:#94a3b8}
        .back-btn svg{width:14px;height:14px}
    </style>
</head>
<body>
<div class="card">

    <a href="/user/register" class="back-btn">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Back to Regular Registration
    </a>

    <a href="/" class="brand">
        <div class="brand-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
        </div>
        <span class="brand-name">Recharge<span>Hub</span></span>
    </a>

    <div class="api-badge">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
        API User Registration
    </div>

    <h2>Developer / API Access</h2>
    <p class="sub">Create an account to integrate RechargeHub into your platform</p>

    <!-- Feature highlights -->
    <div class="features">
        <div class="feat"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>Recharge API Access</div>
        <div class="feat"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>Wallet Integration</div>
        <div class="feat"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>Secure API Keys</div>
        <div class="feat"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>Transaction Reports</div>
    </div>

    <!-- Alerts -->
    <div class="alert alert-error" id="alert-err">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span id="alert-err-msg"></span>
    </div>
    <div class="alert alert-success" id="alert-ok">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span id="alert-ok-msg"></span>
    </div>

    <form id="form" novalidate enctype="multipart/form-data">

        <div class="section-div"><span>Personal Information</span></div>

        <div class="grid2">
            <!-- Full Name -->
            <div class="field">
                <div class="field-label">Full Name <span class="req">*</span></div>
                <div class="input-wrap">
                    <span class="f-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></span>
                    <input type="text" id="name" placeholder="Your full name" maxlength="100">
                </div>
                <div class="err-msg" id="name-err"><svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg><span id="name-err-t">Required.</span></div>
            </div>
            <!-- Mobile -->
            <div class="field">
                <div class="field-label">Mobile Number <span class="req">*</span></div>
                <div class="input-wrap">
                    <span class="f-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg></span>
                    <input type="tel" id="mobile" placeholder="9876543210" maxlength="10">
                </div>
                <div class="err-msg" id="mobile-err"><svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg><span id="mobile-err-t">10-digit number required.</span></div>
            </div>
        </div>

        <!-- Email -->
        <div class="field">
            <div class="field-label">Email Address <span class="req">*</span></div>
            <div class="input-wrap">
                <span class="f-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg></span>
                <input type="email" id="email" placeholder="developer@company.com" maxlength="150">
            </div>
            <div class="err-msg" id="email-err"><svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg><span id="email-err-t">Valid email required.</span></div>
        </div>

        <div class="section-div"><span>Business / Integration Details</span></div>

        <!-- Company / App Name -->
        <div class="field">
            <div class="field-label">Company / App Name <span class="req">*</span></div>
            <div class="input-wrap">
                <span class="f-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1"/></svg></span>
                <input type="text" id="company" placeholder="e.g. TechPay Pvt Ltd or MyApp" maxlength="150">
            </div>
            <div class="err-msg" id="company-err"><svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg><span id="company-err-t">Company or app name required.</span></div>
        </div>

        <!-- Website -->
        <div class="field">
            <div class="field-label">Website / App URL <span class="opt">(optional)</span></div>
            <div class="input-wrap">
                <span class="f-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg></span>
                <input type="url" id="website" placeholder="https://yourwebsite.com" maxlength="250">
            </div>
        </div>

        <!-- Intended Use -->
        <div class="field">
            <div class="field-label">Intended Use Case <span class="req">*</span></div>
            <div class="use-cases" id="use-cases">
                <label class="uc-item" onclick="toggleUc(this)">
                    <input type="checkbox" value="mobile_recharge">
                    <div class="uc-check"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></div>
                    Mobile Recharge
                </label>
                <label class="uc-item" onclick="toggleUc(this)">
                    <input type="checkbox" value="dth_recharge">
                    <div class="uc-check"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></div>
                    DTH Recharge
                </label>
                <label class="uc-item" onclick="toggleUc(this)">
                    <input type="checkbox" value="wallet_topup">
                    <div class="uc-check"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></div>
                    Wallet Top-up
                </label>
                <label class="uc-item" onclick="toggleUc(this)">
                    <input type="checkbox" value="bill_payment">
                    <div class="uc-check"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></div>
                    Bill Payment
                </label>
                <label class="uc-item" onclick="toggleUc(this)">
                    <input type="checkbox" value="b2b_reseller">
                    <div class="uc-check"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></div>
                    B2B Reseller
                </label>
                <label class="uc-item" onclick="toggleUc(this)">
                    <input type="checkbox" value="other">
                    <div class="uc-check"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></div>
                    Other
                </label>
            </div>
            <div class="err-msg" id="usecase-err"><svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg><span>Please select at least one use case.</span></div>
        </div>

        <div class="section-div"><span>Set Password</span></div>

        <div class="grid2">
            <!-- Password -->
            <div class="field">
                <div class="field-label">Password <span class="req">*</span></div>
                <div class="input-wrap">
                    <span class="f-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></span>
                    <input type="password" id="password" placeholder="Min. 8 characters" maxlength="72" style="padding-right:36px">
                    <button type="button" class="pwd-toggle" onclick="tPwd('password','eye1')">
                        <svg id="eye1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
                <div class="strength-bar"><div class="strength-fill" id="sfill"></div></div>
                <div class="strength-lbl" id="slbl"></div>
                <div class="pwd-rules">
                    <div class="rule" id="r-len"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>Min 8 chars</div>
                    <div class="rule" id="r-upper"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>Uppercase</div>
                    <div class="rule" id="r-lower"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>Lowercase</div>
                    <div class="rule" id="r-num"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>Number</div>
                    <div class="rule" id="r-sym"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>Symbol</div>
                </div>
                <div class="err-msg" id="password-err"><svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg><span id="password-err-t">Must meet all requirements.</span></div>
            </div>
            <!-- Confirm -->
            <div class="field">
                <div class="field-label">Confirm Password <span class="req">*</span></div>
                <div class="input-wrap">
                    <span class="f-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg></span>
                    <input type="password" id="password_confirmation" placeholder="Repeat password" maxlength="72" style="padding-right:36px">
                    <button type="button" class="pwd-toggle" onclick="tPwd('password_confirmation','eye2')">
                        <svg id="eye2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
                <div class="err-msg" id="confirm-err"><svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg><span id="confirm-err-t">Passwords do not match.</span></div>
            </div>
        </div>

        <div class="section-div"><span>Upload Required Documents</span></div>

        <!-- PAN Image — MANDATORY for API users -->
        <div class="field">
            <div class="field-label">PAN Card Image <span class="req">*</span></div>
            <div class="file-zone" id="pan-zone" style="border-color:rgba(234,179,8,.3)">
                <input type="file" id="pan_image" accept=".jpg,.jpeg,.png,.pdf,.webp" onchange="onFileChange(this,'pan-zone','pan-fname')">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="color:#ca8a04"><path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z"/></svg>
                <div class="file-zone-text"><b>Click to upload PAN Card</b></div>
                <div class="file-name" id="pan-fname"></div>
                <div class="file-hint">JPG, PNG, PDF, WebP · Max 4 MB · Required</div>
            </div>
            <div class="err-msg" id="pan-err"><svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg><span id="pan-err-t">PAN card image is required.</span></div>
        </div>

        <!-- GST Certificate — MANDATORY for API users -->
        <div class="field">
            <div class="field-label">GST Certificate <span class="req">*</span></div>
            <div class="file-zone" id="gst-zone" style="border-color:rgba(99,102,241,.3)">
                <input type="file" id="gst_certificate" accept=".jpg,.jpeg,.png,.pdf,.webp" onchange="onFileChange(this,'gst-zone','gst-fname')">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="color:#818cf8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/></svg>
                <div class="file-zone-text"><b>Click to upload GST Certificate</b></div>
                <div class="file-name" id="gst-fname"></div>
                <div class="file-hint">JPG, PNG, PDF, WebP · Max 4 MB · Required</div>
            </div>
            <div class="err-msg" id="gst-err"><svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg><span id="gst-err-t">GST certificate is required.</span></div>
        </div>

        <!-- Approval notice -->
        <div style="background:rgba(251,191,36,.07);border:1px solid rgba(251,191,36,.2);border-radius:10px;padding:12px 14px;margin-bottom:14px;font-size:12.5px;color:#fde68a;display:flex;align-items:flex-start;gap:10px">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;flex-shrink:0;margin-top:1px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span>After submitting, your application will be <strong>reviewed by our admin team</strong>. You will receive an email notification once your API account is approved.</span>
        </div>

        <!-- Terms -->
        <div class="terms-row">
            <input type="checkbox" id="terms">
            <label for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">API Usage Policy</a>. I will not misuse the API for spam or illegal activities.</label>
        </div>
        <div class="err-msg" id="terms-err" style="margin-bottom:10px"><svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg><span>You must accept the terms to continue.</span></div>

        <button type="submit" class="btn" id="btn">
            <div class="spinner" id="spinner"></div>
            <span id="btn-t">Submit API Registration</span>
        </button>
    </form>

    <div class="link-row">Already have an account? <a href="/user/login">Sign in</a> &nbsp;·&nbsp; <a href="/user/register">Regular registration</a></div>
</div>

<script>
const CSRF = '{{ csrf_token() }}';
if (localStorage.getItem('user_token')) location.href = '/user/dashboard';

const $ = id => document.getElementById(id);
const ES = `<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>`;
const EH = `<path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>`;

function tPwd(id, eid) { const i=$(id); const t=i.type==='text'; i.type=t?'password':'text'; $(eid).innerHTML=t?ES:EH; }

function showE(id, msg) { $(id).classList.add('show'); if(msg) { const t=$(id.replace('-err','-err-t')); if(t) t.textContent=msg; } }
function hideE(id) { $(id).classList.remove('show'); }
function setV(id, ok) { $(id).classList.toggle('err', !ok); $(id).classList.toggle('ok', ok); }
function showAlert(type, msg) {
    $('alert-err').classList.remove('show'); $('alert-ok').classList.remove('show');
    if(type==='err') { $('alert-err-msg').textContent=msg; $('alert-err').classList.add('show'); }
    else             { $('alert-ok-msg').textContent=msg; $('alert-ok').classList.add('show'); }
}

// Use case toggles
function toggleUc(el) {
    el.classList.toggle('selected');
    el.querySelector('input').checked = el.classList.contains('selected');
    hideE('usecase-err');
}

// Password strength
function checkPwd(v) {
    const r = { len:v.length>=8, upper:/[A-Z]/.test(v), lower:/[a-z]/.test(v), num:/[0-9]/.test(v), sym:/[^A-Za-z0-9]/.test(v) };
    ['len','upper','lower','num','sym'].forEach(k => $('r-'+k).classList.toggle('pass', r[k]));
    const sc = Object.values(r).filter(Boolean).length;
    const bars = [['0%','transparent',''],['20%','#ef4444','Weak'],['40%','#f97316','Fair'],['70%','#6366f1','Good'],['90%','#8b5cf6','Strong'],['100%','#7c3aed','Very Strong']];
    $('sfill').style.width=bars[sc][0]; $('sfill').style.background=bars[sc][1];
    $('slbl').textContent=bars[sc][2]; $('slbl').style.color=bars[sc][1];
    return r.len && r.upper && r.lower && r.num && r.sym;
}

$('password').addEventListener('input', function() { checkPwd(this.value); });
$('mobile').addEventListener('input', function() { this.value=this.value.replace(/\D/g,'').slice(0,10); });

const ALLOWED_TYPES = ['image/jpeg','image/png','image/webp','application/pdf'];

function onFileChange(inp, zoneId, fnameId) {
    const f = inp.files[0];
    if (!f) return;
    if (!ALLOWED_TYPES.includes(f.type)) {
        showE(zoneId.replace('-zone','-err'), 'File must be JPG, PNG, PDF, or WebP.');
        inp.value = '';
        return;
    }
    if (f.size > 4 * 1024 * 1024) {
        showE(zoneId.replace('-zone','-err'), 'File must be under 4 MB.');
        inp.value = '';
        return;
    }
    hideE(zoneId.replace('-zone','-err'));
    $(fnameId).textContent = '📎 ' + f.name;
}

$('form').addEventListener('submit', async e => {
    e.preventDefault();
    $('alert-err').classList.remove('show'); $('alert-ok').classList.remove('show');

    const name    = $('name').value.trim();
    const mobile  = $('mobile').value.trim();
    const email   = $('email').value.trim();
    const company = $('company').value.trim();
    const pwd     = $('password').value;
    const cpwd    = $('password_confirmation').value;
    const usecases= [...document.querySelectorAll('#use-cases input:checked')].map(i=>i.value);

    let ok = true;

    if(!name)                            { setV('name',false);  showE('name-err','Full name is required.'); ok=false; } else { setV('name',true); hideE('name-err'); }
    if(!/^\d{10}$/.test(mobile))         { setV('mobile',false);showE('mobile-err','Enter a valid 10-digit mobile number.'); ok=false; } else { setV('mobile',true); hideE('mobile-err'); }
    if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { setV('email',false);showE('email-err','Enter a valid email address.'); ok=false; } else { setV('email',true); hideE('email-err'); }
    if(!company)                         { setV('company',false);showE('company-err','Company or app name is required.'); ok=false; } else { setV('company',true); hideE('company-err'); }
    if(usecases.length===0)              { showE('usecase-err'); ok=false; }
    if(!checkPwd(pwd))                   { setV('password',false);showE('password-err','Password must meet all requirements.'); ok=false; } else { setV('password',true); hideE('password-err'); }
    if(pwd!==cpwd)                       { setV('password_confirmation',false);showE('confirm-err','Passwords do not match.'); ok=false; } else if(cpwd){ setV('password_confirmation',true); hideE('confirm-err'); }
    if(!$('pan_image').files[0])         { showE('pan-err','PAN card image is required.'); ok=false; } else { hideE('pan-err'); }
    if(!$('gst_certificate').files[0])   { showE('gst-err','GST certificate is required.'); ok=false; } else { hideE('gst-err'); }
    if(!$('terms').checked)              { showE('terms-err'); ok=false; }

    if(!ok) return;

    $('btn').disabled=true; $('spinner').style.display='block'; $('btn-t').textContent='Submitting…';

    try {
        const fd = new FormData();
        fd.append('name', name); fd.append('mobile', mobile); fd.append('email', email);
        fd.append('password', pwd); fd.append('password_confirmation', cpwd);
        fd.append('role', 'api_user');
        fd.append('device_name', 'web');
        // Store extra metadata in the notes field (backend accepts it via description or ignores gracefully)
        fd.append('company_name', company);
        fd.append('use_cases', usecases.join(','));
        const website = $('website').value.trim();
        if(website) fd.append('website', website);

        // PAN and GST uploads (mandatory)
        const panFile = $('pan_image').files[0];
        if(panFile) fd.append('pan_image', panFile);
        const gstFile = $('gst_certificate').files[0];
        if(gstFile) fd.append('gst_certificate', gstFile);

        const res  = await fetch('/api/v1/auth/register', { method:'POST', headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF}, body:fd });
        const data = await res.json();

        if(res.ok) {
            showAlert('ok', '⏳ Your API registration has been submitted successfully! Your application is pending admin approval. You will receive an email notification once approved. You can then login to access your account.');
            $('btn').disabled=true;
            $('btn-t').textContent='Registration Submitted';
            setTimeout(() => location.href='/user/login?registered=1', 5000);
            return;
        }
        if(data.errors) {
            const map = {
                name:'name-err', email:'email-err', mobile:'mobile-err',
                password:'password-err', pan_image:'pan-err', gst_certificate:'gst-err'
            };
            let first=null;
            Object.entries(data.errors).forEach(([f,msgs]) => {
                const msg=Array.isArray(msgs)?msgs[0]:msgs;
                if(!first) first=msg;
                if(map[f]) showE(map[f], msg);
            });
            showAlert('err', first||data.message||'Please fix the errors below.');
        } else { showAlert('err', data.message||'Registration failed. Please try again.'); }
    } catch { showAlert('err','Network error. Please check your connection.'); }
    finally {
        if($('btn').disabled && $('btn-t').textContent==='Registration Submitted') return; // keep disabled on success
        $('btn').disabled=false; $('spinner').style.display='none'; $('btn-t').textContent='Submit API Registration';
    }
});
</script>
</body>
</html>
