@php($data = $draft['data'] ?? [])
@php($qualifications = \App\Services\ApplicationWizard::qualifications())
@php($storedQualification = old('highest_qualification', $data['highest_qualification'] ?? ''))
@php($hasCustomQualification = $storedQualification !== '' && ! in_array($storedQualification, $qualifications, true))
@php($isOtherQualification = $storedQualification === 'Other' || $hasCustomQualification)
@php($selectedQualification = $hasCustomQualification ? 'Other' : $storedQualification)
@php($customQualification = old('highest_qualification_other', $hasCustomQualification ? $storedQualification : ''))

<x-select name="highest_qualification" label="Highest Qualification" required
          data-qualification-select
          :options="array_combine($qualifications, $qualifications)"
          :value="$selectedQualification" placeholder="Select your highest qualification" />

<div data-qualification-other class="{{ $isOtherQualification ? '' : 'hidden' }}">
    <x-input name="highest_qualification_other" label="Your qualification (required for Other)"
             data-qualification-other-input :disabled="! $isOtherQualification"
             :value="$customQualification" placeholder="e.g. MEng, PGD, or an equivalent qualification" />
</div>

<div class="grid gap-x-4 sm:grid-cols-2">
    <x-input name="institution" label="Institution Name" :value="$data['institution'] ?? ''" placeholder="e.g. University of Lagos" />
    <x-input name="course_of_study" label="Course of Study" :value="$data['course_of_study'] ?? ''" placeholder="e.g. Computer Science" />
</div>

<div class="grid gap-x-4 sm:grid-cols-2">
    <x-input name="graduation_year" label="Graduation Year" type="number" :value="$data['graduation_year'] ?? ''" placeholder="e.g. 2022" />
    <div></div>
</div>

<x-textarea name="additional_qualifications" label="Additional Qualification Details" rows="4"
            :value="$data['additional_qualifications'] ?? ''"
            placeholder="Professional certifications, training, etc. (optional)" />

<p class="rounded-lg bg-slate-50 px-4 py-3 text-sm text-slate-600">
    Only your highest qualification is required. Institution, course, year and additional details are optional.
</p>
