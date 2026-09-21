<?php

namespace Tests\Feature\Api\V1;

use App\Enums\TransactionType;
use App\Models\Drinker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_users_with_decimal_balances(): void
    {
        $drinker = Drinker::factory()->create(['name' => 'Ada', 'balance' => 12.34]);

        $this->getJson('/users.json')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $drinker->id)
            ->assertJsonPath('0.name', 'Ada')
            ->assertJsonPath('0.balance', '12.34')
            ->assertJsonPath('0.active', true)
            ->assertJsonPath('0.audit', false)
            ->assertJsonPath('0.redirect', true);
    }

    public function test_it_returns_defaults_for_a_new_user(): void
    {
        $this->getJson('/users/new.json')
            ->assertOk()
            ->assertJsonPath('id', null)
            ->assertJsonPath('balance', '0.00')
            ->assertJsonPath('active', true);
    }

    public function test_it_creates_a_user(): void
    {
        $this->postJson('/users.json', ['name' => 'Grace', 'email' => 'grace@example.com', 'balance' => '5.00'])
            ->assertCreated()
            ->assertJsonPath('name', 'Grace')
            ->assertJsonPath('balance', '5.00');
    }

    public function test_it_rejects_a_user_without_a_name(): void
    {
        $this->postJson('/users.json', [])->assertStatus(400);
    }

    public function test_it_shows_a_user(): void
    {
        $drinker = Drinker::factory()->create(['balance' => -2.5]);

        $this->getJson("/users/{$drinker->id}.json")
            ->assertOk()
            ->assertJsonPath('balance', '-2.50');
    }

    public function test_it_edits_a_user_without_a_response_body(): void
    {
        $drinker = Drinker::factory()->create(['name' => 'Ada']);

        $this->patchJson("/users/{$drinker->id}.json", ['name' => 'Ada L.', 'audit' => true])
            ->assertNoContent();

        $this->assertSame('Ada L.', $drinker->fresh()->name);
        $this->assertTrue($drinker->fresh()->audit);
    }

    public function test_editing_the_balance_is_recorded_as_a_transaction(): void
    {
        $drinker = Drinker::factory()->create(['balance' => 1]);

        $this->patchJson("/users/{$drinker->id}.json", ['balance' => '3.50'])->assertNoContent();

        $transaction = $drinker->transactions()->sole();
        $this->assertSame(TransactionType::Deposit, $transaction->type);
        $this->assertSame('2.50', $transaction->amount);
        $this->assertSame('3.50', $drinker->fresh()->balance);
    }

    public function test_it_deletes_a_user(): void
    {
        $drinker = Drinker::factory()->create();

        $this->deleteJson("/users/{$drinker->id}.json")->assertNoContent();

        $this->assertDatabaseMissing('drinkers', ['id' => $drinker->id]);
    }

    public function test_it_returns_user_statistics(): void
    {
        Drinker::factory()->create(['balance' => 10]);
        Drinker::factory()->create(['balance' => -2.5]);

        $this->getJson('/users/stats.json')
            ->assertOk()
            ->assertJsonPath('user_count', 2)
            ->assertJsonPath('balance_sum', '7.50');
    }
}
