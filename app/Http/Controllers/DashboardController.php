<?php

namespace App\Http\Controllers;

use App\Enums\MilestoneStatus;
use App\Enums\ProposalStatus;
use App\Enums\ThesisStatus;
use App\Models\Department;
use App\Models\Milestone;
use App\Models\Proposal;
use App\Models\Thesis;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the role-based dashboard.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $stats = [];

        if ($user->isStudent()) {
            $stats = [
                'my_proposals' => $user->proposalsAsStudent()->count(),
                'pending_review' => $user->proposalsAsStudent()
                    ->whereIn('status', [ProposalStatus::Submitted, ProposalStatus::UnderReview])
                    ->count(),
                'approved_proposals' => $user->proposalsAsStudent()
                    ->where('status', ProposalStatus::Approved)
                    ->count(),
                'active_theses' => $user->thesesAsStudent()
                    ->whereIn('status', ThesisStatus::activeCases())
                    ->count(),
                'milestones_due' => Milestone::query()
                    ->whereIn('status', MilestoneStatus::openCases())
                    ->whereDate('due_date', '<=', now()->addDays(7))
                    ->whereHas('thesis', fn ($query) => $query
                        ->where('student_id', $user->id)
                        ->whereIn('status', ThesisStatus::activeCases()))
                    ->count(),
            ];
        }

        if ($user->isSupervisor()) {
            $stats = [
                'pending_reviews' => $user->proposalsAsSupervisor()
                    ->whereIn('status', ProposalStatus::reviewableCases())
                    ->count(),
                'supervised_proposals' => $user->proposalsAsSupervisor()->count(),
                'approved_proposals' => $user->proposalsAsSupervisor()
                    ->where('status', ProposalStatus::Approved)
                    ->count(),
                'active_projects' => $user->thesesAsSupervisor()
                    ->whereIn('status', ThesisStatus::activeCases())
                    ->count(),
                'overdue_milestones' => Milestone::query()
                    ->whereIn('status', MilestoneStatus::openCases())
                    ->whereDate('due_date', '<', now()->startOfDay())
                    ->whereHas('thesis', fn ($query) => $query
                        ->where('supervisor_id', $user->id)
                        ->whereIn('status', ThesisStatus::activeCases()))
                    ->count(),
            ];
        }

        if ($user->isAdmin()) {
            $stats = [
                'total_users' => User::count(),
                'active_users' => User::where('is_active', true)->count(),
                'total_departments' => Department::count(),
                'total_proposals' => Proposal::count(),
                'active_theses' => Thesis::whereIn('status', ThesisStatus::activeCases())->count(),
            ];
        }

        return view('dashboard.index', [
            'user' => $user,
            'stats' => $stats,
        ]);
    }
}
