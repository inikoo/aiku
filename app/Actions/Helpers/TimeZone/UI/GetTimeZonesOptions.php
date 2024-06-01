<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 01 Jun 2024 19:35:06 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\TimeZone\UI;

use App\Models\Helpers\Timezone;
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
