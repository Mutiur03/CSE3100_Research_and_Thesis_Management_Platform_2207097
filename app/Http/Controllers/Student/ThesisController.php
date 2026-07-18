<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Thesis;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ThesisController extends Controller
{
    public function index(Request $request): View
    {

        $theses = Thesis::query()
            ->where('student_id', $request->user()->id)
            ->with(['supervisor', 'department', 'proposal'])
            ->latest('started_at')
            ->paginate(10);

        return view('student.theses.index', [
            'theses' => $theses,
        ]);
    }

    public function show(Thesis $thesis): View
    {
        $thesis->load([
            'supervisor', 'department', 'proposal',
            'milestones.tasks.assignee',
            'milestones.dependency',
            'meetings.organizer',
            'meetings.attendees.user',
            'comments' => fn ($query) => $query->topLevel()->visibleTo(auth()->user())->with([
                'user',
                'mentions',
                'replies' => fn ($replyQuery) => $replyQuery->visibleTo(auth()->user())->with(['user', 'mentions']),
            ]),
            'documents.versions.uploader', 'documents.uploader',
        ]);

        return view('student.theses.show', [
            'thesis' => $thesis,
        ]);
    }

    public function submitFinal(Thesis $thesis): RedirectResponse
    {
        $thesis->update(['final_submitted_at' => now()]);

        return redirect()->route('student.theses.show', $thesis)
            ->with('success', 'Final thesis submitted for supervisor review.');
    }
}
