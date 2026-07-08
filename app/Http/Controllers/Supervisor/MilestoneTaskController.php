<?php

namespace App\Http\Controllers\Supervisor;

use App\Enums\MilestoneTaskPriority;
use App\Enums\MilestoneTaskStatus;
use App\Http\Controllers\Controller;
use App\Models\Milestone;
use App\Models\MilestoneTask;
use App\Models\Thesis;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MilestoneTaskController extends Controller
{
    public function store(Request $request, Thesis $thesis, Milestone $milestone): RedirectResponse
    {
        abort_unless($milestone->thesis_id === $thesis->id, 404);
        abort_unless($thesis->supervisor_id === $request->user()->id, 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'priority' => ['required', Rule::enum(MilestoneTaskPriority::class)],
            'due_date' => ['nullable', 'date'],
        ]);

        $milestone->tasks()->create([
            ...$validated,
            'assigned_to' => $thesis->student_id,
            'status' => MilestoneTaskStatus::Todo,
            'created_by' => $request->user()->id,
        ]);

        $milestone->recalculateProgress();

        return redirect()->route('supervisor.theses.show', $thesis)
            ->with('success', 'Task added to milestone.');
    }

    public function update(Request $request, Thesis $thesis, Milestone $milestone, MilestoneTask $task): RedirectResponse
    {
        abort_unless($milestone->thesis_id === $thesis->id, 404);
        abort_unless($task->milestone_id === $milestone->id, 404);
        abort_unless($thesis->supervisor_id === $request->user()->id, 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'priority' => ['required', Rule::enum(MilestoneTaskPriority::class)],
            'status' => ['required', Rule::enum(MilestoneTaskStatus::class)],
            'due_date' => ['nullable', 'date'],
        ]);

        $status = $request->enum('status', MilestoneTaskStatus::class);

        $task->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'priority' => $validated['priority'],
            'due_date' => $validated['due_date'] ?? null,
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
        abort_unless($thesis->supervisor_id === auth()->id(), 403);

        $task->delete();
        $milestone->recalculateProgress();

        return redirect()->route('supervisor.theses.show', $thesis)
            ->with('success', 'Task removed.');
    }
}
