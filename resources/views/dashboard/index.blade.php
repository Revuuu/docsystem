@extends('layouts.app')

@section('title', ucfirst(auth()->user()->roles->first()?->name ?? auth()->user()->role) . ' Dashboard')

@section('content')

{{-- Upload Section / Upload Modal --}}
@include('partials.upload-section')

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
        
        @include('dashboard.user')

    </div>
        {{-- Documents --}}
        <div id="section-documents" class="dashboard-section">
              @if(session('document_success'))
                    <div class="alert alert-success" style="margin-bottom: 16px;">
                        {{ session('document_success') }}
                    </div>
                @endif

                @if(session('document_error'))
                    <div class="alert alert-error" style="margin-bottom: 16px;">
                        {{ session('document_error') }}
                    </div>
                @endif

                 @if(session('approval_success'))
                    <div class="alert alert-success" style="margin-bottom: 16px;">
                        {{ session('approval_success') }}
                    </div>
                @endif

                @if(session('approval_error'))
                    <div class="alert alert-error" style="margin-bottom: 16px;">
                        {{ session('approval_error') }}
                    </div>
                @endif

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
                                    <th>File name</th>
                                    <th>Uploaded By</th>
                                    <th>Uploaded At</th>
                                    <th>Status</th>
                                    <th>Elapsed Time</th>
                                    <th>Current Signatory</th>
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

                                        $currentPendingApproval = $doc->approvals
                                            ->where('status', 'pending')
                                            ->sortBy('step_order')
                                            ->first();

                                        $rejectedApproval = $doc->approvals
                                            ->where('status', 'rejected')
                                            ->sortByDesc('updated_at')
                                            ->first();

                                        $lastApprovedApproval = $doc->approvals
                                            ->where('status', 'approved')
                                            ->sortByDesc('signed_at')
                                            ->first();

                                        /*
                                            Display rules:
                                            - Status column only shows: Pending, Approved, Rejected, Upcoming
                                            - Elapsed Time column only shows time
                                            - Current Signatory column only shows the active signer
                                        */
                                        if ($myApproval?->status === 'approved') {
                                            $displayStatus = 'Approved';
                                            $statusClass = 'success';

                                            $elapsedTime = ($myApproval->received_at && $myApproval->signed_at)
                                                ? $myApproval->received_at->diffForHumans($myApproval->signed_at, true)
                                                : ($myApproval->signed_at
                                                    ? $doc->created_at->diffForHumans($myApproval->signed_at, true)
                                                    : '-');

                                            $currentSignatory = 'Completed';

                                        } elseif ($myApproval?->status === 'pending') {
                                            $displayStatus = 'Pending';
                                            $statusClass = 'waiting';

                                            $elapsedTime = $myApproval->received_at
                                                ? $myApproval->received_at->diffForHumans(now(), true)
                                                : $doc->created_at->diffForHumans(now(), true);

                                            $currentSignatory = auth()->user()->name;

                                        } elseif ($myApproval?->status === 'waiting') {
                                            $displayStatus = 'Upcoming';
                                            $statusClass = 'ongoing';

                                            $elapsedTime = '-';

                                            $currentSignatory = $currentPendingApproval?->user?->name
                                                ?? 'Unknown approver';

                                        } else {
                                            if ($doc->status === 'approved') {
                                                $displayStatus = 'Approved';
                                                $statusClass = 'success';

                                                $elapsedTime = $lastApprovedApproval?->signed_at
                                                    ? $doc->created_at->diffForHumans($lastApprovedApproval->signed_at, true)
                                                    : '-';

                                                $currentSignatory = 'Completed';

                                            } elseif ($doc->status === 'rejected') {
                                                $displayStatus = 'Rejected';
                                                $statusClass = 'rejected';

                                                $elapsedTime = $rejectedApproval?->updated_at
                                                    ? $doc->created_at->diffForHumans($rejectedApproval->updated_at, true)
                                                    : '-';

                                                $currentSignatory = 'None';

                                            } else {
                                                $displayStatus = 'Pending';
                                                $statusClass = 'waiting';

                                                $elapsedTime = $currentPendingApproval?->received_at
                                                    ? $currentPendingApproval->received_at->diffForHumans(now(), true)
                                                    : $doc->created_at->diffForHumans(now(), true);

                                                $currentSignatory = $currentPendingApproval?->user?->name
                                                    ?? 'Unknown approver';
                                            }
                                        }
                                    @endphp

                                    <tr class="document-row"
                                        data-title="{{ strtolower($doc->title) }}"
                                        data-file="{{ strtolower($latestVersion ? basename($latestVersion->generated_storage_path) : '') }}"
                                        data-status="{{ strtolower($myApproval?->status ?? $doc->status) }}">

                                        <td>
                                            <div class="file-name">
                                                {{ $doc->title }}
                                            </div>
                                        </td>

                                        <td>
                                            <div class="table-user">
                                                 <div class="table-user-info">
                                                    <strong>{{ $doc->uploader->name ?? 'Unknown' }}</strong>
                                                    <small>{{ ucfirst($doc->uploader->role ?? '-') }}</small>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            {{ $doc->created_at->format('M d, Y') }}
                                            <br>
                                            <small>{{ $doc->created_at->format('h:i A') }}</small>
                                        </td>

                                        <td>
                                            <span class="status-pill {{ $statusClass }}">
                                                {{ $displayStatus }}
                                            </span>
                                        </td>

                                        <td>
                                            {{ $elapsedTime }}
                                        </td>

                                        <td>
                                            {{ $currentSignatory }}
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

                <div class="upload-modal-body" style="padding:24px;">
                   @if(session('profile_success'))
                        <div class="alert alert-success">
                            {{ session('profile_success') }}
                        </div>
                    @endif

                    @if(session('profile_error'))
                        <div class="alert alert-error">
                            {{ session('profile_error') }}
                        </div>
                    @endif

                    @if(session('signature_success'))
                        <div class="alert alert-success">
                            {{ session('signature_success') }}
                        </div>
                    @endif

                    @if(session('signature_error'))
                        <div class="alert alert-error">
                            {{ session('signature_error') }}
                        </div>
                    @endif

                    @if($errors->signature->any())
                        <div class="alert alert-error">
                            <ul>
                                @foreach($errors->signature->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="profileForm"
                          method="POST"
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
                                   id="password"
                                   name="password"
                                   placeholder="Leave blank if unchanged"
                                   autocomplete="new-password">
                        </div>

                        <div class="form-group">
                            <label>Confirm Password</label>
                            <input type="password"
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   placeholder="Confirm new password"
                                   autocomplete="new-password">
                        </div>
                        <div id="passwordMatchMessage"
                            class="password-match-message">
                        </div>
                        @error('password')
                            <div class="text-danger mt-1">
                                {{ $message }}
                            </div>
                        @enderror
                       
                        <div class="password-rules" id="passwordRules">
                            <div id="ruleLength" class="rule invalid">
                                <i class="bi bi-x-circle-fill text-danger"></i>
                                Minimum 12 characters
                            </div>

                            <div id="ruleLetter" class="rule invalid">
                                <i class="bi bi-x-circle-fill text-danger"></i>
                                Contains letters
                            </div>

                            <div id="ruleCase" class="rule invalid">
                                <i class="bi bi-x-circle-fill text-danger"></i>
                                Contains uppercase and lowercase
                            </div>

                            <div id="ruleNumber" class="rule invalid">
                                <i class="bi bi-x-circle-fill text-danger"></i>
                                Contains a number
                            </div>

                            <div id="ruleSymbol" class="rule invalid">
                                <i class="bi bi-x-circle-fill text-danger"></i>
                                Contains a symbol (!@#$%^&*)
                            </div>
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
                        <div class="toast-container position-fixed top-0 end-0 p-3">

                            <div id="profileErrorToast"
                                class="toast text-bg-danger"
                                role="alert">

                                <div class="toast-header">
                                    <strong class="me-auto">Profile Error</strong>
                                    <button type="button"
                                            class="btn-close"
                                            data-bs-dismiss="toast">
                                    </button>
                                </div>

                                <div class="toast-body">
                                    Please correct the errors and try again.
                                </div>

                            </div>
                        </div>
                        @if($errors->any())
                            <script>
                            document.addEventListener('DOMContentLoaded', function () {

                                const toast =
                                    new bootstrap.Toast(
                                        document.getElementById(
                                            'profileErrorToast'
                                        )
                                    );

                                toast.show();

                            });
                            </script>
                        @endif
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
            
            <button class="btn-confirm" id="btnConfirm" onclick="submitSignature()" disabled>
                ✔ Approve & Sign
            </button>
        </div>
        <span class="hint" id="hintText">Click anywhere on the document to place your signature</span>
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
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('profileForm')
.addEventListener('submit', function (e) {

    const password =
        document.getElementById('password');

    const confirmation =
        document.getElementById('password_confirmation');

    if (
        password.value &&
        password.value !== confirmation.value
    ) {

        e.preventDefault();

        const toast = new bootstrap.Toast(
            document.getElementById('profileErrorToast')
        );

        document.querySelector(
            '#profileErrorToast .toast-body'
        ).textContent =
            'The password confirmation does not match.';

        toast.show();
    }
});

    const passwordInput =
        document.getElementById('password');

    if (!passwordInput) return;

    passwordInput.addEventListener('input', validatePassword);

    function validatePassword() {

        const value = passwordInput.value;

        updateRule(
            'ruleLength',
            value.length >= 12
        );

        updateRule(
            'ruleLetter',
            /[a-zA-Z]/.test(value)
        );

        updateRule(
            'ruleCase',
            /[a-z]/.test(value) &&
            /[A-Z]/.test(value)
        );

        updateRule(
            'ruleNumber',
            /\d/.test(value)
        );

        updateRule(
            'ruleSymbol',
            /[^A-Za-z0-9]/.test(value)
        );
    }

    function updateRule(id, valid) {

        const rule =
            document.getElementById(id);

        const icon =
            rule.querySelector('i');

        if (valid) {

            rule.classList.remove('invalid');
            rule.classList.add('valid');

            icon.classList.remove(
                'bi-x-circle-fill',
                'text-danger'
            );

            icon.classList.add(
                'bi-check-circle-fill',
                'text-success'
            );

        } else {

            rule.classList.remove('valid');
            rule.classList.add('invalid');

            icon.classList.remove(
                'bi-check-circle-fill',
                'text-success'
            );

            icon.classList.add(
                'bi-x-circle-fill',
                'text-danger'
            );
        }
    }
     const password =
        document.getElementById('password');

    const confirmation =
        document.getElementById(
            'password_confirmation'
        );

    const matchMessage =
        document.getElementById(
            'passwordMatchMessage'
        );

    function checkMatch() {

    if (confirmation.value.length === 0) {

        matchMessage.innerHTML = '';
        return;
    }

    if (password.value === confirmation.value) {

        matchMessage.innerHTML =
            '<i class="bi bi-check-circle-fill text-success"></i> Passwords match';

    } else {

        matchMessage.innerHTML =
            '<i class="bi bi-exclamation-circle-fill text-danger"></i> Passwords do not match';
    }
}

    password.addEventListener(
        'input',
        checkMatch
    );

    confirmation.addEventListener(
        'input',
        checkMatch
    );

});
</script>
@include('partials.password-modal')

@endsection
