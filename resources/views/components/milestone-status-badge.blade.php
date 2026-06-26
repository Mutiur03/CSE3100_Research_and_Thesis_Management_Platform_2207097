@props(['status', 'overdue' => false])

@php
    $statusEnum = $status instanceof \App\Enums\MilestoneStatus ? $status : \App\Enums\MilestoneStatus::from($status);
    $isOpen = in_array($statusEnum, \App\Enums\MilestoneStatus::openCases(), true);
    $classes = $overdue && $isOpen
        ? 'bg-amber-50 text-amber-900 ring-1 ring-amber-200'
        : $statusEnum->color();
    $label = $overdue && $isOpen
        ? 'Overdue'
        : $statusEnum->label();
@endphp

<span class="inline-flex items-center rounded px-2 py-0.5 text-xs font-medium {{ $classes }}">
    {{ $label }}
</span>
