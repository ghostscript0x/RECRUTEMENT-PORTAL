@extends('layouts.admin')

@section('title', 'Prepare interview')

@section('content')
    @php($fieldClass = 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 focus:border-brand-blue focus:ring-4 focus:ring-blue-100')
    <div class="mx-auto max-w-3xl space-y-5">
        <a href="{{ route('admin.interviews.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-brand-blue hover:text-brand-orange-dark"><span aria-hidden="true">←</span> Interview list</a>

        <header>
            <p class="text-xs font-bold uppercase tracking-[0.12em] text-brand-orange">Interview coordination</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">{{ $interview?->scheduled_at ? 'Manage interview' : 'Prepare interview' }}</h1>
            <p class="mt-1 text-sm text-slate-600">{{ $application->full_name }} · {{ $application->position?->title }} · {{ $application->email }}</p>
        </header>

        @error('interview')
            <div class="rounded-xl border border-orange-200 bg-orange-50 p-4 text-sm leading-6 text-orange-950">{{ $message }}</div>
        @enderror

        @if($interview?->status === 'reschedule_requested')
            <section class="rounded-xl border border-orange-200 bg-orange-50 p-4" aria-labelledby="reschedule-request">
                <h2 id="reschedule-request" class="font-bold text-orange-950">Candidate requested a different time</h2>
                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-orange-900">{{ $interview->reschedule_reason }}</p>
                <p class="mt-2 text-xs text-orange-800">Received {{ $interview->reschedule_requested_at?->setTimezone($timezone)->format('j M Y, g:i A') }} ({{ $timezone }}).</p>
            </section>
        @endif

        @if($interview?->invitation_sent_at)
            <p class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-900">
                An invitation was queued {{ $interview->invitation_sent_at->format('j M Y, g:i A') }}. Sending again will queue an updated invitation.
            </p>
        @endif

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
            <form method="POST" action="{{ route('admin.interviews.store', $application) }}" class="space-y-6">
                @csrf

                <div>
                    <div class="mb-3">
                        <h2 class="font-bold text-slate-900">Schedule and contact method</h2>
                        <p class="mt-1 text-sm leading-5 text-slate-600">Create the meeting in your preferred service, then paste its details here. The portal will not create or join meetings for you.</p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="scheduled_at" class="mb-1.5 block text-sm font-semibold text-slate-700">Date and start time <span class="text-brand-orange">*</span></label>
                            <input id="scheduled_at" name="scheduled_at" type="datetime-local" required
                                   value="{{ old('scheduled_at', $interview?->scheduled_at?->setTimezone($timezone)->format('Y-m-d\TH:i')) }}"
                                   class="{{ $fieldClass }}">
                            @error('scheduled_at')<p class="mt-1 text-sm font-semibold text-rose-700">{{ $message }}</p>@enderror
                            <p class="mt-1 text-xs text-slate-500">Times use {{ $timezone }}{{ $timezone === 'Africa/Lagos' ? ' (WAT)' : '' }}.</p>
                        </div>
                        <div>
                            <label for="duration_minutes" class="mb-1.5 block text-sm font-semibold text-slate-700">Duration <span class="text-brand-orange">*</span></label>
                            <select id="duration_minutes" name="duration_minutes" required class="{{ $fieldClass }}">
                                @foreach([15, 30, 45, 60, 90, 120] as $duration)
                                    <option value="{{ $duration }}" @selected((int) old('duration_minutes', $interview?->duration_minutes ?? 30) === $duration)>{{ $duration }} minutes</option>
                                @endforeach
                            </select>
                            @error('duration_minutes')<p class="mt-1 text-sm font-semibold text-rose-700">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="mode" class="mb-1.5 block text-sm font-semibold text-slate-700">Interview channel <span class="text-brand-orange">*</span></label>
                        <select id="mode" name="mode" required class="{{ $fieldClass }}">
                            @foreach(\App\Models\Interview::CHANNELS as $channel => $label)
                                <option value="{{ $channel }}" @selected(old('mode', $interview?->mode ?? 'google_meet') === $channel)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('mode')<p class="mt-1 text-sm font-semibold text-rose-700">{{ $message }}</p>@enderror
                    </div>

                    <div class="mt-4">
                        <label for="location" class="mb-1.5 block text-sm font-semibold text-slate-700">Meeting link, phone/WhatsApp contact, or venue <span class="text-brand-orange">*</span></label>
                        <textarea id="location" name="location" rows="2" maxlength="1000" required placeholder="Paste the link, phone number, venue address, or instructions for reaching you"
                                  class="{{ $fieldClass }}">{{ old('location', $interview?->location) }}</textarea>
                        @error('location')<p class="mt-1 text-sm font-semibold text-rose-700">{{ $message }}</p>@enderror
                    </div>

                    <div class="mt-4">
                        <label for="instructions" class="mb-1.5 block text-sm font-semibold text-slate-700">Additional instructions <span class="font-normal text-slate-500">(optional)</span></label>
                        <textarea id="instructions" name="instructions" rows="3" maxlength="3000" placeholder="What should the candidate prepare or bring?"
                                  class="{{ $fieldClass }}">{{ old('instructions', $interview?->instructions) }}</textarea>
                        @error('instructions')<p class="mt-1 text-sm font-semibold text-rose-700">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-5">
                    <div class="mb-3">
                        <h2 class="font-bold text-slate-900">Compose the invitation</h2>
                        <p class="mt-1 text-sm leading-5 text-slate-600">Your message will be included in the branded email along with the schedule, contact details, and a link for requesting a reschedule.</p>
                    </div>
                    <div>
                        <label for="email_subject" class="mb-1.5 block text-sm font-semibold text-slate-700">Email subject <span class="text-brand-orange">*</span></label>
                        <input id="email_subject" name="email_subject" maxlength="200" required
                               value="{{ old('email_subject', $interview?->email_subject ?? 'Interview invitation — '.$application->full_name.' | '.config('recruitment.organization_name')) }}"
                               class="{{ $fieldClass }}">
                        @error('email_subject')<p class="mt-1 text-sm font-semibold text-rose-700">{{ $message }}</p>@enderror
                    </div>
                    <div class="mt-4">
                        <label for="email_message" class="mb-1.5 block text-sm font-semibold text-slate-700">Your message <span class="text-brand-orange">*</span></label>
                        <textarea id="email_message" name="email_message" rows="7" minlength="5" maxlength="5000" required
                                  placeholder="Write the invitation and any details you want the candidate to know"
                                  class="{{ $fieldClass }}">{{ old('email_message', $interview?->email_message ?? "We are pleased to invite you to an interview for the position listed below.\n\nPlease confirm that you have received this invitation.") }}</textarea>
                        @error('email_message')<p class="mt-1 text-sm font-semibold text-rose-700">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ route('admin.interviews.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <button type="submit" name="action" value="save" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50">Save draft</button>
                        <button type="submit" name="action" value="send" class="brand-button min-h-10 rounded-lg px-5 py-2.5 text-sm font-bold">
                            {{ $application->status === 'interview' ? 'Save and send updated invitation' : 'Save, send invitation & move to Interview' }}
                        </button>
                    </div>
                </div>
            </form>
        </section>
    </div>
@endsection
