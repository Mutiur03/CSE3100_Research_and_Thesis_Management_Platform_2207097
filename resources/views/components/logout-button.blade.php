@props([
    'class' => 'block w-full px-4 py-2 text-left text-sm text-red-700 hover:bg-red-50',
    'label' => 'Sign out',
])

<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit" {{ $attributes->merge(['class' => $class]) }}>
        {{ $label }}
    </button>
</form>
