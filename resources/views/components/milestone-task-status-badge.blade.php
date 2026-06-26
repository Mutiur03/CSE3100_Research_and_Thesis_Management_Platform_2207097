@props(['status'])

@php
    $statusEnum = $status instanceof \App\Enums\MilestoneTaskStatus ? $status : \App\Enums\MilestoneTaskStatus::from($status);
@endphp

<span class="inline-flex items-center rounded px-2 py-0.5 text-xs font-medium {{ $statusEnum->color() }}">
    {{ $statusEnum->label() }}
</span>
