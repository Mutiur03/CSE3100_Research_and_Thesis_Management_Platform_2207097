<?php

namespace Tests\Feature\Setup;

use App\Enums\UserRole;
use App\Mail\AdminSetupCodeMail;
use App\Models\AdminSetupToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminSetupTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seedBootstrapAdmin = false;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'setup.admin_email' => 'bootstrap@university.edu',
            'setup.token_lifetime' => 60,
        ]);
    }

    public function test_visitors_are_redirected_to_setup_when_no_admin_exists(): void
    {
        $response = $this->get(route('login'));

        $response->assertRedirect(route('setup.index'));
    }

    public function test_setup_page_is_available_before_first_admin_exists(): void
    {
        $response = $this->get(route('setup.index'));

        $response->assertStatus(200);
        $response->assertSee('Initial administrator setup');
        $response->assertSee('university.edu');
    }

    public function test_setup_code_is_emailed_to_configured_address(): void
    {
        Mail::fake();

        $response = $this->post(route('setup.code.send'));

        $response->assertRedirect(route('setup.complete'));
        $response->assertSessionHas('success');

        Mail::assertSent(AdminSetupCodeMail::class, function (AdminSetupCodeMail $mail): bool {
            return $mail->hasTo('bootstrap@university.edu');
        });

        $this->assertDatabaseHas('admin_setup_tokens', [
            'email' => 'bootstrap@university.edu',
        ]);
    }

    public function test_valid_setup_code_creates_administrator(): void
    {
        $plainCode = 'ABCD-EFGH-IJKL-MNOP';
        $normalizedCode = 'ABCDEFGHIJKLMNOP';

        AdminSetupToken::create([
            'email' => 'bootstrap@university.edu',
            'token' => Hash::make($normalizedCode),
            'expires_at' => now()->addHour(),
            'created_at' => now(),
        ]);

        $response = $this->post(route('setup.complete.store'), [
            'code' => $plainCode,
            'name' => 'Platform Admin',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $admin = User::query()->where('email', 'bootstrap@university.edu')->first();

        $this->assertNotNull($admin);
        $this->assertEquals(UserRole::Admin, $admin->role);
        $this->assertEquals('Platform Admin', $admin->name);
        $this->assertNotNull($admin->email_verified_at);
        $this->assertDatabaseCount('admin_setup_tokens', 0);
    }

    public function test_invalid_setup_code_is_rejected(): void
    {
        AdminSetupToken::create([
            'email' => 'bootstrap@university.edu',
            'token' => Hash::make('VALIDCODE123456'),
            'expires_at' => now()->addHour(),
            'created_at' => now(),
        ]);

        $response = $this->post(route('setup.complete.store'), [
            'code' => 'WRONG-CODE-HERE-NOW',
            'name' => 'Platform Admin',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('code');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'bootstrap@university.edu']);
    }

    public function test_expired_setup_code_is_rejected(): void
    {
        AdminSetupToken::create([
            'email' => 'bootstrap@university.edu',
            'token' => Hash::make('ABCDEFGHIJKLMNOP'),
            'expires_at' => now()->subMinute(),
            'created_at' => now()->subHour(),
        ]);

        $response = $this->post(route('setup.complete.store'), [
            'code' => 'ABCD-EFGH-IJKL-MNOP',
            'name' => 'Platform Admin',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_setup_routes_are_blocked_after_admin_exists(): void
    {
        User::factory()->admin()->create(['email' => 'bootstrap@university.edu']);

        $response = $this->get(route('setup.index'));

        $response->assertRedirect(route('login'));
    }
}
