@extends('layouts.admin')

@section('title', 'Email delivery')

@section('content')
    <div class="mx-auto max-w-7xl space-y-5">
        <header class="flex flex-col gap-4 border-b border-slate-200 pb-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-brand-orange">Communications</p>
                    <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Email delivery history</h1>
                    <p class="mt-1 max-w-xl text-sm leading-6 text-slate-600">Review notification status and delivery details for applicant emails.</p>
                </div>
                <a href="{{ route('admin.templates.index') }}" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-brand-blue transition hover:border-brand-blue hover:bg-blue-50">
                    Email templates <span aria-hidden="true">→</span>
                </a>
        </header>

        @if(session('emailStatus'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">
                {{ session('emailStatus') }}
            </div>
        @endif
        @if(session('emailError'))
            <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-900">
                {{ session('emailError') }}
            </div>
        @endif

        <form method="GET" action="{{ route('admin.email-logs.index') }}" class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-[minmax(0,1fr)_220px_auto] sm:items-end sm:p-5">
            <div>
                <label for="q" class="mb-1.5 block text-sm font-semibold text-slate-700">Search</label>
                <input id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Recipient, subject or template" class="block min-h-10 w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-brand-blue focus:ring-4 focus:ring-blue-100">
            </div>
            <div>
                <label for="status" class="mb-1.5 block text-sm font-semibold text-slate-700">Delivery status</label>
                <select id="status" name="status" class="block min-h-10 w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-800 focus:border-brand-blue focus:ring-4 focus:ring-blue-100">
                    <option value="">All statuses</option>
                    @foreach(['sending' => 'Sending', 'queued' => 'Queued', 'sent' => 'Sent', 'failed' => 'Failed'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" data-reset-form class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Reset</button>
                <a href="{{ route('admin.email-logs.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Clear all</a>
                <button type="submit" class="brand-button inline-flex min-h-10 flex-1 items-center justify-center rounded-lg px-4 py-2 text-sm font-bold sm:flex-initial">Apply filters</button>
            </div>
        </form>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-2 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <h2 class="font-bold text-slate-900">Delivery records</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ number_format($logs->total()) }} {{ \Illuminate\Support\Str::plural('email', $logs->total()) }} {{ $logs->total() === 1 ? 'match' : 'matches' }}</p>
                </div>
                <span class="text-xs font-medium text-slate-500">Most recent first</span>
            </div>
            @if($logs->isEmpty())
                <div class="px-6 py-14 text-center">
                    <h3 class="font-bold text-slate-900">No delivery records found</h3>
                    <p class="mx-auto mt-1 max-w-md text-sm leading-6 text-slate-500">Adjust your search or filters to see other email attempts.</p>
                </div>
            @else
                <div class="divide-y divide-slate-100 md:hidden">
                    @foreach($logs as $log)
                        <article class="space-y-3 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="break-all text-sm font-bold text-slate-900">{{ $log->to_email }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $log->created_at?->format('j M Y, g:i a') ?? 'Date unavailable' }}</p>
                                </div>
                                <span @class([
                                    'inline-flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold',
                                    'bg-emerald-50 text-emerald-800' => $log->status === 'sent',
                                    'bg-amber-50 text-amber-800' => in_array($log->status, ['sending', 'queued'], true),
                                    'bg-rose-50 text-rose-800' => $log->status === 'failed',
                                    'bg-slate-100 text-slate-700' => ! in_array($log->status, ['sent', 'sending', 'queued', 'failed'], true),
                                ])>
                                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>{{ \Illuminate\Support\Str::headline($log->status) }}
                                </span>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3">
                                <p class="break-words text-sm font-semibold text-slate-800">{{ $log->subject }}</p>
                                <p class="mt-1 font-mono text-[11px] text-slate-500">{{ $log->template_key }}</p>
                            </div>
                            <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                                @if($log->application)
                                    <a href="{{ route('admin.applications.show', $log->application) }}" class="font-mono font-bold text-brand-blue hover:text-brand-orange-dark">Application {{ $log->application->reference }} →</a>
                                @else
                                    <span class="text-slate-500">No application linked</span>
                                @endif
                                @if($log->error)
                                    <span class="text-right font-semibold text-rose-700">Needs attention</span>
                                @elseif($log->status === 'sent')
                                    <span class="font-semibold text-emerald-700">Accepted by mail server</span>
                                @else
                                    <span class="text-slate-500">No error details</span>
                                @endif
                            </div>
                            @if($log->error)
                                <p class="break-words rounded-lg bg-rose-50 p-3 text-xs leading-5 text-rose-800">{{ $log->error }}</p>
                            @endif
                            @if($log->status === 'failed')
                                <form method="POST" action="{{ route('admin.email-logs.retry', $log) }}" data-confirm="Queue a retry for this failed email?">
                                    @csrf
                                    <button type="submit" class="inline-flex min-h-10 w-full items-center justify-center rounded-lg border border-brand-blue px-4 py-2 text-sm font-bold text-brand-blue transition hover:bg-blue-50">Retry email now</button>
                                </form>
                            @endif
                        </article>
                    @endforeach
                </div>

                <div class="hidden overflow-x-auto md:block">
                    <table class="w-full min-w-[880px] text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th scope="col" class="px-5 py-3 sm:px-6">Created</th>
                                <th scope="col" class="px-5 py-3">Recipient</th>
                                <th scope="col" class="px-5 py-3">Notification</th>
                                <th scope="col" class="px-5 py-3">Application</th>
                                <th scope="col" class="px-5 py-3">Status</th>
                                <th scope="col" class="px-5 py-3">Delivery detail</th>
                                <th scope="col" class="px-5 py-3">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($logs as $log)
                                <tr class="align-top hover:bg-slate-50/80">
                                    <td class="whitespace-nowrap px-5 py-4 text-slate-600 sm:px-6">{{ $log->created_at?->format('j M Y, g:i a') ?? '—' }}</td>
                                    <td class="px-5 py-4 text-slate-800">{{ $log->to_email }}</td>
                                    <td class="max-w-sm px-5 py-4">
                                        <p class="font-semibold text-slate-800">{{ $log->subject }}</p>
                                        <p class="mt-1 font-mono text-xs text-slate-500">{{ $log->template_key }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        @if($log->application)
                                            <a href="{{ route('admin.applications.show', $log->application) }}" class="font-mono text-xs font-bold text-brand-blue hover:text-brand-orange-dark">{{ $log->application->reference }}</a>
                                        @else
                                            <span class="text-slate-500">No application</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <span @class([
                                            'inline-flex rounded-full px-2.5 py-1 text-xs font-bold',
                                            'bg-emerald-50 text-emerald-800' => $log->status === 'sent',
                                            'bg-amber-50 text-amber-800' => in_array($log->status, ['sending', 'queued'], true),
                                            'bg-rose-50 text-rose-800' => $log->status === 'failed',
                                            'bg-slate-100 text-slate-700' => ! in_array($log->status, ['sent', 'sending', 'queued', 'failed'], true),
                                        ])>{{ \Illuminate\Support\Str::headline($log->status) }}</span>
                                    </td>
                                    <td class="max-w-xs px-5 py-4">
                                        @if($log->error)
                                            <p class="whitespace-pre-line break-words text-xs leading-5 text-rose-700">{{ $log->error }}</p>
                                        @else
                                            <span class="text-xs text-slate-500">{{ $log->status === 'sent' ? 'Accepted by mail server' : 'No error details recorded' }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        @if($log->status === 'failed')
                                            <form method="POST" action="{{ route('admin.email-logs.retry', $log) }}" data-confirm="Queue a retry for this failed email?">
                                                @csrf
                                                <button type="submit" class="inline-flex min-h-9 items-center justify-center whitespace-nowrap rounded-lg border border-brand-blue px-3 py-1.5 text-xs font-bold text-brand-blue transition hover:bg-blue-50">Retry email</button>
                                            </form>
                                        @else
                                            <span class="text-xs text-slate-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-100 px-5 py-4 sm:px-6">{{ $logs->links() }}</div>
            @endif
        </section>
    </div>
@endsection
