<?php

namespace Tests\Feature\Api\V3;

use App\Enums\TransactionType;
use App\Models\Barcode;
use App\Models\Drink;
use App\Models\Drinker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deposits_a_bare_integer_body(): void
    {
        $drinker = Drinker::factory()->create(['balance' => 1]);

        $this->call('POST', "/v3/users/{$drinker->id}/deposit/", [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], '500')->assertNoContent();

        $this->assertSame('6.00', $drinker->fresh()->balance);
        $this->assertSame(TransactionType::Deposit, $drinker->transactions()->sole()->type);
    }

    public function test_it_deposits_an_object_body(): void
    {
        $drinker = Drinker::factory()->create(['balance' => 0]);

        $this->postJson("/v3/users/{$drinker->id}/deposit/", ['amount' => 250])->assertNoContent();

        $this->assertSame('2.50', $drinker->fresh()->balance);
    }

    public function test_it_rejects_a_deposit_without_an_amount(): void
    {
        $drinker = Drinker::factory()->create();

        $this->postJson("/v3/users/{$drinker->id}/deposit/", [])->assertStatus(400);
    }

    public function test_it_spends_from_the_balance(): void
    {
        $drinker = Drinker::factory()->create(['balance' => 5]);

        $this->postJson("/v3/users/{$drinker->id}/spend/", ['amount' => 150])->assertNoContent();

        $this->assertSame('3.50', $drinker->fresh()->balance);
    }

    public function test_it_buys_a_product(): void
    {
        $drinker = Drinker::factory()->create(['balance' => 5]);
        $drink = Drink::factory()->create(['price' => 1.5, 'stock_tracked' => true, 'stock' => 2]);

        $this->postJson("/v3/users/{$drinker->id}/buy/", ['product' => $drink->id])->assertNoContent();

        $this->assertSame('3.50', $drinker->fresh()->balance);
        $this->assertSame(1, $drink->fresh()->stock);
        $this->assertSame(TransactionType::Purchase, $drinker->transactions()->sole()->type);
    }

    public function test_buying_an_out_of_stock_product_fails(): void
    {
        $drinker = Drinker::factory()->create(['balance' => 5]);
        $drink = Drink::factory()->create(['price' => 1.5, 'stock_tracked' => true, 'stock' => 0]);

        $this->postJson("/v3/users/{$drinker->id}/buy/", ['product' => $drink->id])->assertStatus(400);

        $this->assertSame('5.00', $drinker->fresh()->balance);
    }

    public function test_buying_an_unknown_product_is_not_found(): void
    {
        $drinker = Drinker::factory()->create();

        $this->postJson("/v3/users/{$drinker->id}/buy/", ['product' => 404])->assertNotFound();
    }

    public function test_it_buys_a_product_by_barcode(): void
    {
        $drinker = Drinker::factory()->create(['balance' => 5]);
        $drink = Drink::factory()->create(['price' => 2]);
        Barcode::factory()->create(['barcode' => '4001234567890', 'linked' => $drink->id]);

        $this->postJson("/v3/users/{$drinker->id}/buy/barcode/", ['barcode' => '4001234567890'])
            ->assertNoContent();

        $this->assertSame('3.00', $drinker->fresh()->balance);
    }

    public function test_buying_by_a_user_barcode_fails(): void
    {
        $drinker = Drinker::factory()->create(['balance' => 5]);
        Barcode::factory()->forUser($drinker->id)->create(['barcode' => '4001234567890']);

        $this->postJson("/v3/users/{$drinker->id}/buy/barcode/", ['barcode' => '4001234567890'])
            ->assertStatus(400);
    }

    public function test_it_transfers_funds_between_users(): void
    {
        $sender = Drinker::factory()->create(['balance' => 10]);
        $receiver = Drinker::factory()->create(['balance' => 0]);

        $this->postJson("/v3/users/{$sender->id}/transfer/", ['amount' => 250, 'receiver' => $receiver->id])
            ->assertNoContent();

        $this->assertSame('7.50', $sender->fresh()->balance);
        $this->assertSame('2.50', $receiver->fresh()->balance);
        $this->assertSame(TransactionType::Transfer, $sender->transactions()->sole()->type);
        $this->assertSame(TransactionType::Transfer, $receiver->transactions()->sole()->type);
    }

    public function test_a_transfer_beyond_the_credit_limit_is_rejected(): void
    {
        config(['spacemarket.global_credit_limit' => null]);

        $sender = Drinker::factory()->create(['balance' => 1]);
        $receiver = Drinker::factory()->create(['balance' => 0]);

        $this->postJson("/v3/users/{$sender->id}/transfer/", ['amount' => 200, 'receiver' => $receiver->id])
            ->assertStatus(402);

        $this->assertSame('1.00', $sender->fresh()->balance);
        $this->assertSame('0.00', $receiver->fresh()->balance);
    }

    public function test_a_transfer_may_use_the_configured_credit_limit(): void
    {
        config(['spacemarket.global_credit_limit' => 2000]);

        $sender = Drinker::factory()->create(['balance' => 1]);
        $receiver = Drinker::factory()->create(['balance' => 0]);

        $this->postJson("/v3/users/{$sender->id}/transfer/", ['amount' => 200, 'receiver' => $receiver->id])
            ->assertNoContent();

        $this->assertSame('-1.00', $sender->fresh()->balance);
    }

    public function test_a_user_cannot_transfer_to_themselves(): void
    {
        $drinker = Drinker::factory()->create(['balance' => 10]);

        $this->postJson("/v3/users/{$drinker->id}/transfer/", ['amount' => 100, 'receiver' => $drinker->id])
            ->assertStatus(400);
    }

    public function test_transferring_to_an_unknown_user_is_rejected(): void
    {
        $drinker = Drinker::factory()->create(['balance' => 10]);

        $this->postJson("/v3/users/{$drinker->id}/transfer/", ['amount' => 100, 'receiver' => 404])
            ->assertStatus(400);
    }
}
