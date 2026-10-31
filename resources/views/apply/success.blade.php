@extends('layouts.app')

@section('title', 'Application Submitted')

@section('content')
    <div class="mx-auto max-w-2xl px-4 py-14 sm:px-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm">
            <span class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-emerald-100 text-3xl text-emerald-600">✓</span>

            <h1 class="mt-5 text-2xl font-bold text-slate-900">Application Submitted Successfully</h1>
            <p class="mt-2 text-slate-600">Thank you for applying. A confirmation email has been sent to
                <strong>{{ $email }}</strong>.
            </p>

            <div class="mt-6 rounded-xl border-2 border-dashed border-blue-200 bg-blue-50 px-6 py-5">
                <p class="text-sm font-medium uppercase tracking-wide text-blue-700">Your Application Reference Number</p>
                <p class="mt-2 font-mono text-3xl font-bold tracking-widest text-blue-900">{{ $reference }}</p>
                <p class="mt-3 text-sm text-blue-800">
                    Please <strong>keep this number safe</strong>. You will need it together with your email address to
                    track your application.
                </p>
            </div>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-center">
                <a href="{{ route('tracking.show', ['reference' => $reference]) }}"
                   class="brand-button rounded-lg px-5 py-2.5 text-sm font-semibold">Track my application</a>
                <a href="{{ route('positions.index') }}"
                   class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Browse More Positions</a>
            </div>
        </div>
    </div>
@endsection
