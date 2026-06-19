@extends('layouts.auth')

@section('title', 'Create Account')

@section('content')
    <div>
        <h2 class="font-display text-2xl text-stone-900">Create account</h2>
        <p class="mt-2 text-sm text-stone-500">Register with your institutional email address.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5" id="register-form">
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
                autocomplete="name"
                class="input-field @error('name') input-error @enderror"
                placeholder="Dr. Jane Smith"
            >
            @error('name')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="field-label">Email address</label>
            <input
                type="email"
                name="email"
                id="email"
                value="{{ old('email') }}"
                required
                autocomplete="email"
                class="input-field @error('email') input-error @enderror"
                placeholder="name@university.edu"
            >
            @error('email')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <span class="field-label">Account type</span>
            <div class="mt-2 grid grid-cols-2 gap-3">
                <label class="flex cursor-pointer flex-col rounded border border-stone-300 p-3 transition-colors has-[:checked]:border-navy-700 has-[:checked]:bg-navy-50">
                    <input type="radio" name="role" value="student" class="sr-only" {{ old('role', 'student') === 'student' ? 'checked' : '' }}>
                    <span class="text-sm font-medium text-stone-800">Student</span>
                    <span class="mt-0.5 text-xs text-stone-500">Submit and track research</span>
                </label>
                <label class="flex cursor-pointer flex-col rounded border border-stone-300 p-3 transition-colors has-[:checked]:border-navy-700 has-[:checked]:bg-navy-50">
                    <input type="radio" name="role" value="supervisor" class="sr-only" {{ old('role') === 'supervisor' ? 'checked' : '' }}>
                    <span class="text-sm font-medium text-stone-800">Supervisor</span>
                    <span class="mt-0.5 text-xs text-stone-500">Guide student research</span>
                </label>
            </div>
            @error('role')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        @if($departments->isNotEmpty())
            <div>
                <label for="department_id" class="field-label">Department</label>
                <select name="department_id" id="department_id" class="select-field @error('department_id') input-error @enderror">
                    <option value="">Select department (optional)</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ (string) old('department_id') === (string) $department->id ? 'selected' : '' }}>
                            {{ $department->display_name }}
                        </option>
                    @endforeach
                </select>
                @error('department_id')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>
        @endif

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
            <p class="field-hint">Minimum 8 characters.</p>
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

        <button type="submit" id="register-submit" class="btn-primary w-full">
            Create account
        </button>
    </form>

    <p class="mt-8 text-center text-sm text-stone-500">
        Already registered?
        <a href="{{ route('login') }}" class="font-medium text-brand-700 hover:text-brand-800">Sign in</a>
    </p>
@endsection
