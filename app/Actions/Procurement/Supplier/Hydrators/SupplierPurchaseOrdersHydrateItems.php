<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 08 May 2023 11:47:40 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Supplier\Hydrators;

use App\Actions\WithTenantJob;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Enums\Procurement\PurchaseOrderItem\PurchaseOrderItemStateEnum;
use App\Models\Procurement\PurchaseOrder;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class SupplierPurchaseOrdersHydrateItems implements ShouldBeUnique
{
    use AsAction;
    use WithTenantJob;

    public function handle(PurchaseOrder $supplierPurchaseOrder): void
    {
        $stats = [
            'number_of_items' => $supplierPurchaseOrder->items()->count(),
            'cost_items'      => $this->getTotalCostItem($supplierPurchaseOrder),
            'gross_weight'    => $this->getGrossWeight($supplierPurchaseOrder),
            'net_weight'      => $this->getNetWeight($supplierPurchaseOrder)
        ];

        $checkedItemsCount = $supplierPurchaseOrder->items()->where('state', PurchaseOrderItemStateEnum::CHECKED)->count();
        $items = $supplierPurchaseOrder->items()->count();

        if(($checkedItemsCount === $items) && ($items > 0)) {
            $stats['state'] = SupplierDeliveryStateEnum::CHECKED;
            $stats['checked_at'] = now();
            $stats[$supplierPurchaseOrder->state->value . '_at'] = null;
        }

        $supplierPurchaseOrder->update($stats);
    }

    public function getGrossWeight(PurchaseOrder $supplierPurchaseOrder): float
    {
        $grossWeight = 0;

        foreach ($supplierPurchaseOrder->items as $item) {
            foreach ($item->supplierProduct['tradeUnits'] as $tradeUnit) {
                $grossWeight += $item->supplierProduct['grossWeight'] * $tradeUnit->pivot->package_quantity;
            }
        }

        return $grossWeight;
    }

    public function getNetWeight(PurchaseOrder $supplierPurchaseOrder): float
    {
        $netWeight = 0;

        foreach ($supplierPurchaseOrder->items as $item) {
            foreach ($item->supplierProduct['tradeUnits'] as $tradeUnit) {
                $netWeight += $item->supplierProduct['netWeight'] * $tradeUnit->pivot->package_quantity;
            }
        }

        return $netWeight;
    }

    public function getTotalCostItem(PurchaseOrder $supplierPurchaseOrder): float
    {
        $costItems = 0;

        foreach ($supplierPurchaseOrder->items as $item) {
            $costItems += $item->unit_price * $item->supplierProduct['cost'];
        }

        return $costItems;
    }
}
