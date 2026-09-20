<?php

namespace Database\Factories;

use App\Enums\TransactionType;
use App\Models\Drinker;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = fake()->randomFloat(2, 0.5, 3);

        return [
            'drinker_id' => Drinker::factory(),
            'type' => TransactionType::Deposit,
            'amount' => $amount,
            'balance_after' => $amount,
        ];
    }
}
