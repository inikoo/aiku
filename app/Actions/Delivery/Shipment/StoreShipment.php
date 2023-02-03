<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 02 Feb 2023 13:17:23 Malaysia Time, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Delivery\Shipment;

use App\Models\Delivery\DeliveryNote;
use App\Models\Delivery\Shipment;
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
