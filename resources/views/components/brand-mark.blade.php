@props(['size' => 'h-10 w-12'])

<img src="{{ asset('images/fintec-logo.png') }}" alt=""
     {{ $attributes->class([$size, 'shrink-0 object-contain']) }}>
