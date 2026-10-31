@extends('layouts.app')

@section('title', 'Apply — '.$position->title)

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
        <a href="{{ route('positions.show', $position) }}" class="text-sm font-medium text-blue-700 hover:text-blue-900">← {{ $position->title }}</a>

        <div class="mt-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
            <div class="mb-5 flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Application Form</p>
                    <h1 class="text-xl font-bold text-slate-900">Step {{ $step }} of {{ count(\App\Services\ApplicationWizard::STEPS) }}: {{ $stepTitle }}</h1>
                </div>
                <a href="{{ route('tracking.show') }}" class="hidden text-sm font-medium text-slate-500 hover:text-slate-700 sm:block">Have a reference? Track it</a>
            </div>

            <x-stepper :current="$step" />
            <p class="mb-5 rounded-lg bg-slate-50 px-4 py-3 text-xs leading-5 text-slate-600">
                Your progress is saved when you continue, in this browser session. Keep using this browser to resume; drafts are not shared across devices and may be cleared when your session expires.
            </p>

            <form method="POST" action="{{ route('apply.step', [$position, $step]) }}"
                  @if($step === 7) enctype="multipart/form-data" @endif>
                @csrf

                @include($stepView)

                <div class="mt-6 flex items-center justify-between gap-3 border-t border-slate-100 pt-5">
                    <a href="{{ $step > 1 ? route('apply.step', [$position, $step - 1]) : route('positions.show', $position) }}"
                       class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">← Previous</a>

                    <button type="submit"
                            class="brand-button rounded-lg px-6 py-2.5 text-sm font-semibold shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
                        {{ $step === \App\Services\ApplicationWizard::COUNT ? 'Review Application →' : 'Continue →' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
