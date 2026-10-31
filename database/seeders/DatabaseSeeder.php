<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's core reference data.
     */
    public function run(): void
    {
        $seeders = [
            RoleSeeder::class,
            GeographySeeder::class,
            EmailTemplateSeeder::class,
            AdminUserSeeder::class,
        ];

        if (! app()->isProduction()) {
            $seeders[] = PositionSeeder::class;
        }

        $this->call($seeders);
    }
}
