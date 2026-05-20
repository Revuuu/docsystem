@extends('layouts.app')

@section('title', 'Staff Dashboard')

@section('content')

{{-- Hamburger Menu --}}
    @include('partials.hamburger')

<div class="main-container">

    {{-- Sidebar --}}
    @include('partials.sidebar')

    {{-- Content --}}
    <div class="content">

        {{-- Upload Section --}}
        @include('partials.upload-section')
        {{-- Pending Approvals --}}
        @include('partials.approvals-section')

        {{-- My Documents --}}
        <div id="section-documents" class="dashboard-section">

            <h1 class="section-title">
                My Documents
            </h1>

            @php
                $myDocuments = $documents;
            @endphp

            @if($myDocuments->count())

                <table>

                    <thead>

                        <tr>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Uploaded</th>
                            <th>Remarks</th>
                            <th>File</th>
                        </tr>

                    </thead>

                    <tbody>

                        @foreach($myDocuments as $doc)

                            <tr>

                                <td>
                                    {{ $doc->title }}
                                </td>

                                <td>

    @php

        $firstApproval = $doc->approvals()
            ->orderBy('step_order')
            ->first();

        $lastApproval = $doc->approvals()
            ->whereNotNull('completed_at')
            ->orderByDesc('step_order')
            ->first();

        $currentPendingApproval = $doc->approvals()
            ->where('status', 'pending')
            ->first();

    @endphp

    {{-- REJECTED --}}
@if($doc->status === 'rejected')

    <span class="status-rejected">
        Rejected
    </span>

{{-- PENDING --}}
@elseif(
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

{{-- DEFAULT --}}
@else

    <span class="status-upcoming">
        {{ ucfirst($doc->status) }}
    </span>

@endif

</td>

                                <td>
                                    {{ $doc->created_at->format('M d, Y h:i A') }}
                                </td>

                                <td> 
                                    @php
                                        $rejectedApproval = \App\Models\Approval::where('document_id', $doc->id)
                                            ->where('status', 'rejected')
                                            ->latest('rejected_at')
                                            ->first();
                                    @endphp

                                    {{ $rejectedApproval->remarks ?? '-' }}
                                </td>

                               <td>
    @php
        $viewPath = $doc->current_file_path;
    @endphp

    <button type="button" class="view-pdf-btn"
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
                    No uploaded documents yet.
                </p>

            @endif

        </div>

        {{-- My Signature --}}
        <div id="section-signature" class="dashboard-section">

            <h1 class="section-title">
                My Signature
            </h1>

          @if($errors->signature->any())

    <div class="alert alert-error">

        {{ $errors->signature->first('password') }}

    </div>

@endif

            @if(auth()->user()->signature_path)
                <div class="signature-preview-card">

                    <h3>Current Signature</h3>

                    <img src="{{ asset('storage/' . auth()->user()->signature_path) }}"
                        class="signature-preview"
                        alt="Signature">

                 <form id="removeSignatureForm"
      method="POST"
      action="{{ route('signature.remove') }}"
         onsubmit="return openPasswordModal(event, this)">

    @csrf
    @method('DELETE')

    <input type="hidden"
           name="password"
           class="password-hidden-input">

    <button type="button"
            class="btn-remove-signature"
          >


        Remove Signature

    </button>

</form>

                </div>
            @endif

            <div class="signature-grid">

                <div class="form-card">
                    <h3>Upload Signature Image</h3>
<form id="uploadSignatureForm"
      method="POST"
      action="{{ route('signature.upload') }}"
      enctype="multipart/form-data"
      class="user-form"
      onsubmit="return openPasswordModal(event, this)"
      >

    @csrf

                        <div class="form-group">
                            <label>Signature Image</label>

                            <input type="file"
                                name="signature"
                                accept="image/*"
                                required>
                        </div>
             <input type="hidden"
       name="password"
       class="password-hidden-input">
                <button type="button"
            class="btn-submit"
          >


    Upload Signature

</button>

                    </form>
                </div>

                <div class="form-card">
                    <h3>Draw Signature</h3>

                    <form id="drawSignatureForm" method="POST"
    action="{{ route('signature.draw') }}"
   
    
    class="user-form"  onsubmit="
        saveDrawnSignature();
        return openPasswordModal(event, this);
    ">

                        @csrf

                        <canvas id="signatureCanvas"
                                width="500"
                                height="200"
                                class="signature-canvas"></canvas>

                        <input type="hidden"
                            name="signature_data"
                            id="signatureData">
    <input type="hidden"
           name="password"
           class="password-hidden-input">
                        <button type="button"
                                class="btn-clear-signature"
                                onclick="clearSignatureCanvas()">
                            Clear
                        </button>
                    
                       <button type="button"
            class="btn-submit"
            onclick="
                saveDrawnSignature();
                openPasswordModal(
                    document.getElementById('drawSignatureForm')
                );
            ">
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

<form id="signatureForm" method="POST" action="">
    @csrf

    <input type="hidden" name="sig_x" id="formSigX">
    <input type="hidden" name="sig_y" id="formSigY">
    <input type="hidden" name="sig_w" id="formSigW">
    <input type="hidden" name="sig_h" id="formSigH">
    <input type="hidden" name="sig_page" id="formSigPage">
</form>

@include('partials.password-modal')
@endsection