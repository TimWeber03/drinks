<?php

namespace Tests\Feature\Api\V1;

use App\Enums\TransactionType;
use App\Models\Barcode;
use App\Models\Drink;
use App\Models\Drinker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deposits_euros_through_a_get_request(): void
    {
        $drinker = Drinker::factory()->create(['balance' => 1]);

        $this->getJson("/users/{$drinker->id}/deposit.json?amount=5")->assertNoContent();

        $this->assertSame('6.00', $drinker->fresh()->balance);
        $this->assertSame(TransactionType::Deposit, $drinker->transactions()->sole()->type);
    }

    public function test_a_deposit_needs_an_amount(): void
    {
        $drinker = Drinker::factory()->create();

        $this->getJson("/users/{$drinker->id}/deposit.json")->assertStatus(400);
    }

    public function test_it_subtracts_a_payment(): void
    {
        $drinker = Drinker::factory()->create(['balance' => 5]);

        $this->getJson("/users/{$drinker->id}/payment.json?amount=1.5")->assertNoContent();

        $this->assertSame('3.50', $drinker->fresh()->balance);
    }

    public function test_it_buys_a_drink(): void
    {
        $drinker = Drinker::factory()->create(['balance' => 5]);
        $drink = Drink::factory()->create(['price' => 1.5, 'stock_tracked' => true, 'stock' => 2]);

        $this->getJson("/users/{$drinker->id}/buy.json?drink={$drink->id}")->assertNoContent();

        $this->assertSame('3.50', $drinker->fresh()->balance);
        $this->assertSame(1, $drink->fresh()->stock);
        $this->assertSame(TransactionType::Purchase, $drinker->transactions()->sole()->type);
    }

    public function test_buying_an_out_of_stock_drink_fails(): void
    {
        $drinker = Drinker::factory()->create(['balance' => 5]);
        $drink = Drink::factory()->create(['price' => 1.5, 'stock_tracked' => true, 'stock' => 0]);

        $this->getJson("/users/{$drinker->id}/buy.json?drink={$drink->id}")->assertStatus(400);

        $this->assertSame('5.00', $drinker->fresh()->balance);
    }

    public function test_buying_an_unknown_drink_is_not_found(): void
    {
        $drinker = Drinker::factory()->create();

        $this->getJson("/users/{$drinker->id}/buy.json?drink=404")->assertNotFound();
    }

    public function test_it_buys_a_drink_by_barcode(): void
    {
        $drinker = Drinker::factory()->create(['balance' => 5]);
        $drink = Drink::factory()->create(['price' => 2]);
        Barcode::factory()->create(['barcode' => '4001234567890', 'linked' => $drink->id]);

        $this->postJson("/users/{$drinker->id}/buy_barcode.json", ['barcode' => '4001234567890'])
            ->assertNoContent();

        $this->assertSame('3.00', $drinker->fresh()->balance);
    }

    public function test_buying_by_an_unknown_barcode_is_not_found(): void
    {
        $drinker = Drinker::factory()->create(['balance' => 5]);

        $this->postJson("/users/{$drinker->id}/buy_barcode.json", ['barcode' => '0000000000000'])
            ->assertNotFound();
    }
}
