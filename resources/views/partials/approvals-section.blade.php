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
                                            '{{ asset('storage/' . ($approval->document->current_file_path)) }}',
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