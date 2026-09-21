<?php

namespace Database\Factories;

use App\Models\Denomination;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Denomination>
 */
class DenominationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'amount' => fake()->unique()->randomElement([50, 100, 200, 500, 1000, 2000]),
            'image_id' => null,
        ];
    }
}
