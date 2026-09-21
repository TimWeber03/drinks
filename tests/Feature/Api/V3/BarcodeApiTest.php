<?php

namespace Tests\Feature\Api\V3;

use App\Enums\BarcodeType;
use App\Models\Barcode;
use App\Models\Drink;
use App\Models\Drinker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarcodeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_barcode(): void
    {
        $drink = Drink::factory()->create();

        $this->postJson('/v3/barcodes/', [
            'barcode' => '4001234567890',
            'type' => 'product',
            'linked' => $drink->id,
        ])
            ->assertOk()
            ->assertJsonPath('0.barcode', '4001234567890')
            ->assertJsonPath('0.type', 'product')
            ->assertJsonPath('0.linked', $drink->id);
    }

    public function test_it_rejects_a_duplicate_barcode(): void
    {
        $drink = Drink::factory()->create();
        Barcode::factory()->create(['barcode' => '4001234567890', 'linked' => $drink->id]);

        $this->postJson('/v3/barcodes/', [
            'barcode' => '4001234567890',
            'type' => 'product',
            'linked' => $drink->id,
        ])->assertStatus(409);
    }

    public function test_it_rejects_a_barcode_linked_to_nothing(): void
    {
        $this->postJson('/v3/barcodes/', [
            'barcode' => '4001234567890',
            'type' => 'user',
            'linked' => 404,
        ])->assertStatus(400);
    }

    public function test_it_rejects_an_unknown_type(): void
    {
        $this->postJson('/v3/barcodes/', [
            'barcode' => '4001234567890',
            'type' => 'spaceship',
            'linked' => 1,
        ])->assertStatus(400);
    }

    public function test_it_shows_a_barcode(): void
    {
        $barcode = Barcode::factory()->create();

        $this->getJson("/v3/barcodes/{$barcode->id}/")
            ->assertOk()
            ->assertJsonPath('0.id', $barcode->id);
    }

    public function test_it_relinks_a_barcode(): void
    {
        $barcode = Barcode::factory()->create();
        $drinker = Drinker::factory()->create();

        $this->patchJson("/v3/barcodes/{$barcode->id}/", ['type' => 'user', 'linked' => $drinker->id])
            ->assertOk()
            ->assertJsonPath('0.type', 'user')
            ->assertJsonPath('0.linked', $drinker->id);

        $this->assertSame(BarcodeType::User, $barcode->fresh()->type);
    }

    public function test_it_deletes_a_barcode(): void
    {
        $barcode = Barcode::factory()->create();

        $this->deleteJson("/v3/barcodes/{$barcode->id}/")->assertNoContent();

        $this->assertDatabaseMissing('barcodes', ['id' => $barcode->id]);
    }

    public function test_an_unknown_barcode_is_not_found(): void
    {
        $this->getJson('/v3/barcodes/404/')->assertNotFound();
    }
}
