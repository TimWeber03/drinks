<?php

namespace Tests\Feature\Api\V3;

use App\Enums\TransactionType;
use App\Models\Barcode;
use App\Models\Drinker;
use App\Models\Image;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_users_with_balances_in_cents(): void
    {
        $drinker = Drinker::factory()->create(['name' => 'Ada', 'balance' => 12.34]);
        Barcode::factory()->forUser($drinker->id)->create(['barcode' => '1234567890123']);

        $this->getJson('/v3/users/')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $drinker->id)
            ->assertJsonPath('0.name', 'Ada')
            ->assertJsonPath('0.balance', 1234)
            ->assertJsonPath('0.barcode', '1234567890123')
            ->assertJsonPath('0.audit', false)
            ->assertJsonPath('0.redirect', true);
    }

    public function test_it_creates_a_user(): void
    {
        $avatar = Image::factory()->create();

        $this->postJson('/v3/users/', [
            'name' => 'Grace',
            'email' => 'grace@example.com',
            'balance' => 500,
            'audit' => true,
            'avatar' => $avatar->id,
        ])
            ->assertOk()
            ->assertJsonPath('name', 'Grace')
            ->assertJsonPath('balance', 500)
            ->assertJsonPath('audit', true)
            ->assertJsonPath('avatar', $avatar->id);

        $this->assertSame('5.00', Drinker::firstOrFail()->balance);
    }

    public function test_it_rejects_a_user_without_a_name(): void
    {
        $this->postJson('/v3/users/', ['email' => 'nobody@example.com'])->assertStatus(400);
    }

    public function test_it_shows_a_single_user(): void
    {
        $drinker = Drinker::factory()->create(['balance' => -2.5]);

        $this->getJson("/v3/users/{$drinker->id}/")
            ->assertOk()
            ->assertJsonPath('balance', -250);
    }

    public function test_it_edits_a_user(): void
    {
        $drinker = Drinker::factory()->create(['name' => 'Ada', 'active' => true]);

        $this->patchJson("/v3/users/{$drinker->id}/", ['name' => 'Ada L.', 'active' => false, 'redirect' => false])
            ->assertOk()
            ->assertJsonPath('name', 'Ada L.')
            ->assertJsonPath('active', false)
            ->assertJsonPath('redirect', false);
    }

    public function test_editing_the_balance_is_recorded_as_a_transaction(): void
    {
        $drinker = Drinker::factory()->create(['balance' => 1]);

        $this->patchJson("/v3/users/{$drinker->id}/", ['balance' => 350])
            ->assertOk()
            ->assertJsonPath('balance', 350);

        $transaction = $drinker->transactions()->sole();
        $this->assertSame(TransactionType::Deposit, $transaction->type);
        $this->assertSame('2.50', $transaction->amount);
        $this->assertSame('3.50', $drinker->fresh()->balance);
    }

    public function test_it_deletes_a_user(): void
    {
        $drinker = Drinker::factory()->create();

        $this->deleteJson("/v3/users/{$drinker->id}/")->assertOk();

        $this->assertDatabaseMissing('drinkers', ['id' => $drinker->id]);
    }

    public function test_it_returns_user_statistics(): void
    {
        Drinker::factory()->create(['balance' => 10, 'active' => true]);
        Drinker::factory()->create(['balance' => -2.5, 'active' => false]);

        $this->getJson('/v3/users/stats/')
            ->assertOk()
            ->assertJsonPath('user_count', 2)
            ->assertJsonPath('active_count', 1)
            ->assertJsonPath('balance_sum', 750);
    }

    public function test_it_finds_a_user_by_barcode(): void
    {
        $drinker = Drinker::factory()->create();
        Barcode::factory()->forUser($drinker->id)->create(['barcode' => '4001234567890']);

        $this->getJson('/v3/users/barcode/4001234567890/')
            ->assertOk()
            ->assertJsonPath('id', $drinker->id);
    }

    public function test_a_product_barcode_is_not_a_user(): void
    {
        Barcode::factory()->create(['barcode' => '4001234567890']);

        $this->getJson('/v3/users/barcode/4001234567890/')->assertStatus(400);
    }

    public function test_an_unknown_barcode_is_not_found(): void
    {
        $this->getJson('/v3/users/barcode/0000000000000/')->assertNotFound();
    }
}
