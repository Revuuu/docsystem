@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')

{{-- Dashboard --}}
<div id="section-admin-dashboard" class="dashboard-section">

    {{-- KPI Cards --}}
    <div class="analytics-grid">

        <div class="analytics-card primary">
            <div class="analytics-value">{{ $totalDocuments }}</div>
            <div class="analytics-label">Total Documents</div>
        </div>

        <div class="analytics-card warning">
            <div class="analytics-value">{{ $pendingDocuments }}</div>
            <div class="analytics-label">Pending Approvals</div>
        </div>

        <div class="analytics-card success">
            <div class="analytics-value">{{ $approvedDocuments }}</div>
            <div class="analytics-label">Approved</div>
        </div>

        <div class="analytics-card danger">
            <div class="analytics-value">{{ $rejectedDocuments }}</div>
            <div class="analytics-label">Rejected</div>
        </div>

        <div class="analytics-card info">
            <div class="analytics-value">{{ $totalUsers }}</div>
            <div class="analytics-label">Users</div>
        </div>

        <div class="analytics-card dark">
            <div class="analytics-value">{{ $documentsThisMonth }}</div>
            <div class="analytics-label">Documents This Month</div>
        </div>

    </div>

    {{-- Workflow Status --}}
    <div class="dashboard-row">

        <div class="dashboard-panel">

            <h2>Workflow Status</h2>

            <div class="workflow-bars">

                <div class="workflow-item">
                    <span>Pending</span>
                    <div class="bar pending">
                        <div style="width: {{ $pendingDocuments }}%"></div>
                    </div>
                    <strong>{{ $pendingDocuments }}</strong>
                </div>

                <div class="workflow-item">
                    <span>Approved</span>
                    <div class="bar approved">
                        <div style="width: {{ $approvedDocuments }}%"></div>
                    </div>
                    <strong>{{ $approvedDocuments }}</strong>
                </div>

                <div class="workflow-item">
                    <span>Rejected</span>
                    <div class="bar rejected">
                        <div style="width: {{ $rejectedDocuments }}%"></div>
                    </div>
                    <strong>{{ $rejectedDocuments }}</strong>
                </div>

            </div>

        </div>

        <div class="dashboard-panel">

            <h2>System Summary</h2>

            <table class="summary-table">
                <tr>
                    <td>Documents Today</td>
                    <td>{{ $documentsToday }}</td>
                </tr>

                <tr>
                    <td>Documents This Month</td>
                    <td>{{ $documentsThisMonth }}</td>
                </tr>

                <tr>
                    <td>Total Users</td>
                    <td>{{ $totalUsers }}</td>
                </tr>

                <tr>
                    <td>Audit Logs</td>
                    <td>{{ $auditLogs->total() }}</td>
                </tr>
            </table>

        </div>

    </div>

    {{-- Recent Activities --}}
    <div class="dashboard-panel">

        <h2>Recent Activities</h2>

        <div class="activity-list">

            @forelse($recentActivities as $activity)

                <div class="activity-item">

                    <div class="activity-user">
                        {{ $activity->user?->name ?? 'System' }}
                    </div>

                    <div class="activity-desc">
                        {{ $activity->description }}
                    </div>

                    <div class="activity-time">
                        {{ $activity->created_at->diffForHumans() }}
                    </div>

                </div>

            @empty

                <div class="no-data">
                    No recent activity found.
                </div>

            @endforelse

        </div>

    </div>

</div>

{{-- My Documents --}}

