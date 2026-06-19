@extends('layouts.auth')

@section('title', 'Welcome')

@section('content')
    <div>
        <h2 class="font-display text-2xl text-stone-900">Get started</h2>
        <p class="mt-2 text-sm text-stone-500">Sign in with your institutional account or register as a student or supervisor.</p>
    </div>

    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
        @auth
            <a wire:navigate.hover href="{{ route('dashboard') }}" class="btn-primary text-center">Go to dashboard</a>
        @else
            <a wire:navigate.hover href="{{ route('login') }}" class="btn-primary text-center">Sign in</a>
            @if (Route::has('register'))
                <a wire:navigate.hover href="{{ route('register') }}" class="btn-secondary text-center">Create account</a>
            @endif
        @endauth
    </div>

    <p class="mt-10 text-xs leading-relaxed text-stone-500">
        Reviewer accounts are provisioned by your department administrator after platform setup is complete.
    </p>
@endsection
