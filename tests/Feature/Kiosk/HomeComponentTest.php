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
}
