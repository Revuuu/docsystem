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

    <nav class="sidebar-nav">
        <button class="nav-btn" onclick="showSection('upload')">
            Upload Document
        </button>

        <button class="nav-btn" onclick="showSection('approvals')">
            Pending Approvals
        </button>

        <button class="nav-btn" onclick="showSection('documents')">
            My Documents
        </button>

        <button class="nav-btn" onclick="showSection('signature')">
            My Signature
        </button>
    </nav>

    <div class="logout-wrap">
        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="btn-logout">
                Logout
            </button>
        </form>
    </div>

</div>