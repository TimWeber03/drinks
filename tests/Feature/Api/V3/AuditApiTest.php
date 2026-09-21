<?php

namespace Tests\Feature\Api\V3;

use App\Enums\TransactionType;
use App\Models\Drink;
use App\Models\Drinker;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_requires_a_start_date(): void
    {
        $this->getJson('/v3/audits/')->assertStatus(400);
    }

    public function test_it_sums_transactions_in_the_requested_range(): void
    {
        $drinker = Drinker::factory()->create();
        $drink = Drink::factory()->create();

        $this->transaction($drinker, TransactionType::Deposit, 5, now()->subDays(2));
        $this->transaction($drinker, TransactionType::Purchase, -1.5, now()->subDay(), $drink);
        $this->transaction($drinker, TransactionType::Purchase, -1.5, now()->subDays(10));

        $response = $this->getJson('/v3/audits/?start='.now()->subDays(3)->toDateString());

        $response->assertOk()
            ->assertJsonPath('sum', 350)
            ->assertJsonPath('deposits_sum', 500)
            ->assertJsonPath('payments_sum', -150)
            ->assertJsonCount(2, 'audits')
            ->assertJsonPath('audits.1.difference', -150)
            ->assertJsonPath('audits.1.product', $drink->id);
    }

    public function test_an_end_date_limits_the_range(): void
    {
        $drinker = Drinker::factory()->create();

        $this->transaction($drinker, TransactionType::Deposit, 5, now()->subDays(5));
        $this->transaction($drinker, TransactionType::Deposit, 5, now());

        $this->getJson('/v3/audits/?start='.now()->subDays(6)->toDateString().'&end='.now()->subDays(2)->toDateString())
            ->assertOk()
            ->assertJsonCount(1, 'audits')
            ->assertJsonPath('sum', 500);
    }

    public function test_it_filters_by_user_when_auditing_is_enabled(): void
    {
        $audited = Drinker::factory()->create(['audit' => true]);
        $other = Drinker::factory()->create(['audit' => true]);

        $this->transaction($audited, TransactionType::Deposit, 2, now());
        $this->transaction($other, TransactionType::Deposit, 7, now());

        $this->getJson('/v3/audits/?start='.now()->subDay()->toDateString().'&user='.$audited->id)
            ->assertOk()
            ->assertJsonCount(1, 'audits')
            ->assertJsonPath('sum', 200);
    }

    public function test_it_refuses_audits_for_users_who_disabled_them(): void
    {
        $drinker = Drinker::factory()->create(['audit' => false]);

        $this->getJson('/v3/audits/?start='.now()->subDay()->toDateString().'&user='.$drinker->id)
            ->assertStatus(401);
    }

    public function test_an_unknown_user_is_not_found(): void
    {
        $this->getJson('/v3/audits/?start='.now()->subDay()->toDateString().'&user=404')
            ->assertNotFound();
    }

    private function transaction(
        Drinker $drinker,
        TransactionType $type,
        float $amount,
        \DateTimeInterface $createdAt,
        ?Drink $drink = null,
    ): Transaction {
        $transaction = Transaction::create([
            'drinker_id' => $drinker->id,
            'drink_id' => $drink?->id,
            'type' => $type,
            'amount' => $amount,
            'balance_after' => $amount,
        ]);

        $transaction->timestamps = false;
        $transaction->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();

        return $transaction;
    }
}
