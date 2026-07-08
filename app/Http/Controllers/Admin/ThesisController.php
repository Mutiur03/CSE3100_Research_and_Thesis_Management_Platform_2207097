<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ThesisStatus;
use App\Http\Controllers\Controller;
use App\Models\Thesis;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ThesisController extends Controller
{
    public function index(Request $request): View
    {

        $statusFilter = $request->input('status');

        $query = Thesis::query()
            ->with(['student', 'supervisor', 'department'])
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

        $thesis->load([
            'student',
            'supervisor',
            'department',
            'proposal',
        ]);

        return view('admin.theses.show', [
            'thesis' => $thesis,
        ]);
    }

    public function updateStatus(Request $request, Thesis $thesis): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(ThesisStatus::class)],
        ]);

        $status = ThesisStatus::from($validated['status']);

        $attributes = ['status' => $status];

        if ($status === ThesisStatus::Completed) {
            $attributes['completed_at'] = $thesis->completed_at ?? now();
        } elseif ($status === ThesisStatus::Active) {
            $attributes['completed_at'] = null;
        }

        $thesis->update($attributes);

        return redirect()->route('admin.theses.show', $thesis)
            ->with('success', 'Thesis status updated successfully.');
    }
}
