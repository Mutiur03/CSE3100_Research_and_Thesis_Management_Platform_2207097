@extends('layouts.app')

@section('title', 'Supervised Theses')

@section('content')
    <div class="page-shell">
        <header class="page-header flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="page-title">Supervised theses</h2>
                <p class="page-lead">Research projects from approved student proposals.</p>
            </div>
        </header>

        <div class="mb-6 flex flex-wrap gap-2">
            <a wire:navigate.hover href="{{ route('supervisor.theses.index') }}"
               class="rounded px-3 py-1.5 text-sm font-medium {{ !$statusFilter ? 'bg-navy-800 text-white' : 'bg-white text-stone-600 ring-1 ring-stone-200 hover:bg-stone-50' }}">
                All
            </a>
            @foreach($statuses as $status)
                <a wire:navigate.hover href="{{ route('supervisor.theses.index', ['status' => $status->value]) }}"
                   class="rounded px-3 py-1.5 text-sm font-medium {{ $statusFilter === $status->value ? 'bg-navy-800 text-white' : 'bg-white text-stone-600 ring-1 ring-stone-200 hover:bg-stone-50' }}">
                    {{ $status->label() }}
                </a>
            @endforeach
        </div>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead class="table-head">
                        <tr>
                            <th class="px-6 py-3">Title</th>
                            <th class="px-6 py-3">Student</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Started</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 bg-white">
                        @forelse($theses as $thesis)
                            <tr class="hover:bg-stone-50/80">
                                <td class="px-6 py-4">
                                    <p class="font-medium text-stone-800">{{ $thesis->title }}</p>
                                </td>
                                <td class="px-6 py-4 text-stone-600">
                                    {{ $thesis->student->name }}
                                </td>
                                <td class="px-6 py-4">
                                    <x-thesis-status-badge :status="$thesis->status" />
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-stone-500">
                                    {{ $thesis->started_at->format('M j, Y') }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <a wire:navigate.hover href="{{ route('supervisor.theses.show', $thesis) }}" class="btn-secondary btn-sm">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <p class="text-sm font-medium text-stone-700">No thesis projects yet</p>
                                    <p class="mt-1 text-sm text-stone-500">Theses appear here when you approve a student proposal.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($theses->hasPages())
                <div class="border-t border-stone-200 bg-stone-50 px-6 py-3">
                    {{ $theses->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
