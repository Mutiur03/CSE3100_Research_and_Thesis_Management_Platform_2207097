<?php

namespace Tests\Feature\Supervisor;

use App\Enums\MilestoneStatus;
use App\Models\Milestone;
use App\Models\Proposal;
use App\Models\Thesis;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesThesisContext;
use Tests\TestCase;

class MilestoneManagementTest extends TestCase
{
    use CreatesThesisContext;
    use RefreshDatabase;

    public function test_supervisor_can_create_update_and_delete_milestones_on_their_thesis(): void
    {
        ['supervisor' => $supervisor, 'thesis' => $thesis] = $this->createSupervisedThesis();
        $dueDate = now()->addWeek()->toDateString();

        $this->actingAs($supervisor)
            ->post(route('supervisor.theses.milestones.store', $thesis), [
                'title' => 'Literature review',
                'description' => 'Survey related work',
                'due_date' => $dueDate,
            ])
            ->assertRedirect(route('supervisor.theses.show', $thesis))
            ->assertSessionHas('success');

        $milestone = Milestone::query()->where('thesis_id', $thesis->id)->firstOrFail();

        $this->assertSame('Literature review', $milestone->title);
        $this->assertSame(MilestoneStatus::Pending, $milestone->status);
        $this->assertSame(1, $milestone->sort_order);

        $updatedDueDate = now()->addWeeks(2)->toDateString();

        $this->actingAs($supervisor)
            ->put(route('supervisor.theses.milestones.update', [$thesis, $milestone]), [
                'title' => 'Updated literature review',
                'description' => 'Expanded scope',
                'due_date' => $updatedDueDate,
            ])
            ->assertRedirect(route('supervisor.theses.show', $thesis))
            ->assertSessionHas('success');

        $milestone->refresh();

        $this->assertSame('Updated literature review', $milestone->title);

        $this->actingAs($supervisor)
            ->delete(route('supervisor.theses.milestones.destroy', [$thesis, $milestone]))
            ->assertRedirect(route('supervisor.theses.show', $thesis))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('milestones', ['id' => $milestone->id]);
    }

    public function test_student_cannot_create_milestones(): void
    {
        ['student' => $student, 'thesis' => $thesis] = $this->createSupervisedThesis();

        $this->actingAs($student)
            ->post(route('supervisor.theses.milestones.store', $thesis), [
                'title' => 'Unauthorized milestone',
                'due_date' => now()->addWeek()->toDateString(),
            ])
            ->assertForbidden();
    }

    public function test_supervisor_cannot_manage_milestones_on_another_supervisors_thesis(): void
    {
        ['thesis' => $thesis] = $this->createSupervisedThesis();
        $otherSupervisor = User::factory()->supervisor()->create();
        $milestone = Milestone::factory()->create(['thesis_id' => $thesis->id]);

        $this->actingAs($otherSupervisor)
            ->post(route('supervisor.theses.milestones.store', $thesis), [
                'title' => 'Sneaky milestone',
                'due_date' => now()->addWeek()->toDateString(),
            ])
            ->assertForbidden();

        $this->actingAs($otherSupervisor)
            ->put(route('supervisor.theses.milestones.update', [$thesis, $milestone]), [
                'title' => 'Hijacked title',
                'due_date' => now()->addWeek()->toDateString(),
            ])
            ->assertForbidden();

        $this->actingAs($otherSupervisor)
            ->delete(route('supervisor.theses.milestones.destroy', [$thesis, $milestone]))
            ->assertForbidden();
    }

    public function test_supervisor_cannot_update_or_delete_completed_milestone(): void
    {
        ['supervisor' => $supervisor, 'thesis' => $thesis] = $this->createSupervisedThesis();
        $milestone = Milestone::factory()->completed()->create(['thesis_id' => $thesis->id]);

        $this->actingAs($supervisor)
            ->put(route('supervisor.theses.milestones.update', [$thesis, $milestone]), [
                'title' => 'Should not update',
                'due_date' => now()->addWeek()->toDateString(),
            ])
            ->assertForbidden();

        $this->actingAs($supervisor)
            ->delete(route('supervisor.theses.milestones.destroy', [$thesis, $milestone]))
            ->assertForbidden();
    }

    public function test_cross_thesis_milestone_id_is_rejected_for_supervisor_routes(): void
    {
        ['supervisor' => $supervisor, 'thesis' => $thesisA] = $this->createSupervisedThesis();

        $studentB = User::factory()->student()->create();
        $proposalB = Proposal::factory()->approved()->create([
            'student_id' => $studentB->id,
            'supervisor_id' => $supervisor->id,
        ]);
        $thesisB = Thesis::factory()->create([
            'proposal_id' => $proposalB->id,
            'student_id' => $studentB->id,
            'supervisor_id' => $supervisor->id,
            'department_id' => $proposalB->department_id,
            'title' => $proposalB->title,
        ]);
        $milestoneOnB = Milestone::factory()->create(['thesis_id' => $thesisB->id]);

        $this->actingAs($supervisor)
            ->put(route('supervisor.theses.milestones.update', [$thesisA, $milestoneOnB]), [
                'title' => 'Wrong thesis',
                'due_date' => now()->addWeek()->toDateString(),
            ])
            ->assertNotFound();

        $this->actingAs($supervisor)
            ->delete(route('supervisor.theses.milestones.destroy', [$thesisA, $milestoneOnB]))
            ->assertNotFound();
    }
}
