@extends('layouts.app')

@section('title', 'Review & Submit')

@section('content')
    @php($data = $draft['data'] ?? [])
    @php($files = $draft['files'] ?? [])
    @php($fileNames = $draft['file_names'] ?? [])
    @php($stateName = fn ($id) => $stateNames->get($id, '—'))
    @php($lgaName = fn ($id) => $localGovernmentNames->get($id, '—'))

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
        <a href="{{ route('apply.step', [$position, \App\Services\ApplicationWizard::COUNT]) }}" class="text-sm font-medium text-blue-700 hover:text-blue-900">← Back to form</a>

        <div class="mt-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
            <div class="mb-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Step {{ \App\Services\ApplicationWizard::COUNT + 1 }}</p>
                <h1 class="text-xl font-bold text-slate-900">Review & Submit</h1>
                <p class="mt-1 text-sm text-slate-500">Check your information carefully. You can return to any section to make corrections.</p>
            </div>

            <x-stepper :current="\App\Services\ApplicationWizard::COUNT + 1" />

            <div class="space-y-6">
                {{-- 1. Position --}}
                <section class="rounded-xl border border-slate-200">
                    <header class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-4 py-3">
                        <h2 class="font-semibold text-slate-800">1. Position Applied For</h2>
                        <a href="{{ route('apply.step', [$position, 1]) }}" class="text-sm font-medium text-blue-700 hover:underline">Edit</a>
                    </header>
                    <div class="px-4 py-3 text-sm">
                        <p class="font-medium text-slate-900">{{ $position->title }}</p>
                        <p class="text-slate-500">{{ $position->department }}</p>
                    </div>
                </section>

                {{-- 2. Personal --}}
                <section class="rounded-xl border border-slate-200">
                    <header class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-4 py-3">
                        <h2 class="font-semibold text-slate-800">2. Personal Information</h2>
                        <a href="{{ route('apply.step', [$position, 2]) }}" class="text-sm font-medium text-blue-700 hover:underline">Edit</a>
                    </header>
                    <dl class="grid gap-x-6 gap-y-3 px-4 py-4 text-sm sm:grid-cols-2">
                        @foreach([
                            'Full Name' => trim(($data['first_name'] ?? '').' '.($data['middle_name'] ?? '').' '.($data['last_name'] ?? '')),
                            'Email Address' => $data['email'] ?? '—',
                            'Phone Number' => $data['phone'] ?? '—',
                            'Date of Birth' => isset($data['date_of_birth']) ? \Illuminate\Support\Carbon::parse($data['date_of_birth'])->format('d M Y') : '—',
                            'Gender' => ucfirst($data['gender'] ?? '—'),
                            'State of Origin' => $stateName($data['state_of_origin_id'] ?? null).' / '.($lgaName($data['origin_lga_id'] ?? null) === '—' ? '' : $lgaName($data['origin_lga_id'] ?? null)),
                            'Current Residence' => $stateName($data['residence_state_id'] ?? null).' / '.($lgaName($data['residence_lga_id'] ?? null) === '—' ? '' : $lgaName($data['residence_lga_id'] ?? null)),
                            'Residential Address' => $data['residential_address'] ?? '—',
                        ] as $label => $value)
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $label }}</dt>
                                <dd class="mt-0.5 text-slate-800">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </section>

                {{-- 3. Education --}}
                <section class="rounded-xl border border-slate-200">
                    <header class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-4 py-3">
                        <h2 class="font-semibold text-slate-800">3. Educational Qualifications</h2>
                        <a href="{{ route('apply.step', [$position, 3]) }}" class="text-sm font-medium text-blue-700 hover:underline">Edit</a>
                    </header>
                    <dl class="grid gap-x-6 gap-y-3 px-4 py-4 text-sm sm:grid-cols-2">
                        @foreach([
                            'Highest Qualification' => $data['highest_qualification'] ?? '—',
                            'Institution' => $data['institution'] ?? '—',
                            'Course of Study' => $data['course_of_study'] ?? '—',
                            'Graduation Year' => $data['graduation_year'] ?? '—',
                            'Additional Details' => $data['additional_qualifications'] ?? '—',
                        ] as $label => $value)
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $label }}</dt>
                                <dd class="mt-0.5 whitespace-pre-line text-slate-800">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </section>

                {{-- 4. Skills --}}
                <section class="rounded-xl border border-slate-200">
                    <header class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-4 py-3">
                        <h2 class="font-semibold text-slate-800">4. Skills & Competencies</h2>
                        <a href="{{ route('apply.step', [$position, 4]) }}" class="text-sm font-medium text-blue-700 hover:underline">Edit</a>
                    </header>
                    <div class="flex flex-wrap gap-2 px-4 py-4">
                        @foreach(array_filter($data['skills'] ?? []) as $skill)
                            <span class="rounded-full bg-blue-50 px-3 py-1 text-sm font-medium text-blue-700">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>

                {{-- 5. Leadership --}}
                <section class="rounded-xl border border-slate-200">
                    <header class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-4 py-3">
                        <h2 class="font-semibold text-slate-800">5. Leadership Experience</h2>
                        <a href="{{ route('apply.step', [$position, 5]) }}" class="text-sm font-medium text-blue-700 hover:underline">Edit</a>
                    </header>
                    <div class="px-4 py-4 text-sm">
                        @if(($data['has_leadership'] ?? 'no') === 'yes')
                            <p class="font-medium text-slate-900">Yes</p>
                            <dl class="mt-3 grid gap-x-6 gap-y-3 sm:grid-cols-2">
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Organization / Institution</dt>
                                    <dd class="mt-0.5 text-slate-800">{{ $data['leadership_organization'] ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Position / Role</dt>
                                    <dd class="mt-0.5 text-slate-800">{{ $data['leadership_position'] ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Duration</dt>
                                    <dd class="mt-0.5 text-slate-800">{{ $data['leadership_duration'] ?? '—' }}</dd>
                                </div>
                            </dl>
                            <p class="mt-3 whitespace-pre-line text-slate-700">{{ $data['leadership_description'] ?? '—' }}</p>
                        @else
                            <p class="text-slate-600">No leadership experience declared.</p>
                        @endif
                    </div>
                </section>

                {{-- 6. About --}}
                <section class="rounded-xl border border-slate-200">
                    <header class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-4 py-3">
                        <h2 class="font-semibold text-slate-800">6. About Yourself</h2>
                        <a href="{{ route('apply.step', [$position, 6]) }}" class="text-sm font-medium text-blue-700 hover:underline">Edit</a>
                    </header>
                    <p class="whitespace-pre-line px-4 py-4 text-sm text-slate-700">{{ $data['about'] ?? '—' }}</p>
                </section>

                {{-- 7. Documents --}}
                <section class="rounded-xl border border-slate-200">
                    <header class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-4 py-3">
                        <h2 class="font-semibold text-slate-800">7. Supporting Documents</h2>
                        <a href="{{ route('apply.step', [$position, 7]) }}" class="text-sm font-medium text-blue-700 hover:underline">Edit</a>
                    </header>
                    <ul class="divide-y divide-slate-100 px-4 py-2 text-sm">
                        @foreach($position->documentRequirements() as $document)
                            <li class="flex items-center justify-between py-2">
                                <span>{{ $document['label'] }}</span>
                                @if(isset($files[$document['key']]))
                                    <span class="text-sm font-medium text-emerald-700">✓ {{ $fileNames[$document['key']] ?? basename($files[$document['key']]) }}</span>
                                @else
                                    <span class="text-xs text-slate-400">Not provided</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </section>
            </div>

            <form method="POST" action="{{ route('apply.submit', $position) }}" class="mt-6 border-t border-slate-100 pt-5">
                @csrf
                <label class="flex items-start gap-3 rounded-xl bg-blue-50 p-4 text-sm text-slate-700">
                    <input type="checkbox" name="confirm" value="1" required class="mt-0.5 h-4 w-4 rounded border-slate-300 text-blue-700 focus:ring-blue-300">
                    <span>I confirm that the information provided above is true and complete to the best of my knowledge.</span>
                </label>
                @error('confirm')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror

                <div class="mt-5 flex items-center justify-between gap-3">
                    <a href="{{ route('apply.step', [$position, \App\Services\ApplicationWizard::COUNT]) }}"
                       class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">← Back</a>
                    <button type="submit"
                            class="rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-300">
                        Submit Application
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
