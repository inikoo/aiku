<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 10 May 2023 14:06:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\StockDeliveryItem;

use App\Models\Procurement\PurchaseOrderItem;
use App\Models\Procurement\StockDelivery;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreStockDeliveryItemBySelectedPurchaseOrderItem
{
    use AsAction;

    public function handle(StockDelivery $stockDelivery, array $purchaseOrderIds): array
    {
        $items              = [];
        $purchaseOrderItems = PurchaseOrderItem::whereIn('purchase_order_id', $purchaseOrderIds)->get();

        foreach ($purchaseOrderItems as $item) {
            $items[] = StoreStockDeliveryItem::run($stockDelivery, [
                'supplier_product_id' => $item->supplier_product_id,
                'unit_price'          => $item->unit_price,
                'unit_quantity'       => $item->unit_quantity
            ]);
        }

        return $items;
    }
}
