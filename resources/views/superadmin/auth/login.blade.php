<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Super Admin Login — RechargeHub Command Center</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        /* ============================================================
           RESET & ROOT
        ============================================================ */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:       #050816;
            --card:     rgba(255,255,255,.04);
            --border:   rgba(255,255,255,.08);
            --text:     #e2e8f0;
            --muted:    #94a3b8;
            --cyan:     #06b6d4;
            --cyan-10:  rgba(6,182,212,.10);
            --cyan-20:  rgba(6,182,212,.20);
            --cyan-glow: 0 0 30px rgba(6,182,212,.4);
            --violet:   #8b5cf6;
            --rose:     #f43f5e;
            --emerald:  #10b981;
            --amber:    #f59e0b;
        }

        html, body {
            height: 100%;
            font-family: 'Inter', -apple-system, sans-serif;
            background: var(--bg);
            color: var(--text);
            -webkit-font-smoothing: antialiased;
        }

        /* ============================================================
           ANIMATED BACKGROUND
        ============================================================ */
        .bg-scene {
            position: fixed;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
            z-index: 0;
        }

        /* Grid lines */
        .bg-grid {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(6,182,212,.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(6,182,212,.04) 1px, transparent 1px);
            background-size: 60px 60px;
        }

        /* Orb 1 — cyan */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            animation: orbFloat 8s ease-in-out infinite;
        }

        .orb-1 {
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(6,182,212,.18) 0%, transparent 70%);
            top: -200px; left: -150px;
            animation-delay: 0s;
        }

        .orb-2 {
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(139,92,246,.15) 0%, transparent 70%);
            bottom: -150px; right: -100px;
            animation-delay: -3s;
        }

        .orb-3 {
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(6,182,212,.10) 0%, transparent 70%);
            top: 50%; left: 50%;
            transform: translate(-50%,-50%);
            animation-delay: -5s;
        }

        @keyframes orbFloat {
            0%, 100% { transform: translateY(0px) scale(1); }
            33%       { transform: translateY(-30px) scale(1.05); }
            66%       { transform: translateY(20px) scale(.97); }
        }

        /* Particles */
        .particles-container {
            position: absolute;
            inset: 0;
        }

        .particle {
            position: absolute;
            width: 2px; height: 2px;
            background: var(--cyan);
            border-radius: 50%;
            opacity: 0;
            animation: particleFade var(--dur, 4s) var(--delay, 0s) ease-in-out infinite;
        }

        @keyframes particleFade {
            0%         { opacity: 0; transform: translateY(0) scale(0); }
            20%        { opacity: .8; transform: translateY(-20px) scale(1); }
            80%        { opacity: .4; transform: translateY(-80px) scale(.8); }
            100%       { opacity: 0; transform: translateY(-120px) scale(0); }
        }

        /* Scan line */
        .scan-line {
            position: absolute;
            left: 0; right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--cyan), transparent);
            opacity: .15;
            animation: scanMove 6s linear infinite;
        }

        @keyframes scanMove {
            0%   { top: -2px; }
            100% { top: 100%; }
        }

        /* ============================================================
           LAYOUT
        ============================================================ */
        .login-wrapper {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }

        .login-container {
            display: grid;
            grid-template-columns: 1fr 420px;
            width: 100%;
            max-width: 900px;
            min-height: 600px;
            background: rgba(8,14,36,.6);
            border: 1px solid var(--border);
            border-radius: 24px;
            overflow: hidden;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow:
                0 0 0 1px rgba(6,182,212,.08),
                0 40px 80px rgba(0,0,0,.6),
                inset 0 1px 0 rgba(255,255,255,.05);
        }

        /* ============================================================
           LEFT PANEL — BRAND
        ============================================================ */
        .login-left {
            background: linear-gradient(135deg, rgba(6,182,212,.08) 0%, rgba(139,92,246,.06) 50%, rgba(5,8,22,.9) 100%);
            border-right: 1px solid var(--border);
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }

        /* decorative circuit lines */
        .login-left::before {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 1px;
            height: 100%;
            background: linear-gradient(180deg, transparent, var(--cyan), transparent);
            opacity: .3;
        }

        .login-left::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0;
            width: 100%;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--cyan), transparent);
            opacity: .2;
        }

        /* Brand mark */
        .brand-mark {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 48px;
        }

        .brand-icon {
            width: 48px; height: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--cyan), var(--violet));
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
            box-shadow: var(--cyan-glow);
            position: relative;
        }

        .brand-icon::after {
            content: '';
            position: absolute;
            inset: -2px;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--cyan), var(--violet));
            opacity: .3;
            z-index: -1;
            filter: blur(8px);
        }

        .brand-text h1 {
            font-size: 1.2rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -.02em;
            line-height: 1.2;
        }

        .brand-text span {
            font-size: .72rem;
            font-weight: 600;
            color: var(--cyan);
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        /* Heading */
        .left-heading {
            margin-bottom: 32px;
        }

        .left-heading h2 {
            font-size: 1.65rem;
            font-weight: 800;
            color: #fff;
            line-height: 1.25;
            letter-spacing: -.03em;
            margin-bottom: 10px;
        }

        .left-heading h2 span {
            background: linear-gradient(135deg, var(--cyan), var(--violet));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .left-heading p {
            font-size: .83rem;
            color: var(--muted);
            line-height: 1.6;
        }

        /* Feature bullets */
        .feature-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 14px;
            margin-bottom: auto;
        }

        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .feature-icon {
            width: 30px; height: 30px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .feature-icon.cyan    { background: rgba(6,182,212,.15);   }
        .feature-icon.violet  { background: rgba(139,92,246,.15);  }
        .feature-icon.emerald { background: rgba(16,185,129,.15);  }
        .feature-icon.amber   { background: rgba(245,158,11,.15);  }

        .feature-text h4 {
            font-size: .82rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 2px;
        }

        .feature-text p {
            font-size: .72rem;
            color: var(--muted);
            line-height: 1.5;
        }

        /* Stats */
        .left-stats {
            display: grid;
            grid-template-columns: repeat(3,1fr);
            gap: 10px;
            margin-top: 32px;
        }

        .stat-chip {
            background: rgba(255,255,255,.04);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 12px 10px;
            text-align: center;
        }

        .stat-chip-val {
            font-size: 1rem;
            font-weight: 800;
            color: var(--cyan);
            display: block;
            margin-bottom: 3px;
        }

        .stat-chip-lbl {
            font-size: .64rem;
            color: var(--muted);
            font-weight: 500;
        }

        /* ============================================================
           RIGHT PANEL — FORM
        ============================================================ */
        .login-right {
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: rgba(5,8,22,.4);
        }

        .form-header {
            margin-bottom: 32px;
        }

        .form-header-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            background: var(--cyan-10);
            border: 1px solid var(--cyan-20);
            border-radius: 99px;
            font-size: .68rem;
            font-weight: 600;
            color: var(--cyan);
            letter-spacing: .06em;
            text-transform: uppercase;
            margin-bottom: 14px;
        }

        .form-header h2 {
            font-size: 1.5rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -.03em;
            margin-bottom: 6px;
        }

        .form-header p {
            font-size: .8rem;
            color: var(--muted);
        }

        /* Form group */
        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            font-size: .75rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 7px;
            letter-spacing: .02em;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            pointer-events: none;
            display: flex;
        }

        .input-icon svg {
            width: 16px; height: 16px;
            stroke: currentColor; fill: none;
            stroke-width: 1.8;
            stroke-linecap: round; stroke-linejoin: round;
        }

        .form-input {
            width: 100%;
            padding: 11px 12px 11px 40px;
            background: rgba(255,255,255,.05);
            border: 1px solid var(--border);
            border-radius: 12px;
            font-size: .85rem;
            font-family: 'Inter', sans-serif;
            color: var(--text);
            outline: none;
            transition: all .25s ease;
        }

        .form-input::placeholder { color: var(--muted); }

        .form-input:focus {
            border-color: var(--cyan);
            background: var(--cyan-10);
            box-shadow: 0 0 0 3px rgba(6,182,212,.15);
        }

        /* password toggle */
        .pwd-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--muted);
            display: flex;
            transition: color .2s;
            padding: 4px;
        }

        .pwd-toggle:hover { color: var(--cyan); }

        .pwd-toggle svg {
            width: 16px; height: 16px;
            stroke: currentColor; fill: none;
            stroke-width: 1.8;
            stroke-linecap: round; stroke-linejoin: round;
        }

        /* Form row */
        .form-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .form-check input[type="checkbox"] {
            width: 15px; height: 15px;
            accent-color: var(--cyan);
            cursor: pointer;
        }

        .form-check-label {
            font-size: .78rem;
            color: var(--muted);
        }

        .form-link {
            font-size: .78rem;
            color: var(--cyan);
            text-decoration: none;
            transition: opacity .2s;
        }

        .form-link:hover { opacity: .75; }

        /* Submit button */
        .btn-submit {
            width: 100%;
            padding: 13px 20px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--cyan) 0%, rgba(6,182,212,.75) 50%, var(--violet) 100%);
            color: #fff;
            font-size: .88rem;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all .3s ease;
            position: relative;
            overflow: hidden;
            box-shadow: 0 6px 24px rgba(6,182,212,.35);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-submit::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.15), transparent);
            transition: left .5s;
        }

        .btn-submit:hover::before { left: 100%; }

        .btn-submit:hover {
            box-shadow: 0 8px 32px rgba(6,182,212,.5);
            transform: translateY(-2px);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit:disabled {
            opacity: .6;
            cursor: not-allowed;
            transform: none;
        }

        .btn-submit svg {
            width: 18px; height: 18px;
            stroke: currentColor; fill: none;
            stroke-width: 2;
            stroke-linecap: round; stroke-linejoin: round;
        }

        /* Spinner */
        .spinner {
            width: 18px; height: 18px;
            border: 2px solid rgba(255,255,255,.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .7s linear infinite;
            display: none;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        /* Alert */
        .alert {
            padding: 12px 14px;
            border-radius: 10px;
            font-size: .8rem;
            font-weight: 500;
            margin-bottom: 18px;
            display: none;
            align-items: flex-start;
            gap: 8px;
        }

        .alert.show { display: flex; }

        .alert-error {
            background: rgba(244,63,94,.1);
            border: 1px solid rgba(244,63,94,.25);
            color: #fca5a5;
        }

        .alert-success {
            background: rgba(16,185,129,.1);
            border: 1px solid rgba(16,185,129,.25);
            color: #6ee7b7;
        }

        .alert svg {
            width: 16px; height: 16px;
            stroke: currentColor; fill: none;
            stroke-width: 2;
            stroke-linecap: round; stroke-linejoin: round;
            flex-shrink: 0;
            margin-top: 1px;
        }

        /* Divider */
        .form-divider {
            text-align: center;
            position: relative;
            margin: 22px 0;
        }

        .form-divider::before {
            content: '';
            position: absolute;
            top: 50%; left: 0; right: 0;
            height: 1px;
            background: var(--border);
        }

        .form-divider span {
            position: relative;
            background: rgba(5,8,22,.6);
            padding: 0 12px;
            font-size: .72rem;
            color: var(--muted);
        }

        /* Security note */
        .security-note {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 20px;
            padding: 10px 14px;
            background: rgba(255,255,255,.03);
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: .72rem;
            color: var(--muted);
        }

        .security-note svg {
            width: 14px; height: 14px;
            stroke: var(--cyan); fill: none;
            stroke-width: 2;
            stroke-linecap: round; stroke-linejoin: round;
            flex-shrink: 0;
        }

        /* ============================================================
           RESPONSIVE
        ============================================================ */
        @media (max-width: 800px) {
            .login-container {
                grid-template-columns: 1fr;
                max-width: 440px;
            }
            .login-left {
                padding: 36px 32px;
                border-right: none;
                border-bottom: 1px solid var(--border);
            }
            .left-heading h2 { font-size: 1.3rem; }
            .left-stats { grid-template-columns: repeat(3,1fr); }
        }

        @media (max-width: 480px) {
            .login-right { padding: 32px 24px; }
            .login-left  { padding: 32px 24px; }
        }

        /* Loading bar at top */
        .page-load-bar {
            position: fixed;
            top: 0; left: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--cyan), var(--violet));
            width: 0%;
            z-index: 9999;
            transition: width .3s ease;
        }
    </style>
</head>
<body>

    <!-- Page load bar -->
    <div class="page-load-bar" id="pageLoadBar"></div>

    <!-- ============================================================
         ANIMATED BACKGROUND
    ============================================================ -->
    <div class="bg-scene">
        <div class="bg-grid"></div>
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
        <div class="scan-line"></div>
        <div class="particles-container" id="particlesContainer"></div>
    </div>

    <!-- ============================================================
         MAIN LAYOUT
    ============================================================ -->
    <div class="login-wrapper">
        <div class="login-container">

            <!-- ---- LEFT PANEL ---- -->
            <div class="login-left">

                <!-- Brand -->
                <div class="brand-mark">
                    <div class="brand-icon">⚡</div>
                    <div class="brand-text">
                        <h1>RechargeHub</h1>
                        <span>Command Center</span>
                    </div>
                </div>

                <!-- Heading -->
                <div class="left-heading">
                    <h2>Platform <span>Command</span> Access</h2>
                    <p>Secure gateway to the RechargeHub super admin control panel. Full platform oversight with real-time intelligence.</p>
                </div>

                <!-- Features -->
                <ul class="feature-list">
                    <li class="feature-item">
                        <div class="feature-icon cyan">🔭</div>
                        <div class="feature-text">
                            <h4>Full Platform Oversight</h4>
                            <p>Monitor every transaction, admin, and operator in real-time from a single command center.</p>
                        </div>
                    </li>
                    <li class="feature-item">
                        <div class="feature-icon violet">👥</div>
                        <div class="feature-text">
                            <h4>Multi-Admin Control</h4>
                            <p>Create, suspend, and manage admin accounts across all geographic zones instantly.</p>
                        </div>
                    </li>
                    <li class="feature-item">
                        <div class="feature-icon emerald">📊</div>
                        <div class="feature-text">
                            <h4>Revenue Intelligence</h4>
                            <p>Deep analytics on gross revenue, commissions, refunds, and platform-wide financial health.</p>
                        </div>
                    </li>
                    <li class="feature-item">
                        <div class="feature-icon amber">🛡️</div>
                        <div class="feature-text">
                            <h4>Security Command</h4>
                            <p>Audit trail, access control, and real-time security watch for the entire platform.</p>
                        </div>
                    </li>
                </ul>

                <!-- Stats -->
                <div class="left-stats">
                    <div class="stat-chip">
                        <span class="stat-chip-val">99.99%</span>
                        <span class="stat-chip-lbl">Uptime</span>
                    </div>
                    <div class="stat-chip">
                        <span class="stat-chip-val">12ms</span>
                        <span class="stat-chip-lbl">Response</span>
                    </div>
                    <div class="stat-chip">
                        <span class="stat-chip-val" id="adminCount">8</span>
                        <span class="stat-chip-lbl">Admins</span>
                    </div>
                </div>

            </div>

            <!-- ---- RIGHT PANEL — FORM ---- -->
            <div class="login-right">

                <div class="form-header">
                    <div class="form-header-badge">
                        <svg width="10" height="10" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                        Restricted Access
                    </div>
                    <h2>Super Admin Login</h2>
                    <p>Enter your credentials to access the command center</p>
                </div>

                <!-- Alert -->
                <div class="alert alert-error" id="alertError">
                    <svg viewBox="0 0 24 24"><path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span id="alertErrorText">Invalid credentials. Please try again.</span>
                </div>

                <div class="alert alert-success" id="alertSuccess">
                    <svg viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Login successful! Redirecting to Command Center...</span>
                </div>

                <!-- Form -->
                <form id="loginForm" onsubmit="handleLogin(event)">

                    <!-- Email -->
                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <div class="input-wrap">
                            <span class="input-icon">
                                <svg viewBox="0 0 24 24"><path d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                            </span>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-input"
                                placeholder="superadmin@rechargeshub.com"
                                autocomplete="email"
                                required
                            >
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <div class="input-wrap">
                            <span class="input-icon">
                                <svg viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </span>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-input"
                                placeholder="Enter your password"
                                autocomplete="current-password"
                                required
                            >
                            <button type="button" class="pwd-toggle" onclick="togglePassword()" id="pwdToggle">
                                <svg id="eyeIcon" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Remember + Forgot -->
                    <div class="form-row">
                        <label class="form-check">
                            <input type="checkbox" id="remember" name="remember">
                            <span class="form-check-label">Remember me</span>
                        </label>
                        <a href="/superadmin/forgot-password" class="form-link">Forgot password?</a>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn-submit" id="submitBtn">
                        <span id="btnText">Access Command Center</span>
                        <span class="spinner" id="btnSpinner"></span>
                        <svg id="btnArrow" viewBox="0 0 24 24"><path d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </button>

                </form>

                <!-- Security note -->
                <div class="security-note">
                    <svg viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    All login attempts are logged and monitored. Unauthorised access is strictly prohibited.
                </div>

            </div>
            <!-- end right panel -->

        </div>
    </div>

    <!-- ============================================================
         JAVASCRIPT
    ============================================================ -->
    <script>
    /* ---- Redirect if already logged in ---- */
    (function() {
        const token = localStorage.getItem('sa_token');
        if (token) {
            window.location.href = '/superadmin/dashboard';
        }
    })();

    /* ---- Particles ---- */
    (function createParticles() {
        const container = document.getElementById('particlesContainer');
        if (!container) return;
        const colors = ['rgba(6,182,212,.9)', 'rgba(139,92,246,.7)', 'rgba(16,185,129,.6)'];

        for (let i = 0; i < 35; i++) {
            const p = document.createElement('div');
            p.className = 'particle';
            p.style.cssText = `
                left: ${Math.random() * 100}%;
                top: ${Math.random() * 100}%;
                background: ${colors[Math.floor(Math.random() * colors.length)]};
                --dur: ${3 + Math.random() * 5}s;
                --delay: ${-Math.random() * 8}s;
                width: ${1 + Math.random() * 2}px;
                height: ${1 + Math.random() * 2}px;
            `;
            container.appendChild(p);
        }
    })();

    /* ---- Page load bar ---- */
    window.addEventListener('load', function() {
        const bar = document.getElementById('pageLoadBar');
        if (bar) {
            bar.style.width = '100%';
            setTimeout(() => { bar.style.opacity = '0'; }, 500);
        }
    });

    /* ---- Password toggle ---- */
    let pwdVisible = false;
    function togglePassword() {
        pwdVisible = !pwdVisible;
        const inp = document.getElementById('password');
        const ico = document.getElementById('eyeIcon');
        if (!inp) return;
        inp.type = pwdVisible ? 'text' : 'password';
        if (ico) {
            ico.innerHTML = pwdVisible
                ? '<path d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>'
                : '<path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
        }
    }

    /* ---- Login handler ---- */
    async function handleLogin(e) {
        e.preventDefault();

        const email    = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        const remember = document.getElementById('remember').checked;

        if (!email || !password) {
            showError('Please enter your email and password.');
            return;
        }

        setLoading(true);
        hideAlerts();

        try {
            const res = await fetch('/api/v1/superadmin/auth/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ email, password, remember })
            });

            const data = await res.json();

            if (res.ok && data.token) {
                /* Store credentials */
                localStorage.setItem('sa_token', data.token);
                localStorage.setItem('sa_data', JSON.stringify(data.user || data.admin || {}));

                showSuccess();

                /* Redirect after short delay */
                setTimeout(() => {
                    window.location.href = '/superadmin/dashboard';
                }, 1200);

            } else {
                const msg = data.message || data.error || 'Invalid credentials. Please try again.';
                showError(msg);
                setLoading(false);
            }

        } catch(err) {
            showError('Network error. Please check your connection and try again.');
            setLoading(false);
            console.error('Login error:', err);
        }
    }

    /* ---- UI helpers ---- */
    function setLoading(on) {
        const btn     = document.getElementById('submitBtn');
        const text    = document.getElementById('btnText');
        const spinner = document.getElementById('btnSpinner');
        const arrow   = document.getElementById('btnArrow');

        if (!btn) return;
        btn.disabled = on;

        if (text)    text.textContent = on ? 'Authenticating...' : 'Access Command Center';
        if (spinner) spinner.style.display = on ? 'block' : 'none';
        if (arrow)   arrow.style.display   = on ? 'none'  : 'block';
    }

    function hideAlerts() {
        document.getElementById('alertError').classList.remove('show');
        document.getElementById('alertSuccess').classList.remove('show');
    }

    function showError(msg) {
        const el = document.getElementById('alertError');
        const tx = document.getElementById('alertErrorText');
        if (tx) tx.textContent = msg;
        if (el) el.classList.add('show');
        document.getElementById('alertSuccess').classList.remove('show');
    }

    function showSuccess() {
        document.getElementById('alertSuccess').classList.add('show');
        document.getElementById('alertError').classList.remove('show');
    }
    </script>

</body>
</html>
