<?php

namespace Database\Seeders;

use App\Enums\Role as RoleEnum;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Creates the first Super Administrator. Credentials come from the environment
 * (ADMIN_EMAIL / ADMIN_PASSWORD); when no password is configured a strong one
 * is generated and printed once, so nothing weak is stored by default.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = strtolower(trim((string) config('auth.bootstrap_admin.email')));
        $password = config('auth.bootstrap_admin.password');

        if (app()->isProduction() && (! $email || ! $password)) {
            throw new RuntimeException('Set ADMIN_EMAIL and ADMIN_PASSWORD before creating the first production administrator.');
        }

        $email = $email ?: 'admin@example.com';

        if (User::where('email', $email)->exists()) {
            return;
        }

        $generated = false;

        if (! $password) {
            $password = bin2hex(random_bytes(12));
            $generated = true;
        }

        User::create([
            'name' => 'Super Administrator',
            'email' => $email,
            'password' => $password,
            'role_id' => Role::for(RoleEnum::SuperAdmin)->id,
        ]);

        $this->command?->info("Administrator created: {$email}");

        if ($generated && ! app()->isProduction()) {
            $this->command?->warn("Generated password (shown once): {$password}");
        }
    }
}
