<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 24 May 2023 20:59:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatching\DeliveryNote;

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
