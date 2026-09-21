<?php

namespace Tests\Feature\Api\V1;

use App\Models\Barcode;
use App\Models\Drink;
use App\Models\Drinker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarcodeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_drink_barcodes_only(): void
    {
        $drink = Drink::factory()->create();
        $drinker = Drinker::factory()->create();
        Barcode::factory()->create(['barcode' => '4001234567890', 'linked' => $drink->id]);
        Barcode::factory()->forUser($drinker->id)->create(['barcode' => '4009999999999']);

        $this->getJson('/barcodes.json')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', '4001234567890')
            ->assertJsonPath('0.drink', $drink->id);
    }

    public function test_it_returns_defaults_for_a_new_barcode(): void
    {
        $this->getJson('/barcodes/new.json')
            ->assertOk()
            ->assertJsonPath('drink', null);
    }

    public function test_it_creates_a_barcode(): void
    {
        $drink = Drink::factory()->create();

        $this->postJson('/barcodes.json', ['id' => '4001234567890', 'drink' => $drink->id])
            ->assertCreated()
            ->assertJsonPath('id', '4001234567890')
            ->assertJsonPath('drink', $drink->id);
    }

    public function test_it_rejects_a_duplicate_barcode(): void
    {
        $drink = Drink::factory()->create();
        Barcode::factory()->create(['barcode' => '4001234567890', 'linked' => $drink->id]);

        $this->postJson('/barcodes.json', ['id' => '4001234567890', 'drink' => $drink->id])
            ->assertStatus(409);
    }

    public function test_it_rejects_a_barcode_for_an_unknown_drink(): void
    {
        $this->postJson('/barcodes.json', ['id' => '4001234567890', 'drink' => 404])
            ->assertStatus(400);
    }

    public function test_it_deletes_a_barcode_by_its_value(): void
    {
        $drink = Drink::factory()->create();
        Barcode::factory()->create(['barcode' => '4001234567890', 'linked' => $drink->id]);

        $this->deleteJson('/barcodes/4001234567890.json')->assertNoContent();

        $this->assertDatabaseMissing('barcodes', ['barcode' => '4001234567890']);
    }

    public function test_deleting_an_unknown_barcode_is_not_found(): void
    {
        $this->deleteJson('/barcodes/0000000000000.json')->assertNotFound();
    }
}
