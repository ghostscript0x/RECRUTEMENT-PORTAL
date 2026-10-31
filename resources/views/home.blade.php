@extends('layouts.app')

@section('title', 'Home')

@section('content')
    <section class="relative overflow-hidden bg-gradient-to-br from-brand-blue via-brand-blue to-brand-blue-dark text-white">
        <div class="pointer-events-none absolute -right-32 -top-40 h-96 w-96 rounded-full border-[48px] border-white/5"></div>
        <div class="pointer-events-none absolute -bottom-48 right-1/4 h-80 w-80 rounded-full border-[36px] border-brand-orange/20"></div>
        <div class="relative mx-auto max-w-6xl px-4 py-14 sm:px-6 sm:py-20 lg:py-24">
            <p class="mb-4 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-xs font-bold uppercase tracking-[0.12em] text-blue-100">
                <span class="h-2 w-2 rounded-full bg-brand-orange"></span>
                {{ $academySettings->organization_name }}
            </p>
            <h1 class="max-w-3xl text-4xl font-extrabold leading-[1.08] tracking-tight sm:text-5xl lg:text-6xl">
                Your next opportunity starts here.
            </h1>
            <p class="mt-5 max-w-2xl text-base leading-7 text-blue-100 sm:text-lg">
                Explore open roles, apply in a few clear steps, and check your application status whenever you need to.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('positions.index') }}" class="brand-button rounded-lg px-5 py-3 text-sm font-bold shadow-sm sm:px-6">Explore open positions</a>
                <a href="{{ route('tracking.show') }}" class="rounded-lg border border-white/30 bg-white/5 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/10 sm:px-6">Track an application</a>
            </div>
            <dl class="mt-12 grid gap-3 sm:grid-cols-3 sm:gap-4">
                <div class="rounded-xl border border-white/10 bg-white/[0.08] p-4 sm:p-5">
                    <dt class="text-sm text-blue-100">Open positions</dt>
                    <dd class="mt-1 text-3xl font-extrabold">{{ $totalPositions }}</dd>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/[0.08] p-4 sm:p-5">
                    <dt class="text-sm text-blue-100">Guided application steps</dt>
                    <dd class="mt-1 text-3xl font-extrabold">8</dd>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/[0.08] p-4 sm:p-5">
                    <dt class="text-sm text-blue-100">Status checks</dt>
                    <dd class="mt-1 text-3xl font-extrabold">Anytime</dd>
                </div>
            </dl>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 py-12 sm:px-6 sm:py-16">
        <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.12em] text-brand-orange">Take the next step</p>
                <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Open opportunities</h2>
                <p class="mt-1 text-slate-600">Roles currently accepting applications.</p>
            </div>
            <a href="{{ route('positions.index') }}" class="text-sm font-bold text-brand-blue hover:text-brand-orange-dark">View all positions <span aria-hidden="true">→</span></a>
        </div>

        @if($positions->isEmpty())
            <div class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500">
                There are no open positions at the moment. Please check back soon.
            </div>
        @else
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($positions->take(6) as $position)
                    @include('positions.card', ['position' => $position])
                @endforeach
            </div>
        @endif
    </section>

    <section class="border-t border-slate-200 bg-white">
        <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6">
            <h2 class="text-2xl font-bold text-slate-900">How to Apply</h2>
            <ol class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach([
                    ['1', 'Choose a Position', 'Review the open vacancies and pick the role that matches your qualifications.'],
                    ['2', 'Complete the Form', 'Fill in your details across the guided sections and upload your documents.'],
                    ['3', 'Review & Submit', 'Check your summary carefully, confirm and submit your application.'],
                    ['4', 'Track Your Progress', 'Keep your reference number safe and track your application status online.'],
                ] as [$number, $title, $description])
                    <li class="rounded-xl border border-slate-200 p-5">
                        <span class="grid h-9 w-9 place-items-center rounded-full bg-blue-700 font-semibold text-white">{{ $number }}</span>
                        <h3 class="mt-3 font-semibold text-slate-900">{{ $title }}</h3>
                        <p class="mt-1 text-sm text-slate-600">{{ $description }}</p>
                    </li>
                @endforeach
            </ol>

            <div class="mt-8 rounded-xl bg-slate-50 p-5 text-sm text-slate-600">
                <p class="font-semibold text-slate-800">Application Deadline</p>
                <p class="mt-1">Each position displays its own closing date. Applications are only accepted while a position is open — check the individual vacancy for details.</p>
            </div>
        </div>
    </section>
@endsection