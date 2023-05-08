<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 08 May 2023 11:47:40 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


namespace App\Actions\Procurement\PurchaseOrder\Hydrators;

use App\Actions\WithTenantJob;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\SupplierProduct;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class PurchaseOrderHydrateItem  implements ShouldBeUnique {
    use AsAction;
    use WithTenantJob;

    public function handle(PurchaseOrder $purchaseOrder): void
    {
        $stats = [
            'number_of_items' => $purchaseOrder->items()->count(),
            'cost_items' => $this->getTotalCostItem($purchaseOrder)
        ];

        $purchaseOrder->update($stats);
    }

    public function getTotalCostItem(PurchaseOrder $purchaseOrder): float
    {
        $costItems = 0;
        $supplierProduct = $purchaseOrder->provider->products->first();

        foreach ($purchaseOrder->items as $item) {
            $costItems += $item->unit_price * $supplierProduct->cost;
        }

        return $costItems;
    }
}
