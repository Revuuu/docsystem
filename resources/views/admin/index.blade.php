@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')

{{-- My Documents --}}

<div id="section-signed" class="dashboard-section">

    <div class="documents-card">

        <div class="documents-card-header">

            <h1 class="section-title">
                Signed Documents
            </h1>

        </div>

        @php
            $signedDocs = \App\Models\Document::where('status', 'approved')
                ->whereNotNull('admin_signed_at')
                ->latest('admin_signed_at')
                ->get();
        @endphp

        @if($signedDocs->count() > 0)

            <div class="table-wrapper">

                <table class="modern-docs-table">

                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Signed At</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($signedDocs as $doc)

                            <tr>

                                <td>
                                    {{ $doc->title }}
                                </td>

                                <td>
                                    {{ $doc->admin_signed_at?->format('M d, Y h:i A') }}
                                </td>

                                <td>
                                    @php
                                        $latestVersion = $doc->latestVersion();
                                    @endphp

                                    @if($latestVersion)
                                        <button type="button"
                                                class="view-pdf-btn"
                                                onclick="openPdfModal('{{ route('files.view', encrypt($latestVersion->id)) }}')">
                                            View PDF
                                        </button>
                                    @else
                                        <span class="no-data">
                                            No file available
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        @else

            <div style="padding:24px;">
                <p class="alert alert-error">
                    No signed documents yet.
                </p>
            </div>
        @endif
    </div>
</div>

{{-- Users Section --}}
<div id="section-users" class="dashboard-section role-section">

    <div class="role-page-header">
        <div>
            <h2 class="section-title">
                Role Assignment
            </h2>

            <p class="role-page-subtitle">
                Create user accounts and assign document workflow roles.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">
            <ul>
                @foreach($errors->all() as $error)
                    <li>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="role-layout">

        <div class="role-form-card">

            <div class="role-card-header">
                <h3>Add New User</h3>
                <p>
                    Fill out the user details and assign their workflow role.
                </p>
            </div>

            <form method="POST"
                action="{{ route('users.store') }}"
                class="user-form role-user-form">

                @csrf

                <div class="role-form-row">

                    <div class="form-group">
                        <label>First Name</label>

                        <input type="text"
                            name="first_name"
                            value="{{ old('first_name') }}"
                            required>
                    </div>

                    <div class="form-group">
                        <label>Last Name</label>

                        <input type="text"
                            name="last_name"
                            value="{{ old('last_name') }}"
                            required>
                    </div>

                </div>

                <div class="form-group">
                    <label>Middle Name</label>

                    <div class="middle-name-wrapper">
                        <input type="text"
                            id="middle_name"
                            name="middle_name"
                            value="{{ old('middle_name') }}">

                        <div class="checkbox-inline">
                            <input type="checkbox"
                                id="no_middle_name"
                                class="middle-name-checkbox">

                            <span>No Middle Name</span>
                        </div>
                    </div>
                </div>

                <div class="role-form-row">

                    <div class="form-group">
                        <label>Gender</label>

                        <select name="gender" required>
                            <option value="">
                                -- Select Gender --
                            </option>

                            <option value="male"
                                {{ old('gender') === 'male' ? 'selected' : '' }}>
                                Male
                            </option>

                            <option value="female"
                                {{ old('gender') === 'female' ? 'selected' : '' }}>
                                Female
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Assign Role</label>

                        <select name="role" required>
                            <option value="">
                                -- Select Role --
                            </option>

                            <option value="staff"
                                {{ old('role') === 'staff' ? 'selected' : '' }}>
                                Staff
                            </option>

                            <option value="supervisor"
                                {{ old('role') === 'supervisor' ? 'selected' : '' }}>
                                Supervisor
                            </option>

                            <option value="depthead"
                                {{ old('role') === 'depthead' ? 'selected' : '' }}>
                                Department Head
                            </option>

                            <option value="division"
                                {{ old('role') === 'division' ? 'selected' : '' }}>
                                Division Head
                            </option>

                            <option value="executive"
                                {{ old('role') === 'executive' ? 'selected' : '' }}>
                                Executive
                            </option>
                        </select>
                    </div>

                </div>

                <div class="form-group">
                    <label>Email</label>

                    <input type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required>
                </div>

                <div class="role-form-row">

                    <div class="form-group">
                        <label>Password</label>

                        <input type="password"
                            name="password"
                            required>
                    </div>

                    <div class="form-group">
                        <label>Confirm Password</label>

                        <input type="password"
                            name="password_confirmation"
                            required>
                    </div>

                </div>

                <button type="submit"
                        class="btn-submit role-create-btn">
                    Create User
                </button>

            </form>

        </div>

        <aside class="role-side-panel">

            <div class="role-guide-card">
                <h3>Role Guide</h3>

                <p>
                    Use these roles to control each user's access in the document workflow.
                </p>

                <div class="role-guide-list">

                    <div class="role-guide-item">
                        <span class="role-dot staff"></span>
                        <div>
                            <strong>Staff</strong>
                            <small>Uploads documents and tracks requests.</small>
                        </div>
                    </div>

                    <div class="role-guide-item">
                        <span class="role-dot supervisor"></span>
                        <div>
                            <strong>Supervisor</strong>
                            <small>Handles the first approval stage.</small>
                        </div>
                    </div>

                    <div class="role-guide-item">
                        <span class="role-dot depthead"></span>
                        <div>
                            <strong>Department Head</strong>
                            <small>Reviews department-level documents.</small>
                        </div>
                    </div>

                    <div class="role-guide-item">
                        <span class="role-dot division"></span>
                        <div>
                            <strong>Division Head</strong>
                            <small>Reviews before final approval.</small>
                        </div>
                    </div>

                    <div class="role-guide-item">
                        <span class="role-dot executive"></span>
                        <div>
                            <strong>Executive</strong>
                            <small>Handles final approval stage.</small>
                        </div>
                    </div>

                </div>
            </div>

            <div class="role-guide-card security-card">
                <h3>Security Note</h3>

                <p>
                    Passwords are securely hashed. User creation and account updates are recorded in the audit trail.
                </p>
            </div>

        </aside>

    </div>

