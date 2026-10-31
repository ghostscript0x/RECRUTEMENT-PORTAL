@php($files = $draft['files'] ?? [])
@php($fileNames = $draft['file_names'] ?? [])

<p class="mb-4 text-sm text-slate-500">
    Accepted formats: <strong>{{ implode(', ', config('recruitment.allowed_mimes')) }}</strong> ·
    Maximum size: <strong>{{ config('recruitment.max_upload_mb') }}MB</strong> per file.
</p>

<div class="space-y-4">
    @foreach($position->documentRequirements() as $document)
        @php($key = $document['key'])
        @php($required = $document['required'] ?? false)

        <div class="rounded-xl border border-slate-200 p-4">
            <div class="flex items-center justify-between gap-3">
                <label for="documents-{{ $key }}" class="text-sm font-medium text-slate-800">
                    {{ $document['label'] }}
                    <span class="{{ $required ? 'text-rose-600' : 'text-slate-400' }}">{{ $required ? '*' : '(optional)' }}</span>
                </label>
                @if(isset($files[$key]))
                    <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-700">
                        Uploaded — {{ $fileNames[$key] ?? basename($files[$key]) }}
                    </span>
                @endif
            </div>

            <input type="file"
                   id="documents-{{ $key }}"
                   name="documents[{{ $key }}]"
                   accept=".pdf,.jpg,.jpeg,.png,.webp"
                   class="mt-3 block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-blue-700 hover:file:bg-blue-100">
            @error('documents.'.$key)<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            <p class="mt-1 text-xs text-slate-400">Uploading a new file replaces the previous one for this document.</p>
        </div>
    @endforeach
</div>
