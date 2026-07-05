<?php

namespace App\Http\Controllers\Reviewer;

use App\Enums\ThesisReviewDecision;
use App\Enums\ThesisReviewStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reviewer\SubmitThesisReviewRequest;
use App\Models\ThesisReview;
use App\Services\ThesisNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ThesisReviewController extends Controller
{
    public function __construct(
        private readonly ThesisNotificationService $notifications,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ThesisReview::class);

        $statusFilter = $request->input('status');

        $query = ThesisReview::query()
            ->where('reviewer_id', $request->user()->id)
            ->with(['thesis.student', 'thesis.supervisor', 'thesis.department'])
            ->latest('assigned_at');

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        } else {
            $query->whereIn('status', array_map(
                fn (ThesisReviewStatus $status) => $status->value,
                ThesisReviewStatus::openCases(),
            ));
        }

        $reviews = $query->paginate(10)->withQueryString();

        return view('reviewer.thesis-reviews.index', [
            'reviews' => $reviews,
            'statusFilter' => $statusFilter,
            'statuses' => ThesisReviewStatus::cases(),
        ]);
    }

    public function show(ThesisReview $thesisReview): View
    {
        $this->authorize('view', $thesisReview);

        if ($thesisReview->status === ThesisReviewStatus::Pending) {
            $thesisReview->update(['status' => ThesisReviewStatus::InProgress]);
        }

        $thesisReview->load([
            'thesis.student',
            'thesis.supervisor',
            'thesis.department',
            'thesis.proposal',
            'thesis.documents.versions.uploader',
            'thesis.documents.uploader',
            'assigner',
        ]);

        return view('reviewer.thesis-reviews.show', [
            'review' => $thesisReview->fresh(),
        ]);
    }

    public function submit(SubmitThesisReviewRequest $request, ThesisReview $thesisReview): RedirectResponse
    {
        $decision = match ($request->input('decision')) {
            'approve' => ThesisReviewDecision::Approve,
            'reject' => ThesisReviewDecision::Reject,
            'request_revision' => ThesisReviewDecision::RequestRevision,
        };

        $thesisReview->update([
            'status' => ThesisReviewStatus::Submitted,
            'decision' => $decision,
            'review_notes' => $request->input('review_notes'),
            'submitted_at' => now(),
        ]);

        $this->notifications->notifyThesisReviewSubmitted($thesisReview->fresh(['thesis.student', 'thesis.supervisor', 'reviewer']));

        return redirect()->route('reviewer.reviews.index')
            ->with('success', 'Your review has been submitted successfully.');
    }
}
