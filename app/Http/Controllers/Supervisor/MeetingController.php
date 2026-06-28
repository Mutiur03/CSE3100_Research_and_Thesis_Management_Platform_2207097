<?php

namespace App\Http\Controllers\Supervisor;

use App\Enums\MeetingRsvpStatus;
use App\Enums\MeetingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Meeting\StoreMeetingRequest;
use App\Http\Requests\Meeting\UpdateMeetingRequest;
use App\Models\Meeting;
use App\Models\Thesis;
use Illuminate\Http\RedirectResponse;

class MeetingController extends Controller
{
    public function store(StoreMeetingRequest $request, Thesis $thesis): RedirectResponse
    {
        $this->authorize('create', [Meeting::class, $thesis]);

        $meeting = $thesis->meetings()->create([
            ...$request->validated(),
            'duration_minutes' => $request->validated('duration_minutes') ?? 60,
            'status' => MeetingStatus::Scheduled,
            'organized_by' => $request->user()->id,
        ]);

        $this->syncDefaultAttendees($meeting, $thesis);

        return redirect()->route('supervisor.theses.show', $thesis)
            ->with('success', 'Meeting scheduled successfully.');
    }

    public function update(UpdateMeetingRequest $request, Thesis $thesis, Meeting $meeting): RedirectResponse
    {
        abort_unless($meeting->thesis_id === $thesis->id, 404);

        $this->authorize('update', $meeting);

        $meeting->update([
            ...$request->validated(),
            'duration_minutes' => $request->validated('duration_minutes') ?? 60,
        ]);

        return redirect()->route('supervisor.theses.show', $thesis)
            ->with('success', 'Meeting updated successfully.');
    }

    public function destroy(Thesis $thesis, Meeting $meeting): RedirectResponse
    {
        abort_unless($meeting->thesis_id === $thesis->id, 404);

        $this->authorize('delete', $meeting);

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
