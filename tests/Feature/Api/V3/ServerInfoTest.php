<?php

namespace Tests\Feature\Api\V3;

use Tests\TestCase;

class ServerInfoTest extends TestCase
{
    public function test_it_reports_server_capabilities(): void
    {
        config([
            'spacemarket.currency' => '€',
            'spacemarket.decimal_separator' => ',',
            'spacemarket.global_credit_limit' => 2000,
        ]);

        $response = $this->getJson('/v3/info/');

        $response->assertOk()
            ->assertJsonPath('0.version', '3.0.0')
            ->assertJsonPath('0.currency', '€')
            ->assertJsonPath('0.currency_before', false)
            ->assertJsonPath('0.decimal_seperator', ',')
            ->assertJsonPath('0.energy', 'kcal')
            ->assertJsonPath('0.global_credit_limit', 2000)
            ->assertJsonPath('0.defaults.price', 150);
    }

    public function test_an_unset_credit_limit_is_reported_as_false(): void
    {
        config(['spacemarket.global_credit_limit' => null]);

        $this->getJson('/v3/info/')->assertOk()->assertJsonPath('0.global_credit_limit', false);
    }
}
