@extends('layouts.app')

@section('title', 'Request an interview reschedule')

@section('content')
    <div class="mx-auto max-w-2xl px-4 py-10 sm:px-6">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand-orange">Interview coordination</p>
            <h1 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-950">Would you like to reschedule?</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">Your interview is currently scheduled for <strong>{{ $interview->scheduled_at->setTimezone($timezone)->format('l, j F Y \a\t g:i A') }} ({{ $timezone }})</strong> via <strong>{{ $interview->channelLabel() }}</strong>. Contact details: <strong class="break-words">{{ $interview->location }}</strong>. Send us a brief reason and the recruitment team will contact you to agree on another time. Your current interview time remains unchanged until we confirm a new one.</p>

            @if($interview->status === 'reschedule_requested')
                <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm leading-6 text-emerald-900">
                    We’ve received your request. The recruitment team will contact you. You can close this page.
                </div>
            @else
                <form method="POST" action="{{ route('interview-reschedule.store', ['token' => request()->route('token')]) }}" class="mt-6 space-y-4">
                    @csrf
                    <div>
                        <label for="reason" class="mb-1.5 block text-sm font-semibold text-slate-700">Why do you need to reschedule? <span class="text-brand-orange">*</span></label>
                        <textarea id="reason" name="reason" rows="5" required minlength="10" maxlength="1000"
                                  placeholder="Please share a short reason so we can assist."
                                  class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 focus:border-brand-blue focus:ring-4 focus:ring-blue-100">{{ old('reason') }}</textarea>
                        @error('reason')<p class="mt-1 text-sm font-semibold text-rose-700">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="brand-button min-h-11 w-full rounded-lg px-5 py-3 text-sm font-bold sm:w-auto">Send reschedule request</button>
                </form>
            @endif
        </section>
    </div>
@endsection
