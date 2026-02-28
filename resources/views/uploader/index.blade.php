<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>DOCSYSTEM - Upload</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;700;900&family=DM+Mono:wght@300;400;500&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet" />

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

    /* Hero + Doc Stack Layout */
    .hero-section {
        display: flex;
        gap: 1.5rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .hero-left {
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        gap: 1.5rem;
        flex: 1 1 400px;
    }

    .hero-eyebrow { display: flex; align-items: center; gap: 1rem; }
    .hero-eyebrow-line { width: 40px; height: 2px; background: var(--gold); }
    .hero-eyebrow-text { font-family: var(--font-mono); font-size: 0.68rem; text-transform: uppercase; color: var(--gold); font-weight:500; }

    .hero-title { font-family: var(--font-display); font-size: clamp(2rem,5vw,4rem); font-weight:900; color: var(--ink); }
    .hero-subtitle { font-size: 1rem; line-height:1.6; color: var(--text-muted); }


.doc-stack {
    position: relative;
    height: 420px;
    pointer-events: none;
    flex-shrink: 0;
    min-width: 320px;
    transform: translate(-200px, 150px); /* X, Y */
}
    .doc-card { position:absolute; background: var(--paper); border:1.5px solid var(--paper-mid); padding:1.5rem; box-shadow:4px 4px 0 var(--paper-mid), 0 16px 48px rgba(13,17,23,0.12); pointer-events:none; transition: transform 0.3s ease, box-shadow 0.3s ease; }
    .doc-card:nth-child(1){ width:300px; top:60px; left:40px; transform:rotate(4deg); z-index:1; }
    .doc-card:nth-child(2){ width:310px; top:30px; left:20px; transform:rotate(-2deg); z-index:2; }
    .doc-card:nth-child(3){ width:320px; top:0; left:0; transform:rotate(1deg); z-index:3; }

    .upload-form {
        background: var(--paper-warm);
        padding: 2.5rem;
        border: 1px solid var(--paper-mid);
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        max-width: 600px;
    }

    .upload-form h2 { font-family: var(--font-display); font-size:1.8rem; color: var(--ink); text-align:center; margin-bottom:1rem; }
    .upload-form label { font-family: var(--font-mono); font-size:0.75rem; color: var(--text-body); margin-bottom:0.25rem; }
    .upload-form input[type="text"], .upload-form input[type="file"] {
        padding: 0.75rem 1rem; border-radius:6px; border:1px solid var(--paper-mid); font-family: var(--font-body); font-size:1rem; width:100%;
    }

    .upload-form button {
        padding: 0.85rem 1rem;
        font-family: var(--font-mono);
        font-size:0.85rem;
        font-weight:500;
        color: var(--paper);
        background: var(--gold);
        border:none;
        border-radius:6px;
        cursor:pointer;
        transition: all 0.25s;
    }

    .upload-form button:hover { background: var(--gold-light); }

    @media(max-width:1200px){
        .hero-section { gap:2rem; }
    }

    @media(max-width:900px){
        .main-container { grid-template-columns: 1fr; }
        .hero-section { flex-direction: column; align-items: center; }
        .doc-stack { margin-top: 1rem; min-width: auto; height: 300px; }
        .upload-form { max-width: 100%; }
        .sidebar { position: relative; height: auto; border-radius:12px; }
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

        <!-- Hero + Doc Stack -->
        <div class="hero-section">
            <!-- Hero Left -->
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

            <!-- Document Stack Right -->
            <div class="doc-stack">
                <div class="doc-card"></div>
                <div class="doc-card"></div>
                <div class="doc-card"></div>
            </div>
        </div>

        <!-- Upload Form -->
        <div class="upload-form">
            <h2>Upload Document</h2>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div>
                    <label for="title">Document Title</label>
                    <input type="text" name="title" id="title" placeholder="Enter document title" required>
                </div>

                <div>
                    <label for="file">Upload PDF</label>
                    <input type="file" name="file" id="file" accept="application/pdf" required>
                </div>

                <button type="submit">Upload Document</button>
            </form>
        </div>

    </div>

</div>

</body>
</html>