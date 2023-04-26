<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 26 Apr 2023 12:12:30 Central European Summer Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace Database\Factories\Sales;

use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'contact_name'             => fake()->name,
            'company_name'             => fake()->company,
            'email'                    => fake()->email,
            'phone'                    => fake()->phoneNumber,
            'identity_document_number' => fake('en_GB')->vat(),
            'website'                  => fake()->url,
        ];
    }
}
