@props([
    'thesis',
    'availableReviewers',
    'routePrefix',
])

@php
    $canAssign = auth()->user()->can('assignReviewers', $thesis);
@endphp

<div class="card">
    <div class="card-section">
        <h3 class="text-sm font-semibold text-stone-900">External reviewers</h3>
        <p class="mt-0.5 text-sm text-stone-500">Assign reviewers for committee or defense evaluation.</p>
    </div>

    @if($canAssign && $thesis->isActive() && $availableReviewers->isNotEmpty())
        <div class="border-t border-stone-100 bg-stone-50 px-6 py-5">
            <form method="POST" action="{{ route($routePrefix.'.theses.reviewers.store', $thesis) }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                @csrf
                <div class="flex-1">
                    <label for="reviewer_id" class="field-label">Assign reviewer</label>
                    <select name="reviewer_id" id="reviewer_id" required class="select-field @error('reviewer_id') input-error @enderror">
                        <option value="">Select reviewer</option>
                        @foreach($availableReviewers as $reviewer)
                            <option value="{{ $reviewer->id }}" @selected(old('reviewer_id') == $reviewer->id)>
                                {{ $reviewer->name }} ({{ $reviewer->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('reviewer_id')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="btn-primary">Assign</button>
            </form>
        </div>
    @elseif($canAssign && $thesis->isActive() && $availableReviewers->isEmpty())
        <div class="border-t border-stone-100 px-6 py-4 text-sm text-stone-500">
            No additional reviewers available. Create reviewer accounts from user management first.
        </div>
    @endif

    <div class="divide-y divide-stone-100">
        @forelse($thesis->reviews as $review)
            <div class="flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="font-medium text-stone-800">{{ $review->reviewer->name }}</p>
                    <p class="text-xs text-stone-500">{{ $review->reviewer->email }}</p>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <x-thesis-review-status-badge :status="$review->status" />
                        @if($review->decision)
                            <span class="inline-flex items-center rounded px-2 py-0.5 text-xs font-medium {{ $review->decision->color() }}">
                                {{ $review->decision->label() }}
                            </span>
                        @endif
                    </div>
                    @if($review->submitted_at)
                        <p class="mt-1 text-xs text-stone-500">Submitted {{ $review->submitted_at->diffForHumans() }}</p>
                    @endif
                </div>
                @if($canAssign && $review->status !== \App\Enums\ThesisReviewStatus::Submitted)
                    <form method="POST" action="{{ route($routePrefix.'.theses.reviewers.destroy', [$thesis, $review]) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger btn-sm">Remove</button>
                    </form>
                @endif
            </div>
        @empty
            <div class="px-6 py-8 text-center text-sm text-stone-500">
                No reviewers assigned yet.
            </div>
        @endforelse
    </div>
</div>
