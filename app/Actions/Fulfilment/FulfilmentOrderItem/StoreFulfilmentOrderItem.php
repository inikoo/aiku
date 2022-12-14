<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 28 Nov 2022 11:42:55 Central Indonesia Time, Ubud, Bali, Indonesia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentOrderItem;

use App\Models\Fulfilment\FulfilmentOrder;
use App\Models\Fulfilment\FulfilmentOrderItem;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreFulfilmentOrderItem
{
    use AsAction;

    public function handle(FulfilmentOrder $fulfilmentOrder, array $modelData): FulfilmentOrderItem
    {
        $modelData['shop_id']         = $fulfilmentOrder->shop_id;
        $modelData['customer_id']     = $fulfilmentOrder->customer_id;

        /** @var FulfilmentOrderItem $fulfilmentOrderItem */
        $fulfilmentOrderItem = $fulfilmentOrder->items()->create($modelData);
        return $fulfilmentOrderItem;
    }
}
