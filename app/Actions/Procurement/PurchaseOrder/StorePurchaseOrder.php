<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 11:26:37 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\PurchaseOrder;

use App\Models\Procurement\PurchaseOrder;
use Lorisleiva\Actions\Concerns\AsAction;

class StorePurchaseOrder
{
    use AsAction;

    public function handle(array $modelData): PurchaseOrder
    {
        $purchaseOrder = PurchaseOrder::create($modelData);

//        $purchaseOrder->stats()->create();
        return $purchaseOrder;
    }
}
