<?php

namespace App\Http\Controllers\Supervisor;

use App\Enums\MilestoneTaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Milestone\StoreMilestoneTaskRequest;
use App\Http\Requests\Milestone\UpdateMilestoneTaskRequest;
use App\Models\Milestone;
use App\Models\MilestoneTask;
use App\Models\Thesis;
use Illuminate\Http\RedirectResponse;

class MilestoneTaskController extends Controller
{
    public function store(StoreMilestoneTaskRequest $request, Thesis $thesis, Milestone $milestone): RedirectResponse
    {
        abort_unless($milestone->thesis_id === $thesis->id, 404);

        $this->authorize('create', [MilestoneTask::class, $milestone]);

        $milestone->tasks()->create([
            ...$request->validated(),
            'assigned_to' => $thesis->student_id,
            'status' => MilestoneTaskStatus::Todo,
            'created_by' => $request->user()->id,
        ]);

        $milestone->recalculateProgress();

        return redirect()->route('supervisor.theses.show', $thesis)
            ->with('success', 'Task added to milestone.');
    }

    public function update(UpdateMilestoneTaskRequest $request, Thesis $thesis, Milestone $milestone, MilestoneTask $task): RedirectResponse
    {
        abort_unless($milestone->thesis_id === $thesis->id, 404);
        abort_unless($task->milestone_id === $milestone->id, 404);

        $this->authorize('update', $task);

        $status = $request->enum('status', MilestoneTaskStatus::class);

        $task->update([
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'priority' => $request->validated('priority'),
            'due_date' => $request->validated('due_date'),
            'status' => $status,
            'completed_at' => $status === MilestoneTaskStatus::Completed ? now() : null,
        ]);

        $milestone->recalculateProgress();

        return redirect()->route('supervisor.theses.show', $thesis)
            ->with('success', 'Task updated.');
    }

    public function destroy(Thesis $thesis, Milestone $milestone, MilestoneTask $task): RedirectResponse
    {
        abort_unless($milestone->thesis_id === $thesis->id, 404);
        abort_unless($task->milestone_id === $milestone->id, 404);

        $this->authorize('delete', $task);

        $task->delete();
        $milestone->recalculateProgress();

        return redirect()->route('supervisor.theses.show', $thesis)
            ->with('success', 'Task removed.');
    }
}
