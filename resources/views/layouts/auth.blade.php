<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Sign in to the Research & Thesis Management Platform">

    <title>{{ config('app.name', 'ResearchHub') }} — @yield('title', 'Sign in')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=ibm-plex-sans:400,500,600,700|libre-baskerville:400,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans antialiased">
    <div class="flex min-h-screen">
        <x-auth-panel />

        {{-- Form panel --}}
        <div class="auth-form-wrap flex-1">
            <x-auth-mobile-header />

            @if(session('success') || session('error'))
                <div class="mb-6 space-y-2">
                    @if(session('success'))
                        <x-alert type="success" :message="session('success')" />
                    @endif
                    @if(session('error'))
                        <x-alert type="error" :message="session('error')" />
                    @endif
                </div>
            @endif

            <div class="mx-auto w-full max-w-sm">
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>
