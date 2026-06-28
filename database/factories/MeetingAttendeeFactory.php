<?php

namespace Database\Factories;

use App\Enums\MeetingRsvpStatus;
use App\Models\Meeting;
use App\Models\MeetingAttendee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MeetingAttendee>
 */
class MeetingAttendeeFactory extends Factory
{
    protected $model = MeetingAttendee::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'meeting_id' => Meeting::factory(),
            'user_id' => User::factory(),
            'rsvp_status' => MeetingRsvpStatus::Pending,
            'attended' => false,
        ];
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'rsvp_status' => MeetingRsvpStatus::Accepted,
        ]);
    }
}
