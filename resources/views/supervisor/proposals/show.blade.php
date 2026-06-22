@extends('layouts.app')

@section('title', 'Review Proposal')

@section('content')
    <div class="page-shell">
        <header class="page-header flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="mb-2">
                    <x-proposal-status-badge :status="$proposal->status" />
                </div>
                <h2 class="page-title">{{ $proposal->title }}</h2>
                <p class="page-lead">
                    Student: {{ $proposal->student->name }} ({{ $proposal->student->email }})
                </p>
            </div>
            <a wire:navigate.hover href="{{ route('supervisor.proposals.index') }}" class="btn-secondary">Back to list</a>
        </header>

        @if($proposal->status === \App\Enums\ProposalStatus::Approved && $proposal->thesis)
            <div class="mb-6 space-y-2">
                <x-alert type="success" message="This proposal is approved. A thesis project is active for this student." />
                <a wire:navigate.hover href="{{ route('supervisor.theses.show', $proposal->thesis) }}" class="inline-flex text-sm font-medium text-emerald-800 hover:text-emerald-900">
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
            </div>

            <div class="space-y-6">
                @can('review', $proposal)
                    <div class="card">
                        <div class="card-section">
                            <h3 class="text-sm font-semibold text-stone-900">Decision</h3>
                            <p class="mt-0.5 text-sm text-stone-500">Approve, reject, or request revisions.</p>
                        </div>
                        <form method="POST" action="{{ route('supervisor.proposals.review', $proposal) }}" class="card-body space-y-4">
                            @csrf
                            <div>
                                <label for="decision" class="field-label">Decision</label>
                                <select name="decision" id="decision" required class="select-field @error('decision') input-error @enderror">
                                    <option value="">Select decision</option>
                                    <option value="approve" {{ old('decision') === 'approve' ? 'selected' : '' }}>Approve</option>
                                    <option value="request_revision" {{ old('decision') === 'request_revision' ? 'selected' : '' }}>Request revision</option>
                                    <option value="reject" {{ old('decision') === 'reject' ? 'selected' : '' }}>Reject</option>
                                </select>
                                @error('decision')
                                    <p class="field-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="review_notes" class="field-label">Feedback</label>
                                <textarea
                                    name="review_notes"
                                    id="review_notes"
                                    rows="5"
                                    class="textarea-field @error('review_notes') input-error @enderror"
                                    placeholder="Required for rejection or revision requests"
                                >{{ old('review_notes') }}</textarea>
                                @error('review_notes')
                                    <p class="field-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <button type="submit" class="btn-primary w-full">Submit decision</button>
                        </form>
                    </div>
                @endcan

                <div class="card">
                    <div class="card-section">
                        <h3 class="text-sm font-semibold text-stone-900">Timeline</h3>
                    </div>
                    <div class="card-body space-y-3 text-sm text-stone-600">
                        @if($proposal->submitted_at)
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-stone-400">Submitted</p>
                                <p>{{ $proposal->submitted_at->format('M j, Y g:i A') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
