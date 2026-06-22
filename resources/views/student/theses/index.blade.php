@extends('layouts.app')

@section('title', 'My Theses')

@section('content')
    <div class="page-shell">
        <header class="page-header flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="page-title">My theses</h2>
                <p class="page-lead">Active research projects spawned from approved proposals.</p>
            </div>
        </header>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead class="table-head">
                        <tr>
                            <th class="px-6 py-3">Title</th>
                            <th class="px-6 py-3">Supervisor</th>
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
                                    {{ $thesis->supervisor->name }}
                                </td>
                                <td class="px-6 py-4">
                                    <x-thesis-status-badge :status="$thesis->status" />
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-stone-500">
                                    {{ $thesis->started_at->format('M j, Y') }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <a wire:navigate.hover href="{{ route('student.theses.show', $thesis) }}" class="btn-secondary btn-sm">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <p class="text-sm font-medium text-stone-700">No thesis projects yet</p>
                                    <p class="mt-1 text-sm text-stone-500">A thesis is created automatically when your supervisor approves a proposal.</p>
                                    <a wire:navigate.hover href="{{ route('student.proposals.index') }}" class="btn-secondary btn-sm mt-4 inline-flex">View proposals</a>
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
