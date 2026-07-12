<?php

namespace Database\Factories;

use App\Enums\MeetingFormat;
use App\Enums\MeetingStatus;
use App\Enums\MeetingType;
use App\Models\Meeting;
use App\Models\Thesis;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Meeting>
 */
class MeetingFactory extends Factory
{
    protected $model = Meeting::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'thesis_id' => Thesis::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
            'type' => fake()->randomElement(MeetingType::cases()),
            'format' => MeetingFormat::Online,
            'scheduled_at' => fake()->dateTimeBetween('+1 day', '+2 months'),
            'duration_minutes' => fake()->randomElement([30, 45, 60, 90]),
            'location' => null,
            'meeting_link' => fake()->optional()->url(),
            'agenda' => fake()->optional()->paragraph(),
            'minutes' => null,
            'status' => MeetingStatus::Scheduled,
            'organized_by' => User::factory()->supervisor(),
        ];
    }

    public function inPerson(?string $location = null): static
    {
        return $this->state(fn (array $attributes) => [
            'format' => MeetingFormat::InPerson,
            'location' => $location ?? fake()->streetAddress(),
            'meeting_link' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MeetingStatus::Completed,
            'scheduled_at' => fake()->dateTimeBetween('-2 months', '-1 day'),
            'minutes' => fake()->paragraph(),
        ]);
    }
}
