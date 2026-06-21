<?php

namespace App\Http\Controllers;

use App\Enums\ProposalStatus;
use App\Models\Department;
use App\Models\Proposal;
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

        if ($user->isAdmin()) {
            $stats = [
                'total_users' => User::count(),
                'active_users' => User::where('is_active', true)->count(),
                'total_departments' => Department::count(),
                'total_proposals' => Proposal::count(),
            ];
        }

        if ($user->isStudent()) {
            $stats = [
                'my_proposals' => $user->proposalsAsStudent()->count(),
                'pending_review' => $user->proposalsAsStudent()
                    ->whereIn('status', [ProposalStatus::Submitted, ProposalStatus::UnderReview])
                    ->count(),
                'approved_proposals' => $user->proposalsAsStudent()
                    ->where('status', ProposalStatus::Approved)
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
            ];
        }

        return view('dashboard.index', [
            'user' => $user,
            'stats' => $stats,
        ]);
    }
}
