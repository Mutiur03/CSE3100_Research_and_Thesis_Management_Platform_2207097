<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\SubmitFinalThesisRequest;
use App\Models\Thesis;
use App\Services\ThesisNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ThesisController extends Controller
{
    public function __construct(
        private readonly ThesisNotificationService $notifications,
    ) {}
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Thesis::class);

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
        $this->authorize('view', $thesis);

        $thesis->load([
            'supervisor', 'department', 'proposal',
            'reviews.reviewer',
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

    public function submitFinal(SubmitFinalThesisRequest $request, Thesis $thesis): RedirectResponse
    {
        $thesis->update(['final_submitted_at' => now()]);

        $this->notifications->notifyFinalThesisSubmitted($thesis->fresh(), $request->user());

        return redirect()->route('student.theses.show', $thesis)
            ->with('success', 'Final thesis submitted for supervisor review.');
    }
}
