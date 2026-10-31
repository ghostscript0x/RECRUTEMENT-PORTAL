@extends('layouts.admin')

@section('title', 'Email templates')

@section('content')
    @php
        $enabledCount = $templates->where('enabled', true)->count();
        $eventDescriptions = [
            'submitted' => 'Sent when an applicant submits an application.',
            'under_review' => 'Sent when an application enters review.',
            'shortlisted' => 'Sent when an applicant is shortlisted.',
            'final_review' => 'Sent when an application reaches final review.',
            'selected' => 'Sent when an applicant is selected.',
            'rejected' => 'Sent when an application is unsuccessful.',
            'more_info_required' => 'Sent when additional information is needed.',
            'withdrawn' => 'Sent when an application is withdrawn.',
        ];
    @endphp

    <div class="mx-auto max-w-7xl space-y-5">
        <header class="flex flex-col gap-4 border-b border-slate-200 pb-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.12em] text-brand-orange">Communications</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Email templates</h1>
                <p class="mt-1 max-w-2xl text-sm leading-6 text-slate-600">Manage the automated messages applicants receive throughout the application process.</p>
            </div>
            <a href="{{ route('admin.email-logs.index') }}" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-brand-blue transition hover:border-brand-blue hover:bg-blue-50">
                Delivery history <span aria-hidden="true">→</span>
            </a>
        </header>

        <section class="grid gap-3 sm:grid-cols-2" aria-label="Email template summary">
            <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Templates</p>
                <p class="mt-1 text-2xl font-extrabold tabular-nums text-slate-900">{{ $templates->count() }}</p>
                <p class="mt-1 text-xs text-slate-500">Configured applicant messages</p>
            </article>
            <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Automatic delivery enabled</p>
                <p class="mt-1 text-2xl font-extrabold tabular-nums text-slate-900">{{ $enabledCount }} <span class="text-sm font-semibold text-slate-500">of {{ $templates->count() }}</span></p>
                <p class="mt-1 text-xs text-slate-500">Templates currently enabled</p>
            </article>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Available templates</h2>
                    <p class="mt-1 text-sm text-slate-600"><span data-template-visible-count>{{ $templates->count() }}</span> messages</p>
                </div>
                <label class="relative block w-full sm:max-w-xs">
                    <span class="sr-only">Find an email message</span>
                    <span class="pointer-events-none absolute inset-y-0 left-3 grid place-items-center text-slate-400" aria-hidden="true">⌕</span>
                    <input type="search" class="block min-h-10 w-full rounded-lg border border-slate-300 py-2 pl-10 pr-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-brand-blue focus:ring-4 focus:ring-blue-100" placeholder="Search templates" data-template-search-box>
                </label>
            </div>

            <div class="mt-4 grid gap-3 lg:grid-cols-2" data-template-grid>
                @forelse($templates as $template)
                    @php
                        $searchText = \Illuminate\Support\Str::lower($template->name.' '.$template->key.' '.$template->subject);
                        $eventDescription = $eventDescriptions[$template->key] ?? 'An automatic update for this application moment.';
                    @endphp
                    <article class="group rounded-xl border border-slate-200 bg-white p-4 transition hover:border-slate-300 hover:shadow-sm sm:p-5"
                        data-template-card data-template-search="{{ $searchText }}">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-sm font-bold text-slate-900">{{ $template->name }}</h3>
                                    <span @class([
                                        'inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-[11px] font-semibold',
                                        'bg-emerald-50 text-emerald-800' => $template->enabled,
                                        'bg-slate-100 text-slate-600' => ! $template->enabled,
                                    ])>
                                        <span @class(['h-1.5 w-1.5 rounded-full', 'bg-emerald-500' => $template->enabled, 'bg-slate-400' => ! $template->enabled])></span>
                                        {{ $template->enabled ? 'Automatic' : 'Paused' }}
                                    </span>
                                </div>
                                <p class="mt-1 text-sm text-slate-600">{{ $eventDescription }}</p>
                            </div>
                            <a href="{{ route('admin.templates.edit', $template) }}" class="inline-flex min-h-9 shrink-0 items-center justify-center gap-2 rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-semibold text-brand-blue transition hover:border-brand-blue hover:bg-blue-50">
                                Edit template <span aria-hidden="true">→</span>
                            </a>
                        </div>

                        <div class="mt-4 border-t border-slate-100 pt-3">
                            <p class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Subject</p>
                            <p class="mt-1 break-words text-sm font-semibold text-slate-800">{{ $template->subject }}</p>
                            <p class="mt-1 line-clamp-2 break-words text-xs leading-5 text-slate-500">{{ \Illuminate\Support\Str::limit(strip_tags($template->body), 150) }}</p>
                        </div>
                    </article>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center lg:col-span-2">
                        <h3 class="font-bold text-slate-900">No email templates are configured</h3>
                        <p class="mt-1 text-sm text-slate-600">Configured applicant messages will appear here.</p>
                    </div>
                @endforelse
            </div>

            <div class="hidden rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center" data-template-no-results>
                <p class="font-bold text-slate-800">No matching templates</p>
                <p class="mt-1 text-sm text-slate-600">Try a different search term.</p>
            </div>
        </section>

    </div>
@endsection
