<?php

namespace Database\Factories\Procurement;

use App\Models\Helpers\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Procurement\PurchaseOrder>
 */
class PurchaseOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $currency = Currency::where('code', 'USD')->firstOrFail();

        return [
            'number'        => fake()->numberBetween(100, 999),
            'date'          => fake()->date,
            'currency_id'   => $currency->id,
        ];
    }
}
