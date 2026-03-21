<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Forgot Password — RechargeHub</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Inter', sans-serif;
      background: #040d21;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px 16px;
      position: relative;
      overflow-x: hidden;
    }

    body::before {
      content: '';
      position: fixed;
      inset: 0;
      background:
        radial-gradient(ellipse 80% 50% at 20% 10%, rgba(5,150,105,.18) 0%, transparent 60%),
        radial-gradient(ellipse 60% 40% at 80% 90%, rgba(16,185,129,.12) 0%, transparent 55%),
        radial-gradient(ellipse 40% 30% at 60% 40%, rgba(6,182,212,.08) 0%, transparent 50%);
      pointer-events: none;
      z-index: 0;
    }

    .container {
      width: 100%;
      max-width: 420px;
      position: relative;
      z-index: 1;
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 12px;
      justify-content: center;
      margin-bottom: 32px;
    }

    .brand-icon {
      width: 44px;
      height: 44px;
      background: linear-gradient(135deg, #059669, #06b6d4);
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 0 20px rgba(5,150,105,.4);
    }

    .brand-icon svg { width: 24px; height: 24px; fill: #fff; }

    .brand-name {
      font-size: 24px;
      font-weight: 700;
      color: #fff;
      letter-spacing: -.5px;
    }

    .brand-name span { font-weight: 400; opacity: .8; }

    .card {
      background: rgba(255,255,255,.04);
      border: 1px solid rgba(255,255,255,.09);
      border-radius: 20px;
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      padding: 36px 40px;
    }

    @media (max-width: 480px) { .card { padding: 28px 20px; } }

    /* Step containers */
    .step {
      transition: opacity .3s, transform .3s;
    }

    .step.hidden {
      display: none;
    }

    .step-icon {
      width: 52px;
      height: 52px;
      background: linear-gradient(135deg, rgba(5,150,105,.2), rgba(16,185,129,.15));
      border: 1px solid rgba(16,185,129,.25);
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 20px;
    }

    .step-icon svg { width: 26px; height: 26px; stroke: #10b981; fill: none; }

    .card-title {
      font-size: 21px;
      font-weight: 700;
      color: #fff;
      margin-bottom: 6px;
    }

    .card-subtitle {
      font-size: 13.5px;
      color: rgba(255,255,255,.45);
      margin-bottom: 26px;
      line-height: 1.6;
    }

    .alert {
      padding: 12px 16px;
      border-radius: 10px;
      font-size: 13.5px;
      font-weight: 500;
      margin-bottom: 18px;
      display: none;
      align-items: center;
      gap: 10px;
    }

    .alert.show { display: flex; }

    .alert-success {
      background: rgba(16,185,129,.15);
      border: 1px solid rgba(16,185,129,.3);
      color: #6ee7b7;
    }

    .alert-error {
      background: rgba(239,68,68,.12);
      border: 1px solid rgba(239,68,68,.25);
      color: #fca5a5;
    }

    .form-group { margin-bottom: 18px; }

    .form-label {
      display: block;
      font-size: 13px;
      font-weight: 500;
      color: rgba(255,255,255,.7);
      margin-bottom: 6px;
    }

    .input-wrap { position: relative; }

    .form-input {
      width: 100%;
      background: rgba(255,255,255,.06);
      border: 1px solid rgba(255,255,255,.1);
      border-radius: 10px;
      color: #fff;
      font-family: 'Inter', sans-serif;
      font-size: 14px;
      padding: 11px 14px;
      outline: none;
      transition: border-color .2s, box-shadow .2s;
    }

    .form-input::placeholder { color: rgba(255,255,255,.25); }

    .form-input:focus {
      border-color: rgba(16,185,129,.6);
      box-shadow: 0 0 0 3px rgba(16,185,129,.12);
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
      color: rgba(255,255,255,.4);
      padding: 4px;
      display: flex;
      align-items: center;
      transition: color .2s;
    }

    .toggle-btn:hover { color: rgba(255,255,255,.7); }
    .toggle-btn svg { width: 18px; height: 18px; }

    .field-error {
      font-size: 12px;
      color: #fca5a5;
      margin-top: 5px;
      display: none;
    }

    .field-error.show { display: block; }

    /* OTP boxes */
    .otp-row {
      display: flex;
      gap: 10px;
      justify-content: center;
      margin-bottom: 24px;
    }

    .otp-box {
      width: 46px;
      height: 54px;
      background: rgba(255,255,255,.06);
      border: 1.5px solid rgba(255,255,255,.12);
      border-radius: 12px;
      color: #fff;
      font-family: 'Inter', sans-serif;
      font-size: 20px;
      font-weight: 700;
      text-align: center;
      outline: none;
      transition: border-color .2s, box-shadow .2s;
      caret-color: #10b981;
    }

    .otp-box:focus {
      border-color: rgba(16,185,129,.7);
      box-shadow: 0 0 0 3px rgba(16,185,129,.15);
    }

    .otp-box.is-error {
      border-color: rgba(239,68,68,.6);
      box-shadow: 0 0 0 3px rgba(239,68,68,.12);
    }

    /* Password strength */
    .strength-bar {
      display: flex;
      gap: 4px;
      margin-top: 8px;
    }

    .strength-seg {
      flex: 1;
      height: 3px;
      border-radius: 99px;
      background: rgba(255,255,255,.1);
      transition: background .3s;
    }

    .strength-seg.weak   { background: #ef4444; }
    .strength-seg.fair   { background: #f59e0b; }
    .strength-seg.good   { background: #3b82f6; }
    .strength-seg.strong { background: #10b981; }

    .strength-rules {
      margin-top: 10px;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 5px 12px;
    }

    .rule {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 11.5px;
      color: rgba(255,255,255,.35);
      transition: color .2s;
    }

    .rule.met { color: #6ee7b7; }

    .rule-icon {
      width: 14px;
      height: 14px;
      border-radius: 50%;
      border: 1.5px solid rgba(255,255,255,.2);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      transition: background .2s, border-color .2s;
    }

    .rule.met .rule-icon { background: #10b981; border-color: #10b981; }
    .rule-icon svg { width: 8px; height: 8px; fill: #fff; opacity: 0; transition: opacity .2s; }
    .rule.met .rule-icon svg { opacity: 1; }

    /* Buttons */
    .btn-primary {
      width: 100%;
      padding: 13px;
      background: linear-gradient(135deg, #059669, #10b981);
      border: none;
      border-radius: 10px;
      color: #fff;
      font-family: 'Inter', sans-serif;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      transition: opacity .2s, transform .15s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      box-shadow: 0 4px 15px rgba(16,185,129,.3);
    }

    .btn-primary:hover { opacity: .92; transform: translateY(-1px); }
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

    .back-link {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      margin-top: 22px;
      font-size: 13.5px;
      color: rgba(255,255,255,.35);
      text-decoration: none;
      transition: color .2s;
    }

    .back-link:hover { color: rgba(255,255,255,.7); }
    .back-link svg { width: 14px; height: 14px; }

    /* Step indicator dots */
    .step-dots {
      display: flex;
      gap: 6px;
      justify-content: center;
      margin-bottom: 28px;
    }

    .step-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: rgba(255,255,255,.15);
      transition: background .3s, transform .3s;
    }

    .step-dot.active {
      background: #10b981;
      transform: scale(1.2);
    }

    .step-dot.done {
      background: rgba(16,185,129,.4);
    }
  </style>
</head>
<body>

<div class="container">
  <div class="brand">
    <div class="brand-icon">
      <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
      </svg>
    </div>
    <div class="brand-name">Recharge<span>Hub</span></div>
  </div>

  <div class="card">

    <!-- Step dots -->
    <div class="step-dots">
      <div class="step-dot active" id="dot1"></div>
      <div class="step-dot" id="dot2"></div>
      <div class="step-dot" id="dot3"></div>
    </div>

    <!-- ===================== STEP 1: Identifier ===================== -->
    <div class="step" id="step1">
      <div class="step-icon">
        <svg viewBox="0 0 24 24" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
        </svg>
      </div>
      <div class="card-title">Forgot Password?</div>
      <div class="card-subtitle">Enter your email or mobile number and we'll send you an OTP to reset your password.</div>

      <div class="alert alert-error" id="s1AlertError">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span id="s1AlertErrorText"></span>
      </div>

      <div class="form-group">
        <label class="form-label" for="identifier">Email or Mobile Number</label>
        <div class="input-wrap">
          <input type="text" id="identifier" class="form-input" placeholder="Enter email or 10-digit mobile" autocomplete="username" />
        </div>
        <div class="field-error" id="identifierError"></div>
      </div>

      <button type="button" class="btn-primary" id="sendOtpBtn">
        <div class="spinner" id="s1Spinner"></div>
        <span id="sendOtpText">Send OTP</span>
      </button>

      <a href="/user/login" class="back-link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
        </svg>
        Back to Login
      </a>
    </div>

    <!-- ===================== STEP 2: Verify OTP ===================== -->
    <div class="step hidden" id="step2">
      <div class="step-icon">
        <svg viewBox="0 0 24 24" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/>
        </svg>
      </div>
      <div class="card-title">Enter OTP</div>
      <div class="card-subtitle">OTP sent to <strong id="maskedIdentifier" style="color:rgba(255,255,255,.7);"></strong></div>

      <div class="alert alert-error" id="s2AlertError">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span id="s2AlertErrorText"></span>
      </div>

      <div class="otp-row" id="s2OtpRow">
        <input class="otp-box" type="text" inputmode="numeric" maxlength="1" data-step2-index="0" autocomplete="one-time-code" />
        <input class="otp-box" type="text" inputmode="numeric" maxlength="1" data-step2-index="1" />
        <input class="otp-box" type="text" inputmode="numeric" maxlength="1" data-step2-index="2" />
        <input class="otp-box" type="text" inputmode="numeric" maxlength="1" data-step2-index="3" />
        <input class="otp-box" type="text" inputmode="numeric" maxlength="1" data-step2-index="4" />
        <input class="otp-box" type="text" inputmode="numeric" maxlength="1" data-step2-index="5" />
      </div>

      <button type="button" class="btn-primary" id="verifyOtpBtn">
        <div class="spinner" id="s2Spinner"></div>
        <span id="verifyOtpText">Verify OTP</span>
      </button>

      <a href="/user/login" class="back-link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
        </svg>
        Back to Login
      </a>
    </div>

    <!-- ===================== STEP 3: New Password ===================== -->
    <div class="step hidden" id="step3">
      <div class="step-icon">
        <svg viewBox="0 0 24 24" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
        </svg>
      </div>
      <div class="card-title">Set New Password</div>
      <div class="card-subtitle">Create a strong password for your account.</div>

      <div class="alert alert-success" id="s3AlertSuccess">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span id="s3AlertSuccessText"></span>
      </div>

      <div class="alert alert-error" id="s3AlertError">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span id="s3AlertErrorText"></span>
      </div>

      <div class="form-group">
        <label class="form-label" for="newPassword">New Password</label>
        <div class="input-wrap">
          <input type="password" id="newPassword" class="form-input pr" placeholder="Create a strong password" autocomplete="new-password" />
          <button type="button" class="toggle-btn" id="toggleNewPwd" aria-label="Toggle password">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
          </button>
        </div>

        <div class="strength-bar">
          <div class="strength-seg" id="rSeg1"></div>
          <div class="strength-seg" id="rSeg2"></div>
          <div class="strength-seg" id="rSeg3"></div>
          <div class="strength-seg" id="rSeg4"></div>
          <div class="strength-seg" id="rSeg5"></div>
        </div>

        <div class="strength-rules">
          <div class="rule" id="rRuleLen">
            <div class="rule-icon"><svg viewBox="0 0 8 8"><path d="M1.5 4l2 2 3-3" stroke="#fff" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
            Min 8 characters
          </div>
          <div class="rule" id="rRuleUpper">
            <div class="rule-icon"><svg viewBox="0 0 8 8"><path d="M1.5 4l2 2 3-3" stroke="#fff" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
            Uppercase letter
          </div>
          <div class="rule" id="rRuleLower">
            <div class="rule-icon"><svg viewBox="0 0 8 8"><path d="M1.5 4l2 2 3-3" stroke="#fff" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
            Lowercase letter
          </div>
          <div class="rule" id="rRuleNum">
            <div class="rule-icon"><svg viewBox="0 0 8 8"><path d="M1.5 4l2 2 3-3" stroke="#fff" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
            Number (0–9)
          </div>
          <div class="rule" id="rRuleSymbol">
            <div class="rule-icon"><svg viewBox="0 0 8 8"><path d="M1.5 4l2 2 3-3" stroke="#fff" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
            Special symbol
          </div>
        </div>

        <div class="field-error" id="newPasswordError"></div>
      </div>

      <div class="form-group">
        <label class="form-label" for="newPasswordConfirm">Confirm New Password</label>
        <div class="input-wrap">
          <input type="password" id="newPasswordConfirm" class="form-input pr" placeholder="Repeat your new password" autocomplete="new-password" />
          <button type="button" class="toggle-btn" id="toggleNewPwdConfirm" aria-label="Toggle confirm password">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
          </button>
        </div>
        <div class="field-error" id="newPasswordConfirmError"></div>
      </div>

      <button type="button" class="btn-primary" id="resetBtn">
        <div class="spinner" id="s3Spinner"></div>
        <span id="resetText">Reset Password</span>
      </button>

      <a href="/user/login" class="back-link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
        </svg>
        Back to Login
      </a>
    </div>

  </div>
</div>

<script>
  (function () {
    'use strict';

    var CSRF       = '{{ csrf_token() }}';
    var identifier = '';   // stored after step 1 success
    var resetToken = '';   // stored after step 2 success

    /* ---- Step navigation ---- */
    function showStep(n) {
      [1, 2, 3].forEach(function (i) {
        var el = document.getElementById('step' + i);
        var dot = document.getElementById('dot' + i);
        if (i === n) {
          el.classList.remove('hidden');
          dot.className = 'step-dot active';
        } else if (i < n) {
          el.classList.add('hidden');
          dot.className = 'step-dot done';
        } else {
          el.classList.add('hidden');
          dot.className = 'step-dot';
        }
      });
    }

    /* ---- Error helpers ---- */
    function setFieldError(inputId, errorId, msg) {
      var inp = document.getElementById(inputId);
      var err = document.getElementById(errorId);
      if (inp) inp.classList.add('is-error');
      if (err) { err.textContent = msg; err.classList.add('show'); }
    }

    function clearFieldError(inputId, errorId) {
      var inp = document.getElementById(inputId);
      var err = document.getElementById(errorId);
      if (inp) inp.classList.remove('is-error');
      if (err) { err.textContent = ''; err.classList.remove('show'); }
    }

    function showAlert(alertId, textId, msg) {
      document.getElementById(textId).textContent = msg;
      document.getElementById(alertId).classList.add('show');
    }

    function hideAlert(alertId) {
      document.getElementById(alertId).classList.remove('show');
    }

    /* ---- Mask identifier ---- */
    function maskIdentifier(val) {
      if (/^\d{10}$/.test(val)) {
        return 'XXXXXXXX' + val.slice(-2);
      }
      var parts = val.split('@');
      if (parts.length === 2) {
        var name = parts[0];
        var masked = name.length > 2
          ? name[0] + '***' + name[name.length - 1]
          : '***';
        return masked + '@' + parts[1];
      }
      return '****';
    }

    /* ============================================================
       STEP 1 — Send OTP
    ============================================================ */
    document.getElementById('sendOtpBtn').addEventListener('click', function () {
      clearFieldError('identifier', 'identifierError');
      hideAlert('s1AlertError');

      var val = document.getElementById('identifier').value.trim();
      if (!val) {
        setFieldError('identifier', 'identifierError', 'Please enter your email or mobile number');
        return;
      }

      var btn  = document.getElementById('sendOtpBtn');
      var spin = document.getElementById('s1Spinner');
      var txt  = document.getElementById('sendOtpText');
      btn.disabled = true; spin.style.display = 'block'; txt.textContent = 'Sending...';

      fetch('/api/v1/auth/forgot-password', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': CSRF,
        },
        body: JSON.stringify({ identifier: val }),
      })
      .then(function (res) {
        return res.json().then(function (d) { return { status: res.status, data: d }; });
      })
      .then(function (result) {
        var data = result.data;
        if (result.status >= 200 && result.status < 300) {
          identifier = data.identifier || val;
          document.getElementById('maskedIdentifier').textContent = maskIdentifier(identifier);
          showStep(2);
          // focus first OTP box
          var s2Boxes = document.querySelectorAll('[data-step2-index]');
          if (s2Boxes.length) s2Boxes[0].focus();
        } else {
          var msg = (data && data.message) ? data.message : 'Failed to send OTP.';
          showAlert('s1AlertError', 's1AlertErrorText', msg);
        }
      })
      .catch(function () {
        showAlert('s1AlertError', 's1AlertErrorText', 'Network error. Please try again.');
      })
      .finally(function () {
        btn.disabled = false; spin.style.display = 'none'; txt.textContent = 'Send OTP';
      });
    });

    /* ============================================================
       STEP 2 — OTP boxes
    ============================================================ */
    var s2Boxes = Array.from(document.querySelectorAll('[data-step2-index]'));

    s2Boxes.forEach(function (box, idx) {
      box.addEventListener('keydown', function (e) {
        if (e.key === 'Backspace' && !box.value && idx > 0) {
          s2Boxes[idx - 1].focus();
          s2Boxes[idx - 1].value = '';
        }
      });
      box.addEventListener('input', function () {
        var val = box.value.replace(/\D/g, '');
        box.value = val ? val[val.length - 1] : '';
        if (box.value && idx < 5) s2Boxes[idx + 1].focus();
        if (idx === 5 && box.value) verifyOtp();
      });
      box.addEventListener('paste', function (e) {
        e.preventDefault();
        var text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
        text.split('').forEach(function (ch, i) { if (s2Boxes[i]) s2Boxes[i].value = ch; });
        var last = Math.min(text.length - 1, 5);
        s2Boxes[last].focus();
        if (text.length === 6) verifyOtp();
      });
    });

    function getS2Otp() {
      return s2Boxes.map(function (b) { return b.value; }).join('');
    }

    function verifyOtp() {
      var otp = getS2Otp();
      if (otp.length < 6) {
        s2Boxes.forEach(function (b) { b.classList.add('is-error'); });
        showAlert('s2AlertError', 's2AlertErrorText', 'Please enter all 6 digits.');
        return;
      }
      s2Boxes.forEach(function (b) { b.classList.remove('is-error'); });
      hideAlert('s2AlertError');

      var btn  = document.getElementById('verifyOtpBtn');
      var spin = document.getElementById('s2Spinner');
      var txt  = document.getElementById('verifyOtpText');
      btn.disabled = true; spin.style.display = 'block'; txt.textContent = 'Verifying...';

      fetch('/api/v1/auth/forgot-password/verify-otp', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': CSRF,
        },
        body: JSON.stringify({ mobile: identifier, otp: otp }),
      })
      .then(function (res) {
        return res.json().then(function (d) { return { status: res.status, data: d }; });
      })
      .then(function (result) {
        var data = result.data;
        if (result.status >= 200 && result.status < 300) {
          resetToken = data.reset_token || '';
          showStep(3);
          document.getElementById('newPassword').focus();
        } else {
          s2Boxes.forEach(function (b) { b.classList.add('is-error'); });
          var msg = (data && data.message) ? data.message : 'Invalid OTP. Please try again.';
          showAlert('s2AlertError', 's2AlertErrorText', msg);
          s2Boxes.forEach(function (b) { b.value = ''; });
          s2Boxes[0].focus();
        }
      })
      .catch(function () {
        showAlert('s2AlertError', 's2AlertErrorText', 'Network error. Please try again.');
      })
      .finally(function () {
        btn.disabled = false; spin.style.display = 'none'; txt.textContent = 'Verify OTP';
      });
    }

    document.getElementById('verifyOtpBtn').addEventListener('click', verifyOtp);

    /* ============================================================
       STEP 3 — Password strength + Reset
    ============================================================ */
    var rRuleChecks = {
      rRuleLen:    function (v) { return v.length >= 8; },
      rRuleUpper:  function (v) { return /[A-Z]/.test(v); },
      rRuleLower:  function (v) { return /[a-z]/.test(v); },
      rRuleNum:    function (v) { return /[0-9]/.test(v); },
      rRuleSymbol: function (v) { return /[^A-Za-z0-9]/.test(v); },
    };
    var rSegs = ['rSeg1','rSeg2','rSeg3','rSeg4','rSeg5'];
    var rColors = ['','weak','fair','fair','good','strong'];

    document.getElementById('newPassword').addEventListener('input', function () {
      var v = this.value;
      var score = 0;
      Object.keys(rRuleChecks).forEach(function (id) {
        var el = document.getElementById(id);
        if (rRuleChecks[id](v)) { el.classList.add('met'); score++; }
        else { el.classList.remove('met'); }
      });
      var cls = rColors[score];
      rSegs.forEach(function (id, i) {
        var seg = document.getElementById(id);
        seg.className = 'strength-seg';
        if (i < score) seg.classList.add(cls);
      });
    });

    // Eye toggles for step 3
    function setupToggle(btnId, inputId) {
      var btn = document.getElementById(btnId);
      var inp = document.getElementById(inputId);
      var open   = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>';
      var closed = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>';
      btn.addEventListener('click', function () {
        if (inp.type === 'password') { inp.type = 'text'; btn.innerHTML = closed; }
        else { inp.type = 'password'; btn.innerHTML = open; }
      });
    }

    setupToggle('toggleNewPwd', 'newPassword');
    setupToggle('toggleNewPwdConfirm', 'newPasswordConfirm');

    document.getElementById('resetBtn').addEventListener('click', function () {
      clearFieldError('newPassword', 'newPasswordError');
      clearFieldError('newPasswordConfirm', 'newPasswordConfirmError');
      hideAlert('s3AlertError');
      hideAlert('s3AlertSuccess');

      var pwd  = document.getElementById('newPassword').value;
      var cpwd = document.getElementById('newPasswordConfirm').value;
      var ok   = true;

      if (pwd.length < 8) {
        setFieldError('newPassword', 'newPasswordError', 'Password must be at least 8 characters');
        ok = false;
      }
      if (pwd !== cpwd) {
        setFieldError('newPasswordConfirm', 'newPasswordConfirmError', 'Passwords do not match');
        ok = false;
      }
      if (!ok) return;

      var btn  = document.getElementById('resetBtn');
      var spin = document.getElementById('s3Spinner');
      var txt  = document.getElementById('resetText');
      btn.disabled = true; spin.style.display = 'block'; txt.textContent = 'Resetting...';

      fetch('/api/v1/auth/forgot-password/reset', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': CSRF,
        },
        body: JSON.stringify({
          reset_token: resetToken,
          password: pwd,
          password_confirmation: cpwd,
        }),
      })
      .then(function (res) {
        return res.json().then(function (d) { return { status: res.status, data: d }; });
      })
      .then(function (result) {
        var data = result.data;
        if (result.status >= 200 && result.status < 300) {
          showAlert('s3AlertSuccess', 's3AlertSuccessText', 'Password reset successful! Redirecting to login...');
          setTimeout(function () { window.location.href = '/user/login'; }, 2000);
        } else {
          if (data.errors) {
            Object.keys(data.errors).forEach(function (field) {
              var msgs = data.errors[field];
              if (field === 'password') setFieldError('newPassword', 'newPasswordError', Array.isArray(msgs) ? msgs[0] : msgs);
              if (field === 'password_confirmation') setFieldError('newPasswordConfirm', 'newPasswordConfirmError', Array.isArray(msgs) ? msgs[0] : msgs);
            });
          }
          var msg = (data && data.message) ? data.message : 'Failed to reset password.';
          showAlert('s3AlertError', 's3AlertErrorText', msg);
        }
      })
      .catch(function () {
        showAlert('s3AlertError', 's3AlertErrorText', 'Network error. Please try again.');
      })
      .finally(function () {
        btn.disabled = false; spin.style.display = 'none'; txt.textContent = 'Reset Password';
      });
    });

  })();
</script>
</body>
</html>
