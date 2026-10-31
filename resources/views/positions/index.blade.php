@extends('layouts.app')

@section('title', 'Available Positions')

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
        <h1 class="text-3xl font-bold text-slate-900">Available Positions</h1>
        <p class="mt-2 text-slate-600">Open vacancies you can apply for right now.</p>

        @if($positions->isEmpty())
            <div class="mt-8 rounded-xl border border-dashed border-slate-300 bg-white p-12 text-center text-slate-500">
                There are no positions currently accepting applications.
            </div>
        @else
            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($positions as $position)
                    @include('positions.card', ['position' => $position])
                @endforeach
            </div>
        @endif

        @if($closed->isNotEmpty())
            <div class="mt-12">
                <h2 class="text-lg font-semibold text-slate-700">Recently Closed Positions</h2>
                <ul class="mt-3 divide-y divide-slate-100 rounded-xl border border-slate-200 bg-white">
                    @foreach($closed as $position)
                        <li class="flex items-center justify-between px-4 py-3 text-sm">
                            <div>
                                <p class="font-medium text-slate-800">{{ $position->title }}</p>
                                <p class="text-slate-500">{{ $position->department }}</p>
                            </div>
                            <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-500">Closed</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endsection
