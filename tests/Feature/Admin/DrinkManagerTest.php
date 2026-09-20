<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\DrinkManager;
use App\Models\Drink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DrinkManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('admin.drinks'))->assertRedirect(route('login'));
    }

    public function test_admin_can_create_a_drink(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(DrinkManager::class)
            ->call('create')
            ->set('name', 'Mate')
            ->set('price', '1.50')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('drinks', ['name' => 'Mate', 'price' => 1.5, 'active' => true]);
    }

    public function test_admin_can_edit_a_drink(): void
    {
        $this->actingAs(User::factory()->create());
        $drink = Drink::factory()->create(['name' => 'Mate', 'price' => 1.0]);

        Livewire::test(DrinkManager::class)
            ->call('edit', $drink->id)
            ->set('price', '2.00')
            ->call('save');

        $this->assertSame('2.00', $drink->fresh()->price);
    }

    public function test_admin_can_toggle_a_drink_inactive(): void
    {
        $this->actingAs(User::factory()->create());
        $drink = Drink::factory()->create(['active' => true]);

        Livewire::test(DrinkManager::class)->call('toggleActive', $drink->id);

        $this->assertFalse($drink->fresh()->active);
    }
}
