<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 03 Feb 2023 21:12:43 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Delivery\Shipment;

use App\Actions\WithActionUpdate;
use App\Models\Delivery\Shipment;

class UpdateShipment
{
    use WithActionUpdate;

    public function handle(Shipment $shipment, array $modelData): Shipment
    {
        return $this->update($shipment, $modelData, ['data']);
    }
}
