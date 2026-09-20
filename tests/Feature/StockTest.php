<?php

namespace Tests\Feature;

use App\Exceptions\OutOfStockException;
use App\Models\Drink;
use App\Models\Drinker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockTest extends TestCase
{
    use RefreshDatabase;

    public function test_buying_a_stock_tracked_drink_decrements_stock(): void
    {
        $drinker = Drinker::factory()->create(['balance' => 10]);
        $drink = Drink::factory()->create(['price' => 1, 'stock_tracked' => true, 'stock' => 3]);

        $drinker->buy($drink);

        $this->assertSame(2, $drink->fresh()->stock);
    }

    public function test_buying_a_non_tracked_drink_never_touches_stock(): void
    {
        $drinker = Drinker::factory()->create(['balance' => 10]);
        $drink = Drink::factory()->create(['price' => 1, 'stock_tracked' => false, 'stock' => 0]);

        $drinker->buy($drink);

        $this->assertSame(0, $drink->fresh()->stock);
        $this->assertSame('9.00', $drinker->fresh()->balance);
    }

    public function test_buying_an_out_of_stock_drink_throws_and_changes_nothing(): void
    {
        $drinker = Drinker::factory()->create(['balance' => 10]);
        $drink = Drink::factory()->create(['price' => 1, 'stock_tracked' => true, 'stock' => 0]);

        $this->expectException(OutOfStockException::class);

        try {
            $drinker->buy($drink);
        } finally {
            $this->assertSame('10.00', $drinker->fresh()->balance);
            $this->assertSame(0, $drink->fresh()->stock);
            $this->assertSame(0, $drinker->transactions()->count());
        }
    }

    public function test_concurrent_purchases_do_not_oversell_the_last_unit(): void
    {
        $drinker = Drinker::factory()->create(['balance' => 10]);
        $drink = Drink::factory()->create(['price' => 1, 'stock_tracked' => true, 'stock' => 1]);

        $drinker->buy($drink);

        $this->assertSame(0, $drink->fresh()->stock);

        try {
            $drinker->buy($drink);
            $this->fail('Expected an OutOfStockException on the second purchase.');
        } catch (OutOfStockException) {
            // expected
        }

        $this->assertSame(0, $drink->fresh()->stock);
        $this->assertSame('9.00', $drinker->fresh()->balance);
        $this->assertSame(1, $drinker->transactions()->count());
    }
}
