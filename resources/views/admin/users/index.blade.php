@extends('layouts.app')

@section('title', 'Manage Users')

@section('content')
    <div class="page-shell">
        <header class="page-header">
            <h2 class="page-title">User management</h2>
            <p class="page-lead">Manage platform accounts. Promote a user to administrator from their edit page when needed.</p>
        </header>

        <div class="card mb-6">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col gap-4 sm:flex-row sm:items-end" id="user-filter-form">
                    <div class="flex-1">
                        <label for="search" class="field-label">Search</label>
                        <input
                            type="text"
                            name="search"
                            id="search"
                            value="{{ $search }}"
                            placeholder="Name or email"
                            class="input-field"
                        >
                    </div>
                    <div class="w-full sm:w-44">
                        <label for="role" class="field-label">Role</label>
                        <select name="role" id="role" class="select-field">
                            <option value="">All roles</option>
                            @foreach(\App\Enums\UserRole::manageableCases() as $roleOption)
                                <option value="{{ $roleOption->value }}" {{ $roleFilter === $roleOption->value ? 'selected' : '' }}>{{ $roleOption->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full sm:w-44">
                        <label for="department_id" class="field-label">Department</label>
                        <select name="department_id" id="department_id" class="select-field">
                            <option value="">All departments</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ (string) $departmentFilter === (string) $department->id ? 'selected' : '' }}>
                                    {{ $department->code }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="btn-primary">Apply</button>
                        @if($search || $roleFilter || $departmentFilter)
                            <a wire:navigate.hover href="{{ route('admin.users.index') }}" class="btn-secondary">Clear</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="data-table" id="users-table">
                    <thead class="table-head">
                        <tr>
                            <th class="px-6 py-3">User</th>
                            <th class="px-6 py-3">Role</th>
                            <th class="px-6 py-3">Department</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Joined</th>
                            <th class="px-6 py-3">Last login</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 bg-white">
                        @forelse($users as $u)
                            <tr class="hover:bg-stone-50/80">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $u->avatar_url }}" alt="" class="h-8 w-8 rounded object-cover ring-1 ring-stone-200">
                                        <div>
                                            <p class="font-medium text-stone-800">{{ $u->name }}</p>
                                            <p class="text-xs text-stone-500">{{ $u->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <x-role-badge :role="$u->role" />
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-stone-600">
                                    {{ $u->department?->code ?? '—' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    @if($u->is_active)
                                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-800">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span> Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-stone-500">
                                            <span class="h-1.5 w-1.5 rounded-full bg-stone-400"></span> Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-stone-600">
                                    {{ $u->created_at->format('M d, Y') }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-stone-600">
                                    {{ $u->last_login_at ? $u->last_login_at->diffForHumans() : 'Never' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <a wire:navigate.hover href="{{ route('admin.users.edit', $u) }}" class="btn-secondary btn-sm">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <p class="text-sm font-medium text-stone-700">No users found</p>
                                    <p class="mt-1 text-sm text-stone-500">Try adjusting your search or filters.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="border-t border-stone-200 bg-stone-50 px-6 py-3">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
