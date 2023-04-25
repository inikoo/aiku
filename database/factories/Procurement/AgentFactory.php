<?php

namespace Database\Factories\Procurement;

use App\Models\Assets\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Procurement\Agent>
 */
class AgentFactory extends Factory
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
            'code' => strtolower(Str::random(4)),
            'name' => fake()->name,
            'company_name' => fake()->company,
            'contact_name' => fake()->name,
            'email' => fake()->email,
            'currency_id' => $currency->id,
        ];
    }
}
