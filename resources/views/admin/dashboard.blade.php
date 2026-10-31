@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <section class="flex flex-col gap-5 rounded-2xl bg-gradient-to-br from-brand-blue to-brand-blue-dark p-6 text-white shadow-sm sm:flex-row sm:items-end sm:justify-between sm:p-8">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-200">FINTEC recruitment</p>
                <h1 class="mt-2 text-2xl font-extrabold tracking-tight sm:text-3xl">Recruitment overview</h1>
                <p class="mt-2 max-w-xl text-sm leading-6 text-blue-100">See what needs attention and keep every application moving.</p>
            </div>
            <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-col gap-2 sm:min-w-64">
                <label for="position_id" class="text-xs font-semibold text-blue-100">View an opportunity</label>
                <select id="position_id" name="position_id" onchange="this.form.submit()" class="rounded-lg border border-white/20 bg-white px-3 py-2.5 text-sm font-medium text-slate-800">
                    <option value="">All positions</option>
                    @foreach($positions as $position)
                        <option value="{{ $position->id }}" @selected($positionId === $position->id)>{{ $position->title }}</option>
                    @endforeach
                </select>
            </form>
        </section>

        <section aria-label="Application statistics">
            <dl class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @foreach([
                    ['Total applications', $stats['total'], 'All statuses'],
                    ['New applications', $stats['new'], 'Waiting for first review'],
                    ['Under review', $stats['review'], 'Currently being reviewed'],
                    ['Shortlisted', $stats['shortlisted'], 'Ready for next steps'],
                    ['Interviews', $stats['interview'], 'Interview stage'],
                    ['Selected', $stats['selected'], 'Successful outcomes'],
                    ['Rejected', $stats['rejected'], 'Closed applications'],
                ] as [$label, $value, $hint])
                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <dt class="text-sm font-semibold text-slate-600">{{ $label }}</dt>
                        <dd class="mt-3 text-3xl font-extrabold tracking-tight text-brand-blue">{{ number_format($value) }}</dd>
                        <p class="mt-1 text-xs text-slate-500">{{ $hint }}</p>
                    </div>
                @endforeach
                <div class="rounded-xl border border-orange-200 bg-orange-50 p-5 shadow-sm">
                    <dt class="text-sm font-semibold text-orange-900">Open positions</dt>
                    <dd class="mt-3 text-3xl font-extrabold tracking-tight text-orange-800">{{ number_format($positions->where('status', 'published')->count()) }}</dd>
                    <a href="{{ route('admin.positions.index') }}" class="mt-1 inline-flex text-xs font-bold text-orange-800 hover:text-orange-950">Manage opportunities <span class="ml-1" aria-hidden="true">→</span></a>
                </div>
            </dl>
        </section>

        <section class="grid gap-3 md:grid-cols-2 xl:grid-cols-4" aria-label="Quick actions">
            @php($quickActions = [
                ['label' => 'Review candidates', 'href' => route('admin.applications.index'), 'description' => 'Open the applicant pipeline', 'tone' => 'brand'],
                ['label' => 'Manage positions', 'href' => route('admin.positions.index'), 'description' => 'Create or update roles', 'tone' => 'amber'],
                ['label' => 'Check interviews', 'href' => route('admin.interviews.index'), 'description' => 'See what is scheduled next', 'tone' => 'blue'],
                ['label' => 'Delivery logs', 'href' => route('admin.email-logs.index'), 'description' => 'Retry failed applicant emails', 'tone' => 'rose'],
            ])

            @foreach($quickActions as $action)
                <a href="{{ $action['href'] }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-blue hover:shadow-md">
                    <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">Quick action</p>
                    <h2 class="mt-2 text-lg font-bold text-slate-900">{{ $action['label'] }}</h2>
                    <p class="mt-1 text-sm text-slate-600">{{ $action['description'] }}</p>
                    <span class="mt-3 inline-flex text-sm font-bold text-brand-blue">Open <span class="ml-1" aria-hidden="true">→</span></span>
                </a>
            @endforeach
        </section>

        <section class="grid gap-4 lg:grid-cols-2" aria-label="Recruiter follow-up">
            @can('templates.manage')
                <article class="rounded-2xl border {{ $failedEmailCount > 0 ? 'border-rose-200 bg-rose-50' : 'border-slate-200 bg-white' }} p-5 shadow-sm sm:p-6">
                    <p class="text-xs font-bold uppercase tracking-[0.12em] {{ $failedEmailCount > 0 ? 'text-rose-700' : 'text-slate-500' }}">Email delivery</p>
                    <h2 class="mt-2 text-lg font-bold text-slate-900">
                        {{ number_format($failedEmailCount) }} failed {{ \Illuminate\Support\Str::plural('email', $failedEmailCount) }}
                    </h2>
                    <p class="mt-1 text-sm leading-6 text-slate-600">
                        {{ $failedEmailCount > 0 ? 'Review failed messages and retry delivery where appropriate.' : 'No failed applicant emails need attention.' }}
                    </p>
                    <a href="{{ route('admin.email-logs.index', $failedEmailCount > 0 ? ['status' => 'failed'] : []) }}" class="mt-4 inline-flex min-h-10 items-center font-bold text-brand-blue hover:text-brand-orange-dark">
                        {{ $failedEmailCount > 0 ? 'Review failed emails' : 'View delivery history' }} <span class="ml-1" aria-hidden="true">→</span>
                    </a>
                </article>
            @endcan

            @can('interviews.manage')
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.12em] text-brand-orange">Next steps</p>
                            <h2 class="mt-2 text-lg font-bold text-slate-900">Upcoming interviews</h2>
                        </div>
                        <a href="{{ route('admin.interviews.index') }}" class="shrink-0 text-sm font-bold text-brand-blue hover:text-brand-orange-dark">View all <span aria-hidden="true">→</span></a>
                    </div>
                    @if($upcomingInterviews->isEmpty())
                        <p class="mt-4 text-sm leading-6 text-slate-600">No scheduled interviews are coming up. Prepare an invitation from the Interviews page when a candidate is shortlisted.</p>
                    @else
                        <ul class="mt-3 divide-y divide-slate-100">
                            @foreach($upcomingInterviews as $interview)
                                <li class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-slate-900">{{ $interview->application->full_name }}</p>
                                        <p class="mt-1 text-xs text-slate-600">
                                            {{ $interview->scheduled_at?->setTimezone($timezone ?? config('recruitment.timezone'))->format('j M, g:i A') }}
                                            · {{ $interview->channelLabel() }}
                                            @if($interview->status === 'reschedule_requested')
                                                · <span class="font-bold text-orange-800">Reschedule requested</span>
                                            @endif
                                        </p>
                                    </div>
                                    <a href="{{ route('admin.interviews.edit', $interview->application) }}" class="shrink-0 text-sm font-bold text-brand-blue hover:text-brand-orange-dark">Open</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </article>
            @endcan
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 sm:px-6">
                <div>
                    <h2 class="font-bold text-slate-900">Latest applications</h2>
                    <p class="mt-1 text-sm text-slate-500">The most recent submissions across your recruitment.</p>
                </div>
                @can('applications.view')
                    <a href="{{ route('admin.applications.index', $positionId ? ['position_id' => $positionId] : []) }}" class="text-sm font-bold text-brand-blue hover:text-brand-orange-dark">View all applications <span aria-hidden="true">→</span></a>
                @endcan
            </div>

            @if($recent->isEmpty())
                <div class="px-6 py-14 text-center">
                    <span class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-blue-50 text-brand-blue" aria-hidden="true">↗</span>
                    <h3 class="mt-4 font-bold text-slate-900">No applications yet</h3>
                    <p class="mt-1 text-sm text-slate-500">New submissions will appear here as soon as they arrive.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th scope="col" class="px-5 py-3 sm:px-6">Applicant</th>
                                <th scope="col" class="px-5 py-3">Position</th>
                                <th scope="col" class="px-5 py-3">Residence</th>
                                <th scope="col" class="px-5 py-3">Status</th>
                                <th scope="col" class="px-5 py-3">Submitted</th>
                                <th scope="col" class="px-5 py-3 text-right sm:px-6">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($recent as $application)
                                <tr class="transition hover:bg-slate-50/80">
                                    <td class="px-5 py-4 sm:px-6">
                                        <p class="font-semibold text-slate-900">{{ $application->full_name }}</p>
                                        <p class="mt-0.5 font-mono text-xs text-slate-500">{{ $application->reference }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-slate-700">{{ $application->position?->title ?? 'Position unavailable' }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ $application->residenceState?->name ?? '—' }}</td>
                                    <td class="px-5 py-4"><x-status-badge :status="$application->status" /></td>
                                    <td class="px-5 py-4 text-slate-600">{{ $application->submitted_at?->format('j M Y') ?? '—' }}</td>
                                    <td class="px-5 py-4 text-right sm:px-6">
                                        <a href="{{ route('admin.applications.show', $application) }}" class="font-bold text-brand-blue hover:text-brand-orange-dark">Review</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="grid gap-4 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="font-bold text-slate-900">Positions by applications</h2>
                        <p class="mt-1 text-sm text-slate-500">Where candidates are applying.</p>
                    </div>
                    @can('positions.manage')
                        <a href="{{ route('admin.positions.create') }}" class="brand-button rounded-lg px-3 py-2 text-xs font-bold">Add position</a>
                    @endcan
                </div>
                <ul class="mt-5 divide-y divide-slate-100">
                    @forelse($positions->take(5) as $position)
                        <li class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-800">{{ $position->title }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">{{ \App\Models\Position::STATUSES[$position->status] ?? ucfirst($position->status) }}</p>
                            </div>
                            <span class="shrink-0 rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-brand-blue">{{ number_format($position->applications_count) }} {{ \Illuminate\Support\Str::plural('application', $position->applications_count) }}</span>
                        </li>
                    @empty
                        <li class="py-6 text-center text-sm text-slate-500">No positions to show.</li>
                    @endforelse
                </ul>
            </div>

            <div class="rounded-2xl border border-blue-100 bg-blue-50/60 p-5 sm:p-6">
                <p class="text-xs font-bold uppercase tracking-[0.12em] text-brand-blue">A smoother review workflow</p>
                <h2 class="mt-2 text-lg font-bold text-slate-900">Keep every candidate in the loop</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">Update application statuses to record progress and send the configured applicant notification.</p>
                @can('applications.view')
                    <a href="{{ route('admin.applications.index', ['status' => 'submitted']) }}" class="mt-5 inline-flex items-center gap-2 font-bold text-brand-blue hover:text-brand-orange-dark">Review new applications <span aria-hidden="true">→</span></a>
                @endcan
            </div>
        </section>
    </div>
@endsection
