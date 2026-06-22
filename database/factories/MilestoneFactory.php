<?php

namespace Database\Factories;

use App\Enums\MilestoneStatus;
use App\Models\Milestone;
use App\Models\Thesis;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Milestone>
 */
class MilestoneFactory extends Factory
{
    protected $model = Milestone::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'thesis_id' => Thesis::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->sentence(),
            'due_date' => fake()->dateTimeBetween('now', '+3 months'),
            'status' => MilestoneStatus::Pending,
            'sort_order' => 0,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MilestoneStatus::Completed,
            'completed_at' => now(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => fake()->dateTimeBetween('-1 month', '-1 day'),
            'status' => MilestoneStatus::Pending,
        ]);
    }
}
