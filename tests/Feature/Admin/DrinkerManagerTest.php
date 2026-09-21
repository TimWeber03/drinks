<?php

namespace Tests\Feature\Admin;

use App\Enums\TransactionType;
use App\Livewire\Admin\DrinkerManager;
use App\Models\Drinker;
use App\Models\Image;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DrinkerManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_open_the_management_view(): void
    {
        $this->get(route('admin.drinkers'))->assertOk();
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

    public function test_a_guest_can_adjust_a_balance_without_an_admin_on_record(): void
    {
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
            'created_by' => null,
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

    public function test_admin_can_upload_an_avatar_for_a_drinker(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        Livewire::test(DrinkerManager::class)
            ->call('create')
            ->set('name', 'Dave')
            ->set('avatar', UploadedFile::fake()->image('dave.jpg'))
            ->call('save')
            ->assertHasNoErrors();

        $drinker = Drinker::where('name', 'Dave')->firstOrFail();
        $this->assertNotNull($drinker->avatar);
        Storage::disk('public')->assertExists($drinker->avatar->path);
    }

    public function test_replacing_a_drinkers_avatar_deletes_the_old_file(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $oldPath = UploadedFile::fake()->image('old.jpg')->store('avatars', 'public');
        $drinker = Drinker::factory()->create([
            'avatar_id' => Image::create(['path' => $oldPath, 'file_name' => 'old.jpg'])->id,
        ]);

        Livewire::test(DrinkerManager::class)
            ->call('edit', $drinker->id)
            ->set('avatar', UploadedFile::fake()->image('new.jpg'))
            ->call('save');

        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($drinker->fresh()->avatar->path);
        $this->assertNotSame($oldPath, $drinker->fresh()->avatar->path);
    }
}
