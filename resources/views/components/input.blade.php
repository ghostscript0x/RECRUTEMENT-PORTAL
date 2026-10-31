@props(['name', 'label', 'type' => 'text', 'required' => false, 'value' => null, 'placeholder' => null, 'autocomplete' => null])

<div class="mb-4">
    <label for="{{ $attributes->get('id', $name) }}" class="mb-1 block text-sm font-medium text-slate-700">
        {{ $label }} @if($required)<span class="text-rose-600" title="Required">*</span>@endif
    </label>
    <input
        type="{{ $type }}"
        id="{{ $attributes->get('id', $name) }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        @if($required) required @endif
        {{ $attributes->except('id') }}
        @class([
            'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100',
            'border-rose-400 focus:border-rose-500 focus:ring-rose-100' => $errors->has($name),
        ])
    >
    @error($name)<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
</div>
