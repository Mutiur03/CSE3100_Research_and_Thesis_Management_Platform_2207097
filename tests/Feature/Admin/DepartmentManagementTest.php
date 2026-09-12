<?php

namespace Tests\Feature\Admin;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_departments_list(): void
    {
        $admin = User::factory()->admin()->create();
        Department::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get(route('admin.departments.index'));

        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_view_departments_list(): void
    {
        $student = User::factory()->student()->create();

        $response = $this->actingAs($student)->get(route('admin.departments.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_create_department(): void
    {
        $admin = User::factory()->admin()->create();
        $head = User::factory()->supervisor()->create();

        $response = $this->actingAs($admin)->post(route('admin.departments.store'), [
            'name' => 'Computer Science',
            'code' => 'cse',
            'faculty' => 'Faculty of Science',
            'head_id' => $head->id,
            'description' => 'CS department',
        ]);

        $department = Department::where('code', 'CSE')->first();

        $response->assertRedirect(route('admin.departments.show', $department));
        $this->assertDatabaseHas('departments', [
            'name' => 'Computer Science',
            'code' => 'CSE',
            'head_id' => $head->id,
        ]);

        $head->refresh();
        $this->assertEquals($department->id, $head->department_id);
    }

    public function test_admin_can_update_department(): void
    {
        $admin = User::factory()->admin()->create();
        $department = Department::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($admin)->put(route('admin.departments.update', $department), [
            'name' => 'Updated Name',
            'code' => $department->code,
            'faculty' => $department->faculty,
            'head_id' => null,
            'description' => 'Updated description',
        ]);

        $response->assertRedirect(route('admin.departments.show', $department));

        $department->refresh();
        $this->assertEquals('Updated Name', $department->name);
    }

    public function test_admin_cannot_delete_department_with_members(): void
    {
        $admin = User::factory()->admin()->create();
        $department = Department::factory()->create();
        User::factory()->student()->create(['department_id' => $department->id]);

        $response = $this->actingAs($admin)->delete(route('admin.departments.destroy', $department));

        $response->assertRedirect();
        $response->assertSessionHasErrors('delete');
        $this->assertDatabaseHas('departments', ['id' => $department->id]);
    }

    public function test_admin_can_delete_empty_department(): void
    {
        $admin = User::factory()->admin()->create();
        $department = Department::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.departments.destroy', $department));

        $response->assertRedirect(route('admin.departments.index'));
        $this->assertDatabaseMissing('departments', ['id' => $department->id]);
    }

    public function test_admin_can_search_departments(): void
    {
        $admin = User::factory()->admin()->create();
        Department::factory()->create(['name' => 'Electrical Engineering', 'code' => 'EEE']);
        Department::factory()->create(['name' => 'Mathematics', 'code' => 'MATH']);

        $response = $this->actingAs($admin)->get(route('admin.departments.index', ['search' => 'Electrical']));

        $response->assertStatus(200);
        $response->assertSee('Electrical Engineering');
        $response->assertDontSee('Mathematics');
    }
}
