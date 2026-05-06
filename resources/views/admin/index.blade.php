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

    <!-- Content -->
    <div class="content">
        <h2 class="section-title">Pending Documents for Admin</h2>

        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(isset($pendingDocuments) && $pendingDocuments->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>File</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pendingDocuments as $doc)
                <tr>
                    <td>{{ $doc->title }}</td>
                    <td>
                        <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank">View Document</a>
                    </td>
                    <td>
                        <button
                            type="button"
                            class="btn-action btn-place"
                            onclick="openSignModal(
                                {{ $doc->id }},
                                '{{ asset('storage/' . $doc->file_path) }}',
                                '{{ route('documents.adminSign', $doc->id) }}'
                            )">
                            Place Signature
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
            <p>No pending documents.</p>
        @endif

        @php
            $signedDocs = \App\Models\Document::whereNotNull('signed_file_path')->get();
        @endphp

        @if($signedDocs->count() > 0)
        <h2 class="section-title">Signed Documents</h2>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Signed At</th>
                    <th>Download</th>
                </tr>
            </thead>
            <tbody>
                @foreach($signedDocs as $doc)
                <tr>
                    <td>{{ $doc->title }}</td>
                    <td>{{ $doc->admin_signed_at }}</td>
                    <td>
                        <a href="{{ asset('storage/' . $doc->signed_file_path) }}" target="_blank">Download Signed PDF</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

</div>

<!-- Signature Placement Modal -->
<div class="modal-overlay" id="signModal">
    <div class="modal-box">

        <div class="modal-header">
            <h3>Place Your Signature</h3>
            <button class="modal-close" onclick="closeSignModal()" title="Close">&#215;</button>
        </div>

        <div class="modal-instructions">
            <div class="dot"></div>
            Click anywhere on the document to place your signature box, then drag to reposition it.
        </div>

        <div class="page-selector">
            <label for="sigPageInput">Page:</label>
            <input type="number" id="sigPageInput" value="1" min="1" max="999">
            <span style="font-size:0.75rem;color:var(--text-muted);">
                Enter the page number where the signature should appear.
            </span>
        </div>

        <div class="pdf-wrapper" id="pdfWrapper">
            <iframe class="pdf-frame" id="pdfFrame" src=""></iframe>
            <div class="pdf-click-layer" id="pdfClickLayer"></div>
            <div class="sig-ghost" id="sigGhost">
                <div class="sig-ghost-inner">
                    <div class="sig-ghost-line" id="sigName">Admin Signature</div>
                    <div class="sig-ghost-label">AUTHORIZED SIGNATORY</div>
                </div>
                <div class="resize-handle br" id="resizeHandle"></div>
            </div>
        </div>

        <div class="modal-footer">
            <div class="placement-info">
                Position — X: <span id="infoX">—</span>&nbsp;
                Y: <span id="infoY">—</span>&nbsp;|&nbsp;
                W: <span id="infoW">200</span>px&nbsp;
                H: <span id="infoH">60</span>px&nbsp;|&nbsp;
                Page: <span id="infoPage">1</span>
            </div>
            <div class="modal-actions">
                <button class="btn-cancel-sig" onclick="closeSignModal()">Cancel</button>
                <button class="btn-confirm-sig" id="btnConfirm" onclick="submitSignature()">
                    Confirm &amp; Sign
                </button>
            </div>
        </div>

    </div>
</div>

<!-- Hidden form submitted programmatically -->
<form id="signatureForm" method="POST" action="">
    @csrf
    <input type="hidden" name="sig_x"    id="formSigX">
    <input type="hidden" name="sig_y"    id="formSigY">
    <input type="hidden" name="sig_w"    id="formSigW">
    <input type="hidden" name="sig_h"    id="formSigH">
    <input type="hidden" name="sig_page" id="formSigPage">
</form>

<script>
let ghostPlaced = false;
let sigX = 0, sigY = 0, sigW = 200, sigH = 60;

const ghost        = document.getElementById('sigGhost');
const clickLayer   = document.getElementById('pdfClickLayer');
const wrapper      = document.getElementById('pdfWrapper');
const pdfFrame     = document.getElementById('pdfFrame');
const btnConfirm   = document.getElementById('btnConfirm');
const resizeHandle = document.getElementById('resizeHandle');
const sigPageInput = document.getElementById('sigPageInput');

