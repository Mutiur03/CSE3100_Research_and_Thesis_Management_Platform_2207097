@extends('layouts.app')

@section('title', 'Departments')

@section('content')
    <div class="page-shell">
        <header class="page-header flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="page-title">Department management</h2>
                <p class="page-lead">Create departments, assign heads, and review membership.</p>
            </div>
            <a href="{{ route('admin.departments.create') }}" class="btn-primary">New department</a>
        </header>

        <div class="card mb-6">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.departments.index') }}" class="flex flex-col gap-4 sm:flex-row sm:items-end">
                    <div class="flex-1">
                        <label for="search" class="field-label">Search</label>
                        <input
                            type="text"
                            name="search"
                            id="search"
                            value="{{ $search }}"
                            placeholder="Name, code, or faculty"
                            class="input-field"
                        >
                    </div>
                    <div class="w-full sm:w-52">
                        <label for="faculty" class="field-label">Faculty</label>
                        <select name="faculty" id="faculty" class="select-field">
                            <option value="">All faculties</option>
                            @foreach($faculties as $faculty)
                                <option value="{{ $faculty }}" {{ $facultyFilter === $faculty ? 'selected' : '' }}>{{ $faculty }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="btn-primary">Apply</button>
                        @if($search || $facultyFilter)
                            <a href="{{ route('admin.departments.index') }}" class="btn-secondary">Clear</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead class="table-head">
                        <tr>
                            <th class="px-6 py-3">Department</th>
                            <th class="px-6 py-3">Faculty</th>
                            <th class="px-6 py-3">Head</th>
                            <th class="px-6 py-3">Members</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 bg-white">
                        @forelse($departments as $department)
                            <tr class="hover:bg-stone-50/80">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <p class="font-medium text-stone-800">{{ $department->name }}</p>
                                    <p class="text-xs text-stone-500">{{ $department->code }}</p>
                                </td>
                                <td class="px-6 py-4 text-stone-600">
                                    {{ $department->faculty ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-stone-600">
                                    @if($department->head)
                                        <p class="font-medium text-stone-800">{{ $department->head->name }}</p>
                                        <p class="text-xs text-stone-500">{{ $department->head->email }}</p>
                                    @else
                                        <span class="text-stone-400">Unassigned</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-stone-600">
                                    {{ $department->users_count }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.departments.show', $department) }}" class="btn-secondary btn-sm">View</a>
                                        <a href="{{ route('admin.departments.edit', $department) }}" class="btn-secondary btn-sm">Edit</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <p class="text-sm font-medium text-stone-700">No departments found</p>
                                    <p class="mt-1 text-sm text-stone-500">Create your first department to organize users.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($departments->hasPages())
                <div class="border-t border-stone-200 bg-stone-50 px-6 py-3">
                    {{ $departments->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
