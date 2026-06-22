<?php

namespace App\Http\Controllers\Student;

use App\Enums\MilestoneStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Milestone\CompleteMilestoneRequest;
use App\Models\Milestone;
use App\Models\Thesis;
use Illuminate\Http\RedirectResponse;

class MilestoneController extends Controller
{
    public function complete(CompleteMilestoneRequest $request, Thesis $thesis, Milestone $milestone): RedirectResponse
    {
        $this->authorize('complete', $milestone);

        abort_unless($milestone->thesis_id === $thesis->id, 404);

        $milestone->update([
            'status' => MilestoneStatus::Completed,
            'completed_at' => now(),
        ]);

        return redirect()->route('student.theses.show', $thesis)
            ->with('success', 'Milestone marked as complete.');
    }
}
