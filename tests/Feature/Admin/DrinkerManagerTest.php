<?php

namespace Tests\Feature\Admin;

use App\Enums\TransactionType;
use App\Livewire\Admin\DrinkerManager;
use App\Models\Drinker;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DrinkerManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('admin.drinkers'))->assertRedirect(route('login'));
    }

    public function test_admin_can_create_a_drinker(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(DrinkerManager::class)
            ->call('create')
            ->set('name', 'Dave')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('drinkers', ['name' => 'Dave', 'balance' => 0]);
    }

    public function test_admin_can_manually_adjust_a_balance_and_it_is_attributed(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);
        $drinker = Drinker::factory()->create(['balance' => 5]);

        Livewire::test(DrinkerManager::class)
            ->call('startAdjustment', $drinker->id)
            ->set('adjustmentAmount', '-2')
            ->call('applyAdjustment')
            ->assertHasNoErrors();

        $this->assertSame('3.00', $drinker->fresh()->balance);
        $this->assertDatabaseHas('transactions', [
            'drinker_id' => $drinker->id,
            'type' => TransactionType::Adjustment->value,
            'created_by' => $admin->id,
        ]);
    }

    public function test_adjustment_requires_a_nonzero_numeric_amount(): void
    {
        $this->actingAs(User::factory()->create());
        $drinker = Drinker::factory()->create(['balance' => 5]);

        Livewire::test(DrinkerManager::class)
            ->call('startAdjustment', $drinker->id)
            ->set('adjustmentAmount', '0')
            ->call('applyAdjustment')
            ->assertHasErrors('adjustmentAmount');

        $this->assertSame('5.00', $drinker->fresh()->balance);
    }
}
