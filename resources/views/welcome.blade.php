@extends('layouts.auth')

@section('title', 'Welcome')

@section('content')
    <div>
        <h2 class="font-display text-3xl tracking-tight text-stone-900">Get started</h2>
        <p class="mt-3 text-base leading-relaxed text-stone-500">Sign in with your institutional account, or create one as a student or supervisor.</p>
    </div>

    <div class="mt-10 flex flex-col gap-3">
        @auth
            <a wire:navigate.hover href="{{ route('dashboard') }}" class="btn-primary w-full text-center">Go to dashboard</a>
        @else
            <a wire:navigate.hover href="{{ route('login') }}" class="btn-primary w-full text-center">Sign in</a>
            @if (Route::has('register'))
                <a wire:navigate.hover href="{{ route('register') }}" class="btn-secondary w-full text-center">Create account</a>
            @endif
        @endauth
    </div>

    <p class="mt-10 text-xs leading-relaxed text-stone-500">
        Students and supervisors can self-register. Administrator accounts are created during platform setup.
    </p>
@endsection
