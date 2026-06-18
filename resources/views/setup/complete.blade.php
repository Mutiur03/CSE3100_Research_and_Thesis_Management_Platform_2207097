@extends('layouts.auth')

@section('title', 'Complete Setup')

@section('content')
    <div>
        <h2 class="font-display text-2xl text-stone-900">Complete administrator setup</h2>
        <p class="mt-2 text-sm text-stone-500">
            Enter the setup code from your email and choose administrator credentials.
        </p>
    </div>

    <form method="POST" action="{{ route('setup.complete.store') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <label for="admin-email" class="field-label">Administrator email</label>
            <input
                type="email"
                id="admin-email"
                value="{{ $adminEmail }}"
                disabled
                class="input-field bg-stone-50 text-stone-500"
            >
            <p class="field-hint">Displayed as {{ $maskedEmail }}. This cannot be changed during setup.</p>
        </div>

        <div>
            <label for="code" class="field-label">Setup code</label>
            <input
                type="text"
                name="code"
                id="code"
                value="{{ old('code') }}"
                required
                autocomplete="one-time-code"
                class="input-field font-mono tracking-widest @error('code') input-error @enderror"
                placeholder="XXXX-XXXX-XXXX-XXXX"
            >
            @error('code')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="name" class="field-label">Full name</label>
            <input
                type="text"
                name="name"
                id="name"
                value="{{ old('name') }}"
                required
                autocomplete="name"
                class="input-field @error('name') input-error @enderror"
            >
            @error('name')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="field-label">Password</label>
            <input
                type="password"
                name="password"
                id="password"
                required
                autocomplete="new-password"
                class="input-field @error('password') input-error @enderror"
            >
            @error('password')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="field-label">Confirm password</label>
            <input
                type="password"
                name="password_confirmation"
                id="password_confirmation"
                required
                autocomplete="new-password"
                class="input-field"
            >
        </div>

        <button type="submit" class="btn-primary w-full">
            Create administrator
        </button>
    </form>

    <p class="mt-8 text-center text-sm text-stone-500">
        Need a new code?
        <a href="{{ route('setup.index') }}" class="font-medium text-brand-700 hover:text-brand-800">Request another code</a>
    </p>
@endsection
