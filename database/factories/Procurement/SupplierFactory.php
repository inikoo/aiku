<?php

namespace Database\Factories\Procurement;

use App\Models\Assets\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Procurement\Supplier>
 */
class SupplierFactory extends Factory
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
            'code'         => 'supplier',
            'name'         => fake()->name,
            'company_name' => fake()->company,
            'contact_name' => fake()->name,
            'email'        => fake()->email,
            'currency_id'  => $currency->id,
            'address_id'   => 1
        ];
    }
}
