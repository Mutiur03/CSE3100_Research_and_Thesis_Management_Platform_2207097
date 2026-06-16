@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
    <div class="page-shell max-w-2xl">
        <a href="{{ route('admin.users.index') }}" class="mb-6 inline-flex items-center gap-1 text-sm text-stone-500 hover:text-stone-800">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Back to users
        </a>

        <header class="page-header">
            <h2 class="page-title">Edit user</h2>
            <p class="page-lead">Update role and account status for {{ $user->name }}.</p>
        </header>

        <div class="card mb-6">
            <div class="card-body flex items-center gap-4">
                <img src="{{ $user->avatar_url }}" alt="" class="h-14 w-14 rounded object-cover ring-1 ring-stone-200">
                <div>
                    <p class="font-medium text-stone-900">{{ $user->name }}</p>
                    <p class="text-sm text-stone-500">{{ $user->email }}</p>
                    <p class="mt-1 text-xs text-stone-400">Member since {{ $user->created_at->format('M d, Y') }}</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6" id="admin-user-edit-form">
            @csrf
            @method('PUT')

            <div class="card">
                <div class="card-section">
                    <h3 class="text-sm font-semibold text-stone-900">Account settings</h3>
                </div>
                <div class="card-body space-y-5">
                    <div>
                        <label for="role" class="field-label">Role</label>
                        <select name="role" id="role" class="select-field @error('role') input-error @enderror">
                            @foreach(\App\Enums\UserRole::cases() as $role)
                                <option value="{{ $role->value }}" {{ old('role', $user->role->value) === $role->value ? 'selected' : '' }}>
                                    {{ $role->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('role')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <span class="field-label">Account status</span>
                        <div class="mt-2 flex gap-4">
                            <label class="flex cursor-pointer items-center gap-2 text-sm text-stone-700">
                                <input type="radio" name="is_active" value="1" class="text-navy-800 focus:ring-navy-700/20" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                Active
                            </label>
                            <label class="flex cursor-pointer items-center gap-2 text-sm text-stone-700">
                                <input type="radio" name="is_active" value="0" class="text-navy-800 focus:ring-navy-700/20" {{ old('is_active', $user->is_active) ? '' : 'checked' }}>
                                Inactive
                            </label>
                        </div>
                        @error('is_active')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.users.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" id="admin-user-save" class="btn-primary">Save changes</button>
            </div>
        </form>
    </div>
@endsection
