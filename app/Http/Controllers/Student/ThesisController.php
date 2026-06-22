<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Thesis;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ThesisController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Thesis::class);

        $theses = Thesis::query()
            ->where('student_id', $request->user()->id)
            ->with(['supervisor', 'department', 'proposal'])
            ->latest('started_at')
            ->paginate(10);

        return view('student.theses.index', [
            'theses' => $theses,
        ]);
    }

    public function show(Thesis $thesis): View
    {
        $this->authorize('view', $thesis);

        $thesis->load(['supervisor', 'department', 'proposal', 'milestones']);

        return view('student.theses.show', [
            'thesis' => $thesis,
        ]);
    }
}
