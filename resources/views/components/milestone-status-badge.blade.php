@props(['status', 'overdue' => false])

@php
    $statusEnum = $status instanceof \App\Enums\MilestoneStatus ? $status : \App\Enums\MilestoneStatus::from($status);
    $classes = $overdue && $statusEnum === \App\Enums\MilestoneStatus::Pending
        ? 'bg-amber-50 text-amber-900 ring-1 ring-amber-200'
        : $statusEnum->color();
    $label = $overdue && $statusEnum === \App\Enums\MilestoneStatus::Pending
        ? 'Overdue'
        : $statusEnum->label();
@endphp

<span class="inline-flex items-center rounded px-2 py-0.5 text-xs font-medium {{ $classes }}">
    {{ $label }}
</span>
