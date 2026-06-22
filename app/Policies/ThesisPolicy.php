<?php

namespace App\Policies;

use App\Models\Thesis;
use App\Models\User;

class ThesisPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStudent() || $user->isSupervisor() || $user->isAdmin();
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

        return false;
    }
}
