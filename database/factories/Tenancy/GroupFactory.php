<?php

namespace Database\Factories\Tenancy;

use App\Models\Assets\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class GroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $currency = Currency::latest()->first();

        return [
            'code' => fake()->userName,
            'name' => fake()->name,
            'currency_id' => $currency->id
        ];
    }
}
