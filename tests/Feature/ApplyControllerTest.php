<?php

namespace Tests\Feature;

use App\Jobs\SendApplicationEmail;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\EmailTemplate;
use App\Models\LocalGovernment;
use App\Models\Position;
use App\Models\State;
use App\Services\ApplicationWizard;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApplyControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_review_renders_location_names_from_the_application_draft(): void
    {
        $position = Position::create([
            'title' => 'Software Developer',
            'description' => 'Build software.',
            'status' => 'published',
        ]);
        $originState = State::create(['name' => 'Ogun', 'slug' => 'ogun']);
        $residenceState = State::create(['name' => 'Lagos', 'slug' => 'lagos']);
        $originLga = LocalGovernment::create([
            'state_id' => $originState->id,
            'name' => 'Abeokuta South',
        ]);
        $residenceLga = LocalGovernment::create([
            'state_id' => $residenceState->id,
            'name' => 'Ikeja',
        ]);

        $response = $this->withSession([
            ApplicationWizard::draftKey($position) => [
                'completed' => [1, 2, 3, 4, 5, 6, 7],
                'data' => [
                    'position_id' => $position->id,
                    'state_of_origin_id' => $originState->id,
                    'origin_lga_id' => $originLga->id,
                    'residence_state_id' => $residenceState->id,
                    'residence_lga_id' => $residenceLga->id,
                ],
            ],
        ])->get(route('apply.review', $position));

        $response->assertOk();
        $response->assertSee('Ogun / Abeokuta South');
        $response->assertSee('Lagos / Ikeja');
    }

    public function test_valid_personal_information_advances_to_the_next_step(): void
    {
        $position = Position::create([
            'title' => 'Software Developer',
            'description' => 'Build software.',
            'status' => 'published',
        ]);
        $state = State::create(['name' => 'Ogun', 'slug' => 'ogun']);
        $localGovernment = LocalGovernment::create([
            'state_id' => $state->id,
            'name' => 'Abeokuta South',
        ]);

        $response = $this
            ->withSession([
                ApplicationWizard::draftKey($position) => [
                    'completed' => [1],
                    'data' => ['position_id' => $position->id],
                ],
            ])
            ->post("/apply/{$position->id}/step/2", [
                'first_name' => 'Ada',
                'last_name' => 'Okafor',
                'email' => 'ada@example.test',
                'phone' => '08000000000',
                'date_of_birth' => '1998-02-01',
                'gender' => 'female',
                'residence_state_id' => $state->id,
                'residence_lga_id' => $localGovernment->id,
                'residential_address' => 'Training Road',
            ]);

        $response->assertRedirect(route('apply.step', [$position, 3]));
        $response->assertSessionHas(
            ApplicationWizard::draftKey($position),
            fn (array $draft): bool => in_array(2, $draft['completed'], true)
                && $draft['data']['residence_lga_id'] === $localGovernment->id,
        );
    }

    public function test_personal_information_rejects_an_lga_outside_the_selected_state(): void
    {
        $position = Position::create([
            'title' => 'Software Developer',
            'description' => 'Build software.',
            'status' => 'published',
        ]);
        $state = State::create(['name' => 'Ogun', 'slug' => 'ogun']);
        $otherState = State::create(['name' => 'Lagos', 'slug' => 'lagos']);
        $localGovernment = LocalGovernment::create([
            'state_id' => $otherState->id,
            'name' => 'Ikeja',
        ]);

        $response = $this
            ->withSession([
                ApplicationWizard::draftKey($position) => [
                    'completed' => [1],
                    'data' => ['position_id' => $position->id],
                ],
            ])
            ->from("/apply/{$position->id}/step/2")
            ->post("/apply/{$position->id}/step/2", [
                'first_name' => 'Ada',
                'last_name' => 'Okafor',
                'email' => 'ada@example.test',
                'phone' => '08000000000',
                'date_of_birth' => '1998-02-01',
                'gender' => 'female',
                'residence_state_id' => $state->id,
                'residence_lga_id' => $localGovernment->id,
                'residential_address' => 'Training Road',
            ]);

        $response->assertRedirect("/apply/{$position->id}/step/2");
        $response->assertSessionHasErrors(['residence_lga_id']);
    }

    public function test_leadership_step_advances_when_applicant_selects_no_experience(): void
    {
        $position = Position::create([
            'title' => 'Software Developer',
            'description' => 'Build software.',
            'status' => 'published',
        ]);

        $response = $this->withSession([
            ApplicationWizard::draftKey($position) => [
                'completed' => [1, 2, 3, 4],
                'data' => [
                    'position_id' => $position->id,
                    'leadership_organization' => 'Old organization',
                    'leadership_position' => 'Old role',
                    'leadership_description' => 'Old experience',
                ],
            ],
        ])->post(route('apply.step', [$position, 5]), [
            'has_leadership' => 'no',
        ]);

        $response->assertRedirect(route('apply.step', [$position, 6]));
        $response->assertSessionHas(
            ApplicationWizard::draftKey($position),
            fn (array $draft): bool => $draft['data']['has_leadership'] === 'no'
                && $draft['data']['leadership_organization'] === null
                && $draft['data']['leadership_position'] === null
                && $draft['data']['leadership_duration'] === null
                && $draft['data']['leadership_description'] === null
                && in_array(5, $draft['completed'], true),
        );
    }

    public function test_leadership_details_are_required_when_applicant_selects_yes(): void
    {
        $position = Position::create([
            'title' => 'Software Developer',
            'description' => 'Build software.',
            'status' => 'published',
        ]);

        $response = $this->withSession([
            ApplicationWizard::draftKey($position) => [
                'completed' => [1, 2, 3, 4],
                'data' => ['position_id' => $position->id],
            ],
        ])->from(route('apply.step', [$position, 5]))
            ->post(route('apply.step', [$position, 5]), [
                'has_leadership' => 'yes',
            ]);

        $response->assertRedirect(route('apply.step', [$position, 5]));
        $response->assertSessionHasErrors([
            'leadership_organization',
            'leadership_position',
            'leadership_description',
        ]);
    }

    public function test_leadership_description_is_not_required_while_its_section_is_hidden(): void
    {
        $position = Position::create([
            'title' => 'Software Developer',
            'description' => 'Build software.',
            'status' => 'published',
        ]);

        $response = $this->withSession([
            ApplicationWizard::draftKey($position) => [
                'completed' => [1, 2, 3, 4],
                'data' => ['position_id' => $position->id],
            ],
        ])->get(route('apply.step', [$position, 5]));

        $response->assertOk();
        $response->assertSee('data-leadership-fields', false);
        $this->assertDoesNotMatchRegularExpression(
            '/<textarea\b[^>]*\bname="leadership_description"[^>]*\brequired\b/i',
            $response->getContent(),
        );
    }

    public function test_other_highest_qualification_requires_and_saves_the_custom_value(): void
    {
        $position = Position::create([
            'title' => 'Software Developer',
            'description' => 'Build software.',
            'status' => 'published',
        ]);
        $draftKey = ApplicationWizard::draftKey($position);

        $invalidResponse = $this->withSession([
            $draftKey => [
                'completed' => [1, 2],
                'data' => ['position_id' => $position->id],
            ],
        ])->from(route('apply.step', [$position, 3]))
            ->post(route('apply.step', [$position, 3]), [
                'highest_qualification' => 'Other',
            ]);

        $invalidResponse->assertRedirect(route('apply.step', [$position, 3]));
        $invalidResponse->assertSessionHasErrors(['highest_qualification_other']);

        $response = $this->withSession([
            $draftKey => [
                'completed' => [1, 2],
                'data' => ['position_id' => $position->id],
            ],
        ])->post(route('apply.step', [$position, 3]), [
            'highest_qualification' => 'Other',
            'highest_qualification_other' => 'MEng',
        ]);

        $response->assertRedirect(route('apply.step', [$position, 4]));
        $response->assertSessionHas(
            $draftKey,
            fn (array $draft): bool => $draft['data']['highest_qualification'] === 'MEng'
                && ! array_key_exists('highest_qualification_other', $draft['data'])
                && in_array(3, $draft['completed'], true),
        );
    }

    public function test_custom_highest_qualification_is_prefilled_when_resuming_the_education_step(): void
    {
        $position = Position::create([
            'title' => 'Software Developer',
            'description' => 'Build software.',
            'status' => 'published',
        ]);

        $response = $this->withSession([
            ApplicationWizard::draftKey($position) => [
                'completed' => [1, 2],
                'data' => [
                    'position_id' => $position->id,
                    'highest_qualification' => 'MEng',
                ],
            ],
        ])->get(route('apply.step', [$position, 3]));

        $response->assertOk();
        $response->assertSee('<option value="Other" selected>Other</option>', false);
        $response->assertSee('name="highest_qualification_other"', false);
        $response->assertSee('value="MEng"', false);
    }

    public function test_submission_queues_notification_and_moves_attachments_into_private_storage(): void
    {
        Storage::fake('local');

        $position = Position::create([
            'title' => 'Software Developer',
            'description' => 'Build software.',
            'status' => 'published',
        ]);
        $state = State::create(['name' => 'Ogun', 'slug' => 'ogun']);
        $draftId = 'draft-attachment-test';
        $stagedPath = "applications/pending/{$draftId}/cv.pdf";
        Storage::disk('local')->put($stagedPath, "%PDF-1.4\nTest attachment");
        EmailTemplate::create([
            'key' => 'submitted',
            'name' => 'Application received',
            'subject' => 'Application received',
            'body' => '<p>Thank you, {{applicant_name}}.</p>',
            'enabled' => true,
        ]);
        Queue::fake();

        $response = $this->withSession([
            ApplicationWizard::draftKey($position) => [
                'completed' => [1, 2, 3, 4, 5, 6, 7],
                'draft_id' => $draftId,
                'files' => ['cv' => $stagedPath],
                'data' => [
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
                    'has_leadership' => 'no',
                    'about' => 'I enjoy building useful software.',
                    'skills' => ['PHP'],
                ],
            ],
        ])->post(route('apply.submit', $position), ['confirm' => 'yes']);

        $response->assertRedirect(route('apply.success'));
        $this->assertDatabaseHas('applications', [
            'position_id' => $position->id,
            'email' => 'ada@example.test',
        ]);
        $this->assertDatabaseHas('email_logs', [
            'to_email' => 'ada@example.test',
            'status' => 'queued',
        ]);
        Queue::assertPushed(SendApplicationEmail::class);

        $application = Application::where('email', 'ada@example.test')->firstOrFail();
        $document = ApplicationDocument::where('application_id', $application->id)->firstOrFail();

        $this->assertSame('cv', $document->type);
        $this->assertSame('cv.pdf', $document->original_name);
        $this->assertSame('pdf', pathinfo($document->path, PATHINFO_EXTENSION));
        Storage::disk('local')->assertExists($document->path);
        Storage::disk('local')->assertMissing($stagedPath);
    }

    public function test_submission_is_rejected_if_the_position_closes_before_final_submit(): void
    {
        $position = Position::create([
            'title' => 'Software Developer',
            'description' => 'Build software.',
            'status' => 'closed',
        ]);
        $state = State::create(['name' => 'Ogun', 'slug' => 'ogun']);

        $response = $this->withSession([
            ApplicationWizard::draftKey($position) => $this->completedDraft($position, $state, 'closed-position@example.test'),
        ])->from(route('apply.review', $position))
            ->post(route('apply.submit', $position), ['confirm' => 'yes']);

        $response->assertRedirect(route('apply.review', $position))
            ->assertSessionHasErrors(['position']);
        $this->assertDatabaseCount('applications', 0);
    }

    public function test_duplicate_application_is_rejected_without_creating_another_record(): void
    {
        $position = Position::create([
            'title' => 'Software Developer',
            'description' => 'Build software.',
            'status' => 'published',
        ]);
        $state = State::create(['name' => 'Ogun', 'slug' => 'ogun']);
        Application::create([
            'reference' => 'APP-2026-DUP001',
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

        $response = $this->withSession([
            ApplicationWizard::draftKey($position) => $this->completedDraft($position, $state, 'ada@example.test'),
        ])->from(route('apply.review', $position))
            ->post(route('apply.submit', $position), ['confirm' => 'yes']);

        $response->assertRedirect(route('apply.review', $position))
            ->assertSessionHasErrors(['email']);
        $this->assertDatabaseCount('applications', 1);
    }

    /** @return array<string, mixed> */
    private function completedDraft(Position $position, State $state, string $email): array
    {
        return [
            'completed' => [1, 2, 3, 4, 5, 6, 7],
            'draft_id' => 'draft-'.str_replace(['@', '.'], '-', $email),
            'files' => [],
            'data' => [
                'position_id' => $position->id,
                'first_name' => 'Ada',
                'last_name' => 'Okafor',
                'email' => $email,
                'phone' => '08000000000',
                'date_of_birth' => '1998-02-01',
                'gender' => 'female',
                'residence_state_id' => $state->id,
                'residential_address' => 'Training Road',
                'highest_qualification' => 'BSc',
                'has_leadership' => 'no',
                'about' => 'I enjoy building useful software.',
                'skills' => ['PHP'],
            ],
        ];
    }
}
