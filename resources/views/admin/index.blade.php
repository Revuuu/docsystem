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

        <h2>Logged in as</h2>

        <p>{{ auth()->user()->name }}</p>

        <p class="font-semibold text-sm">
            {{ ucfirst(auth()->user()->role) }}
        </p>

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

        </nav>

        <a class="nav-btn" href="{{ route('signature.index') }}">
            My Signature
        </a>

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
        <div id="section-documents" class="dashboard-section">

            <h1 class="section-title">
                My Documents
            </h1>

            @php
                $signedDocs = \App\Models\Document::where('status', 'approved')
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
                                    {{ $doc->admin_signed_at?->format('M d, Y h:i A') }}
                                </td>

                                <td>
                                    @php
                                        $isUploader = $doc->uploaded_by === auth()->id();

                                        $viewPath = $doc->current_file_path;
                                    @endphp

                                       <button type="button" class="view-pdf-btn"
                                            onclick="openPdfModal('{{ asset('storage/' . $viewPath) }}')">
                                            View PDF
                                        </button>
                                </td>

                            </tr>

                        @endforeach
                    
                       
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
                    </tbody>

                </table>

            @else

                <p class="alert alert-error">
                    No signed documents yet.
                </p>

            @endif

        </div>

        {{-- Users Section --}}
        <div id="section-users"
             class="dashboard-section"
             style="display:none;">

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

    </div>

</div>

@endsection