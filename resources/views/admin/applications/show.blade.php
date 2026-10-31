@extends('layouts.admin')

@section('title', 'Application review')

@section('content')
    <div class="mx-auto max-w-7xl space-y-5">
        @php($passportPhoto = $application->documents->firstWhere('type', 'passport'))
        <a href="{{ route('admin.applications.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-brand-blue hover:text-brand-orange-dark"><span aria-hidden="true">←</span> All applications</a>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="flex min-w-0 items-start gap-4">
                    @if(in_array($passportPhoto?->mime_type, ['image/jpeg', 'image/png', 'image/webp'], true))
                        <img src="{{ route('admin.applications.documents', [$application, $passportPhoto]) }}"
                             alt="Passport photograph of {{ $application->full_name }}"
                             class="h-12 w-12 shrink-0 rounded-xl object-cover"
                             width="48" height="48">
                    @else
                        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-blue-50 text-lg font-extrabold text-brand-blue">{{ mb_strtoupper(mb_substr($application->first_name, 0, 1).mb_substr($application->last_name, 0, 1)) }}</span>
                    @endif
                    <div class="min-w-0">
                        <p class="font-mono text-xs font-bold tracking-wide text-brand-orange">{{ $application->reference }}</p>
                        <h1 class="mt-1 break-words text-2xl font-extrabold tracking-tight text-slate-950">{{ $application->full_name }}</h1>
                        <p class="mt-1 text-sm text-slate-600">{{ $application->position?->title ?? 'Position unavailable' }}@if($application->position?->department) <span class="text-slate-400">·</span> {{ $application->position->department }}@endif</p>
                        <p class="mt-2 text-xs text-slate-500">Submitted {{ $application->submitted_at?->format('j F Y, g:i a') ?? 'date unavailable' }}</p>
                    </div>
                </div>
                <x-status-badge :status="$application->status" />
            </div>

            @can('interviews.manage')
                @if(in_array($application->status, ['shortlisted', 'interview'], true))
                    <div class="mt-5 border-t border-slate-100 pt-4">
                        <a href="{{ route('admin.interviews.edit', $application) }}" class="brand-button inline-flex min-h-10 items-center justify-center rounded-lg px-4 py-2 text-sm font-bold">
                            {{ $application->interviews->first()?->scheduled_at ? 'Manage interview' : 'Schedule interview' }}
                        </a>
                    </div>
                @endif
            @endcan

            @can('applications.status')
                <form method="POST" action="{{ route('admin.applications.status', $application) }}" class="mt-6 grid gap-3 rounded-xl border border-blue-100 bg-blue-50/60 p-4 sm:grid-cols-[minmax(0,1fr)_minmax(0,2fr)_auto] sm:items-end">
                    @csrf
                    <div>
                        <label for="status" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-600">Update application status</label>
                        <select id="status" name="status" required class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
                            @foreach($statuses as $status)
                                <option value="{{ $status->value }}" @selected(old('status', $application->status) === $status->value)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                        @error('status')<p class="mt-1 text-xs font-medium text-rose-700">{{ $message }}</p>@enderror
                        <p class="mt-1 text-xs leading-5 text-slate-500">Moving a prepared applicant to Interview queues their invitation automatically.</p>
                    </div>
                    <div>
                        <label for="note" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-600">Internal note <span class="font-normal normal-case text-slate-500">(optional)</span></label>
                        <input id="note" name="note" maxlength="1000" value="{{ old('note') }}" placeholder="Add context for the audit history" class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
                        @error('note')<p class="mt-1 text-xs font-medium text-rose-700">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="brand-button rounded-lg px-4 py-2.5 text-sm font-bold">Save status</button>
                </form>
            @endcan
        </section>

        <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,1fr)_340px]">
            <div class="space-y-5">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <h2 class="text-lg font-bold text-slate-900">Personal and contact information</h2>
                    <dl class="mt-5 grid gap-x-6 gap-y-4 sm:grid-cols-2">
                        @foreach([
                            ['First name', $application->first_name],
                            ['Middle name', $application->middle_name],
                            ['Last name', $application->last_name],
                            ['Email address', $application->email],
                            ['Phone number', $application->phone],
                            ['Date of birth', $application->date_of_birth?->format('j F Y')],
                            ['Gender', ucfirst($application->gender)],
                            ['Residential address', $application->residential_address],
                        ] as [$label, $value])
                            <div>
                                <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ $label }}</dt>
                                <dd class="mt-1 break-words text-sm text-slate-800">{{ filled($value) ? $value : 'Not provided' }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <h2 class="text-lg font-bold text-slate-900">Location</h2>
                    <dl class="mt-5 grid gap-x-6 gap-y-4 sm:grid-cols-2">
                        <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-500">State of origin</dt><dd class="mt-1 text-sm text-slate-800">{{ $application->stateOfOrigin?->name ?? 'Not provided' }}</dd></div>
                        <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-500">LGA of origin</dt><dd class="mt-1 text-sm text-slate-800">{{ $application->originLga?->name ?? 'Not provided' }}</dd></div>
                        <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-500">State of residence</dt><dd class="mt-1 text-sm text-slate-800">{{ $application->residenceState?->name ?? 'Not provided' }}</dd></div>
                        <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-500">LGA of residence</dt><dd class="mt-1 text-sm text-slate-800">{{ $application->residenceLga?->name ?? 'Not provided' }}</dd></div>
                    </dl>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <h2 class="text-lg font-bold text-slate-900">Educational qualifications</h2>
                    <dl class="mt-5 grid gap-x-6 gap-y-4 sm:grid-cols-2">
                        <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Highest qualification</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ $application->highest_qualification }}</dd></div>
                        <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Graduation year</dt><dd class="mt-1 text-sm text-slate-800">{{ $application->graduation_year ?? 'Not provided' }}</dd></div>
                        <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Institution</dt><dd class="mt-1 text-sm text-slate-800">{{ $application->institution ?: 'Not provided' }}</dd></div>
                        <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Course of study</dt><dd class="mt-1 text-sm text-slate-800">{{ $application->course_of_study ?: 'Not provided' }}</dd></div>
                    </dl>
                    @if($application->additional_qualifications)
                        <div class="mt-5 border-t border-slate-100 pt-4">
                            <h3 class="text-xs font-bold uppercase tracking-wide text-slate-500">Additional qualifications</h3>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $application->additional_qualifications }}</p>
                        </div>
                    @endif
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <h2 class="text-lg font-bold text-slate-900">Skills and competencies</h2>
                    @if($application->skills->isEmpty())
                        <p class="mt-3 text-sm text-slate-500">No skills provided.</p>
                    @else
                        <ul class="mt-4 flex flex-wrap gap-2">
                            @foreach($application->skills as $skill)
                                <li class="rounded-full bg-blue-50 px-3 py-1.5 text-sm font-semibold text-brand-blue">{{ $skill->name }}</li>
                            @endforeach
                        </ul>
                    @endif
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Leadership experience</h2>
                            <p class="mt-1 text-sm text-slate-600">{{ $application->has_leadership ? 'Candidate has shared leadership experience.' : 'Candidate has not shared leadership experience.' }}</p>
                        </div>
                    </div>
                    @if($application->has_leadership)
                        <dl class="mt-5 grid gap-x-6 gap-y-4 sm:grid-cols-2">
                            <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Organization</dt><dd class="mt-1 text-sm text-slate-800">{{ $application->leadership_organization }}</dd></div>
                            <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Role</dt><dd class="mt-1 text-sm text-slate-800">{{ $application->leadership_position }}</dd></div>
                            <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Duration</dt><dd class="mt-1 text-sm text-slate-800">{{ $application->leadership_duration ?: 'Not provided' }}</dd></div>
                        </dl>
                        <p class="mt-4 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $application->leadership_description }}</p>
                    @endif
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <h2 class="text-lg font-bold text-slate-900">Personal statement</h2>
                    <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-700">{{ $application->about ?: 'No personal statement provided.' }}</p>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <h2 class="text-lg font-bold text-slate-900">Supporting documents</h2>
                    @if($application->documents->isEmpty())
                        <p class="mt-3 text-sm text-slate-500">No documents uploaded.</p>
                    @else
                        <ul class="mt-4 divide-y divide-slate-100">
                            @foreach($application->documents as $document)
                                <li class="flex flex-wrap items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-slate-800">{{ $document->original_name }}</p>
                                        <p class="mt-0.5 text-xs text-slate-500">{{ ucfirst($document->type) }} · {{ $document->humanSize() }}</p>
                                    </div>
                                    <a href="{{ route('admin.applications.documents', [$application, $document]) }}" target="_blank" rel="noopener" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-brand-blue hover:border-brand-blue">View document</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            </div>

            <aside class="space-y-5">
                @can('applications.assign')
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="text-base font-bold text-slate-900">Reviewer assignment</h2>
                        <p class="mt-1 text-xs leading-5 text-slate-500">Assigned reviewers can access this application and its private review materials.</p>
                        <form method="POST" action="{{ route('admin.applications.reviewer', $application) }}" class="mt-4 space-y-3">
                            @csrf
                            @method('PUT')
                            <div>
                                <label for="reviewer_id" class="mb-1.5 block text-sm font-semibold text-slate-700">Application reviewer</label>
                                <select id="reviewer_id" name="reviewer_id" class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
                                    <option value="">Unassigned</option>
                                    @foreach($reviewers as $reviewer)
                                        <option value="{{ $reviewer->id }}" @selected((string) old('reviewer_id', $application->assigned_reviewer_id) === (string) $reviewer->id)>{{ $reviewer->name }} · {{ $reviewer->email }}</option>
                                    @endforeach
                                </select>
                                @error('reviewer_id')<p class="mt-1 text-xs font-medium text-rose-700">{{ $message }}</p>@enderror
                            </div>
                            @if($reviewers->isEmpty())
                                <p class="text-xs text-slate-500">Create an administrator with the Application Reviewer role before assigning this application.</p>
                            @endif
                            <button type="submit" class="brand-button w-full rounded-lg px-4 py-2.5 text-sm font-bold">Save assignment</button>
                        </form>
                    </section>
                @endcan

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-base font-bold text-slate-900">Application activity</h2>
                    @if($application->statusHistory->isEmpty())
                        <p class="mt-3 text-sm text-slate-500">No status changes recorded yet.</p>
                    @else
                        <ol class="mt-5 space-y-5 border-l-2 border-slate-100 pl-5">
                            @foreach($application->statusHistory as $history)
                                @php($historyStatus = \App\Enums\ApplicationStatus::tryFrom($history->to_status))
                                <li class="relative">
                                    <span class="absolute -left-[1.65rem] top-1 h-3 w-3 rounded-full border-2 border-white bg-brand-orange ring-1 ring-orange-200"></span>
                                    <p class="text-sm font-bold text-slate-800">{{ $historyStatus?->label() ?? ucfirst(str_replace('_', ' ', $history->to_status)) }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $history->user?->name ?? 'System' }} · {{ $history->created_at?->format('j M Y, g:i a') }}</p>
                                    @if($history->note)
                                        <p class="mt-2 rounded-lg bg-slate-50 p-2.5 text-xs leading-5 text-slate-600">{{ $history->note }}</p>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-base font-bold text-slate-900">Private reviewer notes</h2>
                    <p class="mt-1 text-xs leading-5 text-slate-500">Visible only to administrators. Never shown to the applicant.</p>
                    @can('applications.notes')
                        <form method="POST" action="{{ route('admin.applications.notes', $application) }}" class="mt-4 space-y-3">
                            @csrf
                            <label for="body" class="sr-only">Add a private note</label>
                            <textarea id="body" name="body" rows="4" maxlength="2000" required placeholder="Add a note for the review team…" class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-100">{{ old('body') }}</textarea>
                            @error('body')<p class="text-xs font-medium text-rose-700">{{ $message }}</p>@enderror
                            <button type="submit" class="brand-button w-full rounded-lg px-4 py-2.5 text-sm font-bold">Add private note</button>
                        </form>
                    @endcan
                    <ul class="mt-5 space-y-3">
                        @forelse($application->notes as $note)
                            <li class="rounded-xl border border-slate-100 bg-slate-50 p-3">
                                <p class="whitespace-pre-line text-sm leading-6 text-slate-700">{{ $note->body }}</p>
                                <p class="mt-2 text-xs text-slate-500">{{ $note->user?->name ?? 'Administrator' }} · {{ $note->created_at?->format('j M Y, g:i a') }}</p>
                            </li>
                        @empty
                            <li class="text-xs text-slate-500">No reviewer notes yet.</li>
                        @endforelse
                    </ul>
                </section>
            </aside>
        </div>
    </div>
@endsection
