<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_open_the_dashboard(): void
    {
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSeeVolt('layout.navigation');
    }

    public function test_guests_are_offered_a_login_link(): void
    {
        $this->get(route('dashboard'))->assertSee(route('login'));
    }
}
