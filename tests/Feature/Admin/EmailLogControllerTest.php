<?php

namespace Tests\Feature\Admin;

use App\Enums\Role as RoleEnum;
use App\Jobs\SendApplicationEmail;
use App\Models\Application;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\Position;
use App\Models\Role;
use App\Models\State;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EmailLogControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_delivery_history_filters_by_status_and_search_term(): void
    {
        $administrator = User::factory()
            ->for(Role::for(RoleEnum::RecruitmentAdmin))
            ->create();
        $matchingLog = EmailLog::create([
            'template_key' => 'submitted',
            'to_email' => 'applicant@academy.test',
            'subject' => 'Application received',
            'status' => 'sent',
            'created_at' => '2026-03-16 10:00:00',
        ]);
        EmailLog::create([
            'template_key' => 'under_review',
            'to_email' => 'other@example.test',
            'subject' => 'Application under review',
            'status' => 'failed',
            'created_at' => '2026-03-16 11:00:00',
        ]);

        $response = $this->actingAs($administrator)->get(route('admin.email-logs.index', [
            'status' => 'sent',
            'q' => 'academy',
        ]));

        $response->assertOk()
            ->assertViewIs('admin.email-logs.index')
            ->assertViewHas('logs', fn ($logs) => $logs->total() === 1 && $logs->first()->is($matchingLog))
            ->assertSee('applicant@academy.test')
            ->assertSee('Accepted by mail server')
            ->assertDontSee('other@example.test');
    }

    public function test_failed_email_has_a_retry_action_next_to_its_delivery_record(): void
    {
        $administrator = User::factory()
            ->for(Role::for(RoleEnum::RecruitmentAdmin))
            ->create();
        $log = EmailLog::create([
            'template_key' => 'submitted',
            'to_email' => 'applicant@academy.test',
            'subject' => 'Application received',
            'status' => 'failed',
            'error' => 'SMTP delivery failed.',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($administrator)
            ->get(route('admin.email-logs.index', ['status' => 'failed']));

        $response->assertOk()
            ->assertSee(route('admin.email-logs.retry', $log), false)
            ->assertSee('Retry email');
    }

    public function test_reviewer_is_forbidden_from_email_delivery_history(): void
    {
        $reviewer = User::factory()
            ->for(Role::for(RoleEnum::Reviewer))
            ->create();

        $this->actingAs($reviewer)
            ->get(route('admin.email-logs.index'))
            ->assertForbidden();
    }

    public function test_administrator_can_queue_a_retry_for_a_failed_email(): void
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
            'reference' => 'FIN-EMAIL-RETRY-HTTP',
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
        EmailTemplate::create([
            'key' => 'submitted',
            'name' => 'Application received',
            'subject' => 'Application received',
            'body' => '<p>Thank you.</p>',
            'enabled' => true,
        ]);
        $log = EmailLog::create([
            'application_id' => $application->id,
            'template_key' => 'submitted',
            'to_email' => $application->email,
            'subject' => 'Application received',
            'status' => 'failed',
            'error' => 'Previous delivery failed.',
            'created_at' => now(),
        ]);
        Queue::fake();

        $response = $this->actingAs($administrator)
            ->from(route('admin.email-logs.index'))
            ->post(route('admin.email-logs.retry', $log));

        $response->assertRedirect(route('admin.email-logs.index'));
        $response->assertSessionHas('emailStatus', 'A retry for ada@example.test has been queued.');
        $this->assertDatabaseHas('email_logs', [
            'id' => $log->id,
            'status' => 'queued',
        ]);
        Queue::assertPushed(SendApplicationEmail::class, fn (SendApplicationEmail $job): bool => $job->emailLogId === $log->id);
    }

    public function test_reviewer_is_forbidden_from_retrying_email(): void
    {
        $reviewer = User::factory()
            ->for(Role::for(RoleEnum::Reviewer))
            ->create();
        $log = EmailLog::create([
            'template_key' => 'submitted',
            'to_email' => 'applicant@academy.test',
            'subject' => 'Application received',
            'status' => 'failed',
            'created_at' => now(),
        ]);

        $this->actingAs($reviewer)
            ->post(route('admin.email-logs.retry', $log))
            ->assertForbidden();
    }
}
