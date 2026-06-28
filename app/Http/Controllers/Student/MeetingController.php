<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Meeting\UpdateMeetingRsvpRequest;
use App\Models\Meeting;
use App\Models\Thesis;
use Illuminate\Http\RedirectResponse;

class MeetingController extends Controller
{
    public function updateRsvp(UpdateMeetingRsvpRequest $request, Thesis $thesis, Meeting $meeting): RedirectResponse
    {
        abort_unless($meeting->thesis_id === $thesis->id, 404);

        $this->authorize('respond', $meeting);

        $attendee = $meeting->attendees()->where('user_id', $request->user()->id)->firstOrFail();

        $attendee->update([
            'rsvp_status' => $request->validated('rsvp_status'),
        ]);

        return redirect()->route('student.theses.show', $thesis)
            ->with('success', 'RSVP updated.');
    }
}
