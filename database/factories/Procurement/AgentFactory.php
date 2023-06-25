<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 25 Apr 2023 12:48:11 Central European Summer Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace Database\Factories\Procurement;

use App\Models\Assets\Currency;
use App\Models\Helpers\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

class AgentFactory extends Factory
{
    public function definition(): array
    {
        $currency = Currency::latest()->first();

        return [
            'code'         => fake()->lexify(),
            'name'         => fake()->company,
            'company_name' => fake()->company,
            'contact_name' => fake()->name,
            'email'        => fake()->email,
            'currency_id'  => $currency->id,
            'address'      => Address::factory()->definition()

        ];
    }
}
