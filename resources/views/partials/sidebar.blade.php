@php
    use App\Services\SidebarService;

    $menuItems = SidebarService::getMenu(auth()->user()->role);
@endphp

<aside class="sidebar" id="sidebar">

    <div class="sidebar-brand">
        <div class="sidebar-brand-logo">
            <img src="{{ asset('images/phmc-logo.png') }}" alt="PHMC Logo">
        </div>

        <span class="brand-text">PHMC</span>
    </div>

    <nav class="sidebar-nav">

        @foreach($menuItems as $item)

            <button
                type="button"
                class="nav-btn"
                onclick="showSection('{{ $item['section'] }}')">

                @if(isset($item['icon']))
                    @include('partials.sidebar-icon', ['icon' => $item['icon']])
                @endif

                <span class="nav-text">{{ $item['label'] }}</span>

            </button>

        @endforeach

    </nav>

    <div class="sidebar-user-card">

        <div class="user-avatar">
            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
        </div>

        <div class="sidebar-user-info">
            <strong>{{ auth()->user()->name }}</strong>
            <small>{{ ucfirst(auth()->user()->role) }}</small>
        </div>

    </div>

    <form method="POST" action="{{ route('logout') }}" class="logout-wrap">
        @csrf

        <button type="submit" class="btn-logout">
            <i class="bi bi-box-arrow-right"></i>
            <span>Logout</span>
        </button>
    </form>

</aside>