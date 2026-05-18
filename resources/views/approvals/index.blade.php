@extends('layouts.app')

@section('title', 'Approvals')

@section('content')

<div class="main-container">

    {{-- Sidebar --}}
    <div class="sidebar">

        <h2>Logged in as</h2>

        <p>{{ auth()->user()->name }}</p>

        <p class="font-semibold text-sm">
            {{ ucfirst(auth()->user()->role) }}
        </p>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="btn-logout">
                Logout
            </button>
        </form>

    </div>

    {{-- Content --}}
    <div class="content">

        {{-- Pending Approvals --}}
        <div class="dashboard-section">

            <h1 class="section-title">
                Pending Approvals
            </h1>

            @if($approvals->count() > 0)

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

    @if($approval->status === 'waiting')

        <span class="status-upcoming">
            Upcoming
        </span>

    @elseif($approval->status === 'pending')

        <span class="status-pending">
            Pending
        </span>

    @elseif($approval->status === 'approved')

        <span class="status-approved">
            Approved
        </span>

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

                                        <button class="btn-sign"
                                            onclick="checkSignatureAndOpenModal(
                                                {{ auth()->user()->signature_path ? 'true' : 'false' }},
                                                {{ $approval->id }},
                                                '{{ asset('storage/' . ($approval->document->signed_file_path ?? $approval->document->file_path)) }}',
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
        <div class="dashboard-section">

            <h1 class="section-title">
                Signed Documents
            </h1>

            @php
                $signedDocs = \App\Models\Document::whereNotNull('signed_file_path')
                    ->whereNotNull('admin_signed_at')
                    ->latest('admin_signed_at')
                    ->get();
            @endphp

            @if($signedDocs->count() > 0)

                <table>

                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Signed At</th>
                            <th>Download</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($signedDocs as $doc)

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

    </div>

</div>

{{-- Modal --}}
<div class="modal-overlay" id="signModal">

    <div class="modal-box">

        <div class="modal-header">

            <h3>Place Signature</h3>

            <button onclick="closeSignModal()"
                    class="modal-close">

                ×

            </button>

        </div>

        <div class="page-selector">

            <label for="sigPageInput">
                Page:
            </label>

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

            <span class="hint"
                  id="hintText">

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

<form id="signatureForm"
      method="POST"
      action="">

    @csrf

    <input type="hidden" name="sig_x" id="formSigX">
    <input type="hidden" name="sig_y" id="formSigY">
    <input type="hidden" name="sig_w" id="formSigW">
    <input type="hidden" name="sig_h" id="formSigH">
    <input type="hidden" name="sig_page" id="formSigPage">

</form>

@endsection