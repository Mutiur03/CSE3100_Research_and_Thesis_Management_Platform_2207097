<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Services\Composio\ComposioClient;
use App\Services\Meetings\GoogleCalendarApi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class GoogleCalendarController extends Controller
{
    public function __construct(
        private readonly ComposioClient $composio,
        private readonly GoogleCalendarApi $googleCalendar,
    ) {}

    public function connect(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isSupervisor(), 403);

        if (! $this->composio->isConfigured()) {
            return redirect()->route('profile.show')
                ->with('error', 'Google Calendar integration is not configured. Ask an administrator to set COMPOSIO_API_KEY and COMPOSIO_GOOGLE_CALENDAR_AUTH_CONFIG_ID.');
        }

        try {
            $link = $this->composio->createGoogleCalendarLink(
                (string) $request->user()->id,
                route('supervisor.google-calendar.callback'),
            );
        } catch (Throwable $e) {
            Log::error('Failed to create Composio Google Calendar connect link.', [
                'user_id' => $request->user()->id,
                'message' => $e->getMessage(),
            ]);

            return redirect()->route('profile.show')
                ->with('error', 'Could not start Google Calendar connection. Please try again.');
        }

        $request->session()->put('composio_pending_google_account_id', $link['connected_account_id']);

        return redirect()->away($link['redirect_url']);
    }

    public function callback(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isSupervisor(), 403);

        $connectedAccountId = $request->session()->pull('composio_pending_google_account_id')
            ?? $request->query('connected_account_id')
            ?? $request->query('connectedAccountId');

        if (! is_string($connectedAccountId) || $connectedAccountId === '') {
            return redirect()->route('profile.show')
                ->with('error', 'Google Calendar connection did not return an account id. Please try connecting again.');
        }

        try {
            $account = $this->composio->getConnectedAccount($connectedAccountId);
            $status = strtoupper((string) ($account['status'] ?? ''));

            if ($status !== '' && $status !== 'ACTIVE') {
                throw new RuntimeException('Connected account status is '.$status.'.');
            }
        } catch (Throwable $e) {
            Log::warning('Composio Google Calendar callback verification failed.', [
                'user_id' => $request->user()->id,
                'connected_account_id' => $connectedAccountId,
                'message' => $e->getMessage(),
            ]);

            return redirect()->route('profile.show')
                ->with('error', 'Google Calendar connection could not be verified. Please try again.');
        }

        $request->user()->update([
            'composio_google_connected_account_id' => $connectedAccountId,
        ]);

        try {
            $this->googleCalendar->ensurePrimaryCalendarTimezone($connectedAccountId);
        } catch (Throwable $e) {
            Log::warning('Connected Google Calendar but failed to set Asia/Dhaka timezone.', [
                'user_id' => $request->user()->id,
                'connected_account_id' => $connectedAccountId,
                'message' => $e->getMessage(),
            ]);
        }

        return redirect()->route('profile.show')
            ->with('success', 'Google Calendar connected. New meetings can create Google Meet links automatically.');
    }

    public function disconnect(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isSupervisor(), 403);

        $user = $request->user();
        $connectedAccountId = $user->composio_google_connected_account_id;

        if (filled($connectedAccountId) && $this->composio->isConfigured()) {
            try {
                $this->composio->deleteConnectedAccount($connectedAccountId);
            } catch (Throwable $e) {
                Log::warning('Failed to delete Composio connected account during disconnect.', [
                    'user_id' => $user->id,
                    'connected_account_id' => $connectedAccountId,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        $user->update([
            'composio_google_connected_account_id' => null,
        ]);

        return redirect()->route('profile.show')
            ->with('success', 'Google Calendar disconnected.');
    }
}
