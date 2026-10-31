<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/fintec-logo.png') }}">
    <title>@yield('title', 'Dashboard') — Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-slate-100 text-slate-800 antialiased">
    <div class="flex min-h-screen flex-col lg:flex-row">
        <aside class="bg-slate-900 text-slate-200 lg:w-64 lg:shrink-0">
            <div class="flex h-16 items-center gap-2 border-b border-slate-800 px-5 font-semibold text-white">
                <x-brand-mark size="h-10 w-12 rounded-lg bg-white p-1" />
                <span>FINTEC Academy Admin</span>
            </div>
            <nav class="flex gap-1 overflow-x-auto p-3 text-sm lg:flex-col">
                @can('dashboard.view')
                    <a href="{{ route('admin.dashboard') }}" @class(['rounded-lg px-3 py-2 font-medium hover:bg-slate-800', 'bg-slate-800 text-white' => request()->routeIs('admin.dashboard')])>Dashboard</a>
                @endcan
                @can('applications.view')
                    <a href="{{ route('admin.applications.index') }}" @class(['rounded-lg px-3 py-2 font-medium hover:bg-slate-800', 'bg-slate-800 text-white' => request()->routeIs('admin.applications.*')])>Applications</a>
                @endcan
                @can('positions.manage')
                    <a href="{{ route('admin.positions.index') }}" @class(['rounded-lg px-3 py-2 font-medium hover:bg-slate-800', 'bg-slate-800 text-white' => request()->routeIs('admin.positions.*')])>Positions</a>
                @endcan
                @can('templates.manage')
                    <a href="{{ route('admin.templates.index') }}" @class(['rounded-lg px-3 py-2 font-medium hover:bg-slate-800', 'bg-slate-800 text-white' => request()->routeIs('admin.templates.*')])>Email Templates</a>
                @endcan
                @can('users.manage')
                    <a href="{{ route('admin.users.index') }}" @class(['rounded-lg px-3 py-2 font-medium hover:bg-slate-800', 'bg-slate-800 text-white' => request()->routeIs('admin.users.*')])>Administrators</a>
                @endcan
                @can('audit.view')
                    <a href="{{ route('admin.audit.index') }}" @class(['rounded-lg px-3 py-2 font-medium hover:bg-slate-800', 'bg-slate-800 text-white' => request()->routeIs('admin.audit.*')])>Audit Logs</a>
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
                        <button class="rounded-lg border border-slate-300 px-3 py-1.5 font-medium text-slate-600 hover:bg-slate-50">Log out</button>
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
