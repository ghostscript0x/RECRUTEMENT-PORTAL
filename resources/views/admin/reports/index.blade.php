@extends('layouts.admin')

@section('title', 'Reports')

@section('content')
    @php($fieldClass = 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition focus:border-blue-600 focus:ring-4 focus:ring-blue-100')
    <div class="mx-auto max-w-7xl space-y-5">
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand-orange">Recruitment insights</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Reports</h1>
                <p class="mt-1 text-sm text-slate-600">Understand where applications come from and how candidates are progressing.</p>
            </div>
            @can('exports.run')
                <div class="flex gap-2">
                    <a href="{{ route('admin.applications.export', array_filter($filters + ['format' => 'csv'])) }}" class="rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm font-bold text-slate-700 hover:border-brand-blue hover:text-brand-blue">Export CSV</a>
                    <a href="{{ route('admin.applications.export', array_filter($filters + ['format' => 'xlsx'])) }}" class="brand-button rounded-lg px-3 py-2.5 text-sm font-bold">Export Excel</a>
                </div>
            @endcan
        </header>

        <form method="GET" action="{{ route('admin.reports.index') }}" class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-2 xl:grid-cols-[1fr_1fr_1.4fr_auto] sm:p-5">
            <div>
                <label for="from" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">From date</label>
                <input id="from" type="date" name="from" value="{{ $filters['from'] }}" class="{{ $fieldClass }}">
            </div>
            <div>
                <label for="to" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">To date</label>
                <input id="to" type="date" name="to" value="{{ $filters['to'] }}" class="{{ $fieldClass }}">
                @error('to')<p class="mt-1 text-xs font-medium text-rose-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="position_id" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Position</label>
                <select id="position_id" name="position_id" class="{{ $fieldClass }}">
                    <option value="">All positions</option>
                    @foreach($positions as $position)
                        <option value="{{ $position->id }}" @selected((string) $filters['position_id'] === (string) $position->id)>{{ $position->title }}</option>
                    @endforeach
                </select>
                @error('position_id')<p class="mt-1 text-xs font-medium text-rose-700">{{ $message }}</p>@enderror
            </div>
            <div class="flex items-end gap-2">
                <a href="{{ route('admin.reports.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Clear</a>
                <button type="submit" class="brand-button rounded-lg px-4 py-2.5 text-sm font-bold">Update</button>
            </div>
        </form>

        <section aria-label="Recruitment outcomes">
            <dl class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @foreach([
                    ['Applications received', $total, 'All submissions in this date range'],
                    ['Shortlisted', $shortlisted, 'Candidates progressed'],
                    ['Selected', $selected, 'Successful outcomes'],
                    ['Rejected', $rejected, 'Closed applications'],
                ] as [$label, $value, $hint])
                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <dt class="text-sm font-semibold text-slate-600">{{ $label }}</dt>
                        <dd class="mt-3 text-3xl font-extrabold tracking-tight text-brand-blue">{{ number_format($value) }}</dd>
                        <p class="mt-1 text-xs text-slate-500">{{ $hint }}</p>
                    </div>
                @endforeach
            </dl>
        </section>

        <div class="grid gap-5 lg:grid-cols-2">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <h2 class="font-bold text-slate-900">Applications by status</h2>
                <p class="mt-1 text-sm text-slate-500">Current pipeline for the selected period.</p>
                <ul class="mt-5 space-y-4">
                    @foreach(\App\Enums\ApplicationStatus::cases() as $status)
                        @php($count = (int) $byStatus->get($status->value, 0))
                        <li>
                            <div class="flex items-center justify-between gap-3 text-sm">
                                <span class="font-medium text-slate-700">{{ $status->label() }}</span>
                                <span class="font-bold text-slate-900">{{ number_format($count) }}</span>
                            </div>
                            <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-brand-orange" style="width: {{ $total > 0 ? round($count / $total * 100, 1) : 0 }}%"></div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <h2 class="font-bold text-slate-900">Applications by position</h2>
                <p class="mt-1 text-sm text-slate-500">Compare interest across recruitment opportunities.</p>
                <ul class="mt-5 divide-y divide-slate-100">
                    @forelse($byPosition as $position)
                        <li class="flex items-center justify-between gap-4 py-3 first:pt-0 last:pb-0">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-800">{{ $position->title }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">{{ $position->department ?: 'No department specified' }}</p>
                            </div>
                            <span class="shrink-0 rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-brand-blue">{{ number_format($position->applications_count) }}</span>
                        </li>
                    @empty
                        <li class="py-6 text-center text-sm text-slate-500">No positions found.</li>
                    @endforelse
                </ul>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <h2 class="font-bold text-slate-900">Applications by state</h2>
                <p class="mt-1 text-sm text-slate-500">State of current residence.</p>
                <ul class="mt-5 divide-y divide-slate-100">
                    @forelse($byState as $state)
                        <li class="flex items-center justify-between gap-4 py-3 first:pt-0 last:pb-0">
                            <span class="text-sm font-medium text-slate-700">{{ $state->name }}</span>
                            <span class="text-sm font-bold text-slate-900">{{ number_format($state->total) }}</span>
                        </li>
                    @empty
                        <li class="py-6 text-center text-sm text-slate-500">No state information in this period.</li>
                    @endforelse
                </ul>
            </section>

            <section class="grid gap-5 sm:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="font-bold text-slate-900">By qualification</h2>
                    <ul class="mt-4 space-y-3">
                        @forelse($byQualification as $qualification)
                            <li class="flex items-center justify-between gap-3 text-sm">
                                <span class="text-slate-600">{{ $qualification->label }}</span>
                                <span class="font-bold text-slate-900">{{ number_format($qualification->total) }}</span>
                            </li>
                        @empty
                            <li class="text-sm text-slate-500">No data.</li>
                        @endforelse
                    </ul>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="font-bold text-slate-900">By gender</h2>
                    <ul class="mt-4 space-y-3">
                        @forelse($byGender as $gender)
                            <li class="flex items-center justify-between gap-3 text-sm">
                                <span class="capitalize text-slate-600">{{ $gender->label }}</span>
                                <span class="font-bold text-slate-900">{{ number_format($gender->total) }}</span>
                            </li>
                        @empty
                            <li class="text-sm text-slate-500">No data.</li>
                        @endforelse
                    </ul>
                </div>
            </section>
        </div>
    </div>
@endsection
