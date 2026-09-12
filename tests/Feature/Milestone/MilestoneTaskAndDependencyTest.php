<?php

namespace Tests\Feature\Milestone;

use App\Enums\MilestoneStatus;
use App\Enums\MilestoneTaskStatus;
use App\Models\Milestone;
use App\Models\MilestoneTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesThesisContext;
use Tests\TestCase;

class MilestoneTaskAndDependencyTest extends TestCase
{
    use CreatesThesisContext;
    use RefreshDatabase;

    public function test_supervisor_can_add_tasks_and_progress_updates(): void
    {
        ['supervisor' => $supervisor, 'thesis' => $thesis] = $this->createSupervisedThesis();
        $milestone = Milestone::factory()->create(['thesis_id' => $thesis->id]);

        $this->actingAs($supervisor)
            ->post(route('supervisor.theses.milestones.tasks.store', [$thesis, $milestone]), [
                'title' => 'Write outline',
                'priority' => 'high',
            ])
            ->assertRedirect(route('supervisor.theses.show', $thesis))
            ->assertSessionHas('success');

        $task = MilestoneTask::query()->where('milestone_id', $milestone->id)->firstOrFail();
        $milestone->refresh();

        $this->assertSame(0, $milestone->progress_percentage);
        $this->assertSame($thesis->student_id, $task->assigned_to);

        $this->actingAs($supervisor)
            ->put(route('supervisor.theses.milestones.tasks.update', [$thesis, $milestone, $task]), [
                'title' => 'Write outline',
                'priority' => 'high',
                'status' => MilestoneTaskStatus::Completed->value,
            ])
            ->assertRedirect(route('supervisor.theses.show', $thesis));

        $milestone->refresh();
        $this->assertSame(100, $milestone->progress_percentage);
        $this->assertSame(MilestoneStatus::InProgress, $milestone->status);
    }

    public function test_student_cannot_complete_milestone_until_dependency_and_tasks_are_done(): void
    {
        ['student' => $student, 'thesis' => $thesis] = $this->createSupervisedThesis();
        $first = Milestone::factory()->create(['thesis_id' => $thesis->id, 'sort_order' => 1]);
        $second = Milestone::factory()->create([
            'thesis_id' => $thesis->id,
            'sort_order' => 2,
            'depends_on_id' => $first->id,
        ]);
        MilestoneTask::factory()->create([
            'milestone_id' => $second->id,
            'assigned_to' => $student->id,
            'created_by' => $thesis->supervisor_id,
        ]);

        $this->actingAs($student)
            ->post(route('student.theses.milestones.complete', [$thesis, $second]))
            ->assertForbidden();

        $this->actingAs($student)
            ->post(route('student.theses.milestones.complete', [$thesis, $first]))
            ->assertRedirect(route('student.theses.show', $thesis));

        $task = $second->tasks()->firstOrFail();

        $this->actingAs($student)
            ->patch(route('student.theses.milestones.tasks.update-status', [$thesis, $second, $task]), [
                'status' => MilestoneTaskStatus::Completed->value,
            ])
            ->assertRedirect(route('student.theses.show', $thesis));

        $this->actingAs($student)
            ->post(route('student.theses.milestones.complete', [$thesis, $second]))
            ->assertRedirect(route('student.theses.show', $thesis));
    }
}
