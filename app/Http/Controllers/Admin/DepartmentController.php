<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDepartmentRequest;
use App\Http\Requests\Admin\UpdateDepartmentRequest;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Department::class);

        $query = Department::query()
            ->with('head')
            ->withCount('users')
            ->orderBy('name');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('faculty', 'like', "%{$search}%");
            });
        }

        if ($faculty = $request->input('faculty')) {
            $query->where('faculty', $faculty);
        }

        $departments = $query->paginate(15)->withQueryString();
        $faculties = Department::query()
            ->whereNotNull('faculty')
            ->distinct()
            ->orderBy('faculty')
            ->pluck('faculty');

        return view('admin.departments.index', [
            'departments' => $departments,
            'search' => $search,
            'facultyFilter' => $faculty,
            'faculties' => $faculties,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Department::class);

        return view('admin.departments.create', [
            'eligibleHeads' => $this->eligibleHeads(),
        ]);
    }

    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        $this->authorize('create', Department::class);

        $department = Department::create($request->validated());
        $this->syncHeadAffiliation($department);

        return redirect()->route('admin.departments.show', $department)
            ->with('success', "Department \"{$department->name}\" created successfully.");
    }

    public function show(Department $department): View
    {
        $this->authorize('view', $department);

        $department->load('head');
        $department->loadCount([
            'users',
            'users as students_count' => fn ($q) => $q->where('role', UserRole::Student),
            'users as supervisors_count' => fn ($q) => $q->where('role', UserRole::Supervisor),
            'users as reviewers_count' => fn ($q) => $q->where('role', UserRole::Reviewer),
            'users as admins_count' => fn ($q) => $q->where('role', UserRole::Admin),
        ]);

        $members = $department->users()
            ->orderBy('name')
            ->paginate(10, ['*'], 'members_page');

        return view('admin.departments.show', [
            'department' => $department,
            'members' => $members,
        ]);
    }

    public function edit(Department $department): View
    {
        $this->authorize('update', $department);

        return view('admin.departments.edit', [
            'department' => $department,
            'eligibleHeads' => $this->eligibleHeads(),
        ]);
    }

    public function update(UpdateDepartmentRequest $request, Department $department): RedirectResponse
    {
        $this->authorize('update', $department);

        $department->update($request->validated());
        $this->syncHeadAffiliation($department);

        return redirect()->route('admin.departments.show', $department)
            ->with('success', "Department \"{$department->name}\" updated successfully.");
    }

    public function destroy(Department $department): RedirectResponse
    {
        $this->authorize('delete', $department);

        if ($department->users()->exists()) {
            return back()->withErrors([
                'delete' => 'Cannot delete a department that still has affiliated members. Reassign users first.',
            ]);
        }

        $department->delete();

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department deleted successfully.');
    }

    /**
     * @return Collection<int, User>
     */
    private function eligibleHeads()
    {
        return User::query()
            ->whereIn('role', [UserRole::Supervisor, UserRole::Admin])
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);
    }

    private function syncHeadAffiliation(Department $department): void
    {
        if (! $department->head_id) {
            return;
        }

        $head = User::find($department->head_id);

        if ($head && $head->department_id !== $department->id) {
            $head->update(['department_id' => $department->id]);
        }
    }
}
