<?php

namespace App\Http\Controllers\Supervisor;

use App\Enums\ProposalStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Supervisor\ReviewProposalRequest;
use App\Models\Proposal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProposalController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Proposal::class);

        $statusFilter = $request->input('status');

        $query = Proposal::query()
            ->where('supervisor_id', $request->user()->id)
            ->with(['student', 'department'])
            ->latest('submitted_at');

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        } else {
            $query->whereIn('status', array_map(
                fn (ProposalStatus $status) => $status->value,
                ProposalStatus::reviewableCases(),
            ));
        }

        $proposals = $query->paginate(10)->withQueryString();

        return view('supervisor.proposals.index', [
            'proposals' => $proposals,
            'statusFilter' => $statusFilter,
        ]);
    }

    public function show(Proposal $proposal): View
    {
        $this->authorize('view', $proposal);

        if ($proposal->status === ProposalStatus::Submitted) {
            $proposal->update(['status' => ProposalStatus::UnderReview]);
        }

        $proposal->load(['student', 'department']);

        return view('supervisor.proposals.show', [
            'proposal' => $proposal->fresh(),
        ]);
    }

    public function review(ReviewProposalRequest $request, Proposal $proposal): RedirectResponse
    {
        $this->authorize('review', $proposal);

        $status = match ($request->input('decision')) {
            'approve' => ProposalStatus::Approved,
            'reject' => ProposalStatus::Rejected,
            'request_revision' => ProposalStatus::RevisionRequested,
        };

        $proposal->update([
            'status' => $status,
            'review_notes' => $request->input('review_notes'),
            'reviewed_at' => now(),
        ]);

        $message = match ($status) {
            ProposalStatus::Approved => 'Proposal approved successfully.',
            ProposalStatus::Rejected => 'Proposal rejected.',
            ProposalStatus::RevisionRequested => 'Revision requested. The student has been notified via the proposal record.',
        };

        return redirect()->route('supervisor.proposals.index')
            ->with('success', $message);
    }
}
