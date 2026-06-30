@props([
    'size' => 'md',
    'showName' => true,
    'showTagline' => false,
    'framed' => false,
    'variant' => 'full',
    'href' => null,
])

@php
    $name = config('app.name', 'ResearchHub');
    $tag = $href ? 'a' : 'div';

    $logoVariant = $framed ? 'framed' : ($variant === 'full' ? 'full' : 'dark');
    $logoSize = match ($size) {
        'sm' => 'sm',
        'lg' => 'lg',
        default => 'md',
    };
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" wire:navigate.hover @endif
    {{ $attributes->merge(['class' => 'flex items-center gap-3 min-w-0']) }}
>
    <x-logo :size="$logoSize" :variant="$logoVariant" />

    @if ($slot->isNotEmpty() || $showName || $showTagline)
        <div class="min-w-0">
            @if ($slot->isNotEmpty())
                {{ $slot }}
            @else
                @if ($showName)
                    <p class="truncate text-sm font-semibold text-stone-900">{{ $name }}</p>
                @endif
                @if ($showTagline)
                    <p class="truncate text-[11px] text-stone-500">Thesis Management</p>
                @endif
            @endif
        </div>
    @endif
</{{ $tag }}>
