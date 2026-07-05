<?php

namespace App\Policies;

use App\Enums\ThesisReviewOutcome;
use App\Models\Thesis;
use App\Models\User;

class ThesisPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStudent() || $user->isSupervisor() || $user->isAdmin() || $user->isReviewer();
    }

    public function view(User $user, Thesis $thesis): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isStudent()) {
            return $thesis->student_id === $user->id;
        }

        if ($user->isSupervisor()) {
            return $thesis->supervisor_id === $user->id;
        }

        if ($user->isReviewer()) {
            return $thesis->reviews()->where('reviewer_id', $user->id)->exists();
        }

        return false;
    }

    public function assignReviewers(User $user, Thesis $thesis): bool
    {
        if (! $thesis->isActive()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $user->isSupervisor() && $thesis->supervisor_id === $user->id;
    }

    public function submitFinal(User $user, Thesis $thesis): bool
    {
        return $user->isStudent()
            && $thesis->student_id === $user->id
            && $thesis->isActive()
            && ! $thesis->isFinalSubmitted()
            && $thesis->hasFinalDocument();
    }

    public function updateStatus(User $user, Thesis $thesis): bool
    {
        return $user->isAdmin();
    }

    public function reopenReviews(User $user, Thesis $thesis): bool
    {
        return $user->isSupervisor()
            && $thesis->supervisor_id === $user->id
            && $thesis->isActive()
            && $thesis->reviewOutcome() === ThesisReviewOutcome::RevisionNeeded;
    }
}
