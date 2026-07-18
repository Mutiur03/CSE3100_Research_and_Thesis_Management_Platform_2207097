@extends('layouts.auth')

@section('title', 'Complete Setup')

@section('content')
    <div>
        <h2 class="font-display text-2xl text-stone-900">Complete administrator setup</h2>
        <p class="mt-2 text-sm leading-relaxed text-stone-500">
            Enter the setup code from your email and choose administrator credentials.
        </p>
    </div>

    <form method="POST" action="{{ route('setup.complete.store') }}" class="mt-8 space-y-5" data-auth-form>
        @csrf

        <div>
            <label for="admin-email" class="field-label">Administrator email</label>
            <input
                type="email"
                id="admin-email"
                value="{{ $adminEmail }}"
                disabled
                spellcheck="false"
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
                autofocus
                minlength="8"
                maxlength="32"
                autocomplete="one-time-code"
                class="input-field font-mono tracking-widest @error('code') input-error @enderror"
                spellcheck="false"
                placeholder="XXXX-XXXX-XXXX-XXXX"
                @error('code') aria-invalid="true" aria-describedby="code-error" @enderror
            >
            @error('code')
                <p id="code-error" class="field-error">{{ $message }}</p>
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
                maxlength="255"
                autocomplete="name"
                class="input-field @error('name') input-error @enderror"
                @error('name') aria-invalid="true" aria-describedby="name-error" @enderror
            >
            @error('name')
                <p id="name-error" class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <x-password-field
            name="password"
            label="Password"
            autocomplete="new-password"
            :minlength="8"
            hint="Minimum 8 characters."
        />

        <div>
            <x-password-field
                name="password_confirmation"
                id="password_confirmation"
                label="Confirm password"
                autocomplete="new-password"
                :minlength="8"
            />
            <p class="field-hint mt-1.5 hidden" data-password-match aria-live="polite"></p>
        </div>

        <button type="submit" class="btn-primary w-full" data-loading-label="Creating administrator…">
            Create administrator
        </button>
    </form>

    <p class="mt-8 text-center text-sm text-stone-500">
        Need a new code?
        <a wire:navigate.hover href="{{ route('setup.index') }}" class="font-medium text-brand-700 hover:text-brand-800">Request another code</a>
    </p>
@endsection
