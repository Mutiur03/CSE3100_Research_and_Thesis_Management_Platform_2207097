<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected bool $seedBootstrapAdmin = true;

    protected function setUp(): void
    {
        parent::setUp();

        if ($this->seedBootstrapAdmin) {
            User::factory()->admin()->create();
        }
    }
}
