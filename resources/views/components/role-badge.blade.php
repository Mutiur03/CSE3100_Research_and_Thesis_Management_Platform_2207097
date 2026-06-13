@props(['role'])

@php
    $roleEnum = $role instanceof \App\Enums\UserRole ? $role : \App\Enums\UserRole::from($role);
@endphp

<span class="inline-flex items-center rounded px-2 py-0.5 text-xs font-medium {{ $roleEnum->color() }}">
    {{ $roleEnum->label() }}
</span>
