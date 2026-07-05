<?php

namespace App\Http\Controllers\Supervisor;

use App\Enums\ThesisStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Thesis;
use App\Models\User;
use App\Services\ThesisReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ThesisController extends Controller
{
    public function __construct(
        private readonly ThesisReviewService $reviews,
    ) {}
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Thesis::class);

        $statusFilter = $request->input('status');

        $query = Thesis::query()
            ->where('supervisor_id', $request->user()->id)
            ->with(['student', 'department'])
            ->latest('started_at');

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        $theses = $query->paginate(10)->withQueryString();

        return view('supervisor.theses.index', [
            'theses' => $theses,
            'statusFilter' => $statusFilter,
            'statuses' => ThesisStatus::cases(),
        ]);
    }

    public function show(Thesis $thesis): View
    {
        $this->authorize('view', $thesis);

        $thesis->load([
            'student', 'department', 'proposal',
            'reviews.reviewer', 'reviews.assigner',
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

        $availableReviewers = User::query()
            ->where('role', UserRole::Reviewer)
            ->where('is_active', true)
            ->whereNotIn('id', $thesis->reviews->pluck('reviewer_id'))
            ->orderBy('name')
            ->get();

        return view('supervisor.theses.show', [
            'thesis' => $thesis,
            'availableReviewers' => $availableReviewers,
        ]);
    }

    public function reopenReviews(Thesis $thesis): RedirectResponse
    {
        $this->authorize('reopenReviews', $thesis);

        $count = $this->reviews->reopenRevisionReviews($thesis);

        if ($count === 0) {
            return redirect()->route('supervisor.theses.show', $thesis)
                ->with('error', 'No revision reviews available to reopen.');
        }

        return redirect()->route('supervisor.theses.show', $thesis)
            ->with('success', 'Reviewer assignments reopened for revision.');
    }
}
