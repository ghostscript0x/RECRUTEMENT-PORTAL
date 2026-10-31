<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#173b73">
    <link rel="icon" type="image/png" href="{{ asset('images/fintec-logo.png') }}">
    <title>@yield('title', 'Home') — {{ $academySettings->organization_name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full flex-col bg-slate-50 text-slate-800 antialiased">
    <header class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/95 backdrop-blur">
        <nav class="relative mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
            <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3 text-slate-900" aria-label="{{ $academySettings->organization_name }} home">
                <x-brand-mark />
                <span class="min-w-0 leading-tight">
                    <span class="block text-sm font-extrabold tracking-[0.12em] sm:text-base">FINTEC</span>
                    <span class="hidden text-[11px] font-medium text-slate-500 sm:block">Digital Skills Academy</span>
                </span>
            </a>
            <button type="button" class="grid h-10 w-10 place-items-center rounded-lg text-slate-700 hover:bg-slate-100 md:hidden"
                    aria-label="Open navigation menu" aria-controls="primary-navigation" aria-expanded="false" data-mobile-menu-toggle>
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round"/>
                </svg>
            </button>
            <div id="primary-navigation" class="absolute left-0 right-0 top-full hidden flex-col gap-1 border-b border-slate-200 bg-white p-3 shadow-lg md:static md:flex md:flex-row md:items-center md:gap-2 md:border-0 md:p-0 md:shadow-none">
                <a href="{{ route('positions.index') }}" class="rounded-lg px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-brand-blue">Explore opportunities</a>
                <a href="{{ route('tracking.show') }}" class="rounded-lg px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-brand-blue">Track application</a>
                <a href="{{ route('positions.index') }}" class="brand-button inline-flex justify-center rounded-lg px-4 py-2.5 text-sm font-bold shadow-sm">Find your next step</a>
            </div>
        </nav>
    </header>

    <main class="flex-1">
        @include('components.alert')
        @yield('content')
    </main>

    <footer class="border-t border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl flex-col gap-2 px-4 py-6 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div class="flex flex-col gap-1">
                <p>&copy; {{ date('Y') }} {{ $academySettings->organization_name }}. All rights reserved.</p>
                @if($academySettings->support_email || $academySettings->support_phone)
                    <p class="flex flex-wrap gap-x-4 gap-y-1 text-xs">
                        @if($academySettings->support_email)
                            <a href="mailto:{{ $academySettings->support_email }}" class="hover:text-brand-blue">Support: {{ $academySettings->support_email }}</a>
                        @endif
                        @if($academySettings->support_phone)
                            <a href="tel:{{ $academySettings->support_phone }}" class="hover:text-brand-blue">{{ $academySettings->support_phone }}</a>
                        @endif
                    </p>
                @endif
            </div>
            <div class="flex gap-4">
                <a href="{{ route('positions.index') }}" class="hover:text-brand-blue">Available positions</a>
                <a href="{{ route('tracking.show') }}" class="hover:text-brand-blue">Track application</a>
            </div>
        </div>
    </footer>
</body>
</html>