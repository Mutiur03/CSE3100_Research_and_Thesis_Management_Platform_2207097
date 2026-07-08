@extends('layouts.app')

@section('title', 'Theses')

@section('content')
    <div class="page-shell">
        <header class="page-header">
            <h2 class="page-title">Theses</h2>
            <p class="page-lead">All thesis projects across the institution.</p>
        </header>

        <div class="card mb-6">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.theses.index') }}" class="flex flex-col gap-4 sm:flex-row sm:items-end">
                    <div class="w-full sm:w-52">
                        <label for="status" class="field-label">Status</label>
                        <select name="status" id="status" class="select-field">
                            <option value="">All statuses</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status->value }}" {{ $statusFilter === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="btn-primary">Apply</button>
                        @if($statusFilter)
                            <a wire:navigate.hover href="{{ route('admin.theses.index') }}" class="btn-secondary">Clear</a>
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
                            <th class="px-6 py-3">Title</th>
                            <th class="px-6 py-3">Student</th>
                            <th class="px-6 py-3">Supervisor</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 bg-white">
                        @forelse($theses as $thesis)
                            <tr class="hover:bg-stone-50/80">
                                <td class="px-6 py-4 font-medium text-stone-800">{{ $thesis->title }}</td>
                                <td class="px-6 py-4 text-stone-600">{{ $thesis->student->name }}</td>
                                <td class="px-6 py-4 text-stone-600">{{ $thesis->supervisor->name }}</td>
                                <td class="px-6 py-4">
                                    <x-thesis-status-badge :status="$thesis->status" />
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a wire:navigate.hover href="{{ route('admin.theses.show', $thesis) }}" class="btn-secondary btn-sm">Manage</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-sm text-stone-500">No theses found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($theses->hasPages())
            <div class="mt-6">{{ $theses->links() }}</div>
        @endif
    </div>
@endsection
