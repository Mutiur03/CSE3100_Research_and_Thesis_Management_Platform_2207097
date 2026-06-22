@extends('layouts.app')

@section('title', $proposal->title)

@section('content')
    <div class="page-shell">
        <header class="page-header flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="mb-2">
                    <x-proposal-status-badge :status="$proposal->status" />
                </div>
                <h2 class="page-title">{{ $proposal->title }}</h2>
                <p class="page-lead">
                    Supervisor: {{ $proposal->supervisor->name }}
                    @if($proposal->department)
                        · {{ $proposal->department->code }}
                    @endif
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                @can('update', $proposal)
                    <a wire:navigate.hover href="{{ route('student.proposals.edit', $proposal) }}" class="btn-secondary">Edit</a>
                @endcan
                @can('submit', $proposal)
                    <form method="POST" action="{{ route('student.proposals.submit', $proposal) }}" onsubmit="return confirm('Submit this proposal to your supervisor for review?')">
                        @csrf
                        <button type="submit" class="btn-primary">Submit for review</button>
                    </form>
                @endcan
                @can('delete', $proposal)
                    <form method="POST" action="{{ route('student.proposals.destroy', $proposal) }}" onsubmit="return confirm('Delete this draft permanently?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-secondary text-red-700">Delete draft</button>
                    </form>
                @endcan
            </div>
        </header>

        @if($proposal->review_notes && $proposal->status === \App\Enums\ProposalStatus::RevisionRequested)
            <div class="mb-6">
                <x-alert type="warning" message="Your supervisor requested revisions. Review the feedback below, update your proposal, then resubmit." />
            </div>
        @endif

        @if($proposal->status === \App\Enums\ProposalStatus::Approved && $proposal->thesis)
            <div class="mb-6 space-y-2">
                <x-alert type="success" message="Your proposal was approved. A thesis project has been created for you." />
                <a wire:navigate.hover href="{{ route('student.theses.show', $proposal->thesis) }}" class="inline-flex text-sm font-medium text-emerald-800 hover:text-emerald-900">
                    View thesis project →
                </a>
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <div class="card">
                    <div class="card-section">
                        <h3 class="text-sm font-semibold text-stone-900">Abstract</h3>
                    </div>
                    <div class="card-body">
                        <p class="whitespace-pre-wrap text-sm leading-relaxed text-stone-700">{{ $proposal->abstract }}</p>
                    </div>
                </div>

                @if($proposal->objectives)
                    <div class="card">
                        <div class="card-section">
                            <h3 class="text-sm font-semibold text-stone-900">Objectives</h3>
                        </div>
                        <div class="card-body">
                            <p class="whitespace-pre-wrap text-sm leading-relaxed text-stone-700">{{ $proposal->objectives }}</p>
                        </div>
                    </div>
                @endif

                @if($proposal->methodology)
                    <div class="card">
                        <div class="card-section">
                            <h3 class="text-sm font-semibold text-stone-900">Methodology</h3>
                        </div>
                        <div class="card-body">
                            <p class="whitespace-pre-wrap text-sm leading-relaxed text-stone-700">{{ $proposal->methodology }}</p>
                        </div>
                    </div>
                @endif

                @if($proposal->review_notes)
                    <div class="card">
                        <div class="card-section">
                            <h3 class="text-sm font-semibold text-stone-900">Supervisor feedback</h3>
                        </div>
                        <div class="card-body">
                            <p class="whitespace-pre-wrap text-sm leading-relaxed text-stone-700">{{ $proposal->review_notes }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="space-y-6">
                <div class="card">
                    <div class="card-section">
                        <h3 class="text-sm font-semibold text-stone-900">Timeline</h3>
                    </div>
                    <div class="card-body space-y-3 text-sm text-stone-600">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-stone-400">Created</p>
                            <p>{{ $proposal->created_at->format('M j, Y g:i A') }}</p>
                        </div>
                        @if($proposal->submitted_at)
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-stone-400">Submitted</p>
                                <p>{{ $proposal->submitted_at->format('M j, Y g:i A') }}</p>
                            </div>
                        @endif
                        @if($proposal->reviewed_at)
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-stone-400">Last reviewed</p>
                                <p>{{ $proposal->reviewed_at->format('M j, Y g:i A') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
