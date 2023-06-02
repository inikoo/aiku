<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 May 2023 12:12:23 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Assets\TimeZone\UI;

use App\Models\Assets\Timezone;
use Lorisleiva\Actions\Concerns\AsObject;

class GetTimeZonesOptions
{
    use AsObject;

    public function handle(): array
    {

        $selectOptions = [];
        /** @var Timezone $timezone */
        foreach (Timezone::all() as $timezone) {

            $selectOptions[$timezone->id] =
                [
                    'label'   => $timezone->name.' ('.$timezone->location.')',
                ];
        }

        return $selectOptions;

    }
}
