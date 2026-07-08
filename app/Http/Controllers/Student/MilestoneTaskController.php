<?php

namespace App\Http\Controllers\Student;

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
    public function updateStatus(Request $request, Thesis $thesis, Milestone $milestone, MilestoneTask $task): RedirectResponse
    {
        $request->validate([
            'status' => ['required', Rule::enum(MilestoneTaskStatus::class)],
        ]);

        abort_unless($milestone->thesis_id === $thesis->id, 404);
        abort_unless($task->milestone_id === $milestone->id, 404);
        abort_unless($thesis->student_id === $request->user()->id, 403);

        $status = $request->enum('status', MilestoneTaskStatus::class);
        $task->syncStatus($status);

        return redirect()->route('student.theses.show', $thesis)
            ->with('success', 'Task status updated.');
    }
}
