@extends('layouts.admin')

@section('title', 'Audit logs')

@section('content')
    <div class="mx-auto max-w-7xl space-y-5">
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand-orange">Accountability</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Audit log</h1>
                <p class="mt-1 text-sm text-slate-600">A read-only record of important recruitment and administrator activity.</p>
            </div>
            <span class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600">Records cannot be edited here</span>
        </header>

        <form method="GET" action="{{ route('admin.audit.index') }}" class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-end sm:p-5">
            <div class="w-full sm:max-w-md">
                <label for="action" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Activity type</label>
                <select id="action" name="action" class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
                    <option value="">All activity</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" @selected($current === $action)>{{ \Illuminate\Support\Str::headline(str_replace('.', ' ', $action)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.audit.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Clear</a>
                <button type="submit" class="brand-button rounded-lg px-5 py-2.5 text-sm font-bold">Apply filter</button>
            </div>
        </form>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <h2 class="font-bold text-slate-900">Activity history</h2>
                <p class="mt-1 text-sm text-slate-500">{{ number_format($logs->total()) }} {{ \Illuminate\Support\Str::plural('record', $logs->total()) }}</p>
            </div>
            @if($logs->isEmpty())
                <div class="px-6 py-14 text-center">
                    <h3 class="font-bold text-slate-900">No matching activity</h3>
                    <p class="mt-1 text-sm text-slate-500">There are no audit entries for this filter yet.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th scope="col" class="px-5 py-3 sm:px-6">Date & time</th>
                                <th scope="col" class="px-5 py-3">Activity</th>
                                <th scope="col" class="px-5 py-3">Administrator</th>
                                <th scope="col" class="px-5 py-3">Record details</th>
                                <th scope="col" class="px-5 py-3">Source IP</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($logs as $log)
                                <tr class="align-top hover:bg-slate-50/80">
                                    <td class="whitespace-nowrap px-5 py-4 text-slate-600 sm:px-6">{{ $log->created_at?->format('j M Y, g:i a') ?? '—' }}</td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-brand-blue">{{ \Illuminate\Support\Str::headline(str_replace('.', ' ', $log->action)) }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="font-semibold text-slate-800">{{ $log->user?->name ?? 'System' }}</p>
                                        @if($log->user?->email)<p class="mt-0.5 text-xs text-slate-500">{{ $log->user->email }}</p>@endif
                                    </td>
                                    <td class="max-w-md px-5 py-4">
                                        @if($log->details)
                                            <dl class="space-y-1">
                                                @foreach($log->details as $key => $value)
                                                    <div class="flex flex-wrap gap-x-2">
                                                        <dt class="text-xs font-semibold text-slate-500">{{ \Illuminate\Support\Str::headline(str_replace('_', ' ', (string) $key)) }}:</dt>
                                                        <dd class="break-all text-xs text-slate-700">{{ is_scalar($value) ? $value : json_encode($value) }}</dd>
                                                    </div>
                                                @endforeach
                                            </dl>
                                        @else
                                            <span class="text-xs text-slate-500">{{ $log->subject_type ? \Illuminate\Support\Str::afterLast($log->subject_type, '\\') : '—' }}{{ $log->subject_id ? ' #'.$log->subject_id : '' }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 font-mono text-xs text-slate-500">{{ $log->ip_address ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-100 px-5 py-4 sm:px-6">{{ $logs->links() }}</div>
            @endif
        </section>
    </div>
@endsection