// ── Open / Close ──────────────────────────────────────────────────────────────
function openSignModal(docId, pdfUrl, signUrl) {
    pdfFrame.src = pdfUrl;
    document.getElementById('signatureForm').action = signUrl;

    ghostPlaced = false;
    ghost.classList.remove('placed', 'confirmed');
    sigW = 200; sigH = 60;
    ghost.style.width  = sigW + 'px';
    ghost.style.height = sigH + 'px';
    btnConfirm.classList.remove('ready');
    updateInfo();

    document.getElementById('sigName').textContent = '{{ auth()->user()->name }}';
    document.getElementById('signModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeSignModal() {
    document.getElementById('signModal').classList.remove('active');
    document.body.style.overflow = '';
    pdfFrame.src = '';
}

// ── Place on click ────────────────────────────────────────────────────────────
clickLayer.addEventListener('click', function(e) {
    const rect = wrapper.getBoundingClientRect();
    sigX = e.clientX - rect.left  + wrapper.scrollLeft - sigW / 2;
    sigY = e.clientY - rect.top   + wrapper.scrollTop  - sigH / 2;
    clampGhost();
    positionGhost();
    if (!ghostPlaced) {
        ghost.classList.add('placed');
        ghostPlaced = true;
        btnConfirm.classList.add('ready');
    }
    ghost.classList.remove('confirmed');
});

// ── Drag ──────────────────────────────────────────────────────────────────────
let dragging = false, dragOffX = 0, dragOffY = 0;

ghost.addEventListener('mousedown', function(e) {
    if (e.target === resizeHandle) return;
    dragging = true;
    dragOffX = e.clientX - ghost.getBoundingClientRect().left;
    dragOffY = e.clientY - ghost.getBoundingClientRect().top;
    ghost.classList.remove('confirmed');
    e.preventDefault();
});

document.addEventListener('mousemove', function(e) {
    if (!dragging) return;
    const rect = wrapper.getBoundingClientRect();
    sigX = e.clientX - rect.left - dragOffX + wrapper.scrollLeft;
    sigY = e.clientY - rect.top  - dragOffY + wrapper.scrollTop;
    clampGhost();
    positionGhost();
});

document.addEventListener('mouseup', function() { dragging = false; });

// ── Resize ────────────────────────────────────────────────────────────────────
let resizing = false, resizeStartX = 0, resizeStartY = 0, resizeStartW = 0, resizeStartH = 0;

resizeHandle.addEventListener('mousedown', function(e) {
    resizing     = true;
    resizeStartX = e.clientX;
    resizeStartY = e.clientY;
    resizeStartW = sigW;
    resizeStartH = sigH;
    e.preventDefault();
    e.stopPropagation();
});

document.addEventListener('mousemove', function(e) {
    if (!resizing) return;
    sigW = Math.max(80, resizeStartW + (e.clientX - resizeStartX));
    sigH = Math.max(30, resizeStartH + (e.clientY - resizeStartY));
    ghost.style.width  = sigW + 'px';
    ghost.style.height = sigH + 'px';
    updateInfo();
});

document.addEventListener('mouseup', function() { resizing = false; });

// ── Touch support ─────────────────────────────────────────────────────────────
clickLayer.addEventListener('touchstart', function(e) {
    const touch = e.touches[0];
    const rect  = wrapper.getBoundingClientRect();
    sigX = touch.clientX - rect.left + wrapper.scrollLeft - sigW / 2;
    sigY = touch.clientY - rect.top  + wrapper.scrollTop  - sigH / 2;
    clampGhost();
    positionGhost();
    if (!ghostPlaced) {
        ghost.classList.add('placed');
        ghostPlaced = true;
        btnConfirm.classList.add('ready');
    }
    e.preventDefault();
}, { passive: false });

ghost.addEventListener('touchstart', function(e) {
    const touch = e.touches[0];
    dragging = true;
    dragOffX = touch.clientX - ghost.getBoundingClientRect().left;
    dragOffY = touch.clientY - ghost.getBoundingClientRect().top;
    e.preventDefault();
}, { passive: false });

document.addEventListener('touchmove', function(e) {
    if (!dragging) return;
    const touch = e.touches[0];
    const rect  = wrapper.getBoundingClientRect();
    sigX = touch.clientX - rect.left - dragOffX + wrapper.scrollLeft;
    sigY = touch.clientY - rect.top  - dragOffY + wrapper.scrollTop;
    clampGhost();
    positionGhost();
    e.preventDefault();
}, { passive: false });

document.addEventListener('touchend', function() { dragging = false; });

// ── Helpers ───────────────────────────────────────────────────────────────────
function clampGhost() {
    sigX = Math.max(0, Math.min(sigX, wrapper.scrollWidth  - sigW));
    sigY = Math.max(0, Math.min(sigY, wrapper.scrollHeight - sigH));
}

function positionGhost() {
    ghost.style.left = sigX + 'px';
    ghost.style.top  = sigY + 'px';
    updateInfo();
}

function updateInfo() {
    document.getElementById('infoX').textContent    = Math.round(sigX);
    document.getElementById('infoY').textContent    = Math.round(sigY);
    document.getElementById('infoW').textContent    = Math.round(sigW);
    document.getElementById('infoH').textContent    = Math.round(sigH);
    document.getElementById('infoPage').textContent = sigPageInput.value;
}

sigPageInput.addEventListener('input', updateInfo);

// ── Submit ────────────────────────────────────────────────────────────────────
function submitSignature() {
    if (!ghostPlaced) return;
    const canvasW = wrapper.scrollWidth;
    const canvasH = wrapper.scrollHeight;

    document.getElementById('formSigX').value    = (sigX / canvasW).toFixed(4);
    document.getElementById('formSigY').value    = (sigY / canvasH).toFixed(4);
    document.getElementById('formSigW').value    = (sigW / canvasW).toFixed(4);
    document.getElementById('formSigH').value    = (sigH / canvasH).toFixed(4);
    document.getElementById('formSigPage').value = sigPageInput.value;

    ghost.classList.add('confirmed');
    btnConfirm.textContent = 'Signing…';
    btnConfirm.classList.remove('ready');
    document.getElementById('signatureForm').submit();
}

// Close on overlay click or Escape key
document.getElementById('signModal').addEventListener('click', function(e) {
    if (e.target === this) closeSignModal();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeSignModal();
});
</script>

</body>
</html>