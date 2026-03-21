<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sign In — RechargeHub</title>
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

    .forgot-row {
      display: flex;
      justify-content: flex-end;
      margin-top: 6px;
    }

    .forgot-link {
      font-size: 12.5px;
      color: rgba(255,255,255,.4);
      text-decoration: none;
      transition: color .2s;
    }

    .forgot-link:hover { color: #10b981; }

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
      margin-top: 8px;
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

    .divider {
      border: none;
      border-top: 1px solid rgba(255,255,255,.07);
      margin: 22px 0;
    }

    .footer-link {
      text-align: center;
      font-size: 13.5px;
      color: rgba(255,255,255,.4);
    }

    .footer-link a {
      color: #10b981;
      text-decoration: none;
      font-weight: 500;
    }

    .footer-link a:hover { text-decoration: underline; }
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
    <div class="card-title">Welcome back</div>
    <div class="card-subtitle">Sign in to your RechargeHub account</div>

    <!-- Registration success banner -->
    <div class="alert alert-success" id="registeredBanner">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <span>Registration successful! Please sign in.</span>
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
        <span id="submitText">Sign In</span>
      </button>

    </form>

    <hr class="divider" />
    <div class="footer-link">Don't have an account? <a href="/user/register">Register</a></div>
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
        submitText.textContent = 'Sign In';
      });
    });

  })();
</script>
</body>
</html>
