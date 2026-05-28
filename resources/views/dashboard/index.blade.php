@extends('layouts.app')

@section('title', ucfirst(auth()->user()->roles->first()?->name ?? auth()->user()->role) . ' Dashboard')

@section('content')

{{-- Hamburger Menu --}}
@include('partials.hamburger')

<div class="main-container">

    {{-- Sidebar --}}
    @include('partials.sidebar')

    {{-- Content --}}
    <div class="content">

        {{-- Upload Section / Upload Modal --}}
        @include('partials.upload-section')
        @if ($errors->any())
            <div class="alert alert-danger" style="margin: 20px;">
                {{ $errors->first() }}
            </div>
        @endif
        @php
            use Illuminate\Pagination\LengthAwarePaginator;

            $search = strtolower(request('search', ''));
            $status = strtolower(request('status', ''));

            $assignedDocuments = $approvals
                ->pluck('document')
                ->filter();

            $mergedDocuments = $documents
                ->merge($assignedDocuments)
                ->unique('id')
                ->filter(function ($doc) use ($search, $status) {

                    $latestVersion = $doc->latestVersion();

                    $title = strtolower($doc->title ?? '');

                    $file = strtolower(
                        $latestVersion
                            ? basename($latestVersion->generated_storage_path)
                            : ''
                    );

                    $myApproval = $doc->approvals
                        ->where('user_id', auth()->id())
                        ->sortBy('step_order')
                        ->first();

                    $docStatus = strtolower(
                        $myApproval?->status ?? $doc->status
                    );

                    $matchesSearch =
                        empty($search) ||
                        str_contains($title, $search) ||
                        str_contains($file, $search);

                    $matchesStatus =
                        empty($status) ||
                        $docStatus === $status;

                    return $matchesSearch && $matchesStatus;
                })
                ->sortByDesc('created_at')
                ->values();

            $perPage = 10;

            $currentPage = LengthAwarePaginator::resolveCurrentPage();

            $currentItems = $mergedDocuments
                ->slice(($currentPage - 1) * $perPage, $perPage)
                ->values();

            $myDocuments = new LengthAwarePaginator(
                $currentItems,
                $mergedDocuments->count(),
                $perPage,
                $currentPage,
                [
                    'path' => request()->url(),
                    'query' => request()->query(),
                ]
            );
        @endphp

{{-- DASHBOARD SECTION --}}
<div id="section-dashboard"
     class="dashboard-section">

    @php

        $role =
            auth()->user()->roles->first()?->name
            ?? auth()->user()->role;

        $dashboardTitle = match($role) {

            'staff' => 'Staff Dashboard',

            'supervisor' => 'Supervisor Dashboard',

            'depthead' => 'Department Head Dashboard',

            'division' => 'Division Head Dashboard',

            'executive' => 'Executive Dashboard',

            default => 'Dashboard',
        };

        $dashboardSubtitle = match($role) {

            'staff' =>
                'Document workflow overview and approvals',

            'supervisor' =>
                'Department workflow monitoring and approvals',

            'depthead' =>
                'Department oversight and document validation',

            'division' =>
                'Division-wide approval management',

            'executive' =>
                'Executive approval and compliance overview',

            default =>
                'Workflow dashboard',
        };

    @endphp

    @include('dashboard.staff')

