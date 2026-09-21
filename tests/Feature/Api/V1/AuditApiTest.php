<?php

namespace Tests\Feature\Api\V1;

use App\Enums\TransactionType;
use App\Models\Drink;
use App\Models\Drinker;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sums_transactions_of_the_current_month_by_default(): void
    {
        $drinker = Drinker::factory()->create();
        $drink = Drink::factory()->create();

        $this->transaction($drinker, TransactionType::Deposit, 5, now()->startOfMonth());
        $this->transaction($drinker, TransactionType::Purchase, -1.5, now(), $drink);
        $this->transaction($drinker, TransactionType::Purchase, -2, now()->subMonths(2));

        $this->getJson('/audits.json')
            ->assertOk()
            ->assertJsonPath('sum', '3.50')
            ->assertJsonPath('deposits_sum', '5.00')
            ->assertJsonPath('payments_sum', '-1.50')
            ->assertJsonCount(2, 'audits')
            ->assertJsonPath('audits.1.difference', '-1.50')
            ->assertJsonPath('audits.1.drink', $drink->id);
    }

    public function test_it_accepts_a_date_range_split_into_components(): void
    {
        $drinker = Drinker::factory()->create();
        $inRange = now()->subYear()->startOfYear()->addDays(5);

        $this->transaction($drinker, TransactionType::Deposit, 4, $inRange);
        $this->transaction($drinker, TransactionType::Deposit, 9, now());

        $query = http_build_query([
            'start_date' => ['year' => $inRange->year, 'month' => 1, 'day' => 1],
            'end_date' => ['year' => $inRange->year, 'month' => 1, 'day' => 31],
        ]);

        $this->getJson('/audits.json?'.$query)
            ->assertOk()
            ->assertJsonCount(1, 'audits')
            ->assertJsonPath('sum', '4.00');
    }

    public function test_it_rejects_an_invalid_month(): void
    {
        $this->getJson('/audits.json?'.http_build_query(['start_date' => ['month' => 13]]))
            ->assertStatus(400);
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
