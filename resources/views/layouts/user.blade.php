<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title','Dashboard') — RechargeHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{
            --bg:#040d21;--bg2:#070f2b;--card:rgba(255,255,255,.04);--card2:rgba(255,255,255,.08);
            --border:rgba(255,255,255,.09);--border2:rgba(255,255,255,.14);
            --text:#f1f5f9;--muted:#94a3b8;--muted2:#64748b;
            --blue:#3b82f6;--blue-dk:#2563eb;--indigo:#6366f1;
            --green:#10b981;--orange:#f59e0b;--red:#ef4444;--purple:#8b5cf6;
            --primary:var(--blue);   /* alias used by some pages */
            --sidebar:220px;--radius:14px;
        }
        body{font-family:'Inter',system-ui,sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;font-size:14px;line-height:1.5}

        /* SIDEBAR */
        .sidebar{width:var(--sidebar);min-height:100vh;background:var(--bg2);border-right:1px solid var(--border);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:100;overflow-y:auto;scrollbar-width:none}
        .sidebar::-webkit-scrollbar{display:none}
        .sb-brand{padding:20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px;text-decoration:none}
        .sb-brand-icon{width:34px;height:34px;background:linear-gradient(135deg,var(--blue-dk),var(--indigo));border-radius:9px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(99,102,241,.35)}
        .sb-brand-icon svg{width:18px;height:18px;color:#fff}
        .sb-brand-name{font-size:14px;font-weight:800;color:#fff}
        .sb-brand-name span{background:linear-gradient(90deg,#60a5fa,#a78bfa);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
        .sb-nav{padding:10px 0;flex:1}
        .sb-section{padding:14px 18px 4px;font-size:9.5px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--muted2)}
        .sb-item{display:flex;align-items:center;gap:10px;padding:9px 18px;color:var(--muted);text-decoration:none;font-size:13px;font-weight:500;transition:all .15s;position:relative}
        .sb-item:hover{background:var(--card2);color:var(--text)}
        .sb-item.active{background:var(--card2);color:#fff}
        .sb-item.active::before{content:'';position:absolute;left:0;top:6px;bottom:6px;width:3px;background:var(--blue);border-radius:0 3px 3px 0}
        .sb-item svg{width:16px;height:16px;flex-shrink:0;opacity:.7}
        .sb-item.active svg,.sb-item:hover svg{opacity:1}
        .sb-footer{padding:14px 18px;border-top:1px solid var(--border)}
        .sb-user{display:flex;align-items:center;gap:9px}
        .sb-avatar{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--blue-dk),var(--indigo));display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff;flex-shrink:0}
        .sb-user-name{font-size:12.5px;font-weight:600;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .sb-user-role{font-size:11px;color:var(--muted2)}
        .sb-logout{background:none;border:none;cursor:pointer;color:var(--muted2);padding:4px;border-radius:6px;transition:color .15s;margin-left:auto}
        .sb-logout:hover{color:var(--red)}
        .sb-logout svg{width:15px;height:15px}

        /* MAIN */
        .main{margin-left:var(--sidebar);flex:1;min-height:100vh;display:flex;flex-direction:column}
        .topbar{background:var(--topbar-bg,rgba(7,15,43,.9));border-bottom:1px solid var(--border);padding:0 24px;height:56px;display:flex;align-items:center;gap:14px;position:sticky;top:0;z-index:50;backdrop-filter:blur(12px)}
        .topbar-title{font-size:15px;font-weight:600;color:var(--text);flex:1}
        .topbar-time{font-size:12px;color:var(--muted2);background:var(--card);padding:5px 12px;border-radius:6px;border:1px solid var(--border)}
        .page-body{padding:24px;flex:1}

        /* CARDS */
        .card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius)}
        .card-header{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px}
        .card-title{font-size:14px;font-weight:600;color:var(--text)}
        .card-body{padding:20px}
        .card-footer{padding:12px 20px;border-top:1px solid var(--border);display:flex;align-items:center}

        /* STAT CARDS */
        .stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px}
        .stat-card{border-radius:var(--radius);padding:20px;border:1px solid var(--border);position:relative;overflow:hidden}
        .stat-card.blue{background:linear-gradient(135deg,rgba(37,99,235,.15),rgba(99,102,241,.08))}
        .stat-card.green{background:linear-gradient(135deg,rgba(16,185,129,.15),rgba(20,184,166,.08))}
        .stat-card.orange{background:linear-gradient(135deg,rgba(245,158,11,.15),rgba(249,115,22,.08))}
        .stat-card.red{background:linear-gradient(135deg,rgba(239,68,68,.15),rgba(220,38,38,.08))}
        .stat-label{font-size:12px;font-weight:500;color:var(--muted);margin-bottom:10px}
        .stat-value{font-size:26px;font-weight:800;letter-spacing:-1px}
        .stat-card.blue .stat-value{color:#60a5fa}
        .stat-card.green .stat-value{color:#34d399}
        .stat-card.orange .stat-value{color:#fbbf24}
        .stat-card.red .stat-value{color:#f87171}
        .stat-sub{font-size:11px;color:var(--muted2);margin-top:4px}

        /* TABLE */
        .table-wrap{overflow-x:auto}
        table{width:100%;border-collapse:collapse}
        thead th{padding:10px 14px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.6px;color:var(--muted2);background:rgba(255,255,255,.02);border-bottom:1px solid var(--border)}
        tbody td{padding:12px 14px;font-size:13px;color:var(--text);border-bottom:1px solid var(--border)}
        tbody tr:last-child td{border-bottom:none}
        tbody tr:hover td{background:var(--card2)}

        /* BADGES */
        .badge{font-size:10px;font-weight:600;padding:2px 8px;border-radius:20px}
        .badge.success{background:rgba(16,185,129,.15);color:#34d399;border:1px solid rgba(16,185,129,.2)}
        .badge.failure{background:rgba(239,68,68,.15);color:#f87171;border:1px solid rgba(239,68,68,.2)}
        .badge.pending{background:rgba(245,158,11,.15);color:#fbbf24;border:1px solid rgba(245,158,11,.2)}

        /* BTNS */
        .btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;border:none;transition:all .15s;text-decoration:none;font-family:inherit}
        .btn-primary{background:linear-gradient(135deg,var(--blue-dk),var(--indigo));color:#fff;box-shadow:0 4px 14px rgba(99,102,241,.3)}
        .btn-primary:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(99,102,241,.45)}
        .btn-outline{background:var(--card);color:var(--muted);border:1px solid var(--border)}
        .btn-outline:hover{background:var(--card2);color:var(--text)}
        .btn-sm{padding:6px 12px;font-size:12px}
        .btn svg{width:14px;height:14px}

        /* LOADING */
        .loading{display:flex;align-items:center;justify-content:center;padding:40px;gap:10px;color:var(--muted);font-size:13px}
        .spinner{width:18px;height:18px;border:2px solid var(--border);border-top-color:var(--blue);border-radius:50%;animation:spin .7s linear infinite}
        @keyframes spin{to{transform:rotate(360deg)}}

        /* BREADCRUMB */
        .breadcrumb{display:flex;align-items:center;gap:6px;font-size:12px;color:var(--muted2);margin-bottom:20px}
        .breadcrumb a{color:var(--blue);text-decoration:none;font-weight:500}
        .breadcrumb svg{width:12px;height:12px;color:var(--border)}

        /* ── Force dark chrome on ALL native form controls ───────────── */
        input[type="date"],
        input[type="time"],
        input[type="datetime-local"],
        input[type="month"],
        input[type="week"] { color-scheme: dark }

        /* ── Custom Select Component ──────────────────────────────────── */
        .csel-wrap{position:relative;display:block;width:100%}
        .csel-btn{display:flex;align-items:center;justify-content:space-between;cursor:pointer;user-select:none;width:100%;background:var(--card2,#0d1b3e);border:1px solid var(--border2,rgba(255,255,255,.15));border-radius:8px;padding:9px 12px;font-size:13px;color:var(--text,#f1f5f9);font-family:inherit;box-sizing:border-box;outline:none;line-height:1.4}
        .csel-btn:focus{border-color:var(--primary,var(--blue,#3b82f6))}
        .csel-wrap.open .csel-btn{border-color:var(--primary,var(--blue,#3b82f6))}
        .csel-btn::after{content:'';width:0;height:0;border-left:4px solid transparent;border-right:4px solid transparent;border-top:5px solid var(--muted,#94a3b8);flex-shrink:0;margin-left:8px;transition:transform .15s}
        .csel-wrap.open .csel-btn::after{transform:rotate(180deg)}
        .csel-label{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .csel-list{position:absolute;top:calc(100% + 4px);left:0;right:0;background:var(--card2,#0d1b3e);border:1px solid var(--border2,rgba(255,255,255,.15));border-radius:8px;z-index:9999;max-height:200px;overflow-y:auto;box-shadow:0 8px 32px rgba(0,0,0,.55);display:none;list-style:none;margin:0;padding:4px 0}
        .csel-wrap.open .csel-list{display:block}
        .csel-opt{padding:9px 12px;font-size:13px;color:var(--text,#f1f5f9);cursor:pointer;transition:background .1s;list-style:none}
        .csel-opt:hover,.csel-opt.focused{background:var(--primary,var(--blue,#3b82f6));color:#fff}
        .csel-opt.selected{color:var(--primary,var(--blue,#3b82f6));font-weight:600}
        .csel-opt[data-value=""]{color:var(--muted,#94a3b8)}

        /* ── LIGHT SCHEME: sidebar stays dark-navy; override its text ─── */
        html[data-scheme="light"] .sidebar{background:#1a2035;border-right-color:rgba(255,255,255,.08)}
        html[data-scheme="light"] .sb-section{color:rgba(255,255,255,.35)}
        html[data-scheme="light"] .sb-item{color:rgba(255,255,255,.65)}
        html[data-scheme="light"] .sb-item:hover,
        html[data-scheme="light"] .sb-item.active{background:rgba(255,255,255,.1);color:#fff}
        html[data-scheme="light"] .sb-item.active::before{background:var(--blue)}
        html[data-scheme="light"] .sb-footer{border-top-color:rgba(255,255,255,.1)}
        html[data-scheme="light"] .sb-user-role{color:rgba(255,255,255,.5)}
        html[data-scheme="light"] .sb-logout{color:rgba(255,255,255,.4)}
        html[data-scheme="light"] .sb-logout:hover{color:#f87171}
        html[data-scheme="light"] .topbar{box-shadow:0 1px 4px rgba(0,0,0,.08)}
        html[data-scheme="light"] thead th{background:#f8faff}
        /* Stat cards — stronger colors in light mode */
        html[data-scheme="light"] .stat-card.blue  {background:linear-gradient(135deg,#dbeafe,#e0e7ff);border-color:#bfdbfe}
        html[data-scheme="light"] .stat-card.green {background:linear-gradient(135deg,#d1fae5,#ccfbf1);border-color:#6ee7b7}
        html[data-scheme="light"] .stat-card.orange{background:linear-gradient(135deg,#fef3c7,#ffedd5);border-color:#fcd34d}
        html[data-scheme="light"] .stat-card.red   {background:linear-gradient(135deg,#fee2e2,#fce7f3);border-color:#fca5a5}
        html[data-scheme="light"] .stat-card.blue  .stat-value{color:#1d4ed8}
        html[data-scheme="light"] .stat-card.green .stat-value{color:#047857}
        html[data-scheme="light"] .stat-card.orange .stat-value{color:#b45309}
        html[data-scheme="light"] .stat-card.red   .stat-value{color:#dc2626}
        html[data-scheme="light"] .stat-label{color:var(--muted)}
        html[data-scheme="light"] .stat-sub{color:var(--muted2)}
        /* Cards and tables */
        html[data-scheme="light"] .card{box-shadow:0 1px 4px rgba(0,0,0,.06)}
        html[data-scheme="light"] .btn-outline{background:#fff;border-color:var(--border2)}
        html[data-scheme="light"] tbody tr:hover td{background:#f1f5f9}
        html[data-scheme="light"] input[type="date"],
        html[data-scheme="light"] input[type="time"],
        html[data-scheme="light"] input[type="datetime-local"],
        html[data-scheme="light"] input[type="month"],
        html[data-scheme="light"] input[type="week"]{color-scheme:light}

        @media(max-width:768px){
            .sidebar{transform:translateX(-100%)}
            .sidebar.open{transform:none}
            .main{margin-left:0}
            .stats-grid{grid-template-columns:repeat(2,1fr)}
        }

        /* ── THEME PANEL ─────────────────────────────────────────────── */
        .theme-panel-overlay{position:fixed;inset:0;z-index:199;background:rgba(0,0,0,.5);display:none;backdrop-filter:blur(2px)}
        .theme-panel-overlay.open{display:block}
        .theme-panel{position:fixed;top:0;right:-310px;width:290px;height:100vh;background:var(--bg2,#070f2b);border-left:1px solid var(--border2,rgba(255,255,255,.13));z-index:200;display:flex;flex-direction:column;transition:right .28s cubic-bezier(.16,1,.3,1);box-shadow:-8px 0 40px rgba(0,0,0,.5)}
        .theme-panel.open{right:0}
        .tp-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px}
        .tp-head h3{font-size:14px;font-weight:700;color:var(--text,#f1f5f9);flex:1}
        .tp-close{background:none;border:none;cursor:pointer;color:var(--muted);padding:4px;border-radius:6px;display:flex;align-items:center;transition:color .15s}
        .tp-close:hover{color:var(--text)}
        .tp-close svg{width:16px;height:16px}
        .tp-body{flex:1;overflow-y:auto;padding:16px 20px}
        .tp-sect{font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--muted2);margin-bottom:10px;margin-top:4px}
        .tp-presets{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:4px}
        .tp-preset{padding:10px 12px;border-radius:10px;border:2px solid var(--border);cursor:pointer;text-align:left;transition:all .15s;background:var(--card,rgba(255,255,255,.04));width:100%}
        .tp-preset:hover{border-color:var(--blue)}
        .tp-preset.active{border-color:var(--blue);box-shadow:0 0 0 3px color-mix(in srgb,var(--blue) 20%,transparent)}
        .tp-swatches{display:flex;gap:4px;margin-bottom:6px}
        .tp-swatch{width:14px;height:14px;border-radius:3px;border:1px solid rgba(255,255,255,.1)}
        .tp-name{font-size:12px;font-weight:600;color:var(--text)}
        .tp-divider{height:1px;background:var(--border);margin:16px 0}
        .tp-custom{display:flex;flex-direction:column;gap:12px}
        .tp-color-row{display:flex;align-items:center;justify-content:space-between}
        .tp-color-label{font-size:12.5px;color:var(--muted);font-weight:500}
        .tp-color-input{width:40px;height:32px;border:1px solid var(--border);border-radius:8px;cursor:pointer;padding:3px;background:var(--card)}
        .tp-apply-btn{width:100%;padding:9px;border-radius:8px;background:linear-gradient(135deg,var(--blue-dk,#2563eb),var(--indigo,#6366f1));color:#fff;border:none;font-size:13px;font-weight:600;cursor:pointer;margin-top:4px;transition:opacity .15s;font-family:inherit}
        .tp-apply-btn:hover{opacity:.88}
        .tp-foot{padding:14px 20px;border-top:1px solid var(--border)}
        .tp-reset{width:100%;padding:9px;border-radius:8px;border:1px solid var(--border);background:transparent;color:var(--muted);font-size:13px;font-weight:600;cursor:pointer;transition:all .15s;font-family:inherit}
        .tp-reset:hover{background:var(--card2);color:var(--text)}
        .tp-icon-btn{width:34px;height:34px;border:1px solid var(--border);border-radius:8px;background:var(--card);cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--muted);transition:all .15s;flex-shrink:0}
        .tp-icon-btn:hover{background:var(--card2);color:var(--text)}
        .tp-icon-btn svg{width:17px;height:17px}
    </style>
    <script>(function(){function _lum(h){if(!h||h[0]!=='#'||h.length<7)return 0;var r=parseInt(h.slice(1,3),16),g=parseInt(h.slice(3,5),16),b=parseInt(h.slice(5,7),16);return(r*299+g*587+b*114)/1000;}try{var s=localStorage.getItem('rh_user_theme');if(s){var d=JSON.parse(s);Object.entries(d.vars).forEach(function(e){document.documentElement.style.setProperty(e[0],e[1]);});if(_lum(d.vars['--bg'])>128)document.documentElement.dataset.scheme='light';return;}var u=localStorage.getItem('rh_theme');var m={'Cosmic':{'--bg':'#040d21','--bg2':'#070f2b','--topbar-bg':'#070f2b','--blue':'#3b82f6','--blue-dk':'#2563eb','--indigo':'#6366f1'},'Midnight':{'--bg':'#050505','--bg2':'#0f0f0f','--topbar-bg':'#0f0f0f','--blue':'#60a5fa','--blue-dk':'#2563eb','--indigo':'#818cf8'},'Violet':{'--bg':'#0f0723','--bg2':'#160d33','--topbar-bg':'#160d33','--blue':'#a78bfa','--blue-dk':'#7c3aed','--indigo':'#8b5cf6'},'Forest':{'--bg':'#020d07','--bg2':'#041a0d','--topbar-bg':'#041a0d','--blue':'#34d399','--blue-dk':'#059669','--indigo':'#6366f1'},'Ocean':{'--bg':'#020c18','--bg2':'#031221','--topbar-bg':'#031221','--blue':'#38bdf8','--blue-dk':'#0284c7','--indigo':'#0ea5e9'},'Ember':{'--bg':'#150500','--bg2':'#1f0a00','--topbar-bg':'#1f0a00','--blue':'#fb923c','--blue-dk':'#ea580c','--indigo':'#f97316'}};if(u&&m[u]){Object.entries(m[u]).forEach(function(e){document.documentElement.style.setProperty(e[0],e[1]);});}}catch(e){}})();</script>
    @stack('head')
</head>
<body>

<aside class="sidebar" id="sidebar">
    <a href="/user/dashboard" class="sb-brand">
        <div class="sb-brand-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg></div>
        <span class="sb-brand-name">Recharge<span>Hub</span></span>
    </a>

    <nav class="sb-nav">
        <div class="sb-section">Main</div>
        <a href="/user/dashboard" class="sb-item {{ request()->is('user/dashboard') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Dashboard
        </a>
        <a href="/user/recharges" class="sb-item {{ request()->is('user/recharges') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            Recharges
        </a>
        <a href="/user/bbps" class="sb-item {{ request()->is('user/bbps') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Bill Payments
        </a>
        <div class="sb-section">Finance</div>
        <a href="/user/wallet" class="sb-item {{ request()->is('user/wallet') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            Wallet
        </a>
        <a href="/user/add-money" class="sb-item {{ request()->is('user/add-money') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Add Money
        </a>
        <a href="/user/transactions" class="sb-item {{ request()->is('user/transactions') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            Transactions
        </a>
        <div class="sb-section">More</div>
        <a href="/user/complaints" class="sb-item {{ request()->is('user/complaints') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            Complaints
        </a>
        <a href="/user/reports" class="sb-item {{ request()->is('user/reports') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Reports
        </a>
        <a href="/user/support" class="sb-item {{ request()->is('user/support') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            Help & Support
        </a>
        <div class="sb-section">Account</div>
        <a href="/user/profile" class="sb-item {{ request()->is('user/profile') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            My Profile
        </a>
        <a href="/user/terms" class="sb-item {{ request()->is('user/terms') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Terms & Conditions
        </a>
    </nav>

    <div class="sb-footer">
        <div class="sb-user">
            <div class="sb-avatar" id="sb-avatar">U</div>
            <div style="flex:1;min-width:0">
                <div class="sb-user-name" id="sb-name">Loading…</div>
                <div class="sb-user-role" id="sb-role">—</div>
            </div>
            <button class="sb-logout" onclick="lockScreen()" title="Lock Screen" style="margin-right:2px">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </button>
            <button class="sb-logout" onclick="confirmLogout()" title="Logout">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            </button>
        </div>
    </div>
</aside>

<div class="main">
    <header class="topbar">
        <div class="topbar-title">@yield('page-title','Dashboard')</div>
        <div class="topbar-time" id="clock">—</div>
        <button class="tp-icon-btn" onclick="openThemePanel()" title="Theme Customizer">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
        </button>
    </header>
    <div class="page-body">
        @yield('content')
    </div>
</div>

<script>
const U_TOKEN = 'user_token';
const U_DATA  = 'user_data';

function getToken()   { return localStorage.getItem(U_TOKEN); }
function getUserData(){ try { return JSON.parse(localStorage.getItem(U_DATA)||'{}'); } catch { return {}; } }

function requireAuth() {
    if (!getToken()) { window.location.href = '/user/login'; return false; }
    return true;
}

function _clearSession() {
    localStorage.removeItem(U_TOKEN);
    localStorage.removeItem(U_DATA);
}

function doLogout() {
    const t = getToken();
    _clearSession();
    if (t) {
        fetch('/api/v1/auth/logout', { method:'POST', headers:{ Authorization:'Bearer '+t, Accept:'application/json' }})
        .finally(() => { window.location.href = '/user/login'; });
    } else { window.location.href = '/user/login'; }
}

function confirmLogout() {
    document.getElementById('logout-modal').style.display = 'flex';
}

async function apiFetch(url, opts = {}) {
    const token = getToken();
    opts.headers = Object.assign({ 'Authorization':'Bearer '+token, 'Accept':'application/json', 'Content-Type':'application/json' }, opts.headers||{});
    const res = await fetch(url, opts);
    if (res.status === 401) { localStorage.removeItem(U_TOKEN); window.location.href = '/user/login'; return null; }
    return res;
}

function fmtNum(n)  { return n==null?'—':Number(n).toLocaleString('en-IN'); }
function fmtAmt(n)  { return n==null?'—':'₹'+Number(n).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2}); }
function fmtAgo(d)  { if(!d)return'—'; const s=Math.floor((Date.now()-new Date(d))/1000); if(s<60)return s+'s ago'; if(s<3600)return Math.floor(s/60)+'m ago'; if(s<86400)return Math.floor(s/3600)+'h ago'; return Math.floor(s/86400)+'d ago'; }

function bootSidebar() {
    const u = getUserData();
    if (u.name) {
        document.getElementById('sb-name').textContent  = u.name;
        document.getElementById('sb-role').textContent  = (u.role||'').replace('_',' ');
        document.getElementById('sb-avatar').textContent = u.name.charAt(0).toUpperCase();
    }
}

function updateClock() {
    document.getElementById('clock').textContent = new Date().toLocaleTimeString('en-IN',{hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:true});
}
setInterval(updateClock,1000); updateClock();

document.addEventListener('DOMContentLoaded', () => { requireAuth(); bootSidebar(); initSession(); });

// ── SESSION MANAGEMENT ────────────────────────────────────────────
const LOCK_AFTER_MS   = 15 * 60 * 1000;   // 15 min → lock
const LOGOUT_AFTER_MS = 30 * 60 * 1000;   // 30 min → auto-logout
let _lockTimer = null, _logoutTimer = null, _countdownInterval = null;
let _isLocked = false;

function initSession() {
    resetIdleTimers();
    ['mousemove','mousedown','keydown','touchstart','scroll','click'].forEach(e =>
        document.addEventListener(e, resetIdleTimers, { passive: true })
    );
}

function resetIdleTimers() {
    if (_isLocked) return;
    clearTimeout(_lockTimer);
    clearTimeout(_logoutTimer);
    _lockTimer   = setTimeout(lockScreen,        LOCK_AFTER_MS);
    _logoutTimer = setTimeout(_sessionLogout,    LOGOUT_AFTER_MS);
}

function lockScreen() {
    if (_isLocked) return;
    _isLocked = true;
    clearTimeout(_lockTimer);
    const u = getUserData();
    const overlay = document.getElementById('lock-overlay');
    document.getElementById('lock-user-name').textContent  = u.name  || 'User';
    document.getElementById('lock-user-email').textContent = u.email || '';
    document.getElementById('lock-avatar-text').textContent = (u.name||'U').charAt(0).toUpperCase();
    document.getElementById('lock-password').value = '';
    document.getElementById('lock-error').style.display = 'none';
    overlay.style.display = 'flex';
    document.getElementById('lock-password').focus();
    // countdown to auto-logout
    let remaining = (LOGOUT_AFTER_MS - LOCK_AFTER_MS) / 1000;
    clearInterval(_countdownInterval);
    _countdownInterval = setInterval(() => {
        remaining--;
        const m = Math.floor(remaining / 60), s = remaining % 60;
        const el = document.getElementById('lock-countdown');
        if (el) el.textContent = `Auto-logout in ${m}:${String(s).padStart(2,'0')}`;
        if (remaining <= 0) { clearInterval(_countdownInterval); _sessionLogout(); }
    }, 1000);
}

function _sessionLogout() {
    clearInterval(_countdownInterval);
    _isLocked = false;
    doLogout();
}

async function unlockScreen() {
    const pwd   = document.getElementById('lock-password').value;
    const errEl = document.getElementById('lock-error');
    const btn   = document.getElementById('lock-unlock-btn');
    errEl.style.display = 'none';
    if (!pwd) { errEl.textContent = 'Enter your password to unlock.'; errEl.style.display = 'block'; return; }

    btn.disabled = true;
    btn.textContent = 'Verifying…';
    const u = getUserData();
    try {
        const res = await fetch('/api/v1/auth/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ email: u.email, password: pwd, device_name: 'web' })
        });
        if (res.ok) {
            const data = await res.json();
            localStorage.setItem(U_TOKEN, data.token);
            if (data.user) localStorage.setItem(U_DATA, JSON.stringify(data.user));
            document.getElementById('lock-overlay').style.display = 'none';
            clearInterval(_countdownInterval);
            _isLocked = false;
            resetIdleTimers();
        } else {
            errEl.textContent = 'Incorrect password. Please try again.';
            errEl.style.display = 'block';
        }
    } catch { errEl.textContent = 'Network error. Try again.'; errEl.style.display = 'block'; }
    btn.disabled = false;
    btn.textContent = 'Unlock';
}
</script>
@stack('scripts')

<!-- ── THEME PANEL ──────────────────────────────────────────────────────── -->
<div id="tp-overlay" class="theme-panel-overlay" onclick="closeThemePanel()"></div>
<div id="tp-panel" class="theme-panel">
    <div class="tp-head">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;color:var(--blue)"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
        <h3>Theme Customizer</h3>
        <button class="tp-close" onclick="closeThemePanel()">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    <div class="tp-body">
        <div class="tp-sect">Preset Themes</div>
        <div class="tp-presets" id="tp-presets-grid"></div>
        <div class="tp-divider"></div>
        <div class="tp-sect">Custom Colors</div>
        <div class="tp-custom">
            <div class="tp-color-row">
                <span class="tp-color-label">Background</span>
                <input type="color" class="tp-color-input" id="tc-bg" value="#040d21">
            </div>
            <div class="tp-color-row">
                <span class="tp-color-label">Sidebar / Panel</span>
                <input type="color" class="tp-color-input" id="tc-bg2" value="#070f2b">
            </div>
            <div class="tp-color-row">
                <span class="tp-color-label">Accent Color</span>
                <input type="color" class="tp-color-input" id="tc-ac" value="#3b82f6">
            </div>
            <button class="tp-apply-btn" onclick="_applyCustomUser()">&#10003; Apply Custom Colors</button>
        </div>
    </div>
    <div class="tp-foot">
        <button class="tp-reset" onclick="_resetUserTheme()">&#8635; Reset to Default</button>
    </div>
</div>

<script>
/* ── Theme System (User) ───────────────────────────────────────────────── */
// Each theme defines ALL CSS variables used by buttons, cards, badges etc.
// so no variable is left at an inconsistent default after switching.
const _DARK_BASE = {
    '--card':'rgba(255,255,255,.04)','--card2':'rgba(255,255,255,.08)',
    '--border':'rgba(255,255,255,.09)','--border2':'rgba(255,255,255,.14)',
    '--text':'#f1f5f9','--muted':'#94a3b8','--muted2':'#64748b',
    '--green':'#10b981','--orange':'#f59e0b','--red':'#ef4444','--purple':'#8b5cf6'
};
const _LIGHT_BASE = {
    '--card':'#ffffff','--card2':'#f1f5f9',
    '--border':'#e2e8f0','--border2':'#cbd5e1',
    '--text':'#1e293b','--muted':'#475569','--muted2':'#94a3b8',
    '--green':'#059669','--orange':'#d97706','--red':'#dc2626','--purple':'#7c3aed'
};
const _THEMES_U = {
    /* ── Dark themes ── */
    'Cosmic Dark': { sw:['#040d21','#3b82f6','#6366f1'], vars:Object.assign({}, _DARK_BASE, {'--bg':'#040d21','--bg2':'#070f2b','--topbar-bg':'#070f2b','--blue':'#3b82f6','--blue-dk':'#2563eb','--indigo':'#6366f1'}) },
    'Deep Ocean':  { sw:['#020c18','#38bdf8','#0ea5e9'], vars:Object.assign({}, _DARK_BASE, {'--bg':'#020c18','--bg2':'#031221','--topbar-bg':'#031221','--blue':'#38bdf8','--blue-dk':'#0284c7','--indigo':'#0ea5e9'}) },
    'Midnight':    { sw:['#050505','#60a5fa','#818cf8'], vars:Object.assign({}, _DARK_BASE, {'--bg':'#050505','--bg2':'#0f0f0f','--topbar-bg':'#0f0f0f','--blue':'#60a5fa','--blue-dk':'#2563eb','--indigo':'#818cf8','--green':'#34d399','--orange':'#fbbf24','--red':'#f87171','--purple':'#a78bfa'}) },
    'Purple Haze': { sw:['#0f0723','#a78bfa','#7c3aed'], vars:Object.assign({}, _DARK_BASE, {'--bg':'#0f0723','--bg2':'#160d33','--topbar-bg':'#160d33','--blue':'#a78bfa','--blue-dk':'#7c3aed','--indigo':'#8b5cf6','--purple':'#c084fc'}) },
    'Forest Night':{ sw:['#020d07','#34d399','#059669'], vars:Object.assign({}, _DARK_BASE, {'--bg':'#020d07','--bg2':'#041a0d','--topbar-bg':'#041a0d','--blue':'#34d399','--blue-dk':'#059669','--indigo':'#6366f1'}) },
    'Ember Dark':  { sw:['#150500','#fb923c','#ea580c'], vars:Object.assign({}, _DARK_BASE, {'--bg':'#150500','--bg2':'#1f0a00','--topbar-bg':'#1f0a00','--blue':'#fb923c','--blue-dk':'#ea580c','--indigo':'#f97316'}) },
    'Slate Dark':  { sw:['#0d1117','#58a6ff','#1f6feb'], vars:Object.assign({}, _DARK_BASE, {'--bg':'#0d1117','--bg2':'#161b22','--topbar-bg':'#161b22','--blue':'#58a6ff','--blue-dk':'#1f6feb','--indigo':'#8b949e','--muted':'#8b949e','--muted2':'#6e7681'}) },
    'Rose Night':  { sw:['#150012','#f472b6','#db2777'], vars:Object.assign({}, _DARK_BASE, {'--bg':'#150012','--bg2':'#200018','--topbar-bg':'#200018','--blue':'#f472b6','--blue-dk':'#db2777','--indigo':'#ec4899','--red':'#fb7185'}) },
    /* ── Light themes ── */
    'Classic Light':{ sw:['#f0f4f8','#2563eb','#1a2035'], light:true, vars:Object.assign({}, _LIGHT_BASE, {'--bg':'#f0f4f8','--bg2':'#1a2035','--topbar-bg':'#ffffff','--blue':'#2563eb','--blue-dk':'#1d4ed8','--indigo':'#4f46e5'}) },
    'Violet Light': { sw:['#f5f3ff','#7c3aed','#1e1b4b'], light:true, vars:Object.assign({}, _LIGHT_BASE, {'--bg':'#f5f3ff','--bg2':'#1e1b4b','--topbar-bg':'#ffffff','--blue':'#7c3aed','--blue-dk':'#6d28d9','--indigo':'#8b5cf6'}) },
    'Forest Light': { sw:['#f0fdf4','#16a34a','#052e16'], light:true, vars:Object.assign({}, _LIGHT_BASE, {'--bg':'#f0fdf4','--bg2':'#052e16','--topbar-bg':'#ffffff','--blue':'#16a34a','--blue-dk':'#15803d','--indigo':'#22c55e'}) },
    'Ocean Light':  { sw:['#f0fdfa','#0d9488','#042f2e'], light:true, vars:Object.assign({}, _LIGHT_BASE, {'--bg':'#f0fdfa','--bg2':'#042f2e','--topbar-bg':'#ffffff','--blue':'#0d9488','--blue-dk':'#0f766e','--indigo':'#06b6d4'}) },
    'Sunset Light': { sw:['#fff7ed','#ea580c','#431407'], light:true, vars:Object.assign({}, _LIGHT_BASE, {'--bg':'#fff7ed','--bg2':'#431407','--topbar-bg':'#ffffff','--blue':'#ea580c','--blue-dk':'#c2410c','--indigo':'#f97316'}) },
};

// ── ALL variable keys that need resetting on theme change ──────────────────
const _ALL_VARS = Object.keys(Object.assign({}, _DARK_BASE, _LIGHT_BASE, {'--bg':'','--bg2':'','--topbar-bg':'','--blue':'','--blue-dk':'','--indigo':'','--primary':''}));

function openThemePanel()  {
    document.getElementById('tp-panel').classList.add('open');
    document.getElementById('tp-overlay').classList.add('open');
}
function closeThemePanel() {
    document.getElementById('tp-panel').classList.remove('open');
    document.getElementById('tp-overlay').classList.remove('open');
}

function _isLightColor(hex) {
    if (!hex || hex[0] !== '#' || hex.length < 7) return false;
    const r = parseInt(hex.slice(1,3),16), g = parseInt(hex.slice(3,5),16), b = parseInt(hex.slice(5,7),16);
    return (r*299 + g*587 + b*114) / 1000 > 128;
}

function _applyUserVars(vars) {
    // First clear ALL known vars so no stale value bleeds through
    _ALL_VARS.forEach(k => document.documentElement.style.removeProperty(k));
    Object.entries(vars).forEach(([k,v]) => document.documentElement.style.setProperty(k,v));
    // Ensure --primary always mirrors --blue so active-state highlights update
    const blue = vars['--blue'] || getComputedStyle(document.documentElement).getPropertyValue('--blue').trim();
    if (blue) document.documentElement.style.setProperty('--primary', blue);
    // Toggle sidebar light-override CSS rules
    document.documentElement.dataset.scheme = _isLightColor(vars['--bg'] || '') ? 'light' : 'dark';
}

// Reverse map: user preset name → unified theme name (for cross-layout sync)
const _U_TO_UNIFIED = {
    'Cosmic Dark':'Cosmic','Deep Ocean':'Ocean','Midnight':'Midnight',
    'Purple Haze':'Violet','Forest Night':'Forest','Ember Dark':'Ember',
    'Slate Dark':'Midnight','Rose Night':'Ember',
    'Classic Light':'Cosmic','Violet Light':'Violet','Forest Light':'Forest',
    'Ocean Light':'Ocean','Sunset Light':'Ember',
};

function _selectUserPreset(name) {
    const t = _THEMES_U[name]; if (!t) return;
    _applyUserVars(t.vars);
    try { localStorage.setItem('rh_user_theme', JSON.stringify({name, vars:t.vars})); } catch(e) {}
    // Sync unified theme key so landing page and admin panel follow
    const unified = _U_TO_UNIFIED[name];
    if (unified) try { localStorage.setItem('rh_theme', unified); } catch(e) {}
    // Mark active preset
    document.querySelectorAll('#tp-presets-grid .tp-preset').forEach(b => b.classList.toggle('active', b.dataset.t === name));
    // Sync color pickers to preset
    document.getElementById('tc-bg').value  = t.vars['--bg'];
    document.getElementById('tc-bg2').value = t.vars['--bg2'];
    document.getElementById('tc-ac').value  = t.vars['--blue'];
    // Visual feedback
    _themeToast('✓ ' + name + ' applied');
}

function _applyCustomUser() {
    const bg  = document.getElementById('tc-bg').value;
    const bg2 = document.getElementById('tc-bg2').value;
    const ac  = document.getElementById('tc-ac').value;
    // Derive a slightly purple indigo from accent for gradient variety
    const indigo = _shiftHue(ac, 30);
    const isLight = _isLightColor(bg);
    const vars = Object.assign({}, isLight ? _LIGHT_BASE : _DARK_BASE, {
        '--bg':bg,'--bg2':bg2,'--topbar-bg':isLight ? '#ffffff' : bg2,
        '--blue':ac,'--blue-dk':_darken(ac,12),'--indigo':indigo,
    });
    _applyUserVars(vars);
    try { localStorage.setItem('rh_user_theme', JSON.stringify({name:'Custom', vars})); } catch(e) {}
    document.querySelectorAll('#tp-presets-grid .tp-preset').forEach(b => b.classList.remove('active'));
    _themeToast('✓ Custom colors applied');
}

function _resetUserTheme() {
    localStorage.removeItem('rh_user_theme');
    _applyUserVars(_THEMES_U['Cosmic Dark'].vars);
    document.querySelectorAll('#tp-presets-grid .tp-preset').forEach(b => b.classList.toggle('active', b.dataset.t === 'Cosmic Dark'));
    document.getElementById('tc-bg').value  = '#040d21';
    document.getElementById('tc-bg2').value = '#070f2b';
    document.getElementById('tc-ac').value  = '#3b82f6';
    _themeToast('✓ Reset to Cosmic Dark');
}

// ── Helpers ────────────────────────────────────────────────────────────────
function _themeToast(msg) {
    const old = document.getElementById('_tp_toast');
    if (old) old.remove();
    const t = document.createElement('div');
    t.id = '_tp_toast';
    t.style.cssText = 'position:fixed;bottom:22px;right:22px;background:#1e293b;color:#f1f5f9;padding:10px 16px;border-radius:10px;font-size:13px;font-weight:600;z-index:9999;box-shadow:0 8px 24px rgba(0,0,0,.35);border:1px solid rgba(255,255,255,.1);transition:opacity .3s';
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => { t.style.opacity='0'; setTimeout(()=>t.remove(),300); }, 1800);
}

function _hexToRgb(hex) {
    const r = parseInt(hex.slice(1,3),16), g = parseInt(hex.slice(3,5),16), b = parseInt(hex.slice(5,7),16);
    return [r,g,b];
}
function _rgbToHex(r,g,b) {
    return '#'+[r,g,b].map(v=>Math.min(255,Math.max(0,Math.round(v))).toString(16).padStart(2,'0')).join('');
}
function _darken(hex, pct) {
    const [r,g,b] = _hexToRgb(hex);
    const f = 1 - pct/100;
    return _rgbToHex(r*f, g*f, b*f);
}
function _shiftHue(hex, deg) {
    // simple hue rotation approximation
    let [r,g,b] = _hexToRgb(hex);
    r /= 255; g /= 255; b /= 255;
    const max = Math.max(r,g,b), min = Math.min(r,g,b), d = max - min;
    let h = 0, s = max === 0 ? 0 : d/max, v = max;
    if (d > 0) {
        if (max === r) h = ((g-b)/d + (g<b?6:0))/6;
        else if (max === g) h = ((b-r)/d + 2)/6;
        else h = ((r-g)/d + 4)/6;
    }
    h = (h + deg/360) % 1;
    // hsv to rgb
    const i = Math.floor(h*6), f2 = h*6-i, p = v*(1-s), q = v*(1-f2*s), t2 = v*(1-(1-f2)*s);
    let nr,ng,nb;
    switch(i%6){case 0:nr=v;ng=t2;nb=p;break;case 1:nr=q;ng=v;nb=p;break;case 2:nr=p;ng=v;nb=t2;break;case 3:nr=p;ng=q;nb=v;break;case 4:nr=t2;ng=p;nb=v;break;default:nr=v;ng=p;nb=q;}
    return _rgbToHex(nr*255,ng*255,nb*255);
}

document.addEventListener('DOMContentLoaded', function() {
    const grid = document.getElementById('tp-presets-grid');
    // Unified name → user preset name
    const _UNIFIED_TO_U = {'Cosmic':'Cosmic Dark','Midnight':'Midnight','Violet':'Purple Haze','Forest':'Forest Night','Ocean':'Deep Ocean','Ember':'Ember Dark'};
    let activeName = 'Cosmic Dark';
    try {
        const unified = localStorage.getItem('rh_theme');
        const local   = JSON.parse(localStorage.getItem('rh_user_theme') || '{}');
        if (local.name && local.vars) {
            // User has an explicit saved preference — always respect it and re-apply vars.
            activeName = _THEMES_U[local.name] ? local.name : 'Custom';
            _applyUserVars(local.vars);
        } else if (unified && _UNIFIED_TO_U[unified]) {
            // No explicit local preference → pull from cross-layout unified key
            activeName = _UNIFIED_TO_U[unified];
            const t = _THEMES_U[activeName];
            if (t) { _applyUserVars(t.vars); localStorage.setItem('rh_user_theme', JSON.stringify({name:activeName,vars:t.vars})); }
        }
    } catch(e) {}
    // data-scheme is already set by _applyUserVars above, but ensure fallback for Cosmic Dark default
    if (!document.documentElement.dataset.scheme) {
        document.documentElement.dataset.scheme = 'dark';
    }
    Object.entries(_THEMES_U).forEach(([name, t]) => {
        const btn = document.createElement('button');
        btn.className = 'tp-preset' + (name === activeName && activeName !== 'Custom' ? ' active' : '');
        btn.dataset.t = name;
        btn.onclick = () => _selectUserPreset(name);
        btn.innerHTML = '<div class="tp-swatches">' + t.sw.map(c => '<span class="tp-swatch" style="background:' + c + '"></span>').join('') + '</div><div class="tp-name">' + name + '</div>';
        grid.appendChild(btn);
    });
    const ct = _THEMES_U[activeName] || _THEMES_U['Cosmic Dark'];
    document.getElementById('tc-bg').value  = ct.vars['--bg'];
    document.getElementById('tc-bg2').value = ct.vars['--bg2'];
    document.getElementById('tc-ac').value  = ct.vars['--blue'];
});
</script>
<!-- ── LOCK SCREEN ───────────────────────────────────────────────────────── -->
<div id="lock-overlay" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(4,13,33,.92);backdrop-filter:blur(24px);align-items:center;justify-content:center">
    <div style="text-align:center;width:100%;max-width:340px;padding:20px">
        <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,var(--blue-dk,#2563eb),var(--indigo,#6366f1));display:flex;align-items:center;justify-content:center;font-size:30px;font-weight:800;color:#fff;margin:0 auto 16px;box-shadow:0 8px 32px rgba(99,102,241,.4)" id="lock-avatar-text">U</div>
        <div style="font-size:20px;font-weight:700;color:#fff;margin-bottom:4px" id="lock-user-name">User</div>
        <div style="font-size:12px;color:var(--muted,#94a3b8);margin-bottom:8px" id="lock-user-email"></div>
        <div style="font-size:12px;color:var(--muted2,#64748b);margin-bottom:24px">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;vertical-align:-2px;margin-right:4px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            Session locked due to inactivity
        </div>
        <input type="password" id="lock-password" placeholder="Enter your password"
            onkeydown="if(event.key==='Enter')unlockScreen()"
            style="width:100%;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:10px;padding:12px 14px;font-size:14px;color:#f1f5f9;font-family:inherit;outline:none;text-align:center;letter-spacing:2px;margin-bottom:10px">
        <div id="lock-error" style="display:none;color:#f87171;font-size:12px;margin-bottom:10px;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);border-radius:8px;padding:8px 12px"></div>
        <button id="lock-unlock-btn" onclick="unlockScreen()"
            style="width:100%;background:linear-gradient(135deg,var(--blue-dk,#2563eb),var(--indigo,#6366f1));color:#fff;border:none;border-radius:10px;padding:12px;font-size:14px;font-weight:700;cursor:pointer;font-family:inherit;margin-bottom:10px;box-shadow:0 4px 16px rgba(99,102,241,.4)">
            🔓 Unlock
        </button>
        <button onclick="_sessionLogout()"
            style="width:100%;background:rgba(239,68,68,.1);color:#f87171;border:1px solid rgba(239,68,68,.2);border-radius:10px;padding:10px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
            Sign Out Instead
        </button>
        <div id="lock-countdown" style="margin-top:16px;font-size:11px;color:var(--muted2,#64748b)"></div>
    </div>
</div>

<!-- ── LOGOUT CONFIRMATION ───────────────────────────────────────────────── -->
<div id="logout-modal" style="display:none;position:fixed;inset:0;z-index:9998;background:rgba(0,0,0,.6);backdrop-filter:blur(8px);align-items:center;justify-content:center">
    <div style="background:var(--bg2,#070f2b);border:1px solid var(--border2,rgba(255,255,255,.13));border-radius:16px;width:100%;max-width:360px;padding:28px;text-align:center;margin:20px">
        <div style="width:52px;height:52px;background:rgba(239,68,68,.15);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:24px;height:24px;color:#f87171"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
        </div>
        <div style="font-size:17px;font-weight:700;color:var(--text,#f1f5f9);margin-bottom:8px">Sign Out?</div>
        <div style="font-size:13px;color:var(--muted,#94a3b8);margin-bottom:24px">You will be logged out of your account. Any unsaved changes will be lost.</div>
        <div style="display:flex;gap:10px">
            <button onclick="doLogout()"
                style="flex:1;background:rgba(239,68,68,.15);color:#f87171;border:1px solid rgba(239,68,68,.25);border-radius:8px;padding:11px;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit">
                Yes, Sign Out
            </button>
            <button onclick="document.getElementById('logout-modal').style.display='none'"
                style="flex:1;background:var(--card,rgba(255,255,255,.04));color:var(--muted,#94a3b8);border:1px solid var(--border,rgba(255,255,255,.08));border-radius:8px;padding:11px;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit">
                Cancel
            </button>
        </div>
    </div>
</div>

<!-- ── CUSTOM SELECT COMPONENT ──────────────────────────────────────────── -->
<script>
(function () {
    'use strict';

    var _valProto = Object.getOwnPropertyDescriptor(HTMLSelectElement.prototype, 'value');

    function buildCustomSelect(sel) {
        if (sel._cselBuilt || sel.closest('.csel-wrap')) return;
        sel._cselBuilt = true;

        // Snapshot the select's inline size styles BEFORE hiding it
        var isSmall = !sel.classList.contains('finp');
        var cs = isSmall ? getComputedStyle(sel) : null;

        sel.style.display = 'none';

        var wrap = document.createElement('div');
        wrap.className = 'csel-wrap';
        if (isSmall) wrap.style.cssText = 'display:inline-block;width:auto;min-width:120px';

        var btn = document.createElement('div');
        btn.className = 'csel-btn';
        btn.setAttribute('tabindex', '0');
        btn.setAttribute('role', 'combobox');
        btn.setAttribute('aria-haspopup', 'listbox');
        btn.setAttribute('aria-expanded', 'false');
        if (isSmall) {
            // Match the small filter-select size
            btn.style.padding    = '5px 8px';
            btn.style.fontSize   = cs ? cs.fontSize : '12px';
            btn.style.borderRadius = cs ? cs.borderRadius : '6px';
        }

        var labelSpan = document.createElement('span');
        labelSpan.className = 'csel-label';
        btn.appendChild(labelSpan);

        var list = document.createElement('ul');
        list.className = 'csel-list';
        list.setAttribute('role', 'listbox');

        wrap.appendChild(btn);
        wrap.appendChild(list);
        sel.parentNode.insertBefore(wrap, sel.nextSibling);

        var focusIdx = -1;

        function renderOptions() {
            list.innerHTML = '';
            Array.from(sel.options).forEach(function (opt, i) {
                var li = document.createElement('li');
                li.className = 'csel-opt' + (i === sel.selectedIndex ? ' selected' : '');
                li.dataset.value = opt.value;
                li.dataset.index = i;
                li.textContent = opt.text;
                li.setAttribute('role', 'option');
                li.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    selectOption(i);
                    closeList();
                });
                list.appendChild(li);
            });
        }

        function updateBtn() {
            var opt = sel.options[sel.selectedIndex];
            labelSpan.textContent = opt ? opt.text : '';
            labelSpan.style.color = (opt && opt.value) ? '' : 'var(--muted,#94a3b8)';
        }

        function selectOption(idx) {
            sel.selectedIndex = idx;
            updateBtn();
            list.querySelectorAll('.csel-opt').forEach(function (li, i) {
                li.classList.toggle('selected', i === idx);
            });
            sel.dispatchEvent(new Event('change', { bubbles: true }));
        }

        function openList() {
            document.querySelectorAll('.csel-wrap.open').forEach(function (w) {
                w.classList.remove('open');
                var b = w.querySelector('.csel-btn');
                if (b) b.setAttribute('aria-expanded', 'false');
            });
            renderOptions();
            wrap.classList.add('open');
            btn.setAttribute('aria-expanded', 'true');
            focusIdx = sel.selectedIndex;
            var selLi = list.querySelector('.selected');
            if (selLi) setTimeout(function () { selLi.scrollIntoView({ block: 'nearest' }); }, 0);
        }

        function closeList() {
            wrap.classList.remove('open');
            btn.setAttribute('aria-expanded', 'false');
            list.querySelectorAll('.csel-opt.focused').forEach(function (li) {
                li.classList.remove('focused');
            });
        }

        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            if (wrap.classList.contains('open')) closeList();
            else openList();
        });

        btn.addEventListener('keydown', function (e) {
            var opts = Array.from(list.querySelectorAll('.csel-opt'));
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                if (wrap.classList.contains('open')) {
                    if (focusIdx >= 0) { selectOption(focusIdx); closeList(); }
                } else { openList(); }
            } else if (e.key === 'Escape') {
                e.preventDefault(); closeList();
            } else if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (!wrap.classList.contains('open')) { openList(); return; }
                focusIdx = Math.min(focusIdx + 1, opts.length - 1);
                opts.forEach(function (o, i) { o.classList.toggle('focused', i === focusIdx); });
                if (opts[focusIdx]) opts[focusIdx].scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (!wrap.classList.contains('open')) { openList(); return; }
                focusIdx = Math.max(focusIdx - 1, 0);
                opts.forEach(function (o, i) { o.classList.toggle('focused', i === focusIdx); });
                if (opts[focusIdx]) opts[focusIdx].scrollIntoView({ block: 'nearest' });
            }
        });

        document.addEventListener('click', function (e) {
            if (!wrap.contains(e.target)) closeList();
        });

        // Intercept programmatic .value = '...' (e.g. operator auto-detect)
        Object.defineProperty(sel, 'value', {
            get: function () { return _valProto.get.call(this); },
            set: function (v) {
                _valProto.set.call(this, v);
                updateBtn();
                if (list.children.length) {
                    list.querySelectorAll('.csel-opt').forEach(function (li) {
                        li.classList.toggle('selected', li.dataset.value === String(v));
                    });
                }
            },
            configurable: true,
            enumerable: true
        });

        // Watch for programmatic option changes (e.g. sel.innerHTML = '...')
        new MutationObserver(function () {
            updateBtn();
            // If list is open, re-render immediately
            if (wrap.classList.contains('open')) renderOptions();
        }).observe(sel, { childList: true });

        updateBtn();
    }

    function initAll() {
        // Apply to ALL selects on user pages (finp + filter selects with inline styles)
        document.querySelectorAll('select').forEach(buildCustomSelect);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }

    // Watch for dynamically added selects
    new MutationObserver(function (mutations) {
        mutations.forEach(function (m) {
            m.addedNodes.forEach(function (node) {
                if (node.nodeType !== 1) return;
                if (node.tagName === 'SELECT') buildCustomSelect(node);
                if (node.querySelectorAll) node.querySelectorAll('select').forEach(buildCustomSelect);
            });
        });
    }).observe(document.body, { childList: true, subtree: true });
})();
</script>
</body>
</html>
