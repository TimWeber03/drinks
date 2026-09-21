<?php

namespace Tests\Feature\Api\V3;

use App\Models\Denomination;
use App\Models\Image;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DenominationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_denominations_by_amount(): void
    {
        Denomination::factory()->create(['amount' => 500]);
        Denomination::factory()->create(['amount' => 100]);

        $this->getJson('/v3/denominations/')
            ->assertOk()
            ->assertJsonCount(2)
            ->assertJsonPath('0.amount', 100)
            ->assertJsonPath('1.amount', 500);
    }

    public function test_it_creates_a_denomination(): void
    {
        $image = Image::factory()->create();

        $this->postJson('/v3/denominations/', ['amount' => 200, 'image' => $image->id])
            ->assertOk()
            ->assertJsonPath('0.amount', 200)
            ->assertJsonPath('0.image', $image->id);
    }

    public function test_it_rejects_a_duplicate_amount(): void
    {
        Denomination::factory()->create(['amount' => 200]);

        $this->postJson('/v3/denominations/', ['amount' => 200])->assertStatus(409);
    }

    public function test_it_shows_a_denomination(): void
    {
        $denomination = Denomination::factory()->create(['amount' => 1000]);

        $this->getJson("/v3/denominations/{$denomination->id}/")
            ->assertOk()
            ->assertJsonPath('0.amount', 1000);
    }

    public function test_it_updates_a_denomination(): void
    {
        $denomination = Denomination::factory()->create(['amount' => 1000]);

        $this->patchJson("/v3/denominations/{$denomination->id}/", ['amount' => 2000])
            ->assertOk()
            ->assertJsonPath('0.amount', 2000);
    }

    public function test_it_deletes_a_denomination(): void
    {
        $denomination = Denomination::factory()->create();

        $this->deleteJson("/v3/denominations/{$denomination->id}/")->assertNoContent();

        $this->assertDatabaseMissing('denominations', ['id' => $denomination->id]);
    }
}
