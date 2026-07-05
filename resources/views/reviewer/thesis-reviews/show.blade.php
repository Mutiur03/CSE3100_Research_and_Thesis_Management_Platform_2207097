@extends('layouts.app')

@section('title', 'Review Thesis')

@section('content')
    <div class="page-shell">
        <header class="page-header flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="mb-2 flex flex-wrap items-center gap-2">
                    <x-thesis-review-status-badge :status="$review->status" />
                    <x-thesis-status-badge :status="$review->thesis->status" />
                    @if($review->decision)
                        <span class="inline-flex items-center rounded px-2 py-0.5 text-xs font-medium {{ $review->decision->color() }}">
                            {{ $review->decision->label() }}
                        </span>
                    @endif
                </div>
                <h2 class="page-title">{{ $review->thesis->title }}</h2>
                <p class="page-lead">
                    Student: {{ $review->thesis->student->name }} · Supervisor: {{ $review->thesis->supervisor->name }}
                </p>
            </div>
            <a wire:navigate.hover href="{{ route('reviewer.reviews.index') }}" class="btn-secondary">Back to list</a>
        </header>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                @if($review->thesis->proposal?->abstract)
                    <div class="card">
                        <div class="card-section">
                            <h3 class="text-sm font-semibold text-stone-900">Abstract</h3>
                        </div>
                        <div class="card-body">
                            <p class="whitespace-pre-wrap text-sm leading-relaxed text-stone-700">{{ $review->thesis->proposal->abstract }}</p>
                        </div>
                    </div>
                @endif

                @if($review->thesis->proposal?->objectives)
                    <div class="card">
                        <div class="card-section">
                            <h3 class="text-sm font-semibold text-stone-900">Objectives</h3>
                        </div>
                        <div class="card-body">
                            <p class="whitespace-pre-wrap text-sm leading-relaxed text-stone-700">{{ $review->thesis->proposal->objectives }}</p>
                        </div>
                    </div>
                @endif

                @if($review->thesis->proposal?->methodology)
                    <div class="card">
                        <div class="card-section">
                            <h3 class="text-sm font-semibold text-stone-900">Methodology</h3>
                        </div>
                        <div class="card-body">
                            <p class="whitespace-pre-wrap text-sm leading-relaxed text-stone-700">{{ $review->thesis->proposal->methodology }}</p>
                        </div>
                    </div>
                @endif

                <x-thesis-documents :thesis="$review->thesis" route-prefix="reviewer" />
            </div>

            <div class="space-y-6">
                @can('submit', $review)
                    <div class="card">
                        <div class="card-section">
                            <h3 class="text-sm font-semibold text-stone-900">Your decision</h3>
                            <p class="mt-0.5 text-sm text-stone-500">Submit your evaluation for this thesis.</p>
                        </div>
                        <form method="POST" action="{{ route('reviewer.reviews.submit', $review) }}" class="card-body space-y-4">
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

                            <button type="submit" class="btn-primary w-full">Submit review</button>
                        </form>
                    </div>
                @elseif($review->status === \App\Enums\ThesisReviewStatus::Submitted)
                    <div class="card">
                        <div class="card-section">
                            <h3 class="text-sm font-semibold text-stone-900">Submitted review</h3>
                        </div>
                        <div class="card-body space-y-3 text-sm text-stone-600">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-stone-400">Decision</p>
                                <p class="font-medium text-stone-800">{{ $review->decision?->label() }}</p>
                            </div>
                            @if($review->review_notes)
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wide text-stone-400">Feedback</p>
                                    <p class="whitespace-pre-wrap leading-relaxed">{{ $review->review_notes }}</p>
                                </div>
                            @endif
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-stone-400">Submitted</p>
                                <p>{{ $review->submitted_at?->format('M j, Y g:i A') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="card">
                    <div class="card-section">
                        <h3 class="text-sm font-semibold text-stone-900">Assignment</h3>
                    </div>
                    <div class="card-body space-y-3 text-sm text-stone-600">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-stone-400">Assigned</p>
                            <p>{{ $review->assigned_at->format('M j, Y g:i A') }}</p>
                        </div>
                        @if($review->assigner)
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-stone-400">Assigned by</p>
                                <p>{{ $review->assigner->name }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
