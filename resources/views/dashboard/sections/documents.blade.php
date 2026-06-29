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
                    <i class="bi bi-plus"></i> UPLOAD NEW DOCUMENT
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
                                                class="table-action-btn btn-clear"
                                                onclick="toggleMoreMenu(event, this)">
                                            <i class="bi bi-three-dots-vertical"></i>
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