@extends('layouts.admin')

@section('title', 'Edit email template')

@section('content')
    @php($fieldClass = 'block min-h-12 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-brand-blue focus:ring-4 focus:ring-blue-100')
    @php($placeholders = [
        'applicant_name' => 'Applicant name',
        'application_reference' => 'Application reference',
        'position_title' => 'Position title',
        'application_status' => 'Application status',
        'organization_name' => 'Academy name',
        'tracking_url' => 'Tracking link',
        'submitted_at' => 'Date applied',
    ])

    <div class="mx-auto max-w-7xl space-y-5">
        <a href="{{ route('admin.templates.index') }}" class="inline-flex min-h-10 items-center gap-2 rounded-lg pr-2 text-sm font-bold text-brand-blue transition hover:text-brand-orange-dark">
            <span aria-hidden="true">←</span>
            Email templates
        </a>

        <header class="flex flex-col gap-3 border-b border-slate-200 pb-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.12em] text-brand-orange">Edit notification</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">{{ $template->name }}</h1>
                <p class="mt-1 max-w-2xl text-sm leading-6 text-slate-600">Update the subject and message, review the preview, then save your changes.</p>
            </div>
            <span @class([
                'inline-flex w-fit items-center gap-2 rounded-md px-2.5 py-1.5 text-xs font-semibold',
                'bg-emerald-50 text-emerald-800' => $template->enabled,
                'bg-slate-100 text-slate-700' => ! $template->enabled,
            ]) data-email-enabled-status aria-live="polite">
                <span @class(['h-1.5 w-1.5 rounded-full', 'bg-emerald-500' => $template->enabled, 'bg-slate-400' => ! $template->enabled]) data-email-enabled-indicator></span>
                <span data-email-enabled-copy>{{ $template->enabled ? 'Automatic delivery enabled' : 'Automatic delivery paused' }}</span>
            </span>
        </header>

        <form method="POST" action="{{ route('admin.templates.update', $template) }}" class="grid items-start gap-5 xl:grid-cols-[minmax(0,1.05fr)_minmax(360px,0.95fr)]" data-email-template-form>
            @csrf
            @method('PUT')

            <div class="space-y-5">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex items-start gap-3">
                        <span class="pt-0.5 text-xs font-bold tracking-wide text-brand-orange" aria-hidden="true">01</span>
                        <div>
                            <h2 class="font-bold text-slate-900">Subject line</h2>
                            <p class="mt-1 text-sm leading-5 text-slate-600">Use a clear subject that tells applicants what the message is about.</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label for="subject" class="mb-1.5 block text-sm font-bold text-slate-700">Email subject</label>
                        <input id="subject" name="subject" value="{{ old('subject', $template->subject) }}" maxlength="200" required class="{{ $fieldClass }}" placeholder="Example: We received your application!" data-email-subject>
                        <div class="mt-1.5 flex flex-wrap items-center justify-between gap-2">
                            @error('subject')<p class="text-sm font-semibold text-rose-700">{{ $message }}</p>@else<p class="text-xs text-slate-500">Maximum 200 characters.</p>@enderror
                            <span class="text-xs tabular-nums text-slate-500"><span data-email-subject-count>0</span>/200</span>
                        </div>
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <span class="text-xs font-semibold text-slate-500">Insert:</span>
                            <button type="button" class="rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-semibold text-brand-blue hover:border-brand-blue hover:bg-blue-50" data-email-insert="{{ '{'.'{applicant_name}'.'}' }}" data-email-target="subject">Applicant name</button>
                            <button type="button" class="rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-semibold text-brand-blue hover:border-brand-blue hover:bg-blue-50" data-email-insert="{{ '{'.'{organization_name}'.'}' }}" data-email-target="subject">Academy name</button>
                        </div>
                    </div>
                </section>

                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="p-5 sm:p-6">
                        <div class="flex items-start gap-3">
                            <span class="pt-0.5 text-xs font-bold tracking-wide text-brand-orange" aria-hidden="true">02</span>
                            <div>
                                <h2 class="font-bold text-slate-900">Message content</h2>
                                <p class="mt-1 text-sm leading-5 text-slate-600">Edit the message below. Use formatting controls where needed; changes appear in the preview.</p>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center gap-1.5 rounded-t-xl border border-slate-200 bg-slate-50 p-2" role="toolbar" aria-label="Message formatting">
                            <button type="button" class="grid h-10 min-w-10 place-items-center rounded-lg border border-slate-200 bg-white px-2 font-black text-slate-700 shadow-sm hover:border-blue-300 hover:bg-blue-50" title="Bold selected text" aria-label="Bold" data-email-command="bold">B</button>
                            <button type="button" class="grid h-10 min-w-10 place-items-center rounded-lg border border-slate-200 bg-white px-2 font-serif text-lg italic text-slate-700 shadow-sm hover:border-blue-300 hover:bg-blue-50" title="Italic selected text" aria-label="Italic" data-email-command="italic">I</button>
                            <span class="mx-1 h-6 w-px bg-slate-200" aria-hidden="true"></span>
                            <button type="button" class="inline-flex h-10 items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 text-xs font-bold text-slate-700 shadow-sm hover:border-blue-300 hover:bg-blue-50" title="Add a bulleted list" data-email-command="insertUnorderedList"><span aria-hidden="true">•</span> Bullet list</button>
                            <button type="button" class="inline-flex h-10 items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 text-xs font-bold text-slate-700 shadow-sm hover:border-blue-300 hover:bg-blue-50" title="Add a numbered list" data-email-command="insertOrderedList"><span aria-hidden="true">1.</span> Numbered list</button>
                            <span class="ml-auto inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700" data-email-dirty-label aria-live="polite">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                Saved
                            </span>
                        </div>

                        <div class="min-h-72 rounded-b-xl border border-t-0 border-slate-200 bg-white px-4 py-4 text-sm leading-7 text-slate-800 outline-none transition focus-within:ring-4 focus-within:ring-blue-100 sm:px-5"
                            contenteditable="true" role="textbox" aria-label="Email message content" aria-multiline="true"
                            data-email-editor></div>
                        <textarea id="body" name="body" rows="12" maxlength="20000" required class="block w-full rounded-b-xl border border-slate-300 bg-white px-4 py-3 font-mono text-sm leading-6 text-slate-800 shadow-sm focus:border-brand-blue focus:ring-4 focus:ring-blue-100" aria-label="Message content (plain editor fallback)" data-email-body>{{ old('body', $template->body) }}</textarea>
                        <div class="mt-1.5 flex flex-wrap items-center justify-between gap-2">
                            @error('body')<p class="text-sm font-semibold text-rose-700">{{ $message }}</p>@else<p class="text-xs text-slate-500">Maximum 20,000 characters.</p>@enderror
                            <span class="text-xs tabular-nums text-slate-500"><span data-email-body-count>0</span>/20,000</span>
                        </div>
                        <p class="mt-2 hidden text-sm font-semibold text-rose-700" data-email-editor-error role="alert"></p>
                    </div>
                </section>

                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex items-start gap-3">
                        <span class="pt-0.5 text-xs font-bold tracking-wide text-brand-orange" aria-hidden="true">03</span>
                        <div class="min-w-0 flex-1">
                            <h2 class="font-bold text-slate-900">Personalise with applicant details</h2>
                            <p class="mt-1 text-sm leading-5 text-slate-600">Insert a field to automatically include the correct applicant or application information.</p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach($placeholders as $key => $label)
                                    @php($placeholderToken = '{'.'{'.$key.'}'.'}')
                                    <button type="button" class="inline-flex min-h-9 items-center rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-left text-xs font-semibold text-brand-blue transition hover:border-brand-blue hover:bg-blue-50" data-email-insert="{{ $placeholderToken }}" title="Insert {{ $label }}">
                                        + {{ $label }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex items-start gap-3">
                        <span class="pt-0.5 text-xs font-bold tracking-wide text-brand-orange" aria-hidden="true">04</span>
                        <div>
                            <h2 class="font-bold text-slate-900">Automatic delivery</h2>
                            <p class="mt-1 text-sm leading-5 text-slate-600">Choose whether this message is sent automatically for its event.</p>
                        </div>
                    </div>
                    <label for="enabled" class="mt-4 flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 p-4 transition hover:border-slate-300 hover:bg-slate-50">
                        <input id="enabled" type="checkbox" name="enabled" value="1" @checked(old('enabled', $template->enabled)) class="mt-1 h-4 w-4 rounded border-slate-300 text-brand-blue focus:ring-blue-500">
                        <span>
                            <span class="block text-sm font-semibold text-slate-900">Enable automatic delivery</span>
                            <span class="mt-1 block text-xs leading-5 text-slate-600">When disabled, this template is saved but not sent automatically.</span>
                        </span>
                    </label>
                </section>

                <div class="sticky bottom-3 z-10 flex flex-col-reverse gap-2 rounded-xl border border-slate-200 bg-white/95 p-3 shadow-lg backdrop-blur sm:flex-row sm:items-center sm:justify-between sm:px-4">
                    <a href="{{ route('admin.templates.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100">Cancel</a>
                    <button type="submit" class="brand-button inline-flex min-h-11 items-center justify-center rounded-lg px-5 py-2.5 text-sm font-bold">
                        Save changes
                    </button>
                </div>
            </div>

            <aside class="space-y-4 xl:sticky xl:top-5">
                <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-4 py-4 sm:px-5">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-brand-orange">Preview</p>
                            <h2 class="mt-1 font-bold text-slate-900">Applicant email</h2>
                        </div>
                        <div class="flex items-center gap-1 rounded-xl bg-slate-100 p-1" role="group" aria-label="Preview size">
                            <button type="button" class="rounded-lg bg-white px-3 py-2 text-xs font-bold text-brand-blue shadow-sm" aria-pressed="true" data-email-preview-size="desktop">Desktop</button>
                            <button type="button" class="rounded-lg px-3 py-2 text-xs font-bold text-slate-600 hover:bg-white" aria-pressed="false" data-email-preview-size="mobile">Phone</button>
                        </div>
                    </div>
                    <div class="bg-slate-50 p-3 sm:p-4">
                        <div class="mx-auto max-w-xl overflow-hidden rounded-lg border border-slate-200 bg-white" data-email-preview-shell>
                            <div class="flex items-center gap-2 border-b border-slate-100 bg-white px-4 py-3">
                                <span class="grid h-8 w-8 place-items-center rounded-md bg-brand-blue text-xs font-bold text-white">F</span>
                                <div class="min-w-0">
                                    <p class="truncate text-xs font-bold text-slate-800">{{ $academySettings->organization_name }}</p>
                                    <p class="truncate text-[10px] text-slate-500">to Ada Okafor</p>
                                </div>
                                <span class="ml-auto shrink-0 text-[10px] text-slate-400">now</span>
                            </div>
                            <div class="border-b border-slate-100 px-4 py-3">
                                <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Subject</p>
                                <p class="mt-1 break-words text-sm font-bold text-slate-800" data-email-subject-preview></p>
                            </div>
                            <div class="flex justify-center overflow-hidden bg-slate-50">
                                <iframe class="block h-[430px] w-full bg-white sm:h-[500px]" title="Rendered sample email" sandbox="" referrerpolicy="no-referrer" data-email-body-preview data-brand="{{ $academySettings->organization_name }}" data-logo="{{ asset('images/fintec-logo.png') }}"></iframe>
                            </div>
                            <div class="border-t border-slate-100 bg-slate-50 px-4 py-3 text-center text-[11px] text-slate-500">Preview uses sample applicant information. No email will be sent.</div>
                        </div>
                    </div>
                </section>
                <section class="rounded-xl border border-blue-100 bg-blue-50/60 p-4">
                    <h2 class="text-sm font-bold text-brand-blue">Available applicant fields</h2>
                    <p class="mt-1 text-sm leading-5 text-slate-600">Use the buttons in the message editor to insert applicant-specific details.</p>
                </section>
            </aside>
        </form>
    </div>
@endsection
