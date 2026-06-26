<?php

namespace App\Http\Controllers\Student;

use App\Enums\MilestoneTaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Milestone\UpdateMilestoneTaskStatusRequest;
use App\Models\Milestone;
use App\Models\MilestoneTask;
use App\Models\Thesis;
use App\Services\MilestoneProgressService;
use Illuminate\Http\RedirectResponse;

class MilestoneTaskController extends Controller
{
    public function __construct(
        private readonly MilestoneProgressService $progress,
    ) {}

    public function updateStatus(UpdateMilestoneTaskStatusRequest $request, Thesis $thesis, Milestone $milestone, MilestoneTask $task): RedirectResponse
    {
        abort_unless($milestone->thesis_id === $thesis->id, 404);
        abort_unless($task->milestone_id === $milestone->id, 404);

        $this->authorize('updateStatus', $task);

        $status = $request->enum('status', MilestoneTaskStatus::class);
        $this->progress->syncTaskStatus($task, $status);

        return redirect()->route('student.theses.show', $thesis)
            ->with('success', 'Task status updated.');
    }
}
