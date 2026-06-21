@extends('layouts.app')

@section('title', 'Proposal Reviews')

@section('content')
    <div class="page-shell">
        <header class="page-header">
            <h2 class="page-title">Proposal reviews</h2>
            <p class="page-lead">Review proposals submitted by your students.</p>
        </header>

        <div class="card mb-6">
            <div class="card-body">
                <form method="GET" action="{{ route('supervisor.proposals.index') }}" class="flex flex-col gap-4 sm:flex-row sm:items-end">
                    <div class="w-full sm:w-52">
                        <label for="status" class="field-label">Status</label>
                        <select name="status" id="status" class="select-field">
                            <option value="">Pending review</option>
                            @foreach(\App\Enums\ProposalStatus::cases() as $status)
                                <option value="{{ $status->value }}" {{ $statusFilter === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="btn-primary">Apply</button>
                        @if($statusFilter)
                            <a wire:navigate.hover href="{{ route('supervisor.proposals.index') }}" class="btn-secondary">Clear</a>
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
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Submitted</th>
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
                                    <p class="font-medium text-stone-800">{{ $proposal->student->name }}</p>
                                    <p class="text-xs text-stone-500">{{ $proposal->student->email }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <x-proposal-status-badge :status="$proposal->status" />
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-stone-500">
                                    {{ $proposal->submitted_at?->diffForHumans() ?? '—' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <a wire:navigate.hover href="{{ route('supervisor.proposals.show', $proposal) }}" class="btn-secondary btn-sm">Review</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <p class="text-sm font-medium text-stone-700">No proposals to review</p>
                                    <p class="mt-1 text-sm text-stone-500">Submitted proposals from your students will appear here.</p>
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
