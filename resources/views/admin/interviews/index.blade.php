@extends('layouts.admin')

@section('title', 'Interviews')

@section('content')
    <div class="mx-auto max-w-7xl space-y-5">
        <header class="border-b border-slate-200 pb-5">
            <p class="text-xs font-bold uppercase tracking-[0.12em] text-brand-orange">Candidate coordination</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Interviews</h1>
            <p class="mt-1 max-w-2xl text-sm leading-6 text-slate-600">Choose the interview channel, add your own meeting details, and write the invitation for each candidate.</p>
        </header>

        <section class="rounded-2xl border border-blue-100 bg-blue-50/70 p-5 sm:p-6" aria-labelledby="interview-workflow">
            <h2 id="interview-workflow" class="font-bold text-slate-900">You’re in control of how interviews happen</h2>
            <p class="mt-1 max-w-3xl text-sm leading-6 text-slate-700">Create a Google Meet, Zoom, WhatsApp, or other meeting separately, then paste its link or contact details into the candidate’s interview. Save a draft first, or send the invitation and move the applicant to Interview in one step. Changing an applicant’s status to Interview also sends a prepared invitation.</p>
            <p class="mt-2 text-xs text-slate-600">Interview dates and times use {{ $timezone }}{{ $timezone === 'Africa/Lagos' ? ' (WAT)' : '' }}.</p>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <h2 class="font-bold text-slate-900">Shortlisted and interview-stage applicants</h2>
                <p class="mt-1 text-sm text-slate-500">Each invitation is composed for the candidate and includes their interview details and reschedule link.</p>
            </div>

            @if($applications->isEmpty())
                <div class="px-6 py-14 text-center">
                    <span class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-blue-50 text-brand-blue" aria-hidden="true">◎</span>
                    <h3 class="mt-4 font-bold text-slate-900">No candidates ready for an interview</h3>
                    <p class="mt-1 text-sm text-slate-500">Shortlist an applicant to start coordinating an interview.</p>
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach($applications as $application)
                        @php($interview = $application->interviews->first())
                        <article class="flex flex-col gap-4 px-5 py-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-bold text-slate-900">{{ $application->full_name }}</h3>
                                    @if($interview?->status === 'reschedule_requested')
                                        <span class="inline-flex rounded-full bg-orange-100 px-2.5 py-1 text-xs font-bold text-orange-800">Reschedule requested</span>
                                    @elseif($interview?->scheduled_at)
                                        <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-800">Scheduled</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700">Needs a time</span>
                                    @endif
                                    @if($interview?->invitation_sent_at)
                                        <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-800">Invitation queued</span>
                                    @endif
                                </div>
                                <p class="mt-1 text-sm text-slate-600">{{ $application->position?->title }} <span class="text-slate-400">·</span> {{ $application->email }} <span class="text-slate-400">·</span> <span class="font-mono text-xs">{{ $application->reference }}</span></p>
                                @if($interview?->scheduled_at)
                                    <p class="mt-2 text-sm font-semibold text-brand-blue">
                                        {{ $interview->scheduled_at->setTimezone($timezone)->format('D, j M Y · g:i A') }}
                                        <span class="font-normal text-slate-500">({{ $interview->channelLabel() }})</span>
                                    </p>
                                    <p class="mt-1 break-words text-sm text-slate-600">{{ $interview->location }}</p>
                                @endif
                                @if($interview?->status === 'reschedule_requested')
                                    <p class="mt-2 rounded-lg border border-orange-100 bg-orange-50 px-3 py-2 text-sm text-orange-950"><span class="font-bold">Candidate’s reason:</span> {{ $interview->reschedule_reason }}</p>
                                @endif
                            </div>
                            <div class="flex shrink-0 flex-wrap gap-2">
                                <a href="{{ route('admin.interviews.edit', $application) }}" class="brand-button inline-flex min-h-10 items-center justify-center rounded-lg px-4 py-2 text-sm font-bold">
                                    {{ $interview?->scheduled_at ? 'Manage interview' : 'Prepare interview' }}
                                </a>
                                <a href="{{ route('admin.applications.show', $application) }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">View application</a>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="border-t border-slate-100 px-5 py-4 sm:px-6">{{ $applications->links() }}</div>
            @endif
        </section>
    </div>
@endsection
