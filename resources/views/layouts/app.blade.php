<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'DOCSYSTEM')</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
          rel="stylesheet">

    {{-- Bootstrap Icons --}}
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    
    {{-- Tabler Icons --}}
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">

    {{-- Vite Assets --}}
    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    @stack('styles')
</head>

<body>

@auth
    <div class="overlay" id="overlay"></div>

    <div class="dashboard-shell">

        @include('partials.sidebar')

        <main class="dashboard-main">

            <header class="dashboard-topbar">

                <div class="topbar-left">
                    @include('partials.header-section')
                </div>

              <div class="topbar-user-dropdown" id="topbarUserDropdown">

                    <button
                        type="button"
                        class="topbar-user-trigger"
                        id="topbarUserTrigger"
                        aria-expanded="false"
                        aria-controls="topbarUserMenu"
                    >
                        <div class="user-avatar">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>

                        <div class="topbar-user-info">
                            <strong>{{ auth()->user()->name }}</strong>
                            <small>{{ ucfirst(auth()->user()->role) }}</small>
                        </div>

                        <i class="bi bi-chevron-down topbar-user-chevron"></i>
                    </button>

                    <div
                        class="topbar-user-menu"
                        id="topbarUserMenu"
                        role="menu"
                    >
                        <div class="topbar-user-menu-header">
                            <strong>{{ auth()->user()->name }}</strong>
                            <span>{{ auth()->user()->email }}</span>
                        </div>

                        <div class="topbar-user-menu-divider"></div>

                        <a
                            href="{{ route('dashboard', ['section' => 'profile']) }}"
                            class="topbar-user-menu-item"
                            role="menuitem"
                        >
                            <i class="bi bi-person"></i>
                            <span>My Profile</span>
                        </a>

                        <a
                            href="{{ route('dashboard') }}"
                            class="topbar-user-menu-item"
                            role="menuitem"
                        >
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>

                        <div class="topbar-user-menu-divider"></div>

                        <form
                            method="POST"
                            action="{{ route('logout') }}"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="topbar-user-menu-item topbar-user-logout"
                                role="menuitem"
                            >
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>

                </div>
                

            </header>

            <section class="dashboard-page">

                <div class="content">
                    @yield('content')
                </div>

            </section>

        </main>

    </div>
@else
    @yield('content')
@endauth

@stack('scripts')

</body>
</html>