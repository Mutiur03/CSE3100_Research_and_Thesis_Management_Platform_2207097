@extends('layouts.auth')

@section('title', 'Sign In')

@section('content')
    <div>
        <h2 class="font-display text-2xl text-stone-900">Sign in</h2>
        <p class="mt-2 text-sm leading-relaxed text-stone-500">Use your institutional email to access proposals, supervision, and records.</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5" id="login-form" data-auth-form>
        @csrf

        <div>
            <label for="email" class="field-label">Email address</label>
            <input
                type="email"
                name="email"
                id="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="email"
                spellcheck="false"
                inputmode="email"
                class="input-field @error('email') input-error @enderror"
                placeholder="name@dept.kuet.ac.bd"
                @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
            >
            @error('email')
                <p id="email-error" class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <x-password-field name="password" label="Password" autocomplete="current-password">
            <x-slot:labelTrailing>
                <a wire:navigate.hover href="{{ route('password.request') }}" class="text-xs font-medium text-brand-700 hover:text-brand-800">Forgot password?</a>
            </x-slot:labelTrailing>
        </x-password-field>

        <div class="flex items-center gap-2.5">
            <input type="checkbox" name="remember" id="remember" class="h-4 w-4 rounded border-stone-300 text-navy-800 focus:ring-navy-700/20" {{ old('remember') ? 'checked' : '' }}>
            <label for="remember" class="text-sm text-stone-600">Keep me signed in on this device</label>
        </div>

        <button type="submit" id="login-submit" class="btn-primary w-full" data-loading-label="Signing in…">
            Sign in
        </button>
    </form>

    <p class="mt-8 text-center text-sm text-stone-500">
        No account?
        <a wire:navigate.hover href="{{ route('register') }}" class="font-medium text-brand-700 hover:text-brand-800">Create account</a>
    </p>
@endsection
