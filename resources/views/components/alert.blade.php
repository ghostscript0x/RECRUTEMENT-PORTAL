@php($success = session('success'))

@if($success || $errors->any())
    <div class="mx-auto max-w-6xl px-4 pt-4 sm:px-6">
        @if($success)
            <div class="mb-3 flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                <span class="mt-0.5 font-semibold">✓</span>
                <p>{{ $success }}</p>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-3 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <p class="mb-1 font-semibold">Please correct the following:</p>
                <ul class="list-inside list-disc space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endif
