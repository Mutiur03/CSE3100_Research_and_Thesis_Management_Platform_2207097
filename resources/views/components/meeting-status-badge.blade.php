@props(['status'])

@php
    $statusEnum = $status instanceof \App\Enums\MeetingStatus ? $status : \App\Enums\MeetingStatus::from($status);
@endphp

<span class="inline-flex items-center rounded px-2 py-0.5 text-xs font-medium {{ $statusEnum->color() }}">
    {{ $statusEnum->label() }}
</span>
