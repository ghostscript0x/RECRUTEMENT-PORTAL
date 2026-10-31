@extends('layouts.guest')

@section('title', 'Admin sign in')

@section('content')
    <section class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_24px_70px_-38px_rgba(16,44,85,0.4)] sm:p-9" aria-labelledby="sign-in-title">
        <div class="mb-8">
            <p class="mb-3 inline-flex items-center gap-2 rounded-full bg-orange-50 px-3 py-1 text-xs font-bold uppercase tracking-[0.12em] text-orange-800">
                <span class="h-1.5 w-1.5 rounded-full bg-brand-orange"></span>
                Administrator portal
            </p>
            <h1 id="sign-in-title" class="text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Welcome back</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">Sign in to manage applications and academy opportunities.</p>
        </div>

        <form method="POST" action="{{ route('admin.login.attempt') }}" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="mb-1.5 block text-sm font-semibold text-slate-700">Email address</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                       class="block w-full rounded-lg border border-slate-300 px-3.5 py-3 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-blue-600 focus:ring-4 focus:ring-blue-100"
                       placeholder="you@fintecacademy.org"
                       @if($errors->has('email')) aria-describedby="email-error" aria-invalid="true" @endif>
                @error('email')
                    <p id="email-error" class="mt-1.5 text-sm font-medium text-rose-700">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="mb-1.5 block text-sm font-semibold text-slate-700">Password</label>
                <div class="relative">
                    <input id="password" type="password" name="password" required autocomplete="current-password"
                           class="block w-full rounded-lg border border-slate-300 px-3.5 py-3 pe-12 text-sm text-slate-900 shadow-sm outline-none transition focus:border-blue-600 focus:ring-4 focus:ring-blue-100"
                           @if($errors->has('password')) aria-describedby="password-error" aria-invalid="true" @endif>
                    <button type="button" class="absolute inset-y-0 right-0 inline-flex items-center justify-center rounded-r-lg px-3 text-slate-500 transition hover:text-brand-blue focus-visible:text-brand-blue"
                            aria-label="Show password" aria-controls="password" aria-pressed="false" data-password-toggle>
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true" data-password-show-icon>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-6.25 9.75-6.25S21.75 12 21.75 12 18.25 18.25 12 18.25 2.25 12 2.25 12Z"/>
                            <circle cx="12" cy="12" r="2.5"/>
                        </svg>
                        <svg class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true" data-password-hide-icon>
                            <path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.58 10.59a2 2 0 0 0 2.83 2.83"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.88 5.24A10.7 10.7 0 0 1 12 5c6.25 0 9.75 7 9.75 7a15.7 15.7 0 0 1-3.02 3.8M6.23 6.24C3.62 7.98 2.25 12 2.25 12s3.5 7 9.75 7c.83 0 1.62-.13 2.36-.36"/>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p id="password-error" class="mt-1.5 text-sm font-medium text-rose-700">{{ $message }}</p>
                @enderror
            </div>

            <label for="remember" class="flex w-fit cursor-pointer items-center gap-2.5 text-sm text-slate-600">
                <input id="remember" type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300 text-blue-700 focus:ring-blue-500">
                Keep me signed in
            </label>

            <button type="submit" class="brand-button flex w-full items-center justify-center rounded-lg px-4 py-3 text-sm font-bold shadow-sm">
                Sign in to admin
            </button>
        </form>

        <p class="mt-6 border-t border-slate-100 pt-5 text-center text-xs leading-5 text-slate-500">
            Need access? Contact your academy administrator.
        </p>
    </section>
@endsection
