@extends('layouts.auth')

@section('title', 'Set New Password')

@section('content')
    <div>
        <h2 class="font-display text-2xl text-stone-900">Set new password</h2>
        <p class="mt-2 text-sm leading-relaxed text-stone-500">Choose a new password for your account, then sign in with it.</p>
    </div>

    <form method="POST" action="{{ route('password.update') }}" class="mt-8 space-y-5" id="reset-password-form" data-auth-form>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label for="email" class="field-label">Email address</label>
            <input
                type="email"
                name="email"
                id="email"
                value="{{ old('email', $email) }}"
                required
                autocomplete="email"
                spellcheck="false"
                inputmode="email"
                readonly
                class="input-field bg-stone-50 text-stone-600 @error('email') input-error @enderror"
                @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
            >
            @error('email')
                <p id="email-error" class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <x-password-field
            name="password"
            label="New password"
            autocomplete="new-password"
            :autofocus="true"
            hint="Minimum 8 characters."
        />

        <div>
            <x-password-field
                name="password_confirmation"
                id="password_confirmation"
                label="Confirm new password"
                autocomplete="new-password"
            />
            <p class="field-hint mt-1.5 hidden" data-password-match aria-live="polite"></p>
        </div>

        <button type="submit" id="reset-password-submit" class="btn-primary w-full" data-loading-label="Updating password…">
            Update password
        </button>
    </form>

    <p class="mt-8 text-center text-sm text-stone-500">
        <a wire:navigate.hover href="{{ route('login') }}" class="font-medium text-brand-700 hover:text-brand-800">Back to sign in</a>
    </p>
@endsection
