@extends('layouts.app')

@section('title', $department->name)

@section('content')
    <div class="page-shell">
        <a href="{{ route('admin.departments.index') }}" class="mb-6 inline-flex items-center gap-1 text-sm text-stone-500 hover:text-stone-800">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Back to departments
        </a>

        <header class="page-header flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-stone-400">{{ $department->code }}</p>
                <h2 class="page-title">{{ $department->name }}</h2>
                <p class="page-lead">{{ $department->faculty ?? 'No faculty assigned' }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.departments.edit', $department) }}" class="btn-secondary">Edit</a>
                @if($department->users_count === 0)
                    <form method="POST" action="{{ route('admin.departments.destroy', $department) }}" onsubmit="return confirm('Delete this department permanently?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-secondary text-red-700 hover:bg-red-50">Delete</button>
                    </form>
                @endif
            </div>
        </header>

        @error('delete')
            <div class="mb-6">
                <x-alert type="error" :message="$message" />
            </div>
        @enderror

        <section class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div class="stat-card">
                <p class="stat-value">{{ $department->users_count }}</p>
                <p class="stat-label">Total members</p>
            </div>
            <div class="stat-card">
                <p class="stat-value">{{ $department->students_count }}</p>
                <p class="stat-label">Students</p>
            </div>
            <div class="stat-card">
                <p class="stat-value">{{ $department->supervisors_count }}</p>
                <p class="stat-label">Supervisors</p>
            </div>
            <div class="stat-card">
                <p class="stat-value">{{ $department->reviewers_count }}</p>
                <p class="stat-label">Reviewers</p>
            </div>
            <div class="stat-card">
                <p class="stat-value">{{ $department->admins_count }}</p>
                <p class="stat-label">Admins</p>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="card lg:col-span-1">
                <div class="card-section">
                    <h3 class="text-sm font-semibold text-stone-900">Overview</h3>
                </div>
                <div class="card-body space-y-4 text-sm">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-stone-400">Department head</p>
                        @if($department->head)
                            <p class="mt-1 font-medium text-stone-800">{{ $department->head->name }}</p>
                            <p class="text-stone-500">{{ $department->head->email }}</p>
                        @else
                            <p class="mt-1 text-stone-500">Not assigned</p>
                        @endif
                    </div>
                    @if($department->description)
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-stone-400">Description</p>
                            <p class="mt-1 text-stone-600">{{ $department->description }}</p>
                        </div>
                    @endif
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-stone-400">Created</p>
                        <p class="mt-1 text-stone-600">{{ $department->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>

            <div class="card overflow-hidden lg:col-span-2">
                <div class="card-section">
                    <h3 class="text-sm font-semibold text-stone-900">Members</h3>
                    <p class="mt-0.5 text-sm text-stone-500">Users affiliated with this department.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead class="table-head">
                            <tr>
                                <th class="px-6 py-3">User</th>
                                <th class="px-6 py-3">Role</th>
                                <th class="px-6 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 bg-white">
                            @forelse($members as $member)
                                <tr class="hover:bg-stone-50/80">
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-stone-800">{{ $member->name }}</p>
                                        <p class="text-xs text-stone-500">{{ $member->email }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <x-role-badge :role="$member->role" />
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('admin.users.edit', $member) }}" class="btn-secondary btn-sm">Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-12 text-center text-sm text-stone-500">
                                        No members yet. Assign users via user management.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($members->hasPages())
                    <div class="border-t border-stone-200 bg-stone-50 px-6 py-3">
                        {{ $members->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
