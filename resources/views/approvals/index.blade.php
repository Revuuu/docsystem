<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>DOCSYSTEM - Approvals</title>
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

    .btn-logout:hover { background-color: #9a2828; }

    /* Right Content */
    .content {
        display: flex;
        flex-direction: column;
        gap: 2rem;
        padding: 2rem;
        overflow-y: auto;
    }

    h1.page-title {
        font-family: var(--font-display);
        font-size: clamp(2rem,5vw,3rem);
        font-weight: 900;
        color: var(--ink);
        margin-bottom: 1rem;
    }

    .hero-subtitle {
        font-size: 1rem;
        line-height: 1.6;
        color: var(--text-muted);
        margin-bottom: 2rem;
    }

    .alert-success {
        background-color: #d1fae5;
        color: #065f46;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
    }

    .alert-danger {
        background-color: #fee2e2;
        color: #991b1b;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
    }

    /* Approvals Table */
    table.approvals-table {
        width: 100%;
        border-collapse: collapse;
        background: var(--paper-warm);
        border: 1px solid var(--paper-mid);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(13,17,23,0.1);
    }

    table.approvals-table th,
    table.approvals-table td {
        padding: 0.75rem 1rem;
        text-align: left;
        font-family: var(--font-body);
        font-size: 0.95rem;
        border-bottom: 1px solid var(--paper-mid);
    }

    table.approvals-table th {
        background: var(--paper-mid);
        font-family: var(--font-mono);
        font-weight: 600;
        color: var(--ink);
    }

    table.approvals-table tr:last-child td {
        border-bottom: none;
    }

    .btn-approve {
        background-color: #16a34a;
        color: white;
        padding: 6px 14px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: background 0.25s;
    }

    .btn-approve:hover {
        background-color: #15803d;
    }

    p.no-approvals {
        color: var(--text-muted);
        font-size: 0.95rem;
        font-style: italic;
    }

    @media(max-width:900px){
        .main-container { grid-template-columns: 1fr; }
        .content { padding: 1rem; }
        table.approvals-table th, table.approvals-table td { font-size: 0.85rem; padding: 0.5rem; }
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
        <h1 class="page-title">Pending Approvals</h1>
        <p class="hero-subtitle">Review documents and approve them securely on DOCSYSTEM.</p>

        @if(session('success'))
            <div class="alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if($approvals->count() > 0)
            <table class="approvals-table">
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
                        <td>{{ $approval->document->title ?? 'Document missing' }}</td>
                        <td>{{ $approval->document->uploader->name ?? 'Uploader missing' }}</td>
                        <td>{{ ucfirst($approval->status ?? 'N/A') }}</td>
                        <td>
                            @if($approval->status === 'pending')
                            <form method="POST" action="{{ route('approvals.approve', $approval->id) }}">
                                @csrf
                                <button type="submit" class="btn-approve">Approve</button>
                            </form>
                            @else
                                <span style="color: var(--text-muted)">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="no-approvals">No pending approvals.</p>
        @endif

    </div>
</div>

</body>
</html>