@php($data = $draft['data'] ?? [])

<x-select
    name="position_id"
    label="Position Applying For"
    required
    :options="\App\Models\Position::accepting()->orderBy('title')->pluck('title', 'id')->all()"
    :value="$data['position_id'] ?? $position->id"
    placeholder="Choose a position"
/>

<p class="rounded-lg bg-slate-50 px-4 py-3 text-sm text-slate-600">
    You may only apply for vacancies that are currently open. Applications for closed or unpublished
    positions cannot be submitted.
</p>
