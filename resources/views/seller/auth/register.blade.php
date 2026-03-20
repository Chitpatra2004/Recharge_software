<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Registration — RechargeHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root { --green:#10b981; --green-dark:#059669; --red:#ef4444; --text:#1e293b; --muted:#64748b; }
        body { font-family:'Inter',system-ui,sans-serif; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:20px; position:relative; overflow:hidden; background:#071a10; }
        body::before { content:''; position:fixed; inset:0; background: radial-gradient(ellipse 80% 60% at 20% 10%,#10b98180 0%,transparent 60%), radial-gradient(ellipse 60% 70% at 80% 90%,#0d948860 0%,transparent 55%), linear-gradient(135deg,#071a10 0%,#0a2a1a 40%,#071210 100%); z-index:0; }
        .orb{position:fixed;border-radius:50%;filter:blur(80px);opacity:.2;animation:floatOrb linear infinite;pointer-events:none;z-index:0;}
        .orb-1{width:500px;height:500px;background:#10b981;top:-100px;left:-100px;animation-duration:18s;}
        .orb-2{width:400px;height:400px;background:#0d9488;bottom:-80px;right:-80px;animation-duration:22s;animation-delay:-7s;}
        @keyframes floatOrb{0%{transform:translate(0,0) scale(1)}50%{transform:translate(30px,-40px) scale(1.05)}100%{transform:translate(0,0) scale(1)}}
        .grid-overlay{position:fixed;inset:0;background-image:linear-gradient(rgba(255,255,255,.025) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.025) 1px,transparent 1px);background-size:60px 60px;z-index:0;pointer-events:none;}
        .reg-card{position:relative;z-index:10;background:rgba(255,255,255,.97);border-radius:24px;padding:44px 48px;max-width:620px;width:100%;box-shadow:0 32px 80px rgba(0,0,0,.5),0 0 0 1px rgba(255,255,255,.08);animation:slideUp .5s cubic-bezier(.16,1,.3,1) both;}
        @keyframes slideUp{from{opacity:0;transform:translateY(28px)}to{opacity:1;transform:translateY(0)}}
        .reg-header{text-align:center;margin-bottom:32px;}
        .reg-logo{display:inline-flex;align-items:center;gap:10px;margin-bottom:16px;}
        .reg-logo-icon{width:44px;height:44px;background:linear-gradient(135deg,#10b981,#0d9488);border-radius:13px;display:flex;align-items:center;justify-content:center;}
        .reg-logo-icon svg{width:22px;height:22px;}
        .reg-logo-name{font-size:18px;font-weight:800;color:var(--text);}
        .reg-logo-sub{font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.8px;}
        .reg-header h1{font-size:22px;font-weight:800;color:var(--text);margin-bottom:6px;}
        .reg-header p{font-size:13.5px;color:var(--muted);}
        .form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
        .form-group{margin-bottom:16px;}
        .form-label{display:flex;align-items:center;gap:5px;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:7px;}
        .required-star{color:var(--red);font-size:13px;}
        .input-wrap{position:relative;}
        .input-icon{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:#9ca3af;pointer-events:none;}
        .input-icon svg{width:16px;height:16px;display:block;}
        .form-input{width:100%;padding:11px 14px 11px 40px;border:1.5px solid #e5e7eb;border-radius:10px;font-size:13.5px;font-family:inherit;color:var(--text);background:#f9fafb;transition:all .15s;outline:none;}
        .form-input:focus{border-color:var(--green);box-shadow:0 0 0 3px rgba(16,185,129,.12);background:#fff;}
        .form-input::placeholder{color:#9ca3af;}
        .form-input.is-invalid{border-color:var(--red)!important;box-shadow:0 0 0 3px rgba(239,68,68,.1)!important;}
        .form-input.is-valid{border-color:var(--green)!important;box-shadow:0 0 0 3px rgba(16,185,129,.1)!important;}
        .field-error{display:none;align-items:center;gap:5px;margin-top:5px;font-size:11.5px;color:var(--red);font-weight:500;}
        .field-error.show{display:flex;}
        .field-error svg{width:12px;height:12px;flex-shrink:0;}
        .toggle-pwd{position:absolute;right:13px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9ca3af;padding:4px;border-radius:5px;display:flex;align-items:center;}
        .toggle-pwd svg{width:16px;height:16px;}
        .section-title{font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.8px;margin-bottom:14px;padding-bottom:8px;border-bottom:1px solid #f0f0f0;}
        .alert{padding:11px 14px;border-radius:10px;font-size:13px;margin-bottom:16px;display:none;align-items:flex-start;gap:9px;}
        .alert svg{width:16px;height:16px;flex-shrink:0;margin-top:1px;}
        .alert.show{display:flex;}
        .alert-error{background:#fff1f2;border:1.5px solid #fecdd3;color:#be123c;}
        .alert-success{background:#f0fdf4;border:1.5px solid #bbf7d0;color:#15803d;}
        .btn-submit{width:100%;padding:13px;background:linear-gradient(135deg,#10b981 0%,#0d9488 100%);color:#fff;border:none;border-radius:11px;font-size:14.5px;font-weight:700;font-family:inherit;cursor:pointer;transition:all .2s;display:flex;align-items:center;justify-content:center;gap:8px;box-shadow:0 4px 20px rgba(16,185,129,.4);}
        .btn-submit:hover{box-shadow:0 6px 28px rgba(16,185,129,.5);transform:translateY(-1px);}
        .btn-submit:disabled{opacity:.7;cursor:not-allowed;transform:none;}
        .spinner{width:17px;height:17px;border:2.5px solid rgba(255,255,255,.35);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite;display:none;}
        @keyframes spin{to{transform:rotate(360deg)}}
        .login-link{text-align:center;font-size:13px;color:var(--muted);margin-top:16px;}
        .login-link a{color:var(--green);font-weight:600;text-decoration:none;}
        .login-link a:hover{text-decoration:underline;}
        .info-box{background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:10px;padding:12px 14px;font-size:12.5px;color:#15803d;margin-bottom:20px;display:flex;gap:8px;align-items:flex-start;}
        .info-box svg{width:15px;height:15px;flex-shrink:0;margin-top:1px;}
        @media(max-width:560px){.form-row{grid-template-columns:1fr;}.reg-card{padding:32px 20px;}}
    </style>
</head>
<body>
<div class="orb orb-1"></div><div class="orb orb-2"></div>
<div class="grid-overlay"></div>
<div class="reg-card">
    <div class="reg-header">
        <div class="reg-logo">
            <div class="reg-logo-icon"><svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg></div>
            <div>
                <div class="reg-logo-name">RechargeHub</div>
                <div class="reg-logo-sub">Seller / Reseller Portal</div>
            </div>
        </div>
        <h1>Create Seller Account</h1>
        <p>Register to access our recharge API platform. Admin approval required.</p>
    </div>
    <div class="info-box">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        After registration, your account will be reviewed by admin. You'll be able to login once approved.
    </div>
    <div class="alert alert-error" id="error-alert">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span id="error-msg"></span>
    </div>
    <div class="alert alert-success" id="success-alert">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span id="success-msg"></span>
    </div>
    <form id="reg-form" novalidate autocomplete="on">
        <p class="section-title">Personal Information</p>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="name">Full Name <span class="required-star">*</span></label>
                <div class="input-wrap">
                    <span class="input-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></span>
                    <input type="text" id="name" class="form-input" placeholder="Your full name" maxlength="100" required>
                </div>
                <div class="field-error" id="name-error"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span id="name-error-msg">Name is required.</span></div>
            </div>
            <div class="form-group">
                <label class="form-label" for="mobile">Mobile Number <span class="required-star">*</span></label>
                <div class="input-wrap">
                    <span class="input-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg></span>
                    <input type="tel" id="mobile" class="form-input" placeholder="10-digit mobile" maxlength="10" required>
                </div>
                <div class="field-error" id="mobile-error"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span id="mobile-error-msg">Valid 10-digit mobile required.</span></div>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label" for="email">Email Address <span class="required-star">*</span></label>
            <div class="input-wrap">
                <span class="input-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg></span>
                <input type="email" id="email" class="form-input" placeholder="you@yourdomain.com" maxlength="150" required>
            </div>
            <div class="field-error" id="email-error"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span id="email-error-msg">Valid email required.</span></div>
        </div>
        <p class="section-title" style="margin-top:8px">Set Password</p>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="password">Password <span class="required-star">*</span></label>
                <div class="input-wrap">
                    <span class="input-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></span>
                    <input type="password" id="password" class="form-input" placeholder="Min 8 characters" minlength="8" maxlength="72" required style="padding-right:44px">
                    <button type="button" class="toggle-pwd" onclick="togglePwd('password','eye1')"><svg id="eye1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
                </div>
                <div class="field-error" id="password-error"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span id="password-error-msg">Min 8 characters required.</span></div>
            </div>
            <div class="form-group">
                <label class="form-label" for="password_confirmation">Confirm Password <span class="required-star">*</span></label>
                <div class="input-wrap">
                    <span class="input-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></span>
                    <input type="password" id="password_confirmation" class="form-input" placeholder="Re-enter password" maxlength="72" required style="padding-right:44px">
                    <button type="button" class="toggle-pwd" onclick="togglePwd('password_confirmation','eye2')"><svg id="eye2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
                </div>
                <div class="field-error" id="confirm-error"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>Passwords do not match.</span></div>
            </div>
        </div>
        <button type="submit" class="btn-submit" id="submit-btn">
            <div class="spinner" id="spinner"></div>
            <span id="btn-text">Register Seller Account</span>
        </button>
    </form>
    <div class="login-link">Already have an account? <a href="/seller/login">Sign In</a></div>
</div>
<script>
const el   = id => document.getElementById(id);
const show = id => el(id).classList.add('show');
const hide = id => el(id).classList.remove('show');
(function(){ if(localStorage.getItem('seller_token')) window.location.href='/seller/dashboard'; })();
function togglePwd(id,iconId){
    const inp=el(id),isText=inp.type==='text'; inp.type=isText?'password':'text';
    el(iconId).innerHTML=isText
        ?`<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>`
        :`<path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>`;
}
function fieldOk(id,eid){el(id).classList.remove('is-invalid');el(id).classList.add('is-valid');hide(eid);}
function fieldErr(id,eid,mid,msg){el(id).classList.add('is-invalid');el(id).classList.remove('is-valid');if(mid)el(mid).textContent=msg;show(eid);}
el('reg-form').addEventListener('submit',async e=>{
    e.preventDefault(); hide('error-alert'); hide('success-alert');
    const name=el('name').value.trim(), mobile=el('mobile').value.trim(),
          email=el('email').value.trim(), pwd=el('password').value, cpwd=el('password_confirmation').value;
    let ok=true;
    if(!name){fieldErr('name','name-error','name-error-msg','Full name is required.');ok=false;}else fieldOk('name','name-error');
    if(!/^[6-9]\d{9}$/.test(mobile)){fieldErr('mobile','mobile-error','mobile-error-msg','Valid 10-digit Indian mobile required.');ok=false;}else fieldOk('mobile','mobile-error');
    if(!email||!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)){fieldErr('email','email-error','email-error-msg','Valid email required.');ok=false;}else fieldOk('email','email-error');
    if(pwd.length<8){fieldErr('password','password-error','password-error-msg','Min 8 characters required.');ok=false;}else fieldOk('password','password-error');
    if(pwd!==cpwd){fieldErr('password_confirmation','confirm-error',null,'');ok=false;}else{el('password_confirmation').classList.remove('is-invalid');el('password_confirmation').classList.add('is-valid');hide('confirm-error');}
    if(!ok)return;
    el('submit-btn').disabled=true; el('spinner').style.display='block'; el('btn-text').textContent='Registering…';
    try{
        const res=await fetch('/api/v1/seller/auth/register',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify({name,mobile,email,password:pwd,password_confirmation:cpwd})});
        const data=await res.json();
        if(res.ok){
            el('success-msg').textContent='Registration successful! Your account is pending admin approval. You will be notified once approved.';
            show('success-alert');
            el('reg-form').reset();
            document.querySelectorAll('.form-input').forEach(i=>i.classList.remove('is-valid','is-invalid'));
        }else{
            const errs=data.errors?Object.values(data.errors).flat().join(' '):data.message||'Registration failed.';
            el('error-msg').textContent=errs; show('error-alert');
        }
    }catch{el('error-msg').textContent='Unable to reach server. Check your connection.'; show('error-alert');}
    finally{el('submit-btn').disabled=false; el('spinner').style.display='none'; el('btn-text').textContent='Register Seller Account';}
});
</script>
</body>
</html>
