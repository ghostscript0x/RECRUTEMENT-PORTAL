@props(['status'])

@php($badge = $status instanceof \App\Enums\ApplicationStatus ? $status : \App\Enums\ApplicationStatus::from($status))

<span @class(['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset', $badge->badge()])>
    {{ $badge->label() }}
</span>
