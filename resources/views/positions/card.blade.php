@props(['position'])

<article class="flex flex-col rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-blue-300 hover:shadow">
    <div class="flex items-start justify-between gap-3">
        <div>
            @if($position->department)
                <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">{{ $position->department }}</p>
            @endif
            <h3 class="mt-1 font-semibold text-slate-900">{{ $position->title }}</h3>
        </div>
        <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">Open</span>
    </div>

    <p class="mt-3 line-clamp-3 text-sm text-slate-600">{{ $position->description }}</p>

    @if($position->requirements)
        <p class="mt-3 text-sm text-slate-600"><span class="font-medium text-slate-800">Requirements:</span> {{ \Illuminate\Support\Str::limit(strip_tags($position->requirements), 140) }}</p>
    @endif

    <dl class="mt-4 grid grid-cols-2 gap-2 border-t border-slate-100 pt-4 text-xs text-slate-500">
        <div>
            <dt class="font-medium text-slate-700">Opens</dt>
            <dd>{{ $position->opens_at?->format('d M Y') ?? 'Immediately' }}</dd>
        </div>
        <div>
            <dt class="font-medium text-slate-700">Closes</dt>
            <dd>{{ $position->closes_at?->format('d M Y') ?? 'Not specified' }}</dd>
        </div>
    </dl>

    <div class="mt-4 flex gap-2">
        <a href="{{ route('positions.show', $position) }}" class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-center text-sm font-medium text-slate-700 hover:bg-slate-50">Details</a>
        <a href="{{ route('apply.start', $position) }}" class="brand-button flex-1 rounded-lg px-3 py-2 text-center text-sm font-semibold">Apply now</a>
    </div>
</article>
