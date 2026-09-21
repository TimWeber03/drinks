<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\UserManager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_open_the_management_view(): void
    {
        $this->get(route('admin.admins'))->assertOk();
    }

    public function test_admin_can_create_another_admin(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(UserManager::class)
            ->call('create')
            ->set('name', 'New Admin')
            ->set('email', 'new-admin@example.com')
            ->set('password', 'password123')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', ['email' => 'new-admin@example.com']);
    }

    public function test_admin_cannot_delete_their_own_account(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        Livewire::test(UserManager::class)
            ->call('delete', $admin->id)
            ->assertHasErrors('delete');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_can_delete_another_admin(): void
    {
        $this->actingAs(User::factory()->create());
        $other = User::factory()->create();

        Livewire::test(UserManager::class)->call('delete', $other->id);

        $this->assertDatabaseMissing('users', ['id' => $other->id]);
    }
}
