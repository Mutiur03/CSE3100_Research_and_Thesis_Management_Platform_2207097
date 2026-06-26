<?php

namespace App\Policies;

use App\Enums\MilestoneStatus;
use App\Models\Milestone;
use App\Models\MilestoneTask;
use App\Models\Thesis;
use App\Models\User;

class MilestoneTaskPolicy
{
    public function viewAny(User $user, Milestone $milestone): bool
    {
        return app(MilestonePolicy::class)->view($user, $milestone->thesis);
    }

    public function view(User $user, MilestoneTask $task): bool
    {
        return $this->viewAny($user, $task->milestone);
    }

    public function create(User $user, Milestone $milestone): bool
    {
        return $user->isSupervisor()
            && $milestone->thesis->supervisor_id === $user->id
            && $milestone->thesis->isActive()
            && in_array($milestone->status, MilestoneStatus::openCases(), true);
    }

    public function update(User $user, MilestoneTask $task): bool
    {
        return $this->create($user, $task->milestone);
    }

    public function delete(User $user, MilestoneTask $task): bool
    {
        return $this->create($user, $task->milestone);
    }

    public function updateStatus(User $user, MilestoneTask $task): bool
    {
        if (! $task->milestone->thesis->isActive()) {
            return false;
        }

        if ($user->isSupervisor() && $task->milestone->thesis->supervisor_id === $user->id) {
            return true;
        }

        return $user->isStudent()
            && $task->milestone->thesis->student_id === $user->id
            && $task->isOwnedByStudent($user);
    }
}
