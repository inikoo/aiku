<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 10:02:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Dispatch\Shipment\Hydrators;

use App\Actions\Traits\WithTenantJob;
use App\Models\Dispatch\Shipment;
use Lorisleiva\Actions\Concerns\AsAction;

class ShipmentHydrateUniversalSearch
{
    use AsAction;
    use WithTenantJob;

    public function handle(Shipment $shipment): void
    {
        $shipment->universalSearch()->updateOrCreate(
            [],
            [
                'section' => 'Dispatch',
                'route'   => json_encode([
                    'name'      => '', // TODO: Need to know the route name
                    'arguments' => [
                        $shipment->slug
                    ]
                ]),
                'icon'           => 'fa-box-usd',
                'title'          => $shipment->code,
                'description'    => $shipment->tracking
            ]
        );
    }

}
