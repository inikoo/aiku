<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 28 Apr 2023 17:02:44 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Database\Factories\Sales;

use App\Enums\Sales\Transaction\TransactionTypeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sales\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'type'             => TransactionTypeEnum::ORDER,
            'quantity_bonus'   => fake()->numberBetween(0, 10),
            'quantity_ordered' => fake()->numberBetween(0, 10),
        ];
    }
}
