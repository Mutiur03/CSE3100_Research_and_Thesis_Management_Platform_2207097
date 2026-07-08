<?php

namespace App\Http\Controllers\Student;

use App\Enums\MeetingRsvpStatus;
use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\Thesis;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MeetingController extends Controller
{
    public function updateRsvp(Request $request, Thesis $thesis, Meeting $meeting): RedirectResponse
    {
        $validated = $request->validate([
            'rsvp_status' => ['required', Rule::enum(MeetingRsvpStatus::class)],
        ]);

        abort_unless($meeting->thesis_id === $thesis->id, 404);
        abort_unless($thesis->student_id === $request->user()->id, 403);

        $attendee = $meeting->attendees()->where('user_id', $request->user()->id)->firstOrFail();

        $attendee->update([
            'rsvp_status' => $validated['rsvp_status'],
        ]);

        return redirect()->route('student.theses.show', $thesis)
            ->with('success', 'RSVP updated.');
    }
}
