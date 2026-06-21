<?php

namespace App\Http\Controllers\Student;

use App\Enums\ProposalStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Proposal\StoreProposalRequest;
use App\Http\Requests\Proposal\UpdateProposalRequest;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProposalController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Proposal::class);

        $proposals = Proposal::query()
            ->where('student_id', $request->user()->id)
            ->with(['supervisor', 'department'])
            ->latest()
            ->paginate(10);

        return view('student.proposals.index', [
            'proposals' => $proposals,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Proposal::class);

        return view('student.proposals.create', [
            'supervisors' => $this->availableSupervisors(),
            'student' => $request->user(),
        ]);
    }

    public function store(StoreProposalRequest $request): RedirectResponse
    {
        $this->authorize('create', Proposal::class);

        $student = $request->user();

        $proposal = Proposal::create([
            ...$request->validated(),
            'student_id' => $student->id,
            'department_id' => $student->department_id,
            'status' => ProposalStatus::Draft,
        ]);

        return redirect()->route('student.proposals.show', $proposal)
            ->with('success', 'Proposal draft saved successfully.');
    }

    public function show(Proposal $proposal): View
    {
        $this->authorize('view', $proposal);

        $proposal->load(['supervisor', 'department']);

        return view('student.proposals.show', [
            'proposal' => $proposal,
        ]);
    }

    public function edit(Proposal $proposal): View
    {
        $this->authorize('update', $proposal);

        return view('student.proposals.edit', [
            'proposal' => $proposal,
            'supervisors' => $this->availableSupervisors(),
        ]);
    }

    public function update(UpdateProposalRequest $request, Proposal $proposal): RedirectResponse
    {
        $this->authorize('update', $proposal);

        $proposal->update($request->validated());

        return redirect()->route('student.proposals.show', $proposal)
            ->with('success', 'Proposal updated successfully.');
    }

    public function destroy(Proposal $proposal): RedirectResponse
    {
        $this->authorize('delete', $proposal);

        $proposal->delete();

        return redirect()->route('student.proposals.index')
            ->with('success', 'Proposal draft deleted.');
    }

    public function submit(Request $request, Proposal $proposal): RedirectResponse
    {
        $this->authorize('submit', $proposal);

        $proposal->update([
            'status' => ProposalStatus::Submitted,
            'submitted_at' => now(),
            'reviewed_at' => null,
            'review_notes' => null,
        ]);

        return redirect()->route('student.proposals.show', $proposal)
            ->with('success', 'Proposal submitted to your supervisor for review.');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    private function availableSupervisors()
    {
        return User::query()
            ->where('role', UserRole::Supervisor)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'department_id']);
    }
}
