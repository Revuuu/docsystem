@php
    use App\Services\SidebarService;

    $menuItems = SidebarService::getMenu(auth()->user()->role);
@endphp

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

    @foreach($menuItems as $item)

        <button
            class="nav-btn"
            onclick="showSection('{{ $item['section'] }}')">

            @if(isset($item['icon']))
                @include('partials.sidebar-icon', ['icon' => $item['icon']])
            @endif

            <span>{{ $item['label'] }}</span>

        </button>

    @endforeach

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