</div>




        {{-- Documents --}}
        <div id="section-documents" class="dashboard-section">

            <div class="documents-card">

                <div class="documents-card-header">
                    <div class="documents-tools">
                        <div class="search-box">
                            <input type="text"
                                id="documentSearchInput"
                                value="{{ request('search') }}"
                                placeholder="Search by file name...">
                        </div>

                        <select id="documentStatusFilter" class="documentStatusFilter">

                            <option value="" {{ request('status') == '' ? 'selected' : '' }}>
                                All Status
                            </option>

                            <option value="waiting" {{ request('status') == 'waiting' ? 'selected' : '' }}>
                                Upcoming
                            </option>

                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                                Pending
                            </option>

                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>
                                Approved
                            </option>

                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>
                                Rejected
                            </option>

                        </select>

                        <button class="upload-document-btn" type="button" onclick="openUploadModal()">
                            ＋ UPLOAD NEW DOCUMENT
                        </button>
                    </div>
                </div>

                <div id="documentsTableContainer">
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

                            <tbody id="documentsTableBody">
                                @foreach($myDocuments as $index => $doc)

                                    @php
                                        $latestVersion = $doc->latestVersion();

                                        $myApproval = $doc->approvals
                                            ->where('user_id', auth()->id())
                                            ->sortBy('step_order')
                                            ->first();

                                        $myPendingApproval = $doc->approvals
                                            ->where('user_id', auth()->id())
                                            ->where('status', 'pending')
                                            ->first();

                                        $signingFileId = $doc->current_signed_file?->id
                                            ?? $latestVersion?->id;
                                    @endphp

                                    <tr class="document-row"
                                        data-title="{{ strtolower($doc->title) }}"
                                        data-file="{{ strtolower($latestVersion ? basename($latestVersion->generated_storage_path) : '') }}"
                                        data-status="{{ strtolower($myApproval?->status ?? $doc->status) }}">

                                        <td>
                                            <span class="doc-id-pill">
                                                {{ $doc->id }}
                                            </span>
                                        </td>

                                        <td>
                                            <div class="file-name">
                                                {{ $doc->title }}
                                            </div>
                                        </td>

                                        <td>
                                            <div class="table-user">
                                                <div class="table-user-avatar">
                                                    {{ strtoupper(substr($doc->uploader->name ?? 'U', 0, 1)) }}
                                                </div>

                                                <div>
                                                    <strong>{{ $doc->uploader->name ?? 'Unknown' }}</strong>
                                                    <small>{{ ucfirst($doc->uploader->role ?? '-') }}</small>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <div class="file-name">
                                                {{ $latestVersion ? basename($latestVersion->generated_storage_path) : 'No file' }}
                                            </div>
                                        </td>

                                        <td>
                                            {{ $doc->created_at->format('M d, Y') }}
                                            <br>
                                            <small>{{ $doc->created_at->format('h:i A') }}</small>
                                        </td>

                                        <td>
                                            @php
                                                $approvedApprovals = $doc->approvals
                                                    ->where('status', 'approved');

                                                $pendingApproval = $doc->approvals
                                                    ->where('status', 'pending')
                                                    ->sortBy('step_order')
                                                    ->first();

                                                $waitingApproval = $doc->approvals
                                                    ->where('status', 'waiting')
                                                    ->sortBy('step_order')
                                                    ->first();

                                                $rejectedApproval = $doc->approvals
                                                    ->where('status', 'rejected')
                                                    ->first();
                                            @endphp

                                            {{-- If current user already approved --}}
                                            @if($myApproval?->status === 'approved')
                                                <span class="status-pill success">
                                                    Approved
                                                </span>

                                            {{-- If current user is the active signer --}}
                                            @elseif($myApproval?->status === 'pending')
                                                <span class="status-pill waiting">
                                                    Pending

                                                    <span class="pending-approver">
                                                        Current Signatory: {{ auth()->user()->name }}
                                                    </span>
                                                </span>

                                            {{-- If current user is waiting for their turn --}}
                                            @elseif($myApproval?->status === 'waiting')
                                                <span class="status-pill ongoing">
                                                    Upcoming

                                                    <span class="pending-approver">
                                                        Waiting For:
                                                        {{
                                                            $doc->approvals
                                                                ->where('status', 'pending')
                                                                ->first()?->user?->name
                                                            ?? 'Unknown approver'
                                                        }}
                                                    </span>
                                                </span>

                                            {{-- Fallback for uploaded documents --}}
                                            @else

                                                {{-- Document fully approved --}}
                                                @if($doc->status === 'approved')
                                                    <span class="status-pill success">
                                                        Approved
                                                    </span>

                                                {{-- Document rejected --}}
                                                @elseif($doc->status === 'rejected')
                                                    <span class="status-pill rejected">
                                                        Rejected
                                                    </span>

                                                {{-- Document still ongoing --}}
                                                @else

                                                    @php
                                                        $currentPendingApproval = $doc->approvals
                                                            ->where('status', 'pending')
                                                            ->first();
                                                    @endphp

                                                    <span class="status-pill waiting">
                                                        Pending

                                                        <span class="pending-approver">
                                                            Current Signatory:
                                                            {{ $currentPendingApproval?->user?->name ?? 'Unknown approver' }}
                                                        </span>
                                                    </span>

                                                @endif

                                            @endif
                                        </td>

                                        <td>
                                            <div class="more-menu">
                                                <button type="button"
                                                        class="table-action-btn"
                                                        onclick="toggleMoreMenu(event, this)">
                                                    More ⋮
                                                </button>

                                                <div class="more-menu-dropdown">

                                                    @if($myPendingApproval && $signingFileId)
                                                        <button type="button"
                                                                class="btn-sign"
                                                                onclick="checkSignatureAndOpenModal(
                                                                    {{ auth()->user()->signature_path ? 'true' : 'false' }},
                                                                    {{ $myPendingApproval->id }},
                                                                    '{{ route('files.view', encrypt($signingFileId)) }}',
                                                                    '{{ route('approvals.approve', $myPendingApproval->id) }}'
                                                                )">
                                                            ✔ Approve & Sign
                                                        </button>

                                                        <button type="button"
                                                                class="btn-reject"
                                                                onclick="openRejectModal({{ $myPendingApproval->id }})">
                                                            Reject
                                                        </button>
                                                    @endif

                                                    @if($latestVersion)
                                                        <button type="button"
                                                                onclick="openPdfModal('{{ route('files.view', encrypt($latestVersion->id)) }}')">
                                                            View PDF
                                                        </button>

                                                        <a href="{{ route('files.download', encrypt($latestVersion->id)) }}">
                                                            Download PDF
                                                        </a>
                                                    @endif

                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div id="noResultsMessage" style="display:none; text-align:center; padding:20px; color:#777;">
                            No results found.
                        </div>
                    </div>
                
                    <div class="documents-footer">

                        <div class="table-pagination">

                            {{ $myDocuments->appends([
                                'section' => 'documents'
                            ])->links() }}

                        </div>

                    </div>
                </div>
            </div>
        </div>

        {{-- Profile --}}
        <div id="section-profile" class="dashboard-section">
            <div class="documents-card">
                <div class="documents-card-header">
                    <h2>My Profile</h2>
                </div>

                <div class="upload-modal-body" style="padding:24px;">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST"
                          action="{{ route('profile.update') }}"
                          class="upload-form">
                        @csrf
                        @method('PATCH')

                        <div class="form-group">
                            <label>Name</label>
                            <input type="text"
                                   name="name"
                                   value="{{ auth()->user()->name }}"
                                   required>
                        </div>

                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password"
                                   name="password"
                                   placeholder="Leave blank if unchanged"
                                   autocomplete="new-password">
                        </div>

                        <div class="form-group">
                            <label>Confirm Password</label>
                            <input type="password"
                                   name="password_confirmation"
                                   placeholder="Confirm new password"
                                   autocomplete="new-password">
                        </div>

                        <button type="button"
                                class="btn-submit"
                                onclick="openSignatureChoiceModal()">
                            Signature
                        </button>

                        @if(auth()->user()->signature_path)
                            <div class="signature-preview-card">
                                <h3>Current Signature</h3>

                                <img src="{{ route('signatures.view', encrypt(auth()->id())) }}"
                                     class="signature-preview"
                                     alt="Signature">
                            </div>
                        @endif

                        <button type="submit"
                                class="btn-submit">
                            Save Profile
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
            <button onclick="closeSignModal()" class="modal-close">×</button>
        </div>

        <div class="page-selector">
            <label for="sigPageInput">Page:</label>
            <input type="number" id="sigPageInput" value="1" min="1">
        </div>

        <div class="pdf-wrapper" id="pdfWrapper">
            <canvas id="pdfCanvas"></canvas>
            <div class="pdf-click-layer" id="pdfClickLayer"></div>
            <div class="sig-ghost" id="sigGhost">✍ {{ auth()->user()->name }}</div>
        </div>

        <div class="modal-footer">
            <button class="btn-cancel" onclick="closeSignModal()">Cancel</button>
            <span class="hint" id="hintText">Click anywhere on the document to place your signature</span>
            <button class="btn-confirm" id="btnConfirm" onclick="submitSignature()" disabled>
                ✔ Approve & Sign
            </button>
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal-overlay" id="rejectModal" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Reject Document</h3>
            <button onclick="closeRejectModal()" class="modal-close">×</button>
        </div>

        <form method="POST" id="rejectForm">
            @csrf

            <div class="form-group">
                <label>Reason for rejection</label>
                <textarea name="remarks" rows="5" required
                          style="width:100%; padding:12px; border:1px solid #ccc; border-radius:8px;"></textarea>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeRejectModal()">Cancel</button>
                <button type="submit" class="btn-reject">Reject</button>
            </div>
        </form>
    </div>
