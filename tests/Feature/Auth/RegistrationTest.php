<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get(route('register'));
        $response->assertStatus(200);
    }

    public function test_new_students_can_register(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Test Student',
            'email' => 'student@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'student',
        ]);

        $this->assertAuthenticated();

        $user = User::where('email', 'student@test.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals(UserRole::Student, $user->role);

        $response->assertRedirect(route('verification.notice'));
    }

    public function test_new_supervisors_can_register(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Test Supervisor',
            'email' => 'supervisor@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'supervisor',
        ]);

        $this->assertAuthenticated();

        $user = User::where('email', 'supervisor@test.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals(UserRole::Supervisor, $user->role);
    }

    public function test_users_cannot_register_as_admin(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Sneaky Admin',
            'email' => 'other-admin@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('role');
    }

    public function test_registration_requires_valid_data(): void
    {
        $response = $this->post(route('register'), [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'mismatch',
            'role' => '',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['name', 'email', 'password', 'role']);
    }

    public function test_duplicate_email_is_rejected(): void
    {
        User::factory()->create(['email' => 'taken@test.com']);

        $response = $this->post(route('register'), [
            'name' => 'Another User',
            'email' => 'taken@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'student',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }
}
