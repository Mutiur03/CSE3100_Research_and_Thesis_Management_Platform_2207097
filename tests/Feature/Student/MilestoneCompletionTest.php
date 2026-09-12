<?php

namespace Tests\Feature\Student;

use App\Enums\MilestoneStatus;
use App\Enums\ThesisStatus;
use App\Models\Milestone;
use App\Models\Proposal;
use App\Models\Thesis;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesThesisContext;
use Tests\TestCase;

class MilestoneCompletionTest extends TestCase
{
    use CreatesThesisContext;
    use RefreshDatabase;

    public function test_student_can_mark_milestone_complete(): void
    {
        ['student' => $student, 'thesis' => $thesis] = $this->createSupervisedThesis();
        $milestone = Milestone::factory()->create(['thesis_id' => $thesis->id]);

        $this->actingAs($student)
            ->post(route('student.theses.milestones.complete', [$thesis, $milestone]))
            ->assertRedirect(route('student.theses.show', $thesis))
            ->assertSessionHas('success');

        $milestone->refresh();

        $this->assertSame(MilestoneStatus::Completed, $milestone->status);
        $this->assertNotNull($milestone->completed_at);
    }

    public function test_completing_all_milestones_marks_thesis_completed(): void
    {
        ['student' => $student, 'thesis' => $thesis] = $this->createSupervisedThesis();
        $first = Milestone::factory()->create(['thesis_id' => $thesis->id, 'sort_order' => 1]);
        $second = Milestone::factory()->create(['thesis_id' => $thesis->id, 'sort_order' => 2]);

        $this->actingAs($student)
            ->post(route('student.theses.milestones.complete', [$thesis, $first]))
            ->assertRedirect(route('student.theses.show', $thesis));

        $thesis->refresh();
        $this->assertSame(ThesisStatus::Active, $thesis->status);
        $this->assertNull($thesis->completed_at);

        $this->actingAs($student)
            ->post(route('student.theses.milestones.complete', [$thesis, $second]))
            ->assertRedirect(route('student.theses.show', $thesis));

        $thesis->refresh();
        $this->assertSame(ThesisStatus::Completed, $thesis->status);
        $this->assertNotNull($thesis->completed_at);
    }

    public function test_cross_thesis_milestone_id_is_rejected_for_student_completion(): void
    {
        ['student' => $student, 'thesis' => $thesisA] = $this->createSupervisedThesis();

        $studentB = User::factory()->student()->create();
        $proposalB = Proposal::factory()->approved()->create(['student_id' => $studentB->id]);
        $thesisB = Thesis::factory()->create([
            'proposal_id' => $proposalB->id,
            'student_id' => $studentB->id,
            'supervisor_id' => $proposalB->supervisor_id,
            'department_id' => $proposalB->department_id,
            'title' => $proposalB->title,
        ]);
        $milestoneOnB = Milestone::factory()->create(['thesis_id' => $thesisB->id]);

        $this->actingAs($student)
            ->post(route('student.theses.milestones.complete', [$thesisA, $milestoneOnB]))
            ->assertNotFound();
    }
}
