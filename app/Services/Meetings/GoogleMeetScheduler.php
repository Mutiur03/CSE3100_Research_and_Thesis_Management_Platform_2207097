<?php

namespace App\Services\Meetings;

use App\Models\Meeting;
use App\Models\Thesis;
use App\Models\User;
use App\Services\Composio\ComposioClient;
use Illuminate\Support\Facades\Log;
use Throwable;

class GoogleMeetScheduler
{
    public function __construct(
        private readonly ComposioClient $composio,
        private readonly GoogleCalendarApi $googleCalendar,
    ) {}

    public function canAutoCreate(User $supervisor): bool
    {
        return $this->composio->isConfigured()
            && $supervisor->hasGoogleCalendarConnected();
    }

    /**
     * Create a Calendar event with Meet and update the local meeting.
     * Returns true when Meet/Calendar data was applied; false when skipped or failed.
     */
    public function createForMeeting(Meeting $meeting, Thesis $thesis, User $supervisor): bool
    {
        if (! $this->canAutoCreate($supervisor)) {
            return false;
        }

        if (filled($meeting->meeting_link)) {
            return false;
        }

        $thesis->loadMissing(['student', 'supervisor']);

        try {
            $this->googleCalendar->ensurePrimaryCalendarTimezone(
                $supervisor->composio_google_connected_account_id,
            );

            $event = $this->googleCalendar->createEvent(
                $supervisor->composio_google_connected_account_id,
                $this->googleEventPayload($meeting, $thesis, createMeet: true),
                sendUpdates: true,
            );

            $eventId = $event['id'] ?? null;
            $meetLink = $event['hangoutLink'] ?? $this->extractMeetLink($event);
            $htmlLink = $event['htmlLink'] ?? null;

            if (! filled($eventId) && ! filled($meetLink)) {
                throw new \RuntimeException('Google Calendar create response did not include an event id or Meet link.');
            }

            $meeting->update([
                'google_event_id' => is_string($eventId) ? $eventId : null,
                'google_html_link' => is_string($htmlLink) ? $htmlLink : null,
                'meeting_link' => is_string($meetLink) ? $meetLink : $meeting->meeting_link,
            ]);

            return true;
        } catch (Throwable $e) {
            Log::warning('Failed to create Google Calendar / Meet event.', [
                'meeting_id' => $meeting->id,
                'supervisor_id' => $supervisor->id,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Sync an existing Calendar event after a local meeting update.
     */
    public function syncMeeting(Meeting $meeting, Thesis $thesis, User $supervisor): bool
    {
        if (! filled($meeting->google_event_id) || ! $this->canAutoCreate($supervisor)) {
            return false;
        }

        $thesis->loadMissing(['student', 'supervisor']);

        try {
            $this->googleCalendar->ensurePrimaryCalendarTimezone(
                $supervisor->composio_google_connected_account_id,
            );

            $event = $this->googleCalendar->updateEvent(
                $supervisor->composio_google_connected_account_id,
                $meeting->google_event_id,
                $this->googleEventPayload($meeting, $thesis, createMeet: blank($meeting->meeting_link)),
                sendUpdates: true,
            );

            $updates = [];
            $meetLink = $event['hangoutLink'] ?? $this->extractMeetLink($event);
            $htmlLink = $event['htmlLink'] ?? null;

            if (is_string($meetLink) && $meetLink !== '' && $meetLink !== $meeting->meeting_link) {
                $updates['meeting_link'] = $meetLink;
            }

            if (is_string($htmlLink) && $htmlLink !== '' && $htmlLink !== $meeting->google_html_link) {
                $updates['google_html_link'] = $htmlLink;
            }

            if ($updates !== []) {
                $meeting->update($updates);
            }

            return true;
        } catch (Throwable $e) {
            Log::warning('Failed to sync Google Calendar event.', [
                'meeting_id' => $meeting->id,
                'google_event_id' => $meeting->google_event_id,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Best-effort delete of the Calendar event.
     */
    public function deleteForMeeting(Meeting $meeting, User $supervisor): bool
    {
        if (! filled($meeting->google_event_id) || ! $this->canAutoCreate($supervisor)) {
            return false;
        }

        try {
            $this->googleCalendar->deleteEvent(
                $supervisor->composio_google_connected_account_id,
                $meeting->google_event_id,
                sendUpdates: true,
            );

            return true;
        } catch (Throwable $e) {
            Log::warning('Failed to delete Google Calendar event.', [
                'meeting_id' => $meeting->id,
                'google_event_id' => $meeting->google_event_id,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function googleEventPayload(Meeting $meeting, Thesis $thesis, bool $createMeet): array
    {
        $timezone = config('app.timezone', 'Asia/Dhaka');
        $start = $meeting->scheduled_at->copy()->timezone($timezone);
        $duration = max(15, (int) ($meeting->duration_minutes ?: 60));
        $end = $start->copy()->addMinutes($duration);

        $descriptionParts = array_filter([
            $meeting->description,
            $meeting->agenda ? "Agenda:\n".$meeting->agenda : null,
            'Thesis: '.$thesis->title,
            filled($meeting->meeting_link) ? 'Join Meet: '.$meeting->meeting_link : null,
        ]);

        $attendees = collect([
            $thesis->student?->email,
            $thesis->supervisor?->email,
        ])->filter()->unique()->values()->map(fn (string $email) => ['email' => $email])->all();

        $payload = [
            'summary' => $meeting->title,
            'description' => implode("\n\n", $descriptionParts) ?: null,
            'location' => $meeting->location,
            'start' => [
                'dateTime' => $start->format('Y-m-d\TH:i:sP'),
                'timeZone' => $timezone,
            ],
            'end' => [
                'dateTime' => $end->format('Y-m-d\TH:i:sP'),
                'timeZone' => $timezone,
            ],
            'attendees' => $attendees,
            'guestsCanSeeOtherGuests' => true,
        ];

        if ($createMeet) {
            $payload['conferenceData'] = [
                'createRequest' => [
                    'requestId' => $this->googleCalendar->newMeetRequestId(),
                    'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
                ],
            ];
        }

        return array_filter(
            $payload,
            fn ($value) => $value !== null && $value !== '' && $value !== [],
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function extractMeetLink(array $payload): ?string
    {
        $candidates = [
            data_get($payload, 'hangoutLink'),
            data_get($payload, 'conferenceData.entryPoints.0.uri'),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && str_contains($candidate, 'meet.google.com')) {
                return $candidate;
            }
        }

        return $this->findMeetUrlRecursive($payload);
    }

    private function findMeetUrlRecursive(mixed $payload): ?string
    {
        if (is_string($payload) && str_contains($payload, 'meet.google.com')) {
            return $payload;
        }

        if (! is_array($payload)) {
            return null;
        }

        foreach ($payload as $value) {
            $found = $this->findMeetUrlRecursive($value);
            if ($found !== null) {
                return $found;
            }
        }

        return null;
    }
}
