@props(['thesis'])

@php
    $outcome = $thesis->reviewOutcome();
@endphp

@if($outcome)
    <div class="card">
        <div class="card-section">
            <h3 class="text-sm font-semibold text-stone-900">Review progress</h3>
            <p class="mt-0.5 text-sm text-stone-500">External reviewer decisions for this thesis.</p>
        </div>
        <div class="card-body space-y-4">
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center rounded px-2.5 py-1 text-xs font-medium {{ $outcome->color() }}">
                    {{ $outcome->label() }}
                </span>
                <span class="text-xs text-stone-500">
                    {{ $thesis->reviews->where('status', \App\Enums\ThesisReviewStatus::Submitted)->count() }}
                    of
                    {{ $thesis->reviews->count() }}
                    reviews submitted
                </span>
            </div>

            <div class="divide-y divide-stone-100 rounded border border-stone-200">
                @foreach($thesis->reviews as $review)
                    <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 text-sm">
                        <div>
                            <p class="font-medium text-stone-800">{{ $review->reviewer->name }}</p>
                            @if($review->review_notes && $review->status === \App\Enums\ThesisReviewStatus::Submitted)
                                <p class="mt-1 text-xs leading-relaxed text-stone-500">{{ Str::limit($review->review_notes, 120) }}</p>
                            @endif
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <x-thesis-review-status-badge :status="$review->status" />
                            @if($review->decision)
                                <span class="inline-flex items-center rounded px-2 py-0.5 text-xs font-medium {{ $review->decision->color() }}">
                                    {{ $review->decision->label() }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            @if($outcome === \App\Enums\ThesisReviewOutcome::Approved && $thesis->status === \App\Enums\ThesisStatus::Completed)
                <p class="text-sm text-emerald-700">All reviewers approved this thesis. Project marked completed.</p>
            @endif

            @if($outcome === \App\Enums\ThesisReviewOutcome::RevisionNeeded && auth()->user()->isSupervisor() && auth()->user()->can('reopenReviews', $thesis))
                <form method="POST" action="{{ route('supervisor.theses.reviews.reopen', $thesis) }}">
                    @csrf
                    <button type="submit" class="btn-secondary btn-sm">Reopen revision reviews</button>
                </form>
            @endif
        </div>
    </div>
@endif
