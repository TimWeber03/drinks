<?php

namespace Database\Seeders;

use App\Models\Drink;
use App\Models\Drinker;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with local dev fixtures.
     * Admin login: admin@example.com / password
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
        ]);

        Drink::factory()->create(['name' => 'Mate', 'price' => 1.00, 'bottle_size' => 0.5]);
        Drink::factory()->create(['name' => 'Cola', 'price' => 1.00, 'bottle_size' => 0.5]);
        Drink::factory()->create(['name' => 'Water', 'price' => 0.50, 'bottle_size' => 0.5]);

        Drinker::factory()->create(['name' => 'Alice', 'balance' => 10]);
        Drinker::factory()->create(['name' => 'Bob', 'balance' => -2.5]);
        Drinker::factory()->create(['name' => 'Charlie', 'balance' => 0]);
    }
}
