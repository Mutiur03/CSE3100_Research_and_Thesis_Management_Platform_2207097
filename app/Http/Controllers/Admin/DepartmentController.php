<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(Request $request): View
    {

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

        return view('admin.departments.create', [
            'eligibleHeads' => $this->eligibleHeads(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->filled('code')) {
            $request->merge([
                'code' => strtoupper($request->input('code')),
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', 'alpha_num', 'unique:departments,code'],
            'faculty' => ['nullable', 'string', 'max:255'],
            'head_id' => ['nullable', 'integer', 'exists:users,id', $this->eligibleHeadRule()],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $department = Department::create($validated);
        $this->syncHeadAffiliation($department);

        return redirect()->route('admin.departments.show', $department)
            ->with('success', "Department \"{$department->name}\" created successfully.");
    }

    public function show(Department $department): View
    {

        $department->load('head');
        $department->loadCount([
            'users',
            'users as students_count' => fn ($q) => $q->where('role', UserRole::Student),
            'users as supervisors_count' => fn ($q) => $q->where('role', UserRole::Supervisor),
            'users as admins_count' => fn ($q) => $q->where('role', UserRole::Admin),
        ]);

        $teachers = $department->users()
            ->where('role', UserRole::Supervisor)
            ->orderBy('name')
            ->paginate(10, ['*'], 'teachers_page');

        $students = $department->users()
            ->where('role', UserRole::Student)
            ->orderBy('name')
            ->paginate(10, ['*'], 'students_page');

        return view('admin.departments.show', [
            'department' => $department,
            'teachers' => $teachers,
            'students' => $students,
        ]);
    }

    public function edit(Department $department): View
    {

        return view('admin.departments.edit', [
            'department' => $department,
            'eligibleHeads' => $this->eligibleHeads(),
        ]);
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        if ($request->filled('code')) {
            $request->merge([
                'code' => strtoupper($request->input('code')),
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:20',
                'alpha_num',
                Rule::unique('departments', 'code')->ignore($department),
            ],
            'faculty' => ['nullable', 'string', 'max:255'],
            'head_id' => ['nullable', 'integer', 'exists:users,id', $this->eligibleHeadRule()],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $department->update($validated);
        $this->syncHeadAffiliation($department);

        return redirect()->route('admin.departments.show', $department)
            ->with('success', "Department \"{$department->name}\" updated successfully.");
    }

    public function destroy(Department $department): RedirectResponse
    {

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

    /**
     * @return \Closure(string, mixed, \Closure): void
     */
    private function eligibleHeadRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if ($value === null) {
                return;
            }

            $head = User::find($value);

            if (! $head || ! in_array($head->role, [UserRole::Supervisor, UserRole::Admin], true)) {
                $fail('The department head must be a supervisor or administrator.');
            }
        };
    }
}
