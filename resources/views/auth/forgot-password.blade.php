@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
    <div>
        <h2 class="font-display text-2xl text-stone-900">Reset password</h2>
        <p class="mt-2 text-sm leading-relaxed text-stone-500">Enter the email on your account. If it matches a registered user, we will send a reset link.</p>
    </div>

    <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-5" id="forgot-password-form" data-auth-form>
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
                aria-describedby="email-hint{{ $errors->has('email') ? ' email-error' : '' }}"
                @error('email') aria-invalid="true" @enderror
            >
            <p id="email-hint" class="field-hint">Check your inbox and spam folder after requesting a link.</p>
            @error('email')
                <p id="email-error" class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" id="forgot-password-submit" class="btn-primary w-full" data-loading-label="Sending link…">
            Send reset link
        </button>
    </form>

    <p class="mt-8 text-center text-sm text-stone-500">
        Remembered it?
        <a wire:navigate.hover href="{{ route('login') }}" class="font-medium text-brand-700 hover:text-brand-800">Back to sign in</a>
    </p>
@endsection
