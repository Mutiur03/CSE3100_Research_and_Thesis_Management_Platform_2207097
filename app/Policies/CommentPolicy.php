<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Thesis;
use App\Models\User;

class CommentPolicy
{
    public function viewAny(User $user, Thesis $thesis): bool
    {
        return app(ThesisPolicy::class)->view($user, $thesis);
    }

    public function view(User $user, Comment $comment): bool
    {
        $thesis = $comment->commentable;

        if (! $thesis instanceof Thesis) {
            return false;
        }

        if (! app(ThesisPolicy::class)->view($user, $thesis)) {
            return false;
        }

        if ($comment->is_private && $user->isStudent()) {
            return false;
        }

        return true;
    }

    public function create(User $user, Thesis $thesis): bool
    {
        return app(ThesisPolicy::class)->view($user, $thesis) && $thesis->isActive();
    }

    public function delete(User $user, Comment $comment): bool
    {
        $thesis = $comment->commentable;

        if (! $thesis instanceof Thesis) {
            return false;
        }

        if ($comment->user_id === $user->id) {
            return true;
        }

        return $user->isSupervisor() && $thesis->supervisor_id === $user->id;
    }
}
