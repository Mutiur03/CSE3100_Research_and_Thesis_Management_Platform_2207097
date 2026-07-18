@extends('layouts.auth')

@section('title', 'Create Account')

@section('content')
    <div>
        <h2 class="font-display text-2xl text-stone-900">Create account</h2>
        <p class="mt-2 text-sm leading-relaxed text-stone-500">Register with your institutional email. Students and supervisors can self-register.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5" id="register-form" data-auth-form>
        @csrf

        <div>
            <label for="name" class="field-label">Full name</label>
            <input
                type="text"
                name="name"
                id="name"
                value="{{ old('name') }}"
                required
                autofocus
                maxlength="255"
                autocomplete="name"
                class="input-field @error('name') input-error @enderror"
                placeholder="Your full name"
                @error('name') aria-invalid="true" aria-describedby="name-error" @enderror
            >
            @error('name')
                <p id="name-error" class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <fieldset>
            <legend class="field-label">I am a</legend>
            <div class="mt-2 grid grid-cols-2 gap-3">
                <label class="group flex cursor-pointer flex-col rounded border border-stone-300 p-3.5 transition-colors has-[:checked]:border-navy-700 has-[:checked]:bg-navy-50 has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-navy-700/20">
                    <input type="radio" name="role" value="student" class="sr-only" {{ old('role', 'student') === 'student' ? 'checked' : '' }}>
                    <span class="flex items-center justify-between gap-2">
                        <span class="text-sm font-medium text-stone-800">Student</span>
                        <span class="flex h-4 w-4 items-center justify-center rounded-full border border-stone-300 group-has-[:checked]:border-navy-700 group-has-[:checked]:bg-navy-800" aria-hidden="true">
                            <span class="h-1.5 w-1.5 rounded-full bg-white opacity-0 group-has-[:checked]:opacity-100"></span>
                        </span>
                    </span>
                    <span class="mt-1 text-xs leading-relaxed text-stone-500">Submit proposals and track thesis progress</span>
                </label>
                <label class="group flex cursor-pointer flex-col rounded border border-stone-300 p-3.5 transition-colors has-[:checked]:border-navy-700 has-[:checked]:bg-navy-50 has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-navy-700/20">
                    <input type="radio" name="role" value="supervisor" class="sr-only" {{ old('role') === 'supervisor' ? 'checked' : '' }}>
                    <span class="flex items-center justify-between gap-2">
                        <span class="text-sm font-medium text-stone-800">Supervisor</span>
                        <span class="flex h-4 w-4 items-center justify-center rounded-full border border-stone-300 group-has-[:checked]:border-navy-700 group-has-[:checked]:bg-navy-800" aria-hidden="true">
                            <span class="h-1.5 w-1.5 rounded-full bg-white opacity-0 group-has-[:checked]:opacity-100"></span>
                        </span>
                    </span>
                    <span class="mt-1 text-xs leading-relaxed text-stone-500">Guide students and review submissions</span>
                </label>
            </div>
            @error('role')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </fieldset>

        <div>
            <label for="email" class="field-label">Email address</label>
            @php
                $selectedRole = old('role', 'student');
                $emailPlaceholder = $selectedRole === 'supervisor'
                    ? 'karim@cse.kuet.ac.bd'
                    : 'rahman21041@stud.kuet.ac.bd';
                $emailHint = $selectedRole === 'supervisor'
                    ? 'Use your faculty email (e.g. name@dept.kuet.ac.bd).'
                    : 'Use your student email (e.g. lastnameroll@stud.kuet.ac.bd).';
            @endphp
            <input
                type="email"
                name="email"
                id="email"
                value="{{ old('email') }}"
                required
                autocomplete="email"
                spellcheck="false"
                inputmode="email"
                class="input-field @error('email') input-error @enderror"
                placeholder="{{ $emailPlaceholder }}"
                aria-describedby="email-hint{{ $errors->has('email') ? ' email-error' : '' }}"
                @error('email') aria-invalid="true" @enderror
            >
            <p id="email-hint" class="field-hint" data-email-hint>{{ $emailHint }}</p>
            @error('email')
                <p id="email-error" class="field-error">{{ $message }}</p>
            @enderror
        </div>

        @if($departments->isNotEmpty())
            <div>
                <label for="department_id" class="field-label">Department <span class="font-normal text-stone-400">(optional)</span></label>
                <select name="department_id" id="department_id" class="select-field @error('department_id') input-error @enderror" @error('department_id') aria-invalid="true" aria-describedby="department-error" @enderror>
                    <option value="">Select your department</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ (string) old('department_id') === (string) $department->id ? 'selected' : '' }}>
                            {{ $department->display_name }}
                        </option>
                    @endforeach
                </select>
                @error('department_id')
                    <p id="department-error" class="field-error">{{ $message }}</p>
                @enderror
            </div>
        @endif

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

        <button type="submit" id="register-submit" class="btn-primary w-full" data-loading-label="Creating account…">
            Create account
        </button>
    </form>

    <p class="mt-8 text-center text-sm text-stone-500">
        Already registered?
        <a wire:navigate.hover href="{{ route('login') }}" class="font-medium text-brand-700 hover:text-brand-800">Sign in</a>
    </p>
@endsection
