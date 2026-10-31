@php($data = $draft['data'] ?? [])
@php($hasLeadership = old('has_leadership', $data['has_leadership'] ?? '') === 'yes')

<fieldset class="mb-4">
    <legend class="mb-2 text-sm font-medium text-slate-700">Do you have any leadership experience? <span class="text-rose-600">*</span></legend>
    <div class="flex gap-4">
        <label class="flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm">
            <input type="radio" name="has_leadership" value="yes" data-leadership-toggle
                   @checked(old('has_leadership', $data['has_leadership'] ?? '') === 'yes')>
            Yes
        </label>
        <label class="flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm">
            <input type="radio" name="has_leadership" value="no" data-leadership-toggle
                   @checked(old('has_leadership', $data['has_leadership'] ?? '') === 'no')>
            No
        </label>
    </div>
    @error('has_leadership')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
</fieldset>

<div id="leadership-fields" data-leadership-fields
     class="{{ $hasLeadership ? '' : 'hidden' }} rounded-xl border border-slate-200 bg-slate-50 p-4">
    <p class="mb-4 text-sm text-slate-600">These fields are only validated when you select “Yes”.</p>

    <div class="grid gap-x-4 sm:grid-cols-2">
        <x-input name="leadership_organization" label="Organization / Institution" :value="$data['leadership_organization'] ?? ''" />
        <x-input name="leadership_position" label="Leadership Position / Role" :value="$data['leadership_position'] ?? ''" />
    </div>

    <x-input name="leadership_duration" label="Duration" :value="$data['leadership_duration'] ?? ''" placeholder="e.g. Jan 2021 – Dec 2022" />

    <x-textarea name="leadership_description" label="Description of Experience" rows="4"
                :value="$data['leadership_description'] ?? ''" placeholder="Describe your responsibilities and achievements" />
</div>
