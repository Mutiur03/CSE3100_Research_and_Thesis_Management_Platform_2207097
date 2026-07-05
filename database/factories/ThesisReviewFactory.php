<?php

namespace Database\Factories;

use App\Enums\ThesisReviewStatus;
use App\Enums\UserRole;
use App\Models\Thesis;
use App\Models\ThesisReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ThesisReview>
 */
class ThesisReviewFactory extends Factory
{
    protected $model = ThesisReview::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'thesis_id' => Thesis::factory(),
            'reviewer_id' => User::factory()->state(['role' => UserRole::Reviewer]),
            'status' => ThesisReviewStatus::Pending,
            'assigned_by' => User::factory()->state(['role' => UserRole::Supervisor]),
            'assigned_at' => now(),
        ];
    }

    public function submitted(): static
    {
        return $this->state(fn () => [
            'status' => ThesisReviewStatus::Submitted,
            'submitted_at' => now(),
        ]);
    }
}
