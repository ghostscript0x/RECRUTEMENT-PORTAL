<?php

namespace Tests\Feature\Services;

use App\Jobs\SendApplicationEmail;
use App\Mail\TemplateMail;
use App\Models\AcademySetting;
use App\Models\Application;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\Position;
use App\Models\State;
use App\Services\NotificationDispatcher;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NotificationDispatcherTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_email_uses_academy_branding_and_escapes_applicant_data(): void
    {
        $position = Position::create([
            'title' => 'Software Developer',
            'description' => 'Build software.',
            'status' => 'published',
        ]);
        $state = State::create(['name' => 'Ogun', 'slug' => 'ogun']);
        $application = Application::create([
            'reference' => 'FIN-EMAIL-BRANDED',
            'position_id' => $position->id,
            'first_name' => '<img src=x onerror=alert(1)>',
            'last_name' => 'Applicant',
            'email' => 'applicant@example.test',
            'phone' => '08000000000',
            'date_of_birth' => '1998-02-01',
            'gender' => 'female',
            'residence_state_id' => $state->id,
            'residential_address' => 'Training Road',
            'highest_qualification' => 'BSc',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
        $settings = new AcademySetting([
            'organization_name' => 'FINTEC Academy',
            'support_email' => 'help@fintec.test',
            'support_phone' => '+2348000000000',
        ]);
        $settings->id = 1;
        $settings->save();
        EmailTemplate::create([
            'key' => 'submitted',
            'name' => 'Application received',
            'subject' => 'Application Received — {{organization_name}}',
            'body' => '<p>Dear {{applicant_name}},</p><p>We received your application.</p>',
            'enabled' => true,
        ]);
        $mail = new TemplateMail('submitted', $application);

        $mail->assertSeeInHtml('FINTEC Academy')
            ->assertSeeInHtml(asset('images/fintec-logo.png'))
            ->assertSeeInHtml('help@fintec.test')
            ->assertSeeInHtml('&lt;img src=x onerror=alert(1)&gt;', false)
            ->assertDontSeeInHtml('<img src=x onerror=alert(1)>', false);
        $this->assertSame('FINTEC Academy', $mail->envelope()->from->name);
    }

    public function test_notification_is_queued_after_creating_a_delivery_log(): void
    {
        $position = Position::create([
            'title' => 'Software Developer',
            'description' => 'Build software.',
            'status' => 'published',
        ]);
        $state = State::create(['name' => 'Ogun', 'slug' => 'ogun']);
        $application = Application::create([
            'reference' => 'FIN-EMAIL-HEADER',
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
        Queue::fake();

        $log = NotificationDispatcher::send($application, 'submitted');

        $this->assertSame('queued', $log?->status);
        $this->assertDatabaseHas('email_logs', [
            'application_id' => $application->id,
            'status' => 'queued',
        ]);
        Queue::assertPushed(SendApplicationEmail::class, fn (SendApplicationEmail $job): bool => $job->emailLogId === $log?->id);
    }

    public function test_queued_email_job_sends_mail_and_marks_the_log_sent(): void
    {
        $position = Position::create([
            'title' => 'Software Developer',
            'description' => 'Build software.',
            'status' => 'published',
        ]);
        $state = State::create(['name' => 'Ogun', 'slug' => 'ogun']);
        $application = Application::create([
            'reference' => 'FIN-EMAIL-RETRY',
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
            'status' => 'queued',
            'created_at' => now(),
        ]);
        Mail::fake();

        (new SendApplicationEmail($log->id))->handle();

        Mail::assertSent(TemplateMail::class, fn (TemplateMail $mail): bool => $mail->application->is($application));
        $this->assertDatabaseHas('email_logs', [
            'id' => $log->id,
            'status' => 'sent',
            'error' => null,
        ]);
    }

    public function test_failed_email_can_be_retried_without_sending_during_the_request(): void
    {
        $position = Position::create([
            'title' => 'Software Developer',
            'description' => 'Build software.',
            'status' => 'published',
        ]);
        $state = State::create(['name' => 'Ogun', 'slug' => 'ogun']);
        $application = Application::create([
            'reference' => 'FIN-EMAIL-RETRY-QUEUED',
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
        $log = EmailLog::create([
            'application_id' => $application->id,
            'template_key' => 'submitted',
            'to_email' => $application->email,
            'subject' => 'Application received',
            'status' => 'failed',
            'error' => 'Previous attempt failed.',
            'created_at' => now(),
        ]);
        Queue::fake();

        $retriedLog = NotificationDispatcher::retry($log);

        $this->assertSame('queued', $retriedLog->status);
        $this->assertNull($retriedLog->error);
        $this->assertDatabaseHas('email_logs', [
            'id' => $log->id,
            'status' => 'queued',
            'error' => null,
        ]);
        Queue::assertPushed(SendApplicationEmail::class, fn (SendApplicationEmail $job): bool => $job->emailLogId === $log->id);
    }

    public function test_failed_email_job_records_a_safe_delivery_error(): void
    {
        $position = Position::create([
            'title' => 'Software Developer',
            'description' => 'Build software.',
            'status' => 'published',
        ]);
        $state = State::create(['name' => 'Ogun', 'slug' => 'ogun']);
        $application = Application::create([
            'reference' => 'FIN-EMAIL-FAILURE',
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
        $log = EmailLog::create([
            'application_id' => $application->id,
            'template_key' => 'submitted',
            'to_email' => $application->email,
            'subject' => 'Application received',
            'status' => 'sending',
            'created_at' => now(),
        ]);

        (new SendApplicationEmail($log->id))->failed(new \RuntimeException('private transport detail'));

        $this->assertDatabaseHas('email_logs', [
            'id' => $log->id,
            'status' => 'failed',
            'error' => 'Email delivery failed after all retry attempts. Check application logs for details.',
        ]);
    }
}
