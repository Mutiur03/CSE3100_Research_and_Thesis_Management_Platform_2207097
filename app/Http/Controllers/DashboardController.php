<?php

namespace App\Http\Controllers;

use App\Models\Department;
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
            ];
        }

        return view('dashboard.index', [
            'user' => $user,
            'stats' => $stats,
        ]);
    }
}
