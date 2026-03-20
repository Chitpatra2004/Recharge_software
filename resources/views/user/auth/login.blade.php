<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login — RechargeHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Inter',system-ui,sans-serif;background:#040d21;color:#f1f5f9;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;position:relative;overflow:hidden}
        body::before{content:'';position:fixed;inset:0;background:radial-gradient(ellipse 80% 60% at 50% 0%,rgba(99,102,241,.18) 0%,transparent 70%),radial-gradient(ellipse 50% 50% at 90% 80%,rgba(139,92,246,.1) 0%,transparent 60%);pointer-events:none}
        body::after{content:'';position:fixed;inset:0;background-image:radial-gradient(circle,rgba(255,255,255,.05) 1px,transparent 1px);background-size:36px 36px;mask-image:radial-gradient(ellipse 70% 70% at 50% 50%,black 30%,transparent 80%);pointer-events:none}
        .card{position:relative;z-index:1;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);border-radius:20px;padding:44px 40px;width:100%;max-width:420px;box-shadow:0 24px 80px rgba(0,0,0,.5);backdrop-filter:blur(20px)}
        .card::before{content:'';position:absolute;top:0;left:40px;right:40px;height:1px;background:linear-gradient(90deg,transparent,rgba(99,102,241,.5),transparent)}
        .brand{display:flex;align-items:center;gap:10px;justify-content:center;margin-bottom:32px;text-decoration:none}
        .brand-icon{width:40px;height:40px;background:linear-gradient(135deg,#2563eb,#7c3aed);border-radius:11px;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 20px rgba(99,102,241,.4)}
        .brand-icon svg{width:22px;height:22px;color:#fff}
        .brand-name{font-size:18px;font-weight:800}
        .brand-name span{background:linear-gradient(90deg,#60a5fa,#a78bfa);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
        h2{font-size:22px;font-weight:800;text-align:center;color:#fff;margin-bottom:6px;letter-spacing:-.5px}
        .sub{font-size:13.5px;color:#64748b;text-align:center;margin-bottom:28px}
        .field{margin-bottom:18px}
        label{display:block;font-size:12px;font-weight:600;color:#94a3b8;margin-bottom:6px;letter-spacing:.3px}
        input{width:100%;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:10px;padding:11px 14px;font-size:14px;color:#f1f5f9;font-family:inherit;transition:border-color .2s,box-shadow .2s;outline:none}
        input::placeholder{color:#475569}
        input:focus{border-color:rgba(99,102,241,.6);box-shadow:0 0 0 3px rgba(99,102,241,.12)}
        .btn{width:100%;background:linear-gradient(135deg,#2563eb,#6366f1);color:#fff;border:none;border-radius:10px;padding:13px;font-size:14px;font-weight:700;cursor:pointer;font-family:inherit;transition:all .2s;box-shadow:0 6px 20px rgba(99,102,241,.35);margin-top:4px;display:flex;align-items:center;justify-content:center;gap:8px}
        .btn:hover{transform:translateY(-1px);box-shadow:0 8px 28px rgba(99,102,241,.5)}
        .btn:disabled{opacity:.6;cursor:not-allowed;transform:none}
        .divider{text-align:center;font-size:12px;color:#334155;margin:20px 0;position:relative}
        .divider::before,.divider::after{content:'';position:absolute;top:50%;width:42%;height:1px;background:rgba(255,255,255,.07)}
        .divider::before{left:0}.divider::after{right:0}
        .link-row{text-align:center;font-size:13px;color:#64748b;margin-top:18px}
        .link-row a{color:#818cf8;text-decoration:none;font-weight:600}
        .link-row a:hover{color:#a5b4fc}
        .error-box{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);border-radius:8px;padding:10px 14px;font-size:13px;color:#fca5a5;margin-bottom:16px;display:none}
        .spinner{width:16px;height:16px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite;display:none}
        @keyframes spin{to{transform:rotate(360deg)}}
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

    <h2>Welcome back</h2>
    <p class="sub">Sign in to your account to continue</p>

    <div class="error-box" id="error-box"></div>

    <div class="field">
        <label>Email Address</label>
        <input type="email" id="email" placeholder="you@example.com" autocomplete="email">
    </div>
    <div class="field">
        <label>Password</label>
        <input type="password" id="password" placeholder="Enter your password" autocomplete="current-password">
    </div>

    <button class="btn" id="login-btn" onclick="doLogin()">
        <div class="spinner" id="spinner"></div>
        <span id="btn-text">Sign In</span>
    </button>

    <div class="divider">or</div>

    <div class="link-row">Don't have an account? <a href="/user/register">Create one</a></div>
</div>

<script>
const TOKEN_KEY = 'user_token';
const USER_KEY  = 'user_data';

document.addEventListener('DOMContentLoaded', () => {
    if (localStorage.getItem(TOKEN_KEY)) window.location.href = '/user/dashboard';
    document.getElementById('password').addEventListener('keydown', e => { if (e.key === 'Enter') doLogin(); });
    document.getElementById('email').addEventListener('keydown', e => { if (e.key === 'Enter') doLogin(); });
});

async function doLogin() {
    const email    = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const errBox   = document.getElementById('error-box');
    errBox.style.display = 'none';

    if (!email || !password) { showError('Please enter your email and password.'); return; }

    setLoading(true);
    try {
        const res  = await fetch('/api/v1/auth/login', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body:    JSON.stringify({ email, password, device_name: 'web' }),
        });
        const data = await res.json();
        if (!res.ok) {
            showError(data.message || data.errors?.email?.[0] || 'Login failed. Please try again.');
            return;
        }
        localStorage.setItem(TOKEN_KEY, data.token);
        localStorage.setItem(USER_KEY,  JSON.stringify(data.user));
        window.location.href = '/user/dashboard';
    } catch (e) {
        showError('Network error. Please check your connection.');
    } finally {
        setLoading(false);
    }
}

function showError(msg) {
    const box = document.getElementById('error-box');
    box.textContent = msg;
    box.style.display = 'block';
}
function setLoading(v) {
    document.getElementById('login-btn').disabled = v;
    document.getElementById('spinner').style.display  = v ? 'block' : 'none';
    document.getElementById('btn-text').textContent   = v ? 'Signing in…' : 'Sign In';
}
</script>
</body>
</html>
