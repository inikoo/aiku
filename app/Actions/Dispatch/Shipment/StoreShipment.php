<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatch\Shipment;

use App\Models\Dispatch\DeliveryNote;
use App\Models\Dispatch\Shipment;
use Lorisleiva\Actions\Concerns\AsAction;


class StoreShipment
{
    use AsAction;

    public function handle(DeliveryNote $parent ,array $modelData): ?Shipment
    {
        if(class_basename($parent)=='DeliveryNote'){

            $parent->shipments()->create($modelData);
        }

        return null;
    }


}
