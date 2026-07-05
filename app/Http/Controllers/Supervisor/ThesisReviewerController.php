<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignThesisReviewerRequest;
use App\Models\Thesis;
use App\Models\ThesisReview;
use App\Models\User;
use App\Services\ThesisReviewService;
use Illuminate\Http\RedirectResponse;

class ThesisReviewerController extends Controller
{
    public function __construct(
        private readonly ThesisReviewService $reviews,
    ) {}

    public function store(AssignThesisReviewerRequest $request, Thesis $thesis): RedirectResponse
    {
        $this->authorize('assignReviewers', $thesis);

        $reviewer = User::query()->findOrFail($request->integer('reviewer_id'));

        $this->reviews->assign($thesis, $reviewer, $request->user());

        return redirect()->route('supervisor.theses.show', $thesis)
            ->with('success', 'Reviewer assigned successfully.');
    }

    public function destroy(Thesis $thesis, ThesisReview $thesisReview): RedirectResponse
    {
        abort_unless($thesisReview->thesis_id === $thesis->id, 404);

        $this->authorize('remove', $thesisReview);

        $this->reviews->remove($thesisReview);

        return redirect()->route('supervisor.theses.show', $thesis)
            ->with('success', 'Reviewer assignment removed.');
    }
}
