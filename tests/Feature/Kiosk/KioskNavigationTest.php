<?php

namespace Tests\Feature\Kiosk;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KioskNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_kiosk_links_guests_straight_to_the_management_view(): void
    {
        $response = $this->get(route('kiosk.home'));

        $response->assertOk()
            ->assertSee('Management')
            ->assertSee(route('dashboard'))
            ->assertDontSee(route('login'));
    }
}
