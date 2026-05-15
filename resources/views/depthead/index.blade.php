@extends('layouts.app')

@section('title', ucfirst(auth()->user()->role) . ' Dashboard')

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
            <button class="nav-btn" onclick="showSection('approvals')">
                Pending Approvals
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

        {{-- Pending Approvals --}}
        <div id="section-approvals" class="dashboard-section">

            <h1 class="section-title">
                Pending Approvals
            </h1>

            @if($approvals->count())

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
                                <td>
                                    {{ $approval->document->title ?? 'Missing' }}
                                </td>

                                <td>
                                    {{ $approval->document->uploader->name ?? 'Missing' }}
                                </td>

                                <td>
                                    {{ ucfirst($approval->status) }}
                                </td>

                                <td>
                                    @if($approval->status === 'pending')
                                        <button class="btn-sign"
                                            onclick="checkSignatureAndOpenModal(
                                                {{ auth()->user()->signature_path ? 'true' : 'false' }},
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

                <p class="no-data">
                    No pending approvals.
                </p>

            @endif

        </div>

        {{-- Signed Documents --}}
        <div id="section-signed" class="dashboard-section">

            <h1 class="section-title">
                Signed Documents
            </h1>

            @php
                $signedDocs = \App\Models\Document::whereNotNull('signed_file_path')
                    ->latest('updated_at')
                    ->get();
            @endphp

            @if($signedDocs->count())

                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Download</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($signedDocs as $doc)
                            <tr>
                                <td>{{ $doc->title }}</td>

                                <td>{{ ucfirst($doc->status) }}</td>

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

                    <form method="POST"
                        action="{{ route('signature.remove') }}"
                        onsubmit="return confirm('Are you sure you want to remove your signature?')">

                        @csrf
                        @method('DELETE')

                        <button type="submit" class="btn-remove-signature">
                            Remove Signature
                        </button>

                    </form>

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

{{-- Signature Modal --}}
<div class="modal-overlay" id="signModal">

    <div class="modal-box">

        <div class="modal-header">
            <h3>Place Signature</h3>

            <button onclick="closeSignModal()" class="modal-close">
                ×
            </button>
        </div>

        <div class="page-selector">
            <label for="sigPageInput">Page:</label>

            <input type="number"
                   id="sigPageInput"
                   value="1"
                   min="1">
        </div>

        <div class="pdf-wrapper" id="pdfWrapper">

            <iframe class="pdf-frame"
                    id="pdfFrame"></iframe>

            <div class="pdf-click-layer"
                 id="pdfClickLayer"></div>

            <div class="sig-ghost"
                 id="sigGhost">

                ✍ {{ auth()->user()->name }}

            </div>

        </div>

        <div class="modal-footer">

            <button class="btn-cancel"
                    onclick="closeSignModal()">
                Cancel
            </button>

            <span class="hint" id="hintText">
                Click anywhere on the document to place your signature
            </span>

            <button class="btn-confirm"
                    id="btnConfirm"
                    onclick="submitSignature()"
                    disabled>
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

@endsection