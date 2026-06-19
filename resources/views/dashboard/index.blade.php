@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="page-shell">
        <header class="page-header flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="page-title">Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ Str::words($user->name, 2, '') }}</h2>
                <p class="page-lead">Overview of your research activity and pending actions.</p>
            </div>
            <x-role-badge :role="$user->role" />
        </header>

        <section class="mb-8">
            <h3 class="mb-4 text-xs font-semibold uppercase tracking-wide text-stone-500">Summary</h3>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @if($user->isStudent())
                    @foreach([
                        ['My Proposals', 0],
                        ['Active Projects', 0],
                        ['Milestones Due', 0],
                        ['Upcoming Meetings', 0],
                    ] as [$label, $value])
                        <div class="stat-card">
                            <p class="stat-value">{{ $value }}</p>
                            <p class="stat-label">{{ $label }}</p>
                        </div>
                    @endforeach
                @endif

                @if($user->isSupervisor())
                    @foreach([
                        ['My Students', 0],
                        ['Pending Reviews', 0],
                        ['Active Projects', 0],
                        ['Supervision Requests', 0],
                    ] as [$label, $value])
                        <div class="stat-card">
                            <p class="stat-value">{{ $value }}</p>
                            <p class="stat-label">{{ $label }}</p>
                        </div>
                    @endforeach
                @endif

                @if($user->isAdmin())
                    <div class="stat-card">
                        <p class="stat-value">{{ $stats['total_users'] }}</p>
                        <p class="stat-label">Total Users</p>
                    </div>
                    <div class="stat-card">
                        <p class="stat-value">{{ $stats['active_users'] }}</p>
                        <p class="stat-label">Active Accounts</p>
                    </div>
                    <div class="stat-card">
                        <p class="stat-value">0</p>
                        <p class="stat-label">Total Projects</p>
                    </div>
                    <div class="stat-card">
                        <p class="stat-value">{{ $stats['total_departments'] }}</p>
                        <p class="stat-label">Departments</p>
                    </div>
                @endif

                @if($user->isReviewer())
                    <div class="stat-card">
                        <p class="stat-value">0</p>
                        <p class="stat-label">Assigned Reviews</p>
                    </div>
                    <div class="stat-card">
                        <p class="stat-value">0</p>
                        <p class="stat-label">Completed Reviews</p>
                    </div>
                @endif
            </div>
        </section>

        <section class="card">
            <div class="card-section">
                <h3 class="text-sm font-semibold text-stone-900">Quick actions</h3>
                <p class="mt-0.5 text-sm text-stone-500">Common tasks for your role.</p>
            </div>
            <div class="divide-y divide-stone-100">
                <a href="{{ route('profile.show') }}" class="flex items-center justify-between px-6 py-4 text-sm transition-colors hover:bg-stone-50">
                    <div>
                        <p class="font-medium text-stone-800">Update profile</p>
                        <p class="text-stone-500">Bio, contact details, and research interests</p>
                    </div>
                    <svg class="h-4 w-4 text-stone-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                </a>

                @if($user->isAdmin())
                    <a href="{{ route('admin.users.index') }}" class="flex items-center justify-between px-6 py-4 text-sm transition-colors hover:bg-stone-50">
                        <div>
                            <p class="font-medium text-stone-800">Manage users</p>
                            <p class="text-stone-500">Roles, access, and account status</p>
                        </div>
                        <svg class="h-4 w-4 text-stone-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                    </a>
                    <a href="{{ route('admin.departments.index') }}" class="flex items-center justify-between px-6 py-4 text-sm transition-colors hover:bg-stone-50">
                        <div>
                            <p class="font-medium text-stone-800">Manage departments</p>
                            <p class="text-stone-500">Create departments and assign heads</p>
                        </div>
                        <svg class="h-4 w-4 text-stone-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                    </a>
                @endif
            </div>
        </section>
    </div>
@endsection