<div id="section-signed" class="dashboard-section">

    <div class="documents-card">

        @php
            $signedDocs = \App\Models\Document::where('status', 'approved')
                ->whereNotNull('admin_signed_at')
                ->latest('admin_signed_at')
                ->paginate(10);
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

                 <div class="documents-footer">

                {{ $signedDocs->appends(['section' => 'signed'])->links() }}

                </div>
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

    @if(session('role_success'))
        <div class="alert alert-success">
            {{ session('role_success') }}
        </div>
    @endif

    @if(session('role_error'))
        <div class="alert alert-error">
            {{ session('role_error') }}
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
{{-- User Directory --}}
<div id="section-user-management" class="dashboard-section">
    @if(session('user_success'))
        <div class="alert alert-success">
            {{ session('user_success') }}
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
    <div id="userTableContainer" class="documents-card" style="margin-top:24px;">
        <div class="card-header">
    <div class="user-filters-card">

        {{-- Search Row --}}
        <div class="search-row">

            <div class="search-box">

                <i class="bi bi-search" id="search-icon"></i>

                <input
                    type="text"
                    id="userSearch"
                    placeholder="Search by Name or Email">

            </div>
            
               <button
                    id="clearFilters"
                    class="clear-filters-btn"
                    type="button">
                    Clear All
                </button>
        </div>

        {{-- Filter Controls --}}
        <div class="filter-grid">

            <select id="roleFilter">
                <option value="">All Roles</option>
                <option value="admin">Admin</option>
                <option value="staff">Staff</option>
                <option value="supervisor">Supervisor</option>
                <option value="depthead">Department Head</option>
                <option value="division">Division Head</option>
                <option value="executive">Executive</option>
            </select>

            <select id="statusFilter">
                <option value="">All Status</option>
                <option value="verified">Verified</option>
                <option value="unverified">Unverified</option>
            </select>

            <input type="date" id="dateFilter">

        </div>

        <div class="results-info">

            Showing {{ $users->count() }} users

        </div>

    </div>

        </div>

        <div class="table-wrapper">

            <table class="modern-docs-table">

                <thead>

                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Email Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>

                </thead>

                <tbody id="userTable">

                    @forelse($users as $user)

                        <tr>

                            <td>
                                {{ $user->name }}
                            </td>

                            <td>
                                {{ $user->email }}
                            </td>

                            <td>
                                @if($user->roles->count())
                                    @foreach($user->roles as $role)
                                        <span class="role-badge">
                                            {{ ucfirst($role->name) }}
                                        </span>

                                    @endforeach

                                @elseif($user->role)

                                    <span class="role-badge">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                @else
                                    <span class="role-badge">
                                        -
                                    </span>
                                @endif

                            </td>

                            <td>

                                @if($user->email_verified_at)

                                    <span class="status-pill approved">
                                        Verified
                                    </span>

                                @else

                                    <span class="status-pill rejected">
                                        Unverified
                                    </span>

                                @endif

                            </td>

                            <td>

                                {{ $user->created_at->format('M d, Y') }}

                            </td>

                            <td>

                                <div class="more-menu">

                                    <button type="button"
                                            class="table-action-btn btn-clear"
                                            onclick="toggleMoreMenu(event, this)">
                                          <i class="bi bi-three-dots-vertical"></i>
                                    </button>

                                    <div class="more-dropdown">

                                        <button type="button"
                                                class="dropdown-item"
                                                onclick="editUser(
                                                        {{ $user->id }},
                                                        '{{ addslashes($user->name) }}',
                                                        '{{ $user->email }}',
                                                        '{{ $user->role }}'
                                                    )">
                                            Edit User
                                        </button>

                                        <form method="POST"
                                            action="{{ route('users.resetPassword', $user) }}">

                                            @csrf

                                            <button type="submit"
                                                    class="dropdown-item"
                                                    onclick="return confirm('Send password reset email?')">

                                            Reset Password

                                            </button>

                                        </form>

                                        <form method="POST"
                                            action="{{ route('users.destroy', $user) }}"
                                            class="delete-user-form">

                                            @csrf
                                            @method('DELETE')

                                            <input type="hidden"
                                                name="password"
                                                class="password-hidden-input">
                                                
                                            <button type="button"
                                                    class="dropdown-item danger"
                                                    onclick="return openPasswordModal(event, this.closest('form'))">

                                                Delete User

                                            </button>

                                        </form>

                                    </div>

                                </div>

                            </td>
                        </tr>

                    @empty

                        <tr>

                            <td colspan="6">

                                No users found.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>
    </div>
</div>
{{-- Audit Trail Section --}}
<div id="section-audit"
    class="dashboard-section">

    <div class="documents-card">

        @if($auditLogs->count())

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

<!-- Edit User Modal -->
<div id="editUserModal" class="modal-overlay" style="display:none;">

    <div class="modal-card">

        <div class="modal-header">
            <h3>Edit User</h3>

            <button type="button"
                    onclick="closeEditUserModal()">
                ✕
            </button>
        </div>

        <form id="editUserForm"
              method="POST">

            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Name</label>

                <input type="text"
                       id="edit_name"
                       name="name"
                       required>
            </div>

            <div class="form-group">
                <label>Email</label>

                <input type="email"
                       id="edit_email"
                       name="email"
                       required>
            </div>

            <div class="form-group">
                <label>Role</label>

                <select id="edit_role"
                        name="role"
                        required>

                    <option value="staff">Staff</option>
                    <option value="supervisor">Supervisor</option>
                    <option value="depthead">Department Head</option>
                    <option value="division">Division Head</option>
                    <option value="executive">Executive</option>
                    <option value="admin">Admin</option>

                </select>
            </div>

            <button type="submit"
                    class="btn-submit">
                Save Changes
            </button>

        </form>

    </div>

</div>
@include('partials.password-modal')
@endsection