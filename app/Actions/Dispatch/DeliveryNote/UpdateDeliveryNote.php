<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatch\DeliveryNote;

use App\Actions\WithActionUpdate;
use App\Models\Dispatch\DeliveryNote;

class UpdateDeliveryNote
{
    use WithActionUpdate;

    public function handle(DeliveryNote $deliveryNote, array $modelData): DeliveryNote {

        return $this->update($deliveryNote, $modelData, ['data']);

    }
}
