@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
    <div>
        <h2 class="font-display text-2xl text-stone-900">Reset password</h2>
        <p class="mt-2 text-sm text-stone-500">Enter your email and we will send a reset link.</p>
    </div>

    <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-5" id="forgot-password-form">
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
                class="input-field @error('email') input-error @enderror"
                placeholder="name@university.edu"
            >
            @error('email')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" id="forgot-password-submit" class="btn-primary w-full">
            Send reset link
        </button>
    </form>

    <p class="mt-8 text-center text-sm text-stone-500">
        <a wire:navigate.hover href="{{ route('login') }}" class="font-medium text-brand-700 hover:text-brand-800">Back to sign in</a>
    </p>
@endsection
