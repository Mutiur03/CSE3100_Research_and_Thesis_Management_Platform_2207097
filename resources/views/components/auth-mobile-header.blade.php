<div class="mb-8 flex items-center justify-between gap-4 lg:hidden">
    <x-brand size="sm" framed :href="route('home')" />
    @unless(request()->routeIs('home'))
        <a wire:navigate.hover href="{{ route('home') }}" class="text-xs font-medium text-stone-500 hover:text-stone-800">Home</a>
    @endunless
</div>
