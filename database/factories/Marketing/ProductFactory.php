<?php

namespace Database\Factories\Marketing;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code'        => fake()->lexify(),
            'units'       => fake()->numberBetween(20, 200),
            'price'       => fake()->numberBetween(100, 2000),
            'rrp'         => fake()->numberBetween(20, 100),
            'name'        => fake()->name,
            'description' => fake()->text,
        ];
    }
}
