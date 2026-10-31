<?php

namespace Tests\Feature\Admin;

use App\Enums\Role as RoleEnum;
use App\Models\Application;
use App\Models\Position;
use App\Models\Role;
use App\Models\State;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_report_totals_respect_position_and_date_filters(): void
    {
        $role = Role::for(RoleEnum::SuperAdmin);
        $administrator = User::factory()->for($role)->create();
        $state = State::create(['name' => 'Ogun', 'slug' => 'ogun']);
        $position = Position::create([
            'title' => 'Digital Skills Officer',
            'description' => 'Support digital skills training.',
            'status' => 'published',
        ]);
        $otherPosition = Position::create([
            'title' => 'Programme Coordinator',
            'description' => 'Coordinate programme delivery.',
            'status' => 'published',
        ]);

        $this->makeReportApplication($position, $state, 'FIN-REPORT-001', '2026-03-16 10:00:00');
        $this->makeReportApplication($otherPosition, $state, 'FIN-REPORT-002', '2026-03-16 11:00:00');
        $this->makeReportApplication($position, $state, 'FIN-REPORT-003', '2026-03-17 10:00:00');

        $response = $this->actingAs($administrator)->get(route('admin.reports.index', [
            'from' => '2026-03-16',
            'to' => '2026-03-16',
            'position_id' => $position->id,
        ]));

        $response->assertOk()
            ->assertViewIs('admin.reports.index')
            ->assertViewHas('total', 1)
            ->assertViewHas('byStatus', fn ($statuses) => $statuses->get('submitted') === 1)
            ->assertViewHas('byPosition', fn ($positions) => $positions->firstWhere('id', $position->id)->applications_count === 1);
    }

    public function test_report_rejects_an_end_date_before_the_start_date(): void
    {
        $administrator = User::factory()->for(Role::for(RoleEnum::SuperAdmin))->create();

        $response = $this->actingAs($administrator)->get(route('admin.reports.index', [
            'from' => '2026-03-17',
            'to' => '2026-03-16',
        ]));

        $response->assertRedirect()
            ->assertSessionHasErrors('to');
    }

    private function makeReportApplication(Position $position, State $state, string $reference, string $submittedAt): Application
    {
        return Application::create([
            'reference' => $reference,
            'position_id' => $position->id,
            'first_name' => 'Ada',
            'last_name' => 'Okafor',
            'email' => "{$reference}@example.test",
            'phone' => '08000000000',
            'date_of_birth' => '1998-02-01',
            'gender' => 'female',
            'residence_state_id' => $state->id,
            'residential_address' => 'Training Road',
            'highest_qualification' => 'bachelor',
            'status' => 'submitted',
            'submitted_at' => $submittedAt,
        ]);
    }
}
