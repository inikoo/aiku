<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 Jan 2024 12:18:24 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace Database\Factories\SupplyChain;

use App\Models\Assets\Currency;
use App\Models\Helpers\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

class AgentFactory extends Factory
{
    public function definition(): array
    {
        $currency = Currency::latest()->first();

        return [
            'code'         => fake()->lexify(),
            'name'         => fake()->company,
            'company_name' => fake()->company,
            'contact_name' => fake()->name,
            'email'        => fake()->email,
            'currency_id'  => $currency->id,
            'address'      => Address::factory()->definition()

        ];
    }
}
