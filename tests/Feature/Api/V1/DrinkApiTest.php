<?php

namespace Tests\Feature\Api\V1;

use App\Models\Drink;
use App\Models\Image;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DrinkApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_drinks_with_decimal_prices(): void
    {
        $image = Image::factory()->create(['file_name' => 'mate.png', 'mime_type' => 'image/png', 'size' => 4242]);
        $drink = Drink::factory()->create([
            'name' => 'Mate',
            'price' => 1.5,
            'bottle_size' => 0.5,
            'caffeine' => 20,
            'image_id' => $image->id,
        ]);

        $this->getJson('/drinks.json')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $drink->id)
            ->assertJsonPath('0.name', 'Mate')
            ->assertJsonPath('0.price', '1.50')
            ->assertJsonPath('0.donation_recommendation', '1.50')
            ->assertJsonPath('0.bottle_size', '0.50')
            ->assertJsonPath('0.caffeine', 20)
            ->assertJsonPath('0.active', true)
            ->assertJsonPath('0.logo_file_name', 'mate.png')
            ->assertJsonPath('0.logo_content_type', 'image/png')
            ->assertJsonPath('0.logo_file_size', 4242);
    }

    public function test_it_returns_defaults_for_a_new_drink(): void
    {
        config(['spacemarket.defaults.price' => 150]);

        $this->getJson('/drinks/new.json')
            ->assertOk()
            ->assertJsonPath('id', null)
            ->assertJsonPath('price', '1.50')
            ->assertJsonPath('active', true);
    }

    public function test_it_creates_a_drink(): void
    {
        $this->postJson('/drinks.json', ['name' => 'Cola', 'price' => '1.20', 'caffeine' => 10])
            ->assertCreated()
            ->assertJsonPath('name', 'Cola')
            ->assertJsonPath('price', '1.20');

        $this->assertSame('1.20', Drink::firstOrFail()->price);
    }

    public function test_it_accepts_the_deprecated_donation_recommendation(): void
    {
        $this->postJson('/drinks.json', ['name' => 'Cola', 'donation_recommendation' => '0.80'])
            ->assertCreated()
            ->assertJsonPath('price', '0.80');
    }

    public function test_it_uploads_a_logo(): void
    {
        Storage::fake('public');

        $response = $this->post('/drinks.json', [
            'name' => 'Mate',
            'logo' => UploadedFile::fake()->image('mate.png'),
        ], ['Accept' => 'application/json']);

        $response->assertCreated()->assertJsonPath('logo_file_name', 'mate.png');

        $drink = Drink::firstOrFail();
        $this->assertNotNull($drink->image);
        Storage::disk('public')->assertExists($drink->image->path);
    }

    public function test_it_rejects_a_drink_without_a_name(): void
    {
        $this->postJson('/drinks.json', ['price' => '1.00'])->assertStatus(400);
    }

    public function test_it_shows_a_drink(): void
    {
        $drink = Drink::factory()->create(['price' => 2]);

        $this->getJson("/drinks/{$drink->id}.json")
            ->assertOk()
            ->assertJsonPath('price', '2.00');
    }

    public function test_it_edits_a_drink_without_a_response_body(): void
    {
        $drink = Drink::factory()->create(['name' => 'Mate', 'price' => 1]);

        $this->patchJson("/drinks/{$drink->id}.json", ['price' => '1.30', 'active' => false])
            ->assertNoContent();

        $this->assertSame('1.30', $drink->fresh()->price);
        $this->assertFalse($drink->fresh()->active);
    }

    public function test_it_deletes_a_drink(): void
    {
        $drink = Drink::factory()->create();

        $this->deleteJson("/drinks/{$drink->id}.json")->assertNoContent();

        $this->assertDatabaseMissing('drinks', ['id' => $drink->id]);
    }

    public function test_an_unknown_drink_is_not_found(): void
    {
        $this->getJson('/drinks/404.json')->assertNotFound();
    }
}
