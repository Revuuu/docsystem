<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>DOCSYSTEM</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;700;900&family=DM+Mono:wght@300;400;500&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet" />

        <style>
            *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

            :root {
                --ink:       #0d1117;
                --ink-mid:   #1c2333;
                --ink-soft:  #2d3a52;
                --paper:     #f5f0e8;
                --paper-warm:#ede8dc;
                --paper-mid: #d4cfc3;
                --gold:      #c9a84c;
                --gold-light:#e8c97a;
                --red-acc:   #b83232;
                --blue-acc:  #2a4a7f;
                --text-body: #1c2333;
                --text-muted:#6b7890;
                --font-display: 'Playfair Display', Georgia, serif;
                --font-body:    'Libre Baskerville', Georgia, serif;
                --font-mono:    'DM Mono', monospace;
            }

            html { font-size: 16px; }

            body {
                background-color: var(--paper);
                color: var(--text-body);
                font-family: var(--font-body);
                min-height: 100vh;
                overflow-x: hidden;
                position: relative;
            }

            /* ─── Paper texture overlay ─── */
            body::before {
                content: '';
                position: fixed;
                inset: 0;
                background-image:
                    url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='400'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3CfeColorMatrix type='saturate' values='0'/%3E%3C/filter%3E%3Crect width='400' height='400' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
                pointer-events: none;
                z-index: 0;
                opacity: 0.6;
            }

            /* ─── Grid lines background ─── */
            body::after {
                content: '';
                position: fixed;
                inset: 0;
                background-image:
                    linear-gradient(var(--paper-mid) 1px, transparent 1px),
                    linear-gradient(90deg, var(--paper-mid) 1px, transparent 1px);
                background-size: 48px 48px;
                pointer-events: none;
                z-index: 0;
                opacity: 0.35;
            }

            /* ─── Layout ─── */
            .page {
                position: relative;
                z-index: 1;
                min-height: 100vh;
                display: grid;
                grid-template-rows: auto 1fr auto;
            }

            /* ─── Top bar ─── */
            .topbar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 0 3rem;
                height: 64px;
                border-bottom: 1.5px solid var(--ink-mid);
                background: var(--ink);
                position: relative;
            }

            .topbar-left {
                display: flex;
                align-items: center;
                gap: 1rem;
            }

            .topbar-logo-mark {
                width: 28px;
                height: 28px;
                background: var(--gold);
                clip-path: polygon(0 0, 70% 0, 100% 30%, 100% 100%, 0 100%);
                flex-shrink: 0;
            }

            .topbar-brand {
                font-family: var(--font-mono);
                font-size: 0.75rem;
                letter-spacing: 0.22em;
                text-transform: uppercase;
                color: var(--paper);
                font-weight: 500;
            }

            .topbar-version {
                font-family: var(--font-mono);
                font-size: 0.65rem;
                color: var(--gold);
                letter-spacing: 0.1em;
                padding: 2px 8px;
                border: 1px solid var(--gold);
                border-radius: 2px;
            }

            .topbar-nav {
                display: flex;
                align-items: center;
                gap: 0.25rem;
            }

            .topbar-nav a {
                font-family: var(--font-mono);
                font-size: 0.7rem;
                letter-spacing: 0.12em;
                text-transform: uppercase;
                color: var(--paper-mid);
                text-decoration: none;
                padding: 6px 16px;
                border: 1px solid transparent;
                transition: all 0.2s;
            }

            .topbar-nav a:hover {
                color: var(--paper);
                border-color: rgba(245,240,232,0.2);
                background: rgba(245,240,232,0.06);
            }

            .topbar-nav a.btn-primary {
                color: var(--ink);
                background: var(--gold);
                border-color: var(--gold);
                font-weight: 500;
            }

            .topbar-nav a.btn-primary:hover {
                background: var(--gold-light);
                border-color: var(--gold-light);
            }

            /* ─── Fold corner (decorative) ─── */
            .topbar::after {
                content: '';
                position: absolute;
                bottom: -12px;
                left: 3rem;
                width: 0;
                height: 0;
                border-left: 6px solid transparent;
                border-right: 6px solid transparent;
                border-top: 12px solid var(--ink);
                display: none;
            }

            /* ─── Hero ─── */
            .hero {
                display: grid;
                grid-template-columns: 1fr 420px;
                gap: 0;
                padding: 5rem 3rem 4rem;
                max-width: 1400px;
                margin: 0 auto;
                width: 100%;
                align-items: start;
            }

            .hero-left { padding-right: 4rem; }

            .hero-eyebrow {
                display: flex;
                align-items: center;
                gap: 1rem;
                margin-bottom: 2rem;
                animation: fadeSlideUp 0.6s ease both;
            }

            .hero-eyebrow-line {
                width: 40px;
                height: 2px;
                background: var(--gold);
            }

            .hero-eyebrow-text {
                font-family: var(--font-mono);
                font-size: 0.68rem;
                letter-spacing: 0.25em;
                text-transform: uppercase;
                color: var(--gold);
                font-weight: 500;
            }

            .hero-title {
                font-family: var(--font-display);
                font-size: clamp(3rem, 6vw, 5.5rem);
                font-weight: 900;
                line-height: 0.95;
                color: var(--ink);
                margin-bottom: 1.5rem;
                animation: fadeSlideUp 0.6s 0.1s ease both;
            }

            .hero-title em {
                font-style: italic;
                color: var(--blue-acc);
            }

            .hero-subtitle {
                font-size: 1rem;
                line-height: 1.75;
                color: var(--text-muted);
                max-width: 520px;
                margin-bottom: 2.5rem;
                animation: fadeSlideUp 0.6s 0.2s ease both;
            }

            .hero-actions {
                display: flex;
                gap: 1rem;
                align-items: center;
                animation: fadeSlideUp 0.6s 0.3s ease both;
            }
            .hero-meta {
                margin-top: 2.75rem;
                margin-bottom: 2.75rem;
                display: flex;
                gap: 2rem;
                flex-wrap: wrap;
                font-family: var(--font-mono);
                font-size: 0.75rem;
                letter-spacing: 0.2em;
                text-transform: uppercase;
                color: rgba(28, 35, 51, 0.75);
            }

            .hero-meta span {
                position: relative;
            }

            .hero-meta span::before {
                content: '';
                position: absolute;
                left: -1rem;
                top: 50%;
                transform: translateY(-50%);
                width: 6px;
                height: 6px;
                background: var(--gold);
            }

            .btn {
                font-family: var(--font-mono);
                font-size: 0.72rem;
                letter-spacing: 0.14em;
                text-transform: uppercase;
                text-decoration: none;
                padding: 14px 28px;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                transition: all 0.25s;
                font-weight: 500;
            }

            .btn-dark {
                background: var(--ink);
                color: var(--paper);
                border: 2px solid var(--ink);
            }

            .btn-dark:hover {
                background: var(--ink-soft);
                border-color: var(--ink-soft);
                transform: translateY(-2px);
                box-shadow: 0 8px 24px rgba(13,17,23,0.25);
            }

            .btn-outline {
                background: transparent;
                color: var(--ink);
                border: 2px solid var(--ink-mid);
            }

            .btn-outline:hover {
                border-color: var(--ink);
                background: rgba(13,17,23,0.04);
                transform: translateY(-2px);
            }

            .btn-arrow {
                font-size: 1rem;
                transition: transform 0.2s;
            }

            .btn-dark:hover .btn-arrow { transform: translateX(4px); }

            /* ─── Document card stack ─── */
            .hero-right {
                position: relative;
                height: 520px;
                animation: fadeSlideUp 0.7s 0.15s ease both;
            }

            .doc-stack {
                position: absolute;
                inset: 0;
            }

            .doc-card {
                position: absolute;
                background: var(--paper);
                border: 1.5px solid var(--paper-mid);
                padding: 2rem;
                box-shadow: 4px 4px 0 var(--paper-mid), 0 16px 48px rgba(13,17,23,0.12);
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }

            .doc-card:nth-child(1) {
                width: 340px;
                top: 60px;
                left: 40px;
                transform: rotate(4deg);
                background: var(--paper-warm);
                z-index: 1;
            }

            .doc-card:nth-child(2) {
                width: 350px;
                top: 30px;
                left: 20px;
                transform: rotate(-2deg);
                z-index: 2;
            }

            .doc-card:nth-child(3) {
                width: 360px;
                top: 0;
                left: 0;
                transform: rotate(0.5deg);
                z-index: 3;
                box-shadow: 6px 6px 0 var(--paper-mid), 0 24px 64px rgba(13,17,23,0.18);
            }

            .hero-right:hover .doc-card:nth-child(1) { transform: rotate(6deg) translate(8px, 12px); }
            .hero-right:hover .doc-card:nth-child(2) { transform: rotate(-4deg) translate(-6px, 4px); }
            .hero-right:hover .doc-card:nth-child(3) { transform: rotate(0.5deg) translate(0, -6px); box-shadow: 6px 12px 0 var(--paper-mid), 0 32px 80px rgba(13,17,23,0.22); }

            .doc-card-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 1.5rem;
                padding-bottom: 1rem;
                border-bottom: 1px solid var(--paper-mid);
            }

            .doc-card-type {
                font-family: var(--font-mono);
                font-size: 0.6rem;
                letter-spacing: 0.2em;
                text-transform: uppercase;
                color: var(--text-muted);
                background: var(--paper-mid);
                padding: 3px 8px;
            }

            .doc-card-id {
                font-family: var(--font-mono);
                font-size: 0.6rem;
                color: var(--gold);
                letter-spacing: 0.1em;
            }

            .doc-card-title {
                font-family: var(--font-display);
                font-size: 1.1rem;
                font-weight: 700;
                color: var(--ink);
                margin-bottom: 0.75rem;
                line-height: 1.3;
            }

            .doc-card-meta {
                display: flex;
                gap: 1.5rem;
                margin-bottom: 1rem;
            }

            .doc-meta-item {
                display: flex;
                flex-direction: column;
                gap: 2px;
            }

            .doc-meta-label {
                font-family: var(--font-mono);
                font-size: 0.55rem;
                letter-spacing: 0.15em;
                text-transform: uppercase;
                color: var(--text-muted);
            }

            .doc-meta-value {
                font-family: var(--font-mono);
                font-size: 0.72rem;
                color: var(--ink-soft);
                font-weight: 500;
            }

            .doc-card-lines {
                display: flex;
                flex-direction: column;
                gap: 6px;
            }

            .doc-line {
                height: 6px;
                background: var(--paper-mid);
                border-radius: 2px;
            }

            .doc-line:nth-child(1) { width: 100%; }
            .doc-line:nth-child(2) { width: 85%; }
            .doc-line:nth-child(3) { width: 92%; }
            .doc-line:nth-child(4) { width: 60%; background: rgba(201,168,76,0.3); }

            .doc-card-stamp {
                position: absolute;
                bottom: 24px;
                right: 24px;
                width: 64px;
                height: 64px;
                border: 2.5px solid var(--red-acc);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                transform: rotate(-15deg);
                opacity: 0.7;
            }

            .doc-card-stamp-text {
                font-family: var(--font-mono);
                font-size: 0.45rem;
                letter-spacing: 0.1em;
                text-transform: uppercase;
                color: var(--red-acc);
                text-align: center;
                font-weight: 500;
                line-height: 1.4;
            }

            /* ─── Stats bar ─── */
            .stats-bar {
                max-width: 1400px;
                margin: 0 auto;
                padding: 0 3rem 4rem;
                width: 100%;
            }

            .stats-inner {
                border-top: 1.5px solid var(--ink-mid);
                border-bottom: 1.5px solid var(--ink-mid);
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                animation: fadeSlideUp 0.6s 0.35s ease both;
            }

            .stat-item {
                padding: 2rem 2.5rem;
                border-right: 1px solid var(--paper-mid);
                position: relative;
            }

            .stat-item:last-child { border-right: none; }

            .stat-number {
                font-family: var(--font-display);
                font-size: 2.8rem;
                font-weight: 900;
                color: var(--ink);
                line-height: 1;
                margin-bottom: 0.4rem;
            }

            .stat-number sup {
                font-size: 1.2rem;
                font-weight: 700;
                color: var(--gold);
                vertical-align: super;
                margin-left: 2px;
            }

            .stat-label {
                font-family: var(--font-mono);
                font-size: 0.65rem;
                letter-spacing: 0.18em;
                text-transform: uppercase;
                color: var(--text-muted);
            }

            .stat-item::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 3px;
                height: 0;
                background: var(--gold);
                transition: height 0.4s ease;
            }

            .stat-item:hover::before { height: 100%; }
            .stat-item:hover .stat-number { color: var(--blue-acc); }

            /* ─── Features ─── */
            .features {
                background: var(--ink);
                padding: 5rem 3rem;
                position: relative;
                overflow: hidden;
            }

            .features::before {
                content: 'DOCSYSTEM';
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                font-family: var(--font-display);
                font-size: 18vw;
                font-weight: 900;
                color: rgba(255,255,255,0.025);
                white-space: nowrap;
                pointer-events: none;
                letter-spacing: -0.02em;
            }

            .features-inner {
                max-width: 1400px;
                margin: 0 auto;
                position: relative;
                z-index: 1;
            }

            .features-header {
                display: flex;
                align-items: flex-end;
                justify-content: space-between;
                margin-bottom: 3.5rem;
                padding-bottom: 2rem;
                border-bottom: 1px solid rgba(245,240,232,0.12);
            }

            .features-title {
                font-family: var(--font-display);
                font-size: clamp(2rem, 4vw, 3.2rem);
                font-weight: 700;
                color: var(--paper);
                line-height: 1.1;
            }

            .features-title em {
                font-style: italic;
                color: var(--gold-light);
            }

            .features-index {
                font-family: var(--font-mono);
                font-size: 0.65rem;
                letter-spacing: 0.2em;
                text-transform: uppercase;
                color: rgba(245,240,232,0.4);
            }

            .features-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 1px;
                background: rgba(245,240,232,0.08);
            }

            .feature-cell {
                background: var(--ink);
                padding: 2.5rem;
                transition: background 0.25s;
                cursor: default;
            }

            .feature-cell:hover { background: var(--ink-soft); }

            .feature-number {
                font-family: var(--font-mono);
                font-size: 0.6rem;
                letter-spacing: 0.2em;
                color: var(--gold);
                margin-bottom: 1.25rem;
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .feature-number::after {
                content: '';
                flex: 1;
                height: 1px;
                background: rgba(201,168,76,0.3);
            }

            .feature-icon {
                width: 44px;
                height: 44px;
                margin-bottom: 1.25rem;
                position: relative;
            }

            .feature-icon-inner {
                width: 100%;
                height: 100%;
                background: rgba(201,168,76,0.12);
                border: 1px solid rgba(201,168,76,0.3);
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.2rem;
                clip-path: polygon(0 0, 80% 0, 100% 20%, 100% 100%, 0 100%);
            }

            .feature-name {
                font-family: var(--font-display);
                font-size: 1.15rem;
                font-weight: 700;
                color: var(--paper);
                margin-bottom: 0.75rem;
                line-height: 1.3;
            }

            .feature-desc {
                font-size: 0.82rem;
                line-height: 1.7;
                color: rgba(245,240,232,0.5);
            }

            /* ─── Footer ─── */
            .footer {
                background: var(--ink-mid);
                border-top: 1px solid rgba(245,240,232,0.08);
                padding: 2rem 3rem;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .footer-brand {
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .footer-logo {
                width: 20px;
                height: 20px;
                background: var(--gold);
                clip-path: polygon(0 0, 70% 0, 100% 30%, 100% 100%, 0 100%);
            }

            .footer-name {
                font-family: var(--font-mono);
                font-size: 0.68rem;
                letter-spacing: 0.2em;
                text-transform: uppercase;
                color: rgba(245,240,232,0.5);
            }

            .footer-copy {
                font-family: var(--font-mono);
                font-size: 0.62rem;
                letter-spacing: 0.1em;
                color: rgba(245,240,232,0.3);
            }

            /* ─── Animations ─── */
            @keyframes fadeSlideUp {
                from { opacity: 0; transform: translateY(24px); }
                to   { opacity: 1; transform: translateY(0); }
            }

            /* ─── Responsive ─── */
            @media (max-width: 900px) {
                .hero { grid-template-columns: 1fr; padding: 3rem 1.5rem 2rem; }
                .hero-left { padding-right: 0; margin-bottom: 3rem; }
                .hero-right { height: 360px; }
                .doc-card { transform: none !important; }
                .doc-card:nth-child(1) { top: 40px; left: 20px; width: 280px; }
                .doc-card:nth-child(2) { top: 20px; left: 10px; width: 290px; }
                .doc-card:nth-child(3) { top: 0; left: 0; width: 300px; }
                .stats-inner { grid-template-columns: repeat(2, 1fr); }
                .stats-bar { padding: 0 1.5rem 3rem; }
                .features { padding: 3rem 1.5rem; }
                .features-grid { grid-template-columns: 1fr; }
                .features-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
                .topbar { padding: 0 1.5rem; }
                .footer { padding: 1.5rem; flex-direction: column; gap: 0.75rem; text-align: center; }
            }
        </style>
    </head>
    <body>
        <div class="page">

            <!-- ─── Top Bar ─── -->
            <header class="topbar">
                <div class="topbar-left">
                    <div class="topbar-logo-mark"></div>
                    <span class="topbar-brand">DocSystem</span>
                    <span class="topbar-version">v2.5</span>
                </div>

                @if (Route::has('login'))
                    <nav class="topbar-nav">
                        @auth
                            <a href="{{ url('/dashboard') }}">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}">Sign In</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn-primary">Register</a>
                            @endif
                        @endauth
                    </nav>
                @endif
            </header>

            <!-- ─── Main ─── -->
            <main>
                <!-- Hero -->
                <section class="hero">
                    <div class="hero-left">
                        <div class="hero-eyebrow">
                            <div class="hero-eyebrow-line"></div>
                            <span class="hero-eyebrow-text">Document Management Platform</span>
                        </div>

                        <h1 class="hero-title">
                            Upload.<br>Approve.<br><em>Signed.</em>
                        </h1>

                        <p class="hero-subtitle">
                            DOCSYSTEM allows users to upload documents and have them reviewed, approved, and digitally signed within seconds. 
                            No manual routing. No paper trails. Just fast, secure document approval.
                        </p>

                        <div class="hero-meta">
                            <span>Built with Laravel</span>
                            <span>Role-Based Authentication</span>
                            <span>Secure Digital Signing</span>
                        </div>

                        <div class="hero-actions">
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn btn-dark">
                                    Start Signing Electronically <span class="btn-arrow">→</span>
                                </a>
                            @endif
                            <a href="#features" class="btn btn-outline">View Features</a>
                        </div>
                    </div>

                    <div class="hero-right">
                        <div class="doc-stack">
                            <!-- Card 3 (back) -->
                            <div class="doc-card">
                                <div class="doc-card-header">
                                    <span class="doc-card-type">Archive</span>
                                    <span class="doc-card-id">#DOC-0081</span>
                                </div>
                                <div class="doc-card-title">Q3 Financial Report — FY2024</div>
                                <div class="doc-card-meta">
                                    <div class="doc-meta-item">
                                        <span class="doc-meta-label">Owner</span>
                                        <span class="doc-meta-value">Finance Dept.</span>
                                    </div>
                                    <div class="doc-meta-item">
                                        <span class="doc-meta-label">Modified</span>
                                        <span class="doc-meta-value">28 Feb 2026</span>
                                    </div>
                                </div>
                                <div class="doc-card-lines">
                                    <div class="doc-line"></div>
                                    <div class="doc-line"></div>
                                    <div class="doc-line"></div>
                                    <div class="doc-line"></div>
                                </div>
                            </div>

                            <!-- Card 2 (mid) -->
                            <div class="doc-card">
                                <div class="doc-card-header">
                                    <span class="doc-card-type">Policy</span>
                                    <span class="doc-card-id">#DOC-0042</span>
                                </div>
                                <div class="doc-card-title">Data Retention Policy v3</div>
                                <div class="doc-card-meta">
                                    <div class="doc-meta-item">
                                        <span class="doc-meta-label">Owner</span>
                                        <span class="doc-meta-value">Legal &amp; Compliance</span>
                                    </div>
                                    <div class="doc-meta-item">
                                        <span class="doc-meta-label">Modified</span>
                                        <span class="doc-meta-value">14 Feb 2026</span>
                                    </div>
                                </div>
                                <div class="doc-card-lines">
                                    <div class="doc-line"></div>
                                    <div class="doc-line"></div>
                                    <div class="doc-line"></div>
                                    <div class="doc-line"></div>
                                </div>
                            </div>

                            <!-- Card 1 (front) -->
                            <div class="doc-card">
                                <div class="doc-card-header">
                                    <span class="doc-card-type">Contract</span>
                                    <span class="doc-card-id">#DOC-0117</span>
                                </div>
                                <div class="doc-card-title">Vendor Agreement — TechCorp Inc.</div>
                                <div class="doc-card-meta">
                                    <div class="doc-meta-item">
                                        <span class="doc-meta-label">Owner</span>
                                        <span class="doc-meta-value">Procurement</span>
                                    </div>
                                    <div class="doc-meta-item">
                                        <span class="doc-meta-label">Modified</span>
                                        <span class="doc-meta-value">27 Feb 2026</span>
                                    </div>
                                </div>
                                <div class="doc-card-lines">
                                    <div class="doc-line"></div>
                                    <div class="doc-line"></div>
                                    <div class="doc-line"></div>
                                    <div class="doc-line"></div>
                                </div>
                                <div class="doc-card-stamp">
                                    <span class="doc-card-stamp-text">APPROVED<br>SIGNED</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Stats -->
                <section class="stats-bar">
                    <div class="stats-inner">
                        <div class="stat-item">
                            <div class="stat-number">10K<sup>+</sup></div>
                            <div class="stat-label">Documents Signed</div>
                        </div>

                        <div class="stat-item">
                            <div class="stat-number">3<sup>sec</sup></div>
                            <div class="stat-label">Avg. Approval Time</div>
                        </div>

                        <div class="stat-item">
                            <div class="stat-number">100<sup>%</sup></div>
                            <div class="stat-label">Secure Transactions</div>
                        </div>

                        <div class="stat-item">
                            <div class="stat-number">24/7</div>
                            <div class="stat-label">System Availability</div>
                        </div>
                    </div>
                </section>

                <!-- Features -->
                <section class="features" id="features">
                    <div class="features-inner">
                        <div class="features-header">
                            <h2 class="features-title">Built for<br><em>serious work.</em></h2>
                            <span class="features-index">CAPABILITIES / 06</span>
                        </div>

                        <div class="features-grid">
                            <div class="feature-cell">
                                <div class="feature-number">01</div>
                                <div class="feature-icon"><div class="feature-icon-inner">📂</div></div>
                                <div class="feature-name">Instant Document Upload</div>
                                <p class="feature-desc">
                                Upload contracts, reports, or agreements directly into the system with secure file storage and validation.
                                </p>
                            </div>
                            <div class="feature-cell">
                                <div class="feature-number">02</div>
                                <div class="feature-icon"><div class="feature-icon-inner">🔍</div></div>
                                <div class="feature-name">Role-Based Approval</div>
                                <p class="feature-desc">
                                Only authorized approvers can review and approve documents based on their assigned roles.
                                </p>
                            </div>
                            <div class="feature-cell">
                                <div class="feature-number">03</div>
                                <div class="feature-icon"><div class="feature-icon-inner">🔒</div></div>
                                <div class="feature-name">Automated Digital Signing</div>
                                <p class="feature-desc">
                                Approved documents are digitally signed by the system with timestamp validation.
                                </p>
                            </div>
                            <div class="feature-cell">
                                <div class="feature-number">04</div>
                                <div class="feature-icon"><div class="feature-icon-inner">🔄</div></div>
                                <div class="feature-name">Real-Time Status Tracking</div>
                                <p class="feature-desc">
                                Track whether documents are pending, approved, or signed in real time from your dashboard.
                                </p>
                            </div>
                            <div class="feature-cell">
                                <div class="feature-number">05</div>
                                <div class="feature-icon"><div class="feature-icon-inner">✍️</div></div>
                                <div class="feature-name">Secure Access Control</div>
                                <p class="feature-desc">
                                Admin and user roles ensure controlled access to document actions and approvals.
                                </p>
                            </div>
                            <div class="feature-cell">
                                <div class="feature-number">06</div>
                                <div class="feature-icon"><div class="feature-icon-inner">📊</div></div>
                                <div class="feature-name">Complete Audit Trail</div>
                                <p class="feature-desc">
                                Every approval and signature action is logged with timestamps for accountability.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>
            </main>

            <!-- ─── Footer ─── -->
            <footer class="footer">
                <div class="footer-brand">
                    <div class="footer-logo"></div>
                    <span class="footer-name">DocSystem</span>
                </div>
                <span class="footer-copy">© {{ date('Y') }} DOCSYSTEM. All records reserved.</span>
            </footer>

        </div>
    </body>
</html>