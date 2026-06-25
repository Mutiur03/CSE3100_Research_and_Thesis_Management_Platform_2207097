<?php

namespace App\Policies;

use App\Models\Thesis;
use App\Models\ThesisDocument;
use App\Models\ThesisDocumentVersion;
use App\Models\User;

class ThesisDocumentPolicy
{
    public function viewAny(User $user, Thesis $thesis): bool
    {
        return app(ThesisPolicy::class)->view($user, $thesis);
    }

    public function view(User $user, ThesisDocument $document): bool
    {
        return $this->viewAny($user, $document->thesis);
    }

    public function create(User $user, Thesis $thesis): bool
    {
        if (! $thesis->isActive()) {
            return false;
        }

        if ($user->isStudent()) {
            return $thesis->student_id === $user->id;
        }

        if ($user->isSupervisor()) {
            return $thesis->supervisor_id === $user->id;
        }

        return false;
    }

    public function addVersion(User $user, ThesisDocument $document): bool
    {
        return $this->create($user, $document->thesis);
    }

    public function download(User $user, ThesisDocumentVersion $version): bool
    {
        return $this->view($user, $version->document);
    }
}
