<div id="section-approvals" class="dashboard-section">

    <h1 class="section-title">
        Pending Approvals
    </h1>

    @if($errors->has('signature'))

        <div class="alert alert-error">
            {{ $errors->first('signature') }}
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

                            {{-- PENDING --}}
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

                                <br>

                                <small class="status-time">

                                    Completed in

                                    {{
                                        $approval->duration_seconds
                                            ? gmdate('H:i:s', (int) $approval->duration_seconds)
                                            : 'N/A'
                                    }}

                                </small>

                            {{-- REJECTED --}}
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
                                    <button
                                        class="btn-sign"
                                        data-has-signature="{{ auth()->user()->signature_path ? 'true' : 'false' }}"
                                        data-approval-id="{{ $approval->id }}"
                                        data-pdf-url='@json($approval->document->current_file_url)'
                                        data-approve-url='@json(route('approvals.approve', $approval->id))'
                                        onclick="handleApproveButton(this)">

                                        ✔ Approve & Sign

                                    </button>

                                    {{-- REJECT --}}
                                    <button
                                        type="button"
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

</div>

<script>

window.handleApproveButton = function(button)
{
    checkSignatureAndOpenModal(
        button.dataset.hasSignature === 'true',
        button.dataset.approvalId,
        JSON.parse(button.dataset.pdfUrl),
        JSON.parse(button.dataset.approveUrl)
    );
};

function openRejectModal(approvalId)
{
    const modal = document.getElementById('rejectModal');

    const form = document.getElementById('rejectForm');

    form.action = '/approvals/' + approvalId + '/reject';

    modal.style.display = 'flex';
}

function closeRejectModal()
{
    document.getElementById('rejectModal').style.display = 'none';
}

</script>