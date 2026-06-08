<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div class="login-layout">
            <div class="branding-panel">
                <div class="branding-header">
                    <img src="{{ asset('images/phmc-logo.png') }}">
                    <div class="branding-text">
                        <h1>DOCSYSTEM</h1>
                        <h2>Perpetual Help Medical Center - Las Piñas</h2>
                    </div>
                </div>

                <p>
                    Document Workflow Management System
                    for routing, approval, tracking,
                    and secure document signing.
                </p>
            </div>

            <div class="login-card">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
