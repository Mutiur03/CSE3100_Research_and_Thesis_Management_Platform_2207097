<?php

namespace App\Http\Controllers\Student;

use App\Enums\MilestoneStatus;
use App\Enums\ThesisStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Milestone\CompleteMilestoneRequest;
use App\Models\Milestone;
use App\Models\Thesis;
use Illuminate\Http\RedirectResponse;

class MilestoneController extends Controller
{
    public function complete(CompleteMilestoneRequest $request, Thesis $thesis, Milestone $milestone): RedirectResponse
    {
        abort_unless($milestone->thesis_id === $thesis->id, 404);

        $this->authorize('complete', $milestone);

        $milestone->update([
            'status' => MilestoneStatus::Completed,
            'completed_at' => now(),
            'progress_percentage' => 100,
        ]);

        if ($thesis->milestones()->exists()
            && $thesis->milestones()->where('status', '!=', MilestoneStatus::Completed)->doesntExist()) {
            $thesis->update([
                'status' => ThesisStatus::Completed,
                'completed_at' => now(),
            ]);
        }

        return redirect()->route('student.theses.show', $thesis)
            ->with('success', 'Milestone marked as complete.');
    }
}
