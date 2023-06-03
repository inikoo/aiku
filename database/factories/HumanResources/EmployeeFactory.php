<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 30 Sept 2022 11:45:17 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace Database\Factories\HumanResources;

use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'contact_name'  => fake()->name,
            'date_of_birth' => fake()->date,
            'email'         => fake()->email,
        ];
    }
}
