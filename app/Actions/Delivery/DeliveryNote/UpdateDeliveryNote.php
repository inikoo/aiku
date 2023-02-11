<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 05 Dec 2021 15:42:26 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Delivery\DeliveryNote;

use App\Actions\WithActionUpdate;
use App\Models\Delivery\DeliveryNote;

class UpdateDeliveryNote
{
    use WithActionUpdate;

    public function handle(DeliveryNote $deliveryNote, array $modelData): DeliveryNote {

        return $this->update($deliveryNote, $modelData, ['data']);

    }
}
