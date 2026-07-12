<?php

namespace App\Services\Meetings;

use App\Services\Composio\ComposioClient;
use Illuminate\Support\Str;
use RuntimeException;

class GoogleCalendarApi
{
    public function __construct(
        private readonly ComposioClient $composio,
    ) {}

    /**
     * @param  array<string, mixed>  $event
     * @return array<string, mixed>
     */
    public function createEvent(string $connectedAccountId, array $event, bool $sendUpdates = true): array
    {
        return $this->proxy(
            $connectedAccountId,
            'POST',
            '/calendars/primary/events',
            $event,
            [
                'conferenceDataVersion' => '1',
                'sendUpdates' => $sendUpdates ? 'all' : 'none',
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<string, mixed>
     */
    public function updateEvent(string $connectedAccountId, string $eventId, array $event, bool $sendUpdates = true): array
    {
        return $this->proxy(
            $connectedAccountId,
            'PATCH',
            '/calendars/primary/events/'.$eventId,
            $event,
            [
                'conferenceDataVersion' => '1',
                'sendUpdates' => $sendUpdates ? 'all' : 'none',
            ],
        );
    }

    public function deleteEvent(string $connectedAccountId, string $eventId, bool $sendUpdates = true): void
    {
        $this->proxy(
            $connectedAccountId,
            'DELETE',
            '/calendars/primary/events/'.$eventId,
            null,
            [
                'sendUpdates' => $sendUpdates ? 'all' : 'none',
            ],
        );
    }

    /**
     * Ensure the connected primary calendar uses Asia/Dhaka so event wall times match ResearchHub.
     * Google otherwise keeps the account default (e.g. America/Bogota) and clients show shifted times.
     */
    public function ensurePrimaryCalendarTimezone(string $connectedAccountId, ?string $timezone = null): void
    {
        $timezone ??= config('app.timezone', 'Asia/Dhaka');

        $calendar = $this->proxy($connectedAccountId, 'GET', '/calendars/primary', null);

        if (($calendar['timeZone'] ?? null) === $timezone) {
            return;
        }

        $calendarId = $calendar['id'] ?? null;
        if (! is_string($calendarId) || $calendarId === '') {
            throw new RuntimeException('Could not resolve Google primary calendar id to set timezone.');
        }

        $updated = $this->proxy(
            $connectedAccountId,
            'PATCH',
            '/calendars/'.rawurlencode($calendarId),
            ['timeZone' => $timezone],
        );

        if (($updated['timeZone'] ?? null) !== $timezone && isset($updated['error'])) {
            throw new RuntimeException(
                is_string(data_get($updated, 'error.message'))
                    ? data_get($updated, 'error.message')
                    : 'Failed to set Google Calendar timezone to '.$timezone.'.'
            );
        }
    }

    /**
     * @param  array<string, mixed>|null  $body
     * @param  array<string, string>  $query
     * @return array<string, mixed>
     */
    private function proxy(
        string $connectedAccountId,
        string $method,
        string $path,
        ?array $body,
        array $query = [],
    ): array {
        $method = strtoupper($method);
        $endpoint = $path;
        if ($query !== []) {
            $endpoint .= (str_contains($path, '?') ? '&' : '?').http_build_query($query);
        }

        $payload = [
            'connected_account_id' => $connectedAccountId,
            'endpoint' => $endpoint,
            'method' => $method,
        ];

        if ($body !== null && $method !== 'DELETE') {
            $payload['body'] = $body;
        }

        try {
            $response = $this->composio->proxyExecute($payload);
        } catch (RuntimeException $e) {
            // Google returns 404 when the event is already gone — treat as success for DELETE.
            if ($method === 'DELETE' && $this->isNotFoundError($e->getMessage())) {
                return [];
            }

            throw $e;
        }

        $httpStatus = (int) ($response['status'] ?? 0);
        $data = $response['data'] ?? null;

        if (isset($response['successful']) && $response['successful'] === false) {
            $error = is_string($response['error'] ?? null)
                ? $response['error']
                : 'Composio Google Calendar proxy request failed.';

            if ($method === 'DELETE' && $this->isNotFoundError($error)) {
                return [];
            }

            throw new RuntimeException($error);
        }

        // Google Calendar DELETE typically returns 204 with an empty body.
        if ($method === 'DELETE' && ($data === null || $data === '' || $data === [])) {
            return [];
        }

        if (! is_array($data)) {
            throw new RuntimeException('Composio Google Calendar proxy returned an unexpected response.');
        }

        if (isset($data['error']) || ($httpStatus >= 400 && $httpStatus !== 0)) {
            $message = is_string(data_get($data, 'error.message'))
                ? data_get($data, 'error.message')
                : 'Google Calendar API request failed with status '.$httpStatus.'.';

            if ($method === 'DELETE' && ($httpStatus === 404 || $this->isNotFoundError($message))) {
                return [];
            }

            throw new RuntimeException($message);
        }

        return $data;
    }

    private function isNotFoundError(string $message): bool
    {
        return str_contains($message, '404')
            || str_contains(strtolower($message), 'not found')
            || str_contains(strtolower($message), 'notfound');
    }

    public function newMeetRequestId(): string
    {
        return (string) Str::uuid();
    }
}
