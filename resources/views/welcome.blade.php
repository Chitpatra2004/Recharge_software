<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RechargeHub — Fast & Reliable Recharge Platform</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --blue:     #3b82f6;
            --blue-dk:  #2563eb;
            --indigo:   #6366f1;
            --purple:   #8b5cf6;
            --green:    #10b981;
            --orange:   #f59e0b;
            --red:      #ef4444;
            --teal:     #14b8a6;
            /* Dark theme palette */
            --bg-body:  #040d21;
            --bg-deep:  #070f2b;
            --bg-card:  rgba(255,255,255,.04);
            --bg-card2: rgba(255,255,255,.06);
            --border:   rgba(255,255,255,.08);
            --border2:  rgba(255,255,255,.12);
            --text:     #f1f5f9;
            --muted:    #94a3b8;
            --muted2:   #64748b;
            --radius:   16px;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--bg-body);
            color: var(--text);
            font-size: 15px;
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* ── NOISE TEXTURE OVERLAY ─────────────────────── */
        body::before {
            content: '';
            position: fixed; inset: 0; z-index: 0; pointer-events: none;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.04'/%3E%3C/svg%3E");
            opacity: .4;
        }

        /* ── NAV ─────────────────────────────────────────── */
        nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            background: rgba(4,13,33,.85);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            padding: 0 5%;
            height: 64px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .nav-brand {
            display: flex; align-items: center; gap: 10px;
            text-decoration: none; color: var(--text);
        }
        .nav-brand-icon {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, var(--blue), var(--purple));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 16px rgba(99,102,241,.4);
        }
        .nav-brand-icon svg { width: 20px; height: 20px; color: #fff; }
        .nav-brand-name { font-size: 16px; font-weight: 800; letter-spacing: -.3px; }
        .nav-brand-name span { background: linear-gradient(90deg,var(--blue),var(--purple)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }

        .nav-links { display: flex; align-items: center; gap: 28px; }
        .nav-links a {
            font-size: 13.5px; font-weight: 500; color: var(--muted);
            text-decoration: none; transition: color .15s;
        }
        .nav-links a:hover { color: var(--text); }

        .nav-actions { display: flex; align-items: center; gap: 10px; }
        .btn-ghost {
            padding: 7px 16px; border-radius: 8px; font-size: 13px; font-weight: 600;
            color: var(--muted); background: var(--bg-card); border: 1px solid var(--border);
            cursor: pointer; text-decoration: none; transition: all .15s;
            display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-ghost:hover { color: var(--text); border-color: var(--border2); background: var(--bg-card2); }
        .btn-solid {
            padding: 7px 18px; border-radius: 8px; font-size: 13px; font-weight: 600;
            color: #fff; background: linear-gradient(135deg, var(--blue-dk), var(--indigo));
            border: none; cursor: pointer; text-decoration: none; transition: all .2s;
            display: inline-flex; align-items: center; gap: 6px;
            box-shadow: 0 4px 16px rgba(99,102,241,.3);
        }
        .btn-solid:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(99,102,241,.45); }

        /* ── HERO ─────────────────────────────────────────── */
        .hero {
            position: relative;
            padding: 150px 5% 110px;
            text-align: center;
            overflow: hidden;
        }

        /* Multi-layer gradient mesh background */
        .hero::before {
            content: '';
            position: absolute; inset: 0; z-index: 0;
            background:
                radial-gradient(ellipse 100% 80% at 50% -20%, rgba(99,102,241,.22) 0%, transparent 65%),
                radial-gradient(ellipse 60% 60% at 10% 60%,  rgba(59,130,246,.12) 0%, transparent 55%),
                radial-gradient(ellipse 50% 50% at 90% 70%,  rgba(139,92,246,.14) 0%, transparent 55%),
                linear-gradient(180deg, rgba(4,13,33,0) 0%, rgba(4,13,33,.6) 100%);
        }

        /* Grid dots pattern */
        .hero::after {
            content: '';
            position: absolute; inset: 0; z-index: 0;
            background-image: radial-gradient(circle, rgba(255,255,255,.07) 1px, transparent 1px);
            background-size: 40px 40px;
            mask-image: radial-gradient(ellipse 80% 70% at 50% 40%, black 30%, transparent 80%);
        }

        .hero > * { position: relative; z-index: 1; }

        .hero-badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(99,102,241,.15);
            border: 1px solid rgba(99,102,241,.35);
            border-radius: 100px;
            font-size: 12px; font-weight: 600; color: #a5b4fc;
            padding: 6px 16px; margin-bottom: 28px;
            letter-spacing: .3px;
        }
        .live-dot {
            width: 7px; height: 7px; border-radius: 50%;
            background: var(--green);
            box-shadow: 0 0 8px var(--green);
            animation: pulse-glow 2s infinite;
        }
        @keyframes pulse-glow {
            0%,100% { opacity:1; box-shadow: 0 0 6px var(--green); }
            50%      { opacity:.6; box-shadow: 0 0 14px var(--green); }
        }

        .hero h1 {
            font-size: clamp(38px, 5.8vw, 72px);
            font-weight: 900;
            line-height: 1.07;
            letter-spacing: -2.5px;
            color: #fff;
            max-width: 860px;
            margin: 0 auto 22px;
        }
        .hero h1 .grad {
            background: linear-gradient(135deg, #60a5fa 0%, #a78bfa 50%, #f472b6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-sub {
            font-size: clamp(15px, 1.8vw, 18px);
            color: var(--muted);
            max-width: 560px;
            margin: 0 auto 38px;
            line-height: 1.75;
        }

        .hero-actions {
            display: flex; gap: 14px; justify-content: center; flex-wrap: wrap;
            margin-bottom: 64px;
        }
        .btn-hero-primary {
            padding: 15px 32px; border-radius: 12px;
            font-size: 15px; font-weight: 700; color: #fff;
            background: linear-gradient(135deg, var(--blue-dk) 0%, var(--indigo) 100%);
            border: none; cursor: pointer; text-decoration: none;
            display: inline-flex; align-items: center; gap: 8px;
            box-shadow: 0 6px 28px rgba(99,102,241,.45), inset 0 1px 0 rgba(255,255,255,.15);
            transition: all .2s;
        }
        .btn-hero-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 36px rgba(99,102,241,.6); }
        .btn-hero-secondary {
            padding: 15px 30px; border-radius: 12px;
            font-size: 15px; font-weight: 600; color: var(--text);
            background: var(--bg-card2); border: 1px solid var(--border2);
            cursor: pointer; text-decoration: none;
            display: inline-flex; align-items: center; gap: 8px;
            transition: all .2s; backdrop-filter: blur(10px);
        }
        .btn-hero-secondary:hover { border-color: rgba(255,255,255,.25); background: rgba(255,255,255,.1); transform: translateY(-2px); }

        /* Hero stats */
        .hero-stats {
            display: inline-flex;
            border: 1px solid var(--border2);
            border-radius: 16px; overflow: hidden;
            background: rgba(255,255,255,.03);
            backdrop-filter: blur(16px);
            box-shadow: 0 8px 40px rgba(0,0,0,.4), inset 0 1px 0 rgba(255,255,255,.07);
        }
        .hero-stat {
            padding: 20px 36px; text-align: center;
            border-right: 1px solid var(--border);
        }
        .hero-stat:last-child { border-right: none; }
        .hero-stat-value {
            font-size: 28px; font-weight: 800; letter-spacing: -1px; color: #fff;
            background: linear-gradient(135deg, #fff 0%, #94a3b8 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }
        .hero-stat-label { font-size: 11.5px; color: var(--muted2); font-weight: 500; margin-top: 3px; }

        /* ── TICKER ──────────────────────────────────────── */
        .ticker-section {
            padding: 32px 0;
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            background: rgba(255,255,255,.02);
            overflow: hidden;
        }
        .ticker-label {
            text-align: center; font-size: 11px; font-weight: 700;
            color: var(--muted2); text-transform: uppercase; letter-spacing: 1.5px;
            margin-bottom: 18px;
        }
        .ticker-track {
            display: flex; gap: 14px; align-items: center;
            animation: ticker 22s linear infinite;
            width: max-content;
        }
        @keyframes ticker { from { transform:translateX(0); } to { transform:translateX(-50%); } }
        .t-chip {
            display: flex; align-items: center; gap: 8px;
            background: var(--bg-card); border: 1px solid var(--border2);
            border-radius: 10px; padding: 9px 18px;
            white-space: nowrap; font-size: 13px; font-weight: 600; color: var(--text);
            backdrop-filter: blur(8px);
        }
        .t-dot { width: 7px; height: 7px; border-radius: 50%; }

        /* ── SECTIONS ────────────────────────────────────── */
        .section { padding: 100px 5%; position: relative; }

        .section-tag {
            display: inline-block;
            background: rgba(99,102,241,.15);
            border: 1px solid rgba(99,102,241,.3);
            color: #a5b4fc;
            font-size: 11.5px; font-weight: 700;
            padding: 4px 14px; border-radius: 100px;
            text-transform: uppercase; letter-spacing: .8px;
            margin-bottom: 16px;
        }
        .section-title {
            font-size: clamp(26px, 3.2vw, 44px);
            font-weight: 800; line-height: 1.13; letter-spacing: -1.2px;
            color: #fff; margin-bottom: 14px;
        }
        .section-sub {
            font-size: 16px; color: var(--muted); max-width: 520px; line-height: 1.75;
        }

        /* ── FEATURES ────────────────────────────────────── */
        .features-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;
            margin-top: 56px;
        }
        .feat-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 28px;
            transition: all .25s;
            position: relative; overflow: hidden;
        }
        .feat-card::before {
            content: ''; position: absolute;
            top: 0; left: 0; right: 0; height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.12), transparent);
        }
        .feat-card:hover {
            background: var(--bg-card2);
            border-color: var(--border2);
            transform: translateY(-4px);
            box-shadow: 0 16px 48px rgba(0,0,0,.4);
        }
        .feat-card:hover .feat-glow { opacity: 1; }
        .feat-glow {
            position: absolute; width: 120px; height: 120px;
            border-radius: 50%; opacity: 0;
            transition: opacity .3s;
            top: -40px; right: -40px;
            filter: blur(40px); pointer-events: none;
        }
        .feat-icon {
            width: 48px; height: 48px; border-radius: 13px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 18px;
        }
        .feat-icon svg { width: 22px; height: 22px; }
        .fi-blue   { background:rgba(59,130,246,.18); color:#60a5fa; }
        .fi-green  { background:rgba(16,185,129,.18); color:#34d399; }
        .fi-purple { background:rgba(139,92,246,.18); color:#a78bfa; }
        .fi-orange { background:rgba(245,158,11,.18); color:#fbbf24; }
        .fi-red    { background:rgba(239,68,68,.18);  color:#f87171; }
        .fi-teal   { background:rgba(20,184,166,.18); color:#2dd4bf; }
        .glow-blue   { background: #3b82f6; }
        .glow-green  { background: #10b981; }
        .glow-purple { background: #8b5cf6; }
        .glow-orange { background: #f59e0b; }
        .glow-red    { background: #ef4444; }
        .glow-teal   { background: #14b8a6; }
        .feat-title { font-size: 15px; font-weight: 700; color: #fff; margin-bottom: 9px; }
        .feat-desc  { font-size: 13.5px; color: var(--muted); line-height: 1.68; }

        /* ── STEPS ───────────────────────────────────────── */
        .steps-bg {
            background: linear-gradient(180deg, rgba(99,102,241,.06) 0%, rgba(4,13,33,0) 100%);
            border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);
        }
        .steps-grid {
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px;
            margin-top: 56px; position: relative;
        }
        .steps-grid::before {
            content: '';
            position: absolute; top: 31px; left: calc(12.5% + 16px); right: calc(12.5% + 16px);
            height: 2px;
            background: linear-gradient(90deg, var(--blue-dk), var(--purple));
            opacity: .4;
        }
        .step { text-align: center; position: relative; z-index: 1; }
        .step-num {
            width: 64px; height: 64px; border-radius: 50%; margin: 0 auto 20px;
            background: linear-gradient(135deg, var(--blue-dk), var(--indigo));
            box-shadow: 0 0 0 8px rgba(99,102,241,.12), 0 8px 24px rgba(99,102,241,.3);
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; font-weight: 900; color: #fff;
        }
        .step-title { font-size: 14px; font-weight: 700; color: #fff; margin-bottom: 8px; }
        .step-desc  { font-size: 13px; color: var(--muted); line-height: 1.65; }

        /* ── API ─────────────────────────────────────────── */
        .api-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 64px; align-items: center; }
        .code-wrap {
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: 0 24px 80px rgba(0,0,0,.6), 0 0 0 1px var(--border);
            position: relative;
        }
        .code-wrap::before {
            content: '';
            position: absolute; top: -1px; left: 40px; right: 40px; height: 1px;
            background: linear-gradient(90deg, transparent, rgba(99,102,241,.6), transparent);
        }
        .code-bar {
            background: rgba(255,255,255,.04);
            border-bottom: 1px solid var(--border);
            padding: 12px 18px;
            display: flex; align-items: center; gap: 8px;
        }
        .dot { width: 12px; height: 12px; border-radius: 50%; }
        .dot-r { background: #ef4444; } .dot-y { background: #f59e0b; } .dot-g { background: #10b981; }
        .code-file { margin-left: 8px; font-size: 11px; color: var(--muted2); font-family: monospace; }
        .code-body {
            background: #060e25;
            padding: 22px 26px;
            font-family: 'Courier New', monospace; font-size: 13px; line-height: 1.85;
        }
        .ck { color: #7dd3fc; } .cs { color: #86efac; } .cc { color: #475569; }
        .cn { color: #fde68a; } .cp { color: #e2e8f0; } .cm { color: #c4b5fd; }

        .api-points { display: flex; flex-direction: column; gap: 14px; margin-top: 28px; }
        .api-point { display: flex; align-items: flex-start; gap: 12px; font-size: 14px; color: var(--muted); line-height: 1.6; }
        .api-check {
            width: 22px; height: 22px; border-radius: 50%; flex-shrink: 0;
            background: rgba(16,185,129,.15); border: 1px solid rgba(16,185,129,.3);
            display: flex; align-items: center; justify-content: center; margin-top: 1px;
        }
        .api-check svg { width: 11px; height: 11px; color: var(--green); }

        /* ── OPERATORS GRID ──────────────────────────────── */
        .ops-grid {
            display: grid; grid-template-columns: repeat(6, 1fr); gap: 12px;
            margin-top: 48px;
        }
        .op-card {
            background: var(--bg-card); border: 1px solid var(--border);
            border-radius: 14px; padding: 20px 12px;
            text-align: center; transition: all .2s;
        }
        .op-card:hover {
            background: var(--bg-card2); border-color: var(--border2);
            transform: translateY(-3px);
            box-shadow: 0 10px 32px rgba(0,0,0,.4);
        }
        .op-icon {
            width: 46px; height: 46px; border-radius: 13px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 10px;
            font-size: 15px; font-weight: 800;
        }
        .op-name { font-size: 12px; font-weight: 700; color: #cbd5e1; }
        .op-type { font-size: 10.5px; color: var(--muted2); margin-top: 2px; }

        /* ── STATS RIBBON ────────────────────────────────── */
        .ribbon {
            background: linear-gradient(135deg, #0a1628 0%, #0d1f44 50%, #0a1628 100%);
            border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);
            padding: 64px 5%;
            position: relative; overflow: hidden;
        }
        .ribbon::before {
            content: '';
            position: absolute; inset: 0;
            background: radial-gradient(ellipse 60% 80% at 50% 50%, rgba(99,102,241,.1) 0%, transparent 70%);
        }
        .ribbon-grid {
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 0;
            position: relative; z-index: 1;
        }
        .ribbon-stat {
            text-align: center; padding: 16px;
            border-right: 1px solid var(--border);
        }
        .ribbon-stat:last-child { border-right: none; }
        .ribbon-val {
            font-size: 42px; font-weight: 900; letter-spacing: -2px;
            background: linear-gradient(135deg, #fff 30%, #94a3b8 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }
        .ribbon-label { font-size: 13px; color: var(--muted2); margin-top: 4px; }
        .ribbon-sub   { font-size: 11px; color: var(--muted2); opacity:.6; margin-top:2px; }

        /* ── TESTIMONIALS ────────────────────────────────── */
        .testi-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 48px; }
        .testi-card {
            background: var(--bg-card); border: 1px solid var(--border);
            border-radius: var(--radius); padding: 28px;
            position: relative; overflow: hidden;
            transition: all .2s;
        }
        .testi-card::before {
            content: '"'; position: absolute; top: 10px; right: 20px;
            font-size: 80px; color: rgba(99,102,241,.1);
            font-family: Georgia, serif; line-height: 1;
        }
        .testi-card:hover { background: var(--bg-card2); transform: translateY(-3px); }
        .stars { color: var(--orange); font-size: 14px; margin-bottom: 14px; letter-spacing: 2px; }
        .testi-text { font-size: 14px; color: var(--muted); line-height: 1.75; margin-bottom: 20px; font-style: italic; }
        .testi-author { display: flex; align-items: center; gap: 12px; }
        .testi-av {
            width: 38px; height: 38px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 15px; color: #fff; flex-shrink: 0;
        }
        .testi-name { font-size: 13px; font-weight: 700; color: #fff; }
        .testi-role { font-size: 11.5px; color: var(--muted2); }

        /* ── CTA ─────────────────────────────────────────── */
        .cta-wrap {
            margin: 0 5% 80px;
            border-radius: 24px;
            background: linear-gradient(135deg, #1e1b4b 0%, #1e3a5f 50%, #1e1b4b 100%);
            border: 1px solid rgba(99,102,241,.3);
            padding: 80px 48px;
            text-align: center;
            position: relative; overflow: hidden;
            box-shadow: 0 32px 80px rgba(0,0,0,.5);
        }
        .cta-wrap::before {
            content: '';
            position: absolute; inset: 0;
            background: radial-gradient(ellipse 80% 80% at 50% 50%, rgba(99,102,241,.15) 0%, transparent 70%);
        }
        .cta-wrap::after {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; height: 1px;
            background: linear-gradient(90deg, transparent, rgba(99,102,241,.6), rgba(139,92,246,.6), transparent);
        }
        .cta-wrap > * { position: relative; z-index: 1; }
        .cta-wrap h2 {
            font-size: clamp(28px, 4vw, 48px);
            font-weight: 900; color: #fff; letter-spacing: -1.5px; margin-bottom: 14px;
        }
        .cta-wrap p { font-size: 16px; color: #94a3b8; margin-bottom: 36px; line-height: 1.7; }
        .cta-btns { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }
        .btn-cta-primary {
            padding: 15px 32px; border-radius: 12px; font-size: 15px; font-weight: 700;
            color: #fff; background: linear-gradient(135deg, var(--blue-dk), var(--indigo));
            border: none; cursor: pointer; text-decoration: none;
            display: inline-flex; align-items: center; gap: 8px;
            box-shadow: 0 6px 28px rgba(99,102,241,.5);
            transition: all .2s;
        }
        .btn-cta-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 36px rgba(99,102,241,.65); }
        .btn-cta-ghost {
            padding: 14px 28px; border-radius: 12px; font-size: 15px; font-weight: 600;
            color: var(--text); background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.15);
            cursor: pointer; text-decoration: none;
            display: inline-flex; align-items: center; gap: 8px;
            transition: all .2s;
        }
        .btn-cta-ghost:hover { background: rgba(255,255,255,.13); transform: translateY(-2px); }

        /* ── FOOTER ──────────────────────────────────────── */
        footer {
            background: var(--bg-deep);
            border-top: 1px solid var(--border);
            padding: 64px 5% 32px;
        }
        .footer-grid {
            display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 40px;
            margin-bottom: 48px;
        }
        .f-brand-name { font-size: 18px; font-weight: 800; color: #fff; margin-bottom: 12px; }
        .f-brand-name span { background: linear-gradient(90deg,var(--blue),var(--purple)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
        .f-desc { font-size: 13.5px; color: var(--muted2); line-height: 1.7; max-width: 260px; }
        .f-col-title { font-size: 11px; font-weight: 700; color: var(--muted2); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 14px; }
        .f-links { display: flex; flex-direction: column; gap: 9px; }
        .f-links a { font-size: 13.5px; color: #475569; text-decoration: none; transition: color .15s; }
        .f-links a:hover { color: var(--muted); }
        .f-bottom { border-top: 1px solid var(--border); padding-top: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; }
        .f-copy { font-size: 12.5px; color: var(--muted2); }
        .f-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(16,185,129,.1); border: 1px solid rgba(16,185,129,.2);
            color: var(--green); font-size: 11.5px; font-weight: 600;
            padding: 4px 12px; border-radius: 100px;
        }
        .f-badge::before { content:''; width:6px; height:6px; border-radius:50%; background:var(--green); box-shadow:0 0 6px var(--green); }

        /* ── RESPONSIVE ──────────────────────────────────── */
        @media (max-width: 1024px) {
            .features-grid { grid-template-columns: repeat(2, 1fr); }
            .ops-grid       { grid-template-columns: repeat(4, 1fr); }
            .testi-grid     { grid-template-columns: repeat(2, 1fr); }
            .footer-grid    { grid-template-columns: 1fr 1fr; }
            .api-grid       { grid-template-columns: 1fr; }
            .steps-grid     { grid-template-columns: repeat(2, 1fr); }
            .steps-grid::before { display: none; }
            .ribbon-grid    { grid-template-columns: repeat(2, 1fr); }
            .ribbon-stat:nth-child(2) { border-right: none; }
        }
        @media (max-width: 768px) {
            .nav-links { display: none; }
            .features-grid { grid-template-columns: 1fr; }
            .ops-grid       { grid-template-columns: repeat(3, 1fr); }
            .testi-grid     { grid-template-columns: 1fr; }
            .cta-wrap       { margin: 0 0 60px; border-radius: 0; }
            .hero h1        { letter-spacing: -1.5px; }
            .hero-stats     { flex-direction: column; width: 100%; max-width: 340px; }
            .hero-stat      { border-right: none; border-bottom: 1px solid var(--border); }
            .hero-stat:last-child { border-bottom: none; }
        }
        @media (max-width: 480px) {
            .ops-grid    { grid-template-columns: repeat(2, 1fr); }
            .footer-grid { grid-template-columns: 1fr; }
            .ribbon-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>

<!-- ── NAV ──────────────────────────────────────────────────────────────── -->
<nav>
    <a href="/" class="nav-brand">
        <div class="nav-brand-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <span class="nav-brand-name">Recharge<span>Hub</span></span>
    </a>
    <div class="nav-links">
        <a href="#features">Features</a>
        <a href="#how-it-works">How It Works</a>
        <a href="#operators">Operators</a>
        <a href="#api">API</a>
        <a href="#contact">Contact</a>
    </div>
    <div class="nav-actions">
        <a href="/user/login" class="btn-ghost">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            User Login
        </a>
        <a href="/admin/login" class="btn-solid">
            Admin Login
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:13px;height:13px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </a>
    </div>
</nav>

<!-- ── HERO ─────────────────────────────────────────────────────────────── -->
<section class="hero">
    <div class="hero-badge">
        <div class="live-dot"></div>
        Live Platform — 99.97% Uptime SLA
    </div>

    <h1>
        India's Most Reliable<br>
        <span class="grad">Recharge & Bill Payment</span><br>
        Platform
    </h1>

    <p class="hero-sub">
        One API. Every operator. Instant recharges, DTH, broadband, and utility payments
        with real-time tracking, automated retries, and full wallet management.
    </p>

    <div class="hero-actions">
        <a href="/admin/login" class="btn-hero-primary">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:17px;height:17px"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
            Admin Login
        </a>
        <a href="#api" class="btn-hero-secondary">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
            View API Docs
        </a>
    </div>

    <div class="hero-stats">
        <div class="hero-stat"><div class="hero-stat-value">50M+</div><div class="hero-stat-label">Transactions / Month</div></div>
        <div class="hero-stat"><div class="hero-stat-value">99.97%</div><div class="hero-stat-label">Success Rate</div></div>
        <div class="hero-stat"><div class="hero-stat-value">120+</div><div class="hero-stat-label">Operators Supported</div></div>
        <div class="hero-stat"><div class="hero-stat-value">&lt; 3s</div><div class="hero-stat-label">Avg Processing Time</div></div>
    </div>
</section>

<!-- ── TICKER ─────────────────────────────────────────────────────────────── -->
<div class="ticker-section">
    <div class="ticker-label">Operators We Support</div>
    <div style="overflow:hidden;padding:0">
        <div class="ticker-track">
            <div class="t-chip"><div class="t-dot" style="background:#ef4444;box-shadow:0 0 6px #ef4444"></div>Airtel</div>
            <div class="t-chip"><div class="t-dot" style="background:#3b82f6;box-shadow:0 0 6px #3b82f6"></div>Jio</div>
            <div class="t-chip"><div class="t-dot" style="background:#f59e0b;box-shadow:0 0 6px #f59e0b"></div>Vi (Vodafone)</div>
            <div class="t-chip"><div class="t-dot" style="background:#10b981;box-shadow:0 0 6px #10b981"></div>BSNL</div>
            <div class="t-chip"><div class="t-dot" style="background:#8b5cf6;box-shadow:0 0 6px #8b5cf6"></div>Tata Play</div>
            <div class="t-chip"><div class="t-dot" style="background:#14b8a6;box-shadow:0 0 6px #14b8a6"></div>Dish TV</div>
            <div class="t-chip"><div class="t-dot" style="background:#f97316;box-shadow:0 0 6px #f97316"></div>Sun Direct</div>
            <div class="t-chip"><div class="t-dot" style="background:#ec4899;box-shadow:0 0 6px #ec4899"></div>Hathway</div>
            <div class="t-chip"><div class="t-dot" style="background:#22c55e;box-shadow:0 0 6px #22c55e"></div>MTNL</div>
            <div class="t-chip"><div class="t-dot" style="background:#6366f1;box-shadow:0 0 6px #6366f1"></div>ACT Fibernet</div>
            <!-- duplicate -->
            <div class="t-chip"><div class="t-dot" style="background:#ef4444;box-shadow:0 0 6px #ef4444"></div>Airtel</div>
            <div class="t-chip"><div class="t-dot" style="background:#3b82f6;box-shadow:0 0 6px #3b82f6"></div>Jio</div>
            <div class="t-chip"><div class="t-dot" style="background:#f59e0b;box-shadow:0 0 6px #f59e0b"></div>Vi (Vodafone)</div>
            <div class="t-chip"><div class="t-dot" style="background:#10b981;box-shadow:0 0 6px #10b981"></div>BSNL</div>
            <div class="t-chip"><div class="t-dot" style="background:#8b5cf6;box-shadow:0 0 6px #8b5cf6"></div>Tata Play</div>
            <div class="t-chip"><div class="t-dot" style="background:#14b8a6;box-shadow:0 0 6px #14b8a6"></div>Dish TV</div>
            <div class="t-chip"><div class="t-dot" style="background:#f97316;box-shadow:0 0 6px #f97316"></div>Sun Direct</div>
            <div class="t-chip"><div class="t-dot" style="background:#ec4899;box-shadow:0 0 6px #ec4899"></div>Hathway</div>
            <div class="t-chip"><div class="t-dot" style="background:#22c55e;box-shadow:0 0 6px #22c55e"></div>MTNL</div>
            <div class="t-chip"><div class="t-dot" style="background:#6366f1;box-shadow:0 0 6px #6366f1"></div>ACT Fibernet</div>
        </div>
    </div>
</div>

<!-- ── FEATURES ──────────────────────────────────────────────────────────── -->
<section class="section" id="features">
    <div style="text-align:center;max-width:580px;margin:0 auto">
        <div class="section-tag">Platform Features</div>
        <h2 class="section-title">Everything to run a recharge business</h2>
        <p class="section-sub" style="margin:0 auto">From instant recharges to wallet management, complaints, and real-time analytics — all under one roof.</p>
    </div>
    <div class="features-grid">
        <div class="feat-card">
            <div class="feat-glow glow-blue"></div>
            <div class="feat-icon fi-blue"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg></div>
            <div class="feat-title">Instant Recharge Processing</div>
            <div class="feat-desc">Lightning-fast recharges with automated operator routing. Average processing under 3 seconds with smart retry logic for failed transactions.</div>
        </div>
        <div class="feat-card">
            <div class="feat-glow glow-green"></div>
            <div class="feat-icon fi-green"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg></div>
            <div class="feat-title">Wallet Management</div>
            <div class="feat-desc">Full-featured wallet with balance tracking, topups, transaction history, reservations, and instant reversals on failure.</div>
        </div>
        <div class="feat-card">
            <div class="feat-glow glow-purple"></div>
            <div class="feat-icon fi-purple"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg></div>
            <div class="feat-title">Developer REST API</div>
            <div class="feat-desc">Clean REST API with scoped API keys, rate limiting, HMAC callbacks, and comprehensive request logging for all integrations.</div>
        </div>
        <div class="feat-card">
            <div class="feat-glow glow-orange"></div>
            <div class="feat-icon fi-orange"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div>
            <div class="feat-title">Real-time Analytics</div>
            <div class="feat-desc">Live admin dashboard with KPI cards, operator health, failure heatmaps, hourly/weekly charts, and auto-refresh polling.</div>
        </div>
        <div class="feat-card">
            <div class="feat-glow glow-red"></div>
            <div class="feat-icon fi-red"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg></div>
            <div class="feat-title">Complaint Management</div>
            <div class="feat-desc">Built-in ticketing with SLA tracking, priority escalation, agent workload reporting, and full status audit logs.</div>
        </div>
        <div class="feat-card">
            <div class="feat-glow glow-teal"></div>
            <div class="feat-icon fi-teal"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg></div>
            <div class="feat-title">Enterprise Security</div>
            <div class="feat-desc">Brute-force protection, rate limiting, HMAC webhook verification, scoped API keys, and sanitized inputs on all endpoints.</div>
        </div>
    </div>
</section>

<!-- ── STATS RIBBON ───────────────────────────────────────────────────────── -->
<div class="ribbon">
    <div class="ribbon-grid">
        <div class="ribbon-stat">
            <div class="ribbon-val">50M+</div>
            <div class="ribbon-label">Transactions per Month</div>
            <div class="ribbon-sub">and growing</div>
        </div>
        <div class="ribbon-stat">
            <div class="ribbon-val">99.97%</div>
            <div class="ribbon-label">Platform Uptime SLA</div>
            <div class="ribbon-sub">across all regions</div>
        </div>
        <div class="ribbon-stat">
            <div class="ribbon-val">120+</div>
            <div class="ribbon-label">Operators Supported</div>
            <div class="ribbon-sub">mobile, DTH, broadband</div>
        </div>
        <div class="ribbon-stat">
            <div class="ribbon-val">2.8s</div>
            <div class="ribbon-label">Average Processing Time</div>
            <div class="ribbon-sub">end-to-end</div>
        </div>
    </div>
</div>

<!-- ── HOW IT WORKS ───────────────────────────────────────────────────────── -->
<section class="section steps-bg" id="how-it-works">
    <div style="text-align:center;max-width:560px;margin:0 auto">
        <div class="section-tag">How It Works</div>
        <h2 class="section-title">Recharge done in 4 simple steps</h2>
        <p class="section-sub" style="margin:0 auto">From API request to operator confirmation — the entire flow is automated, logged, and monitored.</p>
    </div>
    <div class="steps-grid">
        <div class="step">
            <div class="step-num">1</div>
            <div class="step-title">Authenticate</div>
            <div class="step-desc">Generate an API key from the dashboard. Pass it as <code style="background:rgba(255,255,255,.08);padding:1px 6px;border-radius:4px;font-size:11px">X-API-Key</code> header on every request.</div>
        </div>
        <div class="step">
            <div class="step-num">2</div>
            <div class="step-title">Send Request</div>
            <div class="step-desc">POST to <code style="background:rgba(255,255,255,.08);padding:1px 6px;border-radius:4px;font-size:11px">/api/v1/buyer/recharge</code> with mobile, operator, amount, and type.</div>
        </div>
        <div class="step">
            <div class="step-num">3</div>
            <div class="step-title">We Process It</div>
            <div class="step-desc">Our engine routes to the best gateway, deducts from wallet, and auto-retries on failure with full idempotency.</div>
        </div>
        <div class="step">
            <div class="step-num">4</div>
            <div class="step-title">Get Confirmation</div>
            <div class="step-desc">Poll the status endpoint or receive an instant HMAC-signed webhook callback to your registered URL.</div>
        </div>
    </div>
</section>

<!-- ── API SECTION ───────────────────────────────────────────────────────── -->
<section class="section" id="api">
    <div class="api-grid">
        <div>
            <div class="section-tag">REST API</div>
            <h2 class="section-title">Integrate in minutes,<br>not days</h2>
            <p class="section-sub" style="margin-bottom:0">Clean, predictable JSON API. Authenticate with a scoped API key and start processing recharges with a single POST.</p>
            <div class="api-points">
                <div class="api-point"><div class="api-check"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></div>Versioned endpoints <code style="background:rgba(255,255,255,.08);padding:1px 7px;border-radius:4px;font-size:12px;color:#a5b4fc">/api/v1/</code></div>
                <div class="api-point"><div class="api-check"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></div>Scoped API keys: <code style="background:rgba(255,255,255,.08);padding:1px 7px;border-radius:4px;font-size:12px;color:#a5b4fc">recharge:write</code> · <code style="background:rgba(255,255,255,.08);padding:1px 7px;border-radius:4px;font-size:12px;color:#a5b4fc">wallet:read</code></div>
                <div class="api-point"><div class="api-check"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></div>HMAC-signed webhook callbacks for async status updates</div>
                <div class="api-point"><div class="api-check"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></div>Rate limits: 60 req/min global · 10 req/min on recharge endpoint</div>
                <div class="api-point"><div class="api-check"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></div>Idempotent requests — safe to retry without duplicate charges</div>
            </div>
        </div>

        <div class="code-wrap">
            <div class="code-bar">
                <div class="dot dot-r"></div><div class="dot dot-y"></div><div class="dot dot-g"></div>
                <span class="code-file">POST /api/v1/buyer/recharge</span>
            </div>
            <div class="code-body">
<span class="cc">// Initiate a mobile recharge</span><br>
<span class="ck">curl</span> <span class="cs">-X POST</span> <span class="cp">\</span><br>
&nbsp; <span class="cs">"https://rechargechub.in/api/v1/buyer/recharge"</span> <span class="cp">\</span><br>
&nbsp; <span class="ck">-H</span> <span class="cs">"X-API-Key: rh_live_xxxxxxxxxxxxxxxx"</span> <span class="cp">\</span><br>
&nbsp; <span class="ck">-H</span> <span class="cs">"Content-Type: application/json"</span> <span class="cp">\</span><br>
&nbsp; <span class="ck">-d</span> <span class="cp">{</span><br>
&nbsp;&nbsp;&nbsp; <span class="cn">"mobile"</span><span class="cp">:</span>   <span class="cs">"9876543210"</span><span class="cp">,</span><br>
&nbsp;&nbsp;&nbsp; <span class="cn">"operator"</span><span class="cp">:</span> <span class="cs">"AIRTEL"</span><span class="cp">,</span><br>
&nbsp;&nbsp;&nbsp; <span class="cn">"amount"</span><span class="cp">:</span>   <span class="cm">299</span><span class="cp">,</span><br>
&nbsp;&nbsp;&nbsp; <span class="cn">"type"</span><span class="cp">:</span>     <span class="cs">"prepaid"</span><br>
&nbsp; <span class="cp">}</span><br>
<br>
<span class="cc">// 200 OK Response</span><br>
<span class="cp">{</span><br>
&nbsp; <span class="cn">"txn_id"</span><span class="cp">:</span>             <span class="cs">"TXN20260317001234"</span><span class="cp">,</span><br>
&nbsp; <span class="cn">"status"</span><span class="cp">:</span>             <span class="cs">"queued"</span><span class="cp">,</span><br>
&nbsp; <span class="cn">"wallet_balance"</span><span class="cp">:</span>      <span class="cm">4701.00</span><span class="cp">,</span><br>
&nbsp; <span class="cn">"poll_after_seconds"</span><span class="cp">:</span>  <span class="cm">5</span><br>
<span class="cp">}</span>
            </div>
        </div>
    </div>
</section>

<!-- ── OPERATORS ─────────────────────────────────────────────────────────── -->
<section class="section" id="operators" style="background:linear-gradient(180deg,rgba(99,102,241,.04) 0%,transparent 100%);border-top:1px solid var(--border)">
    <div style="text-align:center;max-width:560px;margin:0 auto">
        <div class="section-tag">Supported Operators</div>
        <h2 class="section-title">120+ operators across all categories</h2>
        <p class="section-sub" style="margin:0 auto">Mobile prepaid & postpaid, DTH, broadband, and utility bill payments — all on one platform.</p>
    </div>
    <div class="ops-grid">
        <div class="op-card"><div class="op-icon" style="background:rgba(239,68,68,.15);color:#f87171">AI</div><div class="op-name">Airtel</div><div class="op-type">Mobile · DTH</div></div>
        <div class="op-card"><div class="op-icon" style="background:rgba(59,130,246,.15);color:#60a5fa">JI</div><div class="op-name">Jio</div><div class="op-type">Mobile · Fiber</div></div>
        <div class="op-card"><div class="op-icon" style="background:rgba(245,158,11,.15);color:#fbbf24">Vi</div><div class="op-name">Vodafone Idea</div><div class="op-type">Mobile</div></div>
        <div class="op-card"><div class="op-icon" style="background:rgba(16,185,129,.15);color:#34d399">BS</div><div class="op-name">BSNL</div><div class="op-type">Mobile · Broadband</div></div>
        <div class="op-card"><div class="op-icon" style="background:rgba(139,92,246,.15);color:#a78bfa">TS</div><div class="op-name">Tata Play</div><div class="op-type">DTH</div></div>
        <div class="op-card"><div class="op-icon" style="background:rgba(20,184,166,.15);color:#2dd4bf">DT</div><div class="op-name">Dish TV</div><div class="op-type">DTH</div></div>
        <div class="op-card"><div class="op-icon" style="background:rgba(236,72,153,.15);color:#f472b6">SD</div><div class="op-name">Sun Direct</div><div class="op-type">DTH</div></div>
        <div class="op-card"><div class="op-icon" style="background:rgba(2,132,199,.15);color:#38bdf8">AC</div><div class="op-name">ACT Fibernet</div><div class="op-type">Broadband</div></div>
        <div class="op-card"><div class="op-icon" style="background:rgba(34,197,94,.15);color:#4ade80">HW</div><div class="op-name">Hathway</div><div class="op-type">Cable · Net</div></div>
        <div class="op-card"><div class="op-icon" style="background:rgba(249,115,22,.15);color:#fb923c">MT</div><div class="op-name">MTNL</div><div class="op-type">Broadband</div></div>
        <div class="op-card"><div class="op-icon" style="background:rgba(168,85,247,.15);color:#c084fc">DN</div><div class="op-name">Den Networks</div><div class="op-type">Cable TV</div></div>
        <div class="op-card" style="background:rgba(255,255,255,.02);border-style:dashed;border-color:rgba(255,255,255,.1)">
            <div class="op-icon" style="background:transparent;font-size:24px;color:#475569">+</div>
            <div class="op-name" style="color:#475569">110 more</div>
            <div class="op-type">All categories</div>
        </div>
    </div>
</section>

<!-- ── TESTIMONIALS ──────────────────────────────────────────────────────── -->
<section class="section">
    <div style="text-align:center;max-width:560px;margin:0 auto">
        <div class="section-tag">Testimonials</div>
        <h2 class="section-title">Trusted by retailers & distributors</h2>
        <p class="section-sub" style="margin:0 auto">From small retailers to large distribution networks, RechargeHub powers recharge businesses of all sizes.</p>
    </div>
    <div class="testi-grid">
        <div class="testi-card">
            <div class="stars">★★★★★</div>
            <p class="testi-text">"The API integration was seamless. Within 2 hours we had our first live recharge. The wallet system and instant failure reversals are game-changers for our business."</p>
            <div class="testi-author">
                <div class="testi-av" style="background:linear-gradient(135deg,#2563eb,#7c3aed)">R</div>
                <div><div class="testi-name">Rahul Mehta</div><div class="testi-role">CTO, FastRecharge Pvt. Ltd.</div></div>
            </div>
        </div>
        <div class="testi-card">
            <div class="stars">★★★★★</div>
            <p class="testi-text">"We process 50,000+ recharges daily. The admin dashboard gives complete visibility — operator health, failure reasons, SLAs. Everything in one place."</p>
            <div class="testi-author">
                <div class="testi-av" style="background:linear-gradient(135deg,#10b981,#2563eb)">P</div>
                <div><div class="testi-name">Priya Sharma</div><div class="testi-role">Operations Head, RechargeKart</div></div>
            </div>
        </div>
        <div class="testi-card">
            <div class="stars">★★★★★</div>
            <p class="testi-text">"Success rate went from 94% to 99.6% after switching. The auto-retry and smart routing alone paid for the upgrade in the first week."</p>
            <div class="testi-author">
                <div class="testi-av" style="background:linear-gradient(135deg,#f59e0b,#ef4444)">A</div>
                <div><div class="testi-name">Anil Kumar</div><div class="testi-role">Founder, AK Distributors</div></div>
            </div>
        </div>
    </div>
</section>

<!-- ── CTA ───────────────────────────────────────────────────────────────── -->
<div class="cta-wrap" id="contact">
    <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(99,102,241,.15);border:1px solid rgba(99,102,241,.3);border-radius:100px;font-size:12px;font-weight:600;padding:5px 16px;color:#a5b4fc;margin-bottom:22px">
        <div class="live-dot"></div>
        Platform is live and accepting partners
    </div>
    <h2>Ready to scale your recharge business?</h2>
    <p>Join thousands of retailers and distributors already on RechargeHub.<br>Get started in minutes — no setup fees, no contracts.</p>
    <div class="cta-btns">
        <a href="/admin/login" class="btn-cta-primary">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:17px;height:17px"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
            Admin Login
        </a>
        <a href="/user/register" class="btn-cta-ghost">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            Get Started
        </a>
    </div>
</div>

<!-- ── FOOTER ─────────────────────────────────────────────────────────────── -->
<footer>
    <div class="footer-grid">
        <div>
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px">
                <div style="width:32px;height:32px;background:linear-gradient(135deg,var(--blue-dk),var(--purple));border-radius:9px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(99,102,241,.35)">
                    <svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2.5" style="width:17px;height:17px"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div class="f-brand-name">Recharge<span>Hub</span></div>
            </div>
            <p class="f-desc">India's most reliable recharge and bill payment platform. Powering retailers, distributors, and API partners since 2024.</p>
            <div style="display:flex;gap:8px;margin-top:20px;flex-wrap:wrap">
                <span style="background:rgba(255,255,255,.05);border:1px solid var(--border);color:var(--muted2);font-size:11px;font-weight:600;padding:3px 10px;border-radius:6px">Laravel 11</span>
                <span style="background:rgba(255,255,255,.05);border:1px solid var(--border);color:var(--muted2);font-size:11px;font-weight:600;padding:3px 10px;border-radius:6px">REST API v1</span>
                <span style="background:rgba(255,255,255,.05);border:1px solid var(--border);color:var(--muted2);font-size:11px;font-weight:600;padding:3px 10px;border-radius:6px">99.97% SLA</span>
            </div>
        </div>
        <div>
            <div class="f-col-title">Platform</div>
            <div class="f-links">
                <a href="/admin/login">Admin Login</a>
                <a href="/user/login">User Login</a>
                <a href="/user/register">Register</a>
            </div>
        </div>
        <div>
            <div class="f-col-title">API</div>
            <div class="f-links">
                <a href="#">Authentication</a>
                <a href="#">Recharge API</a>
                <a href="#">Wallet API</a>
                <a href="#">Webhooks</a>
                <a href="#">Rate Limits</a>
            </div>
        </div>
        <div>
            <div class="f-col-title">Company</div>
            <div class="f-links">
                <a href="#">About</a>
                <a href="#">Pricing</a>
                <a href="#">Terms of Service</a>
                <a href="#">Privacy Policy</a>
                <a href="/admin/login">Admin Login</a>
            </div>
        </div>
    </div>
    <div class="f-bottom">
        <div class="f-copy">© {{ date('Y') }} RechargeHub. All rights reserved. Built with Laravel.</div>
        <div class="f-badge">Systems Operational</div>
    </div>
</footer>

</body>
</html>
