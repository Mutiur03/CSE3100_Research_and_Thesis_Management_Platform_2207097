<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertStatus(200);
        $response->assertSee($user->name);
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Updated Name',
            'bio' => 'My updated bio',
            'phone' => '01712345678',
            'research_interests' => 'Machine Learning, NLP, Data Science',
        ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertEquals('Updated Name', $user->name);
        $this->assertEquals('My updated bio', $user->bio);
        $this->assertEquals('01712345678', $user->phone);
        $this->assertEquals(['Machine Learning', 'NLP', 'Data Science'], $user->research_interests);
    }

    public function test_profile_rejects_invalid_phone_number(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from(route('profile.show'))->put(route('profile.update'), [
            'name' => $user->name,
            'phone' => '+880171234567',
        ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHasErrors('phone');
    }

    public function test_avatar_can_be_uploaded(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'avatar' => UploadedFile::fake()->createWithContent(
                'avatar.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAFgwJ/lhODJwAAAABJRU5ErkJggg==')
            ),
        ]);

        $response->assertRedirect(route('profile.show'));

        $user->refresh();
        $this->assertNotNull($user->avatar);
        Storage::disk('public')->assertExists($user->avatar);
    }

    public function test_guests_cannot_access_profile(): void
    {
        $response = $this->get(route('profile.show'));

        $response->assertRedirect(route('login'));
    }
}
