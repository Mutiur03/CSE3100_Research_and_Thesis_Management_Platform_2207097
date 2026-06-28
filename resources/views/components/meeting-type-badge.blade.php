@props(['type'])

@php
    $typeEnum = $type instanceof \App\Enums\MeetingType ? $type : \App\Enums\MeetingType::from($type);
@endphp

<span class="inline-flex items-center rounded px-2 py-0.5 text-xs font-medium {{ $typeEnum->color() }}">
    {{ $typeEnum->label() }}
</span>
