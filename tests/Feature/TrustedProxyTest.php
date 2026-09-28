<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TrustedProxyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/_asset-url', fn () => asset('build/app.css'));
    }

    public function test_asset_urls_use_https_when_a_trusted_proxy_terminates_tls(): void
    {
        config(['trustedproxy.proxies' => '127.0.0.1']);

        $response = $this->withHeaders([
            'X-Forwarded-Proto' => 'https',
            'X-Forwarded-Host' => 'drinks.example.com',
            'X-Forwarded-Port' => '443',
        ])->get('/_asset-url');

        $response->assertOk()->assertContent('https://drinks.example.com/build/app.css');
    }

    public function test_forwarded_headers_are_ignored_from_untrusted_clients(): void
    {
        config(['trustedproxy.proxies' => null]);

        $response = $this->withHeaders([
            'X-Forwarded-Proto' => 'https',
            'X-Forwarded-Host' => 'evil.example.com',
        ])->get('/_asset-url');

        $response->assertOk()->assertDontSee('evil.example.com')->assertDontSee('https://');
    }
}
