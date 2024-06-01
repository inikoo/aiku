<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 May 2023 12:12:23 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Location;

use App\Models\Helpers\Country;
use App\Models\Inventory\Location;
use Lorisleiva\Actions\Concerns\AsObject;

class GetLocationsOptions
{
    use AsObject;

    public function handle(): array
    {

        $selectOptions = [];
        /** @var Country $location */
        foreach (Location::limit(10)->get() as $location // TODO make it fast reload
        ) {
            $selectOptions[$location->id] =
                [
                    'label' => $location->name.' ('.$location->code.')',
                ];
        }

        return $selectOptions;
    }

    public function asController(): array
    {
        return $this->handle();
    }
}
