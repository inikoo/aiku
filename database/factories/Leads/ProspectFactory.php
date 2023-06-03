<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 25 Apr 2023 12:47:10 Central European Summer Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace Database\Factories\Leads;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProspectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'contact_name'    => fake()->name,
            'company_name'    => fake()->company,
            'email'           => fake()->email,
            'phone'           => fake()->phoneNumber,
            'contact_website' => fake()->url,
        ];
    }
}
