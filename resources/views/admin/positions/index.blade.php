@extends('layouts.admin')

@section('title', 'Positions')

@section('content')
    <div class="mx-auto max-w-7xl space-y-5">
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand-orange">Recruitment exercises</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Positions</h1>
                <p class="mt-1 text-sm text-slate-600">Publish opportunities, manage application dates and review interest.</p>
            </div>
            <a href="{{ route('admin.positions.create') }}" class="brand-button inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-bold">
                <span class="text-lg leading-none" aria-hidden="true">+</span> Create position
            </a>
        </header>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 sm:px-6">
                <div>
                    <h2 class="font-bold text-slate-900">All positions</h2>
                    <p class="mt-1 text-sm text-slate-500">Closing an opportunity stops new applications without removing existing records.</p>
                </div>
                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-brand-blue">{{ number_format($positions->total()) }} total</span>
            </div>

            @if($positions->isEmpty())
                <div class="px-6 py-14 text-center">
                    <h3 class="font-bold text-slate-900">No positions created</h3>
                    <p class="mt-1 text-sm text-slate-500">Create your first opportunity to get the recruitment process started.</p>
                    <a href="{{ route('admin.positions.create') }}" class="brand-button mt-5 inline-flex rounded-lg px-4 py-2.5 text-sm font-bold">Create a position</a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[850px] text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th scope="col" class="px-5 py-3 sm:px-6">Position</th>
                                <th scope="col" class="px-5 py-3">Status</th>
                                <th scope="col" class="px-5 py-3">Application window</th>
                                <th scope="col" class="px-5 py-3">Applicants</th>
                                <th scope="col" class="px-5 py-3 text-right sm:px-6">Manage</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($positions as $position)
                                <tr class="align-top transition hover:bg-slate-50/80">
                                    <td class="px-5 py-4 sm:px-6">
                                        <p class="font-semibold text-slate-900">{{ $position->title }}</p>
                                        <p class="mt-0.5 text-xs text-slate-500">{{ $position->department ?: 'No department specified' }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span @class([
                                            'inline-flex rounded-full px-2.5 py-1 text-xs font-bold',
                                            'bg-emerald-50 text-emerald-800' => $position->status === 'published',
                                            'bg-slate-100 text-slate-700' => $position->status === 'draft',
                                            'bg-orange-50 text-orange-800' => $position->status === 'closed',
                                            'bg-violet-50 text-violet-800' => $position->status === 'archived',
                                        ])>{{ \App\Models\Position::STATUSES[$position->status] ?? ucfirst($position->status) }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-slate-600">
                                        <p>{{ $position->opens_at?->format('j M Y') ?? 'Opens immediately' }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $position->closes_at?->format('j M Y') ?? 'No closing date' }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <a href="{{ route('admin.applications.index', ['position_id' => $position->id]) }}" class="font-bold text-brand-blue hover:text-brand-orange-dark">{{ number_format($position->applications_count) }} <span class="font-normal text-slate-500">{{ \Illuminate\Support\Str::plural('application', $position->applications_count) }}</span></a>
                                    </td>
                                    <td class="px-5 py-4 text-right sm:px-6">
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <a href="{{ route('admin.positions.edit', $position) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700 hover:border-brand-blue hover:text-brand-blue">Edit</a>
                                            <form method="POST" action="{{ route('admin.positions.status', $position) }}" class="flex gap-1">
                                                @csrf
                                                @method('PATCH')
                                                <label for="status-{{ $position->id }}" class="sr-only">Change status for {{ $position->title }}</label>
                                                <select id="status-{{ $position->id }}" name="status" class="max-w-32 rounded-lg border border-slate-300 bg-white px-2 py-2 text-xs text-slate-700">
                                                    @foreach(\App\Models\Position::STATUSES as $value => $label)
                                                        <option value="{{ $value }}" @selected($position->status === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="rounded-lg border border-slate-300 px-2.5 py-2 text-xs font-bold text-brand-blue hover:border-brand-blue" aria-label="Save status for {{ $position->title }}">Save</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-100 px-5 py-4 sm:px-6">{{ $positions->links() }}</div>
            @endif
        </section>
    </div>
@endsection
