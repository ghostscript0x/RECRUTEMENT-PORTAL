<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class TrackingControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_tracking_page_renders_the_reference_and_email_form(): void
    {
        $response = $this->get(route('tracking.show'));

        $response->assertOk()
            ->assertViewIs('tracking.show')
            ->assertSee('action="'.route('tracking.track').'"', false)
            ->assertSee('name="reference"', false)
            ->assertSee('name="email"', false)
            ->assertSee('FINTEC Digital Skills Academy');
    }

    public function test_tracking_form_reports_missing_reference_and_email(): void
    {
        $response = $this->post(route('tracking.track'), []);

        $response->assertSessionHasErrors(['reference', 'email']);
    }
}
