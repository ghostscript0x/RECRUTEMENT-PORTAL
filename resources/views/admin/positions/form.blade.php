@extends('layouts.admin')

@section('title', $position->exists ? 'Edit position' : 'Create position')

@section('content')
    @php
        $isEditing = $position->exists;
        $documentUsage = old('document_usage', collect($position->required_documents ?? [])
            ->mapWithKeys(fn ($document) => [$document['key'] => 'required'])
            ->merge(collect($position->optional_documents ?? [])
                ->mapWithKeys(fn ($document) => [$document['key'] => 'optional']))
            ->all());
        $fieldClass = 'block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition focus:border-blue-600 focus:ring-4 focus:ring-blue-100';
    @endphp
    <div class="mx-auto max-w-4xl space-y-5">
        <a href="{{ route('admin.positions.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-brand-blue hover:text-brand-orange-dark"><span aria-hidden="true">←</span> All positions</a>

        <header>
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand-orange">Recruitment opportunity</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">{{ $isEditing ? 'Edit position' : 'Create a position' }}</h1>
            <p class="mt-1 text-sm text-slate-600">Add clear details so applicants know what the role involves and what to prepare.</p>
        </header>

        <form method="POST" action="{{ $isEditing ? route('admin.positions.update', $position) : route('admin.positions.store') }}" class="space-y-5">
            @csrf
            @if($isEditing)
                @method('PUT')
            @endif

            <section class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div>
                    <h2 class="font-bold text-slate-900">Position details</h2>
                    <p class="mt-1 text-sm text-slate-500">Share the essential information about this opportunity.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="title" class="mb-1.5 block text-sm font-semibold text-slate-700">Position title <span class="text-brand-orange">*</span></label>
                        <input id="title" name="title" value="{{ old('title', $position->title) }}" required maxlength="200" class="{{ $fieldClass }}" placeholder="e.g. Digital Skills Programme Officer">
                        @error('title')<p class="mt-1 text-sm font-medium text-rose-700">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="department" class="mb-1.5 block text-sm font-semibold text-slate-700">Department <span class="font-normal text-slate-500">(optional)</span></label>
                        <input id="department" name="department" value="{{ old('department', $position->department) }}" maxlength="200" class="{{ $fieldClass }}">
                        @error('department')<p class="mt-1 text-sm font-medium text-rose-700">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="description" class="mb-1.5 block text-sm font-semibold text-slate-700">Position description <span class="text-brand-orange">*</span></label>
                        <textarea id="description" name="description" rows="6" required maxlength="10000" class="{{ $fieldClass }}" placeholder="Describe the role, responsibilities and the impact this person will have.">{{ old('description', $position->description) }}</textarea>
                        @error('description')<p class="mt-1 text-sm font-medium text-rose-700">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="requirements" class="mb-1.5 block text-sm font-semibold text-slate-700">Requirements <span class="font-normal text-slate-500">(optional)</span></label>
                        <textarea id="requirements" name="requirements" rows="5" maxlength="10000" class="{{ $fieldClass }}" placeholder="List qualifications, experience and skills applicants should have.">{{ old('requirements', $position->requirements) }}</textarea>
                        @error('requirements')<p class="mt-1 text-sm font-medium text-rose-700">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            <section class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div>
                    <h2 class="font-bold text-slate-900">Application window</h2>
                    <p class="mt-1 text-sm text-slate-500">Applications are accepted only while the position is published and within these dates.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label for="opens_at" class="mb-1.5 block text-sm font-semibold text-slate-700">Opens on</label>
                        <input id="opens_at" type="date" name="opens_at" value="{{ old('opens_at', $position->opens_at?->format('Y-m-d')) }}" class="{{ $fieldClass }}">
                        @error('opens_at')<p class="mt-1 text-sm font-medium text-rose-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="closes_at" class="mb-1.5 block text-sm font-semibold text-slate-700">Closes on</label>
                        <input id="closes_at" type="date" name="closes_at" value="{{ old('closes_at', $position->closes_at?->format('Y-m-d')) }}" class="{{ $fieldClass }}">
                        @error('closes_at')<p class="mt-1 text-sm font-medium text-rose-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="status" class="mb-1.5 block text-sm font-semibold text-slate-700">Publication status</label>
                        <select id="status" name="status" required class="{{ $fieldClass }}">
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $position->status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')<p class="mt-1 text-sm font-medium text-rose-700">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            <section class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div>
                    <h2 class="font-bold text-slate-900">Supporting documents</h2>
                    <p class="mt-1 text-sm text-slate-500">Choose whether each document is required, optional or not needed for this position.</p>
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach($documentOptions as $document)
                        <div class="grid gap-2 py-3 first:pt-0 last:pb-0 sm:grid-cols-[minmax(0,1fr)_220px] sm:items-center">
                            <label for="document-{{ $document['key'] }}" class="text-sm font-semibold text-slate-800">{{ $document['label'] }}</label>
                            <select id="document-{{ $document['key'] }}" name="document_usage[{{ $document['key'] }}]" class="{{ $fieldClass }}">
                                <option value="" @selected(($documentUsage[$document['key']] ?? '') === '')>Not requested</option>
                                <option value="required" @selected(($documentUsage[$document['key']] ?? '') === 'required')>Required</option>
                                <option value="optional" @selected(($documentUsage[$document['key']] ?? '') === 'optional')>Optional</option>
                            </select>
                        </div>
                    @endforeach
                </div>
                @error('document_usage')<p class="text-sm font-medium text-rose-700">{{ $message }}</p>@enderror
            </section>

            <div class="flex flex-col-reverse justify-between gap-3 sm:flex-row">
                <a href="{{ route('admin.positions.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
                <button type="submit" class="brand-button rounded-lg px-5 py-2.5 text-sm font-bold">{{ $isEditing ? 'Save changes' : 'Create position' }}</button>
            </div>
        </form>

        @if($isEditing)
            <section class="rounded-2xl border border-blue-100 bg-blue-50/60 p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="font-bold text-slate-900">Applications for this position</h2>
                        <p class="mt-1 text-sm text-slate-600">Submitted applications remain available after the position closes.</p>
                    </div>
                    <a href="{{ route('admin.applications.index', ['position_id' => $position->id]) }}" class="font-bold text-brand-blue hover:text-brand-orange-dark">View applications <span aria-hidden="true">→</span></a>
                </div>
            </section>
        @endif
    </div>
@endsection
