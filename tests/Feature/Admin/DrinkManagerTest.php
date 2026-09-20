<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\DrinkManager;
use App\Models\Drink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_admin_can_enable_stock_tracking_with_a_quantity(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(DrinkManager::class)
            ->call('create')
            ->set('name', 'Mate')
            ->set('price', '1.50')
            ->set('stockTracked', true)
            ->set('stock', '12')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('drinks', ['name' => 'Mate', 'stock_tracked' => true, 'stock' => 12]);
    }

    public function test_stock_is_forced_to_zero_when_tracking_is_disabled(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(DrinkManager::class)
            ->call('create')
            ->set('name', 'Mate')
            ->set('price', '1.50')
            ->set('stockTracked', false)
            ->set('stock', '12')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('drinks', ['name' => 'Mate', 'stock_tracked' => false, 'stock' => 0]);
    }

    public function test_replacing_a_drinks_image_deletes_the_old_file(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $oldPath = UploadedFile::fake()->image('old.jpg')->store('drinks', 'public');
        $drink = Drink::factory()->create(['image_path' => $oldPath]);

        Livewire::test(DrinkManager::class)
            ->call('edit', $drink->id)
            ->set('image', UploadedFile::fake()->image('new.jpg'))
            ->call('save');

        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($drink->fresh()->image_path);
        $this->assertNotSame($oldPath, $drink->fresh()->image_path);
    }
}