</div>

{{-- Audit Trail Section --}}
<div id="section-audit"
    class="dashboard-section">

    <div class="documents-card">

        <div class="documents-card-header">

            <h2 class="section-title">
                Audit Trail
            </h2>

        </div>

        @if($auditLogs->count())

            <div style="padding: 0 24px 24px;">

                {{ $auditLogs->appends(['section' => 'audit'])->links() }}

            </div>

            <div class="table-wrapper">

                <table class="modern-docs-table audit-table">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Description</th>
                            <th>Event</th>
                            <th>Action</th>
                            <th>Table</th>
                            <th>Record ID</th>
                            <th>IP Address</th>
                            <th>User</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($auditLogs as $log)

                            <tr>

                                <td>{{ $log->id }}</td>

                                <td style="min-width: 320px;">
                                    {{ $log->description ?? '-' }}
                                </td>

                                <td>
                                    {{ $log->event ?? '-' }}
                                </td>

                                <td>
                                    <span class="status-pill ongoing">
                                        {{ strtoupper($log->action) }}
                                    </span>
                                </td>

                                <td>
                                    {{ $log->table_name ?? '-' }}
                                </td>

                                <td>
                                    {{ $log->table_id ?? '-' }}
                                </td>

                                <td>
                                    {{ $log->ip_address ?? '-' }}
                                </td>

                                <td>
                                    {{ $log->user?->name ?? 'System' }}
                                </td>

                                <td style="white-space: nowrap;">
                                    {{ $log->created_at?->format('M d, Y h:i:s A') ?? '-' }}
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

            <div class="documents-footer">

                {{ $auditLogs->appends(['section' => 'audit'])->links() }}

            </div>

        @else

            <div style="padding:24px;">

                <p class="no-data">
                    No audit logs available.
                </p>

            </div>

        @endif

    </div>

</div>

{{-- Signature Section --}}
<div id="section-signature"
    class="dashboard-section"
    >

    <h2 class="section-title">
        My Signature
    </h2>

    <div class="form-card">

        @if(auth()->user()->signature_path)

            <div style="margin-bottom:20px;">

                <h3>Current Signature</h3>

                <img src="{{ route(
                        'signatures.view',
                        encrypt(auth()->id())
                    ) }}"
                    alt="Signature"
                    class="signature-preview">

            </div>

        @else

            <p class="alert alert-error">
                No signature uploaded yet.
            </p>

        @endif

        <form method="POST"
            action="{{ route('signature.upload') }}"
            enctype="multipart/form-data"
            class="user-form">

            @csrf

            <div class="form-group">

                <label>Upload Signature</label>

                <input type="file"
                    name="signature"
                    accept="image/*"
                    required>

            </div>

            <button type="submit"
                    class="btn-submit">

                Upload Signature

            </button>

            
        </form>
    </div>
</div>

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
@endsection