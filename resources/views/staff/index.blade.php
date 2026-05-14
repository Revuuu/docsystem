@extends('layouts.app')

@section('title', 'Staff Dashboard')

@section('content')

<div class="main-container">

    {{-- Sidebar --}}
    <div class="sidebar">

        <div class="sidebar-logo">
            DOCSYSTEM
        </div>

        <div class="sidebar-user">

            <div class="user-avatar">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </div>

            <h2>{{ auth()->user()->name }}</h2>

            <span class="user-role-badge">
                {{ ucfirst(auth()->user()->role) }}
            </span>

        </div>

        <nav class="sidebar-nav">
            <button class="nav-btn" onclick="showSection('upload')">
                Upload Document
            </button>

            <button class="nav-btn" onclick="showSection('documents')">
                My Documents
            </button>

            <button class="nav-btn" onclick="showSection('signed')">
                Signed Documents
            </button>

            <button class="nav-btn" onclick="showSection('signature')">
                My Signature
            </button>
        </nav>

        <div class="logout-wrap">

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button type="submit" class="btn-logout">
                    Logout
                </button>
            </form>

        </div>

    </div>

    {{-- Content --}}
    <div class="content">

        {{-- Upload Section --}}
        <div id="section-upload" class="dashboard-section">

            <h1 class="section-title">
                Upload Document
            </h1>

            <form method="POST"
                  action="{{ route('documents.store') }}"
                  enctype="multipart/form-data"
                  class="upload-form">

                @csrf

                <div class="form-group">

                    <label>
                        Document Title
                    </label>

                    <input type="text"
                           name="title"
                           required>

                </div>

                <div class="form-group">

                    <label>
                        PDF File
                    </label>

                    <input type="file"
                           name="file"
                           accept=".pdf"
                           required>

                </div>

                <div class="form-group">

                    <label>Select Approvers</label>

                    <div id="approver-container">

                        <div class="approver-row">

                            <select name="approvers[]" required>

                                <option value="">
                                    -- Select Approver --
                                </option>

                                @foreach($approvers as $approver)

                                    <option value="{{ $approver->id }}">

                                        {{ $approver->name }}
                                        ({{ ucfirst($approver->role) }})

                                    </option>

                                @endforeach

                            </select>

                        </div>

                    </div>

                    <button type="button"
                            class="btn-add-approver"
                            onclick="addApprover()">

                        + Add Another Approver

                    </button>

                </div>

                <button type="submit" class="btn-submit">
                    Upload Document
                </button>

            </form>

        </div>

        {{-- My Documents --}}
        <div id="section-documents" class="dashboard-section">

            <h1 class="section-title">
                My Documents
            </h1>

            @if($documents->count())

                <table>

                    <thead>

                        <tr>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Uploaded</th>
                            <th>File</th>
                        </tr>

                    </thead>

                    <tbody>

                        @foreach($documents as $document)

                            <tr>

                                <td>
                                    {{ $document->title }}
                                </td>

                                <td>
                                    {{ ucfirst($document->status) }}
                                </td>

                                <td>
                                    {{ $document->created_at->format('M d, Y h:i A') }}
                                </td>

                                <td>

                                    <a href="{{ asset('storage/' . $document->file_path) }}"
                                       target="_blank">

                                        View PDF

                                    </a>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            @else

                <p class="no-data">
                    No uploaded documents yet.
                </p>

            @endif

        </div>

        {{-- Signed Documents --}}
        <div id="section-signed" class="dashboard-section">

            <h1 class="section-title">
                Signed Documents
            </h1>

            @if($signedDocuments->count())

                <table>

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

                                <td>
                                    {{ $doc->title }}
                                </td>

                                <td>

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

                <p class="no-data">
                    No signed documents yet.
                </p>

            @endif

        </div>

        {{-- My Signature --}}
        <div id="section-signature" class="dashboard-section">

            <h1 class="section-title">
                My Signature
            </h1>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(auth()->user()->signature_path)
                <div class="signature-preview-card">
                    <h3>Current Signature</h3>

                    <img src="{{ asset('storage/' . auth()->user()->signature_path) }}"
                        class="signature-preview"
                        alt="Signature">
                </div>
            @endif

            <div class="signature-grid">

                <div class="form-card">
                    <h3>Upload Signature Image</h3>

                    <form method="POST"
                        action="{{ route('signature.upload') }}"
                        enctype="multipart/form-data"
                        class="user-form">

                        @csrf

                        <div class="form-group">
                            <label>Signature Image</label>

                            <input type="file"
                                name="signature"
                                accept="image/*"
                                required>
                        </div>

                        <button type="submit" class="btn-submit">
                            Upload Signature
                        </button>

                    </form>
                </div>

                <div class="form-card">
                    <h3>Draw Signature</h3>

                    <form method="POST"
                        action="{{ route('signature.draw') }}"
                        onsubmit="saveDrawnSignature()"
                        class="user-form">

                        @csrf

                        <canvas id="signatureCanvas"
                                width="500"
                                height="200"
                                class="signature-canvas"></canvas>

                        <input type="hidden"
                            name="signature_data"
                            id="signatureData">

                        <button type="button"
                                class="btn-clear-signature"
                                onclick="clearSignatureCanvas()">
                            Clear
                        </button>

                        <button type="submit" class="btn-submit">
                            Save Drawn Signature
                        </button>

                    </form>
                </div>

            </div>

        </div>

    </div>

</div>



@endsection