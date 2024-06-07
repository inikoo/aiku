<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 07 Jun 2024 11:26:11 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace Database\Factories\CRM;

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
