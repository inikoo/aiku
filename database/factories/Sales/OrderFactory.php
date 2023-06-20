<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 28 Apr 2023 17:02:43 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Database\Factories\Sales;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OMS\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'number'      => fake()->numberBetween(100, 999),
            'date'        => fake()->date,
            'customer_id' => fake()->numberBetween(1, 100)
        ];
    }
}
