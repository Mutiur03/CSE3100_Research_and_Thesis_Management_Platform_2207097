@props([
    'size' => 'md',
    'variant' => 'dark',
])

@php
    $sizes = [
        'xs' => 'h-4 w-4',
        'sm' => 'h-5 w-5',
        'md' => 'h-6 w-6',
        'lg' => 'h-7 w-7',
        'xl' => 'h-8 w-8',
    ];

    $frames = [
        'sm' => 'h-9 w-9 rounded',
        'md' => 'h-8 w-8 rounded',
        'lg' => 'h-12 w-12 rounded-lg shadow-lg shadow-brand-950/50',
        'xl' => 'h-14 w-14 rounded-lg shadow-lg shadow-brand-950/50',
    ];

    $iconClass = $sizes[$size] ?? $sizes['md'];
    $frameClass = $frames[$size] ?? $frames['md'];

    $iconColor = match ($variant) {
        'light', 'framed', 'full' => 'text-white',
        'brand' => 'text-brand-400',
        default => 'text-brand-600',
    };

    $frameBg = match ($variant) {
        'full' => 'bg-brand-600',
        'framed' => 'bg-navy-800',
        default => null,
    };
@endphp

@if ($frameBg)
    <div {{ $attributes->merge(['class' => "{$frameClass} flex shrink-0 items-center justify-center {$frameBg}"]) }}>
        <svg class="{{ $iconClass }} {{ $iconColor }} block shrink-0 translate-y-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" role="img" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
        </svg>
    </div>
@else
    <svg
        {{ $attributes->merge(['class' => "{$iconClass} {$iconColor}"]) }}
        fill="none"
        viewBox="0 0 24 24"
        stroke-width="2"
        stroke="currentColor"
        role="img"
        aria-hidden="true"
    >
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
    </svg>
@endif
