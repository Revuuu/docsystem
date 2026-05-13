@extends('layouts.app')

@section('title', 'DOCSYSTEM - Requestor Dashboard')

@section('content')

{{-- Hamburger --}}
<button class="hamburger" id="hamburger">
    <span></span>
    <span></span>
    <span></span>
</button>

{{-- Overlay --}}
<div class="overlay" id="overlay"></div>

<div class="main-container">

    {{-- Sidebar --}}
    <div class="sidebar" id="sidebar">

        <div class="sidebar-logo">
            DOCSYSTEM
        </div>

        {{-- User --}}
        <div class="sidebar-user">

            <div class="user-avatar">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </div>

            <h2>{{ auth()->user()->name }}</h2>

            <span class="user-role-badge">
                {{ ucfirst(auth()->user()->role) }}
            </span>

        </div>

        {{-- Navigation --}}
        <nav class="sidebar-nav">

            <a href="{{ route('dashboard', ['section' => 'upload']) }}"
               class="{{ $section === 'upload' ? 'active' : '' }}">

                <i class="ti ti-upload"></i>
                Upload

            </a>

            <a href="{{ route('dashboard', ['section' => 'documents']) }}"
               class="{{ $section === 'documents' ? 'active' : '' }}">

                <i class="ti ti-files"></i>
                My Documents

            </a>

            <a href="{{ route('dashboard', ['section' => 'signed']) }}"
               class="{{ $section === 'signed' ? 'active' : '' }}">

                <i class="ti ti-file-check"></i>
                Signed Docs

            </a>

            <a href="{{ route('profile.edit') }}"
               class="{{ request()->routeIs('profile.edit') ? 'active' : '' }}">

                <i class="ti ti-settings"></i>
                Settings

            </a>

        </nav>

        {{-- Logout --}}
        <div class="logout-wrap">

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button type="submit" class="btn-logout">
                    <i class="ti ti-logout"></i>
                    Logout
                </button>
            </form>

        </div>

    </div>

    {{-- Content --}}
    <div class="content">

        {{-- =========================================
             UPLOAD SECTION
        ========================================== --}}
        @if($section === 'upload')

            <div class="hero-section">

                <div class="hero-left">

                    <div class="hero-eyebrow">

                        <div class="hero-eyebrow-line"></div>

                        <span class="hero-eyebrow-text">
                            Secure Document Platform
                        </span>

                    </div>

                    <h1 class="hero-title">
                        Upload<br>
                        Documents<br>
                        to <em>DOCSYSTEM</em>
                    </h1>

                    <p class="hero-subtitle">
                        Add your PDFs securely. Once uploaded,
                        they can be signed, approved,
                        and managed directly on the platform.
                    </p>

                </div>

                <div class="doc-stack" aria-hidden="true">

                    <div class="doc-card">
                        <div class="doc-line gold"></div>
                        <div class="doc-line"></div>
                        <div class="doc-line short"></div>
                    </div>

                    <div class="doc-card">
                        <div class="doc-line gold"></div>
                        <div class="doc-line"></div>
                        <div class="doc-line short"></div>
                    </div>

                    <div class="doc-card">
                        <div class="doc-line gold"></div>
                        <div class="doc-line"></div>
                        <div class="doc-line short"></div>
                    </div>

                </div>

            </div>

            {{-- Upload Form --}}
            <div class="upload-form">

                <h2>Upload Document</h2>

                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
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

                <form action="{{ route('documents.store') }}"
                      method="POST"
                      enctype="multipart/form-data">

                    @csrf

                    <div class="form-group">

                        <label for="title">
                            Document Title
                        </label>

                        <input type="text"
                               name="title"
                               id="title"
                               value="{{ old('title') }}"
                               placeholder="Enter document title"
                               required>

                    </div>

                    <div class="form-group">

                        <label>
                            Approval Workflow
                        </label>

                        <div id="approver-container">

                            {{-- STEP 1 --}}
                            <div class="approver-row">

                                <div class="approver-step">
                                    Step 1
                                </div>

                                <select name="approvers[]" required>

                                    <option value="">
                                        Select Approver
                                    </option>

                                    @foreach(\App\Models\User::where('role', 'approver')->get() as $approver)

                                        <option value="{{ $approver->id }}">

                                            {{ $approver->name }}

                                        </option>

                                    @endforeach

                                </select>

                                <button type="button"
                                        class="remove-approver"
                                        onclick="removeApprover(this)">

                                    ✕

                                </button>

                            </div>

                        </div>

                        {{-- ADD BUTTON --}}
                        <button type="button"
                                class="add-approver-btn"
                                onclick="addApprover()">

                            + Add Another Approver

                        </button>

                        <small class="workflow-note">

                            Approval follows the order above.
                            The first approver signs first,
                            followed by the next approver.

                        </small>

                    </div>
                    <div class="form-group">
                        <label for="file">
                            Upload PDF
                        </label>

                        <input type="file"
                            name="file"
                            id="file"
                            accept="application/pdf"
                            required>
                    </div>
                    <button type="submit" class="btn-submit">

                        <i class="ti ti-upload"></i>
                        Upload Document

                    </button>

                </form>

            </div>

        @endif


        {{-- =========================================
             MY DOCUMENTS
        ========================================== --}}
        @if($section === 'documents')

            <div class="section-wrap">

                <div class="hero-eyebrow">

                    <div class="hero-eyebrow-line"></div>

                    <span class="hero-eyebrow-text">
                        My Documents
                    </span>

                </div>

                <h2 class="section-title">
                    All Uploaded Documents
                </h2>

                @if($documents->count())

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

                                    <td class="mono">
                                        {{ $doc->created_at->format('M d, Y h:i A') }}
                                    </td>

                                    <td class="mono">
                                        {{ ucfirst($doc->status ?? 'pending') }}
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                @else

                    <p class="empty-state">
                        No documents uploaded yet.
                    </p>

                @endif

            </div>

        @endif


        {{-- =========================================
             SIGNED DOCUMENTS
        ========================================== --}}
        @if($section === 'signed')

            <div class="section-wrap">

                <div class="hero-eyebrow">

                    <div class="hero-eyebrow-line"></div>

                    <span class="hero-eyebrow-text">
                        Completed Documents
                    </span>

                </div>

                <h2 class="section-title">
                    Signed Documents
                </h2>

                @if($signedDocuments->count())

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
                                        {{ $doc->admin_signed_at
                                            ? $doc->admin_signed_at->format('M d, Y h:i A')
                                            : 'N/A' }}
                                    </td>

                                    <td>
                                        <a href="{{ asset('storage/' . $doc->signed_file_path) }}"
                                           target="_blank">

                                            Download

                                        </a>
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                @else

                    <p class="empty-state">
                        No signed documents yet.
                    </p>

                @endif

            </div>

        @endif

    </div>

</div>

<script>

const approverOptions = `
    @foreach(\App\Models\User::where('role', 'approver')->get() as $approver)

        <option value="{{ $approver->id }}">
            {{ $approver->name }}
        </option>

    @endforeach
`;

</script>
@endsection
