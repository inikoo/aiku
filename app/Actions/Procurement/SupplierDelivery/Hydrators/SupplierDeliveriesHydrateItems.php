<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 May 2024 00:53:14 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\SupplierDelivery\Hydrators;

use App\Enums\Procurement\SupplierDelivery\SupplierDeliveryStateEnum;
use App\Enums\Procurement\SupplierDeliveryItem\SupplierDeliveryItemStateEnum;
use App\Models\Procurement\SupplierDelivery;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class SupplierDeliveriesHydrateItems implements ShouldBeUnique
{
    use AsAction;


    public function handle(SupplierDelivery $supplierDelivery): void
    {
        $stats = [
            'number_of_items' => $supplierDelivery->items()->count(),
            'cost_items'      => $this->getTotalCostItem($supplierDelivery),
            'gross_weight'    => $this->getGrossWeight($supplierDelivery),
            'net_weight'      => $this->getNetWeight($supplierDelivery)
        ];

        $checkedItemsCount = $supplierDelivery->items()->where('state', SupplierDeliveryItemStateEnum::CHECKED)->count();
        $items             = $supplierDelivery->items()->count();

        if(($checkedItemsCount === $items) && ($items > 0)) {
            $stats['state']                                 = SupplierDeliveryStateEnum::CHECKED;
            $stats['checked_at']                            = now();
            $stats[$supplierDelivery->state->value . '_at'] = null;
        }

        $supplierDelivery->update($stats);
    }

    public function getGrossWeight(SupplierDelivery $supplierDelivery): float
    {
        $grossWeight = 0;

        foreach ($supplierDelivery->items as $item) {
            foreach ($item->supplierProduct['tradeUnits'] as $tradeUnit) {
                $grossWeight += $item->supplierProduct['grossWeight'] * $tradeUnit->pivot->package_quantity;
            }
        }

        return $grossWeight;
    }

    public function getNetWeight(SupplierDelivery $supplierDelivery): float
    {
        $netWeight = 0;

        foreach ($supplierDelivery->items as $item) {
            foreach ($item->supplierProduct['tradeUnits'] as $tradeUnit) {
                $netWeight += $item->supplierProduct['netWeight'] * $tradeUnit->pivot->package_quantity;
            }
        }

        return $netWeight;
    }

    public function getTotalCostItem(SupplierDelivery $supplierDelivery): float
    {
        $costItems = 0;

        foreach ($supplierDelivery->items as $item) {
            $costItems += $item->unit_price * $item->supplierProduct['cost'];
        }

        return $costItems;
    }
}
