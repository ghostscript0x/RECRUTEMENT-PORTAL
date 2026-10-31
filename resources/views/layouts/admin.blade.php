<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#173b73">
    <link rel="icon" type="image/png" href="{{ asset('images/fintec-logo.png') }}">
    <title>@yield('title', 'Dashboard') — {{ $academySettings->organization_name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-slate-100 text-slate-800 antialiased">
    <div class="flex min-h-screen flex-col lg:flex-row">
        <aside class="bg-brand-blue-dark text-slate-200 lg:w-64 lg:shrink-0">
            <div class="flex h-16 items-center gap-3 border-b border-white/10 px-5 font-semibold text-white">
                <x-brand-mark size="h-10 w-12 rounded-lg bg-white p-1" />
                <span class="leading-tight"><span class="block text-xs font-extrabold tracking-[0.12em]">FINTEC</span><span class="text-[10px] font-medium text-slate-300">Academy admin</span></span>
            </div>
            <nav aria-label="Admin navigation" class="grid grid-cols-2 gap-2 p-3 text-sm md:grid-cols-3 lg:flex lg:flex-col">
                @can('dashboard.view')
                    <a href="{{ route('admin.dashboard') }}" @class(['rounded-lg px-3 py-2 font-medium hover:bg-white/10', 'bg-white/10 text-white' => request()->routeIs('admin.dashboard')])>Dashboard</a>
                @endcan
                @can('applications.view')
                    <a href="{{ route('admin.applications.index') }}" @class(['rounded-lg px-3 py-2 font-medium hover:bg-white/10', 'bg-white/10 text-white' => request()->routeIs('admin.applications.*')])>Applications</a>
                    <a href="{{ route('admin.reports.index') }}" @class(['rounded-lg px-3 py-2 font-medium hover:bg-white/10', 'bg-white/10 text-white' => request()->routeIs('admin.reports.*')])>Reports</a>
                @endcan
                @can('interviews.manage')
                    <a href="{{ route('admin.interviews.index') }}" @class(['rounded-lg px-3 py-2 font-medium hover:bg-white/10', 'bg-white/10 text-white' => request()->routeIs('admin.interviews.*')])>Interviews</a>
                @endcan
                @can('positions.manage')
                    <a href="{{ route('admin.positions.index') }}" @class(['rounded-lg px-3 py-2 font-medium hover:bg-white/10', 'bg-white/10 text-white' => request()->routeIs('admin.positions.*')])>Positions</a>
                @endcan
                @can('templates.manage')
                    <a href="{{ route('admin.templates.index') }}" @class(['rounded-lg px-3 py-2 font-medium hover:bg-white/10', 'bg-white/10 text-white' => request()->routeIs('admin.templates.*')])>Email Templates</a>
                    <a href="{{ route('admin.email-logs.index') }}" @class(['rounded-lg px-3 py-2 font-medium hover:bg-white/10', 'bg-white/10 text-white' => request()->routeIs('admin.email-logs.*')])>Email delivery</a>
                @endcan
                @can('users.manage')
                    <a href="{{ route('admin.users.index') }}" @class(['rounded-lg px-3 py-2 font-medium hover:bg-white/10', 'bg-white/10 text-white' => request()->routeIs('admin.users.*')])>Administrators</a>
                @endcan
                @can('audit.view')
                    <a href="{{ route('admin.audit.index') }}" @class(['rounded-lg px-3 py-2 font-medium hover:bg-white/10', 'bg-white/10 text-white' => request()->routeIs('admin.audit.*')])>Audit Logs</a>
                @endcan
                @can('settings.manage')
                    <a href="{{ route('admin.settings.edit') }}" @class(['rounded-lg px-3 py-2 font-medium hover:bg-white/10', 'bg-white/10 text-white' => request()->routeIs('admin.settings.*')])>Settings</a>
                @endcan
            </nav>
        </aside>

        <div class="flex flex-1 flex-col">
            <header class="flex h-16 items-center justify-between border-b border-slate-200 bg-white px-4 sm:px-6">
                <div class="text-sm text-slate-500">@yield('title', 'Dashboard')</div>
                <div class="flex items-center gap-3 text-sm">
                    <span class="hidden text-slate-600 sm:inline">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button class="rounded-lg border border-slate-300 px-3 py-1.5 font-semibold text-slate-700 transition hover:border-brand-orange hover:text-brand-orange-dark">Log out</button>
                    </form>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6">
                @include('components.alert')
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>