@props(['status'])

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded px-2 py-0.5 text-xs font-medium '.$status->color()]) }}>
    {{ $status->label() }}
</span>
