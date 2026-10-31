<?php

namespace Tests\Feature;

use App\Enums\Role as RoleEnum;
use App\Models\Application;
use App\Models\Position;
use App\Models\Role;
use App\Models\State;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ApplicationExportTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_csv_export_escapes_applicant_data_that_could_be_a_spreadsheet_formula(): void
    {
        $administrator = User::factory()->for(Role::for(RoleEnum::RecruitmentAdmin))->create();
        $position = Position::create([
            'title' => 'Digital Skills Officer',
            'description' => 'Support training delivery.',
            'status' => 'published',
        ]);
        $state = State::create(['name' => 'Ogun', 'slug' => 'ogun']);
        Application::create([
            'reference' => 'APP-2026-EXPORT1',
            'position_id' => $position->id,
            'first_name' => '=HYPERLINK("https://example.test","open")',
            'last_name' => 'Applicant',
            'email' => 'applicant@example.test',
            'phone' => '+2348000000000',
            'date_of_birth' => '1998-02-01',
            'gender' => 'female',
            'residence_state_id' => $state->id,
            'residential_address' => 'Training Road',
            'highest_qualification' => 'BSc',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($administrator)
            ->get(route('admin.applications.export', ['format' => 'csv']));

        $response->assertOk();
        $this->assertStringContainsString(
            "'=HYPERLINK",
            $response->streamedContent(),
        );
        $this->assertStringContainsString("'+2348000000000", $response->streamedContent());
    }
}
