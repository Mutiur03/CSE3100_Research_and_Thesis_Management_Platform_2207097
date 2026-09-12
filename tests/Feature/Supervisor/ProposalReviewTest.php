<?php

namespace Tests\Feature\Supervisor;

use App\Enums\ProposalStatus;
use App\Enums\ThesisStatus;
use App\Models\Proposal;
use App\Models\Thesis;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesThesisContext;
use Tests\TestCase;

class ProposalReviewTest extends TestCase
{
    use CreatesThesisContext;
    use RefreshDatabase;

    public function test_supervisor_approving_proposal_creates_thesis(): void
    {
        $student = User::factory()->student()->create();
        $supervisor = User::factory()->supervisor()->create();
        $proposal = Proposal::factory()->submitted()->create([
            'student_id' => $student->id,
            'supervisor_id' => $supervisor->id,
        ]);

        $this->actingAs($supervisor)
            ->post(route('supervisor.proposals.review', $proposal), [
                'decision' => 'approve',
            ])
            ->assertRedirect(route('supervisor.proposals.index'))
            ->assertSessionHas('success');

        $proposal->refresh();

        $this->assertSame(ProposalStatus::Approved, $proposal->status);
        $this->assertNotNull($proposal->reviewed_at);

        $this->assertDatabaseHas('theses', [
            'proposal_id' => $proposal->id,
            'student_id' => $student->id,
            'supervisor_id' => $supervisor->id,
            'status' => ThesisStatus::Active->value,
        ]);

        $this->assertSame(1, Thesis::query()->where('proposal_id', $proposal->id)->count());
    }
}
