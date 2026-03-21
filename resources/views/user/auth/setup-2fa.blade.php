<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Two-Factor Authentication — RechargeHub</title>
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
      align-items: flex-start;
      justify-content: center;
      padding: 32px 16px 48px;
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

    .page {
      width: 100%;
      max-width: 560px;
      position: relative;
      z-index: 1;
    }

    /* Top bar */
    .topbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 32px;
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .brand-icon {
      width: 38px;
      height: 38px;
      background: linear-gradient(135deg, #059669, #06b6d4);
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 0 16px rgba(5,150,105,.4);
    }

    .brand-icon svg { width: 20px; height: 20px; fill: #fff; }

    .brand-name {
      font-size: 20px;
      font-weight: 700;
      color: #fff;
      letter-spacing: -.4px;
    }

    .brand-name span { font-weight: 400; opacity: .7; }

    .back-link {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 13px;
      color: rgba(255,255,255,.4);
      text-decoration: none;
      transition: color .2s;
    }

    .back-link:hover { color: rgba(255,255,255,.7); }
    .back-link svg { width: 14px; height: 14px; }

    /* Page header */
    .page-header {
      margin-bottom: 28px;
    }

    .page-title {
      font-size: 24px;
      font-weight: 700;
      color: #fff;
      margin-bottom: 6px;
    }

    .page-subtitle {
      font-size: 14px;
      color: rgba(255,255,255,.45);
    }

    /* Card */
    .card {
      background: rgba(255,255,255,.04);
      border: 1px solid rgba(255,255,255,.09);
      border-radius: 20px;
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      padding: 28px;
      margin-bottom: 16px;
    }

    /* Method cards */
    .method-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
      margin-bottom: 24px;
    }

    @media (max-width: 480px) { .method-grid { grid-template-columns: 1fr; } }

    .method-card {
      background: rgba(255,255,255,.03);
      border: 1.5px solid rgba(255,255,255,.08);
      border-radius: 14px;
      padding: 20px;
      cursor: pointer;
      transition: border-color .2s, background .2s, transform .15s;
      text-align: center;
    }

    .method-card:hover {
      border-color: rgba(16,185,129,.4);
      background: rgba(16,185,129,.05);
      transform: translateY(-2px);
    }

    .method-card.active {
      border-color: rgba(16,185,129,.6);
      background: rgba(16,185,129,.08);
    }

    .method-icon {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 12px;
    }

    .method-icon.green {
      background: rgba(16,185,129,.15);
      border: 1px solid rgba(16,185,129,.25);
    }

    .method-icon.blue {
      background: rgba(6,182,212,.12);
      border: 1px solid rgba(6,182,212,.2);
    }

    .method-icon svg { width: 24px; height: 24px; fill: none; }
    .method-icon.green svg { stroke: #10b981; }
    .method-icon.blue svg { stroke: #06b6d4; }

    .method-name {
      font-size: 14px;
      font-weight: 600;
      color: #fff;
      margin-bottom: 4px;
    }

    .method-desc {
      font-size: 12px;
      color: rgba(255,255,255,.4);
      line-height: 1.5;
    }

    /* TOTP setup panel */
    .totp-panel {
      display: none;
      margin-top: 20px;
      padding-top: 20px;
      border-top: 1px solid rgba(255,255,255,.07);
    }

    .totp-panel.show { display: block; }

    .totp-panel-title {
      font-size: 14px;
      font-weight: 600;
      color: rgba(255,255,255,.8);
      margin-bottom: 16px;
    }

    .qr-wrap {
      text-align: center;
      margin-bottom: 16px;
    }

    .qr-wrap img {
      width: 160px;
      height: 160px;
      border-radius: 10px;
      border: 2px solid rgba(255,255,255,.1);
      background: #fff;
      padding: 6px;
    }

    .secret-box {
      background: rgba(255,255,255,.05);
      border: 1px solid rgba(255,255,255,.1);
      border-radius: 8px;
      padding: 10px 14px;
      font-family: 'Courier New', monospace;
      font-size: 13px;
      color: #6ee7b7;
      letter-spacing: 2px;
      word-break: break-all;
      text-align: center;
      margin-bottom: 16px;
    }

    .secret-label {
      font-size: 11.5px;
      color: rgba(255,255,255,.35);
      text-align: center;
      margin-bottom: 16px;
    }

    /* OTP input row */
    .otp-row {
      display: flex;
      gap: 8px;
      justify-content: center;
      margin-bottom: 16px;
    }

    .otp-box {
      width: 44px;
      height: 52px;
      background: rgba(255,255,255,.06);
      border: 1.5px solid rgba(255,255,255,.12);
      border-radius: 10px;
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

    /* Alerts */
    .alert {
      padding: 11px 14px;
      border-radius: 10px;
      font-size: 13.5px;
      font-weight: 500;
      margin-bottom: 16px;
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

    /* Buttons */
    .btn-primary {
      width: 100%;
      padding: 12px;
      background: linear-gradient(135deg, #059669, #10b981);
      border: none;
      border-radius: 10px;
      color: #fff;
      font-family: 'Inter', sans-serif;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: opacity .2s, transform .15s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      box-shadow: 0 4px 14px rgba(16,185,129,.3);
    }

    .btn-primary:hover { opacity: .92; transform: translateY(-1px); }
    .btn-primary:active { transform: translateY(0); }
    .btn-primary:disabled { opacity: .55; cursor: not-allowed; transform: none; }

    .btn-outline {
      width: 100%;
      padding: 12px;
      background: transparent;
      border: 1.5px solid rgba(239,68,68,.4);
      border-radius: 10px;
      color: #fca5a5;
      font-family: 'Inter', sans-serif;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: background .2s, border-color .2s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }

    .btn-outline:hover {
      background: rgba(239,68,68,.08);
      border-color: rgba(239,68,68,.6);
    }

    .btn-outline:disabled { opacity: .5; cursor: not-allowed; }

    .btn-sm {
      padding: 9px 18px;
      width: auto;
      font-size: 13px;
    }

    .spinner {
      width: 16px;
      height: 16px;
      border: 2px solid rgba(255,255,255,.3);
      border-top-color: #fff;
      border-radius: 50%;
      animation: spin .7s linear infinite;
      display: none;
    }

    @keyframes spin { to { transform: rotate(360deg); } }

    /* Disable section */
    .disable-section {
      margin-top: 8px;
    }

    .disable-section .card-label {
      font-size: 13px;
      font-weight: 600;
      color: rgba(255,255,255,.6);
      margin-bottom: 10px;
    }

    /* Status badge */
    .status-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 4px 10px;
      border-radius: 99px;
      font-size: 12px;
      font-weight: 500;
      background: rgba(16,185,129,.12);
      color: #6ee7b7;
      border: 1px solid rgba(16,185,129,.2);
    }

    .status-dot {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: #10b981;
    }

    .section-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 20px;
      flex-wrap: wrap;
      gap: 10px;
    }

    .section-title {
      font-size: 16px;
      font-weight: 700;
      color: #fff;
    }

    .section-subtitle {
      font-size: 13px;
      color: rgba(255,255,255,.4);
      margin-top: 2px;
    }

    .divider {
      border: none;
      border-top: 1px solid rgba(255,255,255,.07);
      margin: 20px 0;
    }

    .info-box {
      background: rgba(6,182,212,.06);
      border: 1px solid rgba(6,182,212,.15);
      border-radius: 10px;
      padding: 12px 14px;
      font-size: 12.5px;
      color: rgba(255,255,255,.5);
      line-height: 1.6;
      margin-bottom: 16px;
    }

    .info-box strong { color: rgba(255,255,255,.7); }
  </style>
</head>
<body>

<div class="page">

  <!-- Top bar -->
  <div class="topbar">
    <div class="brand">
      <div class="brand-icon">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
        </svg>
      </div>
      <div class="brand-name">Recharge<span>Hub</span></div>
    </div>
    <a href="/user/dashboard" class="back-link">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
      </svg>
      Dashboard
    </a>
  </div>

  <!-- Page header -->
  <div class="page-header">
    <div class="page-title">Two-Factor Authentication</div>
    <div class="page-subtitle">Add an extra layer of security to your account</div>
  </div>

  <!-- Global alerts -->
  <div class="alert alert-success" id="globalSuccess">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <span id="globalSuccessText"></span>
  </div>

  <div class="alert alert-error" id="globalError">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <span id="globalErrorText"></span>
  </div>

  <!-- Enable 2FA Card -->
  <div class="card">
    <div class="section-header">
      <div>
        <div class="section-title">Enable Two-Factor Authentication</div>
        <div class="section-subtitle">Choose your preferred verification method</div>
      </div>
    </div>

    <div class="method-grid">
      <!-- SMS OTP -->
      <div class="method-card" id="smsCard">
        <div class="method-icon green">
          <svg viewBox="0 0 24 24" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18h3"/>
          </svg>
        </div>
        <div class="method-name">SMS OTP</div>
        <div class="method-desc">Receive a one-time code via SMS to your registered mobile</div>
      </div>

      <!-- Authenticator App -->
      <div class="method-card" id="totpCard">
        <div class="method-icon blue">
          <svg viewBox="0 0 24 24" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z"/>
          </svg>
        </div>
        <div class="method-name">Authenticator App</div>
        <div class="method-desc">Use Google Authenticator, Authy or any TOTP app</div>
      </div>
    </div>

    <!-- SMS OTP enable alert -->
    <div class="alert alert-success" id="smsSuccess">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <span id="smsSuccessText"></span>
    </div>

    <div class="alert alert-error" id="smsError">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <span id="smsErrorText"></span>
    </div>

    <!-- TOTP setup panel -->
    <div class="totp-panel" id="totpPanel">
      <div class="totp-panel-title">Scan QR Code</div>

      <div class="info-box">
        Open your authenticator app (e.g. <strong>Google Authenticator</strong> or <strong>Authy</strong>), tap "Add Account" and scan the QR code below. Then enter the 6-digit code shown to confirm setup.
      </div>

      <div class="alert alert-error" id="totpError">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span id="totpErrorText"></span>
      </div>

      <div class="qr-wrap" id="qrWrap" style="display:none;">
        <img id="qrImage" src="" alt="TOTP QR Code" />
      </div>

      <div id="secretWrap" style="display:none;">
        <div class="secret-box" id="secretText"></div>
        <div class="secret-label">Can't scan? Enter this key manually in your app</div>
      </div>

      <div id="totpConfirmWrap" style="display:none;">
        <div class="totp-panel-title">Enter verification code</div>
        <div class="otp-row" id="totpOtpRow">
          <input class="otp-box" type="text" inputmode="numeric" maxlength="1" data-totp-idx="0" />
          <input class="otp-box" type="text" inputmode="numeric" maxlength="1" data-totp-idx="1" />
          <input class="otp-box" type="text" inputmode="numeric" maxlength="1" data-totp-idx="2" />
          <input class="otp-box" type="text" inputmode="numeric" maxlength="1" data-totp-idx="3" />
          <input class="otp-box" type="text" inputmode="numeric" maxlength="1" data-totp-idx="4" />
          <input class="otp-box" type="text" inputmode="numeric" maxlength="1" data-totp-idx="5" />
        </div>
        <button type="button" class="btn-primary btn-sm" id="totpConfirmBtn" style="display:inline-flex; width:auto; min-width:140px;">
          <div class="spinner" id="totpConfirmSpinner"></div>
          <span id="totpConfirmText">Confirm &amp; Enable</span>
        </button>
      </div>

      <div id="totpLoadingWrap" style="text-align:center; padding: 16px 0; display:none;">
        <div style="width:28px;height:28px;border:3px solid rgba(16,185,129,.2);border-top-color:#10b981;border-radius:50%;animation:spin .8s linear infinite;margin:0 auto 8px;"></div>
        <div style="font-size:12.5px;color:rgba(255,255,255,.35);">Loading QR code...</div>
      </div>
    </div>

  </div>

  <!-- Disable 2FA card -->
  <div class="card disable-section">
    <div class="card-label">Danger Zone</div>

    <div class="alert alert-success" id="disableSuccess">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <span id="disableSuccessText"></span>
    </div>

    <div class="alert alert-error" id="disableError">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <span id="disableErrorText"></span>
    </div>

    <button type="button" class="btn-outline" id="disableBtn">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
      </svg>
      <div class="spinner" id="disableSpinner" style="border-color:rgba(252,165,165,.3);border-top-color:#fca5a5;"></div>
      <span id="disableText">Disable 2FA</span>
    </button>
  </div>

</div>

<script>
  (function () {
    'use strict';

    var CSRF      = '{{ csrf_token() }}';
    var userToken = localStorage.getItem('user_token');

    // Guard: must be logged in
    if (!userToken) {
      window.location.replace('/user/login');
    }

    function authHeaders() {
      return {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': CSRF,
        'Authorization': 'Bearer ' + userToken,
      };
    }

    function showAlert(id, textId, msg) {
      document.getElementById(textId).textContent = msg;
      document.getElementById(id).classList.add('show');
    }

    function hideAlert(id) {
      document.getElementById(id).classList.remove('show');
    }

    function setLoading(btnId, spinnerId, textId, loading, loadingLabel, idleLabel) {
      var btn  = document.getElementById(btnId);
      var spin = document.getElementById(spinnerId);
      var txt  = document.getElementById(textId);
      btn.disabled          = loading;
      spin.style.display    = loading ? 'block' : 'none';
      txt.textContent       = loading ? loadingLabel : idleLabel;
    }

    /* ============================================================
       SMS OTP — Enable
    ============================================================ */
    document.getElementById('smsCard').addEventListener('click', function () {
      hideAlert('smsSuccess');
      hideAlert('smsError');
      hideAlert('globalError');

      setLoading('smsCard', 'smsCard', 'smsCard', false, '', ''); // no spinner on card itself
      document.getElementById('smsCard').classList.add('active');
      document.getElementById('totpCard').classList.remove('active');
      document.getElementById('totpPanel').classList.remove('show');

      var card = document.getElementById('smsCard');
      card.style.pointerEvents = 'none';
      card.style.opacity = '.7';

      fetch('/api/v1/auth/2fa/enable-otp', {
        method: 'POST',
        headers: authHeaders(),
        body: JSON.stringify({}),
      })
      .then(function (res) {
        return res.json().then(function (d) { return { status: res.status, data: d }; });
      })
      .then(function (result) {
        var data = result.data;
        if (result.status >= 200 && result.status < 300) {
          showAlert('smsSuccess', 'smsSuccessText', (data.message || 'SMS OTP 2FA has been enabled for your account.'));
        } else {
          showAlert('smsError', 'smsErrorText', (data.message || 'Failed to enable SMS OTP.'));
          document.getElementById('smsCard').classList.remove('active');
        }
      })
      .catch(function () {
        showAlert('smsError', 'smsErrorText', 'Network error. Please try again.');
        document.getElementById('smsCard').classList.remove('active');
      })
      .finally(function () {
        card.style.pointerEvents = '';
        card.style.opacity = '';
      });
    });

    /* ============================================================
       TOTP — Setup
    ============================================================ */
    document.getElementById('totpCard').addEventListener('click', function () {
      hideAlert('smsSuccess');
      hideAlert('smsError');
      hideAlert('globalError');

      document.getElementById('totpCard').classList.add('active');
      document.getElementById('smsCard').classList.remove('active');

      var panel = document.getElementById('totpPanel');
      panel.classList.add('show');

      // Show loading
      document.getElementById('totpLoadingWrap').style.display = 'block';
      document.getElementById('qrWrap').style.display         = 'none';
      document.getElementById('secretWrap').style.display     = 'none';
      document.getElementById('totpConfirmWrap').style.display = 'none';
      hideAlert('totpError');

      fetch('/api/v1/auth/2fa/setup-totp', {
        method: 'POST',
        headers: authHeaders(),
        body: JSON.stringify({}),
      })
      .then(function (res) {
        return res.json().then(function (d) { return { status: res.status, data: d }; });
      })
      .then(function (result) {
        var data = result.data;
        document.getElementById('totpLoadingWrap').style.display = 'none';

        if (result.status >= 200 && result.status < 300) {
          if (data.qr_url) {
            document.getElementById('qrImage').src = data.qr_url;
            document.getElementById('qrWrap').style.display = 'block';
          }
          if (data.secret) {
            document.getElementById('secretText').textContent = data.secret;
            document.getElementById('secretWrap').style.display = 'block';
          }
          document.getElementById('totpConfirmWrap').style.display = 'block';
          // Focus first TOTP input
          var firstBox = document.querySelector('[data-totp-idx="0"]');
          if (firstBox) firstBox.focus();
        } else {
          showAlert('totpError', 'totpErrorText', (data.message || 'Failed to setup TOTP.'));
          document.getElementById('totpCard').classList.remove('active');
          panel.classList.remove('show');
        }
      })
      .catch(function () {
        document.getElementById('totpLoadingWrap').style.display = 'none';
        showAlert('totpError', 'totpErrorText', 'Network error. Please try again.');
        document.getElementById('totpCard').classList.remove('active');
        document.getElementById('totpPanel').classList.remove('show');
      });
    });

    /* ---- TOTP OTP boxes ---- */
    var totpBoxes = Array.from(document.querySelectorAll('[data-totp-idx]'));

    totpBoxes.forEach(function (box, idx) {
      box.addEventListener('keydown', function (e) {
        if (e.key === 'Backspace' && !box.value && idx > 0) {
          totpBoxes[idx - 1].focus();
          totpBoxes[idx - 1].value = '';
        }
      });
      box.addEventListener('input', function () {
        var val = box.value.replace(/\D/g, '');
        box.value = val ? val[val.length - 1] : '';
        if (box.value && idx < 5) totpBoxes[idx + 1].focus();
        if (idx === 5 && box.value) confirmTotp();
      });
      box.addEventListener('paste', function (e) {
        e.preventDefault();
        var text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
        text.split('').forEach(function (ch, i) { if (totpBoxes[i]) totpBoxes[i].value = ch; });
        var last = Math.min(text.length - 1, 5);
        totpBoxes[last].focus();
        if (text.length === 6) confirmTotp();
      });
    });

    function confirmTotp() {
      var code = totpBoxes.map(function (b) { return b.value; }).join('');
      if (code.length < 6) {
        totpBoxes.forEach(function (b) { b.classList.add('is-error'); });
        showAlert('totpError', 'totpErrorText', 'Please enter all 6 digits.');
        return;
      }
      totpBoxes.forEach(function (b) { b.classList.remove('is-error'); });
      hideAlert('totpError');

      setLoading('totpConfirmBtn', 'totpConfirmSpinner', 'totpConfirmText', true, 'Enabling...', 'Confirm & Enable');

      fetch('/api/v1/auth/2fa/enable-totp', {
        method: 'POST',
        headers: authHeaders(),
        body: JSON.stringify({ code: code }),
      })
      .then(function (res) {
        return res.json().then(function (d) { return { status: res.status, data: d }; });
      })
      .then(function (result) {
        var data = result.data;
        if (result.status >= 200 && result.status < 300) {
          showAlert('globalSuccess', 'globalSuccessText', (data.message || 'Authenticator app 2FA has been enabled!'));
          document.getElementById('totpPanel').classList.remove('show');
          document.getElementById('totpCard').classList.add('active');
          window.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
          totpBoxes.forEach(function (b) { b.classList.add('is-error'); b.value = ''; });
          totpBoxes[0].focus();
          showAlert('totpError', 'totpErrorText', (data.message || 'Invalid code. Please try again.'));
        }
      })
      .catch(function () {
        showAlert('totpError', 'totpErrorText', 'Network error. Please try again.');
      })
      .finally(function () {
        setLoading('totpConfirmBtn', 'totpConfirmSpinner', 'totpConfirmText', false, '', 'Confirm & Enable');
      });
    }

    document.getElementById('totpConfirmBtn').addEventListener('click', confirmTotp);

    /* ============================================================
       Disable 2FA
    ============================================================ */
    document.getElementById('disableBtn').addEventListener('click', function () {
      hideAlert('disableSuccess');
      hideAlert('disableError');

      if (!confirm('Are you sure you want to disable two-factor authentication? This will make your account less secure.')) return;

      setLoading('disableBtn', 'disableSpinner', 'disableText', true, 'Disabling...', 'Disable 2FA');

      fetch('/api/v1/auth/2fa/disable', {
        method: 'POST',
        headers: authHeaders(),
        body: JSON.stringify({}),
      })
      .then(function (res) {
        return res.json().then(function (d) { return { status: res.status, data: d }; });
      })
      .then(function (result) {
        var data = result.data;
        if (result.status >= 200 && result.status < 300) {
          showAlert('disableSuccess', 'disableSuccessText', (data.message || 'Two-factor authentication has been disabled.'));
          document.getElementById('smsCard').classList.remove('active');
          document.getElementById('totpCard').classList.remove('active');
          document.getElementById('totpPanel').classList.remove('show');
        } else {
          showAlert('disableError', 'disableErrorText', (data.message || 'Failed to disable 2FA.'));
        }
      })
      .catch(function () {
        showAlert('disableError', 'disableErrorText', 'Network error. Please try again.');
      })
      .finally(function () {
        setLoading('disableBtn', 'disableSpinner', 'disableText', false, '', 'Disable 2FA');
      });
    });

  })();
</script>
</body>
</html>
