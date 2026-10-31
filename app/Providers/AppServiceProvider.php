<?php

namespace App\Providers;

use App\Enums\Role;
use App\Models\AcademySetting;
use App\Models\User;
use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * One gate per ability, defined from the central Role map so permissions
     * are enforced on the backend for every route that declares `can:` middleware.
     */
    public function boot(): void
    {
        Event::listen(DiagnosingHealth::class, function (): void {
            DB::connection()->getPdo();

            if (config('queue.default') === 'redis') {
                Redis::connection(config('queue.connections.redis.connection'))->ping();
            }
        });

        Gate::before(fn (User $user, string $ability): ?bool => $user->hasAbility('*') ? true : null);

        foreach ($this->abilities() as $ability) {
            Gate::define($ability, fn (User $user) => $user->hasAbility($ability));
        }

        View::composer(
            ['layouts.app', 'layouts.guest', 'layouts.admin', 'components.layouts.app', 'home', 'admin.email-templates.edit'],
            function ($view): void {
                if (! Schema::hasTable('academy_settings')) {
                    $view->with('academySettings', new AcademySetting([
                        'organization_name' => config('recruitment.organization_name'),
                    ]));

                    return;
                }

                $view->with('academySettings', AcademySetting::query()->find(1) ?? new AcademySetting([
                    'organization_name' => config('recruitment.organization_name'),
                ]));
            },
        );
    }

    /** @return list<string> */
    private function abilities(): array
    {
        $abilities = [];

        foreach (Role::cases() as $role) {
            foreach ($role->abilities() as $ability) {
                if ($ability !== '*') {
                    $abilities[] = $ability;
                }
            }
        }

        return array_values(array_unique($abilities));
    }
}
