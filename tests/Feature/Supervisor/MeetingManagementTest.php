<?php

namespace Tests\Feature\Supervisor;

use App\Enums\MeetingFormat;
use App\Enums\MeetingType;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesThesisContext;
use Tests\TestCase;

class MeetingManagementTest extends TestCase
{
    use CreatesThesisContext;
    use RefreshDatabase;

    public function test_supervisor_can_schedule_update_and_delete_meetings(): void
    {
        ['supervisor' => $supervisor, 'student' => $student, 'thesis' => $thesis] = $this->createSupervisedThesis();
        $scheduledAt = now()->addDays(3)->format('Y-m-d\TH:i');

        $this->actingAs($supervisor)
            ->post(route('supervisor.theses.meetings.store', $thesis), [
                'title' => 'Progress review',
                'type' => MeetingType::Supervision->value,
                'format' => MeetingFormat::InPerson->value,
                'scheduled_at' => $scheduledAt,
                'duration_minutes' => 45,
                'location' => 'Room 101',
                'agenda' => 'Discuss chapter 1 draft',
            ])
            ->assertRedirect(route('supervisor.theses.show', $thesis))
            ->assertSessionHas('success');

        $meeting = Meeting::query()->where('thesis_id', $thesis->id)->firstOrFail();

        $this->assertSame('Progress review', $meeting->title);
        $this->assertSame(MeetingType::Supervision, $meeting->type);
        $this->assertSame(MeetingFormat::InPerson, $meeting->format);
        $this->assertSame('Room 101', $meeting->location);
        $this->assertNull($meeting->meeting_link);
        $this->assertDatabaseHas('meeting_attendees', [
            'meeting_id' => $meeting->id,
            'user_id' => $student->id,
        ]);
        $this->assertDatabaseHas('meeting_attendees', [
            'meeting_id' => $meeting->id,
            'user_id' => $supervisor->id,
        ]);

        $updatedAt = now()->addWeek()->format('Y-m-d\TH:i');

        $this->actingAs($supervisor)
            ->put(route('supervisor.theses.meetings.update', [$thesis, $meeting]), [
                'title' => 'Updated progress review',
                'type' => MeetingType::Committee->value,
                'format' => MeetingFormat::InPerson->value,
                'scheduled_at' => $updatedAt,
                'duration_minutes' => 60,
                'location' => 'Room 202',
                'agenda' => 'Revised agenda',
                'minutes' => 'Student presented findings.',
            ])
            ->assertRedirect(route('supervisor.theses.show', $thesis))
            ->assertSessionHas('success');

        $meeting->refresh();

        $this->assertSame('Updated progress review', $meeting->title);
        $this->assertSame(MeetingType::Committee, $meeting->type);
        $this->assertSame('Room 202', $meeting->location);
        $this->assertSame('Student presented findings.', $meeting->minutes);

        $this->actingAs($supervisor)
            ->delete(route('supervisor.theses.meetings.destroy', [$thesis, $meeting]))
            ->assertRedirect(route('supervisor.theses.show', $thesis))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('meetings', ['id' => $meeting->id]);
    }

    public function test_online_meeting_does_not_require_location(): void
    {
        ['supervisor' => $supervisor, 'thesis' => $thesis] = $this->createSupervisedThesis();

        $this->actingAs($supervisor)
            ->post(route('supervisor.theses.meetings.store', $thesis), [
                'title' => 'Online check-in',
                'type' => MeetingType::Supervision->value,
                'format' => MeetingFormat::Online->value,
                'scheduled_at' => now()->addDays(2)->format('Y-m-d\TH:i'),
                'duration_minutes' => 30,
            ])
            ->assertRedirect(route('supervisor.theses.show', $thesis))
            ->assertSessionHas('success');

        $meeting = Meeting::query()->where('thesis_id', $thesis->id)->firstOrFail();

        $this->assertSame(MeetingFormat::Online, $meeting->format);
        $this->assertNull($meeting->location);
    }

