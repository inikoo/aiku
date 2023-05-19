<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 May 2023 12:12:23 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Assets\Country\UI;

use App\Models\Assets\Country;
use Lorisleiva\Actions\Concerns\AsObject;

class GetCountriesOptions
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
                ];
        }

        return $selectOptions;

    }
}
