<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Verify OTP — RechargeHub</title>
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
      max-width: 400px;
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
      text-align: center;
    }

    @media (max-width: 480px) { .card { padding: 28px 20px; } }

    .card-icon {
      width: 56px;
      height: 56px;
      background: linear-gradient(135deg, rgba(5,150,105,.2), rgba(16,185,129,.15));
      border: 1px solid rgba(16,185,129,.25);
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
    }

    .card-icon svg { width: 28px; height: 28px; stroke: #10b981; fill: none; }

    .card-title {
      font-size: 22px;
      font-weight: 700;
      color: #fff;
      margin-bottom: 8px;
    }

    .card-subtitle {
      font-size: 14px;
      color: rgba(255,255,255,.45);
      margin-bottom: 32px;
      line-height: 1.6;
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
      text-align: left;
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

    /* OTP digit boxes */
    .otp-row {
      display: flex;
      gap: 10px;
      justify-content: center;
      margin-bottom: 28px;
    }

    .otp-box {
      width: 48px;
      height: 56px;
      background: rgba(255,255,255,.06);
      border: 1.5px solid rgba(255,255,255,.12);
      border-radius: 12px;
      color: #fff;
      font-family: 'Inter', sans-serif;
      font-size: 22px;
      font-weight: 700;
      text-align: center;
      outline: none;
      transition: border-color .2s, box-shadow .2s, transform .15s;
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

    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      20%       { transform: translateX(-6px); }
      40%       { transform: translateX(6px); }
      60%       { transform: translateX(-4px); }
      80%       { transform: translateX(4px); }
    }

    .otp-box.shake { animation: shake .4s ease; }

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

    .resend-row {
      margin-top: 20px;
      font-size: 13.5px;
      color: rgba(255,255,255,.4);
    }

    .resend-btn {
      background: none;
      border: none;
      cursor: pointer;
      color: #10b981;
      font-family: 'Inter', sans-serif;
      font-size: 13.5px;
      font-weight: 500;
      padding: 0;
      transition: opacity .2s;
    }

    .resend-btn:disabled {
      color: rgba(255,255,255,.25);
      cursor: not-allowed;
    }

    .countdown {
      color: rgba(255,255,255,.35);
      font-size: 13px;
    }

    .back-link {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      margin-top: 24px;
      font-size: 13.5px;
      color: rgba(255,255,255,.35);
      text-decoration: none;
      transition: color .2s;
    }

    .back-link:hover { color: rgba(255,255,255,.7); }
    .back-link svg { width: 14px; height: 14px; }
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

    <div class="card-icon" id="cardIcon">
      <!-- icon filled by JS -->
    </div>

    <div class="card-title" id="cardTitle">Verify OTP</div>
    <div class="card-subtitle" id="cardSubtitle">Loading...</div>

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

    <!-- 6 OTP digit inputs -->
    <div class="otp-row" id="otpRow">
      <input class="otp-box" type="text" inputmode="numeric" maxlength="1" data-index="0" autocomplete="one-time-code" />
      <input class="otp-box" type="text" inputmode="numeric" maxlength="1" data-index="1" />
      <input class="otp-box" type="text" inputmode="numeric" maxlength="1" data-index="2" />
      <input class="otp-box" type="text" inputmode="numeric" maxlength="1" data-index="3" />
      <input class="otp-box" type="text" inputmode="numeric" maxlength="1" data-index="4" />
      <input class="otp-box" type="text" inputmode="numeric" maxlength="1" data-index="5" />
    </div>

    <button type="button" class="btn-primary" id="verifyBtn">
      <div class="spinner" id="spinner"></div>
      <span id="verifyText">Verify</span>
    </button>

    <!-- Resend (only for OTP method) -->
    <div class="resend-row" id="resendRow" style="display:none;">
      Didn't receive it?&nbsp;
      <button type="button" class="resend-btn" id="resendBtn" disabled>Resend OTP</button>
      <span class="countdown" id="countdown"></span>
    </div>

    <br />
    <a href="/user/login" class="back-link">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
      </svg>
      Back to Login
    </a>

  </div>
</div>

<script>
  (function () {
    'use strict';

    var CSRF         = '{{ csrf_token() }}';
    var pendingToken = sessionStorage.getItem('pending_2fa_token');
    var method       = sessionStorage.getItem('pending_2fa_method') || 'otp';

    // Guard: must have a pending token
    if (!pendingToken) {
      window.location.replace('/user/login');
    }

    /* ---- Set UI based on method ---- */
    var cardIcon     = document.getElementById('cardIcon');
    var cardTitle    = document.getElementById('cardTitle');
    var cardSubtitle = document.getElementById('cardSubtitle');
    var resendRow    = document.getElementById('resendRow');

    if (method === 'totp') {
      cardIcon.innerHTML = '<svg viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18h3"/></svg>';
      cardTitle.textContent    = 'Authenticator App Code';
      cardSubtitle.textContent = 'Enter the 6-digit code from your authenticator app.';
      resendRow.style.display  = 'none';
    } else {
      cardIcon.innerHTML = '<svg viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/></svg>';
      cardTitle.textContent    = 'Enter OTP';
      cardSubtitle.textContent = 'A 6-digit OTP has been sent to your registered mobile number.';
      resendRow.style.display  = 'block';
      startCountdown();
    }

    /* ---- OTP box logic ---- */
    var boxes = Array.from(document.querySelectorAll('.otp-box'));

    boxes[0].focus();

    boxes.forEach(function (box, idx) {
      box.addEventListener('keydown', function (e) {
        if (e.key === 'Backspace' && !box.value && idx > 0) {
          boxes[idx - 1].focus();
          boxes[idx - 1].value = '';
        }
      });

      box.addEventListener('input', function (e) {
        var val = box.value.replace(/\D/g, '');
        box.value = val ? val[val.length - 1] : '';

        if (box.value && idx < 5) {
          boxes[idx + 1].focus();
        }

        // Auto-submit when last box filled
        if (idx === 5 && box.value) {
          submitOtp();
        }
      });

      box.addEventListener('paste', function (e) {
        e.preventDefault();
        var text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
        text.split('').forEach(function (ch, i) {
          if (boxes[i]) boxes[i].value = ch;
        });
        var lastIdx = Math.min(text.length - 1, 5);
        boxes[lastIdx].focus();
        if (text.length === 6) submitOtp();
      });
    });

    function getOtpValue() {
      return boxes.map(function (b) { return b.value; }).join('');
    }

    function shakeBoxes() {
      boxes.forEach(function (b) {
        b.classList.add('is-error', 'shake');
        setTimeout(function () { b.classList.remove('shake'); }, 500);
      });
    }

    function clearBoxErrors() {
      boxes.forEach(function (b) { b.classList.remove('is-error'); });
    }

    /* ---- Submit ---- */
    function submitOtp() {
      var otp = getOtpValue();
      if (otp.length < 6) {
        shakeBoxes();
        document.getElementById('alertErrorText').textContent = 'Please enter all 6 digits.';
        document.getElementById('alertError').classList.add('show');
        return;
      }

      clearBoxErrors();
      document.getElementById('alertError').classList.remove('show');
      document.getElementById('alertSuccess').classList.remove('show');

      var verifyBtn  = document.getElementById('verifyBtn');
      var spinner    = document.getElementById('spinner');
      var verifyText = document.getElementById('verifyText');

      verifyBtn.disabled = true;
      spinner.style.display = 'block';
      verifyText.textContent = 'Verifying...';

      var endpoint, payload;

      if (method === 'totp') {
        endpoint = '/api/v1/auth/2fa/verify-totp';
        payload  = { pending_token: pendingToken, code: otp, device_name: 'web' };
      } else {
        endpoint = '/api/v1/auth/2fa/verify-otp';
        payload  = { pending_token: pendingToken, otp: otp, device_name: 'web' };
      }

      fetch(endpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': CSRF,
        },
        body: JSON.stringify(payload),
      })
      .then(function (res) {
        return res.json().then(function (data) { return { status: res.status, data: data }; });
      })
      .then(function (result) {
        var data = result.data;

        if (result.status >= 200 && result.status < 300 && data.token) {
          sessionStorage.removeItem('pending_2fa_token');
          sessionStorage.removeItem('pending_2fa_method');
          localStorage.setItem('user_token', data.token);
          localStorage.setItem('user_data',  JSON.stringify(data.user || {}));
          document.getElementById('alertSuccessText').textContent = 'Verified! Redirecting...';
          document.getElementById('alertSuccess').classList.add('show');
          setTimeout(function () { window.location.href = '/user/dashboard'; }, 1000);
        } else {
          shakeBoxes();
          var msg = (data && data.message) ? data.message : 'Invalid OTP. Please try again.';
          document.getElementById('alertErrorText').textContent = msg;
          document.getElementById('alertError').classList.add('show');
          boxes.forEach(function (b) { b.value = ''; });
          boxes[0].focus();
        }
      })
      .catch(function () {
        shakeBoxes();
        document.getElementById('alertErrorText').textContent = 'Network error. Please try again.';
        document.getElementById('alertError').classList.add('show');
      })
      .finally(function () {
        verifyBtn.disabled = false;
        spinner.style.display = 'none';
        verifyText.textContent = 'Verify';
      });
    }

    document.getElementById('verifyBtn').addEventListener('click', submitOtp);

    /* ---- Resend countdown (OTP only) ---- */
    var countdownEl = document.getElementById('countdown');
    var resendBtn   = document.getElementById('resendBtn');
    var timer       = null;

    function startCountdown() {
      var secs = 60;
      resendBtn.disabled = true;
      countdownEl.textContent = ' (' + secs + 's)';

      clearInterval(timer);
      timer = setInterval(function () {
        secs--;
        if (secs <= 0) {
          clearInterval(timer);
          resendBtn.disabled = false;
          countdownEl.textContent = '';
        } else {
          countdownEl.textContent = ' (' + secs + 's)';
        }
      }, 1000);
    }

    resendBtn.addEventListener('click', function () {
      resendBtn.disabled = true;
      countdownEl.textContent = '';
      document.getElementById('alertError').classList.remove('show');

      fetch('/api/v1/auth/2fa/resend-otp', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': CSRF,
        },
        body: JSON.stringify({ pending_token: pendingToken }),
      })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        if (data.message) {
          document.getElementById('alertSuccessText').textContent = data.message || 'OTP resent.';
          document.getElementById('alertSuccess').classList.add('show');
        }
        startCountdown();
      })
      .catch(function () {
        document.getElementById('alertErrorText').textContent = 'Failed to resend OTP.';
        document.getElementById('alertError').classList.add('show');
        resendBtn.disabled = false;
      });
    });

  })();
</script>
</body>
</html>
