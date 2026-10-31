<?php

namespace Tests\Feature\Admin;

use App\Enums\Role as RoleEnum;
use App\Models\Application;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\Interview;
use App\Models\Position;
use App\Models\Role;
use App\Models\State;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_super_administrator_can_render_all_admin_pages(): void
    {
        $role = Role::for(RoleEnum::SuperAdmin);
        $administrator = User::factory()->for($role)->create();
        $template = EmailTemplate::create([
            'key' => 'submitted',
            'name' => 'Application received',
            'subject' => 'Application received',
            'body' => '<p>Thank you, {{applicant_name}}.</p>',
            'enabled' => true,
        ]);

        $this->actingAs($administrator);

        $pages = [
            [route('admin.dashboard'), 'admin.dashboard'],
            [route('admin.reports.index'), 'admin.reports.index'],
            [route('admin.applications.index'), 'admin.applications.index'],
            [route('admin.positions.index'), 'admin.positions.index'],
            [route('admin.positions.create'), 'admin.positions.form'],
            [route('admin.users.index'), 'admin.users.index'],
            [route('admin.settings.edit'), 'admin.settings.edit'],
            [route('admin.templates.index'), 'admin.email-templates.index'],
            [route('admin.templates.edit', $template), 'admin.email-templates.edit'],
            [route('admin.email-logs.index'), 'admin.email-logs.index'],
            [route('admin.audit.index'), 'admin.audit.index'],
        ];

        foreach ($pages as [$url, $view]) {
            $this->get($url)
                ->assertOk()
                ->assertViewIs($view);
        }

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('aria-label="Admin navigation"', false)
            ->assertSee('grid grid-cols-2 gap-2 p-3 text-sm md:grid-cols-3 lg:flex lg:flex-col', false)
            ->assertSee('src="'.asset('images/fintec-logo.png').'"', false)
            ->assertSee('Email delivery');

        $this->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Temporary password')
            ->assertSee('aria-label="Show password"', false)
            ->assertSee('aria-controls="password"', false)
            ->assertSee('data-password-toggle', false);
    }

    public function test_email_template_editor_offers_visual_controls_and_a_live_preview(): void
    {
        $administrator = User::factory()
            ->for(Role::for(RoleEnum::SuperAdmin))
            ->create();
        $template = EmailTemplate::create([
            'key' => 'submitted',
            'name' => 'Application received',
            'subject' => 'Application received',
            'body' => '<p>Thank you, {{applicant_name}}.</p>',
            'enabled' => true,
        ]);

        $response = $this->actingAs($administrator)
            ->get(route('admin.templates.edit', $template));

        $response->assertOk()
            ->assertViewIs('admin.email-templates.edit')
            ->assertSee('contenteditable="true"', false)
            ->assertSee('data-email-command="bold"', false)
            ->assertSee('data-email-insert="{{applicant_name}}"', false)
            ->assertSee('data-email-target="subject"', false)
            ->assertSee('title="Rendered sample email"', false)
            ->assertSee('data-brand="FINTEC Digital Skills Academy"', false)
            ->assertSee('data-logo="'.asset('images/fintec-logo.png').'"', false)
            ->assertSee('sandbox=""', false)
            ->assertSee('Save changes')
            ->assertSee('Automatic delivery enabled')
            ->assertDontSee('Message makeover');
    }

    public function test_application_list_provides_a_compact_mobile_review_card(): void
    {
        $administrator = User::factory()
            ->for(Role::for(RoleEnum::RecruitmentAdmin))
            ->create();
        $position = Position::create([
            'title' => 'Software Developer',
            'description' => 'Build software.',
            'status' => 'published',
        ]);
        $state = State::create(['name' => 'Ogun', 'slug' => 'ogun']);
        Application::create([
            'reference' => 'APP-2026-TEST1',
            'position_id' => $position->id,
            'first_name' => 'Ada',
            'last_name' => 'Okafor',
            'email' => 'ada@example.test',
            'phone' => '08000000000',
            'date_of_birth' => '1998-02-01',
            'gender' => 'female',
            'residence_state_id' => $state->id,
            'residential_address' => 'Training Road',
            'highest_qualification' => 'BSc',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($administrator)
            ->get(route('admin.applications.index'));

        $response->assertOk()
            ->assertSee('aria-label="Mobile application list"', false)
            ->assertSee('Review application')
            ->assertSee('APP-2026-TEST1');
    }

    public function test_dashboard_highlights_failed_emails_and_upcoming_interviews_for_recruitment_admins(): void
    {
        $administrator = User::factory()
            ->for(Role::for(RoleEnum::RecruitmentAdmin))
            ->create();
        $position = Position::create([
            'title' => 'Software Developer',
            'description' => 'Build software.',
            'status' => 'published',
        ]);
        $state = State::create(['name' => 'Ogun', 'slug' => 'ogun']);
        $application = Application::create([
            'reference' => 'APP-2026-DASH01',
            'position_id' => $position->id,
            'first_name' => 'Ada',
            'last_name' => 'Okafor',
            'email' => 'ada@example.test',
            'phone' => '08000000000',
            'date_of_birth' => '1998-02-01',
            'gender' => 'female',
            'residence_state_id' => $state->id,
            'residential_address' => 'Training Road',
            'highest_qualification' => 'BSc',
            'status' => 'interview',
            'submitted_at' => now(),
        ]);
        Interview::create([
            'application_id' => $application->id,
            'scheduled_at' => now()->addDay(),
            'duration_minutes' => 30,
            'mode' => 'google_meet',
            'location' => 'https://meet.google.com/example',
            'status' => 'scheduled',
            'reschedule_token_hash' => str_repeat('a', 64),
            'reschedule_token' => str_repeat('b', 64),
        ]);
        EmailLog::create([
            'application_id' => $application->id,
            'template_key' => 'submitted',
            'to_email' => 'ada@example.test',
            'subject' => 'Application received',
            'status' => 'failed',
            'error' => 'Delivery failed.',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($administrator)
            ->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertViewHas('failedEmailCount', 1)
            ->assertViewHas('upcomingInterviews', fn ($interviews) => $interviews->count() === 1)
            ->assertSee('1 failed email')
            ->assertSee('Review failed emails')
            ->assertSee('Upcoming interviews')
            ->assertSeeText('Ada Okafor')
            ->assertSeeText('Google Meet');
    }

    public function test_guest_is_redirected_before_admin_dashboard_access(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_user_without_dashboard_ability_is_forbidden_from_dashboard_and_reports(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->get(route('admin.dashboard'))->assertForbidden();
        $this->get(route('admin.reports.index'))->assertForbidden();
    }
}
