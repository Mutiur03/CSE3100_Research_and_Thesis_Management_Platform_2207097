<?php

namespace App\Http\Controllers\Supervisor;

use App\Enums\MeetingRsvpStatus;
use App\Enums\MeetingStatus;
use App\Enums\MeetingType;
use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\Thesis;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MeetingController extends Controller
{
    public function store(Request $request, Thesis $thesis): RedirectResponse
    {
        abort_unless($thesis->supervisor_id === $request->user()->id, 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'type' => ['required', Rule::enum(MeetingType::class)],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'duration_minutes' => ['nullable', 'integer', 'min:15', 'max:480'],
            'location' => ['nullable', 'string', 'max:255'],
            'meeting_link' => ['nullable', 'url', 'max:500'],
            'agenda' => ['nullable', 'string', 'max:5000'],
        ]);

        $meeting = $thesis->meetings()->create([
            ...$validated,
            'duration_minutes' => $validated['duration_minutes'] ?? 60,
            'status' => MeetingStatus::Scheduled,
            'organized_by' => $request->user()->id,
        ]);

        $this->syncDefaultAttendees($meeting, $thesis);

        return redirect()->route('supervisor.theses.show', $thesis)
            ->with('success', 'Meeting scheduled successfully.');
    }

    public function update(Request $request, Thesis $thesis, Meeting $meeting): RedirectResponse
    {
        abort_unless($meeting->thesis_id === $thesis->id, 404);
        abort_unless($thesis->supervisor_id === $request->user()->id, 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'type' => ['required', Rule::enum(MeetingType::class)],
            'scheduled_at' => ['required', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:15', 'max:480'],
            'location' => ['nullable', 'string', 'max:255'],
            'meeting_link' => ['nullable', 'url', 'max:500'],
            'agenda' => ['nullable', 'string', 'max:5000'],
            'minutes' => ['nullable', 'string', 'max:10000'],
            'status' => ['required', Rule::enum(MeetingStatus::class)],
        ]);

        $meeting->update([
            ...$validated,
            'duration_minutes' => $validated['duration_minutes'] ?? 60,
        ]);

        return redirect()->route('supervisor.theses.show', $thesis)
            ->with('success', 'Meeting updated successfully.');
    }

    public function destroy(Thesis $thesis, Meeting $meeting): RedirectResponse
    {
        abort_unless($meeting->thesis_id === $thesis->id, 404);
        abort_unless($thesis->supervisor_id === auth()->id(), 403);

        $meeting->delete();

        return redirect()->route('supervisor.theses.show', $thesis)
            ->with('success', 'Meeting deleted.');
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
}
