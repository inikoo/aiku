<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 12 May 2023 11:55:53 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Assets\Country;

use App\Models\Assets\Country;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsObject;

class GetAddressData
{
    use AsObject;

    public function handle(): array
    {

        $selectOptions = [];
        /** @var Country $country */
        foreach (Country::all() as $country) {
            $selectOptions[$country->id] =
                [
                    'label'               => $country->name.' ('.$country->code.')',
                    'fields'              => Arr::get($country->data, 'fields'),
                    'administrativeAreas' => Arr::get($country->data, 'administrative_areas'),
                ];
        }

        return $selectOptions;

    }
}
