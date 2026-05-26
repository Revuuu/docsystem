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

        {{-- Pending Approvals --}}
        <!-- @include('partials.approvals-section') -->

        {{-- My Documents --}}

        @php
            $myDocuments = $documents;
        @endphp

        <div id="section-documents" class="dashboard-section">

    <!-- DOCUMENT CARD -->
    <div class="documents-card">

        <!-- CARD HEADER -->
        <div class="documents-card-header">

            <div class="documents-tools">

                
                <div class="search-box">

                    <input type="text"
                            id="documentSearchInput"
                            placeholder="Search by file name...">

                </div>

                <select id="documentStatusFilter" class="documentStatusFilter">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>

                <button class="upload-document-btn"
        type="button"
        onclick="openUploadModal()">

    ＋ UPLOAD NEW DOCUMENT

</button>

            </div>

        </div>

        <!-- TABLE -->
        <div class="table-wrapper">

            <table class="modern-docs-table">

                <thead>

                    <tr>
                        <th>ID</th>
                        <th>File name</th>
                        <th>Uploaded By</th>
                        <th>file_path</th>
                        <th>Timestamp</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>

                </thead>

                <tbody>

                    @foreach($myDocuments as $index => $doc)

                    <tr class="document-row"
                            data-title="{{ strtolower($doc->title) }}"
                            data-file="{{ strtolower($doc->latestVersion() ? basename($doc->latestVersion()->generated_storage_path) : '') }}"
                            data-status="{{ strtolower($doc->status) }}">

                        <!-- ID -->
                        <td>

                            <span class="doc-id-pill">
                                {{ 100 + $index }}
                            </span>

                        </td>
                        <!-- FILE NAME -->
                        <td>

                            <div class="file-name">
                                {{ $doc->title }}
                            </div>

                        </td>
                        <!-- USER -->
                        <td>

                            <div class="table-user">

                                <div class="table-user-avatar">
                                    {{ strtoupper(substr($doc->uploader->name ?? 'U',0,1)) }}
                                </div>

                                <div>

                                    <strong>
                                        {{ $doc->uploader->name ?? 'Unknown' }}
                                    </strong>

                                    <small>
                                        {{ ucfirst($doc->uploader->role ?? '-') }}
                                    </small>

                                </div>

                            </div>

                        </td>

                        <!-- FILE -->
                        <td>

                            <div class="file-name">
                                {{ $doc->latestVersion()
                                    ? basename($doc->latestVersion()->generated_storage_path)
                                    : 'No file'
                                }}
                            </div>
                            
                        </td>

                        <!-- DATE -->
                        <td>

                            {{ $doc->created_at->format('M d, Y') }}

                            <br>

                            <small>
                                {{ $doc->created_at->format('h:i A') }}
                            </small>

                        </td>

                        <!-- STATUS -->
                        <td>

                            @if($doc->status === 'approved')

                                <span class="status-pill success">
                                    Fully Signed
                                </span>

                            @elseif($doc->status === 'in_progress')

                                <span class="status-pill ongoing">
                                    Ongoing
                                </span>

                            @elseif($doc->status === 'pending')

                                <span class="status-pill waiting">
                                    Awaiting Signature
                                </span>

                            @else

                                <span class="status-pill neutral">
                                    {{ ucfirst($doc->status) }}
                                </span>

                            @endif

                        </td>

                        <!-- ACTION -->
                        <td>

                            <div class="more-menu">
                                <button type="button" class="table-action-btn">
                                    More ⋮
                                </button>

                               <div class="more-menu-dropdown">
                                     <a href="{{ route('files.view', encrypt($doc->latestVersion()->id)) }}"
                                    target="_blank">
                                        View PDF
                                    </a>

                                    <a href="{{ route('files.download', encrypt($doc->latestVersion()->id)) }}">
                                        Download PDF
                                    </a>
                                </div>
                            </div>

                        </td>

                    </tr>

                    @endforeach
                    
                </tbody>

            </table>
            <div id="noResultsMessage"
                style="display:none; text-align:center; padding:20px; color:#777;">
                No results found.
            </div>
        </div>

        <!-- FOOTER -->
        <div class="documents-footer">

            <div class="table-pagination">
                Table foos:
                <span class="active">1</span>
                <span>2</span>
                <span>3</span>
                ...
                Next →
            </div>

        </div>

    </div>

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

                    <img src="{{ route(
    'signatures.view',
    encrypt(auth()->id())
) }}"
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

    <button type="submit"
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
                   <button type="submit"
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

                        <button type="button"
                                class="btn-clear-signature"
                                onclick="clearSignatureCanvas()">
                            Clear
                        </button>
                        
                        <input type="hidden"
       name="password"
       class="password-hidden-input">
                        <button type="submit"
            class="btn-submit">
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
@include('partials.upload-section')
<form id="signatureForm" method="POST" action="">
    @csrf

    <input type="hidden" name="sig_x" id="formSigX">
    <input type="hidden" name="sig_y" id="formSigY">
    <input type="hidden" name="sig_w" id="formSigW">
    <input type="hidden" name="sig_h" id="formSigH">
    <input type="hidden" name="sig_page" id="formSigPage">
</form>
@if(session('uploaded_signature_base64'))

<script>

    console.log(
        'UPLOADED SIGNATURE BASE64:'
    );

    console.log(
        @json(
            session('uploaded_signature_base64')
        )
    );

</script>

@endif

@if(session('drawn_signature_base64'))

<script>

    console.log(
        'DRAWN SIGNATURE BASE64:'
    );

    console.log(
        @json(
            session('drawn_signature_base64')
        )
    );

</script>

@endif
@include('partials.password-modal')

<script>

function openUploadModal()
{
    document
        .getElementById('uploadModal')
        .classList
        .add('show');
}

function closeUploadModal()
{
    document
        .getElementById('uploadModal')
        .classList
        .remove('show');
}

window.onclick = function(event)
{
    const modal =
        document.getElementById('uploadModal');

    if(event.target === modal)
    {
        closeUploadModal();
    }
}

</script>
@endsection