<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Create Account — RechargeHub</title>
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
      max-width: 520px;
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

    .card-title {
      font-size: 22px;
      font-weight: 700;
      color: #fff;
      margin-bottom: 4px;
    }

    .card-subtitle {
      font-size: 14px;
      color: rgba(255,255,255,.45);
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
      background: rgba(16,185,129,.15);
      border: 1px solid rgba(16,185,129,.3);
      color: #6ee7b7;
    }

    .alert-error {
      background: rgba(239,68,68,.12);
      border: 1px solid rgba(239,68,68,.25);
      color: #fca5a5;
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
    }

    @media (max-width: 480px) { .form-row { grid-template-columns: 1fr; } }

    .form-group { margin-bottom: 18px; }

    .form-label {
      display: block;
      font-size: 13px;
      font-weight: 500;
      color: rgba(255,255,255,.7);
      margin-bottom: 6px;
    }

    .form-label .req { color: #10b981; margin-left: 2px; }

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

    select.form-input {
      cursor: pointer;
      appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='rgba(255,255,255,.4)' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 14px center;
      padding-right: 36px;
    }

    select.form-input option { background: #0f1f3d; color: #fff; }

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

    .rule.met .rule-icon {
      background: #10b981;
      border-color: #10b981;
    }

    .rule-icon svg { width: 8px; height: 8px; fill: #fff; opacity: 0; transition: opacity .2s; }
    .rule.met .rule-icon svg { opacity: 1; }

    .drop-zone {
      border: 1.5px dashed rgba(255,255,255,.15);
      border-radius: 10px;
      padding: 20px;
      text-align: center;
      cursor: pointer;
      transition: border-color .2s, background .2s;
      position: relative;
    }

    .drop-zone:hover,
    .drop-zone.drag-over {
      border-color: rgba(16,185,129,.5);
      background: rgba(16,185,129,.05);
    }

    .drop-zone input[type="file"] {
      position: absolute;
      inset: 0;
      opacity: 0;
      cursor: pointer;
      width: 100%;
      height: 100%;
    }

    .drop-icon {
      width: 36px;
      height: 36px;
      background: rgba(16,185,129,.12);
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 10px;
    }

    .drop-icon svg { width: 20px; height: 20px; stroke: #10b981; fill: none; }

    .drop-text-main {
      font-size: 13px;
      font-weight: 500;
      color: rgba(255,255,255,.6);
    }

    .drop-text-sub {
      font-size: 11.5px;
      color: rgba(255,255,255,.3);
      margin-top: 3px;
    }

    .file-chosen {
      display: none;
      align-items: center;
      gap: 10px;
      background: rgba(16,185,129,.1);
      border: 1px solid rgba(16,185,129,.25);
      border-radius: 8px;
      padding: 10px 12px;
      margin-top: 10px;
    }

    .file-chosen.show { display: flex; }
    .file-chosen svg { width: 16px; height: 16px; stroke: #10b981; fill: none; flex-shrink: 0; }
    .file-chosen span { font-size: 12.5px; color: #6ee7b7; flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    .file-remove {
      background: none;
      border: none;
      cursor: pointer;
      color: rgba(255,255,255,.3);
      padding: 0;
      line-height: 1;
      font-size: 18px;
      transition: color .2s;
    }

    .file-remove:hover { color: #fca5a5; }

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

    .footer-link {
      text-align: center;
      margin-top: 22px;
      font-size: 13.5px;
      color: rgba(255,255,255,.4);
    }

    .footer-link a {
      color: #10b981;
      text-decoration: none;
      font-weight: 500;
    }

    .footer-link a:hover { text-decoration: underline; }

    .divider {
      border: none;
      border-top: 1px solid rgba(255,255,255,.07);
      margin: 20px 0;
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
    <div class="card-title">Create Account</div>
    <div class="card-subtitle">Join RechargeHub and start earning today</div>

    <div class="alert alert-success" id="alertSuccess">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <span id="alertSuccessText"></span>
    </div>

    <div class="alert alert-error" id="alertError">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <span id="alertErrorText"></span>
    </div>

    <form id="registerForm" novalidate>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="name">Full Name <span class="req">*</span></label>
          <div class="input-wrap">
            <input type="text" id="name" name="name" class="form-input" placeholder="John Doe" autocomplete="name" />
          </div>
          <div class="field-error" id="nameError"></div>
        </div>

        <div class="form-group">
          <label class="form-label" for="mobile">Mobile Number <span class="req">*</span></label>
          <div class="input-wrap">
            <input type="tel" id="mobile" name="mobile" class="form-input" placeholder="10-digit number" maxlength="10" autocomplete="tel" />
          </div>
          <div class="field-error" id="mobileError"></div>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="email">Email Address <span class="req">*</span></label>
        <div class="input-wrap">
          <input type="email" id="email" name="email" class="form-input" placeholder="you@example.com" autocomplete="email" />
        </div>
        <div class="field-error" id="emailError"></div>
      </div>

      <div class="form-group">
        <label class="form-label" for="account_type">Account Type <span class="req">*</span></label>
        <div class="input-wrap">
          <select id="account_type" name="account_type" class="form-input">
            <option value="retailer" selected>Retailer — I sell recharges to customers</option>
            <option value="distributor">Distributor — I manage a network of retailers</option>
          </select>
        </div>
        <div class="field-error" id="account_typeError"></div>
      </div>

      <div style="background:rgba(99,102,241,.08);border:1px solid rgba(99,102,241,.2);border-radius:10px;padding:12px 14px;margin-bottom:14px;font-size:12.5px;color:#a5b4fc;display:flex;align-items:center;gap:10px">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>Are you a developer or want API access? <a href="/user/register-api" style="color:#818cf8;font-weight:700;text-decoration:none">Register as API User →</a></span>
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password <span class="req">*</span></label>
        <div class="input-wrap">
          <input type="password" id="password" name="password" class="form-input pr" placeholder="Create a strong password" autocomplete="new-password" />
          <button type="button" class="toggle-btn" id="togglePassword" aria-label="Toggle password visibility">
            <svg id="pwdEyeIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
          </button>
        </div>

        <div class="strength-bar">
          <div class="strength-seg" id="seg1"></div>
          <div class="strength-seg" id="seg2"></div>
          <div class="strength-seg" id="seg3"></div>
          <div class="strength-seg" id="seg4"></div>
          <div class="strength-seg" id="seg5"></div>
        </div>

        <div class="strength-rules">
          <div class="rule" id="ruleLen">
            <div class="rule-icon">
              <svg viewBox="0 0 8 8"><path d="M1.5 4l2 2 3-3" stroke="#fff" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            Min 8 characters
          </div>
          <div class="rule" id="ruleUpper">
            <div class="rule-icon">
              <svg viewBox="0 0 8 8"><path d="M1.5 4l2 2 3-3" stroke="#fff" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            Uppercase letter
          </div>
          <div class="rule" id="ruleLower">
            <div class="rule-icon">
              <svg viewBox="0 0 8 8"><path d="M1.5 4l2 2 3-3" stroke="#fff" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            Lowercase letter
          </div>
          <div class="rule" id="ruleNum">
            <div class="rule-icon">
              <svg viewBox="0 0 8 8"><path d="M1.5 4l2 2 3-3" stroke="#fff" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            Number (0–9)
          </div>
          <div class="rule" id="ruleSymbol">
            <div class="rule-icon">
              <svg viewBox="0 0 8 8"><path d="M1.5 4l2 2 3-3" stroke="#fff" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            Special symbol
          </div>
        </div>

        <div class="field-error" id="passwordError"></div>
      </div>

      <div class="form-group">
        <label class="form-label" for="password_confirmation">Confirm Password <span class="req">*</span></label>
        <div class="input-wrap">
          <input type="password" id="password_confirmation" name="password_confirmation" class="form-input pr" placeholder="Repeat your password" autocomplete="new-password" />
          <button type="button" class="toggle-btn" id="toggleConfirm" aria-label="Toggle confirm password visibility">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
          </button>
        </div>
        <div class="field-error" id="password_confirmationError"></div>
      </div>

      <div class="form-group">
        <label class="form-label" for="document">
          Identity Document
          <span style="color:rgba(255,255,255,.3);font-weight:400;margin-left:4px;">(Optional — Aadhaar / PAN)</span>
        </label>
        <div class="drop-zone" id="dropZone">
          <input type="file" id="document" name="document" accept=".jpg,.jpeg,.png,.pdf,.webp" />
          <div class="drop-icon">
            <svg viewBox="0 0 24 24" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
            </svg>
          </div>
          <div class="drop-text-main">Drag &amp; drop or click to upload</div>
          <div class="drop-text-sub">JPG, JPEG, PNG, PDF, WEBP &mdash; max 4 MB</div>
        </div>
        <div class="file-chosen" id="fileChosen">
          <svg viewBox="0 0 24 24" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
          </svg>
          <span id="fileChosenName"></span>
          <button type="button" class="file-remove" id="fileRemove" aria-label="Remove file">&times;</button>
        </div>
        <div class="field-error" id="documentError"></div>
      </div>

      {{-- PAN Card Upload --}}
      <div class="form-group">
        <label class="form-label" for="pan_image">
          PAN Card Image
          <span style="color:rgba(255,255,255,.3);font-weight:400;margin-left:4px;">(Optional)</span>
        </label>
        <div class="drop-zone" id="panDropZone" style="border-color:rgba(234,179,8,.25)">
          <input type="file" id="pan_image" name="pan_image" accept=".jpg,.jpeg,.png,.pdf,.webp" onchange="updateFileLabel('pan_image','panFileName','panFileChosen')" />
          <div class="drop-icon" style="color:rgba(234,179,8,.5)">
            <svg viewBox="0 0 24 24" stroke-width="1.5" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z"/>
            </svg>
          </div>
          <div class="drop-text-main">Upload PAN Card</div>
          <div class="drop-text-sub">JPG, PNG, PDF, WEBP &mdash; max 4 MB</div>
        </div>
        <div class="file-chosen" id="panFileChosen" style="display:none">
          <svg viewBox="0 0 24 24" stroke-width="1.5" fill="none" stroke="currentColor" style="width:16px;height:16px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
          <span id="panFileName"></span>
          <button type="button" style="margin-left:auto;background:none;border:none;cursor:pointer;color:rgba(255,255,255,.4);font-size:16px" onclick="clearFileInput('pan_image','panFileName','panFileChosen')">&times;</button>
        </div>
        <div class="field-error" id="pan_imageError"></div>
      </div>

      {{-- GST Certificate Upload --}}
      <div class="form-group">
        <label class="form-label" for="gst_certificate">
          GST Certificate
          <span style="color:rgba(255,255,255,.3);font-weight:400;margin-left:4px;">(Optional)</span>
        </label>
        <div class="drop-zone" id="gstDropZone" style="border-color:rgba(99,102,241,.25)">
          <input type="file" id="gst_certificate" name="gst_certificate" accept=".jpg,.jpeg,.png,.pdf,.webp" onchange="updateFileLabel('gst_certificate','gstFileName','gstFileChosen')" />
          <div class="drop-icon" style="color:rgba(99,102,241,.5)">
            <svg viewBox="0 0 24 24" stroke-width="1.5" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/>
            </svg>
          </div>
          <div class="drop-text-main">Upload GST Certificate</div>
          <div class="drop-text-sub">JPG, PNG, PDF, WEBP &mdash; max 4 MB</div>
        </div>
        <div class="file-chosen" id="gstFileChosen" style="display:none">
          <svg viewBox="0 0 24 24" stroke-width="1.5" fill="none" stroke="currentColor" style="width:16px;height:16px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
          <span id="gstFileName"></span>
          <button type="button" style="margin-left:auto;background:none;border:none;cursor:pointer;color:rgba(255,255,255,.4);font-size:16px" onclick="clearFileInput('gst_certificate','gstFileName','gstFileChosen')">&times;</button>
        </div>
        <div class="field-error" id="gst_certificateError"></div>
      </div>

      {{-- Approval notice --}}
      <div style="background:rgba(251,191,36,.07);border:1px solid rgba(251,191,36,.2);border-radius:10px;padding:12px 14px;margin-bottom:16px;font-size:12.5px;color:#fde68a;display:flex;align-items:flex-start;gap:10px">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;flex-shrink:0;margin-top:1px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <span>After submitting, your account will be <strong>pending admin approval</strong>. You will receive an email notification once your account is approved and you can login.</span>
      </div>

      <button type="submit" class="btn-primary" id="submitBtn">
        <div class="spinner" id="spinner"></div>
        <span id="submitText">Create Account</span>
      </button>
    </form>

    <hr class="divider" />
    <div class="footer-link">Already have an account? <a href="/user/login">Sign in</a></div>
  </div>
</div>

<script>
  (function () {
    'use strict';

    // Redirect if already authenticated
    if (localStorage.getItem('user_token')) {
      window.location.replace('/user/dashboard');
    }

    var CSRF = '{{ csrf_token() }}';

    /* ---- Eye toggle helper ---- */
    function setupToggle(btnId, inputId) {
      var btn = document.getElementById(btnId);
      var inp = document.getElementById(inputId);
      var eyeOpen = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>';
      var eyeClosed = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>';
      btn.addEventListener('click', function () {
        if (inp.type === 'password') {
          inp.type = 'text';
          btn.innerHTML = eyeClosed;
        } else {
          inp.type = 'password';
          btn.innerHTML = eyeOpen;
        }
      });
    }

    setupToggle('togglePassword', 'password');
    setupToggle('toggleConfirm', 'password_confirmation');

    /* ---- Password strength ---- */
    var strengthColors = ['', 'weak', 'fair', 'fair', 'good', 'strong'];
    var ruleChecks = {
      ruleLen:    function (v) { return v.length >= 8; },
      ruleUpper:  function (v) { return /[A-Z]/.test(v); },
      ruleLower:  function (v) { return /[a-z]/.test(v); },
      ruleNum:    function (v) { return /[0-9]/.test(v); },
      ruleSymbol: function (v) { return /[^A-Za-z0-9]/.test(v); },
    };

    document.getElementById('password').addEventListener('input', function () {
      var v = this.value;
      var score = 0;
      Object.keys(ruleChecks).forEach(function (id) {
        var el = document.getElementById(id);
        if (ruleChecks[id](v)) { el.classList.add('met'); score++; }
        else { el.classList.remove('met'); }
      });
      var cls = strengthColors[score];
      ['seg1','seg2','seg3','seg4','seg5'].forEach(function (id, i) {
        var seg = document.getElementById(id);
        seg.className = 'strength-seg';
        if (i < score) seg.classList.add(cls);
      });
    });

    /* ---- Document upload ---- */
    var dropZone        = document.getElementById('dropZone');
    var fileInput       = document.getElementById('document');
    var fileChosen      = document.getElementById('fileChosen');
    var fileChosenName  = document.getElementById('fileChosenName');
    var fileRemove      = document.getElementById('fileRemove');
    var MAX_SIZE        = 4 * 1024 * 1024;
    var ALLOWED_TYPES   = ['image/jpeg','image/jpg','image/png','application/pdf','image/webp'];

    function showFile(file) {
      if (!file) return;
      if (ALLOWED_TYPES.indexOf(file.type) === -1) {
        showFieldError('documentError', 'Invalid file type. Allowed: JPG, PNG, PDF, WEBP');
        clearFileInput();
        return;
      }
      if (file.size > MAX_SIZE) {
        showFieldError('documentError', 'File too large. Maximum size is 4 MB');
        clearFileInput();
        return;
      }
      clearFieldError('documentError');
      fileChosenName.textContent = file.name;
      fileChosen.classList.add('show');
    }

    function clearFileInput() {
      fileInput.value = '';
      fileChosen.classList.remove('show');
      fileChosenName.textContent = '';
    }

    fileInput.addEventListener('change', function () { showFile(fileInput.files[0]); });
    fileRemove.addEventListener('click', function () { clearFileInput(); clearFieldError('documentError'); });

    dropZone.addEventListener('dragover', function (e) { e.preventDefault(); dropZone.classList.add('drag-over'); });
    dropZone.addEventListener('dragleave', function () { dropZone.classList.remove('drag-over'); });
    dropZone.addEventListener('drop', function (e) {
      e.preventDefault();
      dropZone.classList.remove('drag-over');
      var file = e.dataTransfer.files[0];
      if (file) {
        try {
          var dt = new DataTransfer();
          dt.items.add(file);
          fileInput.files = dt.files;
        } catch (ex) { /* fallback: only display */ }
        showFile(file);
      }
    });

    /* ---- Error helpers ---- */
    function showFieldError(errorId, msg) {
      var el = document.getElementById(errorId);
      if (el) { el.textContent = msg; el.classList.add('show'); }
      var inputId = errorId.replace('Error', '');
      var inp = document.getElementById(inputId);
      if (inp) inp.classList.add('is-error');
    }

    function clearFieldError(errorId) {
      var el = document.getElementById(errorId);
      if (el) { el.textContent = ''; el.classList.remove('show'); }
      var inputId = errorId.replace('Error', '');
      var inp = document.getElementById(inputId);
      if (inp) inp.classList.remove('is-error');
    }

    function clearAllErrors() {
      ['name','mobile','email','password','password_confirmation','document'].forEach(function (f) {
        clearFieldError(f + 'Error');
      });
      document.getElementById('alertError').classList.remove('show');
      document.getElementById('alertSuccess').classList.remove('show');
    }

    /* ---- Client-side validation ---- */
    function validate() {
      var ok = true;
      var name  = document.getElementById('name').value.trim();
      var mobile = document.getElementById('mobile').value.trim();
      var email  = document.getElementById('email').value.trim();
      var pwd    = document.getElementById('password').value;
      var cpwd   = document.getElementById('password_confirmation').value;

      if (!name) {
        showFieldError('nameError', 'Full name is required');
        ok = false;
      }
      if (!/^\d{10}$/.test(mobile)) {
        showFieldError('mobileError', 'Enter a valid 10-digit mobile number');
        ok = false;
      }
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showFieldError('emailError', 'Enter a valid email address');
        ok = false;
      }
      if (pwd.length < 8) {
        showFieldError('passwordError', 'Password must be at least 8 characters');
        ok = false;
      }
      if (pwd !== cpwd) {
        showFieldError('password_confirmationError', 'Passwords do not match');
        ok = false;
      }
      return ok;
    }

    /* ---- Form submit ---- */
    document.getElementById('registerForm').addEventListener('submit', function (e) {
      e.preventDefault();
      clearAllErrors();
      if (!validate()) return;

      var submitBtn  = document.getElementById('submitBtn');
      var spinner    = document.getElementById('spinner');
      var submitText = document.getElementById('submitText');

      submitBtn.disabled = true;
      spinner.style.display = 'block';
      submitText.textContent = 'Creating account...';

      var formData = new FormData();
      formData.append('name',                  document.getElementById('name').value.trim());
      formData.append('mobile',                document.getElementById('mobile').value.trim());
      formData.append('email',                 document.getElementById('email').value.trim());
      formData.append('role',                   document.getElementById('account_type').value);
      formData.append('password',              document.getElementById('password').value);
      formData.append('password_confirmation', document.getElementById('password_confirmation').value);

      var docFile = document.getElementById('document').files[0];
      if (docFile) formData.append('document', docFile);

      var panFile = document.getElementById('pan_image').files[0];
      if (panFile) formData.append('pan_image', panFile);

      var gstFile = document.getElementById('gst_certificate').files[0];
      if (gstFile) formData.append('gst_certificate', gstFile);

      fetch('/api/v1/auth/register', {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': CSRF,
        },
        body: formData,
      })
      .then(function (res) {
        return res.json().then(function (data) { return { status: res.status, data: data }; });
      })
      .then(function (result) {
        var status = result.status;
        var data   = result.data;

        if (status === 201 || (status >= 200 && status < 300)) {
          document.getElementById('alertSuccessText').textContent = 'Registration submitted! Your account is pending admin approval. You will be notified via email once approved.';
          document.getElementById('alertSuccess').classList.add('show');
          submitBtn.disabled = true;
          submitText.textContent = 'Registration Submitted';
          setTimeout(function () { window.location.href = '/user/login?registered=1'; }, 4000);
        } else {
          if (data.errors) {
            Object.keys(data.errors).forEach(function (field) {
              var msgs = data.errors[field];
              showFieldError(field + 'Error', Array.isArray(msgs) ? msgs[0] : msgs);
            });
          }
          var errMsg = data.message || 'Registration failed. Please check the form.';
          document.getElementById('alertErrorText').textContent = errMsg;
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
        submitText.textContent = 'Create Account';
      });
    });

  })();

  // Helper functions for PAN / GST file inputs
  function updateFileLabel(inputId, nameId, choserId) {
    var file = document.getElementById(inputId).files[0];
    if (file) {
      document.getElementById(nameId).textContent = file.name;
      document.getElementById(choserId).style.display = 'flex';
    }
  }
  function clearFileInput(inputId, nameId, choserId) {
    document.getElementById(inputId).value = '';
    document.getElementById(nameId).textContent = '';
    document.getElementById(choserId).style.display = 'none';
  }
</script>
</body>
</html>
