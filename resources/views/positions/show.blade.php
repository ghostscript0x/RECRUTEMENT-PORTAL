@extends('layouts.app')

@section('title', $position->title)

@section('content')
    <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6">
        <a href="{{ route('positions.index') }}" class="text-sm font-medium text-blue-700 hover:text-blue-900">← All positions</a>

        <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    @if($position->department)
                        <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">{{ $position->department }}</p>
                    @endif
                    <h1 class="mt-1 text-2xl font-bold text-slate-900 sm:text-3xl">{{ $position->title }}</h1>
                </div>
                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Accepting Applications</span>
            </div>

            <dl class="mt-6 grid gap-4 border-y border-slate-100 py-5 sm:grid-cols-3">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Opening Date</dt>
                    <dd class="mt-1 text-sm font-medium text-slate-700">{{ $position->opens_at?->format('d M Y') ?? 'Immediately' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Closing Date</dt>
                    <dd class="mt-1 text-sm font-medium text-slate-700">{{ $position->closes_at?->format('d M Y') ?? 'Not specified' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Status</dt>
                    <dd class="mt-1 text-sm font-medium text-slate-700">Open for applications</dd>
                </div>
            </dl>

            <section class="mt-6">
                <h2 class="text-lg font-semibold text-slate-900">Position Description</h2>
                <div class="mt-2 space-y-3 text-sm leading-relaxed text-slate-700">{!! nl2br(e($position->description)) !!}</div>
            </section>

            @if($position->requirements)
                <section class="mt-6">
                    <h2 class="text-lg font-semibold text-slate-900">Requirements</h2>
                    <div class="mt-2 space-y-3 text-sm leading-relaxed text-slate-700">{!! nl2br(e($position->requirements)) !!}</div>
                </section>
            @endif

            @if($position->documentRequirements() !== [])
                <section class="mt-6">
                    <h2 class="text-lg font-semibold text-slate-900">Documents to Upload</h2>
                    <ul class="mt-2 space-y-1 text-sm text-slate-700">
                        @foreach($position->documentRequirements() as $document)
                            <li class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                                <span>{{ $document['label'] }}</span>
                                <span class="text-xs font-medium {{ ($document['required'] ?? false) ? 'text-rose-600' : 'text-slate-500' }}">
                                    {{ ($document['required'] ?? false) ? 'Required' : 'Optional' }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('apply.start', $position) }}" class="brand-button rounded-lg px-6 py-3 font-semibold shadow-sm">Apply now</a>
                <a href="{{ route('tracking.show') }}" class="rounded-lg border border-slate-300 px-6 py-3 font-semibold text-slate-700 hover:bg-slate-50">Track Application</a>
            </div>
        </div>
    </div>
@endsection
