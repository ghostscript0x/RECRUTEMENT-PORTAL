<?php

namespace Tests\Feature\Admin;

use App\Enums\Role as RoleEnum;
use App\Jobs\SendApplicationEmail;
use App\Mail\TemplateMail;
use App\Models\Application;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\Interview;
use App\Models\Position;
use App\Models\Role;
use App\Models\State;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class InterviewControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_interview_details_can_be_saved_as_a_draft_without_changing_status_or_sending_email(): void
    {
        $administrator = User::factory()->for(Role::for(RoleEnum::RecruitmentAdmin))->create();
        $application = $this->makeApplication('APP-2026-INT001', 'shortlisted');
        Queue::fake();

        $response = $this->actingAs($administrator)
            ->post(route('admin.interviews.store', $application), $this->interviewInput([
                'action' => 'save',
                'mode' => 'whatsapp',
                'location' => 'https://wa.me/2348000000000',
            ]));

        $response->assertRedirect(route('admin.interviews.index'))
            ->assertSessionHas('success', 'Interview details saved as a draft. The invitation has not been sent.');
        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'status' => 'shortlisted',
        ]);
        $this->assertDatabaseHas('interviews', [
            'application_id' => $application->id,
            'mode' => 'whatsapp',
            'location' => 'https://wa.me/2348000000000',
            'email_subject' => 'Interview invitation for Ada Okafor',
            'email_message' => 'We are pleased to invite you to an interview. Please confirm that you received this message.',
        ]);
        $this->assertDatabaseCount('email_logs', 0);
        Queue::assertNothingPushed();
    }

    public function test_changing_status_to_interview_sends_the_admin_composed_branded_invitation(): void
    {
        $administrator = User::factory()->for(Role::for(RoleEnum::RecruitmentAdmin))->create();
        $application = $this->makeApplication('APP-2026-INT002', 'shortlisted');
        $interview = $this->makeInterview($application);
        EmailTemplate::create([
            'key' => 'interview',
            'name' => 'Legacy Interview Template',
            'subject' => 'Legacy default subject',
            'body' => '<p>Legacy default content</p>',
            'enabled' => false,
        ]);
        Queue::fake();

        $response = $this->actingAs($administrator)
            ->from(route('admin.applications.show', $application))
            ->post(route('admin.applications.status', $application), ['status' => 'interview']);

        $response->assertRedirect(route('admin.applications.show', $application))
            ->assertSessionHas('success', 'Status updated to "Interview".');
        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'status' => 'interview',
        ]);
        $emailLog = EmailLog::query()->where('application_id', $application->id)->firstOrFail();
        $this->assertSame('interview', $emailLog->template_key);
        $this->assertSame('Interview invitation — Ada Okafor', $emailLog->subject);
        $this->assertSame('Google Meet', $emailLog->context['interview_mode']);
        $this->assertSame('https://meet.google.com/abc-defg-hij', $emailLog->context['interview_location']);
        $this->assertSame($interview->email_message, $emailLog->context['interview_email_message']);
        $this->assertNotNull($interview->fresh()->invitation_sent_at);
        Queue::assertPushed(SendApplicationEmail::class, 1);

        $mail = new TemplateMail('interview', $application, $emailLog->context);
        $body = $mail->content()->with['body'];
        $this->assertStringContainsString('https://meet.google.com/abc-defg-hij', $body);
        $this->assertStringContainsString('request a reschedule', $body);
        $this->assertSame('Interview invitation — Ada Okafor', $mail->envelope()->subject);
    }

    public function test_status_change_redirects_to_interview_setup_when_no_details_have_been_prepared(): void
    {
        $administrator = User::factory()->for(Role::for(RoleEnum::RecruitmentAdmin))->create();
        $application = $this->makeApplication('APP-2026-INT003', 'shortlisted');
        Queue::fake();

        $this->actingAs($administrator)
            ->post(route('admin.applications.status', $application), ['status' => 'interview'])
            ->assertRedirect(route('admin.interviews.edit', $application));

        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'status' => 'shortlisted',
        ]);
        $this->assertDatabaseCount('email_logs', 0);
        Queue::assertNothingPushed();
    }

    public function test_unprepared_applicant_must_be_shortlisted_before_interview_status(): void
    {
        $administrator = User::factory()->for(Role::for(RoleEnum::RecruitmentAdmin))->create();
        $application = $this->makeApplication('APP-2026-INT009', 'submitted');
        Queue::fake();

        $response = $this->actingAs($administrator)
            ->from(route('admin.applications.show', $application))
            ->post(route('admin.applications.status', $application), ['status' => 'interview']);

        $response->assertRedirect(route('admin.applications.show', $application))
            ->assertSessionHasErrors(['status']);
        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'status' => 'submitted',
        ]);
        Queue::assertNothingPushed();
    }

    public function test_sending_from_the_interview_page_saves_custom_contact_details_and_changes_status(): void
    {
        $administrator = User::factory()->for(Role::for(RoleEnum::RecruitmentAdmin))->create();
        $application = $this->makeApplication('APP-2026-INT004', 'shortlisted');
        Queue::fake();

        $response = $this->actingAs($administrator)
            ->post(route('admin.interviews.store', $application), $this->interviewInput([
                'action' => 'send',
                'mode' => 'zoom',
                'location' => 'https://zoom.us/j/123456789',
                'email_subject' => 'A tailored interview invitation',
                'email_message' => 'Please join using the link below and have your portfolio ready.',
            ]));

        $response->assertRedirect(route('admin.interviews.index'))
            ->assertSessionHas('success');
        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'status' => 'interview',
        ]);
        $this->assertDatabaseHas('interviews', [
            'application_id' => $application->id,
            'mode' => 'zoom',
            'location' => 'https://zoom.us/j/123456789',
        ]);
        $this->assertDatabaseHas('email_logs', [
            'application_id' => $application->id,
            'subject' => 'A tailored interview invitation',
            'status' => 'queued',
        ]);
        Queue::assertPushed(SendApplicationEmail::class, 1);
    }

    public function test_resending_an_updated_interview_queues_the_new_custom_message(): void
    {
        $administrator = User::factory()->for(Role::for(RoleEnum::RecruitmentAdmin))->create();
        $application = $this->makeApplication('APP-2026-INT005', 'interview');
        $this->makeInterview($application);
        Queue::fake();

        $response = $this->actingAs($administrator)
            ->post(route('admin.interviews.store', $application), $this->interviewInput([
                'action' => 'send',
                'mode' => 'phone',
                'location' => '+234 800 000 0000',
                'email_subject' => 'Updated interview details',
                'email_message' => 'We have updated your interview details. Please call the number below.',
            ]));

        $response->assertRedirect(route('admin.interviews.index'));
        $this->assertDatabaseHas('email_logs', [
            'application_id' => $application->id,
            'subject' => 'Updated interview details',
            'status' => 'queued',
        ]);
        $this->assertDatabaseHas('interviews', [
            'application_id' => $application->id,
            'mode' => 'phone',
            'location' => '+234 800 000 0000',
        ]);
        Queue::assertPushed(SendApplicationEmail::class, 1);
    }

    public function test_interview_invitation_escapes_admin_composed_html(): void
    {
        $administrator = User::factory()->for(Role::for(RoleEnum::RecruitmentAdmin))->create();
        $application = $this->makeApplication('APP-2026-INT006', 'shortlisted');
        $interview = $this->makeInterview($application, [
            'email_message' => '<script>alert("bad")</script> Please join the meeting.',
        ]);
        Queue::fake();

        $this->actingAs($administrator)
            ->post(route('admin.applications.status', $application), ['status' => 'interview']);

        $emailLog = EmailLog::query()->where('application_id', $application->id)->firstOrFail();
        $body = (new TemplateMail('interview', $application, $emailLog->context))->content()->with['body'];
        $this->assertStringContainsString('&lt;script&gt;', $body);
        $this->assertStringNotContainsString('<script>', $body);
    }

    public function test_recruitment_admin_can_choose_a_contact_channel_and_compose_an_invitation(): void
    {
        $administrator = User::factory()->for(Role::for(RoleEnum::RecruitmentAdmin))->create();
        $application = $this->makeApplication('APP-2026-INT007', 'shortlisted');

        $this->actingAs($administrator)
            ->get(route('admin.interviews.edit', $application))
            ->assertOk()
            ->assertSee('Google Meet')
            ->assertSee('Zoom')
            ->assertSee('WhatsApp')
            ->assertSee('Phone call')
            ->assertSee('In person')
            ->assertSee('Compose the invitation')
            ->assertSee('Save draft');
    }

    public function test_interview_invitation_is_not_listed_as_an_automated_email_template(): void
    {
        $administrator = User::factory()->for(Role::for(RoleEnum::RecruitmentAdmin))->create();
        EmailTemplate::create([
            'key' => 'interview',
            'name' => 'Legacy Interview Template',
            'subject' => 'Interview invitation',
            'body' => '<p>Legacy body</p>',
            'enabled' => true,
        ]);

        $this->actingAs($administrator)
            ->get(route('admin.templates.index'))
            ->assertOk()
            ->assertDontSee('Legacy Interview Template');
    }

    public function test_reviewer_cannot_open_the_interview_management_page(): void
    {
        $reviewer = User::factory()->for(Role::for(RoleEnum::Reviewer))->create();

        $this->actingAs($reviewer)
            ->get(route('admin.interviews.index'))
            ->assertForbidden();
    }

    public function test_applicant_can_submit_a_reschedule_request_and_explain_why(): void
    {
        $application = $this->makeApplication('APP-2026-INT008', 'interview');
        $interview = $this->makeInterview($application, ['mode' => 'whatsapp']);

        $response = $this->from(route('interview-reschedule.show', ['token' => $interview->reschedule_token]))
            ->post(route('interview-reschedule.store', ['token' => $interview->reschedule_token]), [
                'reason' => 'I have a medical appointment at the scheduled time.',
            ]);

        $response->assertRedirect(route('interview-reschedule.show', ['token' => $interview->reschedule_token]))
            ->assertSessionHas('success', 'Your request was sent. The recruitment team will contact you to agree on a new time.');
        $this->assertDatabaseHas('interviews', [
            'id' => $interview->id,
            'status' => 'reschedule_requested',
            'reschedule_reason' => 'I have a medical appointment at the scheduled time.',
        ]);
        $this->actingAs(User::factory()->for(Role::for(RoleEnum::RecruitmentAdmin))->create())
            ->get(route('admin.interviews.index'))
            ->assertOk()
            ->assertSee('Reschedule requested')
            ->assertSee('I have a medical appointment at the scheduled time.');
    }

    public function test_interview_serialization_does_not_expose_reschedule_credentials(): void
    {
        $application = $this->makeApplication('APP-2026-INT009', 'interview');
        $interview = $this->makeInterview($application);

        $this->assertArrayNotHasKey('reschedule_token', $interview->toArray());
        $this->assertArrayNotHasKey('reschedule_token_hash', $interview->toArray());
    }

    private function makeApplication(string $reference, string $status): Application
    {
        $position = Position::create([
            'title' => 'Software Developer',
            'description' => 'Build software.',
            'status' => 'published',
        ]);
        $state = State::create(['name' => 'Ogun', 'slug' => 'ogun']);

        return Application::create([
            'reference' => $reference,
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
            'status' => $status,
            'submitted_at' => now(),
        ]);
    }

    /** @param array<string, mixed> $overrides */
    private function makeInterview(Application $application, array $overrides = []): Interview
    {
        $token = bin2hex(random_bytes(32));

        return Interview::create(array_merge([
            'application_id' => $application->id,
            'scheduled_at' => now(config('recruitment.timezone'))->addDay()->utc(),
            'duration_minutes' => 30,
            'mode' => 'google_meet',
            'location' => 'https://meet.google.com/abc-defg-hij',
            'email_subject' => 'Interview invitation — Ada Okafor',
            'email_message' => 'We are pleased to invite you to an interview. Please confirm that you received this message.',
            'status' => 'scheduled',
            'reschedule_token_hash' => hash('sha256', $token),
            'reschedule_token' => $token,
        ], $overrides));
    }

    /** @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function interviewInput(array $overrides = []): array
    {
        return array_merge([
            'scheduled_at' => now(config('recruitment.timezone'))->addDay()->setTime(10, 30)->format('Y-m-d\TH:i'),
            'duration_minutes' => 45,
            'mode' => 'google_meet',
            'location' => 'https://meet.google.com/abc-defg-hij',
            'instructions' => 'Please join five minutes early.',
            'email_subject' => 'Interview invitation for Ada Okafor',
            'email_message' => 'We are pleased to invite you to an interview. Please confirm that you received this message.',
            'action' => 'save',
        ], $overrides);
    }
}
