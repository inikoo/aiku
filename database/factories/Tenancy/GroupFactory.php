<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 25 Apr 2023 20:31:30 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Database\Factories\Tenancy;

use App\Models\Assets\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

class GroupFactory extends Factory
{
    public function definition(): array
    {
        $currency = Currency::latest()->first();

        return [
            'code'        => fake()->lexify(),
            'name'        => fake()->company(),
            'currency_id' => $currency->id
        ];
    }
}
