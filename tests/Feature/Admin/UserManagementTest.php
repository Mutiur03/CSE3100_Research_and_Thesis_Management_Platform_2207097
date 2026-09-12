<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_users_list(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->count(5)->create();

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_view_users_list(): void
    {
        $student = User::factory()->student()->create();

        $response = $this->actingAs($student)->get(route('admin.users.index'));

        $response->assertStatus(403);
    }

    public function test_supervisor_cannot_view_users_list(): void
    {
        $supervisor = User::factory()->supervisor()->create();

        $response = $this->actingAs($supervisor)->get(route('admin.users.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_edit_user_page(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->student()->create();

        $response = $this->actingAs($admin)->get(route('admin.users.edit', $user));

        $response->assertStatus(200);
        $response->assertSee($user->name);
    }

    public function test_admin_can_change_user_role(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->student()->create();

        $response = $this->actingAs($admin)->put(route('admin.users.update', $user), [
            'role' => 'supervisor',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertEquals(UserRole::Supervisor, $user->role);
    }

    public function test_admin_can_deactivate_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->student()->create(['is_active' => true]);

        $response = $this->actingAs($admin)->put(route('admin.users.update', $user), [
            'role' => $user->role->value,
            'is_active' => false,
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $user->refresh();
        $this->assertFalse($user->is_active);
    }

    public function test_admin_accounts_are_excluded_from_users_list(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Setup Admin']);
        $student = User::factory()->student()->create(['name' => 'Visible Student']);

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertSee('Visible Student');
        $response->assertDontSee($admin->email);
    }

    public function test_admin_cannot_edit_administrator_accounts(): void
    {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.users.edit', $otherAdmin));

        $response->assertStatus(403);
    }

    public function test_admin_can_promote_user_to_administrator(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->student()->create(['email_verified_at' => null]);

        $response = $this->actingAs($admin)->put(route('admin.users.update', $user), [
            'role' => 'admin',
            'is_active' => true,
            'confirm_admin_promotion' => '1',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertEquals(UserRole::Admin, $user->role);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_admin_promotion_requires_confirmation(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->student()->create();

        $response = $this->actingAs($admin)->put(route('admin.users.update', $user), [
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response->assertSessionHasErrors('confirm_admin_promotion');

        $user->refresh();
        $this->assertEquals(UserRole::Student, $user->role);
    }

    public function test_admin_cannot_deactivate_own_account(): void
    {
        $admin = User::factory()->admin()->create(['is_active' => true]);

        $response = $this->actingAs($admin)->put(route('admin.users.update', $admin), [
            'role' => $admin->role->value,
            'is_active' => false,
        ]);

        $response->assertStatus(403);

        $admin->refresh();
        $this->assertTrue($admin->is_active);
    }

    public function test_admin_can_activate_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->inactive()->create();

        $response = $this->actingAs($admin)->put(route('admin.users.update', $user), [
            'role' => $user->role->value,
            'is_active' => true,
        ]);

        $user->refresh();
        $this->assertTrue($user->is_active);
    }

    public function test_admin_can_search_users(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['name' => 'John Doe', 'email' => 'john@test.com']);
        User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@test.com']);

        $response = $this->actingAs($admin)->get(route('admin.users.index', ['search' => 'John']));

        $response->assertStatus(200);
        $response->assertSee('John Doe');
    }

    public function test_admin_can_filter_users_by_role(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->student()->count(3)->create();
        User::factory()->supervisor()->count(2)->create();

        $response = $this->actingAs($admin)->get(route('admin.users.index', ['role' => 'student']));

        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_update_user(): void
    {
        $student = User::factory()->student()->create();
        $other = User::factory()->create();

        $response = $this->actingAs($student)->put(route('admin.users.update', $other), [
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response->assertStatus(403);
    }
}
