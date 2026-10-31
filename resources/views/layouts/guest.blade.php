<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#173b73">
    <link rel="icon" type="image/png" href="{{ asset('images/fintec-logo.png') }}">
    <title>@yield('title', 'Sign in') — {{ $academySettings->organization_name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full flex-col bg-slate-50 text-slate-800 antialiased">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
            <a href="{{ route('home') }}" class="flex items-center gap-3 text-slate-900" aria-label="{{ $academySettings->organization_name }} home">
                <x-brand-mark />
                <span class="leading-tight">
                    <span class="block text-sm font-extrabold tracking-[0.12em] sm:text-base">FINTEC</span>
                    <span class="hidden text-[11px] font-medium text-slate-500 sm:block">Digital Skills Academy</span>
                </span>
            </a>
            <a href="{{ route('home') }}" class="text-sm font-semibold text-slate-600 hover:text-brand-blue">Back to the academy</a>
        </div>
    </header>

    @include('components.alert')

    <main class="flex flex-1 items-center justify-center px-4 py-10 sm:px-6">
        @yield('content')
    </main>

    <footer class="px-4 py-5 text-center text-xs text-slate-500">
        Secure access for {{ $academySettings->organization_name }} administrators
    </footer>
</body>
</html>
