@php($skills = old('skills', $draft['data']['skills'] ?? []))
@if(count($skills) === 0)
    @php($skills = [''])
@endif

<p class="mb-4 text-sm text-slate-500">
    Add the skills and competencies relevant to this role. Duplicates are not allowed.
    <span class="text-rose-600">*</span>
</p>

@if($errors->has('skills') || $errors->has('skills.*'))
    <p class="mb-3 rounded-lg border border-rose-200 bg-rose-50 px-4 py-2 text-sm text-rose-700">
        {{ $errors->first('skills') ?: $errors->first('skills.*') }}
    </p>
@endif

<div data-skill-list class="space-y-2">
    @foreach($skills as $index => $skill)
        <div data-skill-row class="flex gap-2">
            <input
                type="text"
                name="skills[{{ $index }}]"
                value="{{ $skill }}"
                maxlength="80"
                placeholder="e.g. Microsoft Office"
                class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
            >
            <button type="button" onclick="removeSkill(this)"
                    class="shrink-0 rounded-lg border border-slate-300 px-3 text-sm font-medium text-slate-500 hover:border-rose-300 hover:text-rose-600">Remove</button>
        </div>
    @endforeach
</div>

<template data-skill-template>
    <div data-skill-row class="flex gap-2">
        <input type="text" maxlength="80" placeholder="e.g. Project Management"
               class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
        <button type="button" onclick="removeSkill(this)"
                class="shrink-0 rounded-lg border border-slate-300 px-3 text-sm font-medium text-slate-500 hover:border-rose-300 hover:text-rose-600">Remove</button>
    </div>
</template>

<button type="button" onclick="addSkill()"
        class="mt-3 rounded-lg border border-blue-300 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100">
    + Add Skill
</button>
