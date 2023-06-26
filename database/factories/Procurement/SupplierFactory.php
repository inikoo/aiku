<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Reviewed: Sun, 25 Jun 2023 14:22:58 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Database\Factories\Procurement;

use App\Models\Assets\Currency;
use App\Models\Helpers\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    public function definition(): array
    {
        $currency = Currency::where('code', 'USD')->firstOrFail();

        return [
            'code'         => fake()->lexify(),
            'name'         => fake()->name,
            'company_name' => fake()->company,
            'contact_name' => fake()->name,
            'email'        => fake()->email,
            'currency_id'  => $currency->id,
            'address'      => Address::factory()->definition()
        ];
    }
}
