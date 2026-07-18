<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Milestone;
use App\Models\Thesis;
use Illuminate\Http\RedirectResponse;

class MilestoneController extends Controller
{
    public function complete(Thesis $thesis, Milestone $milestone): RedirectResponse
    {
        abort_unless($milestone->thesis_id === $thesis->id, 404);
        abort_unless($milestone->isCompletable(), 403);

        $milestone->markCompleted();

        return redirect()->route('student.theses.show', $thesis)
            ->with('success', 'Milestone marked as complete.');
    }
}
