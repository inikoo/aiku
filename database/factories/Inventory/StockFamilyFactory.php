<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 12 May 2023 09:11:21 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Database\Factories\Inventory;

use Illuminate\Database\Eloquent\Factories\Factory;

class StockFamilyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => fake()->lexify('????'),
            'name' => fake()->name,
        ];
    }
}
