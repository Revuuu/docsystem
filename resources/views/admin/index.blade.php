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
        justify-content: flex-start;
        margin: 0;
        padding: 0;
    }

    .main-container {
        display: grid;
        grid-template-columns: 220px 1fr;
        width: 100vw;
        min-height: 100vh;
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

    /* Content Area */
    .content {
        padding: 2rem;
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    h2.section-title {
        font-family: var(--font-display);
        font-size: 1.5rem;
        color: var(--ink);
        margin-bottom: 1rem;
    }

    .alert {
        padding: 0.75rem 1rem;
        border-radius: 8px;
        font-family: var(--font-mono);
        font-size: 0.9rem;
    }

    .alert-success { background-color: #dcfce7; color: #166534; }
    .alert-error { background-color: #fee2e2; color: #991b1b; }

    table {
        width: 100%;
        border-collapse: collapse;
        background: var(--paper-warm);
        border: 1px solid var(--paper-mid);
        border-radius: 8px;
        overflow: hidden;
    }

    table thead tr {
        background-color: var(--paper-mid);
    }

    table th, table td {
        padding: 0.75rem 1rem;
        text-align: left;
        font-family: var(--font-body);
        font-size: 0.95rem;
        border-bottom: 1px solid var(--paper-mid);
    }

    table th {
        font-family: var(--font-mono);
        font-weight: 600;
        color: var(--ink);
    }

    table td a {
        color: #2563eb;
        text-decoration: underline;
        font-weight: 500;
    }

    .btn-action {
        padding: 6px 14px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.25s;
    }

    .btn-sign { background-color: #2563eb; color: white; }
    .btn-sign:hover { background-color: #1e40af; }

    @media(max-width:900px){
        .main-container { grid-template-columns: 1fr; }
        .content { padding: 1rem; }
        table th, table td { padding: 0.5rem; font-size: 0.85rem; }
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
        <!-- Pending Documents -->
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
                        <form action="{{ route('documents.adminSign', $doc->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-action btn-sign">Sign Document</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
            <p>No pending documents.</p>
        @endif

        {{-- Signed Documents --}}
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

</body>
</html>