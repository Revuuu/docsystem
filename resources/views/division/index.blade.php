@extends('layouts.app')

@section('title', ucfirst(auth()->user()->role) . ' Dashboard')

@section('content')

{{-- Hamburger Menu --}}
    @include('partials.hamburger')
    
<div class="main-container">

    {{-- Sidebar --}}
    @include('partials.sidebar')

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
        {{-- Pending Approvals --}}
        <div id="section-approvals" class="dashboard-section">

            <h1 class="section-title">
                Pending Approvals
            </h1>

            @if ($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

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

   
    {{-- UPCOMING --}}
    @if($approval->status === 'waiting')

        <span class="status-upcoming">
            Upcoming
        </span>

    {{-- PENDING / IN PROGRESS --}}
    @elseif($approval->status === 'pending')

        <span class="status-pending">
            Pending
        </span>

        <br>

        <small class="status-time">
  Pending for

          {{
                $approval->received_at
                    ? $approval->received_at->diffForHumans(now(), true)
                    : 'Just now'
            }}

        </small>


    {{-- APPROVED --}}


    @elseif($approval->status === 'approved')

        <span class="status-approved">
            Approved
        </span>

         <small class="status-time">

            Completed in

            {{ $approval->duration_seconds
                ? gmdate('H:i:s', (int) $approval->duration_seconds)
                : 'N/A'
            }}

        </small>

    @elseif($approval->status === 'rejected')

        <span class="status-rejected">
            Rejected
        </span>

    @else

        {{ ucfirst($approval->status) }}

    @endif

</td>
                                <td>
                                    @if($approval->status === 'pending')

                                        <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">

                                            {{-- APPROVE --}}
                                            <button class="btn-sign"
                                                onclick="checkSignatureAndOpenModal(
                                                    {{ auth()->user()->signature_path ? 'true' : 'false' }},
                                                    {{ $approval->id }},
                                                    '{{ asset('storage/' . ($approval->document->signed_file_path ?? $approval->document->file_path)) }}',
                                                    '{{ route('approvals.approve', $approval->id) }}'
                                                )">

                                                ✔ Approve & Sign

                                            </button>

                                            {{-- REJECT --}}
                                            <button type="button"
                                                    class="btn-reject"
                                                    onclick="openRejectModal({{ $approval->id }})">

                                                Reject

                                            </button>

                                        </div>

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

        {{-- My Documents --}}
        <div id="section-documents" class="dashboard-section">

            <h1 class="section-title">
                My Documents
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
            <th>Remarks</th>
            <th>Download</th>
        </tr>
    </thead>

    <tbody>

        @foreach($signedDocs as $doc)

            @php

                $firstApproval = $doc->approvals
                    ->sortBy('step_order')
                    ->first();

                $lastApproval = $doc->approvals
                    ->whereNotNull('completed_at')
                    ->sortByDesc('step_order')
                    ->first();

                $currentPendingApproval = $doc->approvals
                    ->where('status', 'pending')
                    ->first();

            @endphp

            <tr>

                <td>
                    {{ $doc->title }}
                </td>

                <td>

                    {{-- PENDING / IN PROGRESS --}}
                    @if(
                        in_array($doc->status, ['pending', 'in_progress']) &&
                        $currentPendingApproval &&
                        $currentPendingApproval->received_at
                    )

                        <span class="status-pending">
                            Pending
                        </span>

                        <br>

                        <small class="status-time">

                            Pending for

                            {{
                                $currentPendingApproval->received_at
                                    ->diffForHumans(now(), true)
                            }}

                        </small>

                    {{-- APPROVED --}}
                    @elseif(
                        $doc->status === 'approved' &&
                        $firstApproval &&
                        $firstApproval->received_at &&
                        $lastApproval &&
                        $lastApproval->completed_at
                    )

                        <span class="status-approved">
                            Approved
                        </span>

                        <br>

                        <small class="status-time">

                            Workflow completed in

                            {{
                                \Carbon\CarbonInterval::seconds(
                                    (int) $firstApproval->received_at
                                        ->diffInSeconds($lastApproval->completed_at)
                                )->cascade()->forHumans()
                            }}

                        </small>

                    {{-- REJECTED --}}
                    @elseif($doc->status === 'rejected')

                        <span class="status-rejected">
                            Rejected
                        </span>

                    {{-- DEFAULT --}}
                    @else

                        <span class="status-upcoming">
                            {{ ucfirst($doc->status) }}
                        </span>

                    @endif

                </td>

                <td>
                    {{ $doc->remarks ?? '-' }}
                </td>

                <td>

                    @php
                        $viewPath = $doc->signed_file_path ?? $doc->file_path;
                    @endphp

                    <button type="button"
                            class="view-pdf-btn"
                            onclick="openPdfModal('{{ asset('storage/' . $viewPath) }}')">

                        View PDF

                    </button>

                </td>

            </tr>

        @endforeach
                        <!-- PDF Modal -->
<div id="pdfModal" class="pdf-modal">
    <div class="pdf-modal-content">

        <span class="close-modal" onclick="closePdfModal()">
            &times;
        </span>

        <iframe id="pdfFrame"
            src=""
            width="100%"
            height="100%">
        </iframe>

    </div>
</div>
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

            <canvas id="pdfCanvas"></canvas>

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
{{-- Reject Modal --}}
<div class="modal-overlay"
     id="rejectModal"
     style="display:none;">

    <div class="modal-box">

        <div class="modal-header">

            <h3>
                Reject Document
            </h3>

            <button onclick="closeRejectModal()"
                    class="modal-close">

                ×

            </button>

        </div>

        <form method="POST"
              id="rejectForm">

            @csrf

            <div class="form-group">

                <label>
                    Reason for rejection
                </label>

                <textarea
                    name="remarks"
                    rows="5"
                    required
                    style="width:100%;
                           padding:12px;
                           border:1px solid #ccc;
                           border-radius:8px;"></textarea>

            </div>

            <div class="modal-footer">

                <button type="button"
                        class="btn-cancel"
                        onclick="closeRejectModal()">

                    Cancel

                </button>

                <button type="submit"
                        class="btn-reject">

                    Reject

                </button>

            </div>

        </form>

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