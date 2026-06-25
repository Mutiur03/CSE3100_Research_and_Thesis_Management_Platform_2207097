@props(['category'])

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded px-2 py-0.5 text-xs font-medium '.$category->color()]) }}>
    {{ $category->label() }}
</span>
