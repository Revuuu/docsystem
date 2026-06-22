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

                <div class="topbar-account">
                    <div class="topbar-avatar">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>

                    <div class="topbar-user-text">
                        <strong>{{ auth()->user()->name }}</strong>
                        <small>{{ ucfirst(auth()->user()->role) }}</small>
                    </div>
                </div>

            </header>

            <section class="dashboard-page">

                <div class="dashboard-page-heading">
                    <h1 id="bannerTitle"></h1>
                    <p>Welcome to your document management dashboard</p>
                </div>

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