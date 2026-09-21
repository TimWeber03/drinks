<?php

namespace Tests\Feature\Api\V3;

use App\Models\Drink;
use App\Models\Image;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_products_with_prices_in_cents(): void
    {
        $drink = Drink::factory()->create(['name' => 'Mate', 'price' => 1.5, 'caffeine' => 20]);

        $response = $this->getJson('/v3/products/');

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $drink->id)
            ->assertJsonPath('0.name', 'Mate')
            ->assertJsonPath('0.price', 150)
            ->assertJsonPath('0.caffeine', 20)
            ->assertJsonPath('0.active', true)
            ->assertJsonPath('0.image', null);
    }

    public function test_it_creates_a_product(): void
    {
        $image = Image::factory()->create();

        $response = $this->postJson('/v3/products/', [
            'name' => 'Fritz-Kola',
            'price' => 220,
            'caffeine' => 25,
            'alcohol' => 0,
            'energy' => 42,
            'sugar' => 108,
            'image' => $image->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('name', 'Fritz-Kola')
            ->assertJsonPath('price', 220)
            ->assertJsonPath('image', $image->id);

        $this->assertSame('2.20', Drink::firstOrFail()->price);
    }

    public function test_it_falls_back_to_the_configured_default_price(): void
    {
        config(['spacemarket.defaults.price' => 150]);

        $this->postJson('/v3/products/', ['name' => 'Water'])
            ->assertCreated()
            ->assertJsonPath('price', 150);
    }

    public function test_it_rejects_a_product_without_a_name(): void
    {
        $this->postJson('/v3/products/', ['price' => 100])->assertStatus(400);
    }

    public function test_it_rejects_a_duplicate_product_name(): void
    {
        Drink::factory()->create(['name' => 'Mate']);

        $this->postJson('/v3/products/', ['name' => 'Mate'])->assertStatus(409);
    }

    public function test_it_shows_a_single_product(): void
    {
        $drink = Drink::factory()->create(['price' => 0.8]);

        $this->getJson("/v3/products/{$drink->id}/")
            ->assertOk()
            ->assertJsonPath('id', $drink->id)
            ->assertJsonPath('price', 80);
    }

    public function test_it_returns_404_for_an_unknown_product(): void
    {
        $this->getJson('/v3/products/404/')->assertNotFound();
    }

    public function test_it_edits_a_product(): void
    {
        $drink = Drink::factory()->create(['name' => 'Mate', 'price' => 1.5]);

        $this->patchJson("/v3/products/{$drink->id}/", ['price' => 175, 'active' => false])
            ->assertOk()
            ->assertJsonPath('price', 175)
            ->assertJsonPath('active', false);

        $this->assertSame('1.75', $drink->fresh()->price);
        $this->assertFalse($drink->fresh()->active);
    }

    public function test_it_deletes_a_product(): void
    {
        $drink = Drink::factory()->create();

        $this->deleteJson("/v3/products/{$drink->id}/")->assertOk();

        $this->assertDatabaseMissing('drinks', ['id' => $drink->id]);
    }
}
