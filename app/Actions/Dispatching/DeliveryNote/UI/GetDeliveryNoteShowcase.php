<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 13:48:10 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatching\DeliveryNote\UI;

use App\Models\Dispatching\DeliveryNote;
use Lorisleiva\Actions\Concerns\AsObject;

class GetDeliveryNoteShowcase
{
    use AsObject;

    public function handle(DeliveryNote $deliveryNote): array
    {
        return [
            []
        ];
    }
}
