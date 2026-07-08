<?php

namespace App\Http\Controllers\Student;

use App\Enums\ProposalStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProposalController extends Controller
{
    public function index(Request $request): View
    {

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

        return view('student.proposals.create', [
            'supervisors' => $this->availableSupervisors(),
            'student' => $request->user(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'abstract' => ['required', 'string', 'max:5000'],
            'objectives' => ['nullable', 'string', 'max:5000'],
            'methodology' => ['nullable', 'string', 'max:5000'],
            'supervisor_id' => ['required', 'integer', 'exists:users,id', $this->supervisorRule()],
        ]);

        $student = $request->user();

        $proposal = Proposal::create([
            ...$validated,
            'student_id' => $student->id,
            'department_id' => $student->department_id,
            'status' => ProposalStatus::Draft,
        ]);

        return redirect()->route('student.proposals.show', $proposal)
            ->with('success', 'Proposal draft saved successfully.');
    }

    public function show(Proposal $proposal): View
    {
        abort_unless($proposal->student_id === auth()->id(), 403);

        $proposal->load(['supervisor', 'department', 'thesis']);

        return view('student.proposals.show', [
            'proposal' => $proposal,
        ]);
    }

    public function edit(Proposal $proposal): View
    {
        abort_unless($proposal->student_id === auth()->id(), 403);
        abort_unless($proposal->isEditable(), 403);

        return view('student.proposals.edit', [
            'proposal' => $proposal,
            'supervisors' => $this->availableSupervisors(),
        ]);
    }

    public function update(Request $request, Proposal $proposal): RedirectResponse
    {
        abort_unless($proposal->student_id === $request->user()->id, 403);
        abort_unless($proposal->isEditable(), 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'abstract' => ['required', 'string', 'max:5000'],
            'objectives' => ['nullable', 'string', 'max:5000'],
            'methodology' => ['nullable', 'string', 'max:5000'],
            'supervisor_id' => ['required', 'integer', 'exists:users,id', $this->supervisorRule()],
        ]);

        $proposal->update($validated);

        return redirect()->route('student.proposals.show', $proposal)
            ->with('success', 'Proposal updated successfully.');
    }

    public function destroy(Proposal $proposal): RedirectResponse
    {
        abort_unless($proposal->student_id === auth()->id(), 403);
        abort_unless($proposal->status === ProposalStatus::Draft, 403);

        $proposal->delete();

        return redirect()->route('student.proposals.index')
            ->with('success', 'Proposal draft deleted.');
    }

    public function submit(Request $request, Proposal $proposal): RedirectResponse
    {
        abort_unless($proposal->student_id === $request->user()->id, 403);
        abort_unless($proposal->isSubmittable(), 403);

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
     * @return Collection<int, User>
     */
    private function availableSupervisors()
    {
        return User::query()
            ->where('role', UserRole::Supervisor)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'department_id']);
    }

    /**
     * @return \Closure(string, mixed, \Closure): void
     */
    private function supervisorRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            $supervisor = User::find($value);

            if (! $supervisor || ! $supervisor->isSupervisor() || ! $supervisor->is_active) {
                $fail('Please select an active supervisor.');
            }
        };
    }
}
