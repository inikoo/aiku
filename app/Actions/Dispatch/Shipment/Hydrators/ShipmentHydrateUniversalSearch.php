<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 10:02:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Dispatch\Shipment\Hydrators;

use App\Actions\Traits\WithOrganisationJob;
use App\Models\Dispatch\Shipment;
use Lorisleiva\Actions\Concerns\AsAction;

class ShipmentHydrateUniversalSearch
{
    use AsAction;
    use WithOrganisationJob;

    public function handle(Shipment $shipment): void
    {
        $shipment->universalSearch()->updateOrCreate(
            [],
            [
                'section'        => 'dispatch',
                'title'          => $shipment->tracking??'',
                'description'    => ''
            ]
        );
    }

}
