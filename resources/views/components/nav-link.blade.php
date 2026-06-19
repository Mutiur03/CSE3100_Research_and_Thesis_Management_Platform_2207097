@props(['href', 'active' => false])

@php
    $classes = $active
        ? 'flex items-center gap-3 rounded border-l-2 border-brand-600 bg-stone-100 py-2.5 pl-[calc(0.75rem-2px)] pr-3 text-sm font-medium text-stone-900'
        : 'flex items-center gap-3 rounded border-l-2 border-transparent py-2.5 pl-[calc(0.75rem-2px)] pr-3 text-sm font-medium text-stone-600 transition-colors hover:bg-stone-50 hover:text-stone-900';
@endphp

<a href="{{ $href }}" wire:navigate.hover {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
