<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRoleRequest;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of all users.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);
        $query = User::query()
            ->where('role', '!=', UserRole::Admin)
            ->orderBy('name');

        // Search filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        $users = $query->paginate(15)->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'search' => $search,
            'roleFilter' => $role,
        ]);
    }

    /**
     * Show the form for editing a user's role and status.
     */
    public function edit(User $user): View
    {
        $this->authorize('manageRole', $user);

        return view('admin.users.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the user's role and active status.
     */
    public function update(UpdateUserRoleRequest $request, User $user): RedirectResponse
    {
        $this->authorize('manageRole', $user);
        if ($request->user()->is($user) && ! $request->boolean('is_active')) {
            return back()
                ->withInput()
                ->withErrors(['is_active' => 'You cannot deactivate your own account.']);
        }

        $user->update([
            'role' => $request->role,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "User \"{$user->name}\" updated successfully.");
    }
}
