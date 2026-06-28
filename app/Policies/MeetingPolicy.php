<?php

namespace App\Policies;

use App\Models\Meeting;
use App\Models\Thesis;
use App\Models\User;

class MeetingPolicy
{
    public function viewAny(User $user, Thesis $thesis): bool
    {
        return app(ThesisPolicy::class)->view($user, $thesis);
    }

    public function view(User $user, Meeting $meeting): bool
    {
        return app(ThesisPolicy::class)->view($user, $meeting->thesis);
    }

    public function create(User $user, Thesis $thesis): bool
    {
        return $user->isSupervisor() && $thesis->supervisor_id === $user->id && $thesis->isActive();
    }

    public function update(User $user, Meeting $meeting): bool
    {
        return $user->isSupervisor()
            && $meeting->thesis->supervisor_id === $user->id
            && $meeting->thesis->isActive();
    }

    public function delete(User $user, Meeting $meeting): bool
    {
        return $this->update($user, $meeting);
    }

    public function respond(User $user, Meeting $meeting): bool
    {
        if (! app(ThesisPolicy::class)->view($user, $meeting->thesis)) {
            return false;
        }

        return $meeting->attendees()->where('user_id', $user->id)->exists();
    }
}
