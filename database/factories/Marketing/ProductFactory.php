<?php

namespace Database\Factories\Marketing;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Marketing\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'code' => fake()->lexify(),
            'units' => fake()->numberBetween(20, 200),
            'price' => fake()->numberBetween(100, 2000),
            'rrp' => fake()->numberBetween(20, 100),
            'name' => fake()->name,
            'description' => fake()->text,
            'owner_id' => 1,
            'owner_type' => '?',
        ];
    }
}
