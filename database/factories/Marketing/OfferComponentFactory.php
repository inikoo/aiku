<?php

namespace Database\Factories\Marketing;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Marketing\OfferComponent>
 */
class OfferComponentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'code' => fake()->lexify,
            'name' => fake()->name
        ];
    }
}
