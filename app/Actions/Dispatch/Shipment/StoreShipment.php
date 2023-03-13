<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatch\Shipment;

use App\Actions\Dispatch\Shipment\Hydrators\ShipmentHydrateUniversalSearch;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Dispatch\Shipment;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreShipment
{
    use AsAction;

    public function handle(DeliveryNote $parent, array $modelData): Shipment
    {
        /** @var Shipment $shipment */
        $shipment = $parent->shipments()->create($modelData);

        ShipmentHydrateUniversalSearch::dispatch($shipment);

        return $shipment;
    }
}
