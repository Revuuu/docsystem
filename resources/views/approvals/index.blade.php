<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>DOCSYSTEM - Approvals</title>

<style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
        --ink:       #0d1117;
        --paper:     #f5f0e8;
        --paper-warm:#ede8dc;
        --paper-mid: #d4cfc3;
        --gold:      #c9a84c;
        --gold-light:#e8c97a;
        --red-acc:   #b83232;
        --text-body: #1c2333;
        --text-muted:#6b7890;
        --font-display: 'Playfair Display', Georgia, serif;
        --font-body:    'Libre Baskerville', Georgia, serif;
        --font-mono:    'DM Mono', monospace;
    }

     body {
        background-color: var(--paper);
        font-family: var(--font-body);
        min-height: 100vh;
        display: flex;
        justify-content: flex-start;
        align-items: flex-start;
        margin: 0;
        padding: 0;
    }

    .main-container {
        display: grid;
        grid-template-columns: 220px 1fr;
        width: 100vw;
        height: 100vh;
    }

    /* Sidebar */
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

    .sidebar h2 {
        font-family: var(--font-display);
        font-size: 1.25rem;
        color: var(--ink);
    }

    .sidebar p {
        font-family: var(--font-body);
        color: var(--text-muted);
        font-size: 0.9rem;
    }

    .btn-logout {
        padding: 0.5rem 1rem;
        font-family: var(--font-mono);
        background-color: var(--red-acc);
        color: var(--paper);
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: background 0.25s;
    }

    .btn-logout:hover {
        background-color: #9a2828;
    }

    /* Right Content */
    .content {
        display: flex;
        flex-direction: column;
        gap: 3rem;
        padding: 2rem;
        overflow-y: auto;
    }
    h1 { font-size: 24px; margin-bottom: 1rem; }

    table { width: 100%; border-collapse: collapse; background: white; }
    th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
    th { background: #e8c97a; }

    .btn-sign {
        background: #2563eb; color: white; border: none;
        padding: 6px 12px; cursor: pointer; border-radius: 5px;
    }
    .btn-sign:hover { background: #1e40af; }
    .no-data { color: #6b7890; font-style: italic; }

    /* MODAL */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.6);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 999;
    }
    .modal-overlay.active { display: flex; }

    .modal-box {
        width: 70%;
        height: 90vh;
        background: white;
        display: flex;
        flex-direction: column;
        border-radius: 8px;
        overflow: hidden;
    }

    .modal-header {
        padding: 12px 16px;
        background: #e8c97a;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .modal-header h3 { margin: 0; }
    .modal-close {
        background: none; border: none;
        font-size: 22px; cursor: pointer; font-weight: bold;
    }

    .page-selector {
        padding: 8px 16px;
        background: #f5f5f5;
        display: flex;
        align-items: center;
        gap: 8px;
        border-bottom: 1px solid #ddd;
    }
    .page-selector input {
        width: 60px; padding: 4px 8px;
        border: 1px solid #ccc; border-radius: 4px;
    }

    /* PDF wrapper: this must be position:relative so the overlay sits on top */
    .pdf-wrapper {
        flex: 1;
        position: relative;
        overflow: hidden;
        background: #555;
    }

    .pdf-frame {
        width: 100%;
        height: 100%;
        border: none;
        display: block;
    }

    /* Transparent click layer sitting on top of the iframe */
    .pdf-click-layer {
        position: absolute;
        inset: 0;
        cursor: crosshair;
        z-index: 10;
    }

    /* Ghost signature that follows the click */
    .sig-ghost {
        position: absolute;
        display: none;
        border: 2px dashed #2563eb;
        background: rgba(37, 99, 235, 0.08);
        padding: 6px 10px;
        pointer-events: none;
        z-index: 20;
        font-size: 13px;
        color: #1e3a8a;
        white-space: nowrap;
        border-radius: 4px;
        transform: translate(0, -50%);
    }

    .modal-footer {
        padding: 10px 16px;
        display: flex;
        justify-content: space-between;
        background: #eee;
        border-top: 1px solid #ddd;
    }

    .btn-cancel {
        background: #b83232; color: white; border: none;
        padding: 8px 14px; cursor: pointer; border-radius: 5px;
    }
    .btn-confirm {
        background: #16a34a; color: white; border: none;
        padding: 8px 14px; cursor: pointer; border-radius: 5px;
    }
    .btn-confirm:disabled {
        background: #9ca3af; cursor: not-allowed;
    }
    .btn-confirm:not(:disabled):hover { background: #15803d; }

    .hint {
        font-size: 12px; color: #6b7280;
        align-self: center;
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
        <h1>Pending Approvals</h1>

        @if($approvals->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Uploaded By</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($approvals as $approval)
                    <tr>
                        <td>{{ $approval->document->title ?? 'Missing' }}</td>
                        <td>{{ $approval->document->uploader->name ?? 'Missing' }}</td>
                        <td>{{ ucfirst($approval->status) }}</td>
                        <td>
                            @if($approval->status === 'pending')
                                <button class="btn-sign"
                                    onclick="openSignModal(
                                        {{ $approval->id }},
                                        '{{ asset('storage/' . $approval->document->file_path) }}',
                                        '{{ route('approvals.approve', $approval->id) }}'
                                    )">
                                    Sign
                                </button>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <p class="no-data">No pending approvals.</p>

        @endif

        {{-- Signed Documents --}}
        <h1>Signed Documents</h1>

        @php
            $signedDocs = \App\Models\Document::whereNotNull('signed_file_path')
                ->whereNotNull('admin_signed_at')
                ->latest('admin_signed_at')
                ->get();
        @endphp

        @if($signedDocs->count() > 0)
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
                        <td>{{ $doc->admin_signed_at ? $doc->admin_signed_at->format('M d, Y h:i A') : 'N/A' }}</td>
                        <td>
                            <a href="{{ asset('storage/' . $doc->signed_file_path) }}"
                               target="_blank"
                               style="color:#2563eb; text-decoration:underline;">
                                Download
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="no-data">No signed documents yet.</p>
        @endif
    </div>
</div>
{{-- MODAL --}}
<div class="modal-overlay" id="signModal">
    <div class="modal-box">

        <div class="modal-header">
            <h3>Place Signature</h3>
            <button onclick="closeSignModal()" class="modal-close">×</button>
        </div>

        <div class="page-selector">
            <label for="sigPageInput">Page:</label>
            <input type="number" id="sigPageInput" value="1" min="1">
        </div>

        <div class="pdf-wrapper" id="pdfWrapper">
            <iframe class="pdf-frame" id="pdfFrame"></iframe>

            {{-- Click layer sits on top of iframe to capture clicks --}}
            <div class="pdf-click-layer" id="pdfClickLayer"></div>

            {{-- Ghost shows where signature will be placed --}}
            <div class="sig-ghost" id="sigGhost">
                ✍ {{ auth()->user()->name }}
            </div>
        </div>

        <div class="modal-footer">
            <button class="btn-cancel" onclick="closeSignModal()">Cancel</button>
            <span class="hint" id="hintText">Click anywhere on the document to place your signature</span>
            <button class="btn-confirm" id="btnConfirm" onclick="submitSignature()" disabled>
                ✔ Approve & Sign
            </button>
        </div>

    </div>
</div>

<form id="signatureForm" method="POST" action="">
    @csrf
    <input type="hidden" name="sig_x" id="formSigX">
    <input type="hidden" name="sig_y" id="formSigY">
    <input type="hidden" name="sig_w" id="formSigW">
    <input type="hidden" name="sig_h" id="formSigH">
    <input type="hidden" name="sig_page" id="formSigPage">
</form>

<script>
let sigX = 0, sigY = 0;
const sigW = 200, sigH = 40; // fixed pixel size of ghost box
let placed = false;

const ghost   = document.getElementById('sigGhost');
const layer   = document.getElementById('pdfClickLayer');
const wrapper = document.getElementById('pdfWrapper');
const btn     = document.getElementById('btnConfirm');
const hint    = document.getElementById('hintText');

function openSignModal(docId, pdfUrl, signUrl) {
    document.getElementById('pdfFrame').src = pdfUrl;
    document.getElementById('signatureForm').action = signUrl;
    document.getElementById('sigPageInput').value = 1;

    ghost.style.display = 'none';
    placed = false;
    btn.disabled = true;
    hint.textContent = 'Click anywhere on the document to place your signature';

    document.getElementById('signModal').classList.add('active');
}

function closeSignModal() {
    document.getElementById('signModal').classList.remove('active');
    document.getElementById('pdfFrame').src = '';
}

layer.addEventListener('click', (e) => {
    const rect = wrapper.getBoundingClientRect();

    // Position relative to the wrapper
    sigX = e.clientX - rect.left;
    sigY = e.clientY - rect.top;

    // Show ghost at click point (centered vertically on click)
    ghost.style.left  = sigX + 'px';
    ghost.style.top   = sigY + 'px';
    ghost.style.display = 'block';

    placed = true;
    btn.disabled = false;
    hint.textContent = 'Signature placed! Click again to reposition, or click Approve & Sign.';
});

function submitSignature() {
    if (!placed) return;

    const canvasW = wrapper.clientWidth;
    const canvasH = wrapper.clientHeight;

    // Send normalized coordinates (0.0 – 1.0) to the backend
    document.getElementById('formSigX').value = (sigX / canvasW).toFixed(6);
    document.getElementById('formSigY').value = (sigY / canvasH).toFixed(6);
    document.getElementById('formSigW').value = (sigW / canvasW).toFixed(6);
    document.getElementById('formSigH').value = (sigH / canvasH).toFixed(6);
    document.getElementById('formSigPage').value = document.getElementById('sigPageInput').value;

    document.getElementById('signatureForm').submit();
}
</script>

</body>
</html>