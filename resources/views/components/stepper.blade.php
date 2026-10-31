@props(['current' => 1])

@php($steps = \App\Services\ApplicationWizard::STEPS)

<div class="mb-6 rounded-xl border border-blue-100 bg-blue-50 p-4 sm:hidden">
    <div class="flex items-center justify-between gap-3 text-sm">
        <span class="font-bold text-blue-900">Step {{ $current }} of {{ count($steps) }}</span>
        <span class="text-right font-semibold text-blue-800">{{ $steps[$current]['title'] }}</span>
    </div>
    <div class="mt-3 h-2 overflow-hidden rounded-full bg-white" role="progressbar" aria-label="Application progress" aria-valuemin="1" aria-valuemax="{{ count($steps) }}" aria-valuenow="{{ $current }}">
        <div class="h-full rounded-full bg-brand-blue" style="width: {{ round($current / count($steps) * 100) }}%"></div>
    </div>
</div>

<ol class="mb-6 hidden gap-2 sm:grid sm:grid-cols-8">
    @foreach($steps as $index => $meta)
        @php($state = $index < $current ? 'done' : ($index === $current ? 'current' : 'todo'))
        <li @class([
            'flex flex-col items-center gap-1 rounded-lg px-1 py-2 text-center',
            'bg-blue-50' => $state === 'current',
        ])>
            <span @class([
                'grid h-7 w-7 place-items-center rounded-full text-xs font-semibold',
                'bg-blue-700 text-white' => $state === 'current',
                'bg-emerald-100 text-emerald-700' => $state === 'done',
                'bg-slate-200 text-slate-500' => $state === 'todo',
            ])>
                {{ $state === 'done' ? '✓' : $index }}
            </span>
            <span @class([
                'text-[11px] leading-tight',
                'font-semibold text-blue-800' => $state === 'current',
                'text-slate-600' => $state === 'done',
                'text-slate-400' => $state === 'todo',
            ])>{{ $meta['title'] }}</span>
        </li>
    @endforeach
</ol>
