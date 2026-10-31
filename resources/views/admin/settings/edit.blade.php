@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
    @php($fieldClass = 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition focus:border-blue-600 focus:ring-4 focus:ring-blue-100')
    <div class="mx-auto max-w-3xl space-y-5">
        <header>
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand-orange">Academy profile</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Settings</h1>
            <p class="mt-1 text-sm leading-6 text-slate-600">Keep the academy name and applicant support details current.</p>
        </header>

        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
            @csrf
            @method('PUT')

            <div>
                <label for="organization_name" class="mb-1.5 block text-sm font-semibold text-slate-700">Academy name <span class="text-brand-orange">*</span></label>
                <input id="organization_name" name="organization_name" value="{{ old('organization_name', $settings->organization_name) }}" required maxlength="150" class="{{ $fieldClass }}">
                @error('organization_name')<p class="mt-1 text-sm font-medium text-rose-700">{{ $message }}</p>@enderror
                <p class="mt-1 text-xs text-slate-500">Used in applicant-facing branding and automated email placeholders.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="support_email" class="mb-1.5 block text-sm font-semibold text-slate-700">Applicant support email</label>
                    <input id="support_email" type="email" name="support_email" value="{{ old('support_email', $settings->support_email) }}" maxlength="150" autocomplete="email" class="{{ $fieldClass }}">
                    @error('support_email')<p class="mt-1 text-sm font-medium text-rose-700">{{ $message }}</p>@enderror
                    <p class="mt-1 text-xs text-slate-500">Shown as a contact link on public pages when provided.</p>
                </div>
                <div>
                    <label for="support_phone" class="mb-1.5 block text-sm font-semibold text-slate-700">Applicant support phone</label>
                    <input id="support_phone" type="tel" name="support_phone" value="{{ old('support_phone', $settings->support_phone) }}" maxlength="32" autocomplete="tel" class="{{ $fieldClass }}">
                    @error('support_phone')<p class="mt-1 text-sm font-medium text-rose-700">{{ $message }}</p>@enderror
                    <p class="mt-1 text-xs text-slate-500">Shown as a tap-to-call link on public pages when provided.</p>
                </div>
            </div>

            <aside class="rounded-xl border border-blue-100 bg-blue-50/70 p-4 text-sm leading-6 text-blue-950">
                Email provider credentials are deliberately not managed here. Keep SMTP usernames and passwords in protected environment or hosting secret storage.
            </aside>

            <div class="flex justify-end border-t border-slate-100 pt-4">
                <button type="submit" class="brand-button rounded-lg px-5 py-2.5 text-sm font-bold">Save settings</button>
            </div>
        </form>
    </div>
@endsection
