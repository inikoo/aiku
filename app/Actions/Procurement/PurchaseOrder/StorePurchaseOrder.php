<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 11:26:37 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\PurchaseOrder;

use App\Models\Procurement\Agent;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\Supplier;
use Lorisleiva\Actions\Concerns\AsAction;

class StorePurchaseOrder
{
    use AsAction;

    public function handle(Agent|Supplier $parent, array $modelData): PurchaseOrder
    {
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = $parent->purchaseOrders()->create($modelData);

        //        $purchaseOrder->stats()->create();
        return $purchaseOrder;
    }
}
