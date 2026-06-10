@extends('layouts.auth')

@section('title', 'Set New Password')

@section('content')
    <div>
        <h2 class="font-display text-2xl text-stone-900">Set new password</h2>
        <p class="mt-2 text-sm text-stone-500">Choose a strong password for your account.</p>
    </div>

    <form method="POST" action="{{ route('password.update') }}" class="mt-8 space-y-5" id="reset-password-form">
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
                class="input-field bg-stone-50 @error('email') input-error @enderror"
            >
            @error('email')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="field-label">New password</label>
            <input
                type="password"
                name="password"
                id="password"
                required
                autofocus
                autocomplete="new-password"
                class="input-field @error('password') input-error @enderror"
            >
            @error('password')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="field-label">Confirm new password</label>
            <input
                type="password"
                name="password_confirmation"
                id="password_confirmation"
                required
                autocomplete="new-password"
                class="input-field"
            >
        </div>

        <button type="submit" id="reset-password-submit" class="btn-primary w-full">
            Update password
        </button>
    </form>
@endsection
