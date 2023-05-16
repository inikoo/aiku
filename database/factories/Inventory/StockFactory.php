<?php

namespace Database\Factories\Inventory;

use Illuminate\Database\Eloquent\Factories\Factory;

class StockFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => fake()->lexify(),
            'value' => rand(100, 1000),
            'quantity' => rand(10, 100),
        ];
    }
}
