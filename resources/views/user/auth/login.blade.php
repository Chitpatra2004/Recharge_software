<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <meta name="theme-color" content="#24145f" />
  <meta name="mobile-web-app-capable" content="yes" />
  <meta name="apple-mobile-web-app-capable" content="yes" />
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
  <title>Sign In - ColdPay</title>
  <link rel="icon" type="image/svg+xml" href="/favicon.svg">
  <link rel="alternate icon" href="/icons/coldpay.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { -webkit-text-size-adjust: 100%; }

    body {
      font-family: 'Inter', sans-serif;
      color: #1f2937;
      background:
        radial-gradient(circle at 18% 10%, rgba(99,102,241,.42), transparent 28%),
        radial-gradient(circle at 78% 84%, rgba(124,58,237,.38), transparent 30%),
        linear-gradient(135deg, #312783 0%, #111a45 48%, #120f2d 100%);
      min-height: 100vh;
      min-height: 100dvh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: calc(24px + env(safe-area-inset-top)) 16px calc(24px + env(safe-area-inset-bottom));
      position: relative;
      overflow-x: hidden;
      overscroll-behavior-y: none;
      -webkit-tap-highlight-color: transparent;
    }

    body::before {
      content: '';
      position: fixed;
      inset: 0;
      background:
        linear-gradient(rgba(255,255,255,.045) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,.045) 1px, transparent 1px);
      background-size: 56px 56px;
      mask-image: linear-gradient(to bottom, rgba(0,0,0,.85), rgba(0,0,0,.25));
      pointer-events: none;
      z-index: 0;
    }

    .container {
      width: 100%;
      max-width: 960px;
      position: relative;
      z-index: 1;
      display: grid;
      grid-template-columns: 1.1fr 1fr;
      min-height: 520px;
      border-radius: 28px;
      overflow: hidden;
      box-shadow: 0 34px 90px rgba(0,0,0,.38);
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 12px;
      justify-content: flex-start;
      margin-bottom: 38px;
    }

    .brand-icon {
      width: 44px;
      height: 44px;
      background:
        linear-gradient(135deg, rgba(255,255,255,.26), rgba(255,255,255,.10));
      border: 1px solid rgba(255,255,255,.22);
      border-radius: 13px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: inset 0 1px 0 rgba(255,255,255,.28);
    }

    .brand-icon svg { width: 24px; height: 24px; fill: #fff; }

    .brand-name {
      font-size: 24px;
      font-weight: 800;
      color: #fff;
      letter-spacing: -.5px;
    }

    .brand-name span { font-weight: 500; opacity: .82; }

    .mobile-brand { display: none; }

    .mobile-brand .brand-icon {
      background: linear-gradient(135deg, #5b49e8, #7c3aed);
      border: none;
      box-shadow: 0 10px 24px rgba(91,73,232,.24);
    }

    .mobile-brand .brand-name { color: #1f2937; }
    .mobile-brand .brand-name span { color: #5b49e8; opacity: 1; }

    .brand-kicker {
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 1.6px;
      text-transform: uppercase;
      color: rgba(255,255,255,.68);
      margin-top: 1px;
    }

    .hero-panel {
      position: relative;
      overflow: hidden;
      padding: 48px 40px;
      color: #fff;
      background:
        radial-gradient(circle at 92% 88%, rgba(255,255,255,.16), transparent 28%),
        linear-gradient(155deg, #5b49e8 0%, #7c3aed 46%, #2e185c 100%);
    }

    .hero-panel::before {
      content: '';
      position: absolute;
      width: 360px;
      height: 360px;
      right: -130px;
      bottom: -130px;
      border: 1px solid rgba(255,255,255,.13);
      border-radius: 50%;
    }

    .hero-panel > * { position: relative; z-index: 1; }

    .hero-title {
      font-size: 29px;
      line-height: 1.12;
      font-weight: 800;
      letter-spacing: -.7px;
      margin-bottom: 16px;
    }

    .hero-title span { color: #bfdbfe; }

    .hero-copy {
      color: rgba(255,255,255,.72);
      font-size: 14px;
      line-height: 1.65;
      max-width: 360px;
      margin-bottom: 28px;
    }

    .feature-list {
      display: grid;
      gap: 14px;
      margin-bottom: 34px;
    }

    .feature-item {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 13.5px;
      font-weight: 650;
      color: rgba(255,255,255,.92);
    }

    .feature-icon {
      width: 24px;
      height: 24px;
      border-radius: 8px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: rgba(255,255,255,.13);
      color: #fff;
    }

    .feature-icon svg { width: 14px; height: 14px; }

    .metric-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
    }

    .metric-card {
      padding: 14px 16px;
      border-radius: 12px;
      background: rgba(255,255,255,.10);
      border: 1px solid rgba(255,255,255,.14);
    }

    .metric-value {
      font-size: 22px;
      font-weight: 800;
      line-height: 1;
    }

    .metric-label {
      margin-top: 6px;
      font-size: 11px;
      font-weight: 700;
      color: rgba(255,255,255,.58);
    }

    .card {
      position: relative;
      overflow: hidden;
      background: #f8fafc;
      border: none;
      border-radius: 0;
      backdrop-filter: none;
      -webkit-backdrop-filter: none;
      padding: 82px 44px 40px;
      box-shadow: none;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .card::before {
      display: none;
    }

    .card > * { position: relative; z-index: 1; }

    @media (max-width: 480px) { .card { padding: 28px 20px; } }

    .card-title {
      font-size: 22px;
      font-weight: 800;
      color: #1f2937;
      margin-bottom: 4px;
    }

    .card-subtitle {
      font-size: 14px;
      color: #64748b;
      margin-bottom: 28px;
    }

    .alert {
      padding: 12px 16px;
      border-radius: 10px;
      font-size: 13.5px;
      font-weight: 500;
      margin-bottom: 20px;
      display: none;
      align-items: center;
      gap: 10px;
    }

    .alert.show { display: flex; }

    .alert-success {
      background: #ecfdf5;
      border: 1px solid #a7f3d0;
      color: #047857;
    }

    .alert-error {
      background: #fef2f2;
      border: 1px solid #fecaca;
      color: #dc2626;
    }

    .form-group { margin-bottom: 18px; }

    .form-label {
      display: block;
      font-size: 13px;
      font-weight: 500;
      color: #334155;
      margin-bottom: 6px;
    }

    .input-wrap { position: relative; }

    .form-input {
      width: 100%;
      background: #f8fafc;
      border: 1px solid #dbe3ef;
      border-radius: 12px;
      color: #111827;
      font-family: 'Inter', sans-serif;
      font-size: 14px;
      padding: 11px 14px;
      outline: none;
      transition: border-color .2s, box-shadow .2s;
    }

    .form-input::placeholder { color: #94a3b8; }

    .form-input:focus {
      border-color: #6d5df6;
      box-shadow: 0 0 0 4px rgba(109,93,246,.12);
      background: #fff;
    }

    .form-input.is-error {
      border-color: rgba(239,68,68,.5);
      box-shadow: 0 0 0 3px rgba(239,68,68,.1);
    }

    .form-input.pr { padding-right: 44px; }

    .toggle-btn {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      cursor: pointer;
      color: #94a3b8;
      padding: 4px;
      display: flex;
      align-items: center;
      transition: color .2s;
    }

    .toggle-btn:hover { color: #6d5df6; }
    .toggle-btn svg { width: 18px; height: 18px; }

    .field-error {
      font-size: 12px;
      color: #dc2626;
      margin-top: 5px;
      display: none;
    }

    .field-error.show { display: block; }

    .forgot-row {
      display: flex;
      justify-content: flex-end;
      margin-top: 6px;
    }

    .forgot-link {
      font-size: 12.5px;
      color: #64748b;
      text-decoration: none;
      transition: color .2s;
    }

    .forgot-link:hover { color: #5b49e8; }

    .btn-primary {
      width: 100%;
      padding: 13px;
      background: linear-gradient(135deg, #5b49e8 0%, #7c3aed 100%);
      border: none;
      border-radius: 13px;
      color: #fff;
      font-family: 'Inter', sans-serif;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      transition: filter .2s, transform .15s, box-shadow .2s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      box-shadow: 0 14px 34px rgba(91,73,232,.28);
      margin-top: 8px;
    }

    .btn-primary:hover { filter: brightness(1.04); transform: translateY(-1px); box-shadow: 0 18px 42px rgba(91,73,232,.34); }
    .btn-primary:active { transform: translateY(0); }
    .btn-primary:disabled { opacity: .55; cursor: not-allowed; transform: none; }

    .spinner {
      width: 18px;
      height: 18px;
      border: 2px solid rgba(255,255,255,.3);
      border-top-color: #fff;
      border-radius: 50%;
      animation: spin .7s linear infinite;
      display: none;
    }

    @keyframes spin { to { transform: rotate(360deg); } }

    .divider {
      border: none;
      border-top: 1px solid #e2e8f0;
      margin: 22px 0;
    }

    .footer-link {
      text-align: center;
      font-size: 13.5px;
      color: #64748b;
    }

    .footer-link a {
      color: #5b49e8;
      text-decoration: none;
      font-weight: 700;
    }

    .footer-link a:hover { text-decoration: underline; }

    .seller-modal {
      position: fixed;
      inset: 0;
      z-index: 50;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 18px;
      background: rgba(15,23,42,.55);
      backdrop-filter: blur(8px);
    }

    .seller-modal.show { display: flex; }

    .seller-modal-card {
      width: 100%;
      max-width: 420px;
      background: #fff;
      border-radius: 18px;
      padding: 24px;
      box-shadow: 0 24px 70px rgba(15,23,42,.28);
      border: 1px solid rgba(148,163,184,.25);
    }

    .seller-modal-title {
      font-size: 18px;
      font-weight: 800;
      color: #0f172a;
      margin-bottom: 8px;
    }

    .seller-modal-text {
      font-size: 13.5px;
      line-height: 1.55;
      color: #475569;
      margin-bottom: 18px;
    }

    .seller-modal-actions {
      display: flex;
      gap: 10px;
      justify-content: flex-end;
      flex-wrap: wrap;
    }

    .seller-modal-btn {
      border: 0;
      border-radius: 10px;
      padding: 10px 14px;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }

    .seller-modal-btn.primary { background: #5b49e8; color: #fff; }
    .seller-modal-btn.secondary { background: #f1f5f9; color: #334155; }

    @media (max-width: 760px) {
      body {
        align-items: stretch;
        padding: calc(14px + env(safe-area-inset-top)) 12px calc(14px + env(safe-area-inset-bottom));
      }

      .container {
        min-height: calc(100dvh - 28px - env(safe-area-inset-top) - env(safe-area-inset-bottom));
        display: flex;
        flex-direction: column;
        justify-content: center;
        max-width: 440px;
        border-radius: 22px;
      }

      .hero-panel { display: none; }

      .mobile-brand { display: flex; margin-bottom: 24px; justify-content: center; }

      .card {
        border-radius: 18px;
        padding: 30px 20px;
      }

      .card-title {
        font-size: 20px;
      }

      .form-input {
        min-height: 46px;
        font-size: 16px;
      }

      .btn-primary {
        min-height: 48px;
      }
    }
  </style>
</head>
<body>

<div class="container">
  <section class="hero-panel">
    <div class="brand">
      <div class="brand-icon">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
        </svg>
      </div>
      <div>
        <div class="brand-name">Cold<span>Pay</span></div>
        <div class="brand-kicker">Retailer Portal</div>
      </div>
    </div>

    <div class="hero-title">Smart Recharge <span>Retailer Platform</span></div>
    <div class="hero-copy">Manage mobile recharge, DTH, bill payments, wallet activity, and reports from one secure dashboard.</div>

    <div class="feature-list">
      <div class="feature-item">
        <span class="feature-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 19V9m5 10V5m5 14v-7m5 7V3"/></svg></span>
        Live recharge and wallet tracking
      </div>
      <div class="feature-item">
        <span class="feature-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg></span>
        Fast operator routing and status updates
      </div>
      <div class="feature-item">
        <span class="feature-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 10v2"/></svg></span>
        Wallet, commission, and reports
      </div>
      <div class="feature-item">
        <span class="feature-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 11c0-1.105.895-2 2-2h4m-6 2v8m0-8H8a2 2 0 00-2 2v6"/></svg></span>
        Secure app-style retailer access
      </div>
    </div>

    <div class="metric-grid">
      <div class="metric-card"><div class="metric-value">24x7</div><div class="metric-label">Availability</div></div>
      <div class="metric-card"><div class="metric-value">Live</div><div class="metric-label">Status</div></div>
      <div class="metric-card"><div class="metric-value">Fast</div><div class="metric-label">Recharge</div></div>
      <div class="metric-card"><div class="metric-value">Safe</div><div class="metric-label">Payments</div></div>
    </div>
  </section>

  <div class="card">
    <div class="brand mobile-brand">
      <div class="brand-icon">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
        </svg>
      </div>
      <div class="brand-name">Cold<span>Pay</span></div>
    </div>
    <div class="card-title">Welcome back</div>
    <div class="card-subtitle">Sign in to your ColdPay account</div>

    <!-- Registration success banner -->
    <div class="alert alert-success" id="registeredBanner">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <span>Registration submitted! Your account is <strong>pending admin approval</strong>. You will be notified via email once approved, then you can login.</span>
    </div>

    <div class="alert alert-error" id="alertError">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <span id="alertErrorText"></span>
    </div>

    <form id="loginForm" novalidate>

      <div class="form-group">
        <label class="form-label" for="login">Email or Mobile Number</label>
        <div class="input-wrap">
          <input
            type="text"
            id="login"
            name="login"
            class="form-input"
            placeholder="Enter email or 10-digit mobile"
            autocomplete="username"
          />
        </div>
        <div class="field-error" id="loginError"></div>
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <div class="input-wrap">
          <input
            type="password"
            id="password"
            name="password"
            class="form-input pr"
            placeholder="Your password"
            autocomplete="current-password"
          />
          <button type="button" class="toggle-btn" id="togglePassword" aria-label="Toggle password visibility">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
          </button>
        </div>
        <div class="field-error" id="passwordError"></div>
        <div class="forgot-row">
          <a href="/user/forgot-password" class="forgot-link">Forgot Password?</a>
        </div>
      </div>

      <button type="submit" class="btn-primary" id="submitBtn">
        <div class="spinner" id="spinner"></div>
        <span id="submitText">Sign In to Dashboard</span>
      </button>

    </form>

    <hr class="divider" />
    <div class="footer-link">Don't have an account? <a href="/user/register">Register</a></div>
  </div>
</div>

<div class="seller-modal" id="sellerAccountModal">
  <div class="seller-modal-card">
    <div class="seller-modal-title">Seller Account Detected</div>
    <div class="seller-modal-text">
      This ID belongs to a seller account. Please log in from the Seller Portal to access your seller dashboard.
    </div>
    <div class="seller-modal-actions">
      <button type="button" class="seller-modal-btn secondary" onclick="closeSellerModal()">Close</button>
      <a class="seller-modal-btn primary" href="/seller/login">Open Seller Portal</a>
    </div>
  </div>
</div>

<script>
  (function () {
    'use strict';

    // Redirect if already authenticated
    if (localStorage.getItem('user_token')) {
      window.location.replace('/user/dashboard');
    }

    // Show registration success banner if redirected from register page
    var urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('registered') === '1') {
      document.getElementById('registeredBanner').classList.add('show');
    }

    var CSRF = '{{ csrf_token() }}';

    /* ---- Eye toggle ---- */
    var toggleBtn = document.getElementById('togglePassword');
    var pwdInput  = document.getElementById('password');
    var eyeOpen   = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>';
    var eyeClosed = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>';

    toggleBtn.addEventListener('click', function () {
      if (pwdInput.type === 'password') {
        pwdInput.type = 'text';
        toggleBtn.innerHTML = eyeClosed;
      } else {
        pwdInput.type = 'password';
        toggleBtn.innerHTML = eyeOpen;
      }
    });

    /* ---- Error helpers ---- */
    function showFieldError(inputId, msg) {
      var inp = document.getElementById(inputId);
      var err = document.getElementById(inputId + 'Error');
      if (inp) inp.classList.add('is-error');
      if (err) { err.textContent = msg; err.classList.add('show'); }
    }

    function clearErrors() {
      ['login','password'].forEach(function (id) {
        var inp = document.getElementById(id);
        var err = document.getElementById(id + 'Error');
        if (inp) inp.classList.remove('is-error');
        if (err) { err.textContent = ''; err.classList.remove('show'); }
      });
      var alertEl = document.getElementById('alertError');
      alertEl.classList.remove('show');
    }

    window.closeSellerModal = function () {
      document.getElementById('sellerAccountModal').classList.remove('show');
    };

    function showSellerModal() {
      document.getElementById('sellerAccountModal').classList.add('show');
    }

    /* ---- Form submit ---- */
    document.getElementById('loginForm').addEventListener('submit', function (e) {
      e.preventDefault();
      clearErrors();

      var loginVal = document.getElementById('login').value.trim();
      var passVal  = document.getElementById('password').value;

      if (!loginVal) {
        showFieldError('login', 'Please enter your email or mobile number');
        return;
      }
      if (!passVal) {
        showFieldError('password', 'Please enter your password');
        return;
      }

      var submitBtn  = document.getElementById('submitBtn');
      var spinner    = document.getElementById('spinner');
      var submitText = document.getElementById('submitText');

      submitBtn.disabled = true;
      spinner.style.display = 'block';
      submitText.textContent = 'Signing in...';

      fetch('/api/v1/auth/login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': CSRF,
        },
        body: JSON.stringify({
          login: loginVal,
          password: passVal,
          device_name: 'web',
        }),
      })
      .then(function (res) {
        return res.json().then(function (data) { return { status: res.status, data: data }; });
      })
      .then(function (result) {
        var status = result.status;
        var data   = result.data;

        if (data.code === 'SELLER_PORTAL_REQUIRED') {
          showSellerModal();
          return;
        }

        if (data.requires_2fa) {
          // 2FA required
          sessionStorage.setItem('pending_2fa_token',  data.pending_token || '');
          sessionStorage.setItem('pending_2fa_method', data.method || 'otp');

          if (data.method === 'totp') {
            window.location.href = '/user/otp';
          } else {
            window.location.href = '/user/otp';
          }
          return;
        }

        if (status >= 200 && status < 300 && data.token) {
          localStorage.setItem('user_token', data.token);
          localStorage.setItem('user_data',  JSON.stringify(data.user || {}));
          window.location.href = '/user/dashboard';
          return;
        }

        // Handle errors
        var msg = data.message || 'Login failed. Please try again.';

        if (msg === 'User not found.' || (data.errors && data.errors.login)) {
          showFieldError('login', msg === 'User not found.' ? 'User not found.' : data.errors.login[0]);
        } else if (msg === 'Incorrect password.' || (data.errors && data.errors.password)) {
          showFieldError('password', msg === 'Incorrect password.' ? 'Incorrect password.' : data.errors.password[0]);
        } else {
          document.getElementById('alertErrorText').textContent = msg;
          document.getElementById('alertError').classList.add('show');
        }
      })
      .catch(function () {
        document.getElementById('alertErrorText').textContent = 'Network error. Please try again.';
        document.getElementById('alertError').classList.add('show');
      })
      .finally(function () {
        submitBtn.disabled = false;
        spinner.style.display = 'none';
        submitText.textContent = 'Sign In to Dashboard';
      });
    });

  })();
</script>
</body>
</html>
