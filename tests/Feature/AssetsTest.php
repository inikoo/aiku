<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:04:32 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Models\Assets\Country;

beforeAll(fn () => loadDB('fresh_with_assets.dump'));

it('has countries', function (string $countryCode) {
    $country= Country::where('code', $countryCode)->firstOrFail();
    expect($country->id)->toBeInt();
})->with([
    'GB','ES','FR','DE'
]);

it('is no countries with code', function (string $countryCode) {
    $country= Country::where('code', $countryCode)->firstOrFail();
    expect($country->id)->toThrow(Exception::class);
})->with([
    'XX','es',''
])->todo('Fix toThrow');
