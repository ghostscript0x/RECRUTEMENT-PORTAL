<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_sign_in_page_renders_the_post_form(): void
    {
        $response = $this->get(route('admin.login'));

        $response->assertOk()
            ->assertViewIs('admin.login')
            ->assertSee('action="'.route('admin.login.attempt').'"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false)
            ->assertSee('name="_token"', false)
            ->assertSee('FINTEC Digital Skills Academy');
    }
}
