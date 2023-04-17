<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 11:26:37 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\PurchaseOrderItem;

use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseOrderItem;
use Lorisleiva\Actions\Concerns\AsAction;

class StorePurchaseOrderItem
{
    use AsAction;

    public function handle(PurchaseOrder $purchaseOrder, array $modelData): PurchaseOrderItem
    {
        /** @var PurchaseOrderItem $purchaseOrderItem */
        $purchaseOrderItem = $purchaseOrder->items()->create($modelData);

        return $purchaseOrderItem;
    }
}