    public function test_in_person_meeting_requires_location(): void
    {
        ['supervisor' => $supervisor, 'thesis' => $thesis] = $this->createSupervisedThesis();

        $this->actingAs($supervisor)
            ->from(route('supervisor.theses.show', $thesis))
            ->post(route('supervisor.theses.meetings.store', $thesis), [
                'title' => 'Campus meeting',
                'type' => MeetingType::Supervision->value,
                'format' => MeetingFormat::InPerson->value,
                'scheduled_at' => now()->addDays(2)->format('Y-m-d\TH:i'),
                'duration_minutes' => 30,
            ])
            ->assertRedirect(route('supervisor.theses.show', $thesis))
            ->assertSessionHasErrors('location');
    }

    public function test_meeting_link_from_request_is_ignored(): void
    {
        ['supervisor' => $supervisor, 'thesis' => $thesis] = $this->createSupervisedThesis();

        $this->actingAs($supervisor)
            ->post(route('supervisor.theses.meetings.store', $thesis), [
                'title' => 'No manual link',
                'type' => MeetingType::Supervision->value,
                'format' => MeetingFormat::Online->value,
                'scheduled_at' => now()->addDays(2)->format('Y-m-d\TH:i'),
                'meeting_link' => 'https://attacker.example/meet',
            ])
            ->assertRedirect(route('supervisor.theses.show', $thesis));

        $meeting = Meeting::query()->where('thesis_id', $thesis->id)->firstOrFail();

        $this->assertNull($meeting->meeting_link);
    }

    public function test_student_cannot_schedule_meetings(): void
    {
        ['student' => $student, 'thesis' => $thesis] = $this->createSupervisedThesis();

        $this->actingAs($student)
            ->post(route('supervisor.theses.meetings.store', $thesis), [
                'title' => 'Unauthorized meeting',
                'type' => MeetingType::Supervision->value,
                'format' => MeetingFormat::Online->value,
                'scheduled_at' => now()->addDay()->format('Y-m-d\TH:i'),
            ])
            ->assertForbidden();
    }

    public function test_supervisor_cannot_manage_meetings_on_another_supervisors_thesis(): void
    {
        ['thesis' => $thesis] = $this->createSupervisedThesis();
        $otherSupervisor = User::factory()->supervisor()->create();
        $meeting = Meeting::factory()->create([
            'thesis_id' => $thesis->id,
            'organized_by' => $thesis->supervisor_id,
        ]);

        $this->actingAs($otherSupervisor)
            ->put(route('supervisor.theses.meetings.update', [$thesis, $meeting]), [
                'title' => 'Hijacked',
                'type' => MeetingType::Supervision->value,
                'format' => MeetingFormat::Online->value,
                'scheduled_at' => now()->addDay()->format('Y-m-d\TH:i'),
            ])
            ->assertForbidden();

        $this->actingAs($otherSupervisor)
            ->delete(route('supervisor.theses.meetings.destroy', [$thesis, $meeting]))
            ->assertForbidden();
    }

    public function test_cross_thesis_meeting_id_is_rejected(): void
    {
        ['supervisor' => $supervisor, 'thesis' => $thesisA] = $this->createSupervisedThesis();
        $otherContext = $this->createSupervisedThesis();
        $thesisB = $otherContext['thesis'];
        $thesisB->update(['supervisor_id' => $supervisor->id]);

        $meetingOnB = Meeting::factory()->create([
            'thesis_id' => $thesisB->id,
            'organized_by' => $supervisor->id,
        ]);

        $this->actingAs($supervisor)
            ->put(route('supervisor.theses.meetings.update', [$thesisA, $meetingOnB]), [
                'title' => 'Wrong thesis',
                'type' => MeetingType::Supervision->value,
                'format' => MeetingFormat::Online->value,
                'scheduled_at' => now()->addDay()->format('Y-m-d\TH:i'),
            ])
            ->assertNotFound();
    }
}
