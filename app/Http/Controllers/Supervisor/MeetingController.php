<?php

namespace App\Http\Controllers\Supervisor;

use App\Enums\MeetingFormat;
use App\Enums\MeetingRsvpStatus;
use App\Enums\MeetingStatus;
use App\Enums\MeetingType;
use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\Thesis;
use App\Services\Meetings\GoogleMeetScheduler;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MeetingController extends Controller
{
    public function __construct(
        private readonly GoogleMeetScheduler $googleMeetScheduler,
    ) {}

    public function store(Request $request, Thesis $thesis): RedirectResponse
    {
        $validated = $this->validateMeeting($request, requiringFutureSchedule: true);
        $format = MeetingFormat::from($validated['format']);

        $meeting = $thesis->meetings()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'format' => $format,
            'scheduled_at' => $validated['scheduled_at'],
            'duration_minutes' => $validated['duration_minutes'] ?? 60,
            'location' => $format->requiresLocation() ? $validated['location'] : null,
            'agenda' => $validated['agenda'] ?? null,
            'status' => MeetingStatus::Scheduled,
            'organized_by' => $request->user()->id,
        ]);

        $this->syncDefaultAttendees($meeting, $thesis);

        $flash = ['success' => 'Meeting scheduled successfully.'];

        if ($format === MeetingFormat::Online) {
            if ($this->googleMeetScheduler->createForMeeting($meeting->fresh(), $thesis, $request->user())) {
                $flash['success'] = 'Meeting scheduled on Google Calendar with a Meet link.';
            } elseif ($this->googleMeetScheduler->canAutoCreate($request->user())) {
                $flash['warning'] = 'Meeting saved, but Google Calendar / Meet could not be created. Try again or reconnect Google Calendar in your profile.';
            }
        }

        return redirect()->route('supervisor.theses.show', $thesis)->with($flash);
    }

    public function update(Request $request, Thesis $thesis, Meeting $meeting): RedirectResponse
    {
        abort_unless($meeting->thesis_id === $thesis->id, 404);

        $validated = $this->validateMeeting($request, requiringFutureSchedule: false);
        $format = MeetingFormat::from($validated['format']);

        $meeting->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'format' => $format,
            'scheduled_at' => $validated['scheduled_at'],
            'duration_minutes' => $validated['duration_minutes'] ?? 60,
            'location' => $format->requiresLocation() ? $validated['location'] : null,
            'agenda' => $validated['agenda'] ?? null,
            'minutes' => $validated['minutes'] ?? null,
        ]);

        $flash = ['success' => 'Meeting updated successfully.'];

        if (filled($meeting->google_event_id)) {
            if (! $this->googleMeetScheduler->syncMeeting($meeting->fresh(), $thesis, $request->user())) {
                $flash['warning'] = 'Meeting updated locally, but the Google Calendar event could not be synced.';
            }
        } elseif (
            $format === MeetingFormat::Online
            && blank($meeting->meeting_link)
            && $this->googleMeetScheduler->createForMeeting($meeting->fresh(), $thesis, $request->user())
        ) {
            $flash['success'] = 'Meeting updated and Google Meet link created.';
        }

        return redirect()->route('supervisor.theses.show', $thesis)->with($flash);
    }

    public function destroy(Thesis $thesis, Meeting $meeting): RedirectResponse
    {
        abort_unless($meeting->thesis_id === $thesis->id, 404);

        $flash = ['success' => 'Meeting deleted.'];
        $hadGoogleEvent = filled($meeting->google_event_id);

        if ($hadGoogleEvent) {
            if ($this->googleMeetScheduler->deleteForMeeting($meeting, auth()->user())) {
                $flash['success'] = 'Meeting deleted from the app and Google Calendar.';
            } else {
                $flash['warning'] = 'Meeting deleted locally, but the Google Calendar event may still exist.';
            }
        }

        $meeting->delete();

        return redirect()->route('supervisor.theses.show', $thesis)->with($flash);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateMeeting(Request $request, bool $requiringFutureSchedule): array
    {
        $scheduledAtRules = ['required', 'date'];
        if ($requiringFutureSchedule) {
            $scheduledAtRules[] = 'after:now';
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'type' => ['required', Rule::enum(MeetingType::class)],
            'format' => ['required', Rule::enum(MeetingFormat::class)],
            'scheduled_at' => $scheduledAtRules,
            'duration_minutes' => ['nullable', 'integer', 'min:15', 'max:480'],
            'location' => [
                Rule::requiredIf(fn () => $request->input('format') === MeetingFormat::InPerson->value),
                'nullable',
                'string',
                'max:255',
            ],
            'agenda' => ['nullable', 'string', 'max:5000'],
            'minutes' => ['nullable', 'string', 'max:10000'],
        ]);

        $validated['scheduled_at'] = $this->parseDhakaDateTime($validated['scheduled_at']);

        return $validated;
    }

    private function syncDefaultAttendees(Meeting $meeting, Thesis $thesis): void
    {
        $meeting->attendees()->createMany([
            [
                'user_id' => $thesis->student_id,
                'rsvp_status' => MeetingRsvpStatus::Pending,
            ],
            [
                'user_id' => $thesis->supervisor_id,
                'rsvp_status' => MeetingRsvpStatus::Accepted,
            ],
        ]);
    }

    /**
     * datetime-local values are naive; treat them as Asia/Dhaka wall time.
     */
    private function parseDhakaDateTime(string $value): Carbon
    {
        return Carbon::parse($value, config('app.timezone', 'Asia/Dhaka'));
    }
}