</div>


{{-- Signature Choice Modal --}}
<div class="modal-overlay" id="signatureChoiceModal" style="display:none;">
    <div class="modal-box" style="max-width:420px;height:auto;">

        <div class="modal-header">
            <h3>Choose Signature Method</h3>

            <button type="button"
                    onclick="closeSignatureChoiceModal()"
                    class="modal-close">
                ×
            </button>
        </div>

        <div class="modal-footer" style="flex-direction:column;">
            <button type="button"
                    class="btn-submit"
                    onclick="openSignatureUploadModal()">
                Upload Signature
            </button>

            <button type="button"
                    class="btn-submit"
                    onclick="openSignatureDrawModal()">
                Draw Signature
            </button>
        </div>

    </div>
</div>

{{-- Upload Signature Modal --}}
<div class="modal-overlay"
     id="signatureUploadModal"
     style="display:none;">

    <div class="modal-box"
         style="max-width:500px;height:auto;">

        <div class="modal-header">
            <h3>Upload Signature</h3>

            <button type="button"
                    onclick="closeSignatureUploadModal()"
                    class="modal-close">
                ×
            </button>
        </div>

        <form method="POST"
              action="{{ route('signature.upload') }}"
              enctype="multipart/form-data"
              class="user-form"
              style="padding:24px;"
              onsubmit="return openPasswordModal(event, this)">

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
                    class="btn-submit">
                Upload Signature
            </button>

        </form>

    </div>
