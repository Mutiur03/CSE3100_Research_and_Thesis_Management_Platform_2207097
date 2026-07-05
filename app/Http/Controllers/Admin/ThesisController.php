<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ThesisStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssignThesisReviewerRequest;
use App\Models\Thesis;
use App\Models\ThesisReview;
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
            ->with(['student', 'supervisor', 'department'])
            ->withCount('reviews')
            ->latest('started_at');

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        $theses = $query->paginate(15)->withQueryString();

        return view('admin.theses.index', [
            'theses' => $theses,
            'statusFilter' => $statusFilter,
            'statuses' => ThesisStatus::cases(),
        ]);
    }

    public function show(Thesis $thesis): View
    {
        $this->authorize('view', $thesis);

        $thesis->load([
            'student',
            'supervisor',
            'department',
            'proposal',
            'reviews.reviewer',
            'reviews.assigner',
        ]);

        $availableReviewers = User::query()
            ->where('role', \App\Enums\UserRole::Reviewer)
            ->where('is_active', true)
            ->whereNotIn('id', $thesis->reviews->pluck('reviewer_id'))
            ->orderBy('name')
            ->get();

        return view('admin.theses.show', [
            'thesis' => $thesis,
            'availableReviewers' => $availableReviewers,
        ]);
    }

    public function assignReviewer(AssignThesisReviewerRequest $request, Thesis $thesis): RedirectResponse
    {
        $reviewer = User::query()->findOrFail($request->integer('reviewer_id'));

        $this->reviews->assign($thesis, $reviewer, $request->user());

        return redirect()->route('admin.theses.show', $thesis)
            ->with('success', 'Reviewer assigned successfully.');
    }

    public function removeReviewer(Thesis $thesis, ThesisReview $thesisReview): RedirectResponse
    {
        abort_unless($thesisReview->thesis_id === $thesis->id, 404);

        $this->authorize('remove', $thesisReview);

        $this->reviews->remove($thesisReview);

        return redirect()->route('admin.theses.show', $thesis)
            ->with('success', 'Reviewer assignment removed.');
    }
}
