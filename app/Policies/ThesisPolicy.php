<?php

namespace App\Policies;

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
}
