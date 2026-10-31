<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('FINTEC Digital Skills Academy')
            ->assertSee('src="'.asset('images/fintec-logo.png').'"', false)
            ->assertSee('rel="icon" type="image/png" href="'.asset('images/fintec-logo.png').'"', false)
            ->assertDontSee('Laravel');
    }
}
