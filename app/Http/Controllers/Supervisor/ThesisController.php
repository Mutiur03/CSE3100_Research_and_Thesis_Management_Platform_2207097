<?php

namespace App\Http\Controllers\Supervisor;

use App\Enums\ThesisStatus;
use App\Http\Controllers\Controller;
use App\Models\Thesis;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ThesisController extends Controller
{
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

        $thesis->load(['student', 'department', 'proposal', 'milestones']);

        return view('supervisor.theses.show', [
            'thesis' => $thesis,
        ]);
    }
}
