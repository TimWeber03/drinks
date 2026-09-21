<?php

namespace Database\Factories;

use App\Models\Image;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Image>
 */
class ImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fileName = fake()->unique()->lexify('??????').'.png';

        return [
            'path' => 'images/'.$fileName,
            'file_name' => $fileName,
            'mime_type' => 'image/png',
            'size' => fake()->numberBetween(1024, 500000),
        ];
    }
}
