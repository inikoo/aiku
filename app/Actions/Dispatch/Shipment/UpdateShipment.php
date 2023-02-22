<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatch\Shipment;

use App\Actions\WithActionUpdate;
use App\Models\Dispatch\Shipment;

class UpdateShipment
{
    use WithActionUpdate;

    public function handle(Shipment $shipment, array $modelData): Shipment
    {
        return $this->update($shipment, $modelData, ['data']);
    }
}
