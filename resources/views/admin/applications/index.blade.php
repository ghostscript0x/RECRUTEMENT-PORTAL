@extends('layouts.admin')

@section('title', 'Applications')

@section('content')
    @php($fieldClass = 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition focus:border-blue-600 focus:ring-4 focus:ring-blue-100')
    @php($filters = $filters + ['q' => '', 'status' => '', 'position_id' => '', 'state_id' => '', 'lga_id' => '', 'qualification' => '', 'gender' => '', 'leadership' => '', 'from' => '', 'to' => ''])
    <div class="mx-auto max-w-7xl space-y-5">
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand-orange">Candidate pipeline</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Applications</h1>
                <p class="mt-1 text-sm text-slate-600">Search, filter and review candidate submissions.</p>
            </div>
            @can('exports.run')
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.applications.export', array_merge(request()->query(), ['format' => 'csv'])) }}" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-bold text-slate-700 transition hover:border-brand-blue hover:text-brand-blue">Export CSV</a>
                    <a href="{{ route('admin.applications.export', array_merge(request()->query(), ['format' => 'xlsx'])) }}" class="brand-button rounded-lg px-3 py-2 text-sm font-bold">Export Excel</a>
                </div>
            @endcan
        </header>

        <form method="GET" action="{{ route('admin.applications.index') }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="q" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Search applications</label>
                    <input id="q" name="q" value="{{ $filters['q'] }}" placeholder="Name, email, phone or reference number" class="{{ $fieldClass }}">
                </div>
                <div>
                    <label for="status" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Status</label>
                    <select id="status" name="status" class="{{ $fieldClass }}">
                        <option value="">All statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->value }}" @selected($filters['status'] === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="position_id" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Position</label>
                    <select id="position_id" name="position_id" class="{{ $fieldClass }}">
                        <option value="">All positions</option>
                        @foreach($positions as $position)
                            <option value="{{ $position->id }}" @selected((string) $filters['position_id'] === (string) $position->id)>{{ $position->title }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <details class="mt-4 rounded-xl border border-slate-200" @if($filters['state_id'] || $filters['lga_id'] || $filters['qualification'] || $filters['gender'] || $filters['leadership'] || $filters['from'] || $filters['to']) open @endif>
                <summary class="cursor-pointer rounded-xl px-4 py-3 text-sm font-semibold text-brand-blue hover:bg-blue-50">
                    More filters <span class="ml-1 text-xs font-normal text-slate-500">Location, qualifications, experience and dates</span>
                </summary>
                <div class="grid gap-4 border-t border-slate-100 p-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <label for="state_id" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">State of residence</label>
                        <select id="state_id" name="state_id" class="{{ $fieldClass }}">
                            <option value="">All states</option>
                            @foreach($states as $state)
                                <option value="{{ $state->id }}" @selected((string) $filters['state_id'] === (string) $state->id)>{{ $state->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="lga_id" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Local government area</label>
                        <select id="lga_id" name="lga_id" class="{{ $fieldClass }}" @disabled($localGovernments->isEmpty())>
                            <option value="">{{ $localGovernments->isEmpty() ? 'Choose a state first' : 'All local areas' }}</option>
                            @foreach($localGovernments as $localGovernment)
                                <option value="{{ $localGovernment->id }}" @selected((string) $filters['lga_id'] === (string) $localGovernment->id)>{{ $localGovernment->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="qualification" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Qualification</label>
                        <select id="qualification" name="qualification" class="{{ $fieldClass }}">
                            <option value="">All qualifications</option>
                            @foreach($qualifications as $qualification)
                                <option value="{{ $qualification }}" @selected($filters['qualification'] === $qualification)>{{ $qualification }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="gender" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Gender</label>
                        <select id="gender" name="gender" class="{{ $fieldClass }}">
                            <option value="">All</option>
                            <option value="male" @selected($filters['gender'] === 'male')>Male</option>
                            <option value="female" @selected($filters['gender'] === 'female')>Female</option>
                            <option value="other" @selected($filters['gender'] === 'other')>Other</option>
                        </select>
                    </div>
                    <div>
                        <label for="leadership" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Leadership experience</label>
                        <select id="leadership" name="leadership" class="{{ $fieldClass }}">
                            <option value="">All applicants</option>
                            <option value="yes" @selected($filters['leadership'] === 'yes')>Has experience</option>
                            <option value="no" @selected($filters['leadership'] === 'no')>No experience</option>
                        </select>
                    </div>
                    <div>
                        <label for="from" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Submitted from</label>
                        <input id="from" type="date" name="from" value="{{ $filters['from'] }}" class="{{ $fieldClass }}">
                    </div>
                    <div>
                        <label for="to" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Submitted to</label>
                        <input id="to" type="date" name="to" value="{{ $filters['to'] }}" class="{{ $fieldClass }}">
                    </div>
                </div>
            </details>

            <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
                <p class="text-xs text-slate-500">Filters work together. Use search for applicant name, email, phone or reference.</p>
                <div class="flex flex-wrap gap-2">
                    <button type="button" data-reset-form class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Reset</button>
                    <a href="{{ route('admin.applications.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Clear all</a>
                    <button type="submit" class="brand-button rounded-lg px-5 py-2.5 text-sm font-bold">Apply filters</button>
                </div>
            </div>
        </form>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 sm:px-6">
                <div>
                    <h2 class="font-bold text-slate-900">Candidate records</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ number_format($applications->total()) }} {{ \Illuminate\Support\Str::plural('application', $applications->total()) }} found</p>
                </div>
                <label class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                    Sort
                    <select class="rounded-lg border border-slate-300 bg-white px-2.5 py-2 text-xs text-slate-700" onchange="const params = new URLSearchParams(window.location.search); params.set('sort', this.value); params.delete('page'); window.location.search = params.toString();">
                        @foreach(['submitted_at' => 'Date submitted', 'reference' => 'Reference', 'first_name' => 'First name', 'last_name' => 'Last name', 'status' => 'Status'] as $value => $label)
                            <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <a href="{{ route('admin.applications.index', array_merge(request()->query(), ['direction' => $direction === 'asc' ? 'desc' : 'asc'])) }}" class="rounded-lg border border-slate-300 px-2.5 py-2 text-slate-700 hover:text-brand-blue" aria-label="Change sort direction">{{ $direction === 'asc' ? 'Ascending' : 'Descending' }}</a>
                </label>
            </div>

            @if($applications->isEmpty())
                <div class="px-6 py-14 text-center">
                    <h3 class="font-bold text-slate-900">No matching applications</h3>
                    <p class="mt-1 text-sm text-slate-500">Try changing or clearing the filters.</p>
                </div>
            @else
                <div class="divide-y divide-slate-100 xl:hidden" aria-label="Mobile application list">
                    @foreach($applications as $application)
                        <article class="space-y-3 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="break-words text-sm font-bold text-slate-900">{{ $application->full_name }}</h3>
                                    <p class="mt-1 font-mono text-xs text-slate-500">{{ $application->reference }}</p>
                                </div>
                                <x-status-badge :status="$application->status" />
                            </div>
                            <dl class="grid grid-cols-2 gap-3 rounded-xl bg-slate-50 p-3 text-xs">
                                <div class="min-w-0">
                                    <dt class="font-semibold text-slate-500">Position</dt>
                                    <dd class="mt-1 break-words text-slate-800">{{ $application->position?->title ?? '—' }}</dd>
                                </div>
                                <div class="min-w-0">
                                    <dt class="font-semibold text-slate-500">Residence</dt>
                                    <dd class="mt-1 break-words text-slate-800">{{ $application->residenceState?->name ?? '—' }}</dd>
                                </div>
                                <div class="min-w-0">
                                    <dt class="font-semibold text-slate-500">Contact</dt>
                                    <dd class="mt-1 break-all text-slate-800">{{ $application->email }}</dd>
                                    <dd class="mt-0.5 text-slate-600">{{ $application->phone }}</dd>
                                </div>
                                <div>
                                    <dt class="font-semibold text-slate-500">Submitted</dt>
                                    <dd class="mt-1 text-slate-800">{{ $application->submitted_at?->format('j M Y') ?? '—' }}</dd>
                                </div>
                            </dl>
                            <a href="{{ route('admin.applications.show', $application) }}" class="inline-flex min-h-10 w-full items-center justify-center rounded-lg border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-bold text-brand-blue hover:border-brand-blue hover:bg-blue-100">
                                Review application <span class="ml-1" aria-hidden="true">→</span>
                            </a>
                        </article>
                    @endforeach
                </div>
                <div class="hidden overflow-x-auto xl:block">
                    <table class="w-full min-w-[980px] text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th scope="col" class="px-5 py-3 sm:px-6">Applicant</th>
                                <th scope="col" class="px-5 py-3">Contact</th>
                                <th scope="col" class="px-5 py-3">Position</th>
                                <th scope="col" class="px-5 py-3">Residence</th>
                                <th scope="col" class="px-5 py-3">Qualification</th>
                                <th scope="col" class="px-5 py-3">Status</th>
                                <th scope="col" class="px-5 py-3">Submitted</th>
                                <th scope="col" class="px-5 py-3 text-right sm:px-6">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($applications as $application)
                                <tr class="transition hover:bg-slate-50/80">
                                    <td class="px-5 py-4 sm:px-6">
                                        <p class="font-semibold text-slate-900">{{ $application->full_name }}</p>
                                        <p class="mt-0.5 font-mono text-xs text-slate-500">{{ $application->reference }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="text-slate-700">{{ $application->email }}</p>
                                        <p class="mt-0.5 text-xs text-slate-500">{{ $application->phone }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-slate-700">{{ $application->position?->title ?? '—' }}</td>
                                    <td class="px-5 py-4 text-slate-700">{{ $application->residenceState?->name ?? '—' }}</td>
                                    <td class="px-5 py-4 text-slate-700">{{ $application->highest_qualification }}</td>
                                    <td class="px-5 py-4"><x-status-badge :status="$application->status" /></td>
                                    <td class="px-5 py-4 whitespace-nowrap text-slate-600">{{ $application->submitted_at?->format('j M Y') ?? '—' }}</td>
                                    <td class="px-5 py-4 text-right sm:px-6">
                                        <a href="{{ route('admin.applications.show', $application) }}" class="font-bold text-brand-blue hover:text-brand-orange-dark">Review</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-100 px-5 py-4 sm:px-6">{{ $applications->links() }}</div>
            @endif
        </section>
    </div>
@endsection
