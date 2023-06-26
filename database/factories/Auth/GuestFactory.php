<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 26 Apr 2023 12:12:30 Central European Summer Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace Database\Factories\Auth;

use App\Enums\Auth\Guest\GuestTypeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

class GuestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'contact_name'             => fake()->company(),
            'email'                    => fake()->email,
            'identity_document_number' => fake('en_GB')->vat(),
            'identity_document_type'   => 'passport',
            'type'                     => GuestTypeEnum::CONTRACTOR->value,
        ];
    }
}
