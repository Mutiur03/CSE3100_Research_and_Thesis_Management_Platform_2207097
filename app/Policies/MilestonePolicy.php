<?php

namespace App\Policies;

use App\Enums\MilestoneStatus;
use App\Models\Milestone;
use App\Models\Thesis;
use App\Models\User;

class MilestonePolicy
{
    public function viewAny(User $user, Thesis $thesis): bool
    {
        return app(ThesisPolicy::class)->view($user, $thesis);
    }

    public function view(User $user, Milestone $milestone): bool
    {
        return $this->viewAny($user, $milestone->thesis);
    }

    public function create(User $user, Thesis $thesis): bool
    {
        return $user->isSupervisor()
            && $thesis->supervisor_id === $user->id
            && $thesis->isActive();
    }

    public function update(User $user, Milestone $milestone): bool
    {
        return $this->create($user, $milestone->thesis)
            && in_array($milestone->status, MilestoneStatus::openCases(), true);
    }

    public function delete(User $user, Milestone $milestone): bool
    {
        return $this->create($user, $milestone->thesis)
            && in_array($milestone->status, MilestoneStatus::openCases(), true);
    }

    public function complete(User $user, Milestone $milestone): bool
    {
        return $user->isStudent()
            && $milestone->thesis->student_id === $user->id
            && $milestone->isCompletable();
    }
}
