<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 Jan 2024 12:18:24 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace Database\Factories\SupplyChain;

use App\Models\Assets\Country;
use App\Models\Assets\Currency;
use App\Models\Assets\Language;
use App\Models\Assets\Timezone;
use App\Models\Helpers\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

class AgentFactory extends Factory
{
    public function definition(): array
    {
        $currency = Currency::latest()->first();

        return [
            'code'        => fake()->lexify(),
            'name'        => fake()->company,
            'email'       => fake()->email,
            'currency_id' => $currency->id,
            'country_id'  => Country::first()->id,
            'timezone_id' => Timezone::first()->id,
            'language_id' => Language::where('code', 'en')->first()->id,
            'address'     => Address::factory()->definition()

        ];
    }
}
