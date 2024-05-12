<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 May 2024 22:13:37 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace Database\Factories\Ordering;

use Illuminate\Database\Eloquent\Factories\Factory;

class ShippingZoneSchemaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'   => fake()->name,
            'status' => false
        ];
    }
}
