<?php

namespace Tests\Feature;

use Database\Seeders\AdminUserSeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminUserSeederTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_first_production_admin_requires_explicit_credentials(): void
    {
        $this->app['env'] = 'production';
        config([
            'auth.bootstrap_admin.email' => null,
            'auth.bootstrap_admin.password' => null,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Set ADMIN_EMAIL and ADMIN_PASSWORD');

        (new AdminUserSeeder)->run();
    }

    public function test_production_database_seeding_does_not_create_sample_positions(): void
    {
        $this->app['env'] = 'production';
        config([
            'auth.bootstrap_admin.email' => 'recruitment@example.test',
            'auth.bootstrap_admin.password' => 'test-only-password',
        ]);

        (new DatabaseSeeder)->run();

        $this->assertDatabaseCount('positions', 0);
    }
}
