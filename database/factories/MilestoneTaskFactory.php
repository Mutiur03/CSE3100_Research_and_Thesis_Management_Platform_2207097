<?php

namespace Database\Factories;

use App\Enums\MilestoneTaskPriority;
use App\Enums\MilestoneTaskStatus;
use App\Models\Milestone;
use App\Models\MilestoneTask;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MilestoneTask>
 */
class MilestoneTaskFactory extends Factory
{
    protected $model = MilestoneTask::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'milestone_id' => Milestone::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
            'assigned_to' => User::factory()->student(),
            'status' => MilestoneTaskStatus::Todo,
            'priority' => MilestoneTaskPriority::Medium,
            'due_date' => fake()->optional()->dateTimeBetween('now', '+1 month'),
            'created_by' => User::factory()->supervisor(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MilestoneTaskStatus::Completed,
            'completed_at' => now(),
        ]);
    }
}
