<?php

namespace Tests\Concerns;

use App\Models\Proposal;
use App\Models\Thesis;
use App\Models\User;

trait CreatesThesisContext
{
    /**
     * @return array{student: User, supervisor: User, proposal: Proposal, thesis: Thesis}
     */
    protected function createSupervisedThesis(): array
    {
        $student = User::factory()->student()->create();
        $supervisor = User::factory()->supervisor()->create();
        $proposal = Proposal::factory()->approved()->create([
            'student_id' => $student->id,
            'supervisor_id' => $supervisor->id,
        ]);
        $thesis = Thesis::factory()->create([
            'proposal_id' => $proposal->id,
            'student_id' => $student->id,
            'supervisor_id' => $supervisor->id,
            'department_id' => $proposal->department_id,
            'title' => $proposal->title,
        ]);

        return compact('student', 'supervisor', 'proposal', 'thesis');
    }
}
