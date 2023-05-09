<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 08 May 2023 11:47:40 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\PurchaseOrder\Hydrators;

use App\Actions\WithTenantJob;
use App\Models\Procurement\PurchaseOrder;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class PurchaseOrderHydrateItem implements ShouldBeUnique
{
    use AsAction;
    use WithTenantJob;

    public function handle(PurchaseOrder $purchaseOrder): void
    {
        $stats = [
            'number_of_items' => $purchaseOrder->items()->count(),
            'cost_items'      => $this->getTotalCostItem($purchaseOrder),
            'total_weight'    => $this->getTotalWeight($purchaseOrder)
        ];

        $purchaseOrder->update($stats);
    }

    public function getTotalWeight(PurchaseOrder $purchaseOrder): float
    {
        $totalWeight = 0;

        foreach ($purchaseOrder->items as $item) {
            foreach ($item->supplierProduct['tradeUnits'] as $tradeUnit) {
                $totalWeight += $item->supplierProduct['weight'] * $tradeUnit->pivot->package_quantity;
            }
        }

        return $totalWeight;
    }

    public function getTotalCostItem(PurchaseOrder $purchaseOrder): float
    {
        $costItems = 0;

        foreach ($purchaseOrder->items as $item) {
            $costItems += $item->unit_price * $item->supplierProduct['cost'];
        }

        return $costItems;
    }
}
