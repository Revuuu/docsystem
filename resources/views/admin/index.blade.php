<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>DOCSYSTEM - Admin Dashboard</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;700;900&family=DM+Mono:wght@300;400;500&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet" />

<style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
        --ink: #0d1117;
        --paper: #f5f0e8;
        --paper-warm: #ede8dc;
        --paper-mid: #d4cfc3;
        --gold: #c9a84c;
        --gold-light: #e8c97a;
        --red-acc: #b83232;
        --blue-acc: #2563eb;
        --text-body: #1c2333;
        --text-muted: #6b7890;
        --font-display: 'Playfair Display', Georgia, serif;
        --font-body: 'Libre Baskerville', Georgia, serif;
        --font-mono: 'DM Mono', monospace;
    }

    body {
        background-color: var(--paper);
        font-family: var(--font-body);
        min-height: 100vh;
        display: flex;
        margin: 0;
        padding: 0;
    }

    .main-container {
        display: grid;
        grid-template-columns: 220px 1fr;
        width: 100vw;
        min-height: 100vh;
    }

    .sidebar {
        background: var(--paper-warm);
        padding: 2rem 1rem;
        border-radius: 0 12px 12px 0;
        display: flex;
        flex-direction: column;
        gap: 2rem;
        align-items: center;
        text-align: center;
        height: 100vh;
        position: sticky;
        top: 0;
    }

    .sidebar h2 { font-family: var(--font-display); font-size: 1.25rem; color: var(--ink); }
    .sidebar p  { font-family: var(--font-body); color: var(--text-muted); font-size: 0.9rem; }

    .btn-logout {
        padding: 0.5rem 1rem;
        font-family: var(--font-mono);
        background-color: var(--red-acc);
        color: var(--paper);
        border: none; border-radius: 6px; cursor: pointer;
        transition: background 0.25s;
    }
    .btn-logout:hover { background-color: #9a2828; }

    .content { padding: 2rem; display: flex; flex-direction: column; gap: 2rem; }

    h2.section-title {
        font-family: var(--font-display);
        font-size: 1.5rem; color: var(--ink); margin-bottom: 1rem;
    }

    .alert { padding: 0.75rem 1rem; border-radius: 8px; font-family: var(--font-mono); font-size: 0.9rem; }
    .alert-success { background-color: #dcfce7; color: #166534; }
    .alert-error   { background-color: #fee2e2; color: #991b1b; }

    table {
        width: 100%; border-collapse: collapse;
        background: var(--paper-warm);
        border: 1px solid var(--paper-mid);
        border-radius: 8px; overflow: hidden;
    }
    table thead tr { background-color: var(--paper-mid); }
    table th, table td {
        padding: 0.75rem 1rem; text-align: left;
        font-family: var(--font-body); font-size: 0.95rem;
        border-bottom: 1px solid var(--paper-mid);
    }
    table th { font-family: var(--font-mono); font-weight: 600; color: var(--ink); }
    table td a { color: #2563eb; text-decoration: underline; font-weight: 500; }

    .btn-action {
        padding: 6px 14px; border-radius: 6px; font-size: 13px;
        font-weight: 600; border: none; cursor: pointer; transition: all 0.25s;
    }
    .btn-sign       { background-color: var(--blue-acc); color: white; }
    .btn-sign:hover { background-color: #1e40af; }
    .btn-place      { background-color: var(--gold); color: var(--ink); }
    .btn-place:hover{ background-color: var(--gold-light); }

    /* ── Modal ── */
    .modal-overlay {
        display: none;
        position: fixed; inset: 0;
        background: rgba(13,17,23,0.75);
        z-index: 1000;
        align-items: flex-start;
        justify-content: center;
        overflow-y: auto;
        padding: 2rem 1rem;
    }
    .modal-overlay.active { display: flex; }

    .modal-box {
        background: var(--paper);
        border-radius: 12px;
        width: 100%;
        max-width: 860px;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        box-shadow: 0 24px 80px rgba(0,0,0,0.4);
        margin: auto;
    }

    .modal-header {
        background: var(--paper-warm);
        border-bottom: 1px solid var(--paper-mid);
        padding: 1rem 1.5rem;
        display: flex; align-items: center; justify-content: space-between;
    }
    .modal-header h3 { font-family: var(--font-display); font-size: 1.2rem; color: var(--ink); }
    .modal-close {
        background: none; border: none; cursor: pointer;
        font-size: 1.5rem; color: var(--text-muted); line-height: 1;
        transition: color 0.2s;
    }
    .modal-close:hover { color: var(--red-acc); }

    .modal-instructions {
        padding: 0.75rem 1.5rem;
        font-family: var(--font-mono);
        font-size: 0.82rem;
        color: var(--text-muted);
        background: #fffdf7;
        border-bottom: 1px solid var(--paper-mid);
        display: flex; align-items: center; gap: 0.5rem;
    }
    .modal-instructions .dot {
        width: 8px; height: 8px; border-radius: 50%;
        background: var(--gold); flex-shrink: 0;
        animation: pulse 1.5s ease-in-out infinite;
    }
    @keyframes pulse {
        0%,100% { opacity: 1; transform: scale(1); }
        50%      { opacity: 0.5; transform: scale(1.4); }
    }

    .page-selector {
        padding: 0.75rem 1.5rem;
        background: #fffdf7;
        border-bottom: 1px solid var(--paper-mid);
        display: flex; align-items: center; gap: 1rem;
        font-family: var(--font-mono); font-size: 0.82rem; color: var(--text-muted);
    }
    .page-selector label { color: var(--ink); font-weight: 500; }
    .page-selector input[type=number] {
        width: 60px; padding: 4px 8px;
        border: 1px solid var(--paper-mid); border-radius: 4px;
        font-family: var(--font-mono); font-size: 0.85rem;
        background: var(--paper); color: var(--ink);
    }

    .pdf-wrapper {
        position: relative;
        overflow: auto;
        background: #888;
        max-height: 68vh;
        cursor: crosshair;
    }

    .pdf-frame {
        width: 100%;
        height: 700px;
        border: none;
        display: block;
        pointer-events: none;
    }

    .pdf-click-layer {
        position: absolute;
        inset: 0;
        cursor: crosshair;
        z-index: 10;
    }

    .sig-ghost {
        position: absolute;
        width: 200px;
        height: 60px;
        border: 2px dashed var(--gold);
        background: rgba(201,168,76,0.18);
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: var(--font-mono);
        font-size: 0.78rem;
        color: var(--ink);
        pointer-events: none;
        user-select: none;
        z-index: 20;
        visibility: hidden;
    }
    .sig-ghost.placed {
        visibility: visible;
        pointer-events: all;
        cursor: grab;
    }
    .sig-ghost.placed:active { cursor: grabbing; }
    .sig-ghost.confirmed {
        border-color: #22c55e;
        background: rgba(34,197,94,0.15);
    }

    .sig-ghost-inner {
        display: flex; flex-direction: column; align-items: center; gap: 2px;
    }
    .sig-ghost-line {
        font-family: 'Playfair Display', serif;
        font-style: italic;
        font-size: 1rem;
        color: var(--ink);
        opacity: 0.7;
    }
    .sig-ghost-label {
        font-family: var(--font-mono);
        font-size: 0.65rem;
        color: var(--text-muted);
        letter-spacing: 0.05em;
    }

    .resize-handle {
        position: absolute;
        width: 10px; height: 10px;
        background: var(--gold);
        border: 2px solid white;
        border-radius: 2px;
    }
    .resize-handle.br { bottom: -5px; right: -5px; cursor: se-resize; }

    .modal-footer {
        padding: 1rem 1.5rem;
        background: var(--paper-warm);
        border-top: 1px solid var(--paper-mid);
        display: flex; align-items: center; justify-content: space-between; gap: 1rem;
    }

    .placement-info {
        font-family: var(--font-mono);
        font-size: 0.78rem;
        color: var(--text-muted);
    }
    .placement-info span { color: var(--ink); font-weight: 600; }

    .modal-actions { display: flex; gap: 0.75rem; }

    .btn-cancel-sig {
        padding: 8px 18px; border-radius: 6px;
        font-family: var(--font-mono); font-size: 0.85rem;
        background: var(--paper-mid); color: var(--ink);
        border: none; cursor: pointer; transition: background 0.2s;
    }
    .btn-cancel-sig:hover { background: #c0bab0; }

    .btn-confirm-sig {
        padding: 8px 18px; border-radius: 6px;
        font-family: var(--font-mono); font-size: 0.85rem; font-weight: 600;
        background: #22c55e; color: white;
        border: none; cursor: pointer;
        transition: background 0.2s;
        opacity: 0.4; pointer-events: none;
    }
    .btn-confirm-sig.ready { opacity: 1; pointer-events: all; }
    .btn-confirm-sig:hover { background: #16a34a; }

    @media(max-width:900px){
        .main-container { grid-template-columns: 1fr; }
        .content { padding: 1rem; }
        table th, table td { padding: 0.5rem; font-size: 0.85rem; }
        .modal-box { max-width: 100%; }
    }
</style>
</head>
<body>

<div class="main-container">

    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Logged in as</h2>
        <p>{{ auth()->user()->name }}</p>
        <p class="font-semibold text-sm">{{ ucfirst(auth()->user()->role) }}</p>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout">Logout</button>
        </form>
    </div>

    <!-- Right Content -->
    <div class="content">
     <!-- Signed Documents Section -->
        <div style="max-width:600px;">
            <div class="hero-eyebrow" style="margin-bottom:1rem;">
                <div class="hero-eyebrow-line"></div>
                <span class="hero-eyebrow-text">Completed Documents</span>
            </div>
            <h2 style="font-family:var(--font-display); font-size:1.5rem; color:var(--ink); margin-bottom:1.25rem;">
                Signed Documents
            </h2>

            @php
                $signedDocs = \App\Models\Document::whereNotNull('signed_file_path')
                    ->whereNotNull('admin_signed_at')
                    ->latest('admin_signed_at')
                    ->get();
            @endphp

            @if($signedDocs->count() > 0)
                <table style="width:100%; border-collapse:collapse; background:var(--paper-warm);
                              border:1px solid var(--paper-mid); border-radius:8px; overflow:hidden;">
                    <thead>
                        <tr style="background:var(--paper-mid);">
                            <th style="padding:0.75rem 1rem; text-align:left; font-family:var(--font-mono);
                                       font-size:0.8rem; color:var(--ink);">Title</th>
                            <th style="padding:0.75rem 1rem; text-align:left; font-family:var(--font-mono);
                                       font-size:0.8rem; color:var(--ink);">Signed At</th>
                            <th style="padding:0.75rem 1rem; text-align:left; font-family:var(--font-mono);
                                       font-size:0.8rem; color:var(--ink);">Download</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($signedDocs as $doc)
                        <tr style="border-top:1px solid var(--paper-mid);">
                            <td style="padding:0.75rem 1rem; font-family:var(--font-body); font-size:0.9rem;">
                                {{ $doc->title }}
                            </td>
                            <td style="padding:0.75rem 1rem; font-family:var(--font-mono); 
                                       font-size:0.8rem; color:var(--text-muted);">
                                {{ $doc->admin_signed_at ? $doc->admin_signed_at->format('M d, Y h:i A') : 'N/A' }}
                            </td>
                            <td style="padding:0.75rem 1rem;">
                                <a href="{{ asset('storage/' . $doc->signed_file_path) }}"
                                   target="_blank"
                                   style="color:#2563eb; font-family:var(--font-mono); 
                                          font-size:0.8rem; text-decoration:underline;">
                                    Download
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p style="font-family:var(--font-mono); font-size:0.85rem; color:var(--text-muted);
                           padding:1rem; background:var(--paper-warm); border:1px solid var(--paper-mid);
                           border-radius:8px;">
                    No signed documents yet.
                </p>
            @endif
        </div>
    </div>
</div>

</body>
</html>