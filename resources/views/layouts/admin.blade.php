<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — Recharge Panel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --sidebar-bg: #1a2035;
            --sidebar-hover: #252d47;
            --sidebar-active: #2563eb;
            --sidebar-section: #8892a4;
            --sidebar-text: #c8d0e0;
            --sidebar-width: 220px;
            --topbar-height: 0px;
            --accent-blue: #2563eb;
            --accent-green: #10b981;
            --accent-orange: #f59e0b;
            --accent-red: #ef4444;
            --accent-purple: #7c3aed;
            --bg-page: #f0f4f8;
            --card-bg: #ffffff;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;
            --border: #e2e8f0;
            --shadow-sm: 0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.06);
            --shadow-md: 0 4px 16px rgba(0,0,0,.10);
            --shadow-lg: 0 10px 32px rgba(0,0,0,.12);
            --radius: 12px;
            --radius-sm: 8px;

            /* ── Compatibility aliases (pages may use these short names) ── */
            --card2:   var(--card-bg);
            --border2: var(--border);
            --text:    var(--text-primary);
            --muted:   var(--text-muted);
            --primary: var(--accent-blue);
        }

        /* ── Native inputs / selects — force light-mode chrome ─────────── */
        select,
        input[type="date"],
        input[type="time"],
        input[type="datetime-local"],
        input[type="month"],
        input[type="week"] {
            color-scheme: light;
        }
        select option { background: #ffffff; color: #1e293b; }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--bg-page);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            font-size: 14px;
            line-height: 1.5;
        }

        /* ── SIDEBAR ─────────────────────────────────────────────────────── */
        .sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: var(--sidebar-bg);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 100;
            overflow-y: auto;
            scrollbar-width: none;
        }
        .sidebar::-webkit-scrollbar { display: none; }

        .sidebar-brand {
            padding: 20px 20px 16px;
            border-bottom: 1px solid rgba(255,255,255,.07);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sidebar-brand-icon {
            width: 36px; height: 36px;
            background: var(--accent-blue);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .sidebar-brand-icon svg { width: 20px; height: 20px; color: #fff; }
        .sidebar-brand-name {
            font-size: 15px;
            font-weight: 700;
            color: #fff;
            letter-spacing: -.3px;
        }
        .sidebar-brand-sub {
            font-size: 10px;
            color: var(--sidebar-section);
            text-transform: uppercase;
            letter-spacing: .6px;
            font-weight: 500;
        }

        .sidebar-nav { padding: 12px 0; flex: 1; }

        .nav-section {
            padding: 14px 20px 4px;
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--sidebar-section);
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 20px;
            color: var(--sidebar-text);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            border-radius: 0;
            transition: all .15s ease;
            position: relative;
            cursor: pointer;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
        }
        .nav-item:hover {
            background: var(--sidebar-hover);
            color: #fff;
        }
        .nav-item.active {
            background: var(--sidebar-hover);
            color: #fff;
        }
        .nav-item.active::before {
            content: '';
            position: absolute;
            left: 0; top: 6px; bottom: 6px;
            width: 3px;
            background: var(--accent-blue);
            border-radius: 0 3px 3px 0;
        }
        .nav-item svg { width: 16px; height: 16px; flex-shrink: 0; opacity: .75; }
        .nav-item.active svg, .nav-item:hover svg { opacity: 1; }

        .nav-badge {
            margin-left: auto;
            background: var(--accent-blue);
            color: #fff;
            font-size: 10px;
            font-weight: 600;
            padding: 1px 7px;
            border-radius: 20px;
            line-height: 16px;
        }
        .nav-badge.green { background: var(--accent-green); }
        .nav-badge.orange { background: var(--accent-orange); }

        .nav-chevron {
            margin-left: auto;
            width: 14px; height: 14px;
            opacity: .5;
            transition: transform .2s;
        }
        .nav-item.open .nav-chevron { transform: rotate(90deg); }

        .nav-submenu {
            display: none;
            background: rgba(0,0,0,.15);
        }
        .nav-submenu.open { display: block; }
        .nav-submenu .nav-item {
            padding-left: 46px;
            font-size: 12.5px;
        }

        .sidebar-footer {
            padding: 14px 20px;
            border-top: 1px solid rgba(255,255,255,.07);
        }
        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sidebar-avatar {
            width: 34px; height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent-blue), var(--accent-purple));
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 700; color: #fff;
            flex-shrink: 0;
        }
        .sidebar-user-info { flex: 1; min-width: 0; }
        .sidebar-user-name { font-size: 12.5px; font-weight: 600; color: #fff; truncate: true; }
        .sidebar-user-role { font-size: 11px; color: var(--sidebar-section); }
        .sidebar-logout {
            background: none; border: none; cursor: pointer;
            color: var(--sidebar-section); padding: 4px;
            border-radius: 6px; transition: color .15s;
        }
        .sidebar-logout:hover { color: var(--accent-red); }
        .sidebar-logout svg { width: 16px; height: 16px; }

        /* ── MAIN CONTENT ─────────────────────────────────────────────── */
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── TOP BAR ─────────────────────────────────────────────────── */
        .topbar {
            background: var(--topbar-bg, #fff);
            border-bottom: 1px solid var(--border);
            padding: 0 28px;
            height: 56px;
            display: flex;
            align-items: center;
            gap: 16px;
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .topbar-title { font-size: 15px; font-weight: 600; color: var(--text-primary); flex: 1; }
        .topbar-actions { display: flex; align-items: center; gap: 10px; }
        .topbar-icon-btn {
            width: 36px; height: 36px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: none;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            color: var(--text-secondary);
            transition: all .15s;
            position: relative;
        }
        .topbar-icon-btn:hover { background: var(--bg-page); color: var(--text-primary); }
        .topbar-icon-btn svg { width: 17px; height: 17px; }
        .notif-dot {
            position: absolute;
            top: 7px; right: 7px;
            width: 7px; height: 7px;
            background: var(--accent-red);
            border-radius: 50%;
            border: 2px solid #fff;
        }
        .topbar-time {
            font-size: 12px; color: var(--text-muted);
            background: var(--bg-page);
            padding: 5px 12px;
            border-radius: 6px;
            border: 1px solid var(--border);
        }

        /* ── PAGE BODY ───────────────────────────────────────────────── */
        .page-body { padding: 24px 28px; flex: 1; }

        /* ── BREADCRUMB ──────────────────────────────────────────────── */
        .breadcrumb {
            display: flex; align-items: center; gap: 6px;
            font-size: 12px; color: var(--text-muted); margin-bottom: 20px;
        }
        .breadcrumb a { color: var(--accent-blue); text-decoration: none; font-weight: 500; }
        .breadcrumb a:hover { text-decoration: underline; }
        .breadcrumb-sep { color: var(--border); }

        /* ── ANNOUNCEMENT BANNER ─────────────────────────────────────── */
        .announcement-banner {
            background: linear-gradient(135deg, #4f46e5, #2563eb);
            border-radius: var(--radius);
            padding: 14px 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: 0 4px 20px rgba(37,99,235,.25);
        }
        .announcement-icon {
            width: 40px; height: 40px;
            background: rgba(255,255,255,.15);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .announcement-icon svg { width: 22px; height: 22px; color: #fff; }
        .announcement-text { flex: 1; }
        .announcement-title { font-size: 14px; font-weight: 600; color: #fff; }
        .announcement-sub { font-size: 12px; color: rgba(255,255,255,.75); margin-top: 1px; }
        .announcement-close {
            background: rgba(255,255,255,.15);
            border: none; cursor: pointer;
            width: 28px; height: 28px;
            border-radius: 6px;
            color: #fff;
            display: flex; align-items: center; justify-content: center;
            transition: background .15s;
        }
        .announcement-close:hover { background: rgba(255,255,255,.25); }
        .announcement-close svg { width: 14px; height: 14px; }

        /* ── NOTE BAR ────────────────────────────────────────────────── */
        .note-bar {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: var(--radius-sm);
            padding: 10px 16px;
            margin-bottom: 20px;
            display: flex; align-items: flex-start; gap: 10px;
            font-size: 12.5px; color: #1e40af;
        }
        .note-bar svg { width: 15px; height: 15px; flex-shrink: 0; margin-top: 1px; }

        /* ── CARDS ───────────────────────────────────────────────────── */
        .card {
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
        }
        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: 10px;
        }
        .card-title { font-size: 14px; font-weight: 600; color: var(--text-primary); }
        .card-body { padding: 20px; }
        .card-footer {
            padding: 12px 20px;
            border-top: 1px solid var(--border);
            display: flex; align-items: center;
        }

        /* ── STAT CARDS ──────────────────────────────────────────────── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 20px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            position: relative;
            overflow: hidden;
        }
        .stat-card::after {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 80px; height: 80px;
            border-radius: 50%;
            opacity: .06;
            transform: translate(20px, -20px);
        }
        .stat-card.blue::after { background: var(--accent-blue); }
        .stat-card.green::after { background: var(--accent-green); }
        .stat-card.orange::after { background: var(--accent-orange); }
        .stat-card.red::after { background: var(--accent-red); }

        .stat-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 12px; }
        .stat-label { font-size: 12px; font-weight: 500; color: var(--text-secondary); }
        .stat-icon {
            width: 38px; height: 38px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .stat-icon svg { width: 20px; height: 20px; }
        .stat-card.blue .stat-icon { background: #dbeafe; color: var(--accent-blue); }
        .stat-card.green .stat-icon { background: #d1fae5; color: var(--accent-green); }
        .stat-card.orange .stat-icon { background: #fef3c7; color: var(--accent-orange); }
        .stat-card.red .stat-icon { background: #fee2e2; color: var(--accent-red); }

        .stat-value {
            font-size: 26px; font-weight: 700; line-height: 1; letter-spacing: -.5px;
            margin-bottom: 4px;
        }
        .stat-card.blue .stat-value { color: var(--accent-blue); }
        .stat-card.green .stat-value { color: var(--accent-green); }
        .stat-card.orange .stat-value { color: var(--accent-orange); }
        .stat-card.red .stat-value { color: var(--accent-red); }

        .stat-amount { font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px; }
        .stat-footer { display: flex; align-items: center; gap: 6px; }
        .stat-updated { font-size: 11px; color: var(--text-muted); }
        .stat-pulse {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: var(--accent-green);
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: .5; transform: scale(.8); }
        }

        /* ── CHARTS GRID ─────────────────────────────────────────────── */
        .charts-grid {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 16px;
            margin-bottom: 20px;
        }

        /* ── LIVE BADGE ──────────────────────────────────────────────── */
        .live-badge {
            display: inline-flex; align-items: center; gap: 5px;
            background: #dcfce7; color: #16a34a;
            font-size: 10.5px; font-weight: 700;
            padding: 3px 8px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .live-badge::before {
            content: '';
            width: 6px; height: 6px;
            border-radius: 50%;
            background: #16a34a;
            animation: pulse 1.5s infinite;
        }

        /* ── COMPLAINTS PANEL ────────────────────────────────────────── */
        .complaints-grid {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 16px;
        }

        .complaints-summary {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 16px;
        }
        .complaint-stat {
            background: var(--bg-page);
            border-radius: var(--radius-sm);
            padding: 16px;
            text-align: center;
        }
        .complaint-stat-icon {
            width: 36px; height: 36px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 8px;
        }
        .complaint-stat-icon svg { width: 18px; height: 18px; }
        .complaint-stat.total .complaint-stat-icon { background: #ede9fe; color: var(--accent-purple); }
        .complaint-stat.solved .complaint-stat-icon { background: #d1fae5; color: var(--accent-green); }
        .complaint-stat-label { font-size: 11px; color: var(--text-muted); margin-bottom: 2px; }
        .complaint-stat-value { font-size: 22px; font-weight: 700; color: var(--text-primary); }

        .complaint-category {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
        }
        .complaint-category:last-child { border-bottom: none; }
        .cat-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
        .cat-label { flex: 1; font-size: 13px; color: var(--text-primary); font-weight: 500; }
        .cat-count { font-size: 13px; font-weight: 700; }
        .cat-amount { font-size: 11px; color: var(--text-muted); text-align: right; }

        /* ── RECENT TRANSACTIONS ─────────────────────────────────────── */
        .txn-list { list-style: none; }
        .txn-item {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
        }
        .txn-item:last-child { border-bottom: none; }
        .txn-avatar {
            width: 32px; height: 32px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700; color: #fff;
            flex-shrink: 0;
        }
        .txn-info { flex: 1; min-width: 0; }
        .txn-mobile { font-size: 12.5px; font-weight: 600; color: var(--text-primary); }
        .txn-operator { font-size: 11px; color: var(--text-muted); }
        .txn-amount { font-size: 13px; font-weight: 600; }
        .txn-status { font-size: 10px; font-weight: 600; padding: 2px 7px; border-radius: 20px; }
        .txn-status.success { background: #d1fae5; color: #059669; }
        .txn-status.failure { background: #fee2e2; color: #dc2626; }
        .txn-status.pending { background: #fef3c7; color: #d97706; }

        /* ── OPERATOR TABLE ──────────────────────────────────────────── */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        thead th {
            padding: 10px 14px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: var(--text-muted);
            background: var(--bg-page);
            border-bottom: 1px solid var(--border);
        }
        tbody td {
            padding: 12px 14px;
            font-size: 13px;
            color: var(--text-primary);
            border-bottom: 1px solid var(--border);
        }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover td { background: #f8faff; }

        /* ── PROGRESS BAR ────────────────────────────────────────────── */
        .progress { height: 5px; background: var(--border); border-radius: 10px; overflow: hidden; }
        .progress-bar { height: 100%; border-radius: 10px; transition: width .4s; }

        /* ── BUTTONS ─────────────────────────────────────────────────── */
        .btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px; font-weight: 600;
            cursor: pointer; border: none;
            transition: all .15s;
            text-decoration: none;
        }
        .btn svg { width: 15px; height: 15px; }
        .btn-primary { background: var(--accent-blue); color: #fff; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-outline {
            background: #fff; color: var(--text-primary);
            border: 1px solid var(--border);
        }
        .btn-outline:hover { background: var(--bg-page); }
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        .btn-link {
            background: none; border: none; color: var(--accent-blue);
            font-size: 12px; font-weight: 500; cursor: pointer;
            display: inline-flex; align-items: center; gap: 4px;
        }
        .btn-link svg { width: 13px; height: 13px; }

        /* ── SPINNER ─────────────────────────────────────────────────── */
        .spinner {
            width: 18px; height: 18px;
            border: 2px solid var(--border);
            border-top-color: var(--accent-blue);
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        .loading-overlay {
            display: flex; align-items: center; justify-content: center;
            padding: 40px; gap: 10px; color: var(--text-muted);
            font-size: 13px;
        }

        /* ── TOOLTIP ─────────────────────────────────────────────────── */
        [data-tooltip] { position: relative; cursor: default; }
        [data-tooltip]::after {
            content: attr(data-tooltip);
            position: absolute; bottom: calc(100% + 8px); left: 50%;
            transform: translateX(-50%);
            background: #1e293b; color: #fff;
            font-size: 11px; font-weight: 500;
            padding: 5px 10px; border-radius: 6px;
            white-space: nowrap; pointer-events: none;
            opacity: 0; transition: opacity .15s;
        }
        [data-tooltip]:hover::after { opacity: 1; }

        /* ── RESPONSIVE ──────────────────────────────────────────────── */
        @media (max-width: 1200px) {
            .charts-grid, .complaints-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 900px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 600px) {
            .stats-grid { grid-template-columns: 1fr; }
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: none; }
            .main-content { margin-left: 0; }
            .page-body { padding: 16px; }
        }

        /* ── SCROLLBAR ───────────────────────────────────────────────── */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 10px; }

        /* ── VIEW MORE LINK ──────────────────────────────────────────── */
        .view-more {
            color: var(--accent-blue); font-size: 12px; font-weight: 500;
            text-decoration: none; display: inline-flex; align-items: center; gap: 4px;
        }
        .view-more:hover { text-decoration: underline; }
        .view-more svg { width: 12px; height: 12px; }

        /* ── THEME PANEL ─────────────────────────────────────────────── */
        .theme-panel-overlay{position:fixed;inset:0;z-index:199;background:rgba(0,0,0,.3);display:none;backdrop-filter:blur(2px)}
        .theme-panel-overlay.open{display:block}
        .theme-panel{position:fixed;top:0;right:-310px;width:290px;height:100vh;background:var(--card-bg,#fff);border-left:1px solid var(--border);z-index:200;display:flex;flex-direction:column;transition:right .28s cubic-bezier(.16,1,.3,1);box-shadow:-8px 0 40px rgba(0,0,0,.15)}
        .theme-panel.open{right:0}
        .tp-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px}
        .tp-head h3{font-size:14px;font-weight:700;color:var(--text-primary);flex:1}
        .tp-close{background:none;border:none;cursor:pointer;color:var(--text-muted);padding:4px;border-radius:6px;display:flex;align-items:center;transition:color .15s}
        .tp-close:hover{color:var(--text-primary)}
        .tp-close svg{width:16px;height:16px}
        .tp-body{flex:1;overflow-y:auto;padding:16px 20px}
        .tp-sect{font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);margin-bottom:10px;margin-top:4px}
        .tp-presets{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:4px}
        .tp-preset{padding:10px 12px;border-radius:10px;border:2px solid var(--border);cursor:pointer;text-align:left;transition:all .15s;background:var(--bg-page,#f8faff);width:100%}
        .tp-preset:hover{border-color:var(--accent-blue)}
        .tp-preset.active{border-color:var(--accent-blue);box-shadow:0 0 0 3px rgba(37,99,235,.1)}
        .tp-swatches{display:flex;gap:4px;margin-bottom:6px}
        .tp-swatch{width:14px;height:14px;border-radius:3px;border:1px solid rgba(0,0,0,.1)}
        .tp-name{font-size:12px;font-weight:600;color:var(--text-primary)}
        .tp-divider{height:1px;background:var(--border);margin:16px 0}
        .tp-custom{display:flex;flex-direction:column;gap:12px}
        .tp-color-row{display:flex;align-items:center;justify-content:space-between}
        .tp-color-label{font-size:12.5px;color:var(--text-secondary);font-weight:500}
        .tp-color-input{width:40px;height:32px;border:1px solid var(--border);border-radius:8px;cursor:pointer;padding:3px;background:var(--bg-page,#fff)}
        .tp-apply-btn{width:100%;padding:9px;border-radius:8px;background:var(--accent-blue);color:#fff;border:none;font-size:13px;font-weight:600;cursor:pointer;margin-top:4px;transition:opacity .15s;font-family:inherit}
        .tp-apply-btn:hover{opacity:.88}
        .tp-foot{padding:14px 20px;border-top:1px solid var(--border)}
        .tp-reset{width:100%;padding:9px;border-radius:8px;border:1px solid var(--border);background:transparent;color:var(--text-secondary);font-size:13px;font-weight:600;cursor:pointer;transition:all .15s;font-family:inherit}
        .tp-reset:hover{background:var(--bg-page);color:var(--text-primary)}

        /* ── THEME COMPATIBILITY — form elements follow theme vars ──── */
        input:not([type="checkbox"]):not([type="radio"]):not([type="color"]):not([type="range"]),
        select, textarea {
            background: var(--card-bg, #fff) !important;
            color: var(--text-primary, #1e293b) !important;
            border-color: var(--border, #e2e8f0) !important;
        }
        input::placeholder, textarea::placeholder { color: var(--text-muted, #94a3b8) !important; }
        select option { background: var(--card-bg, #fff); color: var(--text-primary, #1e293b); }
    </style>
    <script>(function(){try{var s=localStorage.getItem('rh_admin_theme');if(s){var d=JSON.parse(s);Object.entries(d.vars).forEach(function(e){document.documentElement.style.setProperty(e[0],e[1]);});}}catch(e){}})();</script>
    @stack('head')
</head>
<body>

<!-- ── SIDEBAR ──────────────────────────────────────────────────────────── -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
        </div>
        <div>
            <div class="sidebar-brand-name">RechargeHub</div>
            <div class="sidebar-brand-sub">Admin Panel</div>
        </div>
    </div>

    <nav class="sidebar-nav">

        <div class="nav-section">Main</div>
        <a href="/admin/dashboard" class="nav-item {{ request()->is('admin/dashboard') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Dashboard
            <span class="nav-badge green" id="sb-classic-badge">Classic</span>
        </a>

        <div class="nav-section">Reports</div>
        <button class="nav-item" onclick="toggleSubmenu('reports-sub', this)">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Reports
            <svg class="nav-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
        <div class="nav-submenu {{ request()->is('admin/reports*') ? 'open' : '' }}" id="reports-sub">
            <a href="/admin/reports/recharges"      class="nav-item {{ request()->is('admin/reports/recharges')      ? 'active' : '' }}">Recharge Report</a>
            <a href="/admin/reports/pending"         class="nav-item {{ request()->is('admin/reports/pending')         ? 'active' : '' }}">Pending Recharge</a>
            <a href="/admin/reports/operators"       class="nav-item {{ request()->is('admin/reports/operators')       ? 'active' : '' }}">Operator Report</a>
            <a href="/admin/reports/operator-codes"  class="nav-item {{ request()->is('admin/reports/operator-codes')  ? 'active' : '' }}">Operator Code List</a>
            <a href="/admin/reports/failures"        class="nav-item {{ request()->is('admin/reports/failures')        ? 'active' : '' }}">Failure Report</a>
            <a href="/admin/reports/payments"        class="nav-item {{ request()->is('admin/reports/payments')        ? 'active' : '' }}">Payment Report</a>
            <a href="/admin/reports/wallets"         class="nav-item {{ request()->is('admin/reports/wallets')         ? 'active' : '' }}">Wallet Report</a>
            <a href="/admin/reports/bank-accounts"   class="nav-item {{ request()->is('admin/reports/bank-accounts')   ? 'active' : '' }}">User Bank List</a>
            <a href="/admin/reports/account"         class="nav-item {{ request()->is('admin/reports/account')         ? 'active' : '' }}">Account Report</a>
            <a href="/admin/reports/topup"           class="nav-item {{ request()->is('admin/reports/topup')           ? 'active' : '' }}">Top-up Report</a>
        </div>

        <div class="nav-section">Users</div>
        <a href="/admin/users" class="nav-item {{ request()->is('admin/users') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            User List
        </a>

        <div class="nav-section">API Sellers</div>
        <a href="/admin/sellers" class="nav-item {{ request()->is('admin/sellers') && !request()->is('admin/sellers/*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            API Sellers
            <span class="nav-badge orange" id="sb-pending-sellers" style="display:none">0</span>
        </a>
        <a href="/admin/sellers/payment-requests" class="nav-item {{ request()->is('admin/sellers/payment-requests') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            Seller Payments
            <span class="nav-badge orange" id="sb-pending-payments" style="display:none">0</span>
        </a>

        <div class="nav-section">Manage</div>
        <button class="nav-item" onclick="toggleSubmenu('manage-sub', this)">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Manage
            <svg class="nav-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
        <div class="nav-submenu {{ request()->is('admin/operators') || request()->is('admin/employees') || request()->is('admin/api-keys') ? 'open' : '' }}" id="manage-sub">
            <a href="/admin/operators" class="nav-item {{ request()->is('admin/operators') ? 'active' : '' }}">Operators</a>
            <a href="/admin/employees" class="nav-item {{ request()->is('admin/employees') ? 'active' : '' }}">Employees</a>
            <a href="/admin/api-keys" class="nav-item {{ request()->is('admin/api-keys') ? 'active' : '' }}">API Keys</a>
        </div>

        <div class="nav-section">Commission</div>
        <button class="nav-item" onclick="toggleSubmenu('commission-sub', this)">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Commission
            <svg class="nav-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
        <div class="nav-submenu {{ request()->is('admin/commission*') ? 'open' : '' }}" id="commission-sub">
            <a href="/admin/commission/slab"    class="nav-item {{ request()->is('admin/commission/slab')    ? 'active' : '' }}">Commission Slab</a>
            <a href="/admin/commission/history" class="nav-item {{ request()->is('admin/commission/history') ? 'active' : '' }}">Commission History</a>
        </div>

        <div class="nav-section">Tools</div>
        <a href="/admin/todos" class="nav-item {{ request()->is('admin/todos') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            Todos/Reminders
        </a>
        <a href="/admin/reminders" class="nav-item {{ request()->is('admin/reminders') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            Reminder
        </a>
        <a href="/admin/exports" class="nav-item {{ request()->is('admin/exports') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
            </svg>
            Exports History
        </a>
        <a href="/admin/activity" class="nav-item {{ request()->is('admin/activity') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7l-3 3-1.5-1.5"/>
            </svg>
            Activity Logs
        </a>

        <div class="nav-section">Developer</div>
        <button class="nav-item" onclick="toggleSubmenu('dev-sub', this)">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
            </svg>
            API Tools
            <svg class="nav-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
        <div class="nav-submenu {{ request()->is('admin/api*') ? 'open' : '' }}" id="dev-sub">
            <a href="/admin/api-docs"        class="nav-item {{ request()->is('admin/api-docs')        ? 'active' : '' }}">API Documentation</a>
            <a href="/admin/api-integration" class="nav-item {{ request()->is('admin/api-integration') ? 'active' : '' }}">API Integration</a>
        </div>

        <div class="nav-section">Account</div>
        <a href="/admin/profile" class="nav-item {{ request()->is('admin/profile') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            My Profile
        </a>

        <div class="nav-section">Complaints</div>
        <button class="nav-item" onclick="toggleSubmenu('complaints-sub', this)">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            Complaints
            <span class="nav-badge orange" id="sb-complaint-count">—</span>
            <svg class="nav-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
        <div class="nav-submenu {{ request()->is('admin/complaints*') ? 'open' : '' }}" id="complaints-sub">
            <a href="/admin/complaints"          class="nav-item {{ request()->is('admin/complaints') && !request()->is('admin/complaints/*') ? 'active' : '' }}">All Complaints</a>
            <a href="/admin/complaints/pending"  class="nav-item {{ request()->is('admin/complaints/pending')  ? 'active' : '' }}">Pending</a>
            <a href="/admin/complaints/resolved" class="nav-item {{ request()->is('admin/complaints/resolved') ? 'active' : '' }}">Resolved</a>
        </div>

    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-avatar" id="sb-avatar">—</div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name" id="sb-name">Loading…</div>
                <div class="sidebar-user-role" id="sb-role">—</div>
            </div>
            <button class="sidebar-logout" onclick="lockScreen()" title="Lock Screen" style="margin-right:2px">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </button>
            <button class="sidebar-logout" onclick="confirmLogout()" title="Logout">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </button>
        </div>
    </div>
</aside>

<!-- ── MAIN ─────────────────────────────────────────────────────────────── -->
<div class="main-content">

    <!-- Top bar -->
    <header class="topbar">
        <button class="topbar-icon-btn" onclick="document.getElementById('sidebar').classList.toggle('open')" style="display:none" id="menu-toggle">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
        <div class="topbar-actions">
            <div class="topbar-time" id="clock">—</div>
            <button class="topbar-icon-btn" title="Refresh" onclick="window.refreshDashboard && window.refreshDashboard()">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </button>
            <button class="topbar-icon-btn" title="Notifications" id="notif-btn">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span class="notif-dot" id="notif-dot" style="display:none"></span>
            </button>
            <button class="topbar-icon-btn" title="Theme Customizer" onclick="openThemePanel()">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
            </button>
        </div>
    </header>

    <!-- Page body -->
    <div class="page-body">
        @yield('content')
    </div>
</div>

<!-- ── GLOBAL JS ─────────────────────────────────────────────────────────── -->
<script>
// ── Auth helpers ──────────────────────────────────────────────────────────
const TOKEN_KEY   = 'emp_token';
const EMPLOYEE_KEY = 'emp_data';

function getToken()    { return localStorage.getItem(TOKEN_KEY); }
function getEmployee() { try { return JSON.parse(localStorage.getItem(EMPLOYEE_KEY) || '{}'); } catch { return {}; } }

function requireAuth() {
    const token = getToken();
    if (!token) { window.location.href = '/admin/login'; return false; }
    return token;
}

function _clearAdminSession() {
    localStorage.removeItem(TOKEN_KEY);
    localStorage.removeItem(EMPLOYEE_KEY);
}

function doLogout() {
    const token = getToken();
    _clearAdminSession();
    if (token) {
        fetch('/api/v1/employee/auth/logout', {
            method: 'POST',
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        }).finally(() => { window.location.href = '/admin/login'; });
    } else {
        window.location.href = '/admin/login';
    }
}

function confirmLogout() {
    document.getElementById('logout-modal').style.display = 'flex';
}

// ── SESSION MANAGEMENT ────────────────────────────────────────────
const LOCK_AFTER_MS   = 15 * 60 * 1000;
const LOGOUT_AFTER_MS = 30 * 60 * 1000;
let _lockTimer = null, _logoutTimer = null, _countdownInterval = null;
let _isLocked  = false;

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
    _lockTimer   = setTimeout(lockScreen,     LOCK_AFTER_MS);
    _logoutTimer = setTimeout(_sessionLogout, LOGOUT_AFTER_MS);
}

function lockScreen() {
    if (_isLocked) return;
    _isLocked = true;
    clearTimeout(_lockTimer);
    const emp = getEmployee();
    document.getElementById('lock-user-name').textContent   = emp.name  || 'Admin';
    document.getElementById('lock-user-email').textContent  = emp.email || '';
    document.getElementById('lock-user-role').textContent   = (emp.role || '').replace('_',' ').toUpperCase();
    document.getElementById('lock-avatar-text').textContent = (emp.name || 'A').charAt(0).toUpperCase();
    document.getElementById('lock-password').value = '';
    document.getElementById('lock-error').style.display = 'none';
    document.getElementById('lock-overlay').style.display = 'flex';
    document.getElementById('lock-password').focus();
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
    const emp = getEmployee();
    try {
        const res = await fetch('/api/v1/employee/auth/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ email: emp.email, password: pwd, device_name: 'web' })
        });
        if (res.ok) {
            const data = await res.json();
            localStorage.setItem(TOKEN_KEY, data.token);
            if (data.employee) localStorage.setItem(EMPLOYEE_KEY, JSON.stringify(data.employee));
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

// Authenticated fetch wrapper
async function apiFetch(url, opts = {}) {
    const token = getToken();
    opts.headers = Object.assign({
        'Authorization': 'Bearer ' + token,
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    }, opts.headers || {});

    const res = await fetch(url, opts);

    if (res.status === 401) {
        localStorage.removeItem(TOKEN_KEY);
        window.location.href = '/admin/login';
        return null;
    }
    return res;
}

// ── Boot sidebar user ─────────────────────────────────────────────────────
function bootSidebarUser() {
    const emp = getEmployee();
    if (emp.name) {
        document.getElementById('sb-name').textContent = emp.name;
        document.getElementById('sb-role').textContent = (emp.role || '').replace('_', ' ');
        document.getElementById('sb-avatar').textContent = emp.name.charAt(0).toUpperCase();
    }
}

// ── Clock ─────────────────────────────────────────────────────────────────
function updateClock() {
    const now = new Date();
    document.getElementById('clock').textContent = now.toLocaleTimeString('en-IN', {
        hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true
    });
}
setInterval(updateClock, 1000);
updateClock();

// ── Submenu toggle ────────────────────────────────────────────────────────
function toggleSubmenu(id, btn) {
    const sub = document.getElementById(id);
    const isOpen = sub.classList.contains('open');
    // Close all
    document.querySelectorAll('.nav-submenu.open').forEach(el => el.classList.remove('open'));
    document.querySelectorAll('.nav-item.open').forEach(el => el.classList.remove('open'));
    if (!isOpen) {
        sub.classList.add('open');
        btn.classList.add('open');
    }
}

// ── Format helpers ────────────────────────────────────────────────────────
function fmtNum(n) {
    if (n == null) return '—';
    return Number(n).toLocaleString('en-IN');
}
function fmtAmt(n) {
    if (n == null) return '—';
    return '₹' + Number(n).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function fmtAgo(dateStr) {
    if (!dateStr) return '—';
    const diff = Math.floor((Date.now() - new Date(dateStr)) / 1000);
    if (diff < 60) return diff + 's ago';
    if (diff < 3600) return Math.floor(diff/60) + 'm ago';
    if (diff < 86400) return Math.floor(diff/3600) + 'h ago';
    return Math.floor(diff/86400) + 'd ago';
}
function numToWords(n) {
    if (n >= 1e7) return (n/1e7).toFixed(2) + ' Cr';
    if (n >= 1e5) return (n/1e5).toFixed(2) + ' L';
    if (n >= 1e3) return (n/1e3).toFixed(2) + ' K';
    return fmtNum(n);
}

// ── Mobile sidebar ────────────────────────────────────────────────────────
if (window.innerWidth < 600) {
    document.getElementById('menu-toggle').style.display = 'flex';
}

// ── Boot ──────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    requireAuth();
    bootSidebarUser();
    initSession();
});
</script>

@stack('scripts')

<!-- ── THEME PANEL ──────────────────────────────────────────────────────── -->
<div id="tp-overlay" class="theme-panel-overlay" onclick="closeThemePanel()"></div>
<div id="tp-panel" class="theme-panel">
    <div class="tp-head">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;color:var(--accent-blue)"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
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
                <span class="tp-color-label">Sidebar Color</span>
                <input type="color" class="tp-color-input" id="tc-sb" value="#1a2035">
            </div>
            <div class="tp-color-row">
                <span class="tp-color-label">Accent Color</span>
                <input type="color" class="tp-color-input" id="tc-ac" value="#2563eb">
            </div>
            <div class="tp-color-row">
                <span class="tp-color-label">Page Background</span>
                <input type="color" class="tp-color-input" id="tc-bg" value="#f0f4f8">
            </div>
            <button class="tp-apply-btn" onclick="_applyCustomAdmin()">Apply Custom Theme</button>
        </div>
    </div>
    <div class="tp-foot">
        <button class="tp-reset" onclick="_resetAdminTheme()">&#8635; Reset to Default</button>
    </div>
</div>

<script>
/* ── Theme System (Admin) ──────────────────────────────────────────────── */
const _THEMES_A = {
    'Blue Classic': {sw:['#1a2035','#2563eb','#f0f4f8'], vars:{'--sidebar-bg':'#1a2035','--sidebar-hover':'#252d47','--sidebar-active':'#2563eb','--accent-blue':'#2563eb','--bg-page':'#f0f4f8','--card-bg':'#ffffff','--topbar-bg':'#ffffff','--text-primary':'#1e293b','--text-secondary':'#64748b','--border':'#e2e8f0'}},
    'Dark Mode':    {sw:['#111827','#3b82f6','#1f2937'], vars:{'--sidebar-bg':'#111827','--sidebar-hover':'#1f2937','--sidebar-active':'#3b82f6','--accent-blue':'#3b82f6','--bg-page':'#1a2535','--card-bg':'#111827','--topbar-bg':'#0f172a','--text-primary':'#e2e8f0','--text-secondary':'#94a3b8','--border':'#2d3748'}},
    'Purple Night': {sw:['#1e1b4b','#7c3aed','#f5f3ff'], vars:{'--sidebar-bg':'#1e1b4b','--sidebar-hover':'#2d2a6b','--sidebar-active':'#7c3aed','--accent-blue':'#7c3aed','--bg-page':'#f5f3ff','--card-bg':'#ffffff','--topbar-bg':'#ffffff','--text-primary':'#1e1b4b','--text-secondary':'#6d6a9a','--border':'#ddd6fe'}},
    'Forest Green': {sw:['#052e16','#16a34a','#f0fdf4'], vars:{'--sidebar-bg':'#052e16','--sidebar-hover':'#14532d','--sidebar-active':'#16a34a','--accent-blue':'#16a34a','--bg-page':'#f0fdf4','--card-bg':'#ffffff','--topbar-bg':'#ffffff','--text-primary':'#052e16','--text-secondary':'#166534','--border':'#bbf7d0'}},
    'Ocean Teal':   {sw:['#042f2e','#0d9488','#f0fdfa'], vars:{'--sidebar-bg':'#042f2e','--sidebar-hover':'#134e4a','--sidebar-active':'#0d9488','--accent-blue':'#0d9488','--bg-page':'#f0fdfa','--card-bg':'#ffffff','--topbar-bg':'#ffffff','--text-primary':'#042f2e','--text-secondary':'#0f766e','--border':'#99f6e4'}},
    'Sunset':       {sw:['#431407','#ea580c','#fff7ed'], vars:{'--sidebar-bg':'#431407','--sidebar-hover':'#7c2d12','--sidebar-active':'#ea580c','--accent-blue':'#ea580c','--bg-page':'#fff7ed','--card-bg':'#ffffff','--topbar-bg':'#ffffff','--text-primary':'#431407','--text-secondary':'#9a3412','--border':'#fed7aa'}},
};

function openThemePanel()  { document.getElementById('tp-panel').classList.add('open');  document.getElementById('tp-overlay').classList.add('open');  }
function closeThemePanel() { document.getElementById('tp-panel').classList.remove('open'); document.getElementById('tp-overlay').classList.remove('open'); }

function _applyAdminVars(vars) {
    Object.entries(vars).forEach(([k,v]) => document.documentElement.style.setProperty(k,v));
}
function _selectAdminPreset(name) {
    const t = _THEMES_A[name]; if (!t) return;
    _applyAdminVars(t.vars);
    try { localStorage.setItem('rh_admin_theme', JSON.stringify({name, vars:t.vars})); } catch(e) {}
    document.querySelectorAll('#tp-presets-grid .tp-preset').forEach(b => b.classList.toggle('active', b.dataset.t === name));
    document.getElementById('tc-sb').value = t.vars['--sidebar-bg'];
    document.getElementById('tc-ac').value = t.vars['--accent-blue'];
    document.getElementById('tc-bg').value = t.vars['--bg-page'];
}
function _applyCustomAdmin() {
    const vars = {
        '--sidebar-bg': document.getElementById('tc-sb').value,
        '--sidebar-hover': document.getElementById('tc-sb').value,
        '--sidebar-active': document.getElementById('tc-ac').value,
        '--accent-blue': document.getElementById('tc-ac').value,
        '--bg-page': document.getElementById('tc-bg').value,
        '--card-bg':'#ffffff','--topbar-bg':'#ffffff',
        '--text-primary':'#1e293b','--text-secondary':'#64748b','--border':'#e2e8f0'
    };
    _applyAdminVars(vars);
    try { localStorage.setItem('rh_admin_theme', JSON.stringify({name:'Custom', vars})); } catch(e) {}
    document.querySelectorAll('#tp-presets-grid .tp-preset').forEach(b => b.classList.remove('active'));
}
function _resetAdminTheme() {
    localStorage.removeItem('rh_admin_theme');
    Object.keys(_THEMES_A['Blue Classic'].vars).forEach(k => document.documentElement.style.removeProperty(k));
    document.querySelectorAll('#tp-presets-grid .tp-preset').forEach(b => b.classList.toggle('active', b.dataset.t === 'Blue Classic'));
    document.getElementById('tc-sb').value = '#1a2035';
    document.getElementById('tc-ac').value = '#2563eb';
    document.getElementById('tc-bg').value = '#f0f4f8';
}

document.addEventListener('DOMContentLoaded', function() {
    const grid = document.getElementById('tp-presets-grid');
    let activeName = 'Blue Classic';
    try { activeName = JSON.parse(localStorage.getItem('rh_admin_theme') || '{}').name || 'Blue Classic'; } catch(e) {}
    Object.entries(_THEMES_A).forEach(([name, t]) => {
        const btn = document.createElement('button');
        btn.className = 'tp-preset' + (name === activeName ? ' active' : '');
        btn.dataset.t = name;
        btn.onclick = () => _selectAdminPreset(name);
        btn.innerHTML = '<div class="tp-swatches">' + t.sw.map(c => '<span class="tp-swatch" style="background:' + c + '"></span>').join('') + '</div><div class="tp-name">' + name + '</div>';
        grid.appendChild(btn);
    });
    const ct = _THEMES_A[activeName] || _THEMES_A['Blue Classic'];
    document.getElementById('tc-sb').value = ct.vars['--sidebar-bg'];
    document.getElementById('tc-ac').value = ct.vars['--accent-blue'];
    document.getElementById('tc-bg').value = ct.vars['--bg-page'];
});
</script>

<!-- ── LOCK SCREEN ───────────────────────────────────────────────────────── -->
<div id="lock-overlay" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(15,23,42,.94);backdrop-filter:blur(24px);align-items:center;justify-content:center">
    <div style="text-align:center;width:100%;max-width:340px;padding:20px">
        <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,var(--sidebar-active,#2563eb),#6366f1);display:flex;align-items:center;justify-content:center;font-size:30px;font-weight:800;color:#fff;margin:0 auto 16px;box-shadow:0 8px 32px rgba(37,99,235,.4)" id="lock-avatar-text">A</div>
        <div style="font-size:20px;font-weight:700;color:#e2e8f0;margin-bottom:2px" id="lock-user-name">Admin</div>
        <div style="font-size:11px;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px" id="lock-user-role"></div>
        <div style="font-size:12px;color:#94a3b8;margin-bottom:8px" id="lock-user-email"></div>
        <div style="font-size:12px;color:#64748b;margin-bottom:24px">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;vertical-align:-2px;margin-right:4px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            Session locked due to inactivity
        </div>
        <input type="password" id="lock-password" placeholder="Enter your password"
            onkeydown="if(event.key==='Enter')unlockScreen()"
            style="width:100%;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);border-radius:10px;padding:12px 14px;font-size:14px;color:#e2e8f0;font-family:inherit;outline:none;text-align:center;letter-spacing:2px;margin-bottom:10px;box-sizing:border-box">
        <div id="lock-error" style="display:none;color:#fca5a5;font-size:12px;margin-bottom:10px;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);border-radius:8px;padding:8px 12px"></div>
        <button id="lock-unlock-btn" onclick="unlockScreen()"
            style="width:100%;background:linear-gradient(135deg,var(--sidebar-active,#2563eb),#6366f1);color:#fff;border:none;border-radius:10px;padding:12px;font-size:14px;font-weight:700;cursor:pointer;font-family:inherit;margin-bottom:10px;box-shadow:0 4px 16px rgba(37,99,235,.4)">
            🔓 Unlock
        </button>
        <button onclick="_sessionLogout()"
            style="width:100%;background:rgba(239,68,68,.1);color:#fca5a5;border:1px solid rgba(239,68,68,.2);border-radius:10px;padding:10px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
            Sign Out Instead
        </button>
        <div id="lock-countdown" style="margin-top:16px;font-size:11px;color:#475569"></div>
    </div>
</div>

<!-- ── LOGOUT CONFIRMATION ───────────────────────────────────────────────── -->
<div id="logout-modal" style="display:none;position:fixed;inset:0;z-index:9998;background:rgba(0,0,0,.6);backdrop-filter:blur(8px);align-items:center;justify-content:center">
    <div style="background:var(--card-bg,#fff);border:1px solid var(--border,#e2e8f0);border-radius:16px;width:100%;max-width:360px;padding:28px;text-align:center;margin:20px;box-shadow:0 24px 60px rgba(0,0,0,.3)">
        <div style="width:52px;height:52px;background:rgba(239,68,68,.1);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:24px;height:24px;color:#ef4444"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
        </div>
        <div style="font-size:17px;font-weight:700;color:var(--text-primary,#1e293b);margin-bottom:8px">Sign Out?</div>
        <div style="font-size:13px;color:var(--text-secondary,#64748b);margin-bottom:24px">You will be logged out of the admin panel. Any unsaved changes will be lost.</div>
        <div style="display:flex;gap:10px">
            <button onclick="doLogout()"
                style="flex:1;background:rgba(239,68,68,.1);color:#ef4444;border:1px solid rgba(239,68,68,.25);border-radius:8px;padding:11px;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit">
                Yes, Sign Out
            </button>
            <button onclick="document.getElementById('logout-modal').style.display='none'"
                style="flex:1;background:var(--bg-page,#f0f4f8);color:var(--text-secondary,#64748b);border:1px solid var(--border,#e2e8f0);border-radius:8px;padding:11px;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit">
                Cancel
            </button>
        </div>
    </div>
</div>
</body>
</html>
