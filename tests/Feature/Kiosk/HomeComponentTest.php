<?php

namespace Tests\Feature\Kiosk;

use App\Livewire\Kiosk\Home;
use App\Models\Drink;
use App\Models\Drinker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HomeComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_only_active_drinkers(): void
    {
        Drinker::factory()->create(['name' => 'Active Drinker', 'active' => true]);
        Drinker::factory()->create(['name' => 'Inactive Drinker', 'active' => false]);

        Livewire::test(Home::class)
            ->assertSee('Active Drinker')
            ->assertDontSee('Inactive Drinker');
    }

    public function test_searching_drinkers_ignores_case(): void
    {
        Drinker::factory()->create(['name' => 'Alice']);
        Drinker::factory()->create(['name' => 'Bob']);

        Livewire::test(Home::class)
            ->set('search', 'aLI')
            ->assertSee('Alice')
            ->assertDontSee('Bob');
    }

    public function test_selecting_a_drinker_shows_active_drinks_only(): void
    {
        $drinker = Drinker::factory()->create();
        Drink::factory()->create(['name' => 'Mate', 'active' => true]);
        Drink::factory()->create(['name' => 'Discontinued', 'active' => false]);

        Livewire::test(Home::class)
            ->call('selectDrinker', $drinker->id)
            ->assertSet('view', 'drinks')
            ->assertSee('Mate')
            ->assertDontSee('Discontinued');
    }

    public function test_buying_a_drink_deducts_balance_and_shows_confirmation(): void
    {
        $drinker = Drinker::factory()->create(['balance' => 10]);
        $drink = Drink::factory()->create(['price' => 1.5]);

        Livewire::test(Home::class)
            ->call('selectDrinker', $drinker->id)
            ->call('buy', $drink->id)
            ->assertSet('view', 'confirmation');

        $this->assertSame('8.50', $drinker->fresh()->balance);
    }

    public function test_depositing_a_quick_amount_increases_balance(): void
    {
        $drinker = Drinker::factory()->create(['balance' => 0]);

        Livewire::test(Home::class)
            ->call('selectDrinker', $drinker->id)
            ->call('showDeposit')
            ->call('depositQuickAmount', 10)
            ->assertSet('view', 'confirmation');

        $this->assertSame('10.00', $drinker->fresh()->balance);
    }

    public function test_depositing_zero_or_negative_shows_a_validation_error(): void
    {
        $drinker = Drinker::factory()->create(['balance' => 0]);

        Livewire::test(Home::class)
            ->call('selectDrinker', $drinker->id)
            ->call('showDeposit')
            ->set('depositAmount', '-5')
            ->call('depositCustomAmount')
            ->assertHasErrors('depositAmount')
            ->assertSet('view', 'deposit');

        $this->assertSame('0.00', $drinker->fresh()->balance);
    }

    public function test_out_of_stock_drinks_are_shown_as_out_of_stock_by_default(): void
    {
        $drinker = Drinker::factory()->create();
        Drink::factory()->create(['name' => 'Mate', 'stock_tracked' => true, 'stock' => 0]);

        Livewire::test(Home::class)
            ->call('selectDrinker', $drinker->id)
            ->assertSee('Mate')
            ->assertSee('Out of stock');
    }

    public function test_hiding_out_of_stock_drinks_removes_them_while_keeping_others(): void
    {
        $drinker = Drinker::factory()->create();
        Drink::factory()->create(['name' => 'Mate', 'stock_tracked' => true, 'stock' => 0]);
        Drink::factory()->create(['name' => 'Cola', 'stock_tracked' => true, 'stock' => 5]);

        Livewire::test(Home::class)
            ->call('selectDrinker', $drinker->id)
            ->set('showOutOfStock', false)
            ->assertDontSee('Mate')
            ->assertSee('Cola');
    }

    public function test_buying_an_out_of_stock_drink_is_blocked_and_balance_is_unchanged(): void
    {
        $drinker = Drinker::factory()->create(['balance' => 10]);
        $drink = Drink::factory()->create(['name' => 'Mate', 'price' => 1, 'stock_tracked' => true, 'stock' => 0]);

        Livewire::test(Home::class)
            ->call('selectDrinker', $drinker->id)
            ->call('buy', $drink->id)
            ->assertHasErrors('stock')
            ->assertSet('view', 'drinks');

        $this->assertSame('10.00', $drinker->fresh()->balance);
    }
}
