<?php

namespace Database\Factories;

use App\Enums\BarcodeType;
use App\Models\Barcode;
use App\Models\Drink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Barcode>
 */
class BarcodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'barcode' => fake()->unique()->ean13(),
            'type' => BarcodeType::Product,
            'linked' => Drink::factory(),
        ];
    }

    public function forUser(int $drinkerId): self
    {
        return $this->state([
            'type' => BarcodeType::User,
            'linked' => $drinkerId,
        ]);
    }
}
