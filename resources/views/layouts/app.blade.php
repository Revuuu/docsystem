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

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;700;900&family=DM+Mono:wght@300;400;500&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap"
          rel="stylesheet">
    {{-- Sidebar Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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

            <div class="dashboard-main">

                @include('partials.header-section')

                @php

                    $role =
                        auth()->user()->roles->first()?->name
                        ?? auth()->user()->role;

                @endphp

                <div class="dashboard-banner">
                    <h1 id="bannerTitle">Dashboard</h1>
                </div>

                <div class="content">
                    @yield('content')
                </div>

            </div>

        </div>

    @else

        @yield('content')

    @endauth

    @stack('scripts')

</body>
</html>