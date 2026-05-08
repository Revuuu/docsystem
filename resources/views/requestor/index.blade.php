<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>DOCSYSTEM - Upload</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;700;900&family=DM+Mono:wght@300;400;500&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css"/>

<style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
        --ink:          #0d1117;
        --paper:        #f5f0e8;
        --paper-warm:   #ede8dc;
        --paper-mid:    #d4cfc3;
        --gold:         #c9a84c;
        --gold-light:   #e8c97a;
        --red-acc:      #b83232;
        --text-body:    #1c2333;
        --text-muted:   #6b7890;
        --font-display: 'Playfair Display', Georgia, serif;
        --font-body:    'Libre Baskerville', Georgia, serif;
        --font-mono:    'DM Mono', monospace;
    }

    body {
        background-color: var(--paper);
        font-family: var(--font-body);
        min-height: 100vh;
        margin: 0;
    }

    /* ── Hamburger ─────────────────────────────────────── */
    .hamburger {
        display: none;
        position: fixed;
        top: 1rem; left: 1rem;
        z-index: 1000;
        background: var(--paper-warm);
        border: 1.5px solid var(--paper-mid);
        border-radius: 8px;
        padding: 0.5rem;
        cursor: pointer;
        flex-direction: column;
        gap: 5px;
        width: 40px; height: 40px;
        align-items: center;
        justify-content: center;
    }
    .hamburger span {
        display: block;
        width: 20px; height: 2px;
        background: var(--ink);
        border-radius: 2px;
        transition: transform 0.25s, opacity 0.25s;
    }
    .hamburger.open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
    .hamburger.open span:nth-child(2) { opacity: 0; }
    .hamburger.open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

    /* ── Overlay ────────────────────────────────────────── */
    .overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(13,17,23,0.4);
        z-index: 800;
        transition: opacity 0.3s;
    }
    .overlay.show { display: block; }

    /* ── Layout ─────────────────────────────────────────── */
    .main-container {
        display: grid;
        grid-template-columns: 220px 1fr;
        width: 100%;
        min-height: 100vh;
    }

    /* ── Sidebar ─────────────────────────────────────────── */
    .sidebar {
        background: var(--paper-warm);
        padding: 2rem 1rem;
        border-right: 1.5px solid var(--paper-mid);
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        align-items: center;
        text-align: center;
        height: 100vh;
        position: sticky;
        top: 0;
        transition: transform 0.3s ease;
    }

    .sidebar-logo {
        font-family: var(--font-display);
        font-size: 1.4rem;
        font-weight: 900;
        color: var(--ink);
        border-bottom: 2px solid var(--gold);
        padding-bottom: 0.5rem;
        width: 100%;
        text-align: center;
    }

    .user-avatar {
        width: 44px; height: 44px;
        border-radius: 50%;
        background: rgba(201,168,76,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: var(--font-display);
        font-size: 1rem;
        font-weight: 700;
        color: var(--gold);
    }

    .user-role-badge {
        font-size: 0.75rem;
        background: var(--paper-mid);
        padding: 0.2rem 0.6rem;
        border-radius: 4px;
        font-family: var(--font-mono);
        color: var(--text-body);
    }

    .sidebar h2 { font-family: var(--font-display); font-size: 1rem; color: var(--ink); }
    .sidebar p  { font-family: var(--font-body); color: var(--text-muted); font-size: 0.85rem; }

    .sidebar-nav { display: flex; flex-direction: column; gap: 0.4rem; width: 100%; }

    .sidebar-nav a {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        font-family: var(--font-mono);
        font-size: 0.78rem;
        color: var(--text-muted);
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        text-decoration: none;
        transition: background 0.2s, color 0.2s;
    }
    .sidebar-nav a:hover  { background: var(--paper-mid); color: var(--ink); }
    .sidebar-nav a.active { background: var(--gold); color: white; }
    .sidebar-nav a i { font-size: 16px; }

    .btn-logout {
        padding: 0.5rem 1.25rem;
        font-family: var(--font-mono);
        font-size: 0.8rem;
        background-color: var(--red-acc);
        color: var(--paper);
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: background 0.25s;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }
    .btn-logout:hover { background-color: #9a2828; }

    /* ── Main Content ───────────────────────────────────── */
    .content {
        display: flex;
        flex-direction: column;
        gap: 3rem;
        padding: 2rem;
        overflow-y: auto;
    }

    /* ── Hero ────────────────────────────────────────────── */
    .hero-section { display: flex; gap: 1.5rem; align-items: center; flex-wrap: wrap; }
    .hero-left    { display: flex; flex-direction: column; gap: 1.25rem; flex: 1 1 300px; }

    .hero-eyebrow      { display: flex; align-items: center; gap: 1rem; }
    .hero-eyebrow-line { width: 40px; height: 2px; background: var(--gold); flex-shrink: 0; }
    .hero-eyebrow-text {
        font-family: var(--font-mono);
        font-size: 0.68rem;
        text-transform: uppercase;
        color: var(--gold);
        font-weight: 500;
    }

    .hero-title {
        font-family: var(--font-display);
        font-size: clamp(1.8rem, 4vw, 3.5rem);
        font-weight: 900;
        color: var(--ink);
        line-height: 1.15;
    }
    .hero-subtitle { font-size: 0.95rem; line-height: 1.6; color: var(--text-muted); }

    /* ── Doc Stack ───────────────────────────────────────── */
    .doc-stack {
        position: relative;
        height: 280px;
        flex-shrink: 0;
        width: 260px;
        pointer-events: none;
    }
    .doc-card {
        position: absolute;
        background: var(--paper);
        border: 1.5px solid var(--paper-mid);
        padding: 1.25rem;
        box-shadow: 4px 4px 0 var(--paper-mid), 0 12px 32px rgba(13,17,23,0.1);
    }
    .doc-card .doc-line { height: 8px; background: var(--paper-mid); border-radius: 4px; margin-bottom: 0.5rem; }
    .doc-card .doc-line.short { width: 60%; }
    .doc-card .doc-line.gold  { background: rgba(201,168,76,0.3); }
    .doc-card:nth-child(1) { width: 220px; top: 60px; left: 30px; transform: rotate(4deg);  z-index: 1; }
    .doc-card:nth-child(2) { width: 230px; top: 30px; left: 15px; transform: rotate(-2deg); z-index: 2; }
    .doc-card:nth-child(3) { width: 240px; top: 0;    left: 0;    transform: rotate(1deg);  z-index: 3; }

    /* ── Upload Form ─────────────────────────────────────── */
    .upload-form {
        background: var(--paper-warm);
        padding: 2rem;
        border: 1px solid var(--paper-mid);
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        max-width: 580px;
    }
    .upload-form h2 { font-family: var(--font-display); font-size: 1.6rem; color: var(--ink); text-align: center; }

    .form-group { display: flex; flex-direction: column; gap: 0.35rem; }
    .form-group label {
        font-family: var(--font-mono);
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-body);
    }
    .form-group input[type="text"],
    .form-group input[type="file"],
    .form-group select {
        padding: 0.7rem 0.9rem;
        border-radius: 6px;
        border: 1px solid var(--paper-mid);
        font-family: var(--font-body);
        font-size: 0.95rem;
        width: 100%;
        background: white;
        color: var(--text-body);
    }
    .form-group select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7890' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.9rem center;
    }

    .btn-submit {
        padding: 0.85rem;
        font-family: var(--font-mono);
        font-size: 0.85rem;
        font-weight: 500;
        color: var(--paper);
        background: var(--gold);
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.25s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }
    .btn-submit:hover { background: var(--gold-light); color: var(--ink); }

    /* ── Alerts ──────────────────────────────────────────── */
    .alert { padding: 0.75rem 1rem; border-radius: 6px; font-family: var(--font-mono); font-size: 0.82rem; }
    .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .alert-danger  { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
    .alert-danger ul { padding-left: 1.25rem; margin-top: 0.25rem; }

    /* ── Tables ──────────────────────────────────────────── */
    .section-wrap { max-width: 580px; }

    .docs-table {
        width: 100%;
        border-collapse: collapse;
        background: var(--paper-warm);
        border: 1px solid var(--paper-mid);
        border-radius: 8px;
        overflow: hidden;
    }
    .docs-table th {
        padding: 0.65rem 0.9rem;
        text-align: left;
        font-family: var(--font-mono);
        font-size: 0.75rem;
        background: var(--paper-mid);
        color: var(--ink);
    }
    .docs-table td {
        padding: 0.65rem 0.9rem;
        border-top: 1px solid var(--paper-mid);
        font-family: var(--font-body);
        font-size: 0.85rem;
    }
    .docs-table td.mono { font-family: var(--font-mono); font-size: 0.75rem; color: var(--text-muted); }
    .docs-table td a { color: #2563eb; font-family: var(--font-mono); font-size: 0.78rem; text-decoration: underline; }

    .empty-state {
        font-family: var(--font-mono);
        font-size: 0.82rem;
        color: var(--text-muted);
        padding: 1rem;
        background: var(--paper-warm);
        border: 1px solid var(--paper-mid);
        border-radius: 8px;
    }

    /* ── Responsive ──────────────────────────────────────── */
    @media (max-width: 860px) {
        .hamburger { display: flex; }
        .main-container { grid-template-columns: 1fr; }
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            height: 100vh;
            z-index: 900;
            transform: translateX(-100%);
            box-shadow: 4px 0 20px rgba(0,0,0,0.12);
        }
        .sidebar.open { transform: translateX(0); }
        .content { padding: 1.25rem 1rem 2rem; padding-top: 4.5rem; }
        .hero-section { flex-direction: column; align-items: flex-start; }
        .doc-stack { display: none; }
        .upload-form, .section-wrap { max-width: 100%; }
        .hero-title { font-size: clamp(1.6rem, 7vw, 2.8rem); }
    }

    @media (max-width: 480px) {
        .content { padding: 1rem 0.75rem 2rem; padding-top: 4rem; }
        .upload-form { padding: 1.25rem; }
        .hero-title { font-size: 1.9rem; }
        .docs-table th:nth-child(2),
        .docs-table td:nth-child(2) { display: none; }
    }
</style>
</head>
<body>

{{-- Hamburger Button (mobile only) --}}
<button class="hamburger" id="hamburger" aria-label="Open navigation menu">
    <span></span><span></span><span></span>
</button>

{{-- Overlay --}}
<div class="overlay" id="overlay"></div>

<div class="main-container">

    {{-- Sidebar --}}
    <div class="sidebar" id="sidebar">
        <div class="sidebar-logo">DOCSYSTEM</div>

        {{-- User Info --}}
        <div style="display:flex;flex-direction:column;align-items:center;gap:0.4rem;width:100%;">
            <div class="user-avatar">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </div>
            <h2>{{ auth()->user()->name }}</h2>
            <span class="user-role-badge">{{ ucfirst(auth()->user()->role) }}</span>
        </div>

        {{-- Nav Links --}}
        <nav class="sidebar-nav">
            <a href="{{ route('dashboard', ['section' => 'upload']) }}"
               class="{{ $section === 'upload' ? 'active' : '' }}">
                <i class="ti ti-upload" aria-hidden="true"></i>Upload
            </a>

            <a href="{{ route('dashboard', ['section' => 'documents']) }}"
               class="{{ $section === 'documents' ? 'active' : '' }}">
                <i class="ti ti-files" aria-hidden="true"></i>My Documents
            </a>

            <a href="{{ route('dashboard', ['section' => 'signed']) }}"
               class="{{ $section === 'signed' ? 'active' : '' }}">
                <i class="ti ti-file-check" aria-hidden="true"></i>Signed Docs
            </a>

            <a href="{{ route('profile.edit') }}"
               class="{{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                <i class="ti ti-settings" aria-hidden="true"></i>Settings
            </a>
        </nav>

        {{-- Logout --}}
        <div style="margin-top:auto;padding-top:1rem;width:100%;display:flex;justify-content:center;">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout">
                    <i class="ti ti-logout" aria-hidden="true"></i>Logout
                </button>
            </form>
        </div>
    </div>

    {{-- Right Content --}}
    <div class="content">

        {{-- ══ UPLOAD SECTION ══════════════════════════════ --}}
        @if($section === 'upload')

            <div class="hero-section">
                <div class="hero-left">
                    <div class="hero-eyebrow">
                        <div class="hero-eyebrow-line"></div>
                        <span class="hero-eyebrow-text">Secure Document Platform</span>
                    </div>
                    <h1 class="hero-title">
                        Upload<br>Documents<br>to <em>DOCSYSTEM</em>
                    </h1>
                    <p class="hero-subtitle">
                        Add your PDFs securely. Once uploaded, they can be signed, approved, and managed directly on the platform.
                    </p>
                </div>
                <div class="doc-stack" aria-hidden="true">
                    <div class="doc-card"><div class="doc-line gold"></div><div class="doc-line"></div><div class="doc-line short"></div></div>
                    <div class="doc-card"><div class="doc-line gold"></div><div class="doc-line"></div><div class="doc-line short"></div></div>
                    <div class="doc-card"><div class="doc-line gold"></div><div class="doc-line"></div><div class="doc-line short"></div></div>
                </div>
            </div>

            <div class="upload-form">
                <h2>Upload Document</h2>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="title">Document Title</label>
                        <input type="text" name="title" id="title"
                               placeholder="Enter document title"
                               value="{{ old('title') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="approver_id">Choose Approver</label>
                        <select name="approver_id" id="approver_id" required>
                            <option value="">Select Approver</option>
                            @foreach(\App\Models\User::where('role', 'approver')->get() as $approver)
                                <option value="{{ $approver->id }}"
                                    {{ old('approver_id') == $approver->id ? 'selected' : '' }}>
                                    {{ $approver->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="file">Upload PDF</label>
                        <input type="file" name="file" id="file" accept="application/pdf" required>
                    </div>
                    <button type="submit" class="btn-submit">
                        <i class="ti ti-upload" aria-hidden="true"></i>Upload Document
                    </button>
                </form>
            </div>

        @endif
        {{-- ══ END UPLOAD SECTION ══════════════════════════ --}}


        {{-- ══ MY DOCUMENTS SECTION ════════════════════════ --}}
        @if($section === 'documents')

            <div class="section-wrap">
                <div class="hero-eyebrow" style="margin-bottom:1rem;">
                    <div class="hero-eyebrow-line"></div>
                    <span class="hero-eyebrow-text">My Documents</span>
                </div>
                <h2 style="font-family:var(--font-display);font-size:1.4rem;color:var(--ink);margin-bottom:1rem;">
                    All Uploaded Documents
                </h2>

                @if($documents->count() > 0)
                    <table class="docs-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Uploaded At</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $doc)
                            <tr>
                                <td>{{ $doc->title }}</td>
                                <td class="mono">{{ $doc->created_at->format('M d, Y h:i A') }}</td>
                                <td class="mono">{{ ucfirst($doc->status ?? 'pending') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="empty-state">No documents uploaded yet.</p>
                @endif
            </div>

        @endif
        {{-- ══ END MY DOCUMENTS SECTION ════════════════════ --}}


        {{-- ══ SIGNED DOCS SECTION ═════════════════════════ --}}
        @if($section === 'signed')

            <div class="section-wrap">
                <div class="hero-eyebrow" style="margin-bottom:1rem;">
                    <div class="hero-eyebrow-line"></div>
                    <span class="hero-eyebrow-text">Completed Documents</span>
                </div>
                <h2 style="font-family:var(--font-display);font-size:1.4rem;color:var(--ink);margin-bottom:1rem;">
                    Signed Documents
                </h2>

                @if($signedDocuments->count() > 0)
                    <table class="docs-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Signed At</th>
                                <th>Download</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($signedDocuments as $doc)
                            <tr>
                                <td>{{ $doc->title }}</td>
                                <td class="mono">
                                    {{ $doc->admin_signed_at ? $doc->admin_signed_at->format('M d, Y h:i A') : 'N/A' }}
                                </td>
                                <td>
                                    <a href="{{ asset('storage/' . $doc->signed_file_path) }}" target="_blank">
                                        Download
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="empty-state">No signed documents yet.</p>
                @endif
            </div>

        @endif
        {{-- ══ END SIGNED DOCS SECTION ═════════════════════ --}}

    </div>{{-- end .content --}}
</div>{{-- end .main-container --}}

<script>
    const hamburger = document.getElementById('hamburger');
    const sidebar   = document.getElementById('sidebar');
    const overlay   = document.getElementById('overlay');

    function openMenu() {
        sidebar.classList.add('open');
        hamburger.classList.add('open');
        overlay.style.display = 'block';
        requestAnimationFrame(() => overlay.classList.add('show'));
    }

    function closeMenu() {
        sidebar.classList.remove('open');
        hamburger.classList.remove('open');
        overlay.classList.remove('show');
        setTimeout(() => overlay.style.display = 'none', 300);
    }

    hamburger.addEventListener('click', () => {
        sidebar.classList.contains('open') ? closeMenu() : openMenu();
    });

    overlay.addEventListener('click', closeMenu);

    document.querySelectorAll('.sidebar-nav a').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 860) closeMenu();
        });
    });
</script>

</body>
</html>