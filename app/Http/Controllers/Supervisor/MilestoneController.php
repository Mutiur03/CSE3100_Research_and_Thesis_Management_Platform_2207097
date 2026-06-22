<?php

namespace App\Http\Controllers\Supervisor;

use App\Enums\MilestoneStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Milestone\StoreMilestoneRequest;
use App\Http\Requests\Milestone\UpdateMilestoneRequest;
use App\Models\Milestone;
use App\Models\Thesis;
use Illuminate\Http\RedirectResponse;

class MilestoneController extends Controller
{
    public function store(StoreMilestoneRequest $request, Thesis $thesis): RedirectResponse
    {
        $this->authorize('create', [Milestone::class, $thesis]);

        $sortOrder = $thesis->milestones()->max('sort_order') ?? 0;

        $thesis->milestones()->create([
            ...$request->validated(),
            'status' => MilestoneStatus::Pending,
            'sort_order' => $sortOrder + 1,
        ]);

        return redirect()->route('supervisor.theses.show', $thesis)
            ->with('success', 'Milestone added successfully.');
    }

    public function update(UpdateMilestoneRequest $request, Thesis $thesis, Milestone $milestone): RedirectResponse
    {
        $this->authorize('update', $milestone);

        abort_unless($milestone->thesis_id === $thesis->id, 404);

        $milestone->update($request->validated());

        return redirect()->route('supervisor.theses.show', $thesis)
            ->with('success', 'Milestone updated successfully.');
    }

    public function destroy(Thesis $thesis, Milestone $milestone): RedirectResponse
    {
        $this->authorize('delete', $milestone);

        abort_unless($milestone->thesis_id === $thesis->id, 404);

        $milestone->delete();

        return redirect()->route('supervisor.theses.show', $thesis)
            ->with('success', 'Milestone deleted.');
    }
}
