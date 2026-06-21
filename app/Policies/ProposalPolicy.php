<?php

namespace App\Policies;

use App\Models\Proposal;
use App\Models\User;

class ProposalPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStudent() || $user->isSupervisor() || $user->isAdmin();
    }

    public function view(User $user, Proposal $proposal): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isStudent()) {
            return $proposal->student_id === $user->id;
        }

        if ($user->isSupervisor()) {
            return $proposal->supervisor_id === $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isStudent();
    }

    public function update(User $user, Proposal $proposal): bool
    {
        return $user->isStudent()
            && $proposal->student_id === $user->id
            && $proposal->isEditable();
    }

    public function delete(User $user, Proposal $proposal): bool
    {
        return $user->isStudent()
            && $proposal->student_id === $user->id
            && $proposal->status === \App\Enums\ProposalStatus::Draft;
    }

    public function submit(User $user, Proposal $proposal): bool
    {
        return $this->update($user, $proposal) && $proposal->isSubmittable();
    }

    public function review(User $user, Proposal $proposal): bool
    {
        return $user->isSupervisor()
            && $proposal->supervisor_id === $user->id
            && $proposal->isReviewable();
    }
}
