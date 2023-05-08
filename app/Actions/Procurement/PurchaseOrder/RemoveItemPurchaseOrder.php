<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 05 May 2023 16:55:25 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\PurchaseOrder;

use App\Models\Procurement\PurchaseOrderItem;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class RemoveItemPurchaseOrder
{
    use AsAction;
    use WithAttributes;

    public function handle(PurchaseOrderItem $purchaseOrderItem): void
    {
        $purchaseOrderItem->delete();
    }
}
