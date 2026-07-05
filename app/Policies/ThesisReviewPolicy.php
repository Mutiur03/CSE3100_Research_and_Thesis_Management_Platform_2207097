<?php

namespace App\Policies;

use App\Models\Thesis;
use App\Models\ThesisReview;
use App\Models\User;

class ThesisReviewPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isReviewer() || $user->isSupervisor() || $user->isAdmin();
    }

    public function view(User $user, ThesisReview $review): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isReviewer()) {
            return $review->reviewer_id === $user->id;
        }

        if ($user->isSupervisor()) {
            return $review->thesis->supervisor_id === $user->id;
        }

        return false;
    }

    public function submit(User $user, ThesisReview $review): bool
    {
        return $user->isReviewer()
            && $review->reviewer_id === $user->id
            && $review->isSubmittable();
    }

    public function assign(User $user, Thesis $thesis): bool
    {
        return app(ThesisPolicy::class)->assignReviewers($user, $thesis);
    }

    public function remove(User $user, ThesisReview $review): bool
    {
        if ($review->status === \App\Enums\ThesisReviewStatus::Submitted) {
            return false;
        }

        return app(ThesisPolicy::class)->assignReviewers($user, $review->thesis);
    }
}
