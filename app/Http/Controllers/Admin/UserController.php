<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRoleRequest;
use App\Models\Department;
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
            ->with('department')
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

        // Department filter
        if ($departmentId = $request->input('department_id')) {
            $query->where('department_id', $departmentId);
        }

        $users = $query->paginate(15)->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'search' => $search,
            'roleFilter' => $role,
            'departmentFilter' => $departmentId,
            'departments' => Department::query()->orderBy('name', 'asc')->get(),
        ]);
    }

    /**
     * Show the form for editing a user's role and status.
     */
    public function edit(User $user): View
    {
        $this->authorize('manageRole', $user);

        return view('admin.users.edit', [
            'user' => $user->load('department'),
            'departments' => Department::query()->orderBy('name', 'asc')->get(),
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

        if ($request->user()->is($user) && $request->role !== $user->role->value) {
            return back()
                ->withInput()
                ->withErrors(['role' => 'You cannot change your own role.']);
        }

        $user->update([
            'role' => $request->role,
            'is_active' => $request->boolean('is_active'),
            'department_id' => $request->input('department_id'),
        ]);

        if ($user->isAdmin() && $user->email_verified_at === null) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        $message = $user->isAdmin()
            ? "User \"{$user->name}\" promoted to administrator successfully."
            : "User \"{$user->name}\" updated successfully.";

        return redirect()->route('admin.users.index')
            ->with('success', $message);
    }
}
