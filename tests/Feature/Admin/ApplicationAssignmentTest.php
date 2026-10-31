<?php

namespace Tests\Feature\Admin;

use App\Enums\Role as RoleEnum;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\Position;
use App\Models\Role;
use App\Models\State;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApplicationAssignmentTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_recruitment_administrator_can_assign_an_application_to_a_reviewer(): void
    {
        $administrator = User::factory()
            ->for(Role::for(RoleEnum::RecruitmentAdmin))
            ->create();
        $reviewer = User::factory()
            ->for(Role::for(RoleEnum::Reviewer))
            ->create();
        $application = $this->makeApplication('APP-2026-ASSIGN1');

        $response = $this->actingAs($administrator)
            ->from(route('admin.applications.show', $application))
            ->put(route('admin.applications.reviewer', $application), [
                'reviewer_id' => $reviewer->id,
            ]);

        $response->assertRedirect(route('admin.applications.show', $application))
            ->assertSessionHas('success', 'Application assigned to reviewer.');
        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'assigned_reviewer_id' => $reviewer->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'application.reviewer_assigned',
            'subject_id' => $application->id,
            'user_id' => $administrator->id,
        ]);
    }

    public function test_reviewer_only_sees_and_opens_assigned_applications(): void
    {
        $reviewer = User::factory()
            ->for(Role::for(RoleEnum::Reviewer))
            ->create();
        $assignedApplication = $this->makeApplication('APP-2026-OWNED1', $reviewer);
        $unassignedApplication = $this->makeApplication('APP-2026-OTHER1');

        $this->actingAs($reviewer)
            ->get(route('admin.applications.index'))
            ->assertOk()
            ->assertSee($assignedApplication->reference)
            ->assertDontSee($unassignedApplication->reference);

        $this->get(route('admin.applications.show', $assignedApplication))
            ->assertOk();

        $this->get(route('admin.applications.show', $unassignedApplication))
            ->assertNotFound();
    }

    public function test_uploaded_passport_photo_appears_in_the_application_header(): void
    {
        Storage::fake('local');
        $administrator = User::factory()
            ->for(Role::for(RoleEnum::RecruitmentAdmin))
            ->create();
        $application = $this->makeApplication('APP-2026-PHOTO1');
        $passport = ApplicationDocument::create([
            'application_id' => $application->id,
            'type' => 'passport',
            'path' => 'applications/private/passport.jpg',
            'original_name' => 'passport.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 10,
        ]);
        Storage::disk('local')->put($passport->path, 'photo-bytes');

        $response = $this->actingAs($administrator)
            ->get(route('admin.applications.show', $application));

        $response->assertOk()
            ->assertSee('<img src="'.route('admin.applications.documents', [$application, $passport]).'"', false);
    }

    public function test_application_header_keeps_initials_when_no_passport_photo_is_uploaded(): void
    {
        $administrator = User::factory()
            ->for(Role::for(RoleEnum::RecruitmentAdmin))
            ->create();
        $application = $this->makeApplication('APP-2026-PHOTO2');

        $response = $this->actingAs($administrator)
            ->get(route('admin.applications.show', $application));

        $response->assertOk()
            ->assertSee('>AO</span>', false)
            ->assertDontSee('/documents/', false);
    }

    public function test_reviewer_cannot_view_a_document_from_an_unassigned_application(): void
    {
        Storage::fake('local');
        $reviewer = User::factory()
            ->for(Role::for(RoleEnum::Reviewer))
            ->create();
        $application = $this->makeApplication('APP-2026-DOCOWN');
        $document = ApplicationDocument::create([
            'application_id' => $application->id,
            'type' => 'cv',
            'path' => 'applications/private/cv.pdf',
            'original_name' => 'cv.pdf',
            'mime_type' => 'application/pdf',
            'size' => 10,
        ]);
        Storage::disk('local')->put($document->path, '%PDF-test');

        $this->actingAs($reviewer)
            ->get(route('admin.applications.documents', [$application, $document]))
            ->assertNotFound();
    }

    public function test_authorized_administrator_can_view_an_uploaded_pdf_inline(): void
    {
        Storage::fake('local');
        $administrator = User::factory()
            ->for(Role::for(RoleEnum::RecruitmentAdmin))
            ->create();
        $application = $this->makeApplication('APP-2026-DOCVIEW');
        $document = ApplicationDocument::create([
            'application_id' => $application->id,
            'type' => 'cv',
            'path' => 'applications/private/cv.pdf',
            'original_name' => 'cv.pdf',
            'mime_type' => 'application/pdf',
            'size' => 10,
        ]);
        Storage::disk('local')->put($document->path, '%PDF-test');

        $response = $this->actingAs($administrator)
            ->get(route('admin.applications.documents', [$application, $document]));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringStartsWith('inline;', $response->headers->get('Content-Disposition'));
    }

    public function test_unsupported_document_mime_type_cannot_be_rendered_inline(): void
    {
        Storage::fake('local');
        $administrator = User::factory()
            ->for(Role::for(RoleEnum::RecruitmentAdmin))
            ->create();
        $application = $this->makeApplication('APP-2026-DOCMIME');
        $document = ApplicationDocument::create([
            'application_id' => $application->id,
            'type' => 'other',
            'path' => 'applications/private/document.html',
            'original_name' => 'document.html',
            'mime_type' => 'text/html',
            'size' => 17,
        ]);
        Storage::disk('local')->put($document->path, '<script>alert(1)</script>');

        $this->actingAs($administrator)
            ->get(route('admin.applications.documents', [$application, $document]))
            ->assertNotFound();
    }

    public function test_reviewer_dashboard_and_reports_count_only_assigned_applications(): void
    {
        $reviewer = User::factory()
            ->for(Role::for(RoleEnum::Reviewer))
            ->create();
        $this->makeApplication('APP-2026-DASH01', $reviewer);
        $this->makeApplication('APP-2026-DASH02');

        $this->actingAs($reviewer)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('stats', fn (array $stats): bool => $stats['total'] === 1)
            ->assertSee('APP-2026-DASH01')
            ->assertDontSee('APP-2026-DASH02');

        $this->get(route('admin.reports.index'))
            ->assertOk()
            ->assertViewHas('total', 1);

    }

    public function test_recruitment_administrator_cannot_assign_a_non_reviewer(): void
    {
        $administrator = User::factory()
            ->for(Role::for(RoleEnum::RecruitmentAdmin))
            ->create();
        $nonReviewer = User::factory()
            ->for(Role::for(RoleEnum::RecruitmentAdmin))
            ->create();
        $application = $this->makeApplication('APP-2026-BADASS');

        $response = $this->actingAs($administrator)
            ->from(route('admin.applications.show', $application))
            ->put(route('admin.applications.reviewer', $application), [
                'reviewer_id' => $nonReviewer->id,
            ]);

        $response->assertRedirect(route('admin.applications.show', $application))
            ->assertSessionHasErrors(['reviewer_id']);
        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'assigned_reviewer_id' => null,
        ]);
    }

    private function makeApplication(string $reference, ?User $reviewer = null): Application
    {
        $position = Position::create([
            'title' => 'Software Developer',
            'description' => 'Build software.',
            'status' => 'published',
        ]);
        $state = State::firstOrCreate(
            ['slug' => 'ogun'],
            ['name' => 'Ogun'],
        );

        return Application::create([
            'reference' => $reference,
            'position_id' => $position->id,
            'assigned_reviewer_id' => $reviewer?->id,
            'first_name' => 'Ada',
            'last_name' => 'Okafor',
            'email' => 'ada-'.$reference.'@example.test',
            'phone' => '08000000000',
            'date_of_birth' => '1998-02-01',
            'gender' => 'female',
            'residence_state_id' => $state->id,
            'residential_address' => 'Training Road',
            'highest_qualification' => 'BSc',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
    }
}
