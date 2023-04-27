<?php

namespace Database\Factories\Accounting;

use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reference' => fake()->lexify('????'),
        ];
    }
}
