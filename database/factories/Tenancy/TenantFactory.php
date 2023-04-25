<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 25 Apr 2023 14:35:52 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Database\Factories\Tenancy;

use App\Models\Assets\Country;
use App\Models\Assets\Currency;
use App\Models\Assets\Language;
use App\Models\Assets\Timezone;
use Illuminate\Database\Eloquent\Factories\Factory;

class TenantFactory extends Factory
{
    public function definition(): array
    {
        $country  = Country::where('code', 'US')->firstOrFail();
        $language = Language::where('code', fake()->languageCode)->firstOrFail();
        $timezone = Timezone::where('name', fake()->timezone('USA'))->firstOrFail();
        $currency = Currency::where('code', 'USD')->firstOrFail();

        return [
            'code'        => fake()->lexify(),
            'name'        => fake()->company(),
            'country_id'  => $country->id,
            'language_id' => $language->id,
            'timezone_id' => $timezone->id,
            'currency_id' => $currency->id,
        ];
    }
}
