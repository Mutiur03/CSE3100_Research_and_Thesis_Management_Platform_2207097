<?php

namespace Database\Factories;

use App\Enums\ProposalStatus;
use App\Models\Department;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Proposal>
 */
class ProposalFactory extends Factory
{
    protected $model = Proposal::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => User::factory()->student(),
            'department_id' => Department::factory(),
            'supervisor_id' => User::factory()->supervisor(),
            'title' => fake()->sentence(6),
            'abstract' => fake()->paragraphs(2, true),
            'objectives' => fake()->optional()->paragraph(),
            'methodology' => fake()->optional()->paragraph(),
            'status' => ProposalStatus::Draft,
        ];
    }

    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProposalStatus::Submitted,
            'submitted_at' => now(),
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProposalStatus::Approved,
            'submitted_at' => now()->subDays(3),
            'reviewed_at' => now(),
        ]);
    }

    public function revisionRequested(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProposalStatus::RevisionRequested,
            'submitted_at' => now()->subDay(),
            'reviewed_at' => now(),
            'review_notes' => fake()->sentence(),
        ]);
    }
}
