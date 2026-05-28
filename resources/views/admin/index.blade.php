@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')


{{-- Hamburger --}}
<button class="hamburger" id="hamburger">
    <span></span>
    <span></span>
    <span></span>
</button>

{{-- Overlay --}}
<div class="overlay" id="overlay"></div>

<div class="main-container">

    {{-- Sidebar --}}
    <div class="sidebar" id="sidebar">
        <div class="sidebar-logo">
        <img src="{{ asset('images/phmc-logo.png') }}" alt="PHMC Logo">
        PHMC
    </div>
    <div class="sidebar-user">

        <div class="user-avatar">
            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
        </div>

        <h2>{{ auth()->user()->name }}</h2>

        <span class="user-role-badge">
            {{ ucfirst(auth()->user()->role) }}
        </span>
        
        <div class="user-role-line"></div>

    </div>

        <nav style="display:flex; flex-direction:column; gap:0.5rem; width:100%;">

            <button type="button"
                    onclick="showSection('signed')"
                    class="nav-btn">
                ✅ Signed Documents
            </button>

            <button type="button"
                    onclick="showSection('users')"
                    class="nav-btn">
                👥 Role Assignment
            </button>

            <button type="button"
                    onclick="showSection('audit')"
                    class="nav-btn">
                📜 Audit Trail
            </button>
            <button type="button"
                    onclick="showSection('signature')"
                    class="nav-btn">
                📜 My Signature
            </button>

        </nav>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="btn-logout">
                Logout
            </button>
        </form>

    </div>

    {{-- Content --}}
    <div class="content">

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
                                        $viewPath = $doc->current_file_path;
                                    @endphp

                                    <button type="button"
                                            class="view-pdf-btn"
                                            onclick="openPdfModal('{{ asset('storage/' . $viewPath) }}')">

                                        View PDF

                                    </button>

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
        <div id="section-users"
             class="dashboard-section"
             >

            <h2 class="section-title">
                Role Assignment
            </h2>

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

            {{-- User Form --}}
            <div class="form-card">

                <h3>Add New User</h3>

                <form method="POST"
                      action="{{ route('users.store') }}"
                      class="user-form">

                    @csrf

                    <div class="form-group">
                        <label>Name</label>

                        <input type="text"
                               name="name"
                               value="{{ old('name') }}"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Email</label>

                        <input type="email"
                               name="email"
                               value="{{ old('email') }}"
                               required>
                    </div>

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

                    <div class="form-group">

                        <label>Assign Role</label>

                        <select name="role" required>

                            <option value="">
                                -- Select Role --
                            </option>

                            <option value="approver"
                                {{ old('role') === 'approver' ? 'selected' : '' }}>
                                Approver
                            </option>

                            <option value="requestor"
                                {{ old('role') === 'requestor' ? 'selected' : '' }}>
                                Requestor
                            </option>

                        </select>

                    </div>

                    @if($errors->any())

                        <div class="alert alert-error">

                            <ul>

                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach

                            </ul>

                        </div>

                    @endif

                    <button type="submit" class="btn-submit">
                        Create User
                    </button>

                </form>

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

                <table class="modern-docs-table">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Table</th>
                            <th>Record ID</th>
                            <th>Old State</th>
                            <th>Action</th>
                            <th>New State</th>
                            <th>IP Address</th>
                            <th>Account ID</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($auditLogs as $log)

                            <tr>

                                <td>{{ $log->id }}</td>

                                <td>{{ $log->table_name }}</td>

                                <td>{{ $log->table_id }}</td>

                                <td>
                                    {{ $log->old_values['status'] ?? '-' }}
                                </td>

                                <td>

                                    <span class="status-pill ongoing">
                                        {{ strtoupper($log->action) }}
                                    </span>

                                </td>

                                <td>
                                    {{ $log->new_values['status'] ?? '-' }}
                                </td>

                                <td>
                                    {{ $log->ip_address ?? '-' }}
                                </td>

                                <td>
                                    {{ $log->user_id ?? 'System' }}
                                </td>

                                <td>
                                    {{ $log->created_at->format('M d, Y h:i:s A') }}
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