</div>

{{-- Signature Draw Modal --}}
<div class="modal-overlay" id="signatureDrawModal" style="display:none;">
    <div class="modal-box" style="max-width:650px;height:auto;">

        <div class="modal-header">
            <h3>Draw Signature</h3>

            <button type="button"
                    onclick="closeSignatureDrawModal()"
                    class="modal-close">
                ×
            </button>
        </div>

        
        <form id="drawSignatureForm"
      method="POST"
      action="{{ route('signature.draw') }}"
      class="user-form"
      onsubmit="return openPasswordModal(event, this);">

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

    <div class="signature-btn-row">

        <button type="button"
                class="btn-clear-signature"
                onclick="clearSignatureCanvas()">
            Clear
        </button>

        <button type="submit"
                class="btn-submit">
            Save Drawn Signature
        </button>

    </div>
</form>

    </div>
</div>

{{-- Signature Coordinate Form --}}
<form id="signatureForm" method="POST" action="">
    @csrf
    <input type="hidden" name="sig_x" id="formSigX">
    <input type="hidden" name="sig_y" id="formSigY">
    <input type="hidden" name="sig_w" id="formSigW">
    <input type="hidden" name="sig_h" id="formSigH">
    <input type="hidden" name="sig_page" id="formSigPage">
</form>

{{-- PDF Viewer Modal --}}
<div id="pdfModal" class="pdf-modal">
    <div class="pdf-modal-content">
        <button type="button" class="pdf-close" onclick="closePdfModal()">×</button>
        <iframe id="pdfFrame" class="pdf-frame-viewer"></iframe>
    </div>
</div>

@if(session('uploaded_signature_base64'))
    <script>
        console.log('UPLOADED SIGNATURE BASE64:');
        console.log(@json(session('uploaded_signature_base64')));
    </script>
@endif

@if(session('drawn_signature_base64'))
    <script>
        console.log('DRAWN SIGNATURE BASE64:');
        console.log(@json(session('drawn_signature_base64')));
    </script>
@endif


<script>
function openSignatureChoiceModal() {
    document.getElementById('signatureChoiceModal').style.display = 'flex';
}

function closeSignatureChoiceModal() {
    document.getElementById('signatureChoiceModal').style.display = 'none';
}

function openSignatureUploadModal() {
    closeSignatureChoiceModal();
    document.getElementById('signatureUploadModal').style.display = 'flex';
}

function closeSignatureUploadModal() {
    document.getElementById('signatureUploadModal').style.display = 'none';
}

function openSignatureDrawModal() {
    closeSignatureChoiceModal();
    document.getElementById('signatureDrawModal').style.display = 'flex';
}

function closeSignatureDrawModal() {
    document.getElementById('signatureDrawModal').style.display = 'none';
}
</script>
@include('partials.password-modal')


<script>

function showSection(section)
{
    document.querySelectorAll('.dashboard-section')
        .forEach((el) => {
            el.style.display = 'none';
        });

    const target =
        document.getElementById('section-' + section);

    if (target) {
        target.style.display = 'block';
    }

    document.querySelectorAll('.nav-btn')
        .forEach((btn) => {
            btn.classList.remove('active');
        });

    const activeBtn = document.querySelector(
        `.nav-btn[onclick="showSection('${section}')"]`
    );

    if (activeBtn) {
        activeBtn.classList.add('active');
    }
}

document.addEventListener('DOMContentLoaded', () => {

    showSection('dashboard');

});

</script>


@endsection
