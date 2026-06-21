@extends('layouts.app')

@section('title', 'My Proposals')

@section('content')
    <div class="page-shell">
        <header class="page-header flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="page-title">My proposals</h2>
                <p class="page-lead">Draft, submit, and track your research proposals.</p>
            </div>
            <a wire:navigate.hover href="{{ route('student.proposals.create') }}" class="btn-primary">New proposal</a>
        </header>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead class="table-head">
                        <tr>
                            <th class="px-6 py-3">Title</th>
                            <th class="px-6 py-3">Supervisor</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Updated</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 bg-white">
                        @forelse($proposals as $proposal)
                            <tr class="hover:bg-stone-50/80">
                                <td class="px-6 py-4">
                                    <p class="font-medium text-stone-800">{{ $proposal->title }}</p>
                                </td>
                                <td class="px-6 py-4 text-stone-600">
                                    {{ $proposal->supervisor->name }}
                                </td>
                                <td class="px-6 py-4">
                                    <x-proposal-status-badge :status="$proposal->status" />
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-stone-500">
                                    {{ $proposal->updated_at->diffForHumans() }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <a wire:navigate.hover href="{{ route('student.proposals.show', $proposal) }}" class="btn-secondary btn-sm">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <p class="text-sm font-medium text-stone-700">No proposals yet</p>
                                    <p class="mt-1 text-sm text-stone-500">Start a draft and submit it to your supervisor when ready.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($proposals->hasPages())
                <div class="border-t border-stone-200 bg-stone-50 px-6 py-3">
                    {{ $proposals->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
