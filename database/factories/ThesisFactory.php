<?php

namespace Database\Factories;

use App\Enums\ThesisStatus;
use App\Models\Proposal;
use App\Models\Thesis;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Thesis>
 */
class ThesisFactory extends Factory
{
    protected $model = Thesis::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $proposal = Proposal::factory()->approved()->create();

        return [
            'proposal_id' => $proposal->id,
            'student_id' => $proposal->student_id,
            'department_id' => $proposal->department_id,
            'supervisor_id' => $proposal->supervisor_id,
            'title' => $proposal->title,
            'status' => ThesisStatus::Active,
            'started_at' => now(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ThesisStatus::Completed,
            'completed_at' => now(),
        ]);
    }
}
