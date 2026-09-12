<?php

namespace Tests\Feature\Proposal;

use App\Enums\ProposalStatus;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentProposalTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_view_proposals_list(): void
    {
        $student = User::factory()->student()->create();
        $supervisor = User::factory()->supervisor()->create();

        Proposal::factory()->create([
            'student_id' => $student->id,
            'supervisor_id' => $supervisor->id,
        ]);

        $response = $this->actingAs($student)->get(route('student.proposals.index'));

        $response->assertStatus(200);
    }

    public function test_supervisor_cannot_access_student_proposals(): void
    {
        $supervisor = User::factory()->supervisor()->create();

        $response = $this->actingAs($supervisor)->get(route('student.proposals.index'));

        $response->assertStatus(403);
    }

    public function test_student_can_create_proposal_draft(): void
    {
        $student = User::factory()->student()->create();
        $supervisor = User::factory()->supervisor()->create();

        $response = $this->actingAs($student)->post(route('student.proposals.store'), [
            'title' => 'AI in Healthcare',
            'abstract' => 'This research explores AI applications in healthcare diagnostics.',
            'objectives' => 'Improve diagnostic accuracy.',
            'methodology' => 'Literature review and case studies.',
            'supervisor_id' => $supervisor->id,
        ]);

        $proposal = Proposal::first();

        $response->assertRedirect(route('student.proposals.show', $proposal));
        $this->assertEquals(ProposalStatus::Draft, $proposal->status);
        $this->assertEquals($student->id, $proposal->student_id);
    }

    public function test_student_can_submit_proposal(): void
    {
        $student = User::factory()->student()->create();
        $supervisor = User::factory()->supervisor()->create();
        $proposal = Proposal::factory()->create([
            'student_id' => $student->id,
            'supervisor_id' => $supervisor->id,
            'status' => ProposalStatus::Draft,
        ]);

        $response = $this->actingAs($student)->post(route('student.proposals.submit', $proposal));

        $response->assertRedirect(route('student.proposals.show', $proposal));

        $proposal->refresh();
        $this->assertEquals(ProposalStatus::Submitted, $proposal->status);
        $this->assertNotNull($proposal->submitted_at);
    }

    public function test_student_cannot_edit_submitted_proposal(): void
    {
        $student = User::factory()->student()->create();
        $supervisor = User::factory()->supervisor()->create();
        $proposal = Proposal::factory()->submitted()->create([
            'student_id' => $student->id,
            'supervisor_id' => $supervisor->id,
        ]);

        $response = $this->actingAs($student)->get(route('student.proposals.edit', $proposal));

        $response->assertStatus(403);
    }

    public function test_student_can_edit_revision_requested_proposal(): void
    {
        $student = User::factory()->student()->create();
        $supervisor = User::factory()->supervisor()->create();
        $proposal = Proposal::factory()->revisionRequested()->create([
            'student_id' => $student->id,
            'supervisor_id' => $supervisor->id,
        ]);

        $response = $this->actingAs($student)->put(route('student.proposals.update', $proposal), [
            'title' => 'Revised title',
            'abstract' => 'Updated abstract with more detail on the research scope.',
            'supervisor_id' => $supervisor->id,
        ]);

        $response->assertRedirect(route('student.proposals.show', $proposal));

        $proposal->refresh();
        $this->assertEquals('Revised title', $proposal->title);
    }

    public function test_student_can_delete_draft_only(): void
    {
        $student = User::factory()->student()->create();
        $supervisor = User::factory()->supervisor()->create();
        $proposal = Proposal::factory()->submitted()->create([
            'student_id' => $student->id,
            'supervisor_id' => $supervisor->id,
        ]);

        $response = $this->actingAs($student)->delete(route('student.proposals.destroy', $proposal));

        $response->assertStatus(403);
        $this->assertDatabaseHas('proposals', ['id' => $proposal->id]);
    }
}
