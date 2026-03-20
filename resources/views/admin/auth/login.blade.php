<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Login — RechargeHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --blue:      #4f46e5;
            --blue-dark: #1d4ed8;
            --purple:    #7c3aed;
            --red:       #ef4444;
            --green:     #10b981;
            --orange:    #f59e0b;
            --border:    rgba(255,255,255,.18);
            --text:      #1e293b;
            --muted:     #64748b;
        }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
            position: relative; overflow: hidden;
            background: #0f0c29;
        }
        body::before {
            content: '';
            position: fixed; inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 10%,  #4f46e580 0%, transparent 60%),
                radial-gradient(ellipse 60% 70% at 80% 90%,  #7c3aed60 0%, transparent 55%),
                radial-gradient(ellipse 70% 50% at 60% 20%,  #2563eb50 0%, transparent 60%),
                radial-gradient(ellipse 90% 80% at 10% 80%,  #1e3a8a70 0%, transparent 60%),
                linear-gradient(135deg, #0f0c29 0%, #1a1040 40%, #0f172a 100%);
            z-index: 0;
        }
        .orb { position:fixed; border-radius:50%; filter:blur(80px); opacity:.25; animation:floatOrb linear infinite; pointer-events:none; z-index:0; }
        .orb-1{width:500px;height:500px;background:#4f46e5;top:-100px;left:-100px;animation-duration:18s;}
        .orb-2{width:400px;height:400px;background:#7c3aed;bottom:-80px;right:-80px;animation-duration:22s;animation-delay:-7s;}
        .orb-3{width:300px;height:300px;background:#2563eb;top:40%;left:55%;animation-duration:15s;animation-delay:-3s;}
        .orb-4{width:250px;height:250px;background:#06b6d4;bottom:20%;left:10%;animation-duration:20s;animation-delay:-12s;}
        @keyframes floatOrb {
            0%   { transform:translate(0,0) scale(1); }
            25%  { transform:translate(30px,-40px) scale(1.05); }
            50%  { transform:translate(-20px,30px) scale(.95); }
            75%  { transform:translate(40px,20px) scale(1.03); }
            100% { transform:translate(0,0) scale(1); }
        }
        .grid-overlay {
            position:fixed;inset:0;
            background-image:linear-gradient(rgba(255,255,255,.025) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.025) 1px,transparent 1px);
            background-size:60px 60px;z-index:0;pointer-events:none;
        }

        /* Card */
        .login-card {
            position:relative; z-index:10;
            display:grid; grid-template-columns:1.1fr 1fr;
            max-width:960px; width:100%;
            border-radius:24px; overflow:hidden;
            box-shadow:0 32px 80px rgba(0,0,0,.5),0 0 0 1px rgba(255,255,255,.08);
            animation:slideUp .5s cubic-bezier(.16,1,.3,1) both;
        }
        @keyframes slideUp { from{opacity:0;transform:translateY(28px)} to{opacity:1;transform:translateY(0)} }

        /* Left panel */
        .panel-left {
            background:linear-gradient(160deg,rgba(79,70,229,.9) 0%,rgba(124,58,237,.85) 50%,rgba(15,12,41,.95) 100%);
            backdrop-filter:blur(20px);
            padding:48px 40px;
            display:flex; flex-direction:column;
            position:relative; overflow:hidden;
            border-right:1px solid rgba(255,255,255,.1);
        }
        .panel-left::before,.panel-left::after{content:'';position:absolute;border-radius:50%;border:1px solid rgba(255,255,255,.08);pointer-events:none;}
        .panel-left::before{width:400px;height:400px;bottom:-120px;right:-120px;}
        .panel-left::after{width:250px;height:250px;bottom:-50px;right:-50px;}

        .brand{display:flex;align-items:center;gap:12px;margin-bottom:40px;}
        .brand-icon{width:46px;height:46px;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.2);border-radius:14px;display:flex;align-items:center;justify-content:center;}
        .brand-icon svg{width:24px;height:24px;}
        .brand-text-name{font-size:19px;font-weight:800;color:#fff;letter-spacing:-.3px;}
        .brand-text-sub{font-size:10.5px;color:rgba(255,255,255,.55);text-transform:uppercase;letter-spacing:1px;font-weight:500;}

        .hero-heading{font-size:26px;font-weight:800;color:#fff;line-height:1.2;margin-bottom:12px;letter-spacing:-.4px;}
        .hero-heading span{background:linear-gradient(90deg,#a5b4fc,#e0e7ff);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
        .hero-sub{font-size:13.5px;color:rgba(255,255,255,.6);line-height:1.7;margin-bottom:28px;}

        .feature-list{list-style:none;display:flex;flex-direction:column;gap:11px;margin-bottom:32px;}
        .feature-list li{display:flex;align-items:center;gap:10px;font-size:13px;color:rgba(255,255,255,.82);}
        .feature-icon{width:22px;height:22px;flex-shrink:0;background:rgba(255,255,255,.12);border-radius:6px;display:flex;align-items:center;justify-content:center;}
        .feature-icon svg{width:12px;height:12px;}

        .stats-grid-left{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:auto;}
        .stat-box{background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1);border-radius:12px;padding:14px 16px;}
        .stat-val{font-size:20px;font-weight:800;color:#fff;}
        .stat-label{font-size:11px;color:rgba(255,255,255,.5);margin-top:3px;}

        /* Right panel */
        .panel-right{
            background:rgba(255,255,255,.97);
            backdrop-filter:blur(24px);
            padding:48px 44px;
            display:flex; flex-direction:column; justify-content:center;
        }
        .form-head{margin-bottom:24px;}
        .form-head h1{font-size:24px;font-weight:800;color:var(--text);letter-spacing:-.4px;margin-bottom:5px;}
        .form-head p{font-size:13.5px;color:var(--muted);}

        /* Form groups */
        .form-group{margin-bottom:16px;}
        .form-label{display:flex;align-items:center;gap:5px;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:7px;}
        .required-star{color:var(--red);font-size:13px;}
        .input-wrap{position:relative;}
        .input-icon{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:#9ca3af;pointer-events:none;}
        .input-icon svg{width:16px;height:16px;display:block;}

        .form-input{
            width:100%;
            padding:12px 14px 12px 40px;
            border:1.5px solid #e5e7eb;
            border-radius:11px;
            font-size:14px;font-family:inherit;color:var(--text);
            background:#f9fafb;
            transition:border-color .15s,box-shadow .15s,background .15s;
            outline:none;
        }
        .form-input:focus{border-color:var(--blue);box-shadow:0 0 0 3.5px rgba(79,70,229,.12);background:#fff;}
        .form-input::placeholder{color:#9ca3af;}
        .form-input.is-invalid{border-color:var(--red)!important;box-shadow:0 0 0 3px rgba(239,68,68,.1)!important;}
        .form-input.is-valid{border-color:var(--green)!important;box-shadow:0 0 0 3px rgba(16,185,129,.1)!important;}

        /* Inline field error */
        .field-error{
            display:none;
            align-items:center;gap:5px;
            margin-top:5px;
            font-size:11.5px;color:var(--red);font-weight:500;
        }
        .field-error.show{display:flex;}
        .field-error svg{width:13px;height:13px;flex-shrink:0;}

        .toggle-pwd{position:absolute;right:13px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9ca3af;padding:4px;border-radius:5px;display:flex;align-items:center;}
        .toggle-pwd svg{width:17px;height:17px;}

        .form-row-meta{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;}
        .remember-label{display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:var(--muted);font-weight:500;}
        .remember-label input[type="checkbox"]{display:none;}
        .custom-checkbox{width:17px;height:17px;border:2px solid #d1d5db;border-radius:5px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:#fff;transition:all .15s;}
        .custom-checkbox svg{width:10px;height:10px;display:none;}
        .remember-label input:checked + .custom-checkbox{background:var(--blue);border-color:var(--blue);}
        .remember-label input:checked + .custom-checkbox svg{display:block;}

        .forgot-link{font-size:13px;font-weight:600;color:var(--blue);text-decoration:none;}
        .forgot-link:hover{color:var(--blue-dark);text-decoration:underline;}

        /* Top-level alerts */
        .alert{padding:11px 14px;border-radius:10px;font-size:13px;margin-bottom:14px;display:none;align-items:flex-start;gap:9px;}
        .alert svg{width:16px;height:16px;flex-shrink:0;margin-top:1px;}
        .alert.show{display:flex;}
        .alert-error  {background:#fff1f2;border:1.5px solid #fecdd3;color:#be123c;}
        .alert-lockout{background:#fff7ed;border:1.5px solid #fed7aa;color:#c2410c;}
        .alert-success{background:#f0fdf4;border:1.5px solid #bbf7d0;color:#15803d;}

        /* Submit */
        .btn-submit{
            width:100%;padding:13.5px;
            background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 100%);
            color:#fff;border:none;border-radius:11px;
            font-size:14.5px;font-weight:700;font-family:inherit;cursor:pointer;
            transition:all .2s;
            display:flex;align-items:center;justify-content:center;gap:8px;
            position:relative;overflow:hidden;
            box-shadow:0 4px 20px rgba(79,70,229,.4);
            letter-spacing:.1px;
        }
        .btn-submit::before{content:'';position:absolute;inset:0;background:linear-gradient(135deg,#6366f1,#8b5cf6);opacity:0;transition:opacity .2s;}
        .btn-submit:hover::before{opacity:1;}
        .btn-submit:hover{box-shadow:0 6px 28px rgba(79,70,229,.5);transform:translateY(-1px);}
        .btn-submit:active{transform:translateY(0);}
        .btn-submit:disabled{opacity:.7;cursor:not-allowed;transform:none;}
        .btn-submit span,.btn-submit svg{position:relative;z-index:1;}

        .spinner{width:17px;height:17px;border:2.5px solid rgba(255,255,255,.35);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite;display:none;flex-shrink:0;}
        @keyframes spin{to{transform:rotate(360deg)}}

        /* Divider */
        .divider{display:flex;align-items:center;gap:12px;margin:18px 0;}
        .divider::before,.divider::after{content:'';flex:1;height:1px;background:#e5e7eb;}
        .divider span{font-size:12px;color:#9ca3af;font-weight:500;}

        /* Security badges */
        .security-badges{display:flex;align-items:center;justify-content:center;gap:16px;flex-wrap:wrap;}
        .security-badge{display:flex;align-items:center;gap:5px;font-size:11px;color:#9ca3af;font-weight:500;}
        .security-badge svg{width:13px;height:13px;color:#10b981;}

        /* Modal */
        .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.6);backdrop-filter:blur(4px);z-index:999;display:flex;align-items:center;justify-content:center;padding:20px;opacity:0;pointer-events:none;transition:opacity .2s;}
        .modal-overlay.show{opacity:1;pointer-events:all;}
        .modal-box{background:#fff;border-radius:18px;padding:36px 32px;max-width:400px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.3);transform:scale(.95);transition:transform .2s;position:relative;}
        .modal-overlay.show .modal-box{transform:scale(1);}
        .modal-icon{width:52px;height:52px;background:linear-gradient(135deg,#ede9fe,#ddd6fe);border-radius:14px;display:flex;align-items:center;justify-content:center;margin-bottom:16px;}
        .modal-icon svg{width:26px;height:26px;color:var(--blue);}
        .modal-box h2{font-size:19px;font-weight:800;color:var(--text);margin-bottom:6px;}
        .modal-box p{font-size:13.5px;color:var(--muted);margin-bottom:22px;line-height:1.6;}
        .modal-close{position:absolute;top:14px;right:14px;background:#f3f4f6;border:none;cursor:pointer;width:30px;height:30px;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#6b7280;}
        .modal-close svg{width:15px;height:15px;}

        @media(max-width:700px){
            .login-card{grid-template-columns:1fr;}
            .panel-left{display:none;}
            .panel-right{padding:36px 24px;border-radius:24px;}
        }
    </style>
</head>
<body>

<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>
<div class="orb orb-4"></div>
<div class="grid-overlay"></div>

<div class="login-card">

    <!-- LEFT PANEL -->
    <div class="panel-left">
        <div class="brand">
            <div class="brand-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <div>
                <div class="brand-text-name">RechargeHub</div>
                <div class="brand-text-sub">Admin Portal</div>
            </div>
        </div>
        <div class="hero-heading">Enterprise<br><span>Recharge Platform</span></div>
        <p class="hero-sub">Real-time analytics, operator monitoring, and complete transaction control for your business.</p>
        <ul class="feature-list">
            <li><div class="feature-icon"><svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg></div>Live dashboard with automated metrics</li>
            <li><div class="feature-icon"><svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg></div>Multi-operator performance tracking</li>
            <li><div class="feature-icon"><svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg></div>Wallet & commission management</li>
            <li><div class="feature-icon"><svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div>Comprehensive reports & exports</li>
        </ul>
        <div class="stats-grid-left">
            <div class="stat-box"><div class="stat-val">99.9%</div><div class="stat-label">Uptime SLA</div></div>
            <div class="stat-box"><div class="stat-val">8 hrs</div><div class="stat-label">Session TTL</div></div>
            <div class="stat-box"><div class="stat-val">256-bit</div><div class="stat-label">Encryption</div></div>
            <div class="stat-box"><div class="stat-val">5 tries</div><div class="stat-label">Brute Force Lock</div></div>
        </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="panel-right">
        <div class="form-head">
            <h1>Welcome back 👋</h1>
            <p>Sign in to your employee account to continue</p>
        </div>

        <!-- Top alerts -->
        <div class="alert alert-error" id="error-alert">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span id="error-msg"></span>
        </div>
        <div class="alert alert-lockout" id="lockout-box">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            <span id="lockout-msg">Account locked. Please try again later.</span>
        </div>
        <div class="alert alert-success" id="success-alert">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span id="success-msg"></span>
        </div>

        <form id="login-form" novalidate autocomplete="on">

            <!-- Email -->
            <div class="form-group">
                <label class="form-label" for="email">
                    Employee Email <span class="required-star">*</span>
                </label>
                <div class="input-wrap">
                    <span class="input-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </span>
                    <input type="email" id="email" name="email" class="form-input"
                           placeholder="you@company.com"
                           autocomplete="username"
                           maxlength="150"
                           required>
                </div>
                <div class="field-error" id="email-error">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span id="email-error-msg">Please enter a valid email address.</span>
                </div>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label class="form-label" for="password">
                    Password <span class="required-star">*</span>
                </label>
                <div class="input-wrap">
                    <span class="input-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </span>
                    <input type="password" id="password" name="password" class="form-input"
                           placeholder="Enter your password"
                           autocomplete="current-password"
                           minlength="8" maxlength="72"
                           required
                           style="padding-right:44px">
                    <button type="button" class="toggle-pwd" onclick="togglePwd()">
                        <svg id="eye-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
                <div class="field-error" id="password-error">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span id="password-error-msg">Password must be at least 8 characters.</span>
                </div>
            </div>

            <!-- Remember + Forgot -->
            <div class="form-row-meta">
                <label class="remember-label">
                    <input type="checkbox" id="remember-me">
                    <span class="custom-checkbox">
                        <svg fill="none" viewBox="0 0 12 12" stroke="white" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2 6l3 3 5-5"/></svg>
                    </span>
                    Remember me
                </label>
                <a href="#" class="forgot-link" onclick="openForgot(event)">Forgot password?</a>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn-submit" id="submit-btn">
                <div class="spinner" id="spinner"></div>
                <span id="btn-text">Sign In to Dashboard</span>
                <svg id="btn-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:16px;height:16px">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </button>
        </form>

        <div class="divider"><span>Security</span></div>
        <div class="security-badges">
            <div class="security-badge">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Sanctum Auth
            </div>
            <div class="security-badge">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                Brute Force Protected
            </div>
            <div class="security-badge">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                256-bit Encrypted
            </div>
        </div>
    </div>
</div>

<!-- Forgot Password Modal -->
<div class="modal-overlay" id="forgot-modal" onclick="closeForgotOutside(event)">
    <div class="modal-box">
        <button class="modal-close" onclick="closeForgot()">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <div class="modal-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
        </div>
        <h2>Reset Password</h2>
        <p>Enter your registered employee email and we'll send a reset link to your inbox.</p>
        <div class="alert alert-success" id="forgot-success" style="margin-bottom:16px">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Reset link sent! Check your email inbox.</span>
        </div>
        <div class="alert alert-error" id="forgot-error" style="margin-bottom:16px">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span id="forgot-error-msg">Something went wrong.</span>
        </div>
        <div class="form-group" style="margin-bottom:16px">
            <div class="input-wrap">
                <span class="input-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </span>
                <input type="email" id="forgot-email" class="form-input" placeholder="your@email.com" maxlength="150">
            </div>
            <div class="field-error" id="forgot-field-error">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Please enter a valid email address.</span>
            </div>
        </div>
        <button class="btn-submit" id="forgot-btn" onclick="submitForgot()">
            <div class="spinner" id="forgot-spinner"></div>
            <span id="forgot-btn-text">Send Reset Link</span>
        </button>
        <div style="text-align:center;margin-top:14px">
            <a href="#" onclick="closeForgot()" style="font-size:13px;color:var(--muted);text-decoration:none">← Back to sign in</a>
        </div>
    </div>
</div>

<script>
/* ── Helpers ──────────────────────────────────────────────────── */
const el   = id => document.getElementById(id);
const show = id => el(id).classList.add('show');
const hide = id => el(id).classList.remove('show');

/* ── Redirect if already logged in ───────────────────────────── */
(function () {
    if (localStorage.getItem('emp_token')) window.location.href = '/admin/dashboard';

    // Auto-fill from URL params then immediately wipe them from the address bar
    const params = new URLSearchParams(window.location.search);
    const urlEmail = params.get('email');
    const urlPwd   = params.get('password');
    if (urlEmail || urlPwd) {
        if (urlEmail) el('email').value    = urlEmail;
        if (urlPwd)   el('password').value = urlPwd;
        // Remove credentials from URL without adding to browser history
        history.replaceState(null, '', window.location.pathname);
    }

    const saved = localStorage.getItem('emp_remember_email');
    if (saved && !urlEmail) { el('email').value = saved; el('remember-me').checked = true; }
})();

/* ── Password toggle ─────────────────────────────────────────── */
function togglePwd() {
    const inp = el('password');
    const isText = inp.type === 'text';
    inp.type = isText ? 'password' : 'text';
    el('eye-icon').innerHTML = isText
        ? `<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>`
        : `<path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>`;
}

/* ── Alerts ───────────────────────────────────────────────────── */
function showError(msg)   { el('error-msg').textContent = msg;   show('error-alert');   hide('lockout-box'); hide('success-alert'); }
function showLockout(msg) { el('lockout-msg').textContent = msg; show('lockout-box');   hide('error-alert'); }
function showSuccess(msg) { el('success-msg').textContent = msg; show('success-alert'); hide('error-alert'); }
function clearAlerts()    { hide('error-alert'); hide('lockout-box'); hide('success-alert'); }

/* ── Field validation helpers ────────────────────────────────── */
function fieldError(inputId, errorId, msgId, msg) {
    el(inputId).classList.add('is-invalid');
    el(inputId).classList.remove('is-valid');
    if (msgId) el(msgId).textContent = msg;
    show(errorId);
}
function fieldOk(inputId, errorId) {
    el(inputId).classList.remove('is-invalid');
    el(inputId).classList.add('is-valid');
    hide(errorId);
}
function fieldReset(inputId, errorId) {
    el(inputId).classList.remove('is-invalid', 'is-valid');
    hide(errorId);
}

function validateEmail(value) {
    if (!value) return 'Email is required.';
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return 'Please enter a valid email address.';
    if (value.length > 150) return 'Email must not exceed 150 characters.';
    return null;
}
function validatePassword(value) {
    if (!value) return 'Password is required.';
    if (value.length < 8) return 'Password must be at least 8 characters.';
    if (value.length > 72) return 'Password must not exceed 72 characters.';
    return null;
}

/* ── Real-time blur validation ───────────────────────────────── */
el('email').addEventListener('blur', function () {
    const err = validateEmail(this.value.trim());
    if (err) fieldError('email', 'email-error', 'email-error-msg', err);
    else if (this.value) fieldOk('email', 'email-error');
    else fieldReset('email', 'email-error');
});
el('email').addEventListener('input', function () {
    if (this.classList.contains('is-invalid')) {
        const err = validateEmail(this.value.trim());
        if (!err) fieldOk('email', 'email-error');
    }
});

el('password').addEventListener('blur', function () {
    const err = validatePassword(this.value);
    if (err) fieldError('password', 'password-error', 'password-error-msg', err);
    else if (this.value) fieldOk('password', 'password-error');
    else fieldReset('password', 'password-error');
});
el('password').addEventListener('input', function () {
    if (this.classList.contains('is-invalid')) {
        const err = validatePassword(this.value);
        if (!err) fieldOk('password', 'password-error');
    }
});

/* ── Loading state ────────────────────────────────────────────── */
function setLoading(on) {
    el('submit-btn').disabled        = on;
    el('spinner').style.display      = on ? 'block' : 'none';
    el('btn-text').textContent       = on ? 'Signing in…' : 'Sign In to Dashboard';
    el('btn-icon').style.display     = on ? 'none' : 'inline';
}

/* ── Form submit ──────────────────────────────────────────────── */
el('login-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    clearAlerts();

    const email    = el('email').value.trim();
    const password = el('password').value;
    const remember = el('remember-me').checked;

    // Client-side validation
    const emailErr = validateEmail(email);
    const pwdErr   = validatePassword(password);
    let hasError   = false;

    if (emailErr) { fieldError('email', 'email-error', 'email-error-msg', emailErr); hasError = true; }
    else fieldOk('email', 'email-error');

    if (pwdErr) { fieldError('password', 'password-error', 'password-error-msg', pwdErr); hasError = true; }
    else fieldOk('password', 'password-error');

    if (hasError) return;

    setLoading(true);

    try {
        const res  = await fetch('/api/v1/employee/auth/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ email, password, device: 'admin-panel-web' }),
        });
        const data = await res.json();

        if (res.ok) {
            remember
                ? localStorage.setItem('emp_remember_email', email)
                : localStorage.removeItem('emp_remember_email');

            localStorage.setItem('emp_token', data.token);
            localStorage.setItem('emp_data',  JSON.stringify(data.employee || {}));

            showSuccess('Login successful! Redirecting…');
            setTimeout(() => { window.location.href = '/admin/dashboard'; }, 800);
        } else if (res.status === 423) {
            showLockout(data.message + (data.retry_after ? ' Retry after: ' + data.retry_after + '.' : ''));
        } else {
            fieldError('password', 'password-error', 'password-error-msg', '');
            hide('password-error');
            el('password').classList.add('is-invalid');
            showError(data.message || 'Authentication failed. Please check your credentials.');
        }
    } catch {
        showError('Unable to reach the server. Please check your connection.');
    } finally {
        setLoading(false);
    }
});

/* ── Forgot password modal ────────────────────────────────────── */
function openForgot(e) {
    e.preventDefault();
    el('forgot-email').value = el('email').value;
    hide('forgot-success'); hide('forgot-error'); hide('forgot-field-error');
    el('forgot-email').classList.remove('is-invalid', 'is-valid');
    el('forgot-modal').classList.add('show');
    setTimeout(() => el('forgot-email').focus(), 200);
}
function closeForgot()               { el('forgot-modal').classList.remove('show'); }
function closeForgotOutside(e)       { if (e.target === el('forgot-modal')) closeForgot(); }
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeForgot(); });

async function submitForgot() {
    const email = el('forgot-email').value.trim();
    hide('forgot-success'); hide('forgot-error'); hide('forgot-field-error');

    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        el('forgot-email').classList.add('is-invalid');
        show('forgot-field-error');
        return;
    }
    el('forgot-email').classList.remove('is-invalid');

    el('forgot-btn').disabled          = true;
    el('forgot-spinner').style.display = 'block';
    el('forgot-btn-text').textContent  = 'Sending…';

    await new Promise(r => setTimeout(r, 1200));

    el('forgot-btn').disabled          = false;
    el('forgot-spinner').style.display = 'none';
    el('forgot-btn-text').textContent  = 'Send Reset Link';
    show('forgot-success');
}
</script>
</body>
</html>
