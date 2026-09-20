<?php

namespace Database\Factories;

use App\Models\Drink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Drink>
 */
class DrinkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Mate', 'Cola', 'Water', 'Fritz-Kola', 'Apfelschorle']),
            'price' => fake()->randomFloat(2, 0.5, 3),
            'active' => true,
        ];
    }
}
