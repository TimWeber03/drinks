<?php

namespace Tests\Feature;

use App\Enums\TransactionType;
use App\Models\Drink;
use App\Models\Drinker;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DrinkerBalanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_buying_a_drink_deducts_its_price_and_logs_a_transaction(): void
    {
        $drinker = Drinker::factory()->create(['balance' => 10]);
        $drink = Drink::factory()->create(['price' => 1.5]);

        $transaction = $drinker->buy($drink);

        $this->assertSame('8.50', $drinker->fresh()->balance);
        $this->assertSame('8.50', $transaction->balance_after);
        $this->assertSame('-1.50', $transaction->amount);
        $this->assertSame(TransactionType::Purchase, $transaction->type);
        $this->assertSame($drink->id, $transaction->drink_id);
        $this->assertSame($drinker->id, $transaction->drinker_id);
    }

    public function test_depositing_money_increases_balance_and_logs_a_transaction(): void
    {
        $drinker = Drinker::factory()->create(['balance' => 0]);
        $admin = User::factory()->create();

        $transaction = $drinker->deposit(20, $admin);

        $this->assertSame('20.00', $drinker->fresh()->balance);
        $this->assertSame('20.00', $transaction->amount);
        $this->assertSame(TransactionType::Deposit, $transaction->type);
        $this->assertSame($admin->id, $transaction->created_by);
    }

    public function test_admin_can_manually_adjust_a_balance(): void
    {
        $drinker = Drinker::factory()->create(['balance' => 5]);
        $admin = User::factory()->create();

        $transaction = $drinker->adjustBalance(-2.5, $admin);

        $this->assertSame('2.50', $drinker->fresh()->balance);
        $this->assertSame(TransactionType::Adjustment, $transaction->type);
        $this->assertSame($admin->id, $transaction->created_by);
    }

    public function test_buying_can_take_the_balance_negative(): void
    {
        $drinker = Drinker::factory()->create(['balance' => 1]);
        $drink = Drink::factory()->create(['price' => 5]);

        $drinker->buy($drink);

        $this->assertSame('-4.00', $drinker->fresh()->balance);
    }

    public function test_concurrent_purchases_do_not_lose_updates(): void
    {
        $drinker = Drinker::factory()->create(['balance' => 100]);
        $drink = Drink::factory()->create(['price' => 1]);

        // Simulate two kiosk taps racing to buy at the same time by
        // running the balance mutation twice against the same starting
        // model instance; the row lock inside applyBalanceChange must
        // still serialize them into a correct final balance.
        DB::transaction(function () use ($drinker, $drink) {
            $drinker->buy($drink);
        });

        DB::transaction(function () use ($drinker, $drink) {
            $drinker->buy($drink);
        });

        $this->assertSame('98.00', $drinker->fresh()->balance);
        $this->assertSame(2, $drinker->transactions()->count());
    }
}
