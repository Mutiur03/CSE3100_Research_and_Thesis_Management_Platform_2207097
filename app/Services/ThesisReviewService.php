<?php

namespace App\Services;

use App\Enums\ThesisReviewDecision;
use App\Enums\ThesisReviewOutcome;
use App\Enums\ThesisReviewStatus;
use App\Enums\ThesisStatus;
use App\Enums\UserRole;
use App\Models\Thesis;
use App\Models\ThesisReview;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ThesisReviewService
{
    public function __construct(
        private readonly ThesisNotificationService $notifications,
    ) {}

    public function assign(Thesis $thesis, User $reviewer, User $assigner): ThesisReview
    {
        if ($reviewer->role !== UserRole::Reviewer) {
            throw ValidationException::withMessages([
                'reviewer_id' => 'The selected user must have the reviewer role.',
            ]);
        }

        if (! $reviewer->is_active) {
            throw ValidationException::withMessages([
                'reviewer_id' => 'The selected reviewer account is inactive.',
            ]);
        }

        $existing = ThesisReview::query()
            ->where('thesis_id', $thesis->id)
            ->where('reviewer_id', $reviewer->id)
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'reviewer_id' => 'This reviewer is already assigned to the thesis.',
            ]);
        }

        $review = ThesisReview::create([
            'thesis_id' => $thesis->id,
            'reviewer_id' => $reviewer->id,
            'status' => ThesisReviewStatus::Pending,
            'assigned_by' => $assigner->id,
            'assigned_at' => now(),
        ]);

        $this->notifications->notifyThesisReviewAssigned($review->fresh(['thesis.student', 'thesis.supervisor', 'reviewer']));

        return $review;
    }

    public function remove(ThesisReview $review): void
    {
        $review->delete();
    }

    public function syncThesisAfterReviewSubmission(Thesis $thesis): void
    {
        $thesis->load('reviews');

        if (! $thesis->isActive()) {
            return;
        }

        if ($thesis->reviewOutcome() === ThesisReviewOutcome::Approved) {
            $thesis->update([
                'status' => ThesisStatus::Completed,
                'completed_at' => now(),
            ]);
        }
    }

    public function reopenRevisionReviews(Thesis $thesis): int
    {
        if ($thesis->reviewOutcome() !== ThesisReviewOutcome::RevisionNeeded) {
            return 0;
        }

        return $thesis->reviews()
            ->where('decision', ThesisReviewDecision::RequestRevision)
            ->update([
                'status' => ThesisReviewStatus::Pending,
                'decision' => null,
                'review_notes' => null,
                'submitted_at' => null,
            ]);
    }
}
