<?php

namespace Database\Factories;

use App\Models\Drinker;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Drinker>
 */
class DrinkerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->firstName(),
            'balance' => 0,
            'active' => true,
        ];
    }
}
