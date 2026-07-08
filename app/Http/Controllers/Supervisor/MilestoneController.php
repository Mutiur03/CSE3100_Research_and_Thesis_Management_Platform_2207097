<?php

namespace App\Http\Controllers\Supervisor;

use App\Enums\MilestoneStatus;
use App\Http\Controllers\Controller;
use App\Models\Milestone;
use App\Models\Thesis;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator as ValidatorContract;

class MilestoneController extends Controller
{
    public function store(Request $request, Thesis $thesis): RedirectResponse
    {
        abort_unless($thesis->supervisor_id === $request->user()->id, 403);

        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'due_date' => ['required', 'date', 'after_or_equal:today'],
            'depends_on_id' => [
                'nullable',
                'integer',
                Rule::exists('milestones', 'id')->where(fn ($query) => $query->where('thesis_id', $thesis->id)),
            ],
        ]);

        $validator->after(function (ValidatorContract $validator) use ($request): void {
            $dependsOnId = $request->input('depends_on_id');

            if ($dependsOnId && Milestone::wouldCreateDependencyCycle(0, (int) $dependsOnId)) {
                $validator->errors()->add('depends_on_id', 'Invalid milestone dependency.');
            }
        });

        $validated = $validator->validate();

        $sortOrder = $thesis->milestones()->max('sort_order') ?? 0;

        $thesis->milestones()->create([
            ...$validated,
            'status' => MilestoneStatus::Pending,
            'sort_order' => $sortOrder + 1,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('supervisor.theses.show', $thesis)
            ->with('success', 'Milestone added successfully.');
    }

    public function update(Request $request, Thesis $thesis, Milestone $milestone): RedirectResponse
    {
        abort_unless($milestone->thesis_id === $thesis->id, 404);
        abort_unless($thesis->supervisor_id === $request->user()->id, 403);
        abort_unless(in_array($milestone->status, MilestoneStatus::openCases(), true), 403);

        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'due_date' => ['required', 'date', 'after_or_equal:today'],
            'depends_on_id' => [
                'nullable',
                'integer',
                Rule::exists('milestones', 'id')->where(fn ($query) => $query->where('thesis_id', $thesis->id)),
            ],
        ]);

        $validator->after(function (ValidatorContract $validator) use ($request, $milestone): void {
            $dependsOnId = $request->input('depends_on_id');

            if (! $dependsOnId) {
                return;
            }

            if ((int) $dependsOnId === $milestone->id) {
                $validator->errors()->add('depends_on_id', 'A milestone cannot depend on itself.');

                return;
            }

            if (Milestone::wouldCreateDependencyCycle($milestone->id, (int) $dependsOnId)) {
                $validator->errors()->add('depends_on_id', 'This dependency would create a cycle.');
            }
        });

        $validated = $validator->validate();

        $milestone->update($validated);

        return redirect()->route('supervisor.theses.show', $thesis)
            ->with('success', 'Milestone updated successfully.');
    }

    public function destroy(Thesis $thesis, Milestone $milestone): RedirectResponse
    {
        abort_unless($milestone->thesis_id === $thesis->id, 404);
        abort_unless($thesis->supervisor_id === auth()->id(), 403);
        abort_unless(in_array($milestone->status, MilestoneStatus::openCases(), true), 403);

        $milestone->delete();

        return redirect()->route('supervisor.theses.show', $thesis)
            ->with('success', 'Milestone deleted.');
    }
}
