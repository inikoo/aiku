<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 26 Apr 2023 12:12:30 Central European Summer Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace Database\Factories\Dropshipping;

use App\Models\Helpers\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerClientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reference'    => fake()->lexify(),
            'contact_name' => fake()->name,
            'company_name' => fake()->company,
            'email'        => fake()->email,
            'address'      => Address::factory()->definition(),
        ];
    }
}
