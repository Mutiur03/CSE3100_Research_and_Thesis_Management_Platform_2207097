<?php

namespace Tests;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    /**
     * Most feature tests assume platform setup is already complete.
     */
    protected bool $seedBootstrapAdmin = true;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'setup.admin_email' => 'admin@test.com',
        ]);

        if ($this->seedBootstrapAdmin && Schema::hasTable('users') && User::query()->where('role', UserRole::Admin)->doesntExist()) {
            User::factory()->admin()->create([
                'email' => 'admin@test.com',
                'email_verified_at' => now(),
            ]);
        }
    }
}
