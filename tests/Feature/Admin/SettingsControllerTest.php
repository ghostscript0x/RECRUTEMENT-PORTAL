<?php

namespace Tests\Feature\Admin;

use App\Enums\Role as RoleEnum;
use App\Models\AcademySetting;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SettingsControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_super_administrator_can_save_academy_details_for_public_pages(): void
    {
        $administrator = User::factory()
            ->for(Role::for(RoleEnum::SuperAdmin))
            ->create();

        $response = $this->actingAs($administrator)
            ->from(route('admin.settings.edit'))
            ->put(route('admin.settings.update'), [
                'organization_name' => 'FINTEC Learning Hub',
                'support_email' => 'help@fintec.test',
                'support_phone' => '+2348000000000',
            ]);

        $response->assertRedirect(route('admin.settings.edit'))
            ->assertSessionHas('success', 'Academy settings saved.');
        $this->assertDatabaseHas('academy_settings', [
            'id' => 1,
            'organization_name' => 'FINTEC Learning Hub',
            'support_email' => 'help@fintec.test',
            'support_phone' => '+2348000000000',
            'updated_by' => $administrator->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'academy.settings_updated',
            'user_id' => $administrator->id,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('FINTEC Learning Hub')
            ->assertSee('mailto:help@fintec.test', false)
            ->assertSee('tel:+2348000000000', false);
    }

    public function test_recruitment_administrator_cannot_manage_admin_accounts_or_settings(): void
    {
        $administrator = User::factory()
            ->for(Role::for(RoleEnum::RecruitmentAdmin))
            ->create();

        $this->actingAs($administrator)
            ->get(route('admin.users.index'))
            ->assertForbidden();

        $this->get(route('admin.settings.edit'))
            ->assertForbidden();
    }

    public function test_invalid_support_email_does_not_change_saved_settings(): void
    {
        $administrator = User::factory()
            ->for(Role::for(RoleEnum::SuperAdmin))
            ->create();
        $settings = new AcademySetting([
            'organization_name' => 'FINTEC Academy',
            'support_email' => 'old@fintec.test',
        ]);
        $settings->id = 1;
        $settings->save();

        $response = $this->actingAs($administrator)
            ->from(route('admin.settings.edit'))
            ->put(route('admin.settings.update'), [
                'organization_name' => 'Changed Academy',
                'support_email' => 'not-an-email',
            ]);

        $response->assertRedirect(route('admin.settings.edit'))
            ->assertSessionHasErrors(['support_email']);
        $this->assertDatabaseHas('academy_settings', [
            'id' => 1,
            'organization_name' => 'FINTEC Academy',
            'support_email' => 'old@fintec.test',
        ]);
    }
}
