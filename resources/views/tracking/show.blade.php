@extends('layouts.app')

@section('title', 'Track your application')

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 sm:py-14">
        <div class="mx-auto max-w-xl">
            <a href="{{ route('positions.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-brand-blue hover:text-brand-blue-dark">
                <span aria-hidden="true">←</span> Explore opportunities
            </a>

            <section class="mt-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_24px_70px_-38px_rgba(16,44,85,0.35)] sm:p-9" aria-labelledby="tracking-title">
                <div class="mb-7">
                    <p class="mb-2 text-xs font-bold uppercase tracking-[0.14em] text-brand-orange">Your application, at a glance</p>
                    <h1 id="tracking-title" class="text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Track an application</h1>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Enter the reference number from your confirmation email and the email address used to apply.</p>
                </div>

                @if($notFound)
                    <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-950" role="status">
                        We couldn't find an application with those details. Check your reference number and email address, then try again.
                    </div>
                @endif

                <form method="POST" action="{{ route('tracking.track') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="reference" class="mb-1.5 block text-sm font-semibold text-slate-700">Application reference number</label>
                        <input id="reference" type="text" name="reference" value="{{ old('reference', $reference) }}" required maxlength="32"
                               autocomplete="off" spellcheck="false" placeholder="e.g. APP-2026-8F42K"
                               class="block w-full rounded-lg border border-slate-300 px-3.5 py-3 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-blue-600 focus:ring-4 focus:ring-blue-100">
                        @error('reference')
                            <p class="mt-1.5 text-sm font-medium text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-semibold text-slate-700">Email address used to apply</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required maxlength="150" autocomplete="email"
                               class="block w-full rounded-lg border border-slate-300 px-3.5 py-3 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-blue-600 focus:ring-4 focus:ring-blue-100">
                        @error('email')
                            <p class="mt-1.5 text-sm font-medium text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="brand-button flex w-full items-center justify-center rounded-lg px-4 py-3 text-sm font-bold shadow-sm">
                        Check application status
                    </button>
                </form>

                <p class="mt-5 rounded-lg bg-slate-50 px-4 py-3 text-xs leading-5 text-slate-600">
                    Your application details stay private. Both the reference number and matching email address are required to view a status.
                </p>
            </section>

            @if($application)
                <section class="mt-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="application-status-title">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Application reference</p>
                            <p class="mt-1 font-mono text-lg font-bold tracking-wide text-brand-blue">{{ $application->reference }}</p>
                        </div>
                        <x-status-badge :status="$application->status" />
                    </div>

                    <div class="mt-5 border-t border-slate-100 pt-5">
                        <h2 id="application-status-title" class="text-lg font-bold text-slate-900">{{ $application->position->title }}</h2>
                        @if($application->position->department)
                            <p class="mt-1 text-sm text-slate-600">{{ $application->position->department }}</p>
                        @endif
                        <p class="mt-3 text-sm text-slate-600">
                            Submitted {{ $application->submitted_at?->format('j F Y') ?? 'date unavailable' }}
                        </p>
                    </div>

                    @if($application->statusHistory->isNotEmpty())
                        <ol class="mt-6 space-y-4 border-l-2 border-slate-100 pl-5">
                            @foreach($application->statusHistory as $history)
                                @php($historyStatus = \App\Enums\ApplicationStatus::tryFrom($history->to_status))
                                <li class="relative">
                                    <span class="absolute -left-[1.65rem] top-1 h-3 w-3 rounded-full border-2 border-white bg-brand-orange ring-1 ring-orange-200"></span>
                                    <p class="text-sm font-semibold text-slate-800">{{ $historyStatus?->label() ?? 'Status updated' }}</p>
                                    <time class="mt-0.5 block text-xs text-slate-500" datetime="{{ $history->created_at?->toIso8601String() }}">
                                        {{ $history->created_at?->format('j M Y, g:i a') }}
                                    </time>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </section>
            @endif
        </div>
    </div>
@endsection
