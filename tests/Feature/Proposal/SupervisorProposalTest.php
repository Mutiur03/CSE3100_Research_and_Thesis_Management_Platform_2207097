<?php

namespace Tests\Feature\Proposal;

use App\Enums\ProposalStatus;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupervisorProposalTest extends TestCase
{
    use RefreshDatabase;

    public function test_supervisor_can_view_assigned_submitted_proposals(): void
    {
        $supervisor = User::factory()->supervisor()->create();
        $student = User::factory()->student()->create();

        Proposal::factory()->submitted()->create([
            'student_id' => $student->id,
            'supervisor_id' => $supervisor->id,
            'title' => 'Machine Learning for Crop Yield',
        ]);

        $response = $this->actingAs($supervisor)->get(route('supervisor.proposals.index'));

        $response->assertStatus(200);
        $response->assertSee('Machine Learning for Crop Yield');
    }

    public function test_supervisor_cannot_view_other_supervisors_proposals(): void
    {
        $supervisor = User::factory()->supervisor()->create();
        $otherSupervisor = User::factory()->supervisor()->create();
        $student = User::factory()->student()->create();

        $proposal = Proposal::factory()->submitted()->create([
            'student_id' => $student->id,
            'supervisor_id' => $otherSupervisor->id,
        ]);

        $response = $this->actingAs($supervisor)->get(route('supervisor.proposals.show', $proposal));

        $response->assertStatus(403);
    }

    public function test_supervisor_can_approve_proposal(): void
    {
        $supervisor = User::factory()->supervisor()->create();
        $student = User::factory()->student()->create();
        $proposal = Proposal::factory()->submitted()->create([
            'student_id' => $student->id,
            'supervisor_id' => $supervisor->id,
        ]);

        $response = $this->actingAs($supervisor)->post(route('supervisor.proposals.review', $proposal), [
            'decision' => 'approve',
        ]);

        $response->assertRedirect(route('supervisor.proposals.index'));

        $proposal->refresh();
        $this->assertEquals(ProposalStatus::Approved, $proposal->status);
        $this->assertNotNull($proposal->reviewed_at);
    }

    public function test_supervisor_can_request_revision_with_notes(): void
    {
        $supervisor = User::factory()->supervisor()->create();
        $student = User::factory()->student()->create();
        $proposal = Proposal::factory()->submitted()->create([
            'student_id' => $student->id,
            'supervisor_id' => $supervisor->id,
        ]);

        $response = $this->actingAs($supervisor)->post(route('supervisor.proposals.review', $proposal), [
            'decision' => 'request_revision',
            'review_notes' => 'Please expand the methodology section.',
        ]);

        $proposal->refresh();
        $this->assertEquals(ProposalStatus::RevisionRequested, $proposal->status);
        $this->assertEquals('Please expand the methodology section.', $proposal->review_notes);
    }

    public function test_revision_request_requires_notes(): void
    {
        $supervisor = User::factory()->supervisor()->create();
        $student = User::factory()->student()->create();
        $proposal = Proposal::factory()->submitted()->create([
            'student_id' => $student->id,
            'supervisor_id' => $supervisor->id,
        ]);

        $response = $this->actingAs($supervisor)->post(route('supervisor.proposals.review', $proposal), [
            'decision' => 'request_revision',
        ]);

        $response->assertSessionHasErrors('review_notes');
    }

    public function test_student_cannot_access_supervisor_review_routes(): void
    {
        $student = User::factory()->student()->create();

        $response = $this->actingAs($student)->get(route('supervisor.proposals.index'));

        $response->assertStatus(403);
    }
}
