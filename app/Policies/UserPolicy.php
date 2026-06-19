<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any users (admin panel).
     */
    public function viewAny(User $authUser): bool
    {
        return $authUser->isAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $authUser, User $model): bool
    {
        return $authUser->isAdmin() || $authUser->id === $model->id;
    }

    /**
     * Determine whether the user can update the model.
     * Admins can update any user; users can update their own profile.
     */
    public function update(User $authUser, User $model): bool
    {
        return $authUser->isAdmin() || $authUser->id === $model->id;
    }

    /**
     * Determine whether the user can manage the role of the model.
     * Only admins can change roles.
     */
    public function manageRole(User $authUser, User $model): bool
    {
        return $authUser->isAdmin() && ! $model->isAdmin();
    }

    /**
     * Determine whether the user can toggle the active status.
     * Only admins can, and they cannot deactivate themselves.
     */
    public function toggleActive(User $authUser, User $model): bool
    {
        return $authUser->isAdmin() && $authUser->id !== $model->id;
    }
